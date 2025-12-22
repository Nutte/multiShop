<!-- FILE: resources/views/layouts/admin.blade.php -->
<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin - @yield('title')</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="bg-gray-100 font-sans">
        <div class="flex h-screen">
            <!-- Sidebar -->
            <aside class="w-64 bg-gray-800 text-white flex flex-col">
                <div class="p-6 text-2xl font-bold bg-gray-900">
                    Admin Panel
                    <div class="text-xs font-normal text-gray-400 mt-1">
                        {{ request()->session()->get('is_super_admin') ? 'Super Admin' : 'Manager' }}
                    </div>
                    <div class="text-xs font-normal text-yellow-400 mt-1 uppercase">
                        {{ app(\App\Services\TenantService::class)->getCurrentTenantId() ?? 'Global' }}
                    </div>
                </div>
                <nav class="flex-1 px-4 py-6 space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : '' }}">Dashboard</a>
                    <a href="{{ route('admin.orders.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.orders*') ? 'bg-gray-700' : '' }}">Orders</a>
                    <a href="{{ route('admin.products.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.products*') ? 'bg-gray-700' : '' }}">Products</a>
                    <a href="{{ route('admin.categories.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.categories*') ? 'bg-gray-700' : '' }}">Categories</a>
                    
                    <!-- NEW: Attributes Link -->
                    <a href="{{ route('admin.attributes.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.attributes*') ? 'bg-gray-700' : '' }}">Attributes (Sizes/Types)</a>

                    @if(auth()->user()->role === 'super_admin')
                        <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.users*') ? 'bg-gray-700' : '' }}">Users</a>
                    @endif
                    
                    <a href="{{ route('admin.reports.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.reports*') ? 'bg-gray-700' : '' }}">Reports</a>
                    <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded {{ request()->routeIs('admin.settings*') ? 'bg-gray-700' : '' }}">Settings</a>
                </nav>
                <div class="p-4 border-t border-gray-700">
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button class="w-full text-left px-4 py-2 hover:bg-red-700 rounded">Logout</button>
                    </form>
                </div>
            </aside>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-8">
                @if(session('success'))
                    <div class="bg-green-100 text-green-700 p-4 mb-6 rounded border border-green-200 shadow-sm">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 text-red-700 p-4 mb-6 rounded border border-red-200 shadow-sm">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="bg-red-100 text-red-700 p-4 mb-6 rounded border border-red-200 shadow-sm">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>- {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </body>
    </html>