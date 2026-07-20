<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings;

use Phenogram\GatewayBindings\Types\Interfaces\ResponseInterface;

interface ClientInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function sendRequest(string $method, array $data): ResponseInterface;
}
