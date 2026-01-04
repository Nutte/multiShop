<!-- FILE: resources/views/errors/admin-404.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 | Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-8">
            <div class="text-6xl font-bold text-gray-300 mb-4">404</div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Admin Resource Not Found</h1>
            <p class="text-gray-600 mb-6">
                The requested admin page does not exist or you don't have permission to access it.
            </p>
        </div>
        
        <div class="space-y-4">
            @auth('web')
                <a href="{{ route('admin.dashboard') }}" 
                   class="block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-center transition">
                    ← Return to Dashboard
                </a>
            @else
                <a href="{{ route('admin.login') }}" 
                   class="block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-center transition">
                    Go to Login
                </a>
            @endauth
            
            <a href="javascript:history.back()" 
               class="block border border-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg text-center hover:bg-gray-50 transition">
                ← Go Back
            </a>
        </div>
        
        <div class="mt-8 pt-6 border-t border-gray-200 text-center">
            <p class="text-sm text-gray-500">
                Admin Panel • {{ config('app.name', 'MultiShop') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">
                If you believe this is an error, contact the system administrator.
            </p>
        </div>
    </div>
</body>
</html>