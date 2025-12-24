<!-- FILE: resources/views/layouts/admin.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 text-white flex flex-col flex-shrink-0">
            <div class="p-6 text-2xl font-bold bg-gray-900 border-b border-gray-700">
                Admin Panel
                <div class="text-xs font-normal text-gray-400 mt-1">
                    {{ request()->session()->get('is_super_admin') ? 'Super Admin' : 'Manager' }}
                </div>
                <div class="text-xs font-normal text-yellow-400 mt-1 uppercase">
                    {{ app(\App\Services\TenantService::class)->getCurrentTenantId() ?? 'Global' }}
                </div>
            </div>
            
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700 text-yellow-400' : '' }}">
                   Dashboard
                </a>

                <!-- Orders -->
                <a href="{{ route('admin.orders.index') }}" 
                   class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.orders*') ? 'bg-gray-700 text-yellow-400' : '' }}">
                   Orders
                </a>

                <!-- Products -->
                <a href="{{ route('admin.products.index') }}" 
                   class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.products*') ? 'bg-gray-700 text-yellow-400' : '' }}">
                   Products
                </a>

                <a href="{{ route('admin.inventory.index') }}" 
                    class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.inventory*') ? 'bg-gray-700 text-yellow-400' : '' }}">
                    Inventory Report
                </a>

                <!-- Classification Group -->
                <div class="pt-4 pb-1 text-xs font-bold text-gray-500 uppercase tracking-wider">
                    Catalog Management
                </div>

                <a href="{{ route('admin.categories.index') }}" 
                   class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.categories*') ? 'bg-gray-700 text-yellow-400' : '' }}">
                   Categories
                </a>

                <!-- ВОТ ЗДЕСЬ БЫЛА ОШИБКА. Исправлено: добавлены кавычки -->
                <a href="{{ route('admin.clothing-lines.index') }}" 
                   class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.clothing-lines*') ? 'bg-gray-700 text-yellow-400' : '' }}">
                   Collections
                </a>

                <a href="{{ route('admin.attributes.index') }}" 
                   class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.attributes*') ? 'bg-gray-700 text-yellow-400' : '' }}">
                   Attributes (Sizes)
                </a>

                <a href="{{ route('admin.promocodes.index') }}" 
                   class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.promocodes*') ? 'bg-blue-600 text-white' : '' }}">
                   Promo Codes
                </a>
                
                <!-- Users -->
                <div class="pt-4 pb-1 text-xs font-bold text-gray-500 uppercase tracking-wider">
                    System
                </div>

                <a href="{{ route('admin.users.index') }}" 
                   class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.users*') ? 'bg-gray-700 text-yellow-400' : '' }}">
                   Users
                </a>

                @if(auth()->user()->role === 'super_admin')
                    <a href="{{ route('admin.settings.index') }}" 
                       class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.settings*') ? 'bg-gray-700 text-yellow-400' : '' }}">
                       Settings & Telegram
                    </a>
                @endif
            </nav>

            <div class="p-4 border-t border-gray-700 bg-gray-900">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button class="w-full text-left px-4 py-2 text-red-400 hover:text-white hover:bg-red-900 rounded transition flex items-center gap-2">
                        <span>🚪</span> Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Content -->
        <main class="flex-1 overflow-y-auto p-8 bg-gray-100">
            <!-- Global Flash Messages -->
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm flex justify-between items-center">
                    <div>{{ session('success') }}</div>
                    <button onclick="this.parentElement.remove()" class="text-green-900 font-bold">&times;</button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm flex justify-between items-center">
                    <div>{{ session('error') }}</div>
                    <button onclick="this.parentElement.remove()" class="text-red-900 font-bold">&times;</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>