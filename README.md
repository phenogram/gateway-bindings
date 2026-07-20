[English](README.en.md) · **Русский**

# Phenogram Gateway Bindings

[![CI](https://github.com/phenogram/gateway-bindings/actions/workflows/ci.yml/badge.svg)](https://github.com/phenogram/gateway-bindings/actions/workflows/ci.yml)
[![Последняя стабильная версия](https://poser.pugx.org/phenogram/gateway-bindings/v/stable)](https://packagist.org/packages/phenogram/gateway-bindings)
[![Версия PHP](https://poser.pugx.org/phenogram/gateway-bindings/require/php)](https://packagist.org/packages/phenogram/gateway-bindings)
[![Лицензия](https://poser.pugx.org/phenogram/gateway-bindings/license)](LICENSE)

Строго типизированные PHP-биндинги для
[Telegram Gateway API](https://core.telegram.org/gateway/api).

Пакет помогает отправлять коды подтверждения через Telegram. В нём есть:

- типизированные методы для всех операций Gateway API;
- типизированные объекты запроса, доставки и проверки кода;
- небольшой сериализатор имён полей Gateway API;
- интерфейс HTTP-клиента без привязки к конкретной библиотеке;
- офлайн-тесты для всех примеров в репозитории.

Пакет не выбирает HTTP-библиотеку за ваше приложение. Реализуйте
`ClientInterface` или адаптируйте проверенный
[пример на cURL](examples/CurlClient.php).

## Требования

- PHP 8.4 или новее.
- Composer 2.
- Токен доступа для реальных запросов к Gateway API.
- Расширение PHP cURL, только если вы используете пример с cURL.

## Установка

```bash
composer require phenogram/gateway-bindings
```

## Запуск примеров из репозитория

Для команд с примерами ниже нужен клон репозитория.
Подготовьте клон перед запуском:

```bash
git clone https://github.com/phenogram/gateway-bindings.git
cd gateway-bindings
composer install
```

## Первый запуск без сети

Запустите полный пример. Он использует локальный ответ. Для него не нужны токен,
сеть и платная операция API.

```bash
php examples/offline.php
```

Ожидаемый результат:

```text
Request request-demo: code_valid
```

Также можно имитировать отправку сообщения:

```bash
php examples/send-verification.php
```

Ожидаемый результат:

```text
Simulated request request-demo for +12025550123
```

## Отправка реального сообщения

> [!WARNING]
> Реальный запрос может списать средства со счёта Telegram Gateway. До запуска
> прочитайте раздел [Правила тарификации](#правила-тарификации).

Задайте токен и номер получателя. Используйте формат E.164.

```bash
export TELEGRAM_GATEWAY_TOKEN='your-token'
export TELEGRAM_GATEWAY_PHONE='+12025550123'
php examples/send-verification.php --live
```

Пример вызывает `sendVerificationMessage` напрямую. Если вы хотите применить
этот клиент в приложении, скопируйте
[`examples/CurlClient.php`](examples/CurlClient.php) и замените пространство
имён.

## Правила тарификации

`checkSendAbility` — необязательный метод. Это не бесплатная пробная проверка.

- Если Telegram подтвердит возможность отправки на номер, проверка может списать
  средства.
- Успешная проверка возвращает `request_id`.
- Один последующий вызов `sendVerificationMessage` с этим `request_id`
  выполняется без повторного списания.
- Повторная отправка с тем же `request_id` завершится ошибкой.
- Отправка без этого `request_id` создаст новый запрос и может привести к новому
  списанию.
- По документации Telegram тестовые запросы на собственный номер бесплатны.

Прямой вызов `sendVerificationMessage` тарифицируется по плану Gateway.
Telegram возвращает средства, если сообщение не выполнило условия доставки в
пределах заданного `ttl`. Актуальные правила приведены в
[официальной документации Gateway API](https://core.telegram.org/gateway/api).

## Публичный API

| Метод | Назначение | Результат |
| --- | --- | --- |
| `sendVerificationMessage(...)` | Отправляет код подтверждения. | `RequestStatusInterface` |
| `checkSendAbility($phoneNumber)` | Проверяет возможность отправки на номер. Этот вызов может списать средства. | `RequestStatusInterface` |
| `checkVerificationStatus($requestId, $code)` | Получает статус запроса и при необходимости проверяет код. | `RequestStatusInterface` |
| `revokeVerificationMessage($requestId)` | Просит Telegram отозвать сообщение. | `bool` |

Все параметры и значения статусов описаны в
[русском руководстве по API](docs/ru/api.md).

## Контракт HTTP-клиента

Класс `Api` передаёт имя метода и сериализованный массив данных вашему клиенту:

```php
interface ClientInterface
{
    /** @param array<string, mixed> $data */
    public function sendRequest(string $method, array $data): ResponseInterface;
}
```

Верните `Response` с точной структурой ответа Gateway API:

- успех: `ok: true` и `result`;
- ошибка: `ok: false` и `error`.

Gateway API не возвращает поля Bot API `description`, `error_code` и
`parameters`. Интерфейс и поля конструктора из версии 1.0 сохранены для
совместимости исходного кода. В новых реализациях ответа используйте
`GatewayResponseInterface` и его поле `error`.

Правила транспорта и обработки ошибок описаны в
[русском руководстве по клиенту](docs/ru/client.md).

## Ошибки

Если Telegram вернул `ok: false`, класс `Api` выбрасывает
`ResponseException`. Исключение принимает любую реализацию
`ResponseInterface`.

```php
try {
    $status = $api->checkVerificationStatus($requestId, $code);
} catch (\Phenogram\GatewayBindings\ResponseException $exception) {
    $gatewayError = $exception->gatewayError;
}
```

Некорректный успешный ответ вызывает `UnexpectedValueException`. Транспорт
может использовать `RuntimeException` для ошибок сети, HTTP и JSON.

## Типизированные результаты

`RequestStatusInterface` содержит:

- `requestId`;
- `phoneNumber`;
- `requestCost`;
- `isRefunded`;
- `remainingBalance`;
- `deliveryStatus`;
- `verificationStatus`;
- `payload`.

Если Telegram не вернул необязательное поле, его значение равно `null`.
Сериализатор отклоняет ответ без обязательного поля или с неверным типом.

## Документация и примеры

| Материал | English | Русский |
| --- | --- | --- |
| API и тарификация | [docs/en/api.md](docs/en/api.md) | [docs/ru/api.md](docs/ru/api.md) |
| HTTP-клиенты и ошибки | [docs/en/client.md](docs/en/client.md) | [docs/ru/client.md](docs/ru/client.md) |

Исполняемые примеры:

- [`examples/offline.php`](examples/offline.php) проверяет код с локальным ответом.
- [`examples/send-verification.php`](examples/send-verification.php) по умолчанию имитирует отправку. Флаг `--live` выполняет реальный запрос.
- [`examples/CurlClient.php`](examples/CurlClient.php) содержит HTTP-клиент с внедряемым транспортом.

Запустите все примеры без доступа к сети:

```bash
composer examples
```

## Разработка

Установите основные зависимости и изолированные инструменты контроля качества:

```bash
composer install
composer tools:install
```

Запустите все локальные проверки:

```bash
composer check
```

Команда проверяет метаданные Composer, запускает PHPUnit и все примеры без сети,
выполняет PHPStan на максимальном уровне и проверяет стиль кода.

## Безопасность

- Храните токен вне системы контроля версий.
- Не записывайте токены, номера телефонов и коды подтверждения в журналы.
- Используйте HTTPS для всех реальных запросов.
- Проверяйте подпись и время каждого отчёта о доставке. Следуйте
  [официальной процедуре](https://core.telegram.org/gateway/api#checking-report-integrity).

Сообщайте об уязвимости через закрытый канал связи с сопровождающим. Не
публикуйте учётные данные и персональные данные в открытой задаче. Подробности
приведены в [политике безопасности](SECURITY.md).

## Участие в разработке

Прочитайте [CONTRIBUTING.md](CONTRIBUTING.md). Не используйте сеть в тестах.
Обновляйте английскую и русскую документацию в одном изменении.

## Лицензия

[MIT](LICENSE)
