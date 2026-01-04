<?php
// FILE: app/Http\Controllers\Admin\SettingsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TelegramConfig;
use App\Models\TenantSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;

class SettingsController extends Controller
{
    private function checkSuperAdmin()
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Access denied.');
        }
    }

    public function index()
    {
        $this->checkSuperAdmin();
        $telegramConfigs = TelegramConfig::all();
        $tenants = config('tenants.tenants');
        return view('admin.settings.index', compact('telegramConfigs', 'tenants'));
    }

    public function content(Request $request)
    {
        $this->checkSuperAdmin();
        
        $tenants = config('tenants.tenants');
        $activeTenant = $request->query('tenant', session('admin_current_tenant_id', array_key_first($tenants)));
        
        if (!isset($tenants[$activeTenant])) {
            $activeTenant = array_key_first($tenants);
        }
    
        // Принудительно очищаем сессию при смене магазина
        if ($request->has('tenant')) {
            session()->forget('admin_current_tenant_id');
        }
        
        // Сохраняем выбранный магазин в сессии
        session(['admin_current_tenant_id' => $activeTenant]);
    
        $contentBlocks = TenantSetting::getContentBlocks($activeTenant);
        
        // Генерируем ID и slug для блоков, у которых их нет
        foreach ($contentBlocks as &$block) {
            if (!isset($block['id'])) {
                $block['id'] = uniqid('block_', true);
            }
            if (!isset($block['slug'])) {
                // Генерируем slug из заголовка или типа
                $title = $block['title'] ?? ($block['content'] ?? '');
                if (!empty($title)) {
                    $block['slug'] = Str::slug(Str::limit($title, 50));
                } else {
                    $block['slug'] = $block['type'] . '_' . substr($block['id'], -8);
                }
            }
        }
    
        // Возвращаем ответ с заголовками, отключающими кеширование
        return response()
            ->view('admin.settings.content', compact('tenants', 'activeTenant', 'contentBlocks'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function updateContent(Request $request)
    {
        $this->checkSuperAdmin();
        
        $tenantId = $request->tenant_id;
        $blocks = $request->input('blocks', []);
        $files = $request->file('blocks', []);

        $finalBlocks = [];

        foreach ($blocks as $index => $block) {
            $type = $block['type'];
            $slug = trim($block['slug'] ?? '');
            
            // Если slug пустой - пропускаем
            if (empty($slug)) {
                continue;
            }
            
            // Генерируем ID если нет
            $blockId = $block['id'] ?? uniqid('block_', true);
            
            $newBlock = [
                'id' => $blockId,
                'slug' => $slug,
                'type' => $type,
                'created_at' => $block['created_at'] ?? now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString()
            ];

            if ($type === 'text') {
                $newBlock['content'] = $block['content'] ?? '';
                $newBlock['title'] = $block['title'] ?? '';
            } 
            elseif ($type === 'image' || $type === 'video') {
                if (isset($files[$index]['file'])) {
                    $file = $files[$index]['file'];
                    $path = $file->store("tenants/{$tenantId}/media", 'public');
                    $newBlock['path'] = $path;
                    $newBlock['original_name'] = $file->getClientOriginalName();
                    $newBlock['mime_type'] = $file->getMimeType();
                    $newBlock['size'] = $file->getSize();
                } else {
                    $newBlock['path'] = $block['old_path'] ?? null;
                    if (isset($block['original_name'])) {
                        $newBlock['original_name'] = $block['original_name'];
                    }
                }
                $newBlock['title'] = $block['title'] ?? '';
                $newBlock['alt'] = $block['alt'] ?? '';
            }

            $finalBlocks[] = $newBlock;
        }

        TenantSetting::set("content_blocks_{$tenantId}", $finalBlocks, 'content');

        return back()->with('success', 'Content updated successfully for ' . $tenantId);
    }

    public function deleteContent(Request $request)
    {
        $this->checkSuperAdmin();
        
        $request->validate([
            'tenant_id' => 'required|string',
            'content_ids' => 'required|array',
            'content_ids.*' => 'string'
        ]);

        $result = TenantSetting::deleteContent(
            $request->tenant_id,
            $request->content_ids
        );

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Content deleted successfully',
                'data' => $result
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to delete content'
        ], 400);
    }

    /**
     * Экспорт контента магазина
     */
    public function exportContent(Request $request)
    {
        $this->checkSuperAdmin();
        
        $request->validate([
            'tenant_id' => 'required|string',
            'format' => 'in:json,csv'
        ]);
        
        $tenantId = $request->tenant_id;
        $format = $request->format ?? 'json';
        
        // Получаем данные для экспорта
        $exportData = TenantSetting::exportContent($tenantId);
        
        if ($format === 'json') {
            $fileName = "content-blocks-{$tenantId}-" . date('Y-m-d-H-i-s') . '.json';
            $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            return response($json)
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', "attachment; filename={$fileName}");
        }
        
        // CSV формат (только текстовые блоки для простоты)
        if ($format === 'csv') {
            $fileName = "content-blocks-{$tenantId}-" . date('Y-m-d-H-i-s') . '.csv';
            
            $csvData = [];
            $csvData[] = ['Slug', 'Type', 'Title', 'Content', 'Created At', 'Updated At'];
            
            foreach ($exportData['blocks'] as $block) {
                if ($block['type'] === 'text') {
                    $csvData[] = [
                        $block['slug'] ?? '',
                        $block['type'] ?? '',
                        $block['title'] ?? '',
                        $block['content'] ?? '',
                        $block['created_at'] ?? '',
                        $block['updated_at'] ?? ''
                    ];
                }
            }
            
            $csvContent = $this->arrayToCsv($csvData);
            
            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', "attachment; filename={$fileName}");
        }
        
        return back()->with('error', 'Unsupported export format');
    }

    /**
     * Импорт контента для магазина
     */
    public function importContent(Request $request)
    {
        $this->checkSuperAdmin();
        
        $request->validate([
            'tenant_id' => 'required|string',
            'import_file' => 'required|file|mimes:json,csv,txt',
            'import_mode' => 'required|in:merge,replace,update'
        ]);
        
        $tenantId = $request->tenant_id;
        $importMode = $request->import_mode;
        $file = $request->file('import_file');
        $extension = $file->getClientOriginalExtension();
        
        try {
            if ($extension === 'json') {
                $content = file_get_contents($file->path());
                $importData = json_decode($content, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()->with('error', 'Invalid JSON file: ' . json_last_error_msg());
                }
                
            } elseif ($extension === 'csv') {
                $importData = $this->csvToArray($file->path(), $tenantId);
            } else {
                return back()->with('error', 'Unsupported file format');
            }
            
            // Выполняем импорт
            $result = TenantSetting::importContent($tenantId, $importData, $importMode);
            
            // Формируем сообщение об успехе
            $message = "Import completed! ";
            $message .= "Total: {$result['total_imported']} blocks. ";
            $message .= "New: {$result['new_blocks']}, ";
            $message .= "Updated: {$result['updated_blocks']}, ";
            $message .= "Skipped: {$result['skipped_blocks']}.";
            
            if (!empty($result['errors'])) {
                $message .= " Errors: " . implode(', ', array_slice($result['errors'], 0, 3));
                if (count($result['errors']) > 3) {
                    $message .= " and " . (count($result['errors']) - 3) . " more";
                }
            }
            
            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Конвертация массива в CSV
     */
    private function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');
        
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
     * Конвертация CSV в массив для импорта
     */
    private function csvToArray(string $filePath, string $tenantId): array
    {
        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle);
        
        $blocks = [];
        $rowNumber = 1;
        
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            // Пропускаем пустые строки
            if (empty(array_filter($row))) {
                continue;
            }
            
            // Создаем блок из CSV строки
            $block = [
                'slug' => $row[0] ?? '',
                'type' => $row[1] ?? 'text',
                'title' => $row[2] ?? '',
                'content' => $row[3] ?? '',
                'created_at' => $row[4] ?? now()->toDateTimeString(),
                'updated_at' => $row[5] ?? now()->toDateTimeString()
            ];
            
            // Пропускаем если нет slug
            if (empty($block['slug'])) {
                continue;
            }
            
            $blocks[] = $block;
        }
        
        fclose($handle);
        
        return [
            'blocks' => $blocks,
            'import_info' => [
                'source' => 'csv',
                'import_date' => now()->toDateTimeString(),
                'total_rows' => $rowNumber - 1,
                'valid_blocks' => count($blocks)
            ]
        ];
    }

    public function update(Request $request)
    {
        $this->checkSuperAdmin();
        return back()->with('success', 'General settings updated.');
    }

}