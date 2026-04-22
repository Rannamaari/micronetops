<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    /**
     * GET /api/inventory
     * List all active inventory items (non-service parts only).
     */
    public function index(): JsonResponse
    {
        $items = InventoryItem::active()
            ->parts()
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'brand', 'category', 'unit', 'quantity', 'sell_price', 'low_stock_limit']);

        return response()->json([
            'data'  => $items,
            'total' => $items->count(),
        ]);
    }

    /**
     * GET /api/inventory/search?q=keyword
     * Search inventory items by name or SKU (case-insensitive).
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->query('q', ''));

        if ($q === '') {
            return response()->json(['error' => 'Query parameter "q" is required.'], 422);
        }

        $items = InventoryItem::active()
            ->parts()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('sku',  'like', "%{$q}%")
                      ->orWhere('brand','like', "%{$q}%");
            })
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'brand', 'category', 'unit', 'quantity', 'sell_price', 'low_stock_limit']);

        return response()->json([
            'query' => $q,
            'data'  => $items,
            'total' => $items->count(),
        ]);
    }

    /**
     * POST /api/inventory
     * Add a new inventory item.
     *
     * Body: { "name": "Brake Pad", "category": "moto", "unit": "pcs", "quantity": 10,
     *         "sell_price": 250, "sku": "BP001", "brand": "Honda", "low_stock_limit": 2 }
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'            => ['required', 'string', 'max:255'],
                'category'        => ['required', 'in:moto,ac,it,easyfix'],
                'unit'            => ['nullable', 'string', 'max:20'],
                'quantity'        => ['nullable', 'integer', 'min:0'],
                'sell_price'      => ['nullable', 'numeric', 'min:0'],
                'cost_price'      => ['nullable', 'numeric', 'min:0'],
                'sku'             => ['nullable', 'string', 'max:255', 'unique:inventory_items,sku'],
                'brand'           => ['nullable', 'string', 'max:255'],
                'low_stock_limit' => ['nullable', 'integer', 'min:0'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        $item = InventoryItem::create([
            'name'            => $validated['name'],
            'category'        => $validated['category'],
            'unit'            => $validated['unit'] ?? 'pcs',
            'quantity'        => $validated['quantity'] ?? 0,
            'sell_price'      => $validated['sell_price'] ?? 0,
            'cost_price'      => $validated['cost_price'] ?? 0,
            'sku'             => $validated['sku'] ?? null,
            'brand'           => $validated['brand'] ?? null,
            'low_stock_limit' => $validated['low_stock_limit'] ?? 0,
            'is_active'       => true,
            'is_service'      => false,
        ]);

        return response()->json([
            'message' => "Item \"{$item->name}\" added to inventory.",
            'data'    => $item->only(['id', 'name', 'sku', 'brand', 'category', 'unit', 'quantity', 'sell_price', 'low_stock_limit']),
        ], 201);
    }

    /**
     * DELETE /api/inventory/{id}
     * Delete an inventory item by ID.
     */
    public function destroy(int $id): JsonResponse
    {
        $item = InventoryItem::find($id);

        if (!$item) {
            return response()->json(['error' => "Inventory item #{$id} not found."], 404);
        }

        $name = $item->name;
        $item->delete();

        return response()->json(['message' => "Item \"{$name}\" deleted from inventory."]);
    }

    /**
     * POST /api/inventory/update
     * Update stock for an item.
     *
     * Body: { "identifier": "name or sku", "action": "add|subtract|set", "quantity": 5 }
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'identifier' => ['required', 'string', 'max:255'],
                'action'     => ['required', 'in:add,subtract,set'],
                'quantity'   => ['required', 'integer', 'min:0'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        $identifier = $validated['identifier'];
        $action     = $validated['action'];
        $qty        = (int) $validated['quantity'];

        // Find item by SKU first, then by name (case-insensitive)
        $item = InventoryItem::where('sku', $identifier)->first()
            ?? InventoryItem::where('name', 'like', $identifier)->first();

        if (!$item) {
            return response()->json([
                'error' => "Item not found. No inventory item matches \"{$identifier}\" (checked SKU and name).",
            ], 404);
        }

        $oldQty = $item->quantity;

        switch ($action) {
            case 'add':
                $item->quantity = $oldQty + $qty;
                break;

            case 'subtract':
                if ($qty > $oldQty) {
                    return response()->json([
                        'error'            => "Insufficient stock. Cannot subtract {$qty} from current stock of {$oldQty}.",
                        'current_quantity' => $oldQty,
                    ], 422);
                }
                $item->quantity = $oldQty - $qty;
                break;

            case 'set':
                $item->quantity = $qty;
                break;
        }

        $item->save();

        return response()->json([
            'message'          => "Stock updated successfully.",
            'action'           => $action,
            'quantity_before'  => $oldQty,
            'quantity_change'  => $qty,
            'data'             => [
                'id'             => $item->id,
                'name'           => $item->name,
                'sku'            => $item->sku,
                'brand'          => $item->brand,
                'category'       => $item->category,
                'unit'           => $item->unit,
                'quantity'       => $item->quantity,
                'sell_price'     => $item->sell_price,
                'low_stock_limit'=> $item->low_stock_limit,
                'is_low_stock'   => $item->isLowStock(),
            ],
        ]);
    }
}
