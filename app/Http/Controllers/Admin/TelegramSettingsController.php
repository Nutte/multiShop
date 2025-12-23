<?php
    // FILE: app/Http/Controllers/Admin/TelegramSettingsController.php

    namespace App\Http\Controllers\Admin;

    use App\Http\Controllers\Controller;
    use App\Models\TelegramConfig;
    use Illuminate\Http\Request;
    use Illuminate\Validation\Rule;
    use Illuminate\Support\Facades\Http;

    class TelegramSettingsController extends Controller
    {
        private function checkSuperAdmin()
        {
            if (auth()->user()->role !== 'super_admin') {
                abort(403, 'Access denied.');
            }
        }

        // Index используется только если мы идем на отдельную страницу (но сейчас у нас общие настройки)
        // Оставляем на всякий случай или для API вызовов
        public function index()
        {
            $this->checkSuperAdmin();
            $configs = TelegramConfig::all();
            $tenants = config('tenants.tenants');
            return view('admin.settings.telegram', compact('configs', 'tenants'));
        }

        public function store(Request $request)
        {
            $this->checkSuperAdmin();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'bot_token' => 'required|string',
                'chat_id' => 'required|string',
                'tenant_id' => 'nullable|string|unique:public.telegram_configs,tenant_id',
            ]);

            $validated['is_active'] = $request->has('is_active');

            TelegramConfig::create($validated);

            return back()->with('success', 'Telegram Bot configuration added.');
        }

        public function update(Request $request, $id)
        {
            $this->checkSuperAdmin();
            $config = TelegramConfig::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'bot_token' => 'required|string',
                'chat_id' => 'required|string',
                'tenant_id' => ['nullable', 'string', Rule::unique('public.telegram_configs', 'tenant_id')->ignore($config->id)],
            ]);

            $validated['is_active'] = $request->has('is_active');
            $config->update($validated);

            return back()->with('success', 'Configuration updated.');
        }

        public function destroy($id)
        {
            $this->checkSuperAdmin();
            TelegramConfig::destroy($id);
            return back()->with('success', 'Configuration deleted.');
        }

        public function test($id)
        {
            $this->checkSuperAdmin();
            $config = TelegramConfig::findOrFail($id);

            return $this->performSend($config, "⚡ *TEST*\nConnection successful for bot: _{$config->name}_");
        }

        // НОВЫЙ МЕТОД: Ручная отправка сообщения
        public function sendMessage(Request $request)
        {
            $this->checkSuperAdmin();
            
            $validated = $request->validate([
                'config_id' => 'required|exists:public.telegram_configs,id',
                'message' => 'required|string|min:2',
            ]);

            $config = TelegramConfig::findOrFail($validated['config_id']);
            
            return $this->performSend($config, $validated['message']);
        }

        // Приватный хелпер для отправки, чтобы не дублировать код
        private function performSend($config, $text)
        {
            try {
                $response = Http::post("https://api.telegram.org/bot{$config->bot_token}/sendMessage", [
                    'chat_id' => $config->chat_id,
                    'text' => $text,
                    'parse_mode' => 'Markdown',
                ]);

                if ($response->successful()) {
                    return back()->with('success', 'Message sent successfully via Telegram!');
                } else {
                    return back()->with('error', 'Telegram API Error: ' . $response->body());
                }
            } catch (\Exception $e) {
                return back()->with('error', 'Connection failed: ' . $e->getMessage());
            }
        }
    }