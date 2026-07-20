<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings\Tests\Client;

use Phenogram\GatewayBindings\Api;
use Phenogram\GatewayBindings\ClientInterface;
use Phenogram\GatewayBindings\Serializer;
use Phenogram\GatewayBindings\Tests\TestCase;
use Phenogram\GatewayBindings\Types\Interfaces\ResponseInterface;
use Phenogram\GatewayBindings\Types\Response;
use PHPUnit\Framework\Assert;

final class MockClientTest extends TestCase
{
    public function testCheckSendAbility(): void
    {
        $client = new class implements ClientInterface {
            public string $method = '';

            /** @var array<string, mixed> */
            public array $data = [];

            public function sendRequest(string $method, array $data): ResponseInterface
            {
                $this->method = $method;
                $this->data = $data;

                return new Response(
                    ok: true,
                    result: [
                        'request_id' => 'req_test',
                        'phone_number' => '+11111111',
                        'request_cost' => 0,
                    ],
                );
            }
        };

        $status = new Api($client, new Serializer())->checkSendAbility('+11111111');

        self::assertSame('req_test', $status->requestId);
        self::assertSame(0.0, $status->requestCost);
        self::assertSame('checkSendAbility', $client->method);
        self::assertSame(['phone_number' => '+11111111'], $client->data);
    }

    public function testSendVerificationMessageOmitsNullArguments(): void
    {
        $client = new class implements ClientInterface {
            /** @var array<string, mixed> */
            public array $data = [];

            public function sendRequest(string $method, array $data): ResponseInterface
            {
                Assert::assertSame('sendVerificationMessage', $method);
                $this->data = $data;

                return new Response(
                    ok: true,
                    result: [
                        'request_id' => 'req_send',
                        'phone_number' => '+12025550123',
                        'request_cost' => 0.01,
                    ],
                );
            }
        };

        new Api($client)->sendVerificationMessage(
            phoneNumber: '+12025550123',
            codeLength: 6,
            ttl: 60,
        );

        self::assertSame([
            'phone_number' => '+12025550123',
            'code_length' => 6,
            'ttl' => 60,
        ], $client->data);
    }

    public function testCheckVerificationStatusSerializesTheCode(): void
    {
        $client = new class implements ClientInterface {
            /** @var array<string, mixed> */
            public array $data = [];

            public function sendRequest(string $method, array $data): ResponseInterface
            {
                Assert::assertSame('checkVerificationStatus', $method);
                $this->data = $data;

                return new Response(
                    ok: true,
                    result: [
                        'request_id' => 'req_status',
                        'phone_number' => '+12025550123',
                        'request_cost' => 0.01,
                    ],
                );
            }
        };

        new Api($client)->checkVerificationStatus('req_status', '123456');

        self::assertSame([
            'request_id' => 'req_status',
            'code' => '123456',
        ], $client->data);
    }

    public function testRevokeVerificationMessageReturnsBooleanResult(): void
    {
        $client = new class implements ClientInterface {
            public function sendRequest(string $method, array $data): ResponseInterface
            {
                Assert::assertSame('revokeVerificationMessage', $method);
                Assert::assertSame(['request_id' => 'req_revoke'], $data);

                return new Response(ok: true, result: true);
            }
        };

        self::assertTrue(new Api($client)->revokeVerificationMessage('req_revoke'));
    }

    public function testApiThrowsResponseExceptionForGatewayError(): void
    {
        $response = new Response(ok: false, error: 'PHONE_NUMBER_INVALID');
        $client = new class ($response) implements ClientInterface {
            public function __construct(private readonly ResponseInterface $response) {}

            public function sendRequest(string $method, array $data): ResponseInterface
            {
                return $this->response;
            }
        };

        try {
            new Api($client)->checkSendAbility('invalid');
            self::fail('The API did not throw ResponseException.');
        } catch (\Phenogram\GatewayBindings\ResponseException $exception) {
            self::assertSame($response, $exception->response);
            self::assertSame(
                'Telegram Gateway API request failed: PHONE_NUMBER_INVALID',
                $exception->getMessage(),
            );
        }
    }

    public function testApiRejectsSuccessfulResponseWithoutResult(): void
    {
        $client = new class implements ClientInterface {
            public function sendRequest(string $method, array $data): ResponseInterface
            {
                return new Response(
                    ok: true,
                );
            }
        };

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Telegram Gateway API returned no result for checkSendAbility.');

        new Api($client)->checkSendAbility('+12025550123');
    }
}
