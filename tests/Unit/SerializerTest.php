<?php

namespace Phenogram\GatewayBindings\Tests\Unit;

use Phenogram\GatewayBindings\Serializer;
use Phenogram\GatewayBindings\Tests\TestCase;
use Phenogram\GatewayBindings\Types\Interfaces\RequestStatusInterface;
use Phenogram\GatewayBindings\Types\Interfaces\DeliveryStatusInterface;

class SerializerTest extends TestCase
{
    public function testDeserializeRequestStatus(): void
    {
        $data = [
            'request_id' => 'req_123',
            'phone_number' => '+1234567890',
            'request_cost' => 0.05,
            'is_refunded' => false,
            'delivery_status' => [
                'status' => 'sent',
                'updated_at' => 1700000000,
            ],
        ];

        $serializer = new Serializer();
        /** @var RequestStatusInterface $result */
        $result = $serializer->deserialize($data, RequestStatusInterface::class);

        self::assertInstanceOf(RequestStatusInterface::class, $result);
        self::assertEquals('req_123', $result->requestId);
        self::assertInstanceOf(DeliveryStatusInterface::class, $result->deliveryStatus);
        self::assertEquals('sent', $result->deliveryStatus->status);
    }

    public function testSerializeDeliveryStatus(): void
    {
        $serializer = new Serializer();
        $factory = new \Phenogram\GatewayBindings\Factory();

        $deliveryStatus = $factory->makeDeliveryStatus(
            status: 'delivered',
            updatedAt: 1700000000
        );

        $array = $serializer->serialize(['status' => $deliveryStatus]);

        self::assertEquals([
            'status' => [
                'status' => 'delivered',
                'updated_at' => 1700000000,
            ],
        ], $array);
    }
}
