<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\PettyCash;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PettyCashAccountFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_top_up_creates_staff_account_and_transfers_company_money(): void
    {
        [$manager, $staff, $source] = $this->baseRecords();

        $response = $this->actingAs($manager)->post(route('petty-cash.top-up-user', $staff), [
            'source_account_id' => $source->id,
            'amount' => 1000,
            'purpose' => 'Workshop purchases',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('petty-cash.admin-dashboard'));

        $staffAccount = Account::where('custodian_user_id', $staff->id)->firstOrFail();
        $this->assertTrue($staffAccount->is_petty_cash);
        $this->assertEquals(1000.00, (float) $staffAccount->balance);
        $this->assertEquals(4000.00, (float) $source->fresh()->balance);
        $this->assertDatabaseHas('petty_cash', [
            'assigned_to' => $staff->id,
            'source_account_id' => $source->id,
            'type' => 'topup',
            'status' => 'approved',
        ]);
        $this->assertDatabaseCount('account_transfers', 1);
    }

    public function test_operating_expense_can_be_paid_from_staff_petty_cash(): void
    {
        [$manager, $staff, $source] = $this->baseRecords();
        $staffAccount = $this->topUp($manager, $staff, $source, 1000);
        [$category, $vendor] = $this->expenseRecords(ExpenseCategory::TYPE_OPERATING);

        $response = $this->actingAs($manager)->post(route('expenses.store'), [
            'expense_category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'account_id' => $staffAccount->id,
            'business_unit' => Expense::UNIT_MOTO,
            'amount' => 300,
            'incurred_at' => '2026-09-07',
            'reference' => 'RCPT-100',
            'notes' => 'Fuel for collection',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('expenses.create-operating', [
            'date' => '2026-09-07',
            'expense_category_id' => $category->id,
            'account_id' => $staffAccount->id,
            'business_unit' => Expense::UNIT_MOTO,
            'is_paid' => 1,
            'is_gst_applicable' => 0,
        ]));
        $response->assertSessionHas('last_expense', function (array $lastExpense) {
            return $lastExpense['date'] === '2026-09-07'
                && str_contains($lastExpense['add_another_url'], 'expenses/create-operating?date=2026-09-07')
                && str_contains($lastExpense['add_another_url'], 'expense_category_id=')
                && str_contains($lastExpense['add_another_url'], 'account_id=')
                && $lastExpense['invoice_number'] === 'RCPT-100';
        });
        $expense = Expense::latest('id')->firstOrFail();
        $this->assertEquals(700.00, (float) $staffAccount->fresh()->balance);
        $this->assertDatabaseHas('petty_cash', [
            'expense_id' => $expense->id,
            'assigned_to' => $staff->id,
            'type' => 'expense',
            'status' => 'approved',
        ]);
        $this->assertEquals(700.00, PettyCash::userBalance($staff));

        $this->actingAs($manager)
            ->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Expense added successfully')
            ->assertSee('Add Another Expense')
            ->assertSee('Save & Add Another Expense', false)
            ->assertSee('value="2026-09-07"', false);

        $this->actingAs($manager)
            ->get(route('expenses.create-operating', ['date' => '2026-09-07']))
            ->assertOk()
            ->assertSee('Invoice / Bill Number')
            ->assertSee('id="vendor-search"', false)
            ->assertSee('value="2026-09-07"', false)
            ->assertSee('Save & Add Another Expense', false);
    }

    public function test_cogs_expense_deducts_staff_cash_and_adds_inventory(): void
    {
        [$manager, $staff, $source] = $this->baseRecords();
        $staffAccount = $this->topUp($manager, $staff, $source, 1000);
        [$category, $vendor] = $this->expenseRecords(ExpenseCategory::TYPE_COGS);
        $inventoryCategory = InventoryCategory::create([
            'name' => 'Filters',
            'is_active' => true,
        ]);
        $item = InventoryItem::create([
            'category' => 'moto',
            'inventory_category_id' => $inventoryCategory->id,
            'name' => 'Oil Filter',
            'sku' => 'OF-001',
            'unit' => 'pcs',
            'quantity' => 2,
            'cost_price' => 40,
            'sell_price' => 65,
            'low_stock_limit' => 1,
            'is_active' => true,
            'is_service' => false,
        ]);

        $response = $this->actingAs($manager)->post(route('expenses.store'), [
            'expense_category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'account_id' => $staffAccount->id,
            'business_unit' => Expense::UNIT_MOTO,
            'amount' => 400,
            'incurred_at' => '2026-09-07',
            'purchases' => [[
                'inventory_item_id' => $item->id,
                'quantity' => 10,
                'unit_cost' => 40,
                'sell_price' => 65,
            ]],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(600.00, (float) $staffAccount->fresh()->balance);
        $this->assertEquals(12, (float) $item->fresh()->quantity);
        $this->assertDatabaseHas('inventory_purchases', [
            'inventory_item_id' => $item->id,
            'quantity' => 10,
            'total_cost' => 400,
        ]);
    }

    public function test_expense_cannot_exceed_staff_petty_cash_balance(): void
    {
        [$manager, $staff, $source] = $this->baseRecords();
        $staffAccount = $this->topUp($manager, $staff, $source, 100);
        [$category, $vendor] = $this->expenseRecords(ExpenseCategory::TYPE_OPERATING);

        $response = $this->actingAs($manager)->post(route('expenses.store'), [
            'expense_category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'account_id' => $staffAccount->id,
            'business_unit' => Expense::UNIT_MOTO,
            'amount' => 150,
            'incurred_at' => '2026-09-07',
        ]);

        $response->assertSessionHasErrors('account_id');
        $this->assertEquals(100.00, (float) $staffAccount->fresh()->balance);
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_editing_petty_cash_expense_keeps_balance_and_history_in_sync(): void
    {
        [$manager, $staff, $source] = $this->baseRecords();
        $staffAccount = $this->topUp($manager, $staff, $source, 1000);
        [$category, $vendor] = $this->expenseRecords(ExpenseCategory::TYPE_OPERATING);

        $this->actingAs($manager)->post(route('expenses.store'), [
            'expense_category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'account_id' => $staffAccount->id,
            'business_unit' => Expense::UNIT_MOTO,
            'amount' => 300,
            'incurred_at' => '2026-09-07',
        ])->assertSessionHasNoErrors();

        $expense = Expense::latest('id')->firstOrFail();
        $response = $this->actingAs($manager)->patch(route('expenses.update', $expense), [
            'expense_category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'account_id' => $staffAccount->id,
            'business_unit' => Expense::UNIT_MOTO,
            'amount' => 450,
            'incurred_at' => '2026-09-07',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(550.00, (float) $staffAccount->fresh()->balance);
        $this->assertEquals(450.00, (float) $expense->pettyCashEntry()->firstOrFail()->amount);
        $this->assertEquals(
            550.00,
            (float) $staffAccount->transactions()->sum('amount')
        );
    }

    public function test_credit_expense_is_due_without_deducting_an_account(): void
    {
        [$manager, , $source] = $this->baseRecords();
        [$category, $vendor] = $this->expenseRecords(ExpenseCategory::TYPE_OPERATING);

        $response = $this->actingAs($manager)->post(route('expenses.store'), [
            'expense_category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'business_unit' => Expense::UNIT_MOTO,
            'amount' => 725,
            'is_paid' => 0,
            'incurred_at' => '2026-09-08',
            'due_date' => '2026-09-30',
            'reference' => 'CREDIT-725',
        ]);

        $response->assertSessionHasNoErrors();
        $expense = Expense::latest('id')->firstOrFail();

        $this->assertFalse($expense->is_paid);
        $this->assertNull($expense->account_id);
        $this->assertNull($expense->paid_at);
        $this->assertSame('2026-09-30', $expense->due_date->toDateString());
        $this->assertEquals(5000.00, (float) $source->fresh()->balance);
        $this->assertDatabaseMissing('account_transactions', [
            'related_type' => Expense::class,
            'related_id' => $expense->id,
        ]);

        $this->actingAs($manager)
            ->get(route('expenses.index', ['payment_status' => 'due']))
            ->assertOk()
            ->assertSee('CREDIT-725')
            ->assertSee('Outstanding:');
    }

    public function test_due_expense_can_be_marked_paid_exactly_once(): void
    {
        [$manager, , $source] = $this->baseRecords();
        [$category, $vendor] = $this->expenseRecords(ExpenseCategory::TYPE_OPERATING);

        $expense = Expense::create([
            'expense_category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'business_unit' => Expense::UNIT_MOTO,
            'amount' => 600,
            'is_paid' => false,
            'incurred_at' => '2026-09-01',
            'due_date' => '2026-09-15',
            'vendor' => $vendor->name,
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        $response = $this->actingAs($manager)->post(route('expenses.mark-paid', $expense), [
            'account_id' => $source->id,
            'paid_at' => '2026-09-08',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue($expense->fresh()->is_paid);
        $this->assertSame('2026-09-08', $expense->fresh()->paid_at->toDateString());
        $this->assertEquals(4400.00, (float) $source->fresh()->balance);

        $this->actingAs($manager)->post(route('expenses.mark-paid', $expense), [
            'account_id' => $source->id,
            'paid_at' => '2026-09-08',
        ])->assertSessionHasErrors('payment');

        $this->assertEquals(4400.00, (float) $source->fresh()->balance);
        $this->assertDatabaseCount('account_transactions', 1);
    }

    public function test_credit_cogs_adds_received_stock_without_deducting_cash(): void
    {
        [$manager, , $source] = $this->baseRecords();
        [$category, $vendor] = $this->expenseRecords(ExpenseCategory::TYPE_COGS);
        $inventoryCategory = InventoryCategory::create(['name' => 'Credit Stock', 'is_active' => true]);
        $item = InventoryItem::create([
            'category' => 'moto',
            'inventory_category_id' => $inventoryCategory->id,
            'name' => 'Credit Purchase Item',
            'sku' => 'CREDIT-ITEM-1',
            'unit' => 'pcs',
            'quantity' => 1,
            'cost_price' => 50,
            'sell_price' => 80,
            'low_stock_limit' => 1,
            'is_active' => true,
            'is_service' => false,
        ]);

        $response = $this->actingAs($manager)->post(route('expenses.store'), [
            'expense_category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'business_unit' => Expense::UNIT_MOTO,
            'amount' => 200,
            'is_paid' => 0,
            'incurred_at' => '2026-09-08',
            'purchases' => [[
                'inventory_item_id' => $item->id,
                'quantity' => 4,
                'unit_cost' => 50,
            ]],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(5, (float) $item->fresh()->quantity);
        $this->assertEquals(5000.00, (float) $source->fresh()->balance);
        $this->assertFalse(Expense::latest('id')->firstOrFail()->is_paid);
    }

    public function test_gst_expense_adds_eight_percent_and_deducts_the_total_payable(): void
    {
        [$manager, , $source] = $this->baseRecords();
        [$category, $vendor] = $this->expenseRecords(ExpenseCategory::TYPE_OPERATING);
        $vendor->update(['gst_number' => '1234567GST501']);

        $response = $this->actingAs($manager)->post(route('expenses.store'), [
            'expense_category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'account_id' => $source->id,
            'business_unit' => Expense::UNIT_IT,
            'amount' => 100,
            'is_paid' => 1,
            'is_gst_applicable' => 1,
            'incurred_at' => '2026-09-08',
            'reference' => 'GST-INV-100',
        ]);

        $response->assertSessionHasNoErrors();
        $expense = Expense::latest('id')->firstOrFail();
        $this->assertTrue($expense->is_gst_applicable);
        $this->assertEquals(100.00, (float) $expense->subtotal_amount);
        $this->assertEquals(8.00, (float) $expense->gst_amount);
        $this->assertEquals(108.00, (float) $expense->amount);
        $this->assertEquals(4892.00, (float) $source->fresh()->balance);

        $this->actingAs($manager)
            ->get(route('expenses.reports', ['period' => 'all']))
            ->assertOk()
            ->assertSee('GST Input Tax Summary')
            ->assertSee('GST-INV-100')
            ->assertSee('MVR 8.00');

        $export = $this->actingAs($manager)
            ->get(route('expenses.reports', ['period' => 'all', 'export' => 'gst_csv']));
        $export->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('1234567GST501', $export->streamedContent());
        $this->assertStringContainsString('GST-INV-100', $export->streamedContent());
    }

    public function test_gst_expense_requires_vendor_tin_and_invoice_number(): void
    {
        [$manager, , $source] = $this->baseRecords();
        [$category, $vendor] = $this->expenseRecords(ExpenseCategory::TYPE_OPERATING);

        $payload = [
            'expense_category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'account_id' => $source->id,
            'business_unit' => Expense::UNIT_IT,
            'amount' => 100,
            'is_paid' => 1,
            'is_gst_applicable' => 1,
            'incurred_at' => '2026-09-08',
        ];

        $this->actingAs($manager)
            ->post(route('expenses.store'), $payload)
            ->assertSessionHasErrors('vendor_id');

        $vendor->update(['gst_number' => '7654321GST501']);
        $this->actingAs($manager)
            ->post(route('expenses.store'), $payload)
            ->assertSessionHasErrors('reference');

        $this->assertDatabaseCount('expenses', 0);
        $this->assertEquals(5000.00, (float) $source->fresh()->balance);
    }

    public function test_manager_can_store_vendor_gst_number_from_expense_form(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);

        $response = $this->actingAs($manager)->postJson(route('vendors.store'), [
            'name' => 'GST Supplier',
            'phone' => '7990000',
            'gst_number' => '1112223GST501',
            'is_active' => 1,
        ]);

        $response->assertOk()->assertJsonPath('gst_number', '1112223GST501');
        $this->assertDatabaseHas('vendors', [
            'phone' => '7990000',
            'gst_number' => '1112223GST501',
        ]);
    }

    private function baseRecords(): array
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $staff = User::factory()->create(['role' => User::ROLE_MOTO_MECHANIC, 'name' => 'Ahmed Ali']);
        $source = Account::create([
            'name' => 'Main Cash',
            'type' => Account::TYPE_BUSINESS,
            'balance' => 5000,
            'is_active' => true,
            'is_system' => false,
        ]);

        return [$manager, $staff, $source];
    }

    private function topUp(User $manager, User $staff, Account $source, float $amount): Account
    {
        $this->actingAs($manager)->post(route('petty-cash.top-up-user', $staff), [
            'source_account_id' => $source->id,
            'amount' => $amount,
            'purpose' => 'Purchase allocation',
        ])->assertSessionHasNoErrors();

        return Account::where('custodian_user_id', $staff->id)->firstOrFail();
    }

    private function expenseRecords(string $type): array
    {
        $category = ExpenseCategory::create([
            'name' => $type === ExpenseCategory::TYPE_COGS ? 'Parts Purchase' : 'Transport',
            'type' => $type,
            'is_active' => true,
        ]);
        $vendor = Vendor::create([
            'name' => 'Island Supplier',
            'phone' => '7771234',
            'is_active' => true,
        ]);

        return [$category, $vendor];
    }
}
