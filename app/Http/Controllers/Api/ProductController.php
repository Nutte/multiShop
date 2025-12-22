<?php
// FILE: app/Http/Controllers/Api/ProductController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    // GET /api/products
    public function index()
    {
        return response()->json(Product::latest()->paginate(12));
    }

    // GET /api/products/{id}
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    // GET /api/products/search?q=hoodie
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (!$query) {
            return $this->index();
        }

        $products = $this->productService->search($query);
        return response()->json($products);
    }
}