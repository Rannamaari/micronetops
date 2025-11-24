<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InventoryController extends Controller
{
    /**
     * List inventory items with filters
     */
    public function index(Request $request)
    {
        // Allow viewing inventory to all authenticated users
        $query = InventoryItem::with('inventoryCategory');

        // Filters
        $categoryType = $request->query('category_type', 'all'); // moto, ac, both, all
        $itemType = $request->query('item_type', 'all'); // service, parts, all
        $status = $request->query('status', 'all'); // active, inactive, all
        $categoryId = $request->query('category_id', 'all'); // specific category

        if ($categoryType !== 'all') {
            $query->ofCategory($categoryType);
        }

        if ($itemType === 'service') {
            $query->services();
        } elseif ($itemType === 'parts') {
            $query->parts();
        }

        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($categoryId !== 'all' && $categoryId) {
            $query->where('inventory_category_id', $categoryId);
        }

        $items = $query->orderBy('name')->paginate(25)->withQueryString();

        $categories = InventoryCategory::active()->orderBy('name')->get();

        return view('inventory.index', compact('items', 'categories', 'categoryType', 'itemType', 'status', 'categoryId'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized. You do not have permission to manage inventory.');
        }
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized. You do not have permission to manage inventory.');
        }
        $categories = InventoryCategory::active()->orderBy('name')->get();
        return view('inventory.create', compact('categories'));
    }

    /**
     * Store new inventory item
     */
    public function store(Request $request)
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized. You do not have permission to manage inventory.');
        }
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized. You do not have permission to manage inventory.');
        }
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'sku'                   => ['nullable', 'string', 'max:255', 'unique:inventory_items,sku'],
            'brand'                 => ['nullable', 'string', 'max:255'],
            'unit'                  => ['required', 'string', 'max:50'],
            'category'              => ['required', 'in:moto,ac,both'],
            'inventory_category_id' => ['nullable', 'exists:inventory_categories,id'],
            'quantity'              => ['nullable', 'integer', 'min:0'],
            'cost_price'            => ['required', 'numeric', 'min:0'],
            'sell_price'            => ['required', 'numeric', 'min:0'],
            'low_stock_limit'       => ['nullable', 'integer', 'min:0'],
            'is_service'            => ['nullable', 'in:0,1'],
            'is_active'             => ['nullable', 'in:0,1'],
        ]);

        // Handle checkboxes - convert to boolean
        $validated['is_service'] = (bool) ($request->input('is_service', 0));
        $validated['is_active'] = (bool) ($request->input('is_active', 1)); // Default to active

        // For service items, quantity should be 0
        if ($validated['is_service']) {
            $validated['quantity'] = 0;
        }

        InventoryItem::create($validated);

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Inventory item created successfully.');
    }

    /**
     * Show single item with adjustment form and history
     */
    public function show(InventoryItem $inventory)
    {
        $inventory->load(['inventoryCategory', 'logs.user', 'logs.job']);
        $logs = $inventory->logs()->with(['user', 'job'])->latest()->paginate(20);
        $inventoryItem = $inventory; // For view compatibility

        return view('inventory.show', compact('inventoryItem', 'logs'));
    }

    /**
     * Show edit form
     */
    public function edit(InventoryItem $inventory)
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized. You do not have permission to manage inventory.');
        }
        $categories = InventoryCategory::active()->orderBy('name')->get();
        $inventoryItem = $inventory;
        return view('inventory.edit', compact('inventoryItem', 'categories'));
    }

    /**
     * Update inventory item
     */
    public function update(Request $request, InventoryItem $inventory)
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized. You do not have permission to manage inventory.');
        }
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'sku'                   => ['nullable', 'string', 'max:255', 'unique:inventory_items,sku,' . $inventory->id],
            'brand'                 => ['nullable', 'string', 'max:255'],
            'unit'                  => ['required', 'string', 'max:50'],
            'category'              => ['required', 'in:moto,ac,both'],
            'inventory_category_id' => ['nullable', 'exists:inventory_categories,id'],
            'quantity'              => ['nullable', 'integer', 'min:0'],
            'cost_price'            => ['required', 'numeric', 'min:0'],
            'sell_price'            => ['required', 'numeric', 'min:0'],
            'low_stock_limit'       => ['nullable', 'integer', 'min:0'],
            'is_service'            => ['nullable', 'in:0,1'],
            'is_active'             => ['nullable', 'in:0,1'],
        ]);

        // Handle checkboxes - convert to boolean
        $validated['is_service'] = (bool) ($request->input('is_service', 0));
        $validated['is_active'] = (bool) ($request->input('is_active', 1));

        // For service items, quantity should be 0
        if ($validated['is_service']) {
            $validated['quantity'] = 0;
        }

        $inventory->update($validated);

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Inventory item updated successfully.');
    }

    /**
     * Toggle is_active status
     */
    public function toggleActive(InventoryItem $inventory)
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized. You do not have permission to manage inventory.');
        }
        $inventory->is_active = !$inventory->is_active;
        $inventory->save();

        return back()->with('success', 'Item status updated.');
    }

    /**
     * Adjust stock manually
     */
    public function adjustStock(Request $request, InventoryItem $inventory)
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized. You do not have permission to manage inventory.');
        }
        if ($inventory->is_service) {
            return back()->with('error', 'Cannot adjust stock for service items.');
        }

        $validated = $request->validate([
            'quantity_change' => ['required', 'integer', 'not_in:0'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $quantityChange = (int) $validated['quantity_change'];
        $newQuantity = $inventory->quantity + $quantityChange;

        if ($newQuantity < 0) {
            return back()->with('error', 'Cannot reduce stock below zero.');
        }

        // Update quantity
        $inventory->quantity = $newQuantity;
        $inventory->save();

        // Create log entry
        InventoryLog::create([
            'inventory_item_id' => $inventory->id,
            'job_id'            => null,
            'quantity_change'   => $quantityChange,
            'type'              => 'adjustment',
            'user_id'           => Auth::id(),
            'notes'              => $validated['notes'] ?? 'Manual stock adjustment',
        ]);

        return back()->with('success', 'Stock adjusted successfully.');
    }

    /**
     * Delete inventory item (soft delete by setting is_active = false, or hard delete)
     */
    public function destroy(InventoryItem $inventory)
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized. You do not have permission to manage inventory.');
        }
        // Check if item is used in any jobs
        if ($inventory->jobItems()->exists()) {
            // Instead of deleting, just deactivate
            $inventory->is_active = false;
            $inventory->save();
            return back()->with('success', 'Item deactivated (cannot delete items used in jobs).');
        }

        $inventory->delete();
        return redirect()
            ->route('inventory.index')
            ->with('success', 'Inventory item deleted successfully.');
    }
}
