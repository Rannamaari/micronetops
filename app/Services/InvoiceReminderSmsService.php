<?php

namespace App\Services;

use App\Models\SmsMessage;
use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceReminderMail;

class InvoiceReminderSmsService
{
    public function __construct(
        private readonly SmsMessageSender $sender
    ) {
    }

    public function send(Job $job, ?User $user = null): array
    {
        $destination = $this->normalizeDestination((string) ($job->customer_phone ?? ''));
        if (!$destination) {
            throw new \RuntimeException('This invoice does not have a valid customer phone number for SMS.');
        }

        $account = $this->paymentDetailsForJob($job);
        if (blank($account['account_number'] ?? null)) {
            throw new \RuntimeException('No reminder deposit account number is configured for this business line yet.');
        }

        $message = SmsMessage::create([
            'user_id' => $user?->id,
            'audience' => 'invoice_reminder',
            'status' => SmsMessage::STATUS_DRAFT,
            'source' => (string) config('services.dhiraagu_sms.source', 'Micronet'),
            'content' => $this->buildMessage($job, $account),
            'scheduled_for' => null,
            'destinations' => [$destination],
            'destinations_count' => 1,
            'invalid_destinations' => [],
            'invalid_count' => 0,
            'responses' => null,
            'sent_count' => 0,
            'failed_count' => 0,
            'error_message' => null,
            'sent_at' => null,
        ]);

        return $this->sender->send($message);
    }

    public function sendEmail(Job $job): void
    {
        $email = trim((string) ($job->customer_email ?? ''));
        if ($email === '') {
            throw new \RuntimeException('This invoice does not have a customer email address for reminders.');
        }

        $account = $this->paymentDetailsForJob($job);
        if (blank($account['account_number'] ?? null)) {
            throw new \RuntimeException('No reminder deposit account number is configured for this business line yet.');
        }

        Mail::to($email)->send(new InvoiceReminderMail(
            job: $job,
            paymentDetails: $account,
            invoiceNumber: $this->invoiceNumber($job),
            invoiceDate: $this->invoiceDate($job)
        ));
    }

    public function paymentDetailsForJob(Job $job): array
    {
        $account = config('invoice_reminders.accounts.' . $job->job_type, []);

        return [
            'label' => $account['label'] ?? 'Micronet',
            'account_name' => $account['account_name'] ?? 'Micronet',
            'account_number' => $account['account_number'] ?? '',
            'whatsapp' => '9996210',
        ];
    }

    private function buildMessage(Job $job, array $account): string
    {
        $invoiceNumber = $this->invoiceNumber($job);
        $invoiceDate = $this->invoiceDate($job)->format('d M Y');
        $amount = number_format((float) $job->balance_amount, 2);

        $message = $account['label'] . ' reminder: Invoice ' . $invoiceNumber
            . ' dated ' . $invoiceDate
            . ' for MVR ' . $amount;

        if ($job->due_date) {
            $dueDate = $job->due_date->copy()->timezone(config('app.timezone'));
            $today = now()->timezone(config('app.timezone'))->startOfDay();
            $dueDay = $dueDate->copy()->startOfDay();

            if ($today->gt($dueDay)) {
                $daysOverdue = $dueDay->diffInDays($today);
                $message .= ' was due on ' . $dueDate->format('d M Y')
                    . ' and is overdue by ' . $daysOverdue . ' day' . ($daysOverdue === 1 ? '' : 's');
            } else {
                $message .= ' is due on ' . $dueDate->format('d M Y');
            }
        } else {
            $message .= ' is due upon receipt';
        }

        $message .= '. Please deposit to ' . ($account['account_name'] ?: $account['label'])
            . ' account ' . $account['account_number']
            . ' and share your transfer reference. If you have already made the payment, please disregard this message. Thank you.';

        return $message;
    }

    public function invoiceNumber(Job $job): string
    {
        return 'JOB-' . str_pad((string) $job->id, 5, '0', STR_PAD_LEFT);
    }

    public function invoiceDate(Job $job)
    {
        return ($job->completed_at ?? $job->created_at ?? now())->copy()->timezone(config('app.timezone'));
    }

    private function normalizeDestination(string $token): ?string
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }

        if (preg_match('/[[:alpha:]]/u', $token)) {
            return null;
        }

        if (!preg_match('/^[0-9\\s()+-]+$/', $token)) {
            return null;
        }

        $digits = (string) preg_replace('/\\D+/', '', $token);
        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 7) {
            $digits = '960' . $digits;
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '960')) {
            return $digits;
        }

        return null;
    }
}
