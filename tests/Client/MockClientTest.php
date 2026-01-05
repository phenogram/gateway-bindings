<?php

namespace Phenogram\GatewayBindings\Tests\Client;

use Phenogram\GatewayBindings\Api;
use Phenogram\GatewayBindings\ClientInterface;
use Phenogram\GatewayBindings\Serializer;
use Phenogram\GatewayBindings\Tests\TestCase;
use Phenogram\GatewayBindings\Types\Response;

class MockClientTest extends TestCase
{
    public function testCheckSendAbility(): void
    {
        // 1. Mock the Client
        $client = $this->createMock(ClientInterface::class);

        // 2. Define expected API response
        $apiResponse = [
            'request_id' => 'req_test',
            'phone_number' => '+11111111',
            'request_cost' => 0.0,
        ];

        // 3. Configure Mock
        $client->expects(self::once())
            ->method('sendRequest')
            ->willReturn(new Response(
                ok: true,
                result: $apiResponse
            ));

        // 4. Run API call
        $api = new Api($client, new Serializer());
        $status = $api->checkSendAbility('+11111111');

        self::assertEquals('req_test', $status->requestId);
    }
}
