@extends('layouts.admin')
@section('title', 'Shop Content Management')

@section('content')
<div class="max-w-5xl mx-auto" x-data="contentBuilder()" x-init="init()">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Store Content Customizer</h1>
        
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-gray-500">Managing:</span>
            <select class="border p-2 rounded bg-white shadow-sm font-bold text-blue-600" 
                    id="tenant-selector"
                    onchange="if (window.unsavedChanges) {
                        if (!confirm('You have unsaved changes. If you switch tenants, you will lose these changes. Continue?')) {
                            this.value = '{{ $activeTenant }}';
                            return;
                        }
                    }
                    window.location.href = '?tenant=' + this.value + '&_=' + Date.now();">
                @foreach($tenants as $id => $data)
                    <option value="{{ $id }}" {{ $activeTenant == $id ? 'selected' : '' }}>
                        {{ $data['name'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Панель импорта/экспорта -->
    <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 rounded-lg">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <h3 class="font-bold text-blue-800">Import & Export Content</h3>
                <p class="text-sm text-blue-600">Backup, restore or migrate content between stores</p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <!-- Форма импорта -->
                <form action="{{ route('admin.settings.content.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="tenant_id" value="{{ $activeTenant }}">
                    
                    <select name="import_mode" class="border rounded px-3 py-2 text-sm bg-white">
                        <option value="merge">Merge with existing</option>
                        <option value="replace">Replace all content</option>
                        <option value="update">Update existing only</option>
                    </select>
                    
                    <input type="file" name="import_file" accept=".json,.csv" 
                           class="text-sm border rounded p-2" required>
                    
                    <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 font-bold text-sm">
                        📥 Import
                    </button>
                </form>
                
                <!-- Форма экспорта -->
                <form action="{{ route('admin.settings.content.export') }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="tenant_id" value="{{ $activeTenant }}">
                    
                    <select name="format" class="border rounded px-3 py-2 text-sm bg-white">
                        <option value="json">JSON Format</option>
                        <option value="csv">CSV Format (text only)</option>
                    </select>
                    
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 font-bold text-sm">
                        📤 Export
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Информация о текущих блоках -->
        <div class="mt-4 pt-4 border-t border-blue-200 text-sm text-blue-700">
            <div class="flex gap-6">
                <div class="flex items-center gap-2">
                    <span class="font-bold">{{ count($contentBlocks) }}</span>
                    <span>content blocks</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-bold">{{ count(array_filter($contentBlocks, fn($b) => $b['type'] === 'text')) }}</span>
                    <span>text blocks</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-bold">{{ count(array_filter($contentBlocks, fn($b) => $b['type'] === 'image')) }}</span>
                    <span>images</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-bold">{{ count(array_filter($contentBlocks, fn($b) => $b['type'] === 'video')) }}</span>
                    <span>videos</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Кнопки массового удаления -->
    <div x-show="selectedBlocks.length > 0" 
         class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex justify-between items-center">
            <div>
                <span class="font-bold text-red-700">
                    <span x-text="selectedBlocks.length"></span> blocks selected
                </span>
                <p class="text-sm text-red-600">Selected blocks will be permanently deleted</p>
            </div>
            <button type="button" 
                    @click="deleteSelectedBlocks()"
                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 font-bold">
                Delete Selected
            </button>
        </div>
    </div>

    <form action="{{ route('admin.settings.content.update') }}" method="POST" enctype="multipart/form-data" id="contentForm">
        @csrf
        <input type="hidden" name="tenant_id" value="{{ $activeTenant }}">

        <div class="space-y-4 mb-8">
            <template x-for="(block, index) in blocks" :key="block.id">
                <div class="bg-white border rounded-lg shadow-sm p-4 relative group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" 
                                   x-model="selectedBlocks" 
                                   :value="block.id"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-xs font-bold uppercase" 
                                  x-text="block.type"></span>
                            <span class="text-gray-400 font-mono text-xs">#<span x-text="index + 1"></span></span>
                            
                            <!-- Поле уникального идентификатора -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Identifier</label>
                                <input type="text" 
                                       :name="`blocks[${index}][slug]`" 
                                       x-model="block.slug"
                                       class="w-32 border rounded px-2 py-1 text-xs font-mono bg-gray-50"
                                       placeholder="e.g., header"
                                       required>
                            </div>
                        </div>
                        <button type="button" @click="removeBlock(index)" class="text-red-400 hover:text-red-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>

                    <!-- Скрытые поля -->
                    <input type="hidden" :name="`blocks[${index}][id]`" :value="block.id">
                    <input type="hidden" :name="`blocks[${index}][type]`" :value="block.type">
                    <template x-if="block.created_at">
                        <input type="hidden" :name="`blocks[${index}][created_at]`" :value="block.created_at">
                    </template>

                    <!-- Поля для ТЕКСТА -->
                    <div x-show="block.type === 'text'">
                        <textarea :name="`blocks[${index}][content]`" x-model="block.content" 
                                  rows="4" class="w-full border rounded p-3 focus:ring-2 focus:ring-blue-100 outline-none" 
                                  placeholder="Enter text content..."></textarea>
                        <div class="mt-2">
                            <input type="text" :name="`blocks[${index}][title]`" x-model="block.title"
                                   class="w-full border rounded p-2 text-sm" 
                                   placeholder="Optional title...">
                        </div>
                    </div>

                    <!-- Поля для ФОТО / ВИДЕО -->
                    <div x-show="block.type === 'image' || block.type === 'video'">
                        <input type="hidden" :name="`blocks[${index}][old_path]`" x-model="block.path">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Upload New File</label>
                                <input type="file" :name="`blocks[${index}][file]`" class="w-full text-sm">
                                <p class="text-[10px] text-gray-400 mt-1">Leave empty to keep current file</p>
                                
                                <div class="mt-4 space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 mb-1">Title</label>
                                        <input type="text" :name="`blocks[${index}][title]`" x-model="block.title" 
                                               class="w-full border rounded p-2 text-sm" placeholder="Optional title...">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 mb-1">Alt Text</label>
                                        <input type="text" :name="`blocks[${index}][alt]`" x-model="block.alt"
                                               class="w-full border rounded p-2 text-sm" placeholder="Alt text for accessibility...">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 rounded border border-dashed flex items-center justify-center overflow-hidden min-h-[120px]">
                                <template x-if="block.path">
                                    <div class="w-full">
                                        <template x-if="block.type === 'image'">
                                            <img :src="'/storage/' + block.path" class="max-h-32 mx-auto object-contain">
                                        </template>
                                        <template x-if="block.type === 'video'">
                                            <div class="text-center p-4">
                                                <span class="text-3xl">🎥</span>
                                                <p class="text-[10px] text-blue-500 truncate" x-text="block.path"></p>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!block.path">
                                    <span class="text-gray-300 italic text-sm">No file uploaded</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Кнопки добавления -->
        <div class="flex flex-wrap gap-3 p-4 bg-blue-50 rounded-lg border border-blue-100 mb-8">
            <button type="button" @click="addBlock('text')" class="flex items-center gap-2 bg-white border border-blue-200 px-4 py-2 rounded shadow-sm hover:bg-blue-100 transition text-sm font-bold">
                <span>➕</span> Add Text Block
            </button>
            <button type="button" @click="addBlock('image')" class="flex items-center gap-2 bg-white border border-blue-200 px-4 py-2 rounded shadow-sm hover:bg-blue-100 transition text-sm font-bold">
                <span>🖼️</span> Add Photo
            </button>
            <button type="button" @click="addBlock('video')" class="flex items-center gap-2 bg-white border border-blue-200 px-4 py-2 rounded shadow-sm hover:bg-blue-100 transition text-sm font-bold">
                <span>🎥</span> Add Video
            </button>
        </div>

        <div class="sticky bottom-6 bg-white p-4 border rounded-xl shadow-2xl flex justify-between items-center">
            <p class="text-sm text-gray-500 italic">Changes must be saved to take effect.</p>
            <div class="flex gap-3">
                <a href="{{ route('admin.settings.content') }}?tenant={{ $activeTenant }}&_={{ time() }}" 
                   class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-bold hover:bg-gray-300 transition">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-10 py-3 rounded-lg font-bold hover:bg-blue-700 shadow-lg transition transform active:scale-95">
                    Save All Changes
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function contentBuilder() {
    return {
        blocks: @json($contentBlocks),
        selectedBlocks: [],
        originalBlocks: JSON.parse(JSON.stringify(@json($contentBlocks))),
        hasUnsavedChanges: false,
        
        init() {
            // Следим за изменениями в блоках
            this.$watch('blocks', (newBlocks) => {
                this.checkForChanges();
            }, { deep: true });
            
            // Инициализируем глобальную переменную для проверки несохраненных изменений
            window.unsavedChanges = this.hasUnsavedChanges;
            this.$watch('hasUnsavedChanges', (value) => {
                window.unsavedChanges = value;
            });
            
            // Предотвращаем случайный уход со страницы при несохраненных изменениях
            window.addEventListener('beforeunload', (e) => {
                if (this.hasUnsavedChanges) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                    return e.returnValue;
                }
            });
        },
        
        checkForChanges() {
            const currentBlocks = JSON.stringify(this.blocks);
            const originalBlocks = JSON.stringify(this.originalBlocks);
            this.hasUnsavedChanges = currentBlocks !== originalBlocks;
        },
        
        addBlock(type) {
            const id = 'block_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            const defaultSlug = type + '_' + Date.now();
            
            if (type === 'text') {
                this.blocks.push({ 
                    id: id,
                    slug: defaultSlug,
                    type: 'text', 
                    title: '',
                    content: '',
                    created_at: new Date().toISOString().slice(0, 19).replace('T', ' ')
                });
            } else {
                this.blocks.push({ 
                    id: id,
                    slug: defaultSlug,
                    type: type, 
                    title: '',
                    path: null, 
                    alt: '',
                    created_at: new Date().toISOString().slice(0, 19).replace('T', ' ')
                });
            }
        },
        
        removeBlock(index) {
            if (confirm('Remove this block?')) {
                // Удаляем блок из selectedBlocks если он там есть
                const blockId = this.blocks[index].id;
                this.selectedBlocks = this.selectedBlocks.filter(id => id !== blockId);
                
                this.blocks.splice(index, 1);
            }
        },
        
        async deleteSelectedBlocks() {
            if (!this.selectedBlocks.length) return;
            
            if (!confirm(`Are you sure you want to delete ${this.selectedBlocks.length} blocks? This action cannot be undone.`)) {
                return;
            }
            
            try {
                const response = await fetch('{{ route("admin.settings.content.delete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        tenant_id: '{{ $activeTenant }}',
                        content_ids: this.selectedBlocks
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Удаляем блоки локально
                    this.blocks = this.blocks.filter(block => !this.selectedBlocks.includes(block.id));
                    this.originalBlocks = JSON.parse(JSON.stringify(this.blocks));
                    this.selectedBlocks = [];
                    this.hasUnsavedChanges = false;
                    
                    alert(result.message);
                    
                    // Перезагружаем страницу для полной синхронизации
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Delete error:', error);
                alert('An error occurred while deleting blocks');
            }
        }
    }
}
</script>
@endsection