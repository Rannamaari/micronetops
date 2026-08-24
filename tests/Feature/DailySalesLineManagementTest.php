<?php

namespace Tests\Feature;

use App\Models\DailySalesLine;
use App\Models\DailySalesLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailySalesLineManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_edit_and_reorder_lines_after_invoice_creation(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);

        $log = DailySalesLog::create([
            'date' => '2026-08-24',
            'business_unit' => 'it',
            'created_by' => $manager->id,
            'status' => DailySalesLog::STATUS_INVOICED,
            'approval_method' => 'not_applicable',
        ]);

        $firstLine = DailySalesLine::create([
            'daily_sales_log_id' => $log->id,
            'sort_order' => 1,
            'description' => 'Router Setup',
            'qty' => 1,
            'unit_price' => 100,
            'payment_method' => 'cash',
            'line_total' => 100,
            'is_stock_item' => false,
            'is_gst_applicable' => false,
            'gst_amount' => 0,
        ]);

        $secondLine = DailySalesLine::create([
            'daily_sales_log_id' => $log->id,
            'sort_order' => 2,
            'description' => 'Cable Management',
            'qty' => 1,
            'unit_price' => 50,
            'payment_method' => 'cash',
            'line_total' => 50,
            'is_stock_item' => false,
            'is_gst_applicable' => false,
            'gst_amount' => 0,
        ]);

        $job = $log->createOrUpdateInvoiceJob(false);
        $log->update(['job_id' => $job->id]);

        $updateResponse = $this->actingAs($manager)->patch(route('sales.daily.update-line', [$log, $firstLine]), [
            'description' => 'Router Setup and Testing',
            'qty' => 2,
            'unit_price' => 75,
            'note' => 'Corrected after customer confirmation.',
            'warranty_value' => 3,
            'warranty_unit' => 'months',
            'is_gst_applicable' => 1,
        ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHasNoErrors();

        $firstLine->refresh();

        $this->assertSame('Router Setup and Testing', $firstLine->description);
        $this->assertSame(2, $firstLine->qty);
        $this->assertSame(150.0, (float) $firstLine->line_total);
        $this->assertSame(12.0, (float) $firstLine->gst_amount);
        $this->assertSame('Corrected after customer confirmation.', $firstLine->note);

        $reorderResponse = $this->actingAs($manager)->post(route('sales.daily.move-line', [$log, $secondLine]), [
            'direction' => 'up',
        ]);

        $reorderResponse->assertRedirect();
        $reorderResponse->assertSessionHasNoErrors();

        $orderedLines = $log->fresh()->lines;
        $this->assertSame([$secondLine->id, $firstLine->id], $orderedLines->pluck('id')->all());

        $jobItems = $log->fresh()->job->items()->orderBy('id')->get();
        $this->assertSame(['Cable Management', 'Router Setup and Testing'], $jobItems->pluck('item_name')->all());
        $this->assertSame('Corrected after customer confirmation.', $jobItems->last()->item_description);
    }
}
