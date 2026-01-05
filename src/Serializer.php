<?php

namespace Phenogram\GatewayBindings;

use Phenogram\GatewayBindings\Types\Interfaces\DeliveryStatusInterface;
use Phenogram\GatewayBindings\Types\Interfaces\InputFileInterface;
use Phenogram\GatewayBindings\Types\Interfaces\RequestStatusInterface;
use Phenogram\GatewayBindings\Types\Interfaces\TypeInterface;
use Phenogram\GatewayBindings\Types\Interfaces\VerificationStatusInterface;

class Serializer implements SerializerInterface
{
    public function __construct(
        private readonly FactoryInterface $factory = new Factory(),
    ) {}


    public function denormalizeRequestStatus(array $data): RequestStatusInterface
    {
        $requiredFields = [
            'request_id',
            'phone_number',
            'request_cost',
        ];

        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (count($missingFields) > 0) {
            throw new \InvalidArgumentException(sprintf(
                'Class RequestStatus missing some fields from the data array: %s',
                implode(', ', $missingFields),
            ));
        }

        return $this->factory->makeRequestStatus(
            requestId: $data['request_id'],
            phoneNumber: $data['phone_number'],
            requestCost: $data['request_cost'],
            isRefunded: $data['is_refunded'] ?? null,
            remainingBalance: $data['remaining_balance'] ?? null,
            deliveryStatus: isset($data['delivery_status'])
                ? $this->denormalizeDeliveryStatus($data['delivery_status'])
                : null,
            verificationStatus: isset($data['verification_status'])
                ? $this->denormalizeVerificationStatus($data['verification_status'])
                : null,
            payload: $data['payload'] ?? null,
        );
    }


    public function denormalizeDeliveryStatus(array $data): DeliveryStatusInterface
    {
        $requiredFields = [
            'status',
            'updated_at',
        ];

        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (count($missingFields) > 0) {
            throw new \InvalidArgumentException(sprintf(
                'Class DeliveryStatus missing some fields from the data array: %s',
                implode(', ', $missingFields),
            ));
        }

        return $this->factory->makeDeliveryStatus(
            status: $data['status'],
            updatedAt: $data['updated_at'],
        );
    }


    public function denormalizeVerificationStatus(array $data): VerificationStatusInterface
    {
        $requiredFields = [
            'status',
            'updated_at',
        ];

        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (count($missingFields) > 0) {
            throw new \InvalidArgumentException(sprintf(
                'Class VerificationStatus missing some fields from the data array: %s',
                implode(', ', $missingFields),
            ));
        }

        return $this->factory->makeVerificationStatus(
            status: $data['status'],
            updatedAt: $data['updated_at'],
            codeEntered: $data['code_entered'] ?? null,
        );
    }


    public function serialize(array $data): array
    {
        return $this->normalize($data);
    }


    public function deserialize(mixed $data, string $type, bool $isArray = false): mixed
    {
        return is_array($data)
            ? $this->denormalize($data, $type, $isArray)
            : $data;
    }


    public function denormalize(array $data, string $type, bool $isArray = false): mixed
    {
        if (!interface_exists($type) || !is_subclass_of($type, TypeInterface::class)) {
            throw new \UnexpectedValueException(sprintf('Failed to decode response to the expected type: %s', $type));
        }

        if (!$isArray) {
            return $this->denormalizeType($data, $type);
        }

        return array_map(fn(array $item) => $this->denormalizeType($item, $type), $data);
    }


    private function denormalizeType(array $data, string $type): TypeInterface
    {
        return match ($type) {
            RequestStatusInterface::class => $this->denormalizeRequestStatus($data),
            DeliveryStatusInterface::class => $this->denormalizeDeliveryStatus($data),
            VerificationStatusInterface::class => $this->denormalizeVerificationStatus($data),
            default => throw new \InvalidArgumentException(sprintf('Unknown type %s', $type)),
        };
    }


    private function normalize(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_null($value)) {
                continue;
            }

            $snakeKey = $this->camelToSnake($key);

            if ($value instanceof TypeInterface && !$value instanceof InputFileInterface) {
                $value = get_object_vars($value);
            }

            if (is_array($value)) {
                $result[$snakeKey] = $this->normalize($value);
            } else {
                $result[$snakeKey] = $value;
            }
        }

        return $result;
    }


    private function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($input)));
    }
}
