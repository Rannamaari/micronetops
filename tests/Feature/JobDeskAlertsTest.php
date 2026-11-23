<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\User;
use App\Services\JobDesk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobDeskAlertsTest extends TestCase
{
    use RefreshDatabase;

    private JobDesk $desk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->desk = new JobDesk();
        $this->travelTo(now()->setTime(10, 0));
    }

    private function makeJob(array $attrs = []): Job
    {
        return Job::create(array_merge([
            'job_type' => 'ac',
            'job_category' => 'general',
            'job_date' => now()->toDateString(),
            'title' => 'AC service',
            'customer_name' => 'Ahmed',
            'customer_phone' => '7771234',
            'status' => Job::STATUS_SCHEDULED,
            'priority' => Job::PRIORITY_NORMAL,
            'payment_status' => 'unpaid',
            'scheduled_at' => now()->addHours(5),
        ], $attrs))->fresh();
    }

    private function types(Job $job): array
    {
        return collect($this->desk->alertsFor($job->fresh()))->pluck('type')->all();
    }

    private function alert(Job $job, string $type): ?array
    {
        return collect($this->desk->alertsFor($job->fresh()))->firstWhere('type', $type);
    }

    public function test_job_with_nobody_assigned_is_flagged_for_admin(): void
    {
        $job = $this->makeJob();

        $alert = $this->alert($job, JobDesk::ALERT_UNASSIGNED);
        $this->assertNotNull($alert);
        $this->assertSame(JobDesk::SCOPE_ADMIN, $alert['scope']);
    }

    public function test_not_accepted_warns_at_30_minutes_and_escalates_at_60(): void
    {
        $tech = User::factory()->create(['role' => User::ROLE_AC_MECHANIC]);
        $job = $this->makeJob();
        $job->assignTechnicians([$tech->id]);

        $this->assertNotContains(JobDesk::ALERT_NOT_ACCEPTED, $this->types($job));

        $this->travel(31)->minutes();
        $alert = $this->alert($job, JobDesk::ALERT_NOT_ACCEPTED);
        $this->assertSame('warn', $alert['level']);
        $this->assertSame(JobDesk::SCOPE_ASSIGNEE, $alert['scope']);

        $this->travel(30)->minutes();
        $alert = $this->alert($job, JobDesk::ALERT_NOT_ACCEPTED);
        $this->assertSame('danger', $alert['level']);
        $this->assertSame(JobDesk::SCOPE_TEAM, $alert['scope']);
        $this->assertSame([$tech->name], $alert['meta']['names']);

        $job->fresh()->markAcceptedBy($tech);
        $this->assertNotContains(JobDesk::ALERT_NOT_ACCEPTED, $this->types($job));
    }

    public function test_starting_work_counts_as_accepting(): void
    {
        $tech = User::factory()->create(['role' => User::ROLE_AC_MECHANIC]);
        $job = $this->makeJob();
        $job->assignTechnicians([$tech->id]);

        $job->fresh()->updateStatus(Job::STATUS_IN_PROGRESS, $tech);

        $this->assertNotNull($job->fresh()->assignees->first()->pivot->accepted_at);
    }

    public function test_saving_the_assignee_list_keeps_existing_acceptance(): void
    {
        $a = User::factory()->create(['role' => User::ROLE_AC_MECHANIC]);
        $b = User::factory()->create(['role' => User::ROLE_AC_MECHANIC]);
        $job = $this->makeJob();
        $job->assignTechnicians([$a->id]);
        $job->fresh()->markAcceptedBy($a);

        $this->travel(2)->hours();
        $job->fresh()->assignTechnicians([$a->id, $b->id]);

        $assignees = $job->fresh()->assignees->keyBy('id');
        $this->assertNotNull($assignees[$a->id]->pivot->accepted_at);
        $this->assertNull($assignees[$b->id]->pivot->accepted_at);

        $job->fresh()->assignTechnicians([$b->id]);
        $this->assertSame([$b->id], $job->fresh()->assignees->pluck('id')->all());
    }

    public function test_due_soon_then_overdue_then_customer_call_escalates(): void
    {
        $tech = User::factory()->create(['role' => User::ROLE_AC_MECHANIC]);
        $job = $this->makeJob(['scheduled_at' => now()->addMinutes(90)]);
        $job->assignTechnicians([$tech->id]);
        $job->fresh()->markAcceptedBy($tech);

        $this->assertContains(JobDesk::ALERT_DUE_SOON, $this->types($job));
        $this->assertFalse($job->fresh()->owesCustomerCall());

        $this->travel(100)->minutes(); // 10 minutes late
        $types = $this->types($job);
        $this->assertContains(JobDesk::ALERT_OVERDUE, $types);
        $call = $this->alert($job, JobDesk::ALERT_CALL_CUSTOMER);
        $this->assertSame(JobDesk::SCOPE_ASSIGNEE, $call['scope']);
        $this->assertSame(Job::CONTACT_REASON_OVERDUE, $call['meta']['reason']);

        $this->travel(25)->minutes(); // 35 late
        $this->assertSame(JobDesk::SCOPE_TEAM, $this->alert($job, JobDesk::ALERT_CALL_CUSTOMER)['scope']);

        $this->travel(30)->minutes(); // 65 late
        $this->assertSame(JobDesk::SCOPE_ADMIN, $this->alert($job, JobDesk::ALERT_CALL_CUSTOMER)['scope']);
    }

    public function test_no_answer_keeps_the_call_open_and_informed_clears_it(): void
    {
        $job = $this->makeJob(['scheduled_at' => now()->subHour()]);
        $this->assertTrue($job->owesCustomerCall());

        $job->logCustomerContact(null, 'call', Job::CONTACT_OUTCOME_NO_ANSWER);
        $this->assertTrue($job->fresh()->owesCustomerCall());

        $job->fresh()->logCustomerContact(null, 'call', Job::CONTACT_OUTCOME_INFORMED, 'running_late', 'Will come at 2pm');
        $job = $job->fresh();
        $this->assertFalse($job->owesCustomerCall());
        // Still late, but the customer has been told
        $this->assertContains(JobDesk::ALERT_OVERDUE, $this->types($job));
        $this->assertNotContains(JobDesk::ALERT_CALL_CUSTOMER, $this->types($job));

        $note = $job->notes()->where('type', 'customer_contact')->latest('id')->first();
        $this->assertSame('Will come at 2pm', $note->metadata['note']);
        $this->assertStringContainsString('informed', $note->content);
    }

    public function test_rescheduling_moves_the_promised_time_and_keeps_slot_length(): void
    {
        $job = $this->makeJob([
            'status' => Job::STATUS_NEW,
            'scheduled_at' => now()->subHours(2),
            'scheduled_end_at' => now()->subHour(),
        ]);

        $newTime = now()->addDay()->setTime(15, 0);
        $job->logCustomerContact(null, 'whatsapp', Job::CONTACT_OUTCOME_RESCHEDULED, 'overloaded', null, $newTime);

        $job = $job->fresh();
        $this->assertTrue($job->scheduled_at->eq($newTime));
        $this->assertTrue($job->scheduled_end_at->eq($newTime->copy()->addHour()));
        $this->assertSame(Job::STATUS_SCHEDULED, $job->status);
        $this->assertFalse($job->isOverdue());
        $this->assertFalse($job->owesCustomerCall());
    }

    public function test_waiting_for_parts_requires_a_customer_call(): void
    {
        $job = $this->makeJob(['status' => Job::STATUS_IN_PROGRESS]);

        $job->updateStatus(Job::STATUS_WAITING_PARTS);

        $job = $job->fresh();
        $this->assertTrue($job->owesCustomerCall());
        $this->assertSame(Job::CONTACT_REASON_WAITING_PARTS, $job->customerCallReason());
    }

    public function test_cancelling_because_of_us_requires_a_call_but_customer_request_does_not(): void
    {
        $byUs = $this->makeJob();
        $byUs->cancel(Job::CANCEL_PARTS_UNAVAILABLE);
        $this->assertSame(Job::CONTACT_REASON_CANCELLED_BY_US, $byUs->fresh()->customerCallReason());

        $byCustomer = $this->makeJob();
        $byCustomer->cancel(Job::CANCEL_CUSTOMER_REQUEST);
        $this->assertFalse($byCustomer->fresh()->owesCustomerCall());
    }

    public function test_no_update_for_a_day_is_flagged(): void
    {
        $tech = User::factory()->create(['role' => User::ROLE_AC_MECHANIC]);
        $job = $this->makeJob(['scheduled_at' => now()->addDays(3)]);
        $job->assignTechnicians([$tech->id]);
        $job->fresh()->updateStatus(Job::STATUS_IN_PROGRESS, $tech);

        $this->travel(23)->hours();
        $this->assertNotContains(JobDesk::ALERT_NO_UPDATE, $this->types($job));

        $this->travel(2)->hours();
        $this->assertContains(JobDesk::ALERT_NO_UPDATE, $this->types($job));

        $job->fresh()->addNote('Still working, compressor ordered', $tech);
        $this->assertNotContains(JobDesk::ALERT_NO_UPDATE, $this->types($job));
    }

    public function test_completed_but_not_invoiced_is_flagged_after_a_day(): void
    {
        $job = $this->makeJob(['status' => Job::STATUS_IN_PROGRESS]);
        $job->updateStatus(Job::STATUS_COMPLETED);

        $this->assertNotContains(JobDesk::ALERT_NOT_INVOICED, $this->types($job));

        $this->travel(25)->hours();
        $this->assertContains(JobDesk::ALERT_NOT_INVOICED, $this->types($job));

        $job->fresh()->markInvoiced(null);
        $this->assertNotContains(JobDesk::ALERT_NOT_INVOICED, $this->types($job));
    }

    public function test_paid_jobs_are_never_flagged_as_not_invoiced(): void
    {
        $job = $this->makeJob([
            'status' => Job::STATUS_COMPLETED,
            'payment_status' => 'paid',
            'completed_at' => now()->subDays(3),
        ]);

        $this->assertNotContains(JobDesk::ALERT_NOT_INVOICED, $this->types($job));
    }

    public function test_business_unit_mapping_matches_staff_units(): void
    {
        $this->assertSame('cool', $this->makeJob(['job_type' => 'ac'])->businessUnit());
        $this->assertSame('moto', $this->makeJob(['job_type' => 'moto'])->businessUnit());
        $this->assertSame('it', $this->makeJob(['job_type' => 'it'])->businessUnit());
        $this->assertSame('easyfix', $this->makeJob(['job_type' => 'easyfix'])->businessUnit());
    }
}
