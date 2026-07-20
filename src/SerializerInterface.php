<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings;

interface SerializerInterface
{
    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function serialize(array $data): array;

    /**
     * @param class-string|'bool'|'float'|'int'|'string' $type
     */
    public function deserialize(mixed $data, string $type, bool $isArray = false): mixed;
}
