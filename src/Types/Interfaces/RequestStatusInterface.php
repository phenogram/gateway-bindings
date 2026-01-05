<?php

namespace Phenogram\GatewayBindings\Types\Interfaces;

/**
 * This object represents the status of a verification message request.
 */
interface RequestStatusInterface extends TypeInterface
{
    /** @var string $requestId Unique identifier of the verification request. */
    public string $requestId { set; get; }

    /** @var string $phoneNumber The phone number to which the verification code was sent, in the E.164 format. */
    public string $phoneNumber { set; get; }

    /** @var float $requestCost Total request cost incurred by either checkSendAbility or sendVerificationMessage. */
    public float $requestCost { set; get; }

    /** @var bool|null $isRefunded Optional. If True, the request fee was refunded. */
    public ?bool $isRefunded { set; get; }

    /** @var float|null $remainingBalance Optional. Remaining balance in credits. Returned only in response to a request that incurs a charge. */
    public ?float $remainingBalance { set; get; }

    /** @var DeliveryStatusInterface|null $deliveryStatus Optional. The current message delivery status. Returned only if a verification message was sent to the user. */
    public ?DeliveryStatusInterface $deliveryStatus { set; get; }

    /** @var VerificationStatusInterface|null $verificationStatus Optional. The current status of the verification process. */
    public ?VerificationStatusInterface $verificationStatus { set; get; }

    /** @var string|null $payload Optional. Custom payload if it was provided in the request, 0-256 bytes. */
    public ?string $payload { set; get; }
}
