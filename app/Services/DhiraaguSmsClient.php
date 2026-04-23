<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class DhiraaguSmsClient
{
    /**
     * @param array<int,string> $destinations
     * @return array{ok:bool, responses:array<int,array<string,mixed>>, sent:int, failed:int}
     */
    public function send(array $destinations, string $content, ?string $source = null): array
    {
        $destinations = array_values(array_unique(array_filter($destinations)));
        if (count($destinations) === 0) {
            throw new \InvalidArgumentException('No valid destinations to send.');
        }

        $baseUrl = rtrim((string) config('services.dhiraagu_sms.base_url'), '/');
        $authKey = (string) config('services.dhiraagu_sms.authorization_key');
        $defaultSource = (string) config('services.dhiraagu_sms.source');
        $dryRun = (bool) config('services.dhiraagu_sms.dry_run');
        $chunkSize = (int) config('services.dhiraagu_sms.chunk_size', 200);
        $timeout = (int) config('services.dhiraagu_sms.timeout', 20);

        if (!$dryRun && trim($authKey) === '') {
            throw new \RuntimeException('Dhiraagu SMS is not configured (missing DHIRAAGU_SMS_AUTH_KEY).');
        }

        $source = $source ?: $defaultSource;

        $responses = [];
        $sent = 0;
        $failed = 0;

        $chunks = array_chunk($destinations, max(1, $chunkSize));
        foreach ($chunks as $chunk) {
            if ($dryRun) {
                $responses[] = [
                    'transactionId' => 'dry-run-' . bin2hex(random_bytes(6)),
                    'transactionStatus' => 'true',
                    'transactionDescription' => 'DRY RUN: Message accepted (no external request).',
                    'referenceNumber' => '',
                    '_chunk_size' => count($chunk),
                ];
                $sent += count($chunk);
                continue;
            }

            try {
                $res = Http::timeout($timeout)
                    ->acceptJson()
                    ->asJson()
                    ->post($baseUrl . '/sms', [
                        'destination' => $chunk,
                        'content' => $content,
                        'source' => $source,
                        'authorizationKey' => $authKey,
                    ]);
            } catch (ConnectionException $e) {
                $failed += count($chunk);
                $responses[] = [
                    'transactionId' => null,
                    'transactionStatus' => 'false',
                    'transactionDescription' => 'Connection error: ' . $e->getMessage(),
                    'referenceNumber' => '',
                    '_chunk_size' => count($chunk),
                ];
                continue;
            }

            $json = $res->json();
            if (!is_array($json)) {
                $json = [
                    'transactionId' => null,
                    'transactionStatus' => 'false',
                    'transactionDescription' => 'Invalid response from SMS gateway.',
                    'referenceNumber' => '',
                ];
            }

            $json['_http_status'] = $res->status();
            $json['_chunk_size'] = count($chunk);
            $responses[] = $json;

            $ok = (string) ($json['transactionStatus'] ?? '') === 'true' && $res->successful();
            if ($ok) {
                $sent += count($chunk);
            } else {
                $failed += count($chunk);
            }
        }

        $ok = $failed === 0 && $sent > 0;

        return [
            'ok' => $ok,
            'responses' => $responses,
            'sent' => $sent,
            'failed' => $failed,
        ];
    }
}

