<?php

namespace Phenogram\GatewayBindings;

use Phenogram\GatewayBindings\Types\Interfaces\DeliveryStatusInterface;
use Phenogram\GatewayBindings\Types\Interfaces\RequestStatusInterface;
use Phenogram\GatewayBindings\Types\Interfaces\VerificationStatusInterface;

interface FactoryInterface
{
    public function makeRequestStatus(
        string $requestId,
        string $phoneNumber,
        float $requestCost,
        ?bool $isRefunded,
        ?float $remainingBalance,
        ?DeliveryStatusInterface $deliveryStatus,
        ?VerificationStatusInterface $verificationStatus,
        ?string $payload,
    ): RequestStatusInterface;


    public function makeDeliveryStatus(string $status, int $updatedAt): DeliveryStatusInterface;


    public function makeVerificationStatus(
        string $status,
        int $updatedAt,
        ?string $codeEntered,
    ): VerificationStatusInterface;
}
