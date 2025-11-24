<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobItem;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use App\Models\RoadWorthinessHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobItemController extends Controller
{
    /**
     * Add an item (part/consumable) to a job.
     */
    public function store(Request $request, Job $job)
    {
        $validated = $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'quantity'          => ['required', 'integer', 'min:1'],
            'unit_price'        => ['nullable', 'numeric', 'min:0'],
        ]);

        $inventoryItem = InventoryItem::findOrFail($validated['inventory_item_id']);
        $qty = (int) $validated['quantity'];

        if ($inventoryItem->quantity < $qty && !$inventoryItem->is_service) {
            // For services we usually don't track stock; this check is only for parts.
            return back()
                ->withErrors(['inventory_item_id' => 'Not enough stock for ' . $inventoryItem->name])
                ->withInput();
        }

        $unitPrice = $validated['unit_price'] !== null
            ? $validated['unit_price']
            : $inventoryItem->sell_price;

        $subtotal = $unitPrice * $qty;

        $jobItem = JobItem::create([
            'job_id'            => $job->id,
            'inventory_item_id' => $inventoryItem->id,
            'is_service'        => $inventoryItem->is_service,
            'quantity'          => $qty,
            'unit_price'        => $unitPrice,
            'subtotal'          => $subtotal,
        ]);

        // Deduct stock only for non-service items
        if (!$inventoryItem->is_service) {
            $inventoryItem->quantity -= $qty;
            $inventoryItem->save();

            InventoryLog::create([
                'inventory_item_id' => $inventoryItem->id,
                'job_id'            => $job->id,
                'quantity_change'   => -$qty,
                'type'              => 'sale',
                'user_id'           => Auth::id(),
                'notes'             => 'Used on job #' . $job->id,
            ]);
        }

        // Handle road worthiness service - update vehicle if job has a vehicle
        if ($inventoryItem->is_service && 
            str_contains(strtolower($inventoryItem->name), 'road worthiness') && 
            $job->vehicle_id) {
            
            $job->load('vehicle');
            $vehicle = $job->vehicle;
            
            if ($vehicle) {
                $issuedAt = now();
                $expiresAt = $issuedAt->copy()->addYear();

                // Update vehicle's current road worthiness
                $vehicle->road_worthiness_created_at = $issuedAt;
                $vehicle->road_worthiness_expires_at = $expiresAt;
                $vehicle->save();

                // Create history record
                RoadWorthinessHistory::create([
                    'vehicle_id' => $vehicle->id,
                    'job_id'     => $job->id,
                    'issued_at'  => $issuedAt,
                    'expires_at' => $expiresAt,
                ]);
            }
        }

        $job->recalculateTotals();

        return back()->with('success', 'Item added to job.');
    }

    /**
     * Remove an item from a job (and return stock).
     */
    public function destroy(Job $job, JobItem $item)
    {
        if ($item->job_id !== $job->id) {
            abort(404);
        }

        $inventoryItem = $item->inventoryItem;
        $qty = $item->quantity;

        if ($inventoryItem && !$item->is_service) {
            $inventoryItem->quantity += $qty;
            $inventoryItem->save();

            InventoryLog::create([
                'inventory_item_id' => $inventoryItem->id,
                'job_id'            => $job->id,
                'quantity_change'   => $qty,
                'type'              => 'return',
                'user_id'           => Auth::id(),
                'notes'             => 'Removed from job #' . $job->id,
            ]);
        }

        $item->delete();

        $job->recalculateTotals();

        return back()->with('success', 'Item removed from job.');
    }
}
