<?php
// app/Http/Controllers/Admin/ErrorController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ErrorController extends Controller
{
    public function notFound()
    {
        return response()->view('errors.admin-404', [], 404);
    }
}
