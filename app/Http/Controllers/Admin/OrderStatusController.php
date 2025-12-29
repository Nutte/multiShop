<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\TenantService;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Быстрое обновление статуса заказа
     */
    public function quickUpdate(Request $request, $id)
    {
        $request->validate([
            'tenant_id' => 'required|string',
            'status' => 'required|string',
            'is_instagram' => 'sometimes|boolean',
        ]);

        $this->tenantService->switchTenant($request->input('tenant_id'));
        
        $order = Order::findOrFail($id);
        $order->update([
            'status' => $request->input('status'),
            'is_instagram' => $request->boolean('is_instagram', false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated',
            'order' => $order
        ]);
    }

    /**
     * Получение статистики по статусам
     */
    public function statistics(Request $request)
    {
        $tenantId = $request->get('tenant_id') ?? $this->tenantService->getCurrentTenantId();
        
        if (!$tenantId) {
            return response()->json(['error' => 'Tenant context required'], 400);
        }

        $this->tenantService->switchTenant($tenantId);

        $statistics = [
            'new' => Order::where('status', 'new')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'total' => Order::count(),
        ];

        return response()->json($statistics);
    }

    /**
     * Пакетное обновление статусов
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|string',
            'order_ids' => 'required|array',
            'order_ids.*' => 'integer',
            'status' => 'required|string',
        ]);

        $this->tenantService->switchTenant($request->input('tenant_id'));

        $updatedCount = Order::whereIn('id', $request->input('order_ids'))
            ->update(['status' => $request->input('status')]);

        return response()->json([
            'success' => true,
            'message' => "Updated {$updatedCount} orders",
            'updated_count' => $updatedCount
        ]);
    }
}