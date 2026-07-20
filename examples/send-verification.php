<?php

declare(strict_types=1);

use Phenogram\GatewayBindings\Api;
use Phenogram\GatewayBindings\Examples\CurlClient;

require dirname(__DIR__) . '/vendor/autoload.php';

$live = in_array('--live', $argv, true);
$phoneNumber = '+12025550123';

if ($live) {
    $token = getenv('TELEGRAM_GATEWAY_TOKEN');
    $configuredPhoneNumber = getenv('TELEGRAM_GATEWAY_PHONE');

    if ($token === false || $token === '') {
        throw new RuntimeException('Set TELEGRAM_GATEWAY_TOKEN before you use --live.');
    }

    if ($configuredPhoneNumber === false || $configuredPhoneNumber === '') {
        throw new RuntimeException('Set TELEGRAM_GATEWAY_PHONE before you use --live.');
    }

    $phoneNumber = $configuredPhoneNumber;
    $client = new CurlClient($token);
} else {
    $client = new CurlClient(
        token: 'offline-example-token',
        transport: static function (string $method, array $data): array {
            if ($method !== 'sendVerificationMessage') {
                throw new RuntimeException('The example called an unexpected API method.');
            }

            return [
                'status' => 200,
                'body' => json_encode([
                    'ok' => true,
                    'result' => [
                        'request_id' => 'request-demo',
                        'phone_number' => $data['phone_number'],
                        'request_cost' => 0.01,
                        'remaining_balance' => 9.99,
                        'delivery_status' => [
                            'status' => 'sent',
                            'updated_at' => 1_750_000_000,
                        ],
                    ],
                ], JSON_THROW_ON_ERROR),
            ];
        },
    );
}

$status = new Api($client)->sendVerificationMessage(
    phoneNumber: $phoneNumber,
    codeLength: 6,
    ttl: 60,
);

printf(
    "%s request %s for %s\n",
    $live ? 'Created live' : 'Simulated',
    $status->requestId,
    $status->phoneNumber,
);
