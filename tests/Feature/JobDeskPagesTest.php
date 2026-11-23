<?php

namespace Tests\Feature;

use App\Models\FaultTicket;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobDeskPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setTime(10, 0));
    }

    private function makeJob(array $attrs = []): Job
    {
        return Job::create(array_merge([
            'job_type' => 'ac',
            'job_category' => 'general',
            'job_date' => now()->toDateString(),
            'title' => 'AC not cooling',
            'customer_name' => 'Ahmed Rasheed',
            'customer_phone' => '7771234',
            'status' => Job::STATUS_SCHEDULED,
            'priority' => Job::PRIORITY_NORMAL,
            'payment_status' => 'unpaid',
            'scheduled_at' => now()->addHours(4),
        ], $attrs))->fresh();
    }

    private function staff(string $role = User::ROLE_AC_MECHANIC): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_staff_sees_their_jobs_on_my_jobs(): void
    {
        $tech = $this->staff();
        $mine = $this->makeJob(['title' => 'MINE-JOB']);
        $mine->assignTechnicians([$tech->id]);
        $this->makeJob(['title' => 'OTHER-JOB']);

        $this->actingAs($tech)->get(route('desk.index'))
            ->assertOk()
            ->assertSee('MINE-JOB')
            ->assertDontSee('OTHER-JOB');
    }

    public function test_staff_cannot_open_a_job_that_is_not_theirs(): void
    {
        $tech = $this->staff();
        $job = $this->makeJob();

        $this->actingAs($tech)->get(route('desk.show', $job))->assertForbidden();
        $this->actingAs($tech)->post(route('desk.status', $job), ['action' => 'start'])->assertForbidden();
    }

    public function test_team_sees_a_late_job_in_their_unit_and_can_log_the_call(): void
    {
        $assigned = $this->staff();
        $helper = $this->staff();
        $motoTech = $this->staff(User::ROLE_MOTO_MECHANIC);
        $job = $this->makeJob(['title' => 'LATE-AC-JOB', 'scheduled_at' => now()->subMinutes(45)]);
        $job->assignTechnicians([$assigned->id]);

        // 45 minutes late -> team level
        $this->actingAs($helper)->get(route('desk.index'))
            ->assertOk()
            ->assertSee('Customers waiting for an explanation')
            ->assertSee('LATE-AC-JOB');

        // Moto mechanic does not see AC jobs
        $this->actingAs($motoTech)->get(route('desk.index'))->assertDontSee('LATE-AC-JOB');
        $this->actingAs($motoTech)->get(route('desk.show', $job))->assertForbidden();

        // Helper can open the card and log the call, but cannot change status
        $this->actingAs($helper)->get(route('desk.show', $job))
            ->assertOk()
            ->assertSee('The customer is waiting for a call')
            ->assertDontSee('Work completed');

        $this->actingAs($helper)->post(route('desk.contact', $job), [
            'method' => 'call',
            'outcome' => 'informed',
            'explanation' => 'running_late',
            'note' => 'Coming at 11:30',
        ])->assertRedirect(route('desk.index'))
          ->assertSessionHas('success');

        $this->assertFalse($job->fresh()->owesCustomerCall());
        $this->actingAs($helper)->post(route('desk.status', $job), ['action' => 'complete'])->assertForbidden();
    }

    public function test_full_flow_accept_start_hold_resume_complete_invoice(): void
    {
        $tech = $this->staff();
        $job = $this->makeJob();
        $job->assignTechnicians([$tech->id]);

        $this->actingAs($tech)->get(route('desk.show', $job))->assertOk()->assertSee('Accept job');

        $this->actingAs($tech)->post(route('desk.accept', $job))->assertRedirect(route('desk.show', $job));
        $this->assertNotNull($job->fresh()->assignees->first()->pivot->accepted_at);

        $this->actingAs($tech)->post(route('desk.status', $job), ['action' => 'start']);
        $this->assertSame(Job::STATUS_IN_PROGRESS, $job->fresh()->status);

        $this->actingAs($tech)->post(route('desk.status', $job), ['action' => 'hold']);
        $this->assertSame(Job::STATUS_WAITING_PARTS, $job->fresh()->status);
        $this->assertTrue($job->fresh()->owesCustomerCall());

        $this->actingAs($tech)->post(route('desk.contact', $job), [
            'method' => 'whatsapp', 'outcome' => 'informed', 'explanation' => 'waiting_parts',
        ]);
        $this->assertFalse($job->fresh()->owesCustomerCall());

        $this->actingAs($tech)->post(route('desk.status', $job), ['action' => 'resume']);
        $this->actingAs($tech)->post(route('desk.status', $job), ['action' => 'complete']);
        $this->assertSame(Job::STATUS_COMPLETED, $job->fresh()->status);

        // Invalid transition is refused politely
        $this->actingAs($tech)->post(route('desk.status', $job), ['action' => 'start'])
            ->assertSessionHas('error');

        $this->actingAs($tech)->post(route('desk.invoiced', $job));
        $this->assertNotNull($job->fresh()->invoiced_at);

        $this->actingAs($tech)->get(route('desk.show', $job))
            ->assertOk()
            ->assertSee('Given to customer')
            ->assertSee('WhatsApp: Customer informed');
    }

    public function test_reschedule_needs_a_future_time(): void
    {
        $tech = $this->staff();
        $job = $this->makeJob(['scheduled_at' => now()->subHour()]);
        $job->assignTechnicians([$tech->id]);

        $this->actingAs($tech)->post(route('desk.contact', $job), [
            'method' => 'call', 'outcome' => 'rescheduled',
        ])->assertSessionHasErrors('new_time');

        $this->actingAs($tech)->post(route('desk.contact', $job), [
            'method' => 'call', 'outcome' => 'rescheduled', 'new_time' => now()->addDay()->format('Y-m-d\TH:i'),
        ])->assertSessionHasNoErrors();

        $this->assertFalse($job->fresh()->isOverdue());
    }

    public function test_cannot_cancel_while_customer_is_owed_a_call_unless_told(): void
    {
        $tech = $this->staff();
        $job = $this->makeJob(['scheduled_at' => now()->subHours(2)]);
        $job->assignTechnicians([$tech->id]);

        $this->actingAs($tech)->post(route('desk.cancel', $job), [
            'cancellation_reason' => Job::CANCEL_LATE_SERVICE,
        ])->assertSessionHasErrors('customer_informed');
        $this->assertTrue($job->fresh()->isActive());

        $this->actingAs($tech)->post(route('desk.cancel', $job), [
            'cancellation_reason' => Job::CANCEL_LATE_SERVICE,
            'customer_informed' => '1',
            'contact_method' => 'call',
        ])->assertSessionHasNoErrors();

        $job = $job->fresh();
        $this->assertSame(Job::STATUS_CANCELLED, $job->status);
        $this->assertSame(Job::CANCEL_LATE_SERVICE, $job->cancellation_reason);
        $this->assertFalse($job->owesCustomerCall());
    }

    public function test_cancel_because_of_us_needs_customer_told_but_customer_request_does_not(): void
    {
        $tech = $this->staff();
        $a = $this->makeJob();
        $b = $this->makeJob();
        $a->assignTechnicians([$tech->id]);
        $b->assignTechnicians([$tech->id]);

        $this->actingAs($tech)->post(route('desk.cancel', $a), ['cancellation_reason' => Job::CANCEL_PARTS_UNAVAILABLE])
            ->assertSessionHasErrors('customer_informed');

        $this->actingAs($tech)->post(route('desk.cancel', $b), ['cancellation_reason' => Job::CANCEL_CUSTOMER_REQUEST])
            ->assertSessionHasNoErrors();
        $this->assertSame(Job::STATUS_CANCELLED, $b->fresh()->status);
    }

    public function test_reporting_a_fault_creates_a_ticket_for_any_business_including_it(): void
    {
        $tech = $this->staff(User::ROLE_OPERATIONS_STAFF);
        $job = $this->makeJob(['job_type' => 'it', 'status' => Job::STATUS_COMPLETED, 'completed_at' => now()]);
        $job->assignTechnicians([$tech->id]);

        $this->actingAs($tech)->post(route('desk.fault', $job), [
            'title' => 'Printer still offline',
            'description' => 'Customer called back',
            'priority' => 'urgent',
        ])->assertRedirect(route('desk.show', $job));

        $ticket = FaultTicket::where('job_id', $job->id)->first();
        $this->assertNotNull($ticket);
        $this->assertSame('it', $ticket->business_unit);
        $this->assertSame('FT-' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT), $ticket->ticket_number);
        $this->assertSame(Job::CONTACT_REASON_FAULT, $job->fresh()->customerCallReason());
    }

    public function test_language_toggle_is_saved_and_translates_the_desk(): void
    {
        $tech = $this->staff();

        $this->actingAs($tech)->post(route('desk.locale'), ['locale' => 'bn'])->assertRedirect();
        $this->assertSame('bn', $tech->fresh()->locale);

        $this->actingAs($tech->fresh())->get(route('desk.index'))
            ->assertOk()
            ->assertSee('lang="bn"', false)
            ->assertSee(__('My jobs', [], 'bn'));
        $this->assertNotSame('My jobs', __('My jobs', [], 'bn'));

        $this->actingAs($tech)->post(route('desk.locale'), ['locale' => 'si']);
        $this->assertNotSame('My jobs', __('My jobs', [], 'si'));
        $this->actingAs($tech->fresh())->get(route('desk.index'))->assertSee(__('My jobs', [], 'si'));

        $this->actingAs($tech)->post(route('desk.locale'), ['locale' => 'xx'])->assertSessionHasErrors('locale');
    }

    public function test_language_only_applies_to_the_desk(): void
    {
        $tech = $this->staff();
        $tech->forceFill(['locale' => 'bn'])->save();
        $job = $this->makeJob();
        $job->assignTechnicians([$tech->id]);

        $this->actingAs($tech)->get(route('jobs.show', $job))
            ->assertOk()
            ->assertSee('lang="en"', false);
    }

    public function test_board_is_for_admin_and_manager_only(): void
    {
        $admin = $this->staff(User::ROLE_ADMIN);
        $tech = $this->staff();
        $this->makeJob(['title' => 'UNASSIGNED-JOB']);

        $this->actingAs($admin)->get(route('desk.board'))->assertOk()->assertSee('UNASSIGNED-JOB');
        $this->actingAs($admin)->get(route('desk.board', ['alert' => 'overdue']))->assertOk()->assertDontSee('UNASSIGNED-JOB');
        $this->actingAs($tech)->get(route('desk.board'))->assertForbidden();
    }

    public function test_cashier_and_customers_cannot_use_the_desk(): void
    {
        $this->actingAs($this->staff(User::ROLE_CASHIER))->get(route('desk.index'))->assertForbidden();
        $this->actingAs($this->staff(User::ROLE_CUSTOMER))->get(route('desk.index'))->assertForbidden();
    }

    public function test_job_staff_land_on_the_desk_after_login(): void
    {
        $tech = $this->staff();

        $this->post('/login', ['email' => $tech->email, 'password' => 'password'])
            ->assertRedirect(route('desk.index', absolute: false));
    }
}
