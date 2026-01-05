<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings\Tests\Readme;

use Phenogram\GatewayBindings\Api;
use Phenogram\GatewayBindings\Tests\TestCase;
use Phenogram\GatewayBindings\Types\Interfaces\RequestStatusInterface;

class ReadmeClientTest extends TestCase
{
    private Api $api;
    private ?string $token;
    private ?string $testPhoneNumber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = $_ENV['GATEWAY_TOKEN'] ?? null;
        $this->testPhoneNumber = $_ENV['TEST_PHONE_NUMBER'] ?? null;

        if ($this->token === null) {
            $this->markTestSkipped('GATEWAY_TOKEN not set in .env or environment');
        }

        if ($this->testPhoneNumber === null) {
            $this->markTestSkipped('TEST_PHONE_NUMBER not set in .env or environment');
        }

        $this->api = new Api(
            client: new ReadmeClient(
                token: $this->token
            )
        );
    }

    public function testCheckSendAbility(): void
    {
        try {
            $status = $this->api->checkSendAbility($this->testPhoneNumber);

            self::assertInstanceOf(RequestStatusInterface::class, $status);
            self::assertNotNull($status->requestId);
        } catch (\Exception $e) {
            $this->fail('API Request failed: ' . $e->getMessage());
        }
    }
}
