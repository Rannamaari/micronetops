<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\DailySalesLine;
use App\Models\DailySalesLog;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationsStaffAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_operations_staff_has_only_the_required_operational_modules(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_OPERATIONS_STAFF]);

        $this->assertNull($staff->allowedBusinessUnit());
        $this->assertTrue($staff->canCreateJobs());
        $this->assertTrue($staff->canManageOperationalExpenses());
        $this->assertTrue($staff->canCreateInventory());
        $this->assertFalse($staff->canViewDashboard());
        $this->assertFalse($staff->canViewReports());
        $this->assertFalse($staff->canAccessHR());

        $this->actingAs($staff)
            ->get(route('sales.daily.index'))
            ->assertOk()
            ->assertSee('New Sale (Micro Moto)')
            ->assertSee('New Sale (Micro Cool)')
            ->assertSee('New Sale (Micronet)')
            ->assertSee('New Sale (Easy Fix)')
            ->assertDontSee('Sales Reports')
            ->assertDontSee('Dashboard');

        foreach (['customers.index', 'customers.create', 'jobs.index', 'jobs.create', 'inventory.index', 'inventory.create', 'expenses.index', 'expenses.create-operating', 'expenses.create-cogs', 'vendors.index', 'vendors.create'] as $routeName) {
            $this->actingAs($staff)->get(route($routeName))->assertOk();
        }

        $this->actingAs($staff)
            ->get(route('expenses.index'))
            ->assertDontSee(route('expenses.reports'))
            ->assertDontSee('Manage Recurring');
    }

    public function test_operations_staff_is_blocked_from_management_hr_and_report_routes(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_OPERATIONS_STAFF]);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertRedirect(route('sales.daily.index'));

        foreach (['reports.index', 'expenses.reports', 'hr.dashboard', 'accounts.index', 'sales.eod.index', 'sales.purchase-orders.index', 'fixed-assets.index', 'sms.index', 'users.index', 'leads.index', 'faults.index', 'petty-cash.index', 'inventory-categories.index'] as $routeName) {
            $this->actingAs($staff)->get(route($routeName))->assertForbidden();
        }
    }

    public function test_operations_staff_can_receive_petty_cash_without_accessing_the_module(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $staff = User::factory()->create([
            'role' => User::ROLE_OPERATIONS_STAFF,
            'name' => 'Operations Recipient',
        ]);

        $this->actingAs($manager)
            ->get(route('petty-cash.admin-dashboard'))
            ->assertOk()
            ->assertSee('Operations Recipient')
            ->assertSee(route('petty-cash.show-top-up-form', $staff));

        $this->actingAs($staff)
            ->get(route('petty-cash.index'))
            ->assertForbidden();
    }

    public function test_operations_staff_cannot_maintain_existing_inventory_records(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_OPERATIONS_STAFF]);
        $item = InventoryItem::create([
            'name' => 'Restricted Test Item',
            'unit' => 'pcs',
            'category' => 'it',
            'quantity' => 1,
            'cost_price' => 10,
            'sell_price' => 15,
            'is_service' => false,
            'is_active' => true,
        ]);

        $this->actingAs($staff)->get(route('inventory.show', $item))
            ->assertOk()
            ->assertDontSee('Adjust Stock')
            ->assertDontSee('Record Purchase')
            ->assertDontSee(route('inventory.edit', $item));

        $this->actingAs($staff)->get(route('inventory.edit', $item))->assertForbidden();
        $this->actingAs($staff)->post(route('inventory.adjust-stock', $item), [
            'quantity_change' => 1,
        ])->assertForbidden();
    }

    public function test_operations_staff_login_lands_on_daily_sales(): void
    {
        $staff = User::factory()->create([
            'role' => User::ROLE_OPERATIONS_STAFF,
            'password' => 'password',
        ]);

        $this->post(route('login'), [
            'email' => $staff->email,
            'password' => 'password',
        ])->assertRedirect(route('sales.daily.index'));
    }

    public function test_operations_staff_can_record_an_invoice_payment(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_OPERATIONS_STAFF]);
        $account = Account::create([
            'name' => 'Receiving Account',
            'type' => Account::TYPE_BUSINESS,
            'is_active' => true,
            'is_system' => false,
            'balance' => 0,
        ]);
        $log = DailySalesLog::create([
            'date' => now()->toDateString(),
            'business_unit' => 'easyfix',
            'created_by' => $staff->id,
            'status' => DailySalesLog::STATUS_INVOICED,
            'approval_method' => 'not_applicable',
        ]);
        DailySalesLine::create([
            'daily_sales_log_id' => $log->id,
            'sort_order' => 1,
            'description' => 'Repair service',
            'qty' => 1,
            'unit_price' => 300,
            'payment_method' => 'cash',
            'line_total' => 300,
            'is_stock_item' => false,
            'is_gst_applicable' => false,
            'gst_amount' => 0,
        ]);
        $job = $log->createOrUpdateInvoiceJob(false);
        $log->update(['job_id' => $job->id]);

        $this->actingAs($staff)->post(route('sales.daily.submit', $log), [
            'payment_method' => 'transfer',
            'transfer_account_id' => $account->id,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(DailySalesLog::STATUS_PAID, $log->fresh()->status);
        $this->assertSame(300.0, (float) $account->fresh()->balance);
    }

    public function test_admin_can_assign_the_operations_staff_role(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Restricted Operator',
            'email' => 'operator@example.test',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
            'role' => User::ROLE_OPERATIONS_STAFF,
        ])->assertRedirect(route('users.index'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'operator@example.test',
            'role' => User::ROLE_OPERATIONS_STAFF,
        ]);
    }
}
