<?php
// FILE: app/Helpers/ContentHelper.php

namespace App\Helpers;

use App\Models\TenantSetting;
use App\Services\TenantService;
use Illuminate\Support\Facades\Storage;

class ContentHelper
{
    /**
     * Получить блок контента по slug
     */
    public static function getBlock(string $slug, $tenantId = null)
    {
        $tenantId = $tenantId ?? self::getTenantId();
        return TenantSetting::getContentBlock($tenantId, $slug);
    }

    /**
     * Получить ID текущего магазина
     */
    private static function getTenantId()
    {
        // Сначала пытаемся получить через TenantService
        try {
            $tenantId = TenantService::getStaticCurrentTenantId();
            if ($tenantId) {
                return $tenantId;
            }
        } catch (\Exception $e) {
            // Если не сработало, продолжаем
        }
        
        // Определяем по поддомену
        $host = request()->getHost();
        $subdomain = explode('.', $host)[0];
        
        // Маппинг поддоменов на tenantId
        $tenantMap = [
            'designer' => 'designer_hub',
            'style' => 'style_mart',
            'urban' => 'urban_outfitters',
        ];
        
        return $tenantMap[$subdomain] ?? $subdomain;
    }

    /**
     * Проверить, существует ли блок
     */
    public static function has(string $slug, $tenantId = null)
    {
        return !empty(self::getBlock($slug, $tenantId));
    }

    /**
     * Получить значение поля блока
     */
    public static function field(string $slug, string $field, $default = null, $tenantId = null)
    {
        $block = self::getBlock($slug, $tenantId);
        return $block[$field] ?? $default;
    }

    /**
     * Получить заголовок блока
     */
    public static function title(string $slug, $default = '', $tenantId = null)
    {
        return self::field($slug, 'title', $default, $tenantId);
    }

    /**
     * Получить содержимое текстового блока
     */
    public static function text(string $slug, $default = '', $tenantId = null)
    {
        $block = self::getBlock($slug, $tenantId);
        
        if ($block && $block['type'] === 'text') {
            return $block['content'] ?? $default;
        }
        
        return $default;
    }

    /**
     * Получить URL изображения
     */
    public static function imageUrl(string $slug, $tenantId = null)
    {
        $block = self::getBlock($slug, $tenantId);
        
        if ($block && $block['type'] === 'image' && !empty($block['path'])) {
            return Storage::url($block['path']);
        }
        
        return '';
    }

    /**
     * Получить alt текст изображения
     */
    public static function imageAlt(string $slug, $default = '', $tenantId = null)
    {
        $block = self::getBlock($slug, $tenantId);
        
        if ($block && $block['type'] === 'image') {
            return $block['alt'] ?? $block['title'] ?? $default;
        }
        
        return $default;
    }

    /**
     * Получить путь к изображению
     */
    public static function imagePath(string $slug, $tenantId = null)
    {
        $block = self::getBlock($slug, $tenantId);
        
        if ($block && $block['type'] === 'image') {
            return $block['path'] ?? '';
        }
        
        return '';
    }

    /**
     * Получить URL видео
     */
    public static function videoUrl(string $slug, $tenantId = null)
    {
        $block = self::getBlock($slug, $tenantId);
        
        if ($block && $block['type'] === 'video' && !empty($block['path'])) {
            return Storage::url($block['path']);
        }
        
        return '';
    }

    // =================== МЕТОДЫ ВЫВОДА ===================

    /**
     * Вывести текстовый блок
     */
    public static function renderText(string $slug, $tenantId = null)
    {
        $block = self::getBlock($slug, $tenantId);
        
        if (!$block || $block['type'] !== 'text') {
            return '';
        }

        $content = $block['content'] ?? '';
        $title = $block['title'] ?? '';
        
        if (empty($content)) {
            return '';
        }

        $html = '<div class="content-block text-block">';
        if (!empty($title)) {
            $html .= '<h2 class="text-2xl font-bold mb-4">' . e($title) . '</h2>';
        }
        $html .= '<div class="prose max-w-none">' . nl2br(e($content)) . '</div>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Вывести изображение
     */
    public static function renderImage(string $slug, $tenantId = null)
    {
        $block = self::getBlock($slug, $tenantId);
        
        if (!$block || $block['type'] !== 'image' || empty($block['path'])) {
            return '';
        }

        $url = Storage::url($block['path']);
        $title = $block['title'] ?? '';
        $alt = $block['alt'] ?? $title ?? '';
        
        $html = '<div class="content-block image-block">';
        $html .= '<figure class="relative">';
        $html .= '<img src="' . e($url) . '" alt="' . e($alt) . '" title="' . e($title) . '" class="w-full rounded-lg shadow-md">';
        if (!empty($title)) {
            $html .= '<figcaption class="mt-2 text-center text-gray-600 text-sm italic">' . e($title) . '</figcaption>';
        }
        $html .= '</figure>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Вывести тег изображения без обертки
     */
    public static function imageTag(string $slug, array $attributes = [], $tenantId = null)
    {
        $url = self::imageUrl($slug, $tenantId);
        
        if (empty($url)) {
            return '';
        }

        $alt = self::imageAlt($slug, '', $tenantId);
        $title = self::title($slug, '', $tenantId);
        
        $attrString = '';
        $defaultAttributes = [
            'src' => $url,
            'alt' => $alt,
            'title' => $title,
            'loading' => 'lazy'
        ];
        
        $allAttributes = array_merge($defaultAttributes, $attributes);
        
        foreach ($allAttributes as $key => $value) {
            if ($value !== null) {
                $attrString .= ' ' . $key . '="' . e($value) . '"';
            }
        }
        
        return '<img' . $attrString . '>';
    }

    /**
     * Вывести видео
     */
    public static function renderVideo(string $slug, $tenantId = null)
    {
        $block = self::getBlock($slug, $tenantId);
        
        if (!$block || $block['type'] !== 'video' || empty($block['path'])) {
            return '';
        }

        $url = Storage::url($block['path']);
        $title = $block['title'] ?? '';
        $mime_type = $block['mime_type'] ?? 'video/mp4';
        
        $html = '<div class="content-block video-block">';
        $html .= '<div class="relative pb-[56.25%] h-0 rounded-lg overflow-hidden shadow-md">';
        $html .= '<video controls class="absolute top-0 left-0 w-full h-full object-cover" title="' . e($title) . '">';
        $html .= '<source src="' . e($url) . '" type="' . e($mime_type) . '">';
        $html .= 'Your browser does not support the video tag.';
        $html .= '</video>';
        $html .= '</div>';
        if (!empty($title)) {
            $html .= '<p class="mt-2 text-center text-gray-700 font-medium">' . e($title) . '</p>';
        }
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Универсальный вывод (автоопределение типа)
     */
    public static function render(string $slug, $tenantId = null)
    {
        $block = self::getBlock($slug, $tenantId);
        
        if (!$block) {
            return '';
        }

        switch ($block['type']) {
            case 'text':
                return self::renderText($slug, $tenantId);
            case 'image':
                return self::renderImage($slug, $tenantId);
            case 'video':
                return self::renderVideo($slug, $tenantId);
            default:
                return '';
        }
    }

    /**
     * Получить все блоки определенного типа
     */
    public static function all($type = null, $tenantId = null)
    {
        $tenantId = $tenantId ?? self::getTenantId();
        $blocks = TenantSetting::getContentBlocks($tenantId);
        
        if ($type) {
            return array_filter($blocks, function($block) use ($type) {
                return ($block['type'] ?? '') === $type;
            });
        }
        
        return $blocks;
    }
}