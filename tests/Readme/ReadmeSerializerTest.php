<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings\Tests\Readme;

use Phenogram\GatewayBindings\Serializer;
use Phenogram\GatewayBindings\Tests\TestCase;
use Phenogram\GatewayBindings\Types\Interfaces\RequestStatusInterface;
use Phenogram\GatewayBindings\Types\Interfaces\DeliveryStatusInterface;

class ReadmeSerializerTest extends TestCase
{
    public function testSerializationExample(): void
    {
        $serializer = new Serializer();

        // 1. Serialize a RequestStatus (Simulated)
        // Usually you deserialize responses, but serialization is symmetrical
        // This is just to show structure.

        // 2. Deserialize a JSON response from Telegram
        $apiResponseResult = [
            'request_id' => 'req_abc123',
            'phone_number' => '+19999999999',
            'request_cost' => 0.05,
            'delivery_status' => [
                'status' => 'sent',
                'updated_at' => 1700000000,
            ],
        ];

        $status = $serializer->deserialize(
            data: $apiResponseResult,
            type: RequestStatusInterface::class,
        );

        self::assertInstanceOf(RequestStatusInterface::class, $status);
        self::assertEquals('req_abc123', $status->requestId);
        self::assertInstanceOf(DeliveryStatusInterface::class, $status->deliveryStatus);
    }
}
