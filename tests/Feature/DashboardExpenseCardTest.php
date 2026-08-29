<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardExpenseCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_expenses_this_week_card(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-29'));

        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $category = ExpenseCategory::create([
            'name' => 'Fuel',
            'type' => ExpenseCategory::TYPE_OPERATING,
            'is_active' => true,
        ]);
        $vendor = Vendor::create([
            'name' => 'Weekly Fuel Supplier',
            'phone' => '7771234',
            'is_active' => true,
        ]);
        $account = Account::create([
            'name' => 'Main Cash',
            'type' => 'cash',
            'balance' => 10000,
            'is_active' => true,
            'is_system' => false,
        ]);

        Expense::create([
            'expense_category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'account_id' => $account->id,
            'business_unit' => Expense::UNIT_IT,
            'amount' => 450,
            'incurred_at' => '2026-08-25',
            'vendor' => 'Weekly Fuel Supplier',
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        Expense::create([
            'expense_category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'account_id' => $account->id,
            'business_unit' => Expense::UNIT_IT,
            'amount' => 250,
            'incurred_at' => '2026-08-10',
            'vendor' => 'Weekly Fuel Supplier',
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        $response = $this->actingAs($manager)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Expenses This Week');
        $response->assertSee('450.00', false);
        $response->assertSee(route('expenses.reports', ['period' => 'week']), false);
        $response->assertDontSee('250.00', false);
    }
}
