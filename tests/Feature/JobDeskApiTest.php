<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobDeskApiTest extends TestCase
{
    use RefreshDatabase;

    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.openclaw_api_token' => 'test-token']);
        $this->headers = ['Authorization' => 'Bearer test-token', 'Accept' => 'application/json'];
        User::factory()->create(['role' => User::ROLE_ADMIN, 'name' => 'Hussain']);
    }

    public function test_api_requires_the_token(): void
    {
        $this->getJson('/api/desk/alerts')->assertUnauthorized();
    }

    public function test_create_job_with_assignee_by_name_returns_card_links(): void
    {
        $ali = User::factory()->create(['role' => User::ROLE_AC_MECHANIC, 'name' => 'Ali Waheed']);

        $response = $this->postJson('/api/jobs', [
            'job_type' => 'ac',
            'customer_name' => 'Ahmed',
            'customer_phone' => '7771234',
            'title' => 'AC service',
            'customer_email' => 'ahmed@example.com',
            'location' => 'Ma. Rose Villa',
            'scheduled_at' => now()->addHours(5)->format('Y-m-d H:i:s'),
            'assignees' => ['ali'],
        ], $this->headers)->assertCreated();

        $jobId = $response->json('job_id');
        $response->assertJsonPath('desk_url', route('desk.show', $jobId))
            ->assertJsonPath('admin_url', route('jobs.show', $jobId))
            ->assertJsonPath('desk.assignees.0.name', 'Ali Waheed')
            ->assertJsonPath('desk.assignees.0.accepted', false);

        $this->assertTrue(Job::find($jobId)->isAssignedTo($ali));
        $this->assertSame('ahmed@example.com', Job::find($jobId)->customer->email);
        $this->assertSame('ahmed@example.com', Job::find($jobId)->customer_email);
    }

    public function test_unknown_or_ambiguous_staff_creates_nothing(): void
    {
        User::factory()->create(['role' => User::ROLE_MOTO_MECHANIC, 'name' => 'Ali One']);
        User::factory()->create(['role' => User::ROLE_MOTO_MECHANIC, 'name' => 'Ali Two']);

        $this->postJson('/api/jobs', [
            'job_type' => 'moto', 'customer_name' => 'X', 'customer_phone' => '7000000', 'assignees' => ['Ali'],
        ], $this->headers)->assertStatus(422)->assertJsonPath('error', 'Could not match staff.');

        $this->postJson('/api/jobs', [
            'job_type' => 'moto', 'customer_name' => 'X', 'customer_phone' => '7000000', 'assignees' => ['Nobody Here'],
        ], $this->headers)->assertStatus(422);

        $this->assertSame(0, Job::count());
    }

    public function test_alerts_contact_note_assign_and_cancel_endpoints(): void
    {
        $tech = User::factory()->create(['role' => User::ROLE_MOTO_MECHANIC, 'name' => 'Ibrahim']);
        $job = Job::create([
            'job_type' => 'moto', 'job_category' => 'general', 'job_date' => now()->toDateString(),
            'title' => 'LATE-BIKE', 'customer_name' => 'Ali', 'customer_phone' => '7790000',
            'status' => Job::STATUS_SCHEDULED, 'priority' => 'normal', 'payment_status' => 'unpaid',
            'scheduled_at' => now()->subHours(2),
        ]);

        $this->getJson('/api/desk/alerts', $this->headers)
            ->assertOk()
            ->assertJsonPath('data.0.id', $job->id)
            ->assertJsonPath('data.0.customer_call_owed', true)
            ->assertJsonPath('counts.call_customer', 1);

        $this->getJson('/api/desk/staff', $this->headers)->assertOk()->assertJsonFragment(['name' => 'Ibrahim']);

        $this->patchJson("/api/jobs/{$job->id}/assignees", ['assignees' => ['Ibrahim']], $this->headers)
            ->assertOk()->assertJsonPath('data.assignees.0.name', 'Ibrahim');

        $this->postJson("/api/jobs/{$job->id}/notes", ['content' => 'Customer called asking'], $this->headers)->assertOk();

        // Cancel blocked: customer still owed a call
        $this->postJson("/api/jobs/{$job->id}/cancel", ['cancellation_reason' => 'late_service'], $this->headers)
            ->assertStatus(422);
        $this->patchJson("/api/jobs/{$job->id}/status", ['status' => 'cancelled'], $this->headers)
            ->assertStatus(422);

        $this->postJson("/api/jobs/{$job->id}/contact", [
            'method' => 'call', 'outcome' => 'rescheduled', 'explanation' => 'overloaded',
            'new_time' => now()->addDay()->format('Y-m-d H:i'),
        ], $this->headers)->assertOk()->assertJsonPath('data.customer_call_owed', false);

        $this->getJson("/api/jobs/{$job->id}", $this->headers)
            ->assertOk()->assertJsonPath('data.desk.desk_url', route('desk.show', $job->id));

        $this->postJson("/api/jobs/{$job->id}/cancel", ['cancellation_reason' => 'customer_request'], $this->headers)
            ->assertOk();
        $this->assertSame(Job::STATUS_CANCELLED, $job->fresh()->status);
    }

    public function test_api_accepts_a_job_with_phone_only(): void
    {
        $this->postJson('/api/jobs', [
            'job_type' => 'ac', 'customer_phone' => '9556575', 'title' => 'AC not cooling',
            'location' => 'M. Fenmeeru Villa 2B', 'priority' => 'urgent',
        ], $this->headers)->assertCreated()->assertJsonPath('desk.customer_name', 'Customer 9556575');

        // Name given later through the API fills the placeholder on the customer record
        $this->postJson('/api/jobs', [
            'job_type' => 'ac', 'customer_phone' => '9556575', 'customer_name' => 'Aishath', 'title' => 'Follow-up',
        ], $this->headers)->assertCreated()->assertJsonPath('customer.name', 'Aishath');
    }

    public function test_fault_ticket_can_be_created_by_api_and_linked_to_a_job(): void
    {
        User::factory()->create(['role' => User::ROLE_ADMIN, 'name' => 'Kasun']);
        $job = Job::create([
            'job_type' => 'ac', 'job_category' => 'general', 'job_date' => now()->toDateString(),
            'title' => 'AC', 'customer_name' => 'X', 'customer_phone' => '7816167',
            'status' => Job::STATUS_SCHEDULED, 'priority' => 'urgent', 'payment_status' => 'unpaid',
            'scheduled_at' => now()->addHour(),
        ]);

        $this->postJson('/api/faults', [
            'business_unit' => 'cool', 'priority' => 'urgent', 'customer_name' => 'X', 'customer_phone' => '7816167',
            'title' => 'Repeat', 'description' => 'Not cooling again', 'deadline_hours' => 24,
            'assign_to' => 'Kasun', 'job_id' => $job->id,
        ], $this->headers)->assertCreated()->assertJsonPath('ticket_number', 'FT-00001');

        $this->assertSame(Job::CONTACT_REASON_FAULT, $job->fresh()->customerCallReason());
    }
}
