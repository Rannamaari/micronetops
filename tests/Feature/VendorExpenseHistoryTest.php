<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorExpenseHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_page_shows_only_that_vendors_expenses(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $vendor = Vendor::create(['name' => 'Island Supplier', 'phone' => '7000001', 'is_active' => true]);
        $otherVendor = Vendor::create(['name' => 'Other Supplier', 'phone' => '7000002', 'is_active' => true]);
        $category = ExpenseCategory::create(['name' => 'Office Supplies', 'type' => ExpenseCategory::TYPE_OPERATING, 'is_active' => true]);

        $this->createExpense($vendor, $category, '2026-08-10', 108, 'ISLAND-001', true, 8);
        $this->createExpense($otherVendor, $category, '2026-08-11', 500, 'OTHER-001', false);

        $this->actingAs($manager)
            ->get(route('vendors.show', $vendor))
            ->assertOk()
            ->assertSee('Expense History')
            ->assertSee('ISLAND-001')
            ->assertSee('MVR 108.00')
            ->assertSee('MVR 8.00')
            ->assertDontSee('OTHER-001');
    }

    public function test_vendor_expenses_can_be_filtered_by_date_range(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $vendor = Vendor::create(['name' => 'Date Filter Vendor', 'phone' => '7000003', 'is_active' => true]);
        $category = ExpenseCategory::create(['name' => 'Transport', 'type' => ExpenseCategory::TYPE_OPERATING, 'is_active' => true]);

        $this->createExpense($vendor, $category, '2026-07-31', 100, 'BEFORE-RANGE', true);
        $this->createExpense($vendor, $category, '2026-08-01', 200, 'RANGE-START', true);
        $this->createExpense($vendor, $category, '2026-08-31', 300, 'RANGE-END', false);
        $this->createExpense($vendor, $category, '2026-09-01', 400, 'AFTER-RANGE', false);

        $this->actingAs($manager)
            ->get(route('vendors.show', [
                'vendor' => $vendor,
                'from_date' => '2026-08-01',
                'to_date' => '2026-08-31',
            ]))
            ->assertOk()
            ->assertSee('RANGE-START')
            ->assertSee('RANGE-END')
            ->assertSee('MVR 500.00')
            ->assertSee('MVR 200.00')
            ->assertSee('MVR 300.00')
            ->assertDontSee('BEFORE-RANGE')
            ->assertDontSee('AFTER-RANGE');
    }

    private function createExpense(
        Vendor $vendor,
        ExpenseCategory $category,
        string $date,
        float $amount,
        string $reference,
        bool $isPaid,
        float $gstAmount = 0
    ): Expense {
        return Expense::create([
            'expense_category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'business_unit' => Expense::UNIT_IT,
            'amount' => $amount,
            'subtotal_amount' => $amount - $gstAmount,
            'is_gst_applicable' => $gstAmount > 0,
            'gst_rate' => $gstAmount > 0 ? 8 : 0,
            'gst_amount' => $gstAmount,
            'is_paid' => $isPaid,
            'incurred_at' => $date,
            'reference' => $reference,
        ]);
    }
}
