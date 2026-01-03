@extends('layouts.admin')
@section('title', 'Shop Content Management')

@section('content')
<div class="max-w-5xl mx-auto" x-data="contentBuilder()">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Store Content Customizer</h1>
        
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-gray-500">Managing:</span>
            <select class="border p-2 rounded bg-white shadow-sm font-bold text-blue-600" 
                    @change="window.location.href = '?tenant=' + $event.target.value">
                @foreach($tenants as $id => $data)
                    <option value="{{ $id }}" {{ $activeTenant == $id ? 'selected' : '' }}>
                        {{ $data['name'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Инструкция по использованию slug -->
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <h3 class="font-bold text-blue-800 mb-2">How to use unique identifiers:</h3>
        <ul class="text-sm text-blue-700 space-y-1">
            <li>• Each block needs a <strong>unique identifier</strong> (slug) to display it on store pages</li>
            <li>• Use lowercase letters, numbers, and hyphens (e.g., "hero-section", "about-us", "promo-banner")</li>
            <li>• To display a block in your store template use: <code class="bg-blue-100 px-2 py-1 rounded">@{{ '{' }}{{ '{' }} getContentBlock('your-slug') {{ '}' }}{{ '}' }}</code></li>
        </ul>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <h3 class="font-bold text-red-800 mb-2">Validation Errors:</h3>
            <ul class="text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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

        <div class="space-y-6 mb-8">
            <template x-for="(block, index) in blocks" :key="block.id">
                <div class="bg-white border rounded-lg shadow-sm p-6 relative group hover:border-blue-300 transition"
                     :class="{ 'border-red-300 bg-red-50': selectedBlocks.includes(block.id) }">
                    
                    <!-- Заголовок блока -->
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" 
                                   x-model="selectedBlocks" 
                                   :value="block.id"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-xs font-bold uppercase" 
                                  x-text="block.type"></span>
                            
                            <!-- Поле уникального идентификатора -->
                            <div class="relative">
                                <label class="block text-xs font-bold text-gray-500 mb-1">Unique Identifier:</label>
                                <input type="text" 
                                       :name="`blocks[${index}][slug]`" 
                                       x-model="block.slug"
                                       class="w-48 border rounded px-3 py-1 text-sm font-mono bg-gray-50 focus:bg-white"
                                       placeholder="e.g., hero-section"
                                       required
                                       pattern="[a-z0-9\-]+"
                                       title="Use lowercase letters, numbers, and hyphens only">
                                <div class="absolute right-2 top-7 text-gray-400" title="Copy code for template">
                                    <button type="button" @click="copyToClipboard(block.slug)" class="hover:text-blue-600">
                                        📋
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex gap-2">
                            <button type="button" 
                                    @click="removeBlock(index)" 
                                    class="text-red-400 hover:text-red-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Скрытые поля -->
                    <input type="hidden" :name="`blocks[${index}][id]`" :value="block.id">
                    <input type="hidden" :name="`blocks[${index}][type]`" :value="block.type">
                    <template x-if="block.created_at">
                        <input type="hidden" :name="`blocks[${index}][created_at]`" :value="block.created_at">
                    </template>

                    <!-- Текстовый блок -->
                    <div x-show="block.type === 'text'" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Title (optional)</label>
                            <input type="text" 
                                   :name="`blocks[${index}][title]`" 
                                   x-model="block.title"
                                   class="w-full border rounded p-3 text-sm" 
                                   placeholder="Block title...">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Content</label>
                            <textarea :name="`blocks[${index}][content]`" 
                                      x-model="block.content" 
                                      rows="6" 
                                      class="w-full border rounded p-3 focus:ring-2 focus:ring-blue-100 outline-none" 
                                      placeholder="Enter text content..."></textarea>
                        </div>
                    </div>

                    <!-- Медиа блок (изображение/видео) -->
                    <div x-show="block.type === 'image' || block.type === 'video'" class="space-y-4">
                        <input type="hidden" :name="`blocks[${index}][old_path]`" :value="block.path">
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Upload New File</label>
                                    <input type="file" 
                                           :name="`blocks[${index}][file]`" 
                                           class="w-full text-sm border rounded p-2"
                                           @change="block.original_name = $event.target.files[0]?.name">
                                    
                                    <p class="text-[10px] text-gray-400 mt-1">Leave empty to keep current file</p>
                                </div>
                                
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 mb-1">Title</label>
                                        <input type="text" 
                                               :name="`blocks[${index}][title]`" 
                                               x-model="block.title"
                                               class="w-full border rounded p-2 text-sm" 
                                               placeholder="Optional title...">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 mb-1">Alt Text</label>
                                        <input type="text" 
                                               :name="`blocks[${index}][alt]`" 
                                               x-model="block.alt"
                                               class="w-full border rounded p-2 text-sm" 
                                               placeholder="Alt text for accessibility...">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Предпросмотр -->
                            <div class="bg-gray-50 rounded-lg border border-dashed p-4 flex flex-col items-center justify-center min-h-[200px]">
                                <template x-if="block.path">
                                    <div class="w-full text-center">
                                        <template x-if="block.type === 'image'">
                                            <div>
                                                <img :src="'/storage/' + block.path" 
                                                     :alt="block.alt || block.title"
                                                     class="max-h-40 mx-auto object-contain mb-3 rounded">
                                                <p class="text-xs text-gray-500 truncate" 
                                                   x-text="block.original_name || block.path.split('/').pop()"></p>
                                                <template x-if="block.size">
                                                    <p class="text-xs text-gray-400" 
                                                       x-text="formatFileSize(block.size)"></p>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="block.type === 'video'">
                                            <div class="text-center">
                                                <div class="text-4xl mb-3">🎥</div>
                                                <p class="text-xs text-blue-500 truncate" 
                                                   x-text="block.original_name || block.path.split('/').pop()"></p>
                                                <template x-if="block.size">
                                                    <p class="text-xs text-gray-400" 
                                                       x-text="formatFileSize(block.size)"></p>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!block.path">
                                    <div class="text-center">
                                        <span class="text-4xl text-gray-300">📁</span>
                                        <p class="text-gray-400 italic text-sm mt-3">No file uploaded</p>
                                    </div>
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

        <!-- Панель сохранения -->
        <div class="sticky bottom-6 bg-white p-4 border rounded-xl shadow-2xl flex justify-between items-center">
            <div class="text-sm text-gray-500">
                <span class="font-bold" x-text="blocks.length"></span> blocks
                <span x-show="selectedBlocks.length > 0" class="ml-3 text-red-600">
                    (<span x-text="selectedBlocks.length"></span> selected for deletion)
                </span>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-10 py-3 rounded-lg font-bold hover:bg-blue-700 shadow-lg transition transform active:scale-95">
                Save All Changes
            </button>
        </div>
    </form>
</div>

<script>
function contentBuilder() {
    return {
        blocks: @json($contentBlocks),
        selectedBlocks: [],
        
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
                this.blocks.splice(index, 1);
                const blockId = this.blocks[index]?.id;
                if (blockId) {
                    this.selectedBlocks = this.selectedBlocks.filter(id => id !== blockId);
                }
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
                    this.blocks = this.blocks.filter(block => !this.selectedBlocks.includes(block.id));
                    this.selectedBlocks = [];
                    alert(result.message);
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Delete error:', error);
                alert('An error occurred while deleting blocks');
            }
        },
        
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        },
        
        formatFileSize(bytes) {
            if (!bytes) return '';
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },
        
        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Copied: ' + text);
            });
        }
    }
}
</script>
@endsection