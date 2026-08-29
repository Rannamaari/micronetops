<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_view_reports_hub_with_expense_dashboard_link(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);

        $response = $this->actingAs($manager)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Reporting Hub');
        $response->assertSee('Expense Dashboard');
        $response->assertSee(route('expenses.reports'), false);
        $response->assertSee('Monthly Expense Pack');
    }
}
