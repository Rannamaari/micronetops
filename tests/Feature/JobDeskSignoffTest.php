<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\SmsMessage;
use App\Models\User;
use App\Services\JobCustomerSms;
use App\Services\SmsMessageSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JobDeskSignoffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.dhiraagu_sms.dry_run' => true]);
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
            'status' => Job::STATUS_IN_PROGRESS,
            'priority' => Job::PRIORITY_NORMAL,
            'payment_status' => 'unpaid',
            'scheduled_at' => now()->addHours(1),
        ], $attrs))->fresh();
    }

    private function tech(): User
    {
        return User::factory()->create(['role' => User::ROLE_AC_MECHANIC]);
    }

    public function test_completing_a_job_sends_the_completed_sms_once(): void
    {
        $tech = $this->tech();
        $job = $this->makeJob();
        $job->assignTechnicians([$tech->id]);

        $this->actingAs($tech)->post(route('desk.status', $job), ['action' => 'complete'])->assertRedirect();

        $job->refresh();
        $this->assertSame(Job::STATUS_COMPLETED, $job->status);
        $this->assertNotNull($job->completion_sms_sent_at);

        $sms = SmsMessage::where('audience', 'job_completed')->sole();
        $this->assertSame(['9607771234'], $sms->destinations);
        $this->assertSame(
            "Micronet: Your job #{$job->id} is completed. Thank you! Your invoice will be sent shortly. If anything is not right, please call 7379493.",
            $sms->content
        );
        $this->assertSame(SmsMessage::STATUS_SENT, $sms->status);

        // Re-opening and completing again does not send a second SMS
        $job->updateStatus(Job::STATUS_IN_PROGRESS, $tech);
        $job->updateStatus(Job::STATUS_COMPLETED, $tech);
        $this->assertSame(1, SmsMessage::where('audience', 'job_completed')->count());
    }

    public function test_no_sms_without_a_valid_phone_and_completion_still_works(): void
    {
        $job = $this->makeJob(['customer_phone' => 'not given']);
        $job->updateStatus(Job::STATUS_COMPLETED);

        $this->assertSame(Job::STATUS_COMPLETED, $job->fresh()->status);
        $this->assertNull($job->fresh()->completion_sms_sent_at);
        $this->assertSame(0, SmsMessage::count());
    }

    public function test_sms_failure_never_blocks_completion(): void
    {
        $this->mock(SmsMessageSender::class)->shouldReceive('send')->andThrow(new \RuntimeException('Dhiraagu down'));

        $job = $this->makeJob();
        $job->updateStatus(Job::STATUS_COMPLETED);

        $this->assertSame(Job::STATUS_COMPLETED, $job->fresh()->status);
        $this->assertNull($job->fresh()->completion_sms_sent_at);
    }

    public function test_completion_sms_can_be_switched_off(): void
    {
        config(['job_desk.completion_sms' => false]);
        $job = $this->makeJob();
        $job->updateStatus(Job::STATUS_COMPLETED);

        $this->assertSame(0, SmsMessage::count());
    }

    public function test_phone_normalisation(): void
    {
        $this->assertSame('9607771234', JobCustomerSms::normalizePhone('777 1234'));
        $this->assertSame('9607771234', JobCustomerSms::normalizePhone('+960 777-1234'));
        $this->assertNull(JobCustomerSms::normalizePhone('12345'));
        $this->assertNull(JobCustomerSms::normalizePhone(null));
        $this->assertNull(JobCustomerSms::normalizePhone('call me'));
    }

    public function test_optional_code_signoff_flow(): void
    {
        $tech = $this->tech();
        $job = $this->makeJob(['status' => Job::STATUS_COMPLETED, 'completed_at' => now()]);
        $job->assignTechnicians([$tech->id]);

        // Panel is offered on a completed job
        $this->actingAs($tech)->get(route('desk.show', $job))->assertOk()
            ->assertSee('Confirm with customer code')->assertSee('7771234');

        $this->actingAs($tech)->post(route('desk.signoff.send', $job))->assertRedirect();
        $job->refresh();
        $this->assertNotNull($job->signoff_code_hash);
        $this->assertSame(1, $job->signoff_code_sends);
        $codeSms = SmsMessage::where('audience', 'job_signoff_code')->sole();
        $this->assertMatchesRegularExpression('/sign off: \d{6}$/', $codeSms->content);

        // Wrong code
        $this->actingAs($tech)->post(route('desk.signoff.verify', $job), ['code' => '000000'])
            ->assertSessionHasErrors('code');
        $this->assertNull($job->fresh()->signed_off_at);

        // Right code (read it from the SMS that was sent)
        preg_match('/(\d{6})$/', $codeSms->content, $m);
        $this->actingAs($tech)->post(route('desk.signoff.verify', $job), ['code' => $m[1]])
            ->assertSessionHas('success');

        $job->refresh();
        $this->assertNotNull($job->signed_off_at);
        $this->assertNull($job->signoff_code_hash);
        $this->assertTrue($job->notes()->where('type', 'signed_off')->exists());

        $this->actingAs($tech)->get(route('desk.show', $job))->assertSee('Customer confirmed')
            ->assertDontSee('Confirm with customer code');
        $this->actingAs($tech)->get(route('desk.done'))->assertSee('Customer confirmed');
    }

    public function test_code_limits(): void
    {
        $tech = $this->tech();
        $job = $this->makeJob(['status' => Job::STATUS_COMPLETED, 'completed_at' => now()]);
        $job->assignTechnicians([$tech->id]);

        $this->actingAs($tech)->post(route('desk.signoff.send', $job));
        // Too soon to resend
        $this->actingAs($tech)->post(route('desk.signoff.send', $job))->assertSessionHas('error');
        $this->assertSame(1, $job->fresh()->signoff_code_sends);

        // Expired code
        $this->travel(31)->minutes();
        $this->actingAs($tech)->post(route('desk.signoff.verify', $job), ['code' => '123456'])
            ->assertSessionHasErrors('code');

        // 5 wrong attempts lock the code
        $job->forceFill(['signoff_code_hash' => Hash::make('111111'), 'signoff_code_expires_at' => now()->addMinutes(10)])->save();
        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($tech)->post(route('desk.signoff.verify', $job), ['code' => '999999']);
        }
        $this->actingAs($tech)->post(route('desk.signoff.verify', $job), ['code' => '111111'])
            ->assertSessionHasErrors('code');
        $this->assertNull($job->fresh()->signed_off_at);
    }

    public function test_code_not_available_before_completion_or_for_other_staff(): void
    {
        $tech = $this->tech();
        $stranger = $this->tech();
        $job = $this->makeJob();
        $job->assignTechnicians([$tech->id]);

        $this->actingAs($tech)->post(route('desk.signoff.send', $job))->assertSessionHas('error');
        $this->assertSame(0, SmsMessage::where('audience', 'job_signoff_code')->count());

        $job->updateStatus(Job::STATUS_COMPLETED, $tech);
        $this->actingAs($stranger)->post(route('desk.signoff.send', $job))->assertForbidden();
    }

    public function test_unticking_send_sms_skips_the_completion_sms(): void
    {
        $tech = $this->tech();
        $job = $this->makeJob();
        $job->assignTechnicians([$tech->id]);

        $this->actingAs($tech)->get(route('desk.show', $job))->assertSee('Send SMS to customer');

        // Hidden 0 + unticked box sends only send_sms=0
        $this->actingAs($tech)->post(route('desk.status', $job), ['action' => 'complete', 'send_sms' => '0'])->assertRedirect();

        $job->refresh();
        $this->assertSame(Job::STATUS_COMPLETED, $job->status);
        $this->assertNull($job->completion_sms_sent_at);
        $this->assertSame(0, SmsMessage::count());
        $this->assertTrue($job->notes()->where('content', 'Completion SMS not sent (unticked)')->exists());
    }

    public function test_admin_page_tick_is_respected(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $job = $this->makeJob();

        $this->actingAs($admin)->patch(route('jobs.update-status', $job), ['status' => 'completed', 'send_sms' => '0']);
        $this->assertSame(0, SmsMessage::count());

        $job2 = $this->makeJob();
        $this->actingAs($admin)->patch(route('jobs.update-status', $job2), ['status' => 'completed', 'send_sms' => '1']);
        $this->assertSame(1, SmsMessage::where('audience', 'job_completed')->count());
    }
}
