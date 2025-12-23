<?php
// FILE: app/Http/Controllers/Admin/SettingsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TelegramConfig; // Импортируем модель телеграма

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

        // Данные для Telegram вкладки
        $telegramConfigs = TelegramConfig::all();
        $tenants = config('tenants.tenants');

        return view('admin.settings.index', compact('telegramConfigs', 'tenants'));
    }

    // Метод для сохранения ОБЩИХ настроек (заглушка для примера)
    public function update(Request $request)
    {
        $this->checkSuperAdmin();
        // Здесь логика сохранения названия сайта, логотипа и т.д.
        // ...
        return back()->with('success', 'General settings updated.');
    }
}