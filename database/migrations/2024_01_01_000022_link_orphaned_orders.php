<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Order;

return new class extends Migration
{
    public function up(): void
    {
        // Получаем все заказы, у которых нет привязки к пользователю
        $orders = Order::whereNull('user_id')->get();

        foreach ($orders as $order) {
            // Нормализуем телефон заказа (на всякий случай)
            $phone = $this->normalizePhone($order->customer_phone);

            if ($phone) {
                // Ищем пользователя с таким телефоном
                // (Ищем и по нормализованному, и по оригинальному, чтобы наверняка)
                $user = User::where('phone', $phone)
                            ->orWhere('phone', $order->customer_phone)
                            ->first();

                if ($user) {
                    // Если пользователь найден - привязываем заказ к нему
                    // Используем update напрямую в DB, чтобы обойти любые защиты модели
                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update(['user_id' => $user->id]);
                }
            }
        }
    }

    public function down(): void
    {
        // Не требуется
    }

    // Хелпер нормализации (тот же, что в контроллере)
    private function normalizePhone($phone)
    {
        $clean = preg_replace('/[^0-9+]/', '', $phone);
        if (str_starts_with($clean, '0')) return '+38' . $clean;
        if (str_starts_with($clean, '380')) return '+' . $clean;
        return $clean;
    }
};