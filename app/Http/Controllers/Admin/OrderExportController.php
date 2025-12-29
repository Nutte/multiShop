<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderExportController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Экспорт заказов в CSV
     */
    public function exportCsv(Request $request)
    {
        $tenantId = $request->get('tenant_id') ?? $this->tenantService->getCurrentTenantId();
        
        if (!$tenantId) {
            return back()->with('error', 'Tenant context required.');
        }

        $this->tenantService->switchTenant($tenantId);

        $orders = Order::with(['items', 'user'])->get();
        
        $csvData = $this->generateCsvData($orders);
        $filename = "orders_export_" . date('Y-m-d_H-i-s') . ".csv";
        
        Storage::disk('local')->put("exports/{$filename}", $csvData);
        
        return Storage::disk('local')->download("exports/{$filename}", $filename);
    }

    /**
     * Генерация данных для CSV
     */
    protected function generateCsvData($orders): string
    {
        $headers = [
            'Order ID', 'Order Number', 'Customer Name', 'Customer Phone', 'Customer Email',
            'Status', 'Total Amount', 'Shipping Method', 'Shipping Address',
            'Created At', 'Items Count'
        ];

        $rows = [];
        foreach ($orders as $order) {
            $rows[] = [
                $order->id,
                $order->order_number,
                $order->customer_name,
                $order->customer_phone,
                $order->customer_email,
                $order->status,
                $order->total_amount,
                $order->shipping_method,
                $order->shipping_address,
                $order->created_at->format('Y-m-d H:i:s'),
                $order->items->count()
            ];
        }

        $output = fopen('php://temp', 'w');
        fputcsv($output, $headers);
        
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
     * Экспорт детальной информации по заказу
     */
    public function exportOrderDetail(Request $request, $id)
    {
        $tenantId = $request->get('tenant_id') ?? $this->tenantService->getCurrentTenantId();
        
        if (!$tenantId) {
            return back()->with('error', 'Tenant context required.');
        }

        $this->tenantService->switchTenant($tenantId);
        
        $order = Order::with(['items.product', 'user'])->findOrFail($id);
        
        $data = [
            'order' => $order->toArray(),
            'items' => $order->items->toArray(),
            'exported_at' => now()->toDateTimeString(),
        ];
        
        $filename = "order_{$order->order_number}_" . date('Y-m-d_H-i-s') . ".json";
        
        Storage::disk('local')->put("exports/{$filename}", json_encode($data, JSON_PRETTY_PRINT));
        
        return Storage::disk('local')->download("exports/{$filename}", $filename);
    }

    /**
     * Генерация отчета по заказам за период
     */
    public function periodReport(Request $request)
    {
        $tenantId = $request->get('tenant_id') ?? $this->tenantService->getCurrentTenantId();
        
        if (!$tenantId) {
            return back()->with('error', 'Tenant context required.');
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $this->tenantService->switchTenant($tenantId);

        $orders = Order::whereBetween('created_at', [
            $request->input('start_date'),
            $request->input('end_date')
        ])->get();

        $reportData = [
            'period' => [
                'start' => $request->input('start_date'),
                'end' => $request->input('end_date'),
            ],
            'summary' => [
                'total_orders' => $orders->count(),
                'total_amount' => $orders->sum('total_amount'),
                'avg_order_value' => $orders->avg('total_amount'),
                'status_distribution' => $orders->groupBy('status')->map->count(),
            ],
            'orders' => $orders
        ];

        return response()->json($reportData);
    }
}