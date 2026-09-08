<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DailySalesLine;
use App\Models\DailySalesLog;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GstReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_report_reconciles_output_and_input_gst(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $customer = Customer::create(['name' => 'GST Customer', 'phone' => '7000001', 'gst_number' => '1234567GST501']);
        $vendor = Vendor::create(['name' => 'GST Vendor', 'phone' => '7000002', 'gst_number' => '7654321GST501', 'is_active' => true]);
        $category = ExpenseCategory::create(['name' => 'Supplies', 'type' => ExpenseCategory::TYPE_OPERATING, 'is_active' => true]);

        $sale = DailySalesLog::create(['date' => '2026-08-15', 'business_unit' => 'it', 'status' => DailySalesLog::STATUS_INVOICED, 'customer_id' => $customer->id, 'created_by' => $manager->id]);
        DailySalesLine::create(['daily_sales_log_id' => $sale->id, 'description' => 'Service', 'qty' => 1, 'unit_price' => 1000, 'payment_method' => 'cash', 'line_total' => 1000, 'is_stock_item' => false, 'is_gst_applicable' => true, 'gst_amount' => 80]);
        Expense::create(['expense_category_id' => $category->id, 'vendor_id' => $vendor->id, 'business_unit' => Expense::UNIT_IT, 'amount' => 540, 'subtotal_amount' => 500, 'is_gst_applicable' => true, 'gst_rate' => 8, 'gst_amount' => 40, 'gst_expenditure_type' => 'revenue', 'incurred_at' => '2026-08-20', 'reference' => 'SUP-1']);
        Expense::create(['expense_category_id' => $category->id, 'vendor_id' => $vendor->id, 'business_unit' => Expense::UNIT_IT, 'amount' => 999, 'subtotal_amount' => 925, 'is_gst_applicable' => true, 'gst_rate' => 8, 'gst_amount' => 74, 'incurred_at' => '2026-07-31', 'reference' => 'OLD']);

        $this->actingAs($manager)->get(route('reports.gst', ['period_type' => 'monthly', 'year' => 2026, 'month' => 8]))
            ->assertOk()->assertSee('MVR 80.00')->assertSee('MVR 40.00')->assertSee('MVR 40.00')->assertSee('SUP-1')->assertDontSee('OLD');
    }

    public function test_quarterly_report_and_mira_aligned_csv_exports_use_full_period(): void
    {
        config(['gst.activity_numbers.it' => 'ACT-001']);
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $customer = Customer::create(['name' => 'Quarter Customer', 'phone' => '7000003', 'gst_number' => '1234567GST501']);
        $sale = DailySalesLog::create(['date' => '2026-09-30', 'business_unit' => 'it', 'status' => DailySalesLog::STATUS_PAID, 'customer_id' => $customer->id, 'created_by' => $manager->id]);
        DailySalesLine::create(['daily_sales_log_id' => $sale->id, 'description' => 'Quarter Service', 'qty' => 1, 'unit_price' => 200, 'payment_method' => 'cash', 'line_total' => 200, 'is_stock_item' => false, 'is_gst_applicable' => true, 'gst_amount' => 16]);

        $params = ['period_type' => 'quarterly', 'year' => 2026, 'quarter' => 3];
        $this->actingAs($manager)->get(route('reports.gst', $params))->assertOk()->assertSee('01 Jul 2026')->assertSee('30 Sep 2026')->assertSee('Quarter Customer');

        $csv = $this->actingAs($manager)->get(route('reports.gst.export', $params + ['statement' => 'tax-invoices']));
        $csv->assertOk();
        $content = $csv->streamedContent();
        $this->assertStringContainsString('Customer TIN', $content);
        $this->assertStringContainsString('1234567GST501', $content);
        $this->assertStringContainsString('ACT-001', $content);
        $this->assertStringContainsString('200.00', $content);
    }

    public function test_operations_staff_cannot_view_gst_report(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_OPERATIONS_STAFF]);
        $this->actingAs($staff)->get(route('reports.gst'))->assertForbidden();
    }

    public function test_admin_can_manage_database_backed_gst_settings(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->patch(route('system.settings.gst.update'), [
            'taxpayer_tin' => '9999999gst501',
            'activity_number_moto' => 'moto-01',
            'activity_number_cool' => 'cool-02',
            'activity_number_it' => 'it-03',
            'activity_number_easyfix' => 'easy-04',
            'activity_number_shared' => 'shared-05',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('gst_settings', [
            'taxpayer_tin' => '9999999GST501',
            'activity_number_it' => 'IT-03',
            'updated_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get(route('reports.gst'))
            ->assertOk()
            ->assertSee('9999999GST501');
    }
}
