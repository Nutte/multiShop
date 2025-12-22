<?php
// FILE: app/Http/Controllers/Admin/OrderController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TenantSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::latest()->paginate(20);
        return view('admin.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        // ПРИМЕР ИНТЕГРАЦИИ: Если статус 'shipped', создаем накладную (заглушка)
        if ($request->status === 'shipped') {
            $this->createDeliveryLabel($order);
        }

        return back()->with('success', 'Order status updated');
    }

    // Заглушка отправки в Telegram
    public function sendNotification($id)
    {
        $order = Order::findOrFail($id);
        
        // 1. Получаем настройки текущего магазина из БД
        $chatId = TenantSetting::get('telegram_chat_id');
        
        if (!$chatId) {
            return back()->with('error', 'Telegram Chat ID not configured for this store.');
        }

        // 2. Логика отправки (здесь был бы запрос к Telegram API)
        // Http::post("https://api.telegram.org/bot<TOKEN>/sendMessage", ...)
        
        Log::info("TELEGRAM SENT to {$chatId}: Order {$order->order_number} created.");

        return back()->with('success', "Notification sent to Chat ID: {$chatId}");
    }

    // Заглушка создания ТТН (Новая Почта)
    private function createDeliveryLabel(Order $order)
    {
        $apiKey = TenantSetting::get('novaposhta_api_key');
        if (!$apiKey) {
            Log::warning("Nova Poshta API Key missing for tenant.");
            return;
        }
        Log::info("NOVA POSHTA API call with key {$apiKey} for Order {$order->order_number}");
    }
}