<?php

namespace Phenogram\GatewayBindings\Types;

use Phenogram\GatewayBindings\Types\Interfaces\DeliveryStatusInterface;
use Phenogram\GatewayBindings\Types\Interfaces\VerificationStatusInterface;

/**
 * This object represents the status of a verification message request.
 */
class RequestStatus implements Interfaces\RequestStatusInterface
{
    /**
     * @param string $requestId Unique identifier of the verification request.
     * @param string $phoneNumber The phone number to which the verification code was sent, in the E.164 format.
     * @param float $requestCost Total request cost incurred by either checkSendAbility or sendVerificationMessage.
     * @param bool|null $isRefunded Optional. If True, the request fee was refunded.
     * @param float|null $remainingBalance Optional. Remaining balance in credits. Returned only in response to a request that incurs a charge.
     * @param DeliveryStatusInterface|null $deliveryStatus Optional. The current message delivery status. Returned only if a verification message was sent to the user.
     * @param VerificationStatusInterface|null $verificationStatus Optional. The current status of the verification process.
     * @param string|null $payload Optional. Custom payload if it was provided in the request, 0-256 bytes.
     */
    public function __construct(
        public string $requestId,
        public string $phoneNumber,
        public float $requestCost,
        public ?bool $isRefunded = null,
        public ?float $remainingBalance = null,
        public ?DeliveryStatusInterface $deliveryStatus = null,
        public ?VerificationStatusInterface $verificationStatus = null,
        public ?string $payload = null,
    ) {}
}
