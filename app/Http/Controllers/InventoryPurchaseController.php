<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryLog;
use App\Models\InventoryPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryPurchaseController extends Controller
{
    public function create(InventoryItem $inventory)
    {
        if ($inventory->is_service) {
            return redirect()->route('inventory.show', $inventory)
                ->with('error', 'Cannot record purchases for service items.');
        }

        $businessUnits = [
            'moto' => 'Moto',
            'ac' => 'AC',
            'shared' => 'Shared',
        ];

        return view('inventory.purchases.create', compact('inventory', 'businessUnits'));
    }

    public function store(Request $request, InventoryItem $inventory)
    {
        if ($inventory->is_service) {
            return redirect()->route('inventory.show', $inventory)
                ->with('error', 'Cannot record purchases for service items.');
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'purchased_at' => ['required', 'date'],
            'business_unit' => ['required', 'in:moto,ac,shared'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $quantity = (int) $validated['quantity'];
        $unitCost = (float) $validated['unit_cost'];
        $totalCost = round($quantity * $unitCost, 2);

        $purchase = InventoryPurchase::create([
            'inventory_item_id' => $inventory->id,
            'business_unit' => $validated['business_unit'],
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'purchased_at' => $validated['purchased_at'],
            'vendor' => $validated['vendor'] ?? null,
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        $oldQty = (int) $inventory->quantity;
        $oldCost = (float) $inventory->cost_price;
        $newQty = $oldQty + $quantity;

        if ($newQty > 0) {
            $weightedCost = (($oldQty * $oldCost) + ($quantity * $unitCost)) / $newQty;
            $inventory->cost_price = round($weightedCost, 2);
        }

        $inventory->quantity = $newQty;
        $inventory->save();

        InventoryLog::create([
            'inventory_item_id' => $inventory->id,
            'job_id' => null,
            'quantity_change' => $quantity,
            'type' => 'purchase',
            'user_id' => Auth::id(),
            'notes' => trim('Purchase' . ($validated['vendor'] ? ' from ' . $validated['vendor'] : '') . ($validated['reference'] ? ' (' . $validated['reference'] . ')' : '')),
        ]);

        return redirect()->route('inventory.show', $inventory)
            ->with('success', 'Inventory purchase recorded successfully.');
    }
}
