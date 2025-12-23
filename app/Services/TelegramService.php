<?php
// FILE: app/Services/TelegramService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected ?string $botToken;
    protected ?string $chatId;

    public function __construct()
    {
        // В реальном проекте это берется из конфига или настроек тенанта
        // Для примера используем заглушку или ENV
        $this->botToken = config('services.telegram.bot_token');
        $this->chatId = config('services.telegram.chat_id'); // ID чата админа/менеджера
    }

    public function sendStockAlert(string $productName, string $size, string $tenantName)
    {
        $message = "⚠️ *STOCK ALERT* ⚠️\n\n" .
                   "Store: *{$tenantName}*\n" .
                   "Product: {$productName}\n" .
                   "Size: {$size}\n" .
                   "Status: *OUT OF STOCK* ❌\n\n" .
                   "Please restock immediately.";

        // Если токена нет, пишем в лог (для разработки)
        if (!$this->botToken || !$this->chatId) {
            Log::info("[TELEGRAM MOCK] " . $message);
            return;
        }

        try {
            Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            Log::error("Telegram send failed: " . $e->getMessage());
        }
    }
}