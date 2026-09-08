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
        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('last_expense', function (array $lastExpense) {
            return $lastExpense['date'] === '2026-09-07'
                && str_contains($lastExpense['add_another_url'], 'expenses/create-operating?date=2026-09-07')
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
            ->get(route('expenses.index'))
            ->assertOk()
            ->assertSee('Last expense added successfully')
            ->assertSee('Add Another Expense');

        $this->actingAs($manager)
            ->get(route('expenses.create-operating', ['date' => '2026-09-07']))
            ->assertOk()
            ->assertSee('Invoice / Bill Number')
            ->assertSee('id="vendor-search"', false)
            ->assertSee('value="2026-09-07"', false);
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
