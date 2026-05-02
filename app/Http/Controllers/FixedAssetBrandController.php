<?php

namespace App\Http\Controllers;

use App\Models\FixedAssetBrand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FixedAssetBrandController extends Controller
{
    public function index()
    {
        return view('fixed-assets.brands.index', [
            'brands' => FixedAssetBrand::withCount('assets')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        FixedAssetBrand::create($validated);

        return back()->with('success', 'Brand added successfully.');
    }

    public function update(Request $request, FixedAssetBrand $fixedAssetBrand)
    {
        $validated = $this->validated($request, $fixedAssetBrand);

        $fixedAssetBrand->update($validated);

        return back()->with('success', 'Brand updated successfully.');
    }

    private function validated(Request $request, ?FixedAssetBrand $brand = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('fixed_asset_brands', 'name')->ignore($brand?->id)],
            'code' => ['required', 'string', 'max:10', Rule::unique('fixed_asset_brands', 'code')->ignore($brand?->id)],
        ]);
    }
}
