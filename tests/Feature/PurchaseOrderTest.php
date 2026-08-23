<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_purchase_order(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $vendor = Vendor::create([
            'name' => 'Island Suppliers',
            'phone' => '7771234',
            'contact_name' => 'Ameen',
            'address' => 'Male',
            'is_active' => true,
        ]);
        $item = InventoryItem::create([
            'category' => 'it',
            'name' => 'Network Cable',
            'sku' => 'NET-CABLE-01',
            'unit' => 'roll',
            'quantity' => 0,
            'cost_price' => 250,
            'sell_price' => 300,
            'low_stock_limit' => 1,
            'is_active' => true,
            'is_service' => false,
        ]);

        $response = $this->actingAs($manager)->post(route('sales.purchase-orders.store'), [
            'business_unit' => 'it',
            'vendor_id' => $vendor->id,
            'order_date' => '2026-08-23',
            'expected_date' => '2026-08-30',
            'reference' => 'RFQ-55',
            'notes' => 'Urgent delivery requested.',
            'terms' => 'Please confirm stock before dispatch.',
            'lines' => [
                [
                    'inventory_item_id' => $item->id,
                    'description' => 'Network Cable',
                    'quantity' => 2,
                    'unit' => 'roll',
                    'unit_cost' => 250,
                    'notes' => 'CAT6',
                ],
            ],
        ]);

        $purchaseOrder = PurchaseOrder::first();

        $response->assertRedirect(route('sales.purchase-orders.show', $purchaseOrder));
        $this->assertNotNull($purchaseOrder);
        $this->assertSame('PO-00001', $purchaseOrder->po_number);
        $this->assertEquals('it', $purchaseOrder->business_unit);
        $this->assertEquals(500.00, (float) $purchaseOrder->total_amount);
        $this->assertCount(1, $purchaseOrder->lines);
    }

    public function test_mechanic_cannot_access_purchase_orders(): void
    {
        $mechanic = User::factory()->create(['role' => User::ROLE_MOTO_MECHANIC]);

        $this->actingAs($mechanic)
            ->get(route('sales.purchase-orders.index'))
            ->assertForbidden();
    }

    public function test_manager_can_amend_purchase_order_number(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);

        $purchaseOrder = PurchaseOrder::create([
            'po_number' => 'PO-OLD-001',
            'business_unit' => 'it',
            'vendor_name' => 'Island Suppliers',
            'order_date' => '2026-08-23',
            'status' => PurchaseOrder::STATUS_DRAFT,
            'subtotal' => 0,
            'total_amount' => 0,
            'created_by' => $manager->id,
        ]);

        $response = $this->actingAs($manager)->patch(route('sales.purchase-orders.update-number', $purchaseOrder), [
            'po_number' => 'PO-NEW-009',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertSame('PO-NEW-009', $purchaseOrder->fresh()->po_number);
    }

    public function test_manager_can_resubmit_cancelled_purchase_order_with_new_number(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);

        $purchaseOrder = PurchaseOrder::create([
            'po_number' => 'PO-CANCEL-001',
            'business_unit' => 'easyfix',
            'vendor_name' => 'Island Suppliers',
            'order_date' => '2026-08-23',
            'status' => PurchaseOrder::STATUS_CANCELLED,
            'subtotal' => 200,
            'total_amount' => 200,
            'created_by' => $manager->id,
        ]);

        $purchaseOrder->lines()->create([
            'description' => 'PVC Pipe',
            'quantity' => 2,
            'unit' => 'pcs',
            'unit_cost' => 100,
            'line_total' => 200,
        ]);

        $response = $this->actingAs($manager)->post(route('sales.purchase-orders.resubmit', $purchaseOrder));

        $response->assertRedirect();

        $newPurchaseOrder = PurchaseOrder::where('id', '!=', $purchaseOrder->id)->latest('id')->first();

        $this->assertNotNull($newPurchaseOrder);
        $this->assertSame(PurchaseOrder::STATUS_DRAFT, $newPurchaseOrder->status);
        $this->assertNotSame($purchaseOrder->po_number, $newPurchaseOrder->po_number);
        $this->assertSame('Island Suppliers', $newPurchaseOrder->vendor_name);
        $this->assertCount(1, $newPurchaseOrder->lines);
        $this->assertSame('PVC Pipe', $newPurchaseOrder->lines->first()->description);
    }
}
