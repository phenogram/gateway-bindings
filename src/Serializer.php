<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings;

use Phenogram\GatewayBindings\Types\Interfaces\DeliveryStatusInterface;
use Phenogram\GatewayBindings\Types\Interfaces\RequestStatusInterface;
use Phenogram\GatewayBindings\Types\Interfaces\TypeInterface;
use Phenogram\GatewayBindings\Types\Interfaces\VerificationStatusInterface;

class Serializer implements SerializerInterface
{
    private const array SUPPORTED_INTERFACES = [
        RequestStatusInterface::class,
        DeliveryStatusInterface::class,
        VerificationStatusInterface::class,
    ];

    public function __construct(
        private readonly FactoryInterface $factory = new Factory(),
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function denormalizeRequestStatus(array $data): RequestStatusInterface
    {
        $deliveryStatus = $this->optionalArray($data, 'delivery_status');
        $verificationStatus = $this->optionalArray($data, 'verification_status');

        return $this->factory->makeRequestStatus(
            requestId: $this->requiredString($data, 'request_id'),
            phoneNumber: $this->requiredString($data, 'phone_number'),
            requestCost: $this->requiredFloat($data, 'request_cost'),
            isRefunded: $this->optionalBool($data, 'is_refunded'),
            remainingBalance: $this->optionalFloat($data, 'remaining_balance'),
            deliveryStatus: $deliveryStatus !== null
                ? $this->denormalizeDeliveryStatus($deliveryStatus)
                : null,
            verificationStatus: $verificationStatus !== null
                ? $this->denormalizeVerificationStatus($verificationStatus)
                : null,
            payload: $this->optionalString($data, 'payload'),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function denormalizeDeliveryStatus(array $data): DeliveryStatusInterface
    {
        return $this->factory->makeDeliveryStatus(
            status: $this->requiredString($data, 'status'),
            updatedAt: $this->requiredInt($data, 'updated_at'),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function denormalizeVerificationStatus(array $data): VerificationStatusInterface
    {
        return $this->factory->makeVerificationStatus(
            status: $this->requiredString($data, 'status'),
            updatedAt: $this->requiredInt($data, 'updated_at'),
            codeEntered: $this->optionalString($data, 'code_entered'),
        );
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function serialize(array $data): array
    {
        $result = $this->normalize($data);

        /** @var array<string, mixed> $result */
        return $result;
    }

    public function deserialize(mixed $data, string $type, bool $isArray = false): mixed
    {
        if (in_array($type, ['bool', 'float', 'int', 'string'], true)) {
            if ($isArray) {
                throw new \InvalidArgumentException('Scalar arrays are not supported.');
            }

            return $this->deserializeScalar($data, $type);
        }

        if (!is_array($data)) {
            throw new \UnexpectedValueException(sprintf(
                'Failed to decode response to the expected type: %s.',
                $type,
            ));
        }

        return $this->denormalize($data, $type, $isArray);
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public function denormalize(array $data, string $type, bool $isArray = false): mixed
    {
        if (!in_array($type, self::SUPPORTED_INTERFACES, true)) {
            throw new \UnexpectedValueException(sprintf('Failed to decode response to the expected type: %s', $type));
        }

        if (!$isArray) {
            return $this->denormalizeType($this->objectArray($data), $type);
        }

        $result = [];

        foreach ($data as $item) {
            if (!is_array($item)) {
                throw new \UnexpectedValueException('A response list contains a non-object value.');
            }

            $result[] = $this->denormalizeType($this->objectArray($item), $type);
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function denormalizeType(array $data, string $type): TypeInterface
    {
        return match ($type) {
            RequestStatusInterface::class => $this->denormalizeRequestStatus($data),
            DeliveryStatusInterface::class => $this->denormalizeDeliveryStatus($data),
            VerificationStatusInterface::class => $this->denormalizeVerificationStatus($data),
            default => throw new \InvalidArgumentException(sprintf('Unknown type %s', $type)),
        };
    }

    /**
     * @param  array<array-key, mixed> $data
     * @return array<array-key, mixed>
     */
    private function normalize(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            $normalizedKey = is_string($key) ? $this->camelToSnake($key) : $key;

            if ($value instanceof TypeInterface) {
                $value = get_object_vars($value);
            }

            if (is_array($value)) {
                $result[$normalizedKey] = $this->normalize($value);
            } else {
                $result[$normalizedKey] = $value;
            }
        }

        return $result;
    }

    private function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($input)) ?? $input);
    }

    private function deserializeScalar(mixed $data, string $type): bool|float|int|string
    {
        $isExpectedType = match ($type) {
            'bool' => is_bool($data),
            'float' => is_float($data),
            'int' => is_int($data),
            'string' => is_string($data),
            default => false,
        };

        if (!$isExpectedType) {
            throw new \UnexpectedValueException(sprintf(
                'Expected a %s response, got %s.',
                $type,
                get_debug_type($data),
            ));
        }

        /** @var bool|float|int|string $data */
        return $data;
    }

    /**
     * @param  array<array-key, mixed> $data
     * @return array<string, mixed>
     */
    private function objectArray(array $data): array
    {
        foreach (array_keys($data) as $key) {
            if (!is_string($key)) {
                throw new \UnexpectedValueException('Expected a JSON object with string keys.');
            }
        }

        /** @var array<string, mixed> $data */
        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requiredString(array $data, string $field): string
    {
        $value = $this->requiredValue($data, $field);

        if (!is_string($value)) {
            throw $this->invalidFieldType($field, 'string', $value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requiredInt(array $data, string $field): int
    {
        $value = $this->requiredValue($data, $field);

        if (!is_int($value)) {
            throw $this->invalidFieldType($field, 'int', $value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requiredFloat(array $data, string $field): float
    {
        $value = $this->requiredValue($data, $field);

        if (!is_float($value) && !is_int($value)) {
            throw $this->invalidFieldType($field, 'float', $value);
        }

        return (float) $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requiredValue(array $data, string $field): mixed
    {
        if (!array_key_exists($field, $data) || $data[$field] === null) {
            throw new \InvalidArgumentException(sprintf(
                'The response field "%s" is required.',
                $field,
            ));
        }

        return $data[$field];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function optionalString(array $data, string $field): ?string
    {
        $value = $data[$field] ?? null;

        if ($value !== null && !is_string($value)) {
            throw $this->invalidFieldType($field, 'string or null', $value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function optionalBool(array $data, string $field): ?bool
    {
        $value = $data[$field] ?? null;

        if ($value !== null && !is_bool($value)) {
            throw $this->invalidFieldType($field, 'bool or null', $value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function optionalFloat(array $data, string $field): ?float
    {
        $value = $data[$field] ?? null;

        if ($value !== null && !is_float($value) && !is_int($value)) {
            throw $this->invalidFieldType($field, 'float or null', $value);
        }

        return $value !== null ? (float) $value : null;
    }

    /**
     * @param  array<string, mixed>      $data
     * @return array<string, mixed>|null
     */
    private function optionalArray(array $data, string $field): ?array
    {
        $value = $data[$field] ?? null;

        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw $this->invalidFieldType($field, 'object or null', $value);
        }

        return $this->objectArray($value);
    }

    private function invalidFieldType(string $field, string $expected, mixed $value): \UnexpectedValueException
    {
        return new \UnexpectedValueException(sprintf(
            'The response field "%s" must be %s, got %s.',
            $field,
            $expected,
            get_debug_type($value),
        ));
    }
}
