<?php
// FILE: app/Http\Controllers\Admin\SettingsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TelegramConfig;
use App\Models\TenantSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $telegramConfigs = TelegramConfig::all();
        $tenants = config('tenants.tenants');
        return view('admin.settings.index', compact('telegramConfigs', 'tenants'));
    }

    public function content(Request $request)
    {
        $this->checkSuperAdmin();
        
        $tenants = config('tenants.tenants');
        $activeTenant = $request->query('tenant', session('admin_current_tenant_id', array_key_first($tenants)));
        
        if (!isset($tenants[$activeTenant])) {
            $activeTenant = array_key_first($tenants);
        }

        // Сохраняем выбранный магазин в сессии
        session(['admin_current_tenant_id' => $activeTenant]);

        $contentBlocks = TenantSetting::getContentBlocks($activeTenant);
        
        // Генерируем ID и slug для блоков, у которых их нет
        foreach ($contentBlocks as &$block) {
            if (!isset($block['id'])) {
                $block['id'] = uniqid('block_', true);
            }
            if (!isset($block['slug'])) {
                // Генерируем slug из заголовка или типа
                $title = $block['title'] ?? ($block['content'] ?? '');
                if (!empty($title)) {
                    $block['slug'] = Str::slug(Str::limit($title, 50));
                } else {
                    $block['slug'] = $block['type'] . '_' . substr($block['id'], -8);
                }
            }
        }

        return view('admin.settings.content', compact('tenants', 'activeTenant', 'contentBlocks'));
    }

    public function updateContent(Request $request)
    {
        $this->checkSuperAdmin();
        
        $tenantId = $request->tenant_id;
        $blocks = $request->input('blocks', []);
        $files = $request->file('blocks', []);

        $finalBlocks = [];

        foreach ($blocks as $index => $block) {
            $type = $block['type'];
            $slug = trim($block['slug'] ?? '');
            
            // Если slug пустой - пропускаем
            if (empty($slug)) {
                continue;
            }
            
            // Генерируем ID если нет
            $blockId = $block['id'] ?? uniqid('block_', true);
            
            $newBlock = [
                'id' => $blockId,
                'slug' => $slug,
                'type' => $type,
                'created_at' => $block['created_at'] ?? now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString()
            ];

            if ($type === 'text') {
                $newBlock['content'] = $block['content'] ?? '';
                $newBlock['title'] = $block['title'] ?? '';
            } 
            elseif ($type === 'image' || $type === 'video') {
                if (isset($files[$index]['file'])) {
                    $file = $files[$index]['file'];
                    $path = $file->store("tenants/{$tenantId}/media", 'public');
                    $newBlock['path'] = $path;
                    $newBlock['original_name'] = $file->getClientOriginalName();
                    $newBlock['mime_type'] = $file->getMimeType();
                    $newBlock['size'] = $file->getSize();
                } else {
                    $newBlock['path'] = $block['old_path'] ?? null;
                    if (isset($block['original_name'])) {
                        $newBlock['original_name'] = $block['original_name'];
                    }
                }
                $newBlock['title'] = $block['title'] ?? '';
                $newBlock['alt'] = $block['alt'] ?? '';
            }

            $finalBlocks[] = $newBlock;
        }

        TenantSetting::set("content_blocks_{$tenantId}", $finalBlocks, 'content');

        return back()->with('success', 'Content updated successfully for ' . $tenantId);
    }

    public function deleteContent(Request $request)
    {
        $this->checkSuperAdmin();
        
        $request->validate([
            'tenant_id' => 'required|string',
            'content_ids' => 'required|array',
            'content_ids.*' => 'string'
        ]);

        $result = TenantSetting::deleteContent(
            $request->tenant_id,
            $request->content_ids
        );

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Content deleted successfully',
                'data' => $result
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to delete content'
        ], 400);
    }

    public function update(Request $request)
    {
        $this->checkSuperAdmin();
        return back()->with('success', 'General settings updated.');
    }
}