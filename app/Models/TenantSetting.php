<?php
// FILE: app/Models/TenantSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TenantSetting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    // Убрали кастинг, будем обрабатывать вручную
    // protected $casts = [
    //     'value' => 'array',
    // ];

    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            Log::debug("TenantSetting::get - Key not found", ['key' => $key]);
            return $default;
        }
        
        // Если значение уже массив - возвращаем
        if (is_array($setting->value)) {
            return $setting->value;
        }
        
        // Если это строка JSON - декодируем
        if (is_string($setting->value)) {
            $decoded = json_decode($setting->value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        
        // Если что-то другое
        Log::warning("TenantSetting::get - Unexpected value type", [
            'key' => $key,
            'value' => $setting->value,
            'type' => gettype($setting->value)
        ]);
        
        return $default;
    }

    public static function set(string $key, $value, string $group = 'general')
    {
        // Преобразуем значение в JSON строку
        $jsonValue = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
        
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $jsonValue, 'group' => $group]
        );
    }

    public static function getContentBlocks(string $tenantId)
    {
        $key = "content_blocks_{$tenantId}";
        return self::get($key, []);
    }

    public static function getContentBlock(string $tenantId, string $blockSlug, $default = null)
    {
        $blocks = self::getContentBlocks($tenantId);
        
        foreach ($blocks as $block) {
            if (isset($block['slug']) && $block['slug'] === $blockSlug) {
                return $block;
            }
        }
        
        return $default;
    }

    public static function deleteContent(string $tenantId, array $contentIds = [])
    {
        $key = "content_blocks_{$tenantId}";
        $setting = self::where('key', $key)->first();
        
        if (!$setting || empty($setting->value)) {
            return false;
        }

        $blocks = is_array($setting->value) ? $setting->value : json_decode($setting->value, true);
        $filteredBlocks = [];
        $deletedFiles = [];

        foreach ($blocks as $block) {
            if (in_array($block['id'] ?? null, $contentIds)) {
                // Удаляем файл если есть
                if (isset($block['path']) && $block['path']) {
                    if (Storage::disk('public')->exists($block['path'])) {
                        Storage::disk('public')->delete($block['path']);
                        $deletedFiles[] = $block['path'];
                    }
                }
                continue;
            }
            $filteredBlocks[] = $block;
        }

        // Сохраняем обратно
        $setting->value = json_encode($filteredBlocks, JSON_UNESCAPED_UNICODE);
        $setting->save();

        return [
            'deleted_count' => count($contentIds),
            'remaining_blocks' => count($filteredBlocks),
            'deleted_files' => $deletedFiles
        ];
    }
}