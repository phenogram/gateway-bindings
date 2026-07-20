<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings;

use Phenogram\GatewayBindings\Types\Interfaces\GatewayResponseInterface;
use Phenogram\GatewayBindings\Types\Interfaces\ResponseInterface;

class ResponseException extends \RuntimeException
{
    public readonly ?string $gatewayError;

    public function __construct(public ResponseInterface $response)
    {
        $this->gatewayError = $response instanceof GatewayResponseInterface
            ? $response->error
            : $response->description;

        parent::__construct(
            sprintf(
                'Telegram Gateway API request failed: %s',
                $this->gatewayError ?? 'Unknown error',
            ),
            $response->errorCode ?? 0,
        );
    }
}
