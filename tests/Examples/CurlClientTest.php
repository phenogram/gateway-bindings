<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings\Tests\Examples;

use Phenogram\GatewayBindings\Examples\CurlClient;
use Phenogram\GatewayBindings\Tests\TestCase;

final class CurlClientTest extends TestCase
{
    public function testInjectedTransportKeepsTheExampleOffline(): void
    {
        $client = new CurlClient(
            token: 'secret',
            transport: static function (string $method, array $data): array {
                self::assertSame('checkSendAbility', $method);
                self::assertSame(['phone_number' => '+12025550123'], $data);

                return [
                    'status' => 200,
                    'body' => '{"ok":true,"result":{"request_id":"req_1"}}',
                ];
            },
        );

        $response = $client->sendRequest(
            'checkSendAbility',
            ['phone_number' => '+12025550123'],
        );

        self::assertTrue($response->ok);
        self::assertSame(['request_id' => 'req_1'], $response->result);
        self::assertNull($response->error);
    }

    public function testDecodeGatewayError(): void
    {
        $response = CurlClient::decodeResponse(
            '{"ok":false,"error":"PHONE_NUMBER_INVALID"}',
            400,
        );

        self::assertFalse($response->ok);
        self::assertSame('PHONE_NUMBER_INVALID', $response->error);
    }

    public function testDecodeRejectsInvalidJson(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Telegram Gateway API returned invalid JSON (HTTP 502).');

        CurlClient::decodeResponse('<html>bad gateway</html>', 502);
    }

    public function testDecodeRejectsFailedEnvelopeWithoutError(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Telegram Gateway API returned an error without an error field.',
        );

        CurlClient::decodeResponse('{"ok":false}', 400);
    }
}
