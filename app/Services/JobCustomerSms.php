<?php

namespace App\Services;

use App\Models\Job;
use App\Models\JobNote;
use App\Models\SmsMessage;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Customer SMS for Job Desk:
 *  - "job completed" SMS, sent automatically once when work is completed
 *  - optional sign-off code, sent only when staff press "Send code" with the customer present
 *
 * Both go through the existing Dhiraagu SMS sender and are saved in the SMS log.
 * SMS problems never stop a job from being completed.
 */
class JobCustomerSms
{
    public const CODE_VALID_MINUTES = 30;
    public const CODE_MAX_SENDS = 3;
    public const CODE_MAX_ATTEMPTS = 5;
    public const CODE_RESEND_SECONDS = 60;

    public function __construct(private SmsMessageSender $sender)
    {
    }

    public static function completedText(Job $job): string
    {
        return "Micronet: Your job #{$job->id} is completed. Thank you! Your invoice will be sent shortly. "
            . 'If anything is not right, please call ' . config('job_desk.customer_contact_phone') . '.';
    }

    public static function codeText(Job $job, string $code): string
    {
        return "Micronet: Thank you! Job #{$job->id} is completed and the invoice will be sent to you. "
            . "If you are satisfied, please give this code to our staff to sign off: {$code}";
    }

    /**
     * Send the "job completed" SMS once. Returns true if an SMS was sent.
     */
    public function sendCompleted(Job $job): bool
    {
        if (!config('job_desk.completion_sms') || $job->completion_sms_sent_at !== null) {
            return false;
        }

        $phone = self::normalizePhone($job->customer_phone);
        if ($phone === null) {
            return false;
        }

        try {
            $result = $this->send($job, $phone, self::completedText($job), 'job_completed');
        } catch (\Throwable $e) {
            Log::warning("Job #{$job->id} completion SMS failed: " . $e->getMessage());

            return false;
        }

        if ($result['ok'] ?? false) {
            $job->forceFill(['completion_sms_sent_at' => now()])->save();
            $job->addNote('Completion SMS sent to customer (' . $phone . ')', null, JobNote::TYPE_SYSTEM);

            return true;
        }

        return false;
    }

    /**
     * Send a sign-off code to the customer's phone.
     *
     * @return array{ok:bool, error?:string}
     */
    public function sendCode(Job $job, ?User $user): array
    {
        $phone = self::normalizePhone($job->customer_phone);
        if ($phone === null) {
            return ['ok' => false, 'error' => 'no_phone'];
        }
        if ($job->signoff_code_sends >= self::CODE_MAX_SENDS) {
            return ['ok' => false, 'error' => 'too_many_sends'];
        }
        if ($job->signoff_code_sent_at && $job->signoff_code_sent_at->diffInSeconds(now()) < self::CODE_RESEND_SECONDS) {
            return ['ok' => false, 'error' => 'wait'];
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        try {
            $result = $this->send($job, $phone, self::codeText($job, $code), 'job_signoff_code', $user);
        } catch (\Throwable $e) {
            Log::warning("Job #{$job->id} sign-off code SMS failed: " . $e->getMessage());

            return ['ok' => false, 'error' => 'sms_failed'];
        }

        if (!($result['ok'] ?? false)) {
            return ['ok' => false, 'error' => 'sms_failed'];
        }

        $job->forceFill([
            'signoff_code_hash' => Hash::make($code),
            'signoff_code_expires_at' => now()->addMinutes(self::CODE_VALID_MINUTES),
            'signoff_code_sent_at' => now(),
            'signoff_code_sends' => $job->signoff_code_sends + 1,
            'signoff_code_attempts' => 0,
        ])->save();

        $job->addNote('Sign-off code sent to customer (' . $phone . ')', $user, JobNote::TYPE_SYSTEM);

        return ['ok' => true];
    }

    /**
     * Check the code the customer read out.
     *
     * @return array{ok:bool, error?:string}
     */
    public function verifyCode(Job $job, string $code, ?User $user): array
    {
        if ($job->signed_off_at) {
            return ['ok' => true];
        }
        if (!$job->signoff_code_hash || !$job->signoff_code_expires_at) {
            return ['ok' => false, 'error' => 'no_code'];
        }
        if ($job->signoff_code_expires_at->isPast()) {
            return ['ok' => false, 'error' => 'expired'];
        }
        if ($job->signoff_code_attempts >= self::CODE_MAX_ATTEMPTS) {
            return ['ok' => false, 'error' => 'too_many_attempts'];
        }

        $code = preg_replace('/\D/', '', $code);
        if (!Hash::check($code, $job->signoff_code_hash)) {
            $job->forceFill(['signoff_code_attempts' => $job->signoff_code_attempts + 1])->save();

            return ['ok' => false, 'error' => 'wrong'];
        }

        $job->forceFill([
            'signed_off_at' => now(),
            'signed_off_phone' => self::normalizePhone($job->customer_phone),
            'signoff_code_hash' => null,
            'signoff_code_expires_at' => null,
        ])->save();

        $job->addNote('Customer confirmed the work is done and satisfied (SMS code)', $user, JobNote::TYPE_SIGNED_OFF);

        return ['ok' => true];
    }

    /**
     * Maldives numbers: 7 digits (or 960 + 7 digits) -> 960XXXXXXX. Anything else -> null.
     */
    public static function normalizePhone(?string $phone): ?string
    {
        $phone = trim((string) $phone);
        if ($phone === '' || preg_match('/[[:alpha:]]/u', $phone) || !preg_match('/^[0-9\s()+-]+$/', $phone)) {
            return null;
        }

        $digits = (string) preg_replace('/\D+/', '', $phone);
        if (strlen($digits) === 7) {
            $digits = '960' . $digits;
        }

        return (strlen($digits) === 10 && str_starts_with($digits, '960')) ? $digits : null;
    }

    private function send(Job $job, string $phone, string $text, string $audience, ?User $user = null): array
    {
        $message = SmsMessage::create([
            'user_id' => $user?->id,
            'audience' => $audience,
            'status' => SmsMessage::STATUS_DRAFT,
            'content' => $text,
            'source' => (string) config('services.dhiraagu_sms.source', 'Micronet'),
            'destinations' => [$phone],
            'destinations_count' => 1,
            'invalid_destinations' => [],
            'invalid_count' => 0,
        ]);

        return $this->sender->send($message);
    }
}
