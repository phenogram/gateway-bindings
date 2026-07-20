<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings\Types\Interfaces;

/**
 * The version 1.0 response contract.
 *
 * @deprecated Use GatewayResponseInterface for the Telegram Gateway API error field.
 */
interface ResponseInterface extends TypeInterface
{
    public bool $ok { set; get; }

    public mixed $result { set; get; }

    public ?int $errorCode { set; get; }

    public ?string $description { set; get; }

    public ?ResponseParametersInterface $parameters { set; get; }
}
