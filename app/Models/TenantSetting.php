<?php
// FILE: app/Models/TenantSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TenantSetting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

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

    /**
     * Экспортировать все блоки контента для магазина
     */
    public static function exportContent(string $tenantId)
    {
        $blocks = self::getContentBlocks($tenantId);
        
        // Добавляем мета-информацию
        $exportData = [
            'export_info' => [
                'tenant_id' => $tenantId,
                'export_date' => now()->toDateTimeString(),
                'total_blocks' => count($blocks),
                'version' => '1.0'
            ],
            'blocks' => $blocks
        ];
        
        return $exportData;
    }

    /**
     * Импортировать блоки контента для магазина
     */
    public static function importContent(string $tenantId, array $importData, string $mode = 'merge')
    {
        $existingBlocks = self::getContentBlocks($tenantId);
        $importBlocks = $importData['blocks'] ?? [];
        
        $results = [
            'total_imported' => 0,
            'new_blocks' => 0,
            'updated_blocks' => 0,
            'skipped_blocks' => 0,
            'errors' => []
        ];
        
        if (empty($importBlocks)) {
            $results['errors'][] = 'No blocks found in import data';
            return $results;
        }
        
        $finalBlocks = [];
        
        // Если режим replace - очищаем существующие
        if ($mode === 'replace') {
            $finalBlocks = [];
            $existingBlocks = [];
        } else {
            // merge или update режимы
            $finalBlocks = $existingBlocks;
        }
        
        // Создаем карту существующих блоков по slug для быстрого поиска
        $existingBlocksMap = [];
        foreach ($existingBlocks as $index => $block) {
            if (isset($block['slug'])) {
                $existingBlocksMap[$block['slug']] = $index;
            }
        }
        
        foreach ($importBlocks as $importBlock) {
            // Проверяем обязательные поля
            if (!isset($importBlock['slug']) || !isset($importBlock['type'])) {
                $results['errors'][] = "Block missing required fields (slug or type): " . json_encode($importBlock);
                $results['skipped_blocks']++;
                continue;
            }
            
            $slug = $importBlock['slug'];
            
            // Генерируем новый ID для импортируемого блока
            $newBlock = $importBlock;
            $newBlock['id'] = uniqid('imported_', true);
            $newBlock['updated_at'] = now()->toDateTimeString();
            
            // Если блока еще не было, устанавливаем дату создания
            if (!isset($newBlock['created_at'])) {
                $newBlock['created_at'] = now()->toDateTimeString();
            }
            
            // Проверяем, существует ли уже блок с таким slug
            if (isset($existingBlocksMap[$slug])) {
                // Обновляем существующий блок
                $existingIndex = $existingBlocksMap[$slug];
                
                // Сохраняем ID существующего блока, если он есть
                if (isset($finalBlocks[$existingIndex]['id'])) {
                    $newBlock['id'] = $finalBlocks[$existingIndex]['id'];
                }
                
                // Сохраняем путь к файлу, если в импорте его нет
                if ($mode === 'update' && isset($finalBlocks[$existingIndex]['path']) && !isset($newBlock['path'])) {
                    $newBlock['path'] = $finalBlocks[$existingIndex]['path'];
                }
                
                // Сохраняем дату создания
                if (isset($finalBlocks[$existingIndex]['created_at'])) {
                    $newBlock['created_at'] = $finalBlocks[$existingIndex]['created_at'];
                }
                
                $finalBlocks[$existingIndex] = $newBlock;
                $results['updated_blocks']++;
            } else {
                // Добавляем новый блок
                $finalBlocks[] = $newBlock;
                $results['new_blocks']++;
            }
            
            $results['total_imported']++;
        }
        
        // Сохраняем финальный результат
        self::set("content_blocks_{$tenantId}", $finalBlocks, 'content');
        
        return $results;
    }
}