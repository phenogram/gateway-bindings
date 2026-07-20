<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings\Examples;

use Phenogram\GatewayBindings\ClientInterface;
use Phenogram\GatewayBindings\Types\Interfaces\GatewayResponseInterface;
use Phenogram\GatewayBindings\Types\Response;

/**
 * A small cURL client for the runnable examples.
 *
 * Applications can replace this class with any HTTP client. The bindings only
 * require ClientInterface.
 */
final class CurlClient implements ClientInterface
{
    /**
     * @var \Closure(string, array<string, mixed>): array{status: int, body: string}
     */
    private readonly \Closure $transport;

    /**
     * @param null|\Closure(string, array<string, mixed>): array{status: int, body: string} $transport
     */
    public function __construct(
        private readonly string $token,
        private readonly string $apiUrl = 'https://gatewayapi.telegram.org',
        ?\Closure $transport = null,
    ) {
        $this->transport = $transport ?? $this->request(...);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function sendRequest(string $method, array $data): GatewayResponseInterface
    {
        $httpResponse = ($this->transport)($method, $data);

        return self::decodeResponse(
            body: $httpResponse['body'],
            httpStatus: $httpResponse['status'],
        );
    }

    public static function decodeResponse(string $body, int $httpStatus): GatewayResponseInterface
    {
        try {
            $data = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException(
                sprintf('Telegram Gateway API returned invalid JSON (HTTP %d).', $httpStatus),
                previous: $exception,
            );
        }

        if (!is_array($data) || !isset($data['ok']) || !is_bool($data['ok'])) {
            throw new \RuntimeException(sprintf(
                'Telegram Gateway API returned an invalid response envelope (HTTP %d).',
                $httpStatus,
            ));
        }

        $error = $data['error'] ?? null;

        if ($error !== null && !is_string($error)) {
            throw new \RuntimeException('Telegram Gateway API returned a non-string error.');
        }

        if (!$data['ok'] && $error === null) {
            throw new \RuntimeException('Telegram Gateway API returned an error without an error field.');
        }

        if (($httpStatus < 200 || $httpStatus >= 300) && $data['ok']) {
            throw new \RuntimeException(sprintf(
                'Telegram Gateway API returned a successful envelope with HTTP %d.',
                $httpStatus,
            ));
        }

        return new Response(
            ok: $data['ok'],
            result: $data['result'] ?? null,
            error: $error,
        );
    }

    /**
     * @param  array<string, mixed>                   $data
     * @return array{status: int, body: string}
     */
    private function request(string $method, array $data): array
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('The cURL PHP extension is required for this example.');
        }

        $body = json_encode($data, JSON_THROW_ON_ERROR);
        $handle = curl_init(sprintf(
            '%s/%s',
            rtrim($this->apiUrl, '/'),
            rawurlencode($method),
        ));

        if ($handle === false) {
            throw new \RuntimeException('Failed to initialize cURL.');
        }

        $configured = curl_setopt_array($handle, [
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json',
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);

        if (!$configured) {
            throw new \RuntimeException('Failed to configure cURL.');
        }

        $responseBody = curl_exec($handle);

        if (!is_string($responseBody)) {
            throw new \RuntimeException('Telegram Gateway API request failed: ' . curl_error($handle));
        }

        $httpStatus = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);

        return [
            'status' => $httpStatus,
            'body' => $responseBody,
        ];
    }
}
