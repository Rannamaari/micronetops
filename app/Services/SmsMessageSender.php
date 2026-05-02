<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\SmsMessage;

class SmsMessageSender
{
    public function __construct(
        private readonly DhiraaguSmsClient $smsClient
    ) {
    }

    public function send(SmsMessage $message): array
    {
        $destinations = array_values(array_unique(array_filter((array) $message->destinations)));

        if (count($destinations) === 0) {
            $message->update([
                'status' => SmsMessage::STATUS_FAILED,
                'error_message' => 'No valid phone numbers found to send SMS.',
                'failed_count' => 0,
                'sent_at' => now(),
            ]);

            return [
                'ok' => false,
                'sent' => 0,
                'failed' => 0,
                'message' => 'No valid phone numbers found to send SMS.',
            ];
        }

        $message->update([
            'status' => SmsMessage::STATUS_SENDING,
            'error_message' => null,
        ]);

        try {
            $result = $this->smsClient->send($destinations, (string) $message->content, $message->source);
        } catch (\Throwable $e) {
            $message->update([
                'status' => SmsMessage::STATUS_FAILED,
                'responses' => [[
                    'transactionId' => null,
                    'transactionStatus' => 'false',
                    'transactionDescription' => $e->getMessage(),
                    'referenceNumber' => '',
                ]],
                'failed_count' => count($destinations),
                'error_message' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            ActivityLog::record(
                'sms.failed',
                'SMS failed (' . $message->audience . '): ' . $e->getMessage(),
                $message
            );

            return [
                'ok' => false,
                'sent' => 0,
                'failed' => count($destinations),
                'message' => 'SMS failed: ' . $e->getMessage(),
            ];
        }

        $status = $result['sent'] > 0 ? SmsMessage::STATUS_SENT : SmsMessage::STATUS_FAILED;

        $message->update([
            'status' => $status,
            'responses' => $result['responses'],
            'sent_count' => (int) $result['sent'],
            'failed_count' => (int) $result['failed'],
            'error_message' => $result['ok'] ? null : 'Some messages failed to send.',
            'sent_at' => now(),
        ]);

        ActivityLog::record(
            $result['ok'] ? 'sms.sent' : 'sms.sent_with_errors',
            'SMS sent (' . $message->audience . '): ' . $result['sent'] . ' sent, ' . $result['failed'] . ' failed',
            $message
        );

        return [
            'ok' => $result['ok'],
            'sent' => (int) $result['sent'],
            'failed' => (int) $result['failed'],
            'message' => $result['ok']
                ? 'SMS sent successfully. Sent: ' . $result['sent'] . ', Failed: ' . $result['failed']
                : 'SMS sent with errors. Sent: ' . $result['sent'] . ', Failed: ' . $result['failed'],
        ];
    }
}
