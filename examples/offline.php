<?php

declare(strict_types=1);

use Phenogram\GatewayBindings\Api;
use Phenogram\GatewayBindings\Examples\CurlClient;

require dirname(__DIR__) . '/vendor/autoload.php';

$client = new CurlClient(
    token: 'offline-example-token',
    transport: static function (string $method, array $data): array {
        if ($method !== 'checkVerificationStatus') {
            throw new RuntimeException('The example called an unexpected API method.');
        }

        if (($data['request_id'] ?? null) !== 'request-demo') {
            throw new RuntimeException('The example sent an unexpected request ID.');
        }

        return [
            'status' => 200,
            'body' => json_encode([
                'ok' => true,
                'result' => [
                    'request_id' => 'request-demo',
                    'phone_number' => '+12025550123',
                    'request_cost' => 0.01,
                    'verification_status' => [
                        'status' => 'code_valid',
                        'updated_at' => 1_750_000_000,
                        'code_entered' => '123456',
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        ];
    },
);

$status = new Api($client)->checkVerificationStatus(
    requestId: 'request-demo',
    code: '123456',
);

if ($status->verificationStatus === null) {
    throw new RuntimeException('The offline response has no verification status.');
}

printf(
    "Request %s: %s\n",
    $status->requestId,
    $status->verificationStatus->status,
);
