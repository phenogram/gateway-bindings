[Документация](../../README.md) · [English version](../en/client.md)

# Руководство по HTTP-клиенту и ошибкам

Биндинги не зависят от конкретной HTTP-библиотеки. Приложение предоставляет
реализацию `ClientInterface`.

## Контракт клиента

Клиент получает:

- имя метода Gateway API, например `sendVerificationMessage`;
- массив данных с ключами в формате `snake_case`.

Клиент возвращает `ResponseInterface`.
Новые клиенты возвращают `GatewayResponseInterface` или готовый `Response`.

```php
interface ClientInterface
{
    /** @param array<string, mixed> $data */
    public function sendRequest(string $method, array $data): ResponseInterface;
}
```

Используйте адрес `https://gatewayapi.telegram.org/METHOD_NAME`. Передавайте
токен в заголовке:

```text
Authorization: Bearer YOUR_TOKEN
```

Отправляйте данные в формате JSON с кодировкой UTF-8. Задайте заголовок
`Content-Type: application/json`.

## Структура ответа

Успешный ответ Gateway API:

```json
{
  "ok": true,
  "result": {
    "request_id": "request-demo",
    "phone_number": "+12025550123",
    "request_cost": 0.01
  }
}
```

Создайте объект:

```php
new Response(ok: true, result: $decoded['result']);
```

Ответ Gateway API с ошибкой:

```json
{
  "ok": false,
  "error": "PHONE_NUMBER_INVALID"
}
```

Создайте объект:

```php
new Response(ok: false, error: $decoded['error']);
```

Не записывайте ошибку в `description`. Это поле относится к модели ответа
Telegram Bot API. Gateway API использует поле `error`.

## Ошибки транспорта

Отделяйте ошибки API от ошибок транспорта.

- Возвращайте `Response(ok: false, error: ...)` для корректного ответа Gateway
  API с ошибкой.
- Выбрасывайте `RuntimeException` при ошибке сети.
- Выбрасывайте `RuntimeException`, если ответ не является корректным JSON.
- Выбрасывайте `RuntimeException`, если поле `ok` отсутствует или имеет
  не-Boolean тип.
- Не добавляйте токен в текст исключения.

Эти правила реализованы в
[`examples/CurlClient.php`](../../examples/CurlClient.php). Транспорт клиента
можно внедрить снаружи. Тесты используют эту возможность и не открывают сетевое
соединение.

## Ошибки API

Класс `Api` преобразует ответ с `ok: false` в `ResponseException`. Проверяйте
стабильный идентификатор ошибки Gateway:

```php
try {
    $status = $api->checkSendAbility($phoneNumber);
} catch (ResponseException $exception) {
    $error = $exception->gatewayError;
}
```

Не используйте текст исключения как часть протокола приложения. Используйте
`$exception->gatewayError`.

## Проверка успешного ответа

Сериализатор проверяет результат Gateway до создания типизированного объекта.
Он проверяет:

- наличие всех обязательных полей;
- JSON-тип каждого обязательного поля;
- объектный тип каждого вложенного статуса;
- преобразование числовой стоимости в PHP `float`.

Если обязательное поле отсутствует, сериализатор выбрасывает
`InvalidArgumentException`. Если тип поля или результата неверен, он выбрасывает
`UnexpectedValueException`.

## Проверочный список для рабочего окружения

- Задайте тайм-аут соединения и общий тайм-аут.
- Не отключайте проверку сертификата TLS.
- Храните токен в хранилище секретов.
- Не записывайте заголовки запроса в журналы.
- Не записывайте полное тело запроса в журналы.
- Повторяйте запрос только при наличии стратегии идемпотентности.
- Сохраните `request_id` до следующего шага приложения.
- Проверяйте ошибки API с локальными ответами.
- Не включайте платные реальные проверки в стандартный набор тестов.
