<?php

namespace Tests\Feature;

use App\Models\FaultTicket;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobDeskAdminTest extends TestCase
{
    use RefreshDatabase;

    private function makeJob(array $attrs = []): Job
    {
        return Job::create(array_merge([
            'job_type' => 'moto',
            'job_category' => 'general',
            'job_date' => now()->toDateString(),
            'title' => 'Bike service',
            'customer_name' => 'Ali',
            'customer_phone' => '7790000',
            'status' => Job::STATUS_SCHEDULED,
            'priority' => Job::PRIORITY_NORMAL,
            'payment_status' => 'unpaid',
            'scheduled_at' => now()->addHours(3),
        ], $attrs))->fresh();
    }

    public function test_admin_job_page_shows_desk_alerts_and_link(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $job = $this->makeJob(['scheduled_at' => now()->subHours(2)]);

        $this->actingAs($admin)->get(route('jobs.show', $job))
            ->assertOk()
            ->assertSee('Open on Job Desk')
            ->assertSee('Call the customer')
            ->assertSee('Customer has been told');
    }

    public function test_admin_cancel_is_blocked_until_customer_is_told(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $job = $this->makeJob(['scheduled_at' => now()->subHours(2)]);

        $this->actingAs($admin)->post(route('jobs.cancel', $job), [
            'cancellation_reason' => Job::CANCEL_PRICE_ISSUE,
        ])->assertSessionHasErrors('customer_informed');
        $this->assertTrue($job->fresh()->isActive());

        $this->actingAs($admin)->post(route('jobs.cancel', $job), [
            'cancellation_reason' => Job::CANCEL_PRICE_ISSUE,
            'customer_informed' => '1',
            'contact_method' => 'whatsapp',
        ])->assertRedirect(route('jobs.show', $job));

        $job = $job->fresh();
        $this->assertSame(Job::STATUS_CANCELLED, $job->status);
        $this->assertFalse($job->owesCustomerCall());
        $this->assertSame('whatsapp', $job->notes()->where('type', 'customer_contact')->first()->metadata['method']);
    }

    public function test_customer_requested_cancel_still_works_without_tick(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $job = $this->makeJob();

        $this->actingAs($admin)->post(route('jobs.cancel', $job), [
            'cancellation_reason' => Job::CANCEL_CUSTOMER_REQUEST,
        ])->assertSessionHasNoErrors();

        $this->assertSame(Job::STATUS_CANCELLED, $job->fresh()->status);
    }

    public function test_status_dropdown_cannot_skip_the_cancel_form(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $job = $this->makeJob();

        $this->actingAs($admin)->patch(route('jobs.update-status', $job), ['status' => 'cancelled'])
            ->assertSessionHas('error');
        $this->assertSame(Job::STATUS_SCHEDULED, $job->fresh()->status);

        // Other status changes still work as before
        $this->actingAs($admin)->patch(route('jobs.update-status', $job), ['status' => 'in_progress'])
            ->assertRedirect(route('jobs.show', $job));
        $this->assertSame(Job::STATUS_IN_PROGRESS, $job->fresh()->status);
    }

    public function test_operations_staff_can_be_assigned_from_the_job_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $ops = User::factory()->create(['role' => User::ROLE_OPERATIONS_STAFF, 'name' => 'OPS-PERSON']);
        $job = $this->makeJob();

        $this->actingAs($admin)->get(route('jobs.create'))->assertOk()->assertSee('OPS-PERSON');

        $this->actingAs($admin)->patch(route('jobs.update-assignees', $job), ['assignees' => [$ops->id]]);
        $this->assertTrue($job->fresh()->isAssignedTo($ops));
    }

    public function test_fault_tickets_accept_micronet_and_easyfix_and_flag_the_job(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $job = $this->makeJob(['job_type' => 'it', 'status' => Job::STATUS_COMPLETED, 'completed_at' => now()]);

        $this->actingAs($admin)->post(route('faults.store'), [
            'business_unit' => 'it',
            'priority' => 'normal',
            'customer_name' => 'Ali',
            'customer_phone' => '7790000',
            'job_id' => $job->id,
            'title' => 'Network down again',
            'description' => 'Router keeps dropping',
        ])->assertSessionHasNoErrors();

        $ticket = FaultTicket::firstOrFail();
        $this->assertSame('Micronet', $ticket->unit_label);
        $this->assertSame(Job::CONTACT_REASON_FAULT, $job->fresh()->customerCallReason());

        $this->actingAs($admin)->get(route('faults.show', $ticket))->assertOk()->assertSee('Micronet');
        $this->actingAs($admin)->get(route('faults.index', ['tab' => 'all']))->assertOk()->assertSee('Micronet');
        $this->actingAs($admin)->get(route('faults.create'))->assertOk()->assertSee('Easy Fix');
    }
}
