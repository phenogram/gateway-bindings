<?php

namespace Phenogram\GatewayBindings;

use Phenogram\GatewayBindings\Types\Interfaces\ResponseInterface;

interface ClientInterface
{
	public function sendRequest(string $method, array $data): ResponseInterface;
}
