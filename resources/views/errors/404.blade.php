    <!-- FILE: resources/views/errors/404.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found | {{ config('app.name', 'MultiShop') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <div class="text-9xl font-bold text-gray-200 mb-2">404</div>
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Page Not Found</h1>
            <p class="text-gray-600 mb-8">
                The page you're looking for doesn't exist or has been moved.
            </p>
        </div>
        
        <div class="space-y-4">
            <a href="/" class="block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                ← Go to Homepage
            </a>
            <a href="javascript:history.back()" class="block border border-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-50 transition">
                Go Back
            </a>
        </div>
        
        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-500">
                &copy; {{ date('Y') }} {{ config('app.name', 'MultiShop') }}
            </p>
        </div>
    </div>
</body>
</html>