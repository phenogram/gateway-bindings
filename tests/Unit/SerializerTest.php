<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings\Tests\Unit;

use Phenogram\GatewayBindings\Serializer;
use Phenogram\GatewayBindings\Tests\Fixtures\Foreign\RequestStatusInterface as ForeignRequestStatusInterface;
use Phenogram\GatewayBindings\Tests\TestCase;
use Phenogram\GatewayBindings\Types\Interfaces\DeliveryStatusInterface;
use Phenogram\GatewayBindings\Types\Interfaces\RequestStatusInterface;
use Phenogram\GatewayBindings\Types\Interfaces\VerificationStatusInterface;

final class SerializerTest extends TestCase
{
    public function testDeserializeRequestStatus(): void
    {
        $data = [
            'request_id' => 'req_123',
            'phone_number' => '+1234567890',
            'request_cost' => 0.05,
            'is_refunded' => false,
            'remaining_balance' => 4,
            'delivery_status' => [
                'status' => 'delivered',
                'updated_at' => 1700000000,
            ],
            'verification_status' => [
                'status' => 'code_valid',
                'updated_at' => 1700000001,
                'code_entered' => '123456',
            ],
            'payload' => 'order-42',
        ];

        $serializer = new Serializer();
        $result = $serializer->deserialize($data, RequestStatusInterface::class);

        self::assertInstanceOf(RequestStatusInterface::class, $result);
        self::assertSame('req_123', $result->requestId);
        self::assertSame(4.0, $result->remainingBalance);
        self::assertInstanceOf(DeliveryStatusInterface::class, $result->deliveryStatus);
        self::assertSame('delivered', $result->deliveryStatus->status);
        self::assertInstanceOf(VerificationStatusInterface::class, $result->verificationStatus);
        self::assertSame('code_valid', $result->verificationStatus->status);
        self::assertSame('123456', $result->verificationStatus->codeEntered);
        self::assertSame('order-42', $result->payload);
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

        self::assertSame([
            'status' => [
                'status' => 'delivered',
                'updated_at' => 1700000000,
            ],
        ], $array);
    }

    public function testSerializeKeepsListKeys(): void
    {
        $serializer = new Serializer();

        self::assertSame(
            ['values' => [['code_length' => 6], ['code_length' => 8]]],
            $serializer->serialize([
                'values' => [
                    ['codeLength' => 6],
                    ['codeLength' => 8],
                ],
            ]),
        );
    }

    public function testDeserializeListOfStatuses(): void
    {
        $serializer = new Serializer();
        $result = $serializer->deserialize([
            [
                'status' => 'sent',
                'updated_at' => 1700000000,
            ],
            [
                'status' => 'read',
                'updated_at' => 1700000001,
            ],
        ], DeliveryStatusInterface::class, true);

        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertContainsOnlyInstancesOf(DeliveryStatusInterface::class, $result);
    }

    public function testDeserializeBoolean(): void
    {
        self::assertTrue(new Serializer()->deserialize(true, 'bool'));
    }

    public function testDeserializeRejectsWrongScalarType(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Expected a bool response, got string.');

        new Serializer()->deserialize('true', 'bool');
    }

    public function testDeserializeRejectsAnUnrelatedInterfaceWithTheSameShortName(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Failed to decode response to the expected type');

        new Serializer()->deserialize([], ForeignRequestStatusInterface::class);
    }

    public function testDeserializeRejectsMissingRequiredField(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The response field "phone_number" is required.');

        new Serializer()->deserialize([
            'request_id' => 'req_123',
            'request_cost' => 0.01,
        ], RequestStatusInterface::class);
    }

    public function testDeserializeRejectsInvalidNestedFieldType(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'The response field "delivery_status" must be object or null, got string.',
        );

        new Serializer()->deserialize([
            'request_id' => 'req_123',
            'phone_number' => '+12025550123',
            'request_cost' => 0.01,
            'delivery_status' => 'sent',
        ], RequestStatusInterface::class);
    }
}
