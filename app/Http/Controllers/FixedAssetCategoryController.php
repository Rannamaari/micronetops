<?php

namespace App\Http\Controllers;

use App\Models\FixedAssetCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FixedAssetCategoryController extends Controller
{
    public function index()
    {
        return view('fixed-assets.categories.index', [
            'categories' => FixedAssetCategory::withCount('assets')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        FixedAssetCategory::create($validated);

        return back()->with('success', 'Category added successfully.');
    }

    public function update(Request $request, FixedAssetCategory $fixedAssetCategory)
    {
        $validated = $this->validated($request, $fixedAssetCategory);

        $fixedAssetCategory->update($validated);

        return back()->with('success', 'Category updated successfully.');
    }

    private function validated(Request $request, ?FixedAssetCategory $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('fixed_asset_categories', 'name')->ignore($category?->id)],
            'code' => ['required', 'string', 'max:10', Rule::unique('fixed_asset_categories', 'code')->ignore($category?->id)],
        ]);
    }
}
