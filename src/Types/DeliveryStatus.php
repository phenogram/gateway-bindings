<?php

namespace Phenogram\GatewayBindings\Types;

/**
 * This object represents the delivery status of a message.
 */
class DeliveryStatus implements Interfaces\DeliveryStatusInterface
{
    /**
     * @param string $status The current status of the message. One of the following:- sent – the message has been sent to the recipient's device(s),- delivered – the message has been delivered to the recipient's device(s),- read – the message has been read by the recipient,- expired – the message has expired without being delivered or read,- revoked – the message has been revoked.
     * @param int $updatedAt The timestamp when the status was last updated.
     */
    public function __construct(
        public string $status,
        public int $updatedAt,
    ) {}
}
