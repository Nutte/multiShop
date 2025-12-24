<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\Product;
use App\Models\Category;
use App\Models\ClothingLine;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // Добавлено для работы со строками
use Illuminate\Validation\Rule;

class PromoCodeController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    private function checkSuperAdmin()
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Access denied. Only Super Admin can manage promo codes.');
        }
    }

    public function index()
    {
        $this->checkSuperAdmin();
        $promocodes = PromoCode::latest()->get();
        return view('admin.promocodes.index', compact('promocodes'));
    }

    public function create()
    {
        $this->checkSuperAdmin();

        $catalogData = [];
        $tenants = config('tenants.tenants');

        foreach ($tenants as $id => $config) {
            try {
                $this->tenantService->switchTenant($id);
                
                $catalogData[$id] = [
                    'name' => $config['name'],
                    'products' => Product::select('id', 'name', 'sku')->orderBy('name')->get()->toArray(),
                    'categories' => Category::select('id', 'name', 'slug')->orderBy('name')->get()->toArray(),
                    'lines' => ClothingLine::select('id', 'name', 'slug')->orderBy('name')->get()->toArray(),
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        return view('admin.promocodes.create', compact('catalogData'));
    }

    /**
     * Store a newly created promo code.
     */
    public function store(Request $request)
    {
        $this->checkSuperAdmin();

        // НОРМАЛИЗАЦИЯ: Принудительно переводим код в верхний регистр до валидации.
        // Это гарантирует, что проверка Rule::unique сработает корректно 
        // и в базу попадут данные в нужном формате.
        if ($request->has('code')) {
            $request->merge([
                'code' => Str::upper($request->code)
            ]);
        }

        $validated = $request->validate([
            'code' => [
                'required', 
                'string', 
                'alpha_dash', 
                Rule::unique(PromoCode::class, 'code')
            ],
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'scope_type' => 'required|in:global,specific,category,line',
            'scope_data' => 'nullable|array',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Очистка пустых данных по тенантам
        if (!empty($validated['scope_data'])) {
            $cleanedData = [];
            foreach ($validated['scope_data'] as $tenant => $ids) {
                if (!empty($ids)) {
                    $cleanedData[$tenant] = $ids;
                }
            }
            $validated['scope_data'] = $cleanedData;
        }

        PromoCode::create($validated);

        return redirect()->route('admin.promocodes.index')->with('success', 'Promo Code created successfully.');
    }

    public function destroy($id)
    {
        $this->checkSuperAdmin();
        PromoCode::destroy($id);
        return back()->with('success', 'Promo Code deleted.');
    }
}