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
        $this->makeJob(['title' => 'OTHER-JOB'])->assignTechnicians([$this->staff()->id]);

        $this->actingAs($tech)->get(route('desk.index'))
            ->assertOk()
            ->assertSee('MINE-JOB')
            ->assertDontSee('OTHER-JOB');
    }

    public function test_any_staff_with_the_link_can_view_comment_and_assign_themselves(): void
    {
        $tech = $this->staff(User::ROLE_MOTO_MECHANIC); // even from another business
        $other = $this->staff();
        $job = $this->makeJob();
        $job->assignTechnicians([$other->id]);

        // Can open and comment, but cannot change the job until assigned
        $this->actingAs($tech)->get(route('desk.show', $job))->assertOk()->assertSee('Assign to me')->assertDontSee('Work completed');
        $this->actingAs($tech)->post(route('desk.remark', $job), ['content' => 'Customer also wants gas top-up'])->assertSessionHasNoErrors();
        $this->actingAs($tech)->post(route('desk.status', $job), ['action' => 'start'])->assertForbidden();

        $this->actingAs($tech)->post(route('desk.take', $job))->assertRedirect(route('desk.show', $job));

        $job = $job->fresh();
        $this->assertTrue($job->isAssignedTo($tech));
        $this->assertTrue($job->isAssignedTo($other), 'existing assignee stays');
        $this->assertNotNull($job->assignees->firstWhere('id', $tech->id)->pivot->accepted_at);

        $this->actingAs($tech)->get(route('desk.show', $job))->assertDontSee('Assign to me')->assertSee('Work completed');
        $this->actingAs($tech)->post(route('desk.take', $job))->assertForbidden();
    }

    public function test_unassigned_jobs_show_as_open_jobs_in_the_right_business(): void
    {
        $acTech = $this->staff();
        $motoTech = $this->staff(User::ROLE_MOTO_MECHANIC);
        $this->makeJob(['title' => 'OPEN-AC-JOB']);

        $this->actingAs($acTech)->get(route('desk.index'))->assertSee('Open jobs – nobody assigned')->assertSee('OPEN-AC-JOB');
        $this->actingAs($motoTech)->get(route('desk.index'))->assertDontSee('OPEN-AC-JOB');
    }

    public function test_staff_can_log_a_new_job_without_assigning_anyone(): void
    {
        $ops = $this->staff(User::ROLE_OPERATIONS_STAFF);

        $this->actingAs($ops)->get(route('desk.create'))->assertOk()->assertSee('Micronet');

        $this->actingAs($ops)->post(route('desk.store'), [
            'job_type' => 'ac',
            'customer_name' => 'Mohamed Anas',
            'customer_phone' => '+960 919-6161',
            'location' => 'H. Gardenia, Majeedhee Magu',
            'title' => 'AC water leak',
            'problem_description' => 'Leaking for 3 days',
        ])->assertSessionHasNoErrors();

        $job = Job::latest('id')->first();
        $this->assertSame('9196161', $job->customer_phone);
        $this->assertSame('9196161', $job->customer->phone);
        $this->assertSame(Job::STATUS_NEW, $job->status);
        $this->assertTrue($job->assignees->isEmpty());

        // Same number again re-uses the customer, and "assign me" works
        $this->actingAs($ops)->post(route('desk.store'), [
            'job_type' => 'it', 'customer_name' => 'Anas', 'customer_phone' => '9196161',
            'location' => 'Same', 'title' => 'Router', 'problem_description' => 'Slow wifi', 'assign_me' => '1',
            'scheduled_at' => now()->addDay()->format('Y-m-d\\TH:i'),
        ])->assertSessionHasNoErrors();
        $second = Job::latest('id')->first();
        $this->assertSame($job->customer_id, $second->customer_id);
        $this->assertTrue($second->isAssignedTo($ops));
        $this->assertSame(Job::STATUS_SCHEDULED, $second->status);
    }

    public function test_mechanic_can_only_log_jobs_for_their_business(): void
    {
        $motoTech = $this->staff(User::ROLE_MOTO_MECHANIC);

        $this->actingAs($motoTech)->post(route('desk.store'), [
            'job_type' => 'ac', 'customer_name' => 'X', 'customer_phone' => '7000000',
            'location' => 'Y', 'title' => 'Z', 'problem_description' => 'W',
        ])->assertSessionHasErrors('job_type');
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

        // Moto mechanic does not get AC jobs on their list (but can open a shared link)
        $this->actingAs($motoTech)->get(route('desk.index'))->assertDontSee('LATE-AC-JOB');
        $this->actingAs($motoTech)->get(route('desk.show', $job))->assertOk();

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
        ])->assertRedirect(route('desk.show', $job))
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
