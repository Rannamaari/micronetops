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
