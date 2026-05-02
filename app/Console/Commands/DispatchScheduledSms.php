<?php

namespace App\Console\Commands;

use App\Models\SmsMessage;
use App\Services\SmsMessageSender;
use Illuminate\Console\Command;

class DispatchScheduledSms extends Command
{
    protected $signature = 'sms:dispatch-scheduled';

    protected $description = 'Send scheduled SMS messages that are due';

    public function handle(SmsMessageSender $sender): int
    {
        $dueMessages = SmsMessage::query()
            ->where('status', SmsMessage::STATUS_SCHEDULED)
            ->whereNotNull('scheduled_for')
            ->where('scheduled_for', '<=', now())
            ->orderBy('scheduled_for')
            ->limit(50)
            ->get();

        if ($dueMessages->isEmpty()) {
            $this->info('No scheduled SMS messages are due.');

            return self::SUCCESS;
        }

        foreach ($dueMessages as $message) {
            $result = $sender->send($message);

            $this->line(
                'SMS #' . $message->id . ': ' . ($result['message'] ?? 'Processed')
            );
        }

        $this->info('Processed ' . $dueMessages->count() . ' scheduled SMS message(s).');

        return self::SUCCESS;
    }
}
