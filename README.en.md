**English** · [Русский](README.md)

# Phenogram Gateway Bindings

[![CI](https://github.com/phenogram/gateway-bindings/actions/workflows/ci.yml/badge.svg)](https://github.com/phenogram/gateway-bindings/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/phenogram/gateway-bindings/v/stable)](https://packagist.org/packages/phenogram/gateway-bindings)
[![PHP Version](https://poser.pugx.org/phenogram/gateway-bindings/require/php)](https://packagist.org/packages/phenogram/gateway-bindings)
[![License](https://poser.pugx.org/phenogram/gateway-bindings/license)](LICENSE)

Strict PHP bindings for the [Telegram Gateway API](https://core.telegram.org/gateway/api).

Use this package to send verification codes through Telegram. It gives you:

- Typed methods for all Gateway API operations.
- Typed request, delivery, and verification status objects.
- A small serializer for Gateway API field names.
- An HTTP client interface. You can use your preferred HTTP library.
- Offline tests for all repository examples.

This package does not select an HTTP library for your application. Implement
`ClientInterface`, or adapt the tested [cURL example](examples/CurlClient.php).

## Requirements

- PHP 8.4 or later.
- Composer 2.
- An access token for live Gateway API requests.
- The cURL PHP extension only if you use the cURL example.

## Install

```bash
composer require phenogram/gateway-bindings
```

## Run the repository examples

The example commands below require a source checkout.
Prepare the checkout before you run them:

```bash
git clone https://github.com/phenogram/gateway-bindings.git
cd gateway-bindings
composer install
```

## Start offline

Run the complete example. It uses an injected local response. It does not use a
token, a network connection, or a paid API operation.

```bash
php examples/offline.php
```

Expected output:

```text
Request request-demo: code_valid
```

You can also simulate a send operation:

```bash
php examples/send-verification.php
```

Expected output:

```text
Simulated request request-demo for +12025550123
```

## Send a live verification message

> [!WARNING]
> A live request can charge your Telegram Gateway account. Read
> [Billing rules](#billing-rules) before you run this command.

Set the token and the destination phone number. Use the E.164 phone number
format.

```bash
export TELEGRAM_GATEWAY_TOKEN='your-token'
export TELEGRAM_GATEWAY_PHONE='+12025550123'
php examples/send-verification.php --live
```

The live example calls `sendVerificationMessage` directly. Copy
[`examples/CurlClient.php`](examples/CurlClient.php) into your application if
you want to use it there. Change its namespace to match your application.

## Billing rules

`checkSendAbility` is optional. It is not a free dry run.

- If Telegram confirms that it can send to the phone number, the check can
  charge your account.
- The successful check returns a `request_id`.
- One later call to `sendVerificationMessage` with that `request_id` is free.
- A repeated send with the same `request_id` returns an error.
- A send without that `request_id` creates a new request and can add a charge.
- Tests to your own phone number are free according to the Telegram Gateway
  documentation.

A direct `sendVerificationMessage` request follows the Gateway pricing plan.
Telegram refunds the fee when the message does not meet the delivery conditions
within the specified `ttl`. See the
[official Gateway API reference](https://core.telegram.org/gateway/api) for the
current billing rules.

## Public API

| Method | Purpose | Result |
| --- | --- | --- |
| `sendVerificationMessage(...)` | Send a verification code. | `RequestStatusInterface` |
| `checkSendAbility($phoneNumber)` | Check if Telegram can send to a number. This call can charge the account. | `RequestStatusInterface` |
| `checkVerificationStatus($requestId, $code)` | Read request status and optionally verify a code. | `RequestStatusInterface` |
| `revokeVerificationMessage($requestId)` | Ask Telegram to revoke a message. | `bool` |

See the [English API guide](docs/en/api.md) for all parameters and status
values.

## HTTP client contract

The `Api` class sends a method name and a serialized data array to your client:

```php
interface ClientInterface
{
    /** @param array<string, mixed> $data */
    public function sendRequest(string $method, array $data): ResponseInterface;
}
```

Return a `Response` with the exact Gateway API envelope:

- Success: `ok: true` and `result`.
- Failure: `ok: false` and `error`.

The Gateway API does not return the Bot API fields `description`, `error_code`,
or `parameters`. The version 1.0 interface and constructor fields remain for
source compatibility. New response implementations must use
`GatewayResponseInterface` and its `error` field.

Read the [English client guide](docs/en/client.md) for transport rules and error
handling.

## Errors

`Api` throws `ResponseException` when Telegram returns `ok: false`. The
exception accepts any `ResponseInterface` implementation.

```php
try {
    $status = $api->checkVerificationStatus($requestId, $code);
} catch (\Phenogram\GatewayBindings\ResponseException $exception) {
    $gatewayError = $exception->gatewayError;
}
```

A malformed successful response causes `UnexpectedValueException`. A transport
can use `RuntimeException` for network, HTTP, or JSON failures.

## Typed results

`RequestStatusInterface` contains:

- `requestId`
- `phoneNumber`
- `requestCost`
- `isRefunded`
- `remainingBalance`
- `deliveryStatus`
- `verificationStatus`
- `payload`

Optional fields are `null` when Telegram omits them. The serializer rejects a
missing required field or an invalid field type.

## Documentation and examples

| Resource | English | Russian |
| --- | --- | --- |
| API and billing | [docs/en/api.md](docs/en/api.md) | [docs/ru/api.md](docs/ru/api.md) |
| HTTP clients and errors | [docs/en/client.md](docs/en/client.md) | [docs/ru/client.md](docs/ru/client.md) |

Runnable examples:

- [`examples/offline.php`](examples/offline.php) verifies a code with a local response.
- [`examples/send-verification.php`](examples/send-verification.php) simulates a send by default. The `--live` option sends a real request.
- [`examples/CurlClient.php`](examples/CurlClient.php) is an injectable cURL client.

Run all examples without network access:

```bash
composer examples
```

## Development

Install the main dependencies and the isolated quality tools:

```bash
composer install
composer tools:install
```

Run the complete local gate:

```bash
composer check
```

The gate validates Composer metadata, runs PHPUnit, runs every example offline,
runs PHPStan at the maximum level, and checks the code style.

## Security

- Store the access token outside source control.
- Do not write tokens, phone numbers, or verification codes to logs.
- Use HTTPS for each live request.
- Verify the signature and timestamp of each delivery report. Follow the
  [official integrity procedure](https://core.telegram.org/gateway/api#checking-report-integrity).

Report a suspected vulnerability through a private maintainer channel. Do not
put credentials or personal data in a public issue. Read the
[security policy](SECURITY.md).

## Contributing

Read [CONTRIBUTING.md](CONTRIBUTING.md). Keep tests offline. Update English and
Russian documents in the same change.

## License

[MIT](LICENSE)
