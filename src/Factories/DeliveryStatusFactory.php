<?php

namespace Phenogram\GatewayBindings\Factories;

use Phenogram\GatewayBindings\Types\DeliveryStatus;
use Phenogram\GatewayBindings\Types\Interfaces\DeliveryStatusInterface;

class DeliveryStatusFactory extends AbstractFactory
{
    /**
     * Creates a new DeliveryStatus instance with default fake data.
     *
     * @param string|null $status Optional. The current status of the message. One of the following:- sent – the message has been sent to the recipient's device(s),- delivered – the message has been delivered to the recipient's device(s),- read – the message has been read by the recipient,- expired – the message has expired without being delivered or read,- revoked – the message has been revoked.
     * @param int|null $updatedAt Optional. The timestamp when the status was last updated.
     */
    public static function make(?string $status = null, ?int $updatedAt = null): DeliveryStatusInterface
    {
        return self::factory()->makeDeliveryStatus(
            status: $status ?? self::fake()->word(),
            updatedAt: $updatedAt ?? self::fake()->randomNumber(),
        );
    }
}
