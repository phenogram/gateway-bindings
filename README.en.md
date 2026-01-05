🇬🇧 ENGLISH | [🇷🇺 РУССКИЙ](README.md)

# PHP SDK for Telegram Gateway API

Strictly typed PHP classes for [Telegram Gateway API](https://core.telegram.org/gateway/api).

This package provides a convenient wrapper for sending verification messages and checking delivery capability via the official Telegram Gateway.

This is a work in progress. If you find any inconsistencies with documentation, feel free to file an issue.

# Installation

```bash
composer require phenogram/gateway-bindings
```

# Usage

This package consists of main parts: `Api`, `Serializer`, and `Factory`.

## Client (ClientInterface)

To use the API, you need to implement the `ClientInterface`. The library is agnostic to the specific HTTP client, so you can use whatever suits you best.

Below is an example implementation using native `curl` with zero external dependencies.
> You can see this code in action in [tests](tests/Readme/ReadmeClientTest.php).

```php
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
    ) {
    }

    public function sendRequest(string $method, array $data): Types\Interfaces\ResponseInterface
    {
        $ch = curl_init("{$this->apiUrl}/{$method}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        
        $jsonData = json_encode($data, JSON_THROW_ON_ERROR);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \RuntimeException('Request Error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $responseData = json_decode((string)$response, true);
        
        if ($httpCode !== 200) {
             return new Types\Response(
                ok: false, errorCode: $httpCode, description: $responseData['error'] ?? 'HTTP Error ' . $httpCode
             );
        }
        
        if (!isset($responseData['ok']) || !is_bool($responseData['ok'])) {
             return new Types\Response(ok: false, errorCode: 500, description: 'Invalid response from API');
        }

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
```

## API

Initialization and usage:

```php
use Phenogram\GatewayBindings\Api;
use Phenogram\GatewayBindings\Serializer;

$api = new Api(
    client: new ReadmeClient('YOUR_GATEWAY_TOKEN'),
    serializer: new Serializer()
);

// 1. Check ability to send (Free)
try {
    $status = $api->checkSendAbility(phoneNumber: '+1234567890');
    
    echo "Request ID: " . $status->requestId . "\n";
    echo "Cost: " . $status->requestCost . "\n";
    
    // 2. Send code (if check was successful)
    $result = $api->sendVerificationMessage(
        phoneNumber: '+1234567890',
        requestId: $status->requestId,
        codeLength: 6,
        ttl: 60
    );
    
    echo "Delivery Status: " . $result->deliveryStatus?->status;
    
} catch (\Phenogram\GatewayBindings\ResponseException $e) {
    echo "API Error: " . $e->getMessage();
}
```

## Serializer

The serializer is responsible for converting API responses into strictly typed objects. It is usually used inside the `Api` class, but you can use it standalone.

```php
use Phenogram\GatewayBindings\Serializer;
use Phenogram\GatewayBindings\Types\Interfaces\RequestStatusInterface;

$jsonResponse = '{
    "request_id": "req_123",
    "phone_number": "+1234567890",
    "request_cost": 0.05
}';

$data = json_decode($jsonResponse, true);

$serializer = new Serializer();
$status = $serializer->deserialize(
    data: $data, 
    type: RequestStatusInterface::class
);

assert($status instanceof RequestStatusInterface);
echo $status->phoneNumber; // +1234567890
```

# Development

To work on the project (running tests, static analysis), use the following commands:

1. **Install dependencies:**
   ```bash
   composer install
   composer install -d tools/phpstan
   composer install -d tools/php-cs-fixer
   ```

2. **Run tests:**
   ```bash
   composer test
   ```

3. **Run static analysis (PHPStan):**
   ```bash
   composer phpstan
   ```

4. **Fix code style:**
   ```bash
   composer fix
   ```