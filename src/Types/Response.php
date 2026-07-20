<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings\Types;

/**
 * The response envelope from the Telegram Gateway API.
 *
 * The last four constructor arguments keep source compatibility with version
 * 1.0. New code must use the `error` argument for a Gateway API error.
 */
class Response implements Interfaces\GatewayResponseInterface
{
    public ?string $error;

    /**
     * @param int|null                                   $errorCode   Deprecated. The Gateway API has no error_code field.
     * @param string|null                                $description Deprecated. The Gateway API uses the error field.
     * @param Interfaces\ResponseParametersInterface|null $parameters Deprecated. The Gateway API has no parameters field.
     */
    public function __construct(
        public bool $ok,
        public mixed $result = null,
        public ?int $errorCode = null,
        public ?string $description = null,
        public ?Interfaces\ResponseParametersInterface $parameters = null,
        ?string $error = null,
    ) {
        $this->error = $error ?? $description;
    }
}
