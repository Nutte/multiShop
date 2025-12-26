<?php

// FILE: app/Http/Controllers/Admin/ContactMessageController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ContactMessageController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->role === 'super_admin';
        
        // Определяем текущий контекст
        $currentTenantId = null;
        if ($isSuperAdmin) {
            $currentTenantId = $request->get('tenant_id');
        } else {
            $currentTenantId = $this->tenantService->getCurrentTenantId();
        }

        $messages = collect();
        $paginatedMessages = null;

        if ($currentTenantId) {
            // 1. КОНКРЕТНЫЙ МАГАЗИН
            $this->tenantService->switchTenant($currentTenantId);
            $paginatedMessages = ContactMessage::latest()->paginate(20);
            // Добавляем инфу о магазине для отображения
            $paginatedMessages->getCollection()->transform(function ($msg) use ($currentTenantId) {
                $msg->tenant_id = $currentTenantId;
                $msg->tenant_name = config("tenants.tenants.{$currentTenantId}.name");
                return $msg;
            });
        } else {
            // 2. ВСЕ МАГАЗИНЫ (Только Супер-Админ)
            // Агрегация данных из всех схем
            $allMessages = collect();
            
            foreach (config('tenants.tenants') as $id => $config) {
                try {
                    $this->tenantService->switchTenant($id);
                    // Берем последние 20 из каждого, чтобы не перегружать память
                    $tenantMessages = ContactMessage::latest()->limit(20)->get();
                    
                    foreach ($tenantMessages as $msg) {
                        $msg->tenant_id = $id;
                        $msg->tenant_name = $config['name'];
                        $allMessages->push($msg);
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            // Сортируем все собранные сообщения по дате
            $sorted = $allMessages->sortByDesc('created_at')->values();
            
            // Ручная пагинация для коллекции
            $perPage = 20;
            $page = $request->get('page', 1);
            $paginatedMessages = new LengthAwarePaginator(
                $sorted->forPage($page, $perPage),
                $sorted->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        return view('admin.messages.index', [
            'messages' => $paginatedMessages,
            'currentTenantId' => $currentTenantId
        ]);
    }

    // Пометить как прочитанное / Посмотреть
    public function show(Request $request, $id)
    {
        // Для просмотра нужно знать контекст магазина
        $tenantId = $request->get('tenant_id');
        if (!$tenantId) {
            // Если контекст не передан, пытаемся определить для менеджера
            if (auth()->user()->role !== 'super_admin') {
                $tenantId = $this->tenantService->getCurrentTenantId();
            } else {
                return back()->with('error', 'Tenant ID required to view message.');
            }
        }

        $this->tenantService->switchTenant($tenantId);
        $message = ContactMessage::findOrFail($id);
        
        // Помечаем как прочитанное
        if (!$message->is_read) {
            $message->update(['is_read' => true]);
        }

        return view('admin.messages.show', compact('message', 'tenantId'));
    }

    public function destroy(Request $request, $id)
    {
        $tenantId = $request->get('tenant_id');
        if (auth()->user()->role !== 'super_admin' && !$tenantId) {
             $tenantId = $this->tenantService->getCurrentTenantId();
        }

        $this->tenantService->switchTenant($tenantId);
        ContactMessage::destroy($id);

        return redirect()->route('admin.messages.index', ['tenant_id' => $tenantId])
            ->with('success', 'Message deleted.');
    }
}