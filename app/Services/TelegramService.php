<?php
// FILE: app/Services/TelegramService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\TelegramConfig;
use App\Services\TenantService;

class TelegramService
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Получить конфигурацию бота для текущего магазина.
     */
    private function getConfig()
    {
        $tenantId = $this->tenantService->getCurrentTenantId();

        // 1. Пытаемся найти бота, привязанного к конкретному магазину
        $config = TelegramConfig::where('tenant_id', $tenantId)
                                ->where('is_active', true)
                                ->first();

        // 2. Если не нашли, можно искать "общего" бота (где tenant_id is null), 
        // но для изоляции лучше, чтобы у каждого магазина был свой. 
        // Если хотите фоллбек на общего, раскомментируйте:
        /*
        if (!$config) {
            $config = TelegramConfig::whereNull('tenant_id')->where('is_active', true)->first();
        }
        */

        return $config;
    }

    public function sendStockAlert(string $productName, string $size, string $tenantName)
    {
        $config = $this->getConfig();

        if (!$config) {
            Log::warning("[Telegram] No active configuration found for tenant: {$tenantName}");
            return;
        }

        $message = "⚠️ *STOCK ALERT* ⚠️\n\n" .
                   "Store: *{$tenantName}*\n" .
                   "Product: {$productName}\n" .
                   "Size: {$size}\n" .
                   "Status: *OUT OF STOCK* ❌\n\n" .
                   "Please restock immediately.";

        $this->sendMessage($config, $message);
    }

    public function sendOrderNotification(string $orderNumber, float $amount, string $tenantName)
    {
        $config = $this->getConfig();

        if (!$config) {
            return;
        }

        $message = "💰 *NEW ORDER* 💰\n\n" .
                   "Store: *{$tenantName}*\n" .
                   "Order: `{$orderNumber}`\n" .
                   "Amount: *$" . number_format($amount, 2) . "*\n" .
                   "Status: *Pending*\n";

        $this->sendMessage($config, $message);
    }

    private function sendMessage($config, $text)
    {
        try {
            Http::post("https://api.telegram.org/bot{$config->bot_token}/sendMessage", [
                'chat_id' => $config->chat_id,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            Log::error("Telegram send failed: " . $e->getMessage());
        }
    }
}