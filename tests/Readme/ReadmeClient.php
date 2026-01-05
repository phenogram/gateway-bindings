<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings\Tests\Readme;

use Phenogram\GatewayBindings\ClientInterface;
use Phenogram\GatewayBindings\Types;

final readonly class ReadmeClient implements ClientInterface
{
    public function __construct(
        private string $token,
        private string $apiUrl = 'https://gatewayapi.telegram.org',
    ) {}

    public function sendRequest(string $method, array $data): Types\Interfaces\ResponseInterface
    {
        // The endpoint is directly appended to the base URL
        $ch = curl_init("{$this->apiUrl}/{$method}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        $jsonData = json_encode($data, JSON_THROW_ON_ERROR);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        // Authentication is done via the Authorization header with a Bearer token
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData),
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \RuntimeException('Request Error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $responseData = json_decode((string) $response, true);

        // If the HTTP status is not 200, something went wrong.
        if ($httpCode !== 200) {
            return new Types\Response(
                ok: false,
                errorCode: $httpCode,
                description: $responseData['error'] ?? 'HTTP Error ' . $httpCode
            );
        }

        if (!isset($responseData['ok']) || !is_bool($responseData['ok'])) {
            // Handle cases where the API returns a non-standard error or HTML
            return new Types\Response(
                ok: false,
                errorCode: 500,
                description: 'Invalid response from API'
            );
        }

        // For failed requests ('ok' is false), the error message is in the 'error' field.
        $description = $responseData['description'] ?? $responseData['error'] ?? null;

        return new Types\Response(
            ok: $responseData['ok'],
            result: $responseData['result'] ?? null,
            errorCode: $responseData['error_code'] ?? $httpCode,
            description: $description,
            parameters: null
        );
    }
}
