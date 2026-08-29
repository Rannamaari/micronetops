<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_view_expense_report_dashboard_for_current_month(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-29'));

        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $account = Account::create([
            'name' => 'Main Bank',
            'type' => 'bank',
            'balance' => 50000,
            'is_active' => true,
            'is_system' => false,
        ]);
        $vendor = Vendor::create([
            'name' => 'Island Trade',
            'phone' => '7771234',
            'is_active' => true,
        ]);
        $cogs = ExpenseCategory::create([
            'name' => 'Tools Purchase',
            'type' => ExpenseCategory::TYPE_COGS,
            'is_active' => true,
        ]);
        $operating = ExpenseCategory::create([
            'name' => 'Fuel',
            'type' => ExpenseCategory::TYPE_OPERATING,
            'is_active' => true,
        ]);

        Expense::create([
            'expense_category_id' => $cogs->id,
            'vendor_id' => $vendor->id,
            'account_id' => $account->id,
            'business_unit' => Expense::UNIT_IT,
            'amount' => 1000,
            'incurred_at' => '2026-08-05',
            'vendor' => 'Island Trade',
            'reference' => 'PO-1',
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        Expense::create([
            'expense_category_id' => $operating->id,
            'vendor_id' => $vendor->id,
            'account_id' => $account->id,
            'business_unit' => Expense::UNIT_MOTO,
            'amount' => 500,
            'incurred_at' => '2026-08-20',
            'vendor' => 'Island Trade',
            'reference' => 'PO-2',
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        Expense::create([
            'expense_category_id' => $operating->id,
            'vendor_id' => $vendor->id,
            'account_id' => $account->id,
            'business_unit' => Expense::UNIT_MOTO,
            'amount' => 999,
            'incurred_at' => '2026-07-12',
            'vendor' => 'Island Trade',
            'reference' => 'OLD-1',
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        $response = $this->actingAs($manager)->get(route('expenses.reports'));

        $response->assertOk();
        $response->assertSee('Expense Reports');
        $response->assertSee('1,500.00', false);
        $response->assertSee('1,000.00', false);
        $response->assertSee('500.00', false);
        $response->assertDontSee('999.00', false);
    }

    public function test_expense_report_supports_custom_date_and_business_unit_filters(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-29'));

        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $account = Account::create([
            'name' => 'Cash Box',
            'type' => 'cash',
            'balance' => 50000,
            'is_active' => true,
            'is_system' => false,
        ]);
        $vendor = Vendor::create([
            'name' => 'Metro Vendor',
            'phone' => '7771234',
            'is_active' => true,
        ]);
        $operating = ExpenseCategory::create([
            'name' => 'Transport',
            'type' => ExpenseCategory::TYPE_OPERATING,
            'is_active' => true,
        ]);

        Expense::create([
            'expense_category_id' => $operating->id,
            'vendor_id' => $vendor->id,
            'account_id' => $account->id,
            'business_unit' => Expense::UNIT_EASYFIX,
            'amount' => 300,
            'incurred_at' => '2026-08-10',
            'vendor' => 'Metro Vendor',
            'reference' => 'E-1',
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        Expense::create([
            'expense_category_id' => $operating->id,
            'vendor_id' => $vendor->id,
            'account_id' => $account->id,
            'business_unit' => Expense::UNIT_IT,
            'amount' => 800,
            'incurred_at' => '2026-08-12',
            'vendor' => 'Metro Vendor',
            'reference' => 'I-1',
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        Expense::create([
            'expense_category_id' => $operating->id,
            'vendor_id' => $vendor->id,
            'account_id' => $account->id,
            'business_unit' => Expense::UNIT_IT,
            'amount' => 200,
            'incurred_at' => '2026-08-28',
            'vendor' => 'Metro Vendor',
            'reference' => 'I-2',
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        $response = $this->actingAs($manager)->get(route('expenses.reports', [
            'business_unit' => Expense::UNIT_IT,
            'from_date' => '2026-08-11',
            'to_date' => '2026-08-31',
            'period' => 'all',
        ]));

        $response->assertOk();
        $response->assertSee('1,000.00', false);
        $response->assertDontSee('300.00', false);
        $response->assertSee('Micronet', false);
    }
}
