<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class InventoryCategoryController extends Controller
{
    /**
     * List all categories
     */
    public function index()
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized. You do not have permission to manage inventory categories.');
        }
        $categories = InventoryCategory::orderBy('name')->paginate(20);
        return view('inventory.categories.index', compact('categories'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized. You do not have permission to manage inventory categories.');
        }
        return view('inventory.categories.create');
    }

    /**
     * Store new category
     */
    public function store(Request $request)
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized. You do not have permission to manage inventory categories.');
        }
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255', 'unique:inventory_categories,name'],
            'slug'      => ['nullable', 'string', 'max:255', 'unique:inventory_categories,slug'],
            'type'      => ['required', 'in:moto,ac,both,general'],
            'is_active' => ['nullable', 'in:0,1'],
        ]);

        // Handle checkbox - convert to boolean
        $validated['is_active'] = (bool) ($request->input('is_active', 0));

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        InventoryCategory::create($validated);

        return redirect()
            ->route('inventory-categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(InventoryCategory $inventoryCategory)
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized. You do not have permission to manage inventory categories.');
        }
        return view('inventory.categories.edit', compact('inventoryCategory'));
    }

    /**
     * Update category
     */
    public function update(Request $request, InventoryCategory $inventoryCategory)
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized. You do not have permission to manage inventory categories.');
        }
        try {
            $validated = $request->validate([
                'name'      => ['required', 'string', 'max:255', 'unique:inventory_categories,name,' . $inventoryCategory->id],
                'slug'      => ['nullable', 'string', 'max:255', 'unique:inventory_categories,slug,' . $inventoryCategory->id],
                'type'      => ['required', 'in:moto,ac,both,general'],
                'is_active' => ['nullable', 'in:0,1'],
            ]);

            // Handle checkbox - convert to boolean
            $validated['is_active'] = (bool) ($request->input('is_active', 0));

            // Auto-generate slug if not provided or empty
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            $inventoryCategory->update($validated);

            return redirect()
                ->route('inventory-categories.index')
                ->with('success', 'Category updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    /**
     * Delete category
     */
    public function destroy(InventoryCategory $inventoryCategory)
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized. You do not have permission to manage inventory categories.');
        }
        // Check if category is used
        if ($inventoryCategory->inventoryItems()->exists()) {
            return back()->with('error', 'Cannot delete category that has inventory items. Deactivate it instead.');
        }

        $inventoryCategory->delete();
        return redirect()
            ->route('inventory-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
