<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings;

use Phenogram\GatewayBindings\Types\Response;

class ResponseException extends \RuntimeException
{
    public function __construct(public Response $response)
    {
        parent::__construct(
            sprintf(
                'Response from Telegram gateway API is not ok: %s',
                $response->description ?? 'Unknown error',
            ),
            $response->errorCode ?? 0
        );
    }
}
