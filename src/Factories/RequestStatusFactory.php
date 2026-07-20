<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings\Factories;

use Phenogram\GatewayBindings\Types\Interfaces\DeliveryStatusInterface;
use Phenogram\GatewayBindings\Types\Interfaces\RequestStatusInterface;
use Phenogram\GatewayBindings\Types\Interfaces\VerificationStatusInterface;

class RequestStatusFactory extends AbstractFactory
{
    /**
     * Creates a new RequestStatus instance with default fake data.
     *
     * @param string|null $requestId Optional. Unique identifier of the verification request.
     * @param string|null $phoneNumber Optional. The phone number to which the verification code was sent, in the E.164 format.
     * @param float|null $requestCost Optional. Total request cost incurred by either checkSendAbility or sendVerificationMessage.
     * @param bool|null $isRefunded Optional. Optional. If True, the request fee was refunded.
     * @param float|null $remainingBalance Optional. Optional. Remaining balance in credits. Returned only in response to a request that incurs a charge.
     * @param \Phenogram\GatewayBindings\Types\Interfaces\DeliveryStatusInterface|null $deliveryStatus Optional. Optional. The current message delivery status. Returned only if a verification message was sent to the user.
     * @param \Phenogram\GatewayBindings\Types\Interfaces\VerificationStatusInterface|null $verificationStatus Optional. Optional. The current status of the verification process.
     * @param string|null $payload Optional. Optional. Custom payload if it was provided in the request, 0-256 bytes.
     */
    public static function make(
        ?string $requestId = null,
        ?string $phoneNumber = null,
        ?float $requestCost = null,
        ?bool $isRefunded = null,
        ?float $remainingBalance = null,
        ?DeliveryStatusInterface $deliveryStatus = null,
        ?VerificationStatusInterface $verificationStatus = null,
        ?string $payload = null,
    ): RequestStatusInterface {
        return self::factory()->makeRequestStatus(
            requestId: $requestId ?? bin2hex(random_bytes(8)),
            phoneNumber: $phoneNumber ?? '+10000000000',
            requestCost: $requestCost ?? 0.0,
            isRefunded: $isRefunded,
            remainingBalance: $remainingBalance,
            deliveryStatus: $deliveryStatus,
            verificationStatus: $verificationStatus,
            payload: $payload,
        );
    }
}
