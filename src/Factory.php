<?php

namespace Phenogram\GatewayBindings;

use Phenogram\GatewayBindings\Types\DeliveryStatus;
use Phenogram\GatewayBindings\Types\Interfaces\DeliveryStatusInterface;
use Phenogram\GatewayBindings\Types\Interfaces\RequestStatusInterface;
use Phenogram\GatewayBindings\Types\Interfaces\VerificationStatusInterface;
use Phenogram\GatewayBindings\Types\RequestStatus;
use Phenogram\GatewayBindings\Types\VerificationStatus;

class Factory implements FactoryInterface
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
    ): RequestStatusInterface {
        return new RequestStatus(
            requestId: $requestId,
            phoneNumber: $phoneNumber,
            requestCost: $requestCost,
            isRefunded: $isRefunded,
            remainingBalance: $remainingBalance,
            deliveryStatus: $deliveryStatus,
            verificationStatus: $verificationStatus,
            payload: $payload,
        );
    }


    public function makeDeliveryStatus(string $status, int $updatedAt): DeliveryStatusInterface
    {
        return new DeliveryStatus(
            status: $status,
            updatedAt: $updatedAt,
        );
    }


    public function makeVerificationStatus(
        string $status,
        int $updatedAt,
        ?string $codeEntered,
    ): VerificationStatusInterface {
        return new VerificationStatus(
            status: $status,
            updatedAt: $updatedAt,
            codeEntered: $codeEntered,
        );
    }
}
