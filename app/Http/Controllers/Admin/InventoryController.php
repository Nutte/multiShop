<?php
// FILE: app/Http/Controllers/Admin/InventoryController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ClothingLine;
use App\Models\TelegramConfig; // Импорт модели Telegram
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    private function resolveContext(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'super_admin') {
            return $this->tenantService->getCurrentTenantId();
        }
        $tenantId = $request->get('tenant_id');
        if ($tenantId) {
            $this->tenantService->switchTenant($tenantId);
            return $tenantId;
        }
        
        // Если Супер-Админ не выбрал конкретный магазин, возвращаем null (значит "Все магазины")
        // Если контекст уже был установлен middleware, сбрасываем его для логики "Всех"
        if ($request->has('tenant_id') && empty($request->tenant_id)) {
            return null;
        }

        return $this->tenantService->getCurrentTenantId();
    }

    public function index(Request $request)
    {
        $currentTenantId = $this->resolveContext($request);
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        
        // Получаем ботов для выпадающего списка
        $telegramBots = $isSuperAdmin 
            ? TelegramConfig::where('is_active', true)->get() 
            : TelegramConfig::where('tenant_id', $currentTenantId)->orWhereNull('tenant_id')->where('is_active', true)->get();

        // Логика сбора данных
        $products = new Collection();
        $isGlobalView = $isSuperAdmin && empty($currentTenantId);

        if ($isGlobalView) {
            // Собираем со всех магазинов
            foreach (config('tenants.tenants') as $id => $config) {
                try {
                    $this->tenantService->switchTenant($id);
                    $storeProducts = $this->getFilteredQuery($request)->latest()->take(50)->get();
                    
                    // Добавляем метку магазина
                    $storeProducts->each(function($p) use ($config) {
                        $p->tenant_name = $config['name'];
                    });
                    
                    $products = $products->merge($storeProducts);
                } catch (\Exception $e) { continue; }
            }
            // Ручная пагинация для объединенной коллекции (для красоты)
            $page = $request->get('page', 1);
            $perPage = 50;
            $products = new LengthAwarePaginator(
                $products->forPage($page, $perPage), 
                $products->count(), 
                $perPage, 
                $page, 
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            // Обычный режим одного магазина
            $products = $this->getFilteredQuery($request)->latest()->paginate(50)->withQueryString();
        }

        // --- EXPORT LOGIC ---
        if ($request->get('action') === 'export_csv') {
            // Для экспорта берем полные данные без пагинации
            $exportData = $isGlobalView ? $this->getAllTenantsData($request) : $this->getFilteredQuery($request)->get();
            return $this->exportCsv($exportData, $currentTenantId ?? 'All_Stores');
        }

        // Load filters (только если выбран конкретный магазин, иначе списки пустые)
        $categories = $currentTenantId ? Category::orderBy('name')->get() : collect();
        $lines = $currentTenantId ? ClothingLine::orderBy('name')->get() : collect();

        return view('admin.inventory.index', compact('products', 'categories', 'lines', 'currentTenantId', 'telegramBots'));
    }

    /**
     * Отправка отчета в Telegram
     */
    public function sendToTelegram(Request $request)
    {
        $request->validate([
            'telegram_config_id' => 'required|exists:public.telegram_configs,id',
        ]);

        $config = TelegramConfig::findOrFail($request->telegram_config_id);
        $currentTenantId = $this->resolveContext($request);
        $isGlobalView = auth()->user()->role === 'super_admin' && empty($currentTenantId);

        // 1. Собираем данные
        $data = $isGlobalView ? $this->getAllTenantsData($request) : $this->getFilteredQuery($request)->get();
        
        // 2. Генерируем CSV контент (строка)
        $csvContent = $this->generateCsvString($data);
        $fileName = 'inventory_report_' . date('Y-m-d_H-i') . '.csv';

        // 3. Формируем текстовое сообщение
        $totalItems = $data->sum('stock_quantity');
        $totalValue = $data->sum(fn($p) => $p->stock_quantity * ($p->sale_price ?? $p->price));
        
        $message = "📊 *Inventory Report*\n" .
                   "Store: " . ($currentTenantId ?? 'ALL STORES') . "\n" .
                   "Date: " . date('Y-m-d H:i') . "\n" .
                   "Total Items: *{$totalItems}*\n" .
                   "Total Value: *$" . number_format($totalValue, 2) . "*";

        // 4. Отправляем через Telegram API (multipart/form-data)
        try {
            // Сначала текст
            Http::post("https://api.telegram.org/bot{$config->bot_token}/sendMessage", [
                'chat_id' => $config->chat_id,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            // Потом файл
            $response = Http::attach(
                'document', $csvContent, $fileName
            )->post("https://api.telegram.org/bot{$config->bot_token}/sendDocument", [
                'chat_id' => $config->chat_id,
                'caption' => '📂 Full report attached (Excel compatible)',
            ]);

            if ($response->successful()) {
                return back()->with('success', 'Report sent to Telegram successfully.');
            } else {
                return back()->with('error', 'Telegram Error: ' . $response->body());
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send: ' . $e->getMessage());
        }
    }

    // --- HELPERS ---

    private function getFilteredQuery(Request $request)
    {
        $query = Product::with(['categories', 'variants', 'clothingLine']);

        if ($request->filled('search')) {
            $query->where(fn($q) => $q->where('name', 'ilike', "%{$request->search}%")
                                      ->orWhere('sku', 'ilike', "%{$request->search}%"));
        }
        if ($request->filled('category_id')) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $request->category_id));
        }
        if ($request->filled('clothing_line_id')) {
            $query->where('clothing_line_id', $request->clothing_line_id);
        }
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            } elseif ($request->stock_status === 'low_stock') {
                $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<', 5);
            } elseif ($request->stock_status === 'in_stock') {
                $query->where('stock_quantity', '>=', 5);
            }
        }
        return $query;
    }

    private function getAllTenantsData(Request $request)
    {
        $allData = new Collection();
        foreach (config('tenants.tenants') as $id => $config) {
            try {
                $this->tenantService->switchTenant($id);
                $products = $this->getFilteredQuery($request)->get();
                $products->each(function($p) use ($config) {
                    $p->tenant_name = $config['name'];
                });
                $allData = $allData->merge($products);
            } catch (\Exception $e) { continue; }
        }
        return $allData;
    }

    private function generateCsvString(Collection $products)
    {
        $output = fopen('php://temp', 'r+');
        fputs($output, "\xEF\xBB\xBF"); // BOM
        fputcsv($output, ['Store', 'SKU', 'Product Name', 'Category', 'Collection', 'Size Variants', 'Price', 'Stock', 'Value']);

        foreach ($products as $product) {
            $variantsStr = $product->variants->map(fn($v) => "{$v->size}: {$v->stock}")->join(' | ');
            $categoriesStr = $product->categories->pluck('name')->join(', ');
            $totalValue = $product->stock_quantity * ($product->sale_price ?? $product->price);
            
            fputcsv($output, [
                $product->tenant_name ?? 'Current',
                $product->sku,
                $product->name,
                $categoriesStr,
                $product->clothingLine->name ?? '-',
                $variantsStr ?: 'One Size',
                $product->price,
                $product->stock_quantity,
                number_format($totalValue, 2, '.', '')
            ]);
        }
        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);
        return $content;
    }

    private function exportCsv(Collection $products, string $name)
    {
        $content = $this->generateCsvString($products);
        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="inventory_' . $name . '.csv"');
    }
}