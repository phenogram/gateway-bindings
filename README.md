[🇬🇧 ENGLISH](README.en.md) | 🇷🇺 РУССКИЙ

# PHP SDK для Telegram Gateway API

Строго типизированные PHP классы для [Telegram Gateway API](https://core.telegram.org/gateway/api).

Этот пакет предоставляет удобную обёртку для отправки верификационных сообщений и проверки возможности их доставки через официальный шлюз Telegram.

Работа всё ещё в процессе. Если вы обнаружите какие-либо несоответствия с документацией, не стесняйтесь создать ишью.

# Установка

```bash
composer require phenogram/gateway-bindings
```

# Использование

Этот пакет состоит из основных частей: `Api`, `Serializer` и `Factory`.

## Клиент (ClientInterface)

Чтобы использовать API, вам нужно реализовать интерфейс `ClientInterface`. Библиотека не привязана к конкретному HTTP-клиенту, поэтому вы можете использовать любой удобный вам способ.

Ниже приведен пример реализации с использованием нативного `curl` без внешних зависимостей.
> Вы можете увидеть этот код в действии в [тестах](tests/Readme/ReadmeClientTest.php).

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

Инициализация и использование API:

```php
use Phenogram\GatewayBindings\Api;
use Phenogram\GatewayBindings\Serializer;

$api = new Api(
    client: new ReadmeClient('YOUR_GATEWAY_TOKEN'),
    serializer: new Serializer()
);

// 1. Проверка возможности отправки (бесплатно)
try {
    $status = $api->checkSendAbility(phoneNumber: '+1234567890');
    
    echo "Request ID: " . $status->requestId . "\n";
    echo "Стоимость: " . $status->requestCost . "\n";
    
    // 2. Отправка кода (если проверка прошла успешно)
    $result = $api->sendVerificationMessage(
        phoneNumber: '+1234567890',
        requestId: $status->requestId,
        codeLength: 6,
        ttl: 60
    );
    
    echo "Статус доставки: " . $result->deliveryStatus?->status;
    
} catch (\Phenogram\GatewayBindings\ResponseException $e) {
    echo "Ошибка API: " . $e->getMessage();
}
```

## Сериализатор

Сериализатор отвечает за преобразование ответов API в строго типизированные объекты. Обычно он используется внутри класса `Api`, но вы можете использовать его отдельно.

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

# Разработка

Для работы над проектом (запуск тестов, статический анализ) используйте следующие команды:

1. **Установка зависимостей:**
   ```bash
   composer install
   composer install -d tools/phpstan
   composer install -d tools/php-cs-fixer
   ```

2. **Запуск тестов:**
   ```bash
   composer test
   ```

3. **Статический анализ (PHPStan):**
   ```bash
   composer phpstan
   ```

4. **Исправление стиля кода:**
   ```bash
   composer fix
   ```