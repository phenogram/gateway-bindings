<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings\Types\Interfaces;

/**
 * The response envelope from the Telegram Gateway API.
 *
 * A successful response has an `ok` value of true and a `result` field.
 * A failed response has an `ok` value of false and an `error` field.
 */
interface GatewayResponseInterface extends ResponseInterface
{
    public ?string $error { set; get; }
}
