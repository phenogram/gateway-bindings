<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings\Tests\Unit;

use Phenogram\GatewayBindings\ResponseException;
use Phenogram\GatewayBindings\Tests\TestCase;
use Phenogram\GatewayBindings\Types\Interfaces\GatewayResponseInterface;
use Phenogram\GatewayBindings\Types\Interfaces\ResponseInterface;
use Phenogram\GatewayBindings\Types\Interfaces\ResponseParametersInterface;
use Phenogram\GatewayBindings\Types\Response;

final class ResponseTest extends TestCase
{
    public function testGatewayResponseUsesErrorField(): void
    {
        $response = new Response(
            ok: false,
            error: 'ACCESS_TOKEN_INVALID',
        );

        self::assertFalse($response->ok);
        self::assertNull($response->result);
        self::assertSame('ACCESS_TOKEN_INVALID', $response->error);
    }

    public function testLegacyDescriptionMapsToError(): void
    {
        $response = new Response(
            ok: false,
            errorCode: 401,
            description: 'ACCESS_TOKEN_INVALID',
        );

        self::assertSame('ACCESS_TOKEN_INVALID', $response->error);
        self::assertSame(401, $response->errorCode);
        self::assertSame(401, new ResponseException($response)->getCode());
    }

    public function testResponseExceptionAcceptsAnyResponseInterface(): void
    {
        $response = new class implements GatewayResponseInterface {
            public bool $ok = false;
            public mixed $result = null;
            public ?int $errorCode = null;
            public ?string $description = null;
            public ?ResponseParametersInterface $parameters = null;
            public ?string $error = 'CUSTOM_GATEWAY_ERROR';
        };

        $exception = new ResponseException($response);

        self::assertSame($response, $exception->response);
        self::assertSame(
            'Telegram Gateway API request failed: CUSTOM_GATEWAY_ERROR',
            $exception->getMessage(),
        );
        self::assertSame('CUSTOM_GATEWAY_ERROR', $exception->gatewayError);
    }

    public function testResponseExceptionPreservesTheVersionOneResponseContract(): void
    {
        $response = new class implements ResponseInterface {
            public bool $ok = false;
            public mixed $result = null;
            public ?int $errorCode = 400;
            public ?string $description = 'LEGACY_GATEWAY_ERROR';
            public ?ResponseParametersInterface $parameters = null;
        };

        $exception = new ResponseException($response);

        self::assertSame($response, $exception->response);
        self::assertSame('LEGACY_GATEWAY_ERROR', $exception->gatewayError);
        self::assertSame(400, $exception->getCode());
    }
}
