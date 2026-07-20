[Documentation](../../README.en.md) · [Русская версия](../ru/client.md)

# HTTP client and error guide

The bindings do not depend on an HTTP library. Your application supplies one
`ClientInterface` implementation.

## Client contract

The client receives:

- A Gateway API method name, such as `sendVerificationMessage`.
- A data array with `snake_case` keys.

The client returns `ResponseInterface`.
New clients return `GatewayResponseInterface` or the provided `Response`.

```php
interface ClientInterface
{
    /** @param array<string, mixed> $data */
    public function sendRequest(string $method, array $data): ResponseInterface;
}
```

Use `https://gatewayapi.telegram.org/METHOD_NAME`. Send the token in this
header:

```text
Authorization: Bearer YOUR_TOKEN
```

Send the data as UTF-8 JSON. Set `Content-Type: application/json`.

## Response envelope

A successful Gateway API response has this form:

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

Create:

```php
new Response(ok: true, result: $decoded['result']);
```

A Gateway API failure has this form:

```json
{
  "ok": false,
  "error": "PHONE_NUMBER_INVALID"
}
```

Create:

```php
new Response(ok: false, error: $decoded['error']);
```

Do not map the error to `description`. That field belongs to the Telegram Bot
API response model. Gateway API errors use `error`.

## Transport failures

Keep API failures separate from transport failures.

- Return `Response(ok: false, error: ...)` for a valid Gateway API error
  envelope.
- Throw `RuntimeException` when the network request fails.
- Throw `RuntimeException` when the response is not valid JSON.
- Throw `RuntimeException` when `ok` is absent or is not Boolean.
- Do not include the token in an exception message.

[`examples/CurlClient.php`](../../examples/CurlClient.php) implements these
rules. Its transport is injectable. The unit tests use that injection and do
not open a network connection.

## API failures

The `Api` class converts a response with `ok: false` to `ResponseException`.
Inspect the stable Gateway error identifier:

```php
try {
    $status = $api->checkSendAbility($phoneNumber);
} catch (ResponseException $exception) {
    $error = $exception->gatewayError;
}
```

Do not use the exception text as an application protocol. Use
`$exception->gatewayError`.

## Successful response validation

The serializer checks the Gateway result before it creates a typed object. It
checks:

- Each required field is present.
- Each required field has the expected JSON type.
- Each nested status is an object.
- Numeric costs are converted to PHP `float`.

It throws `InvalidArgumentException` for a missing required field. It throws
`UnexpectedValueException` for an invalid field type or result type.

## Production checklist

- Set connection and total timeouts.
- Keep TLS certificate verification enabled.
- Use a secret store for the token.
- Do not log request headers.
- Do not log full request bodies.
- Retry only when your application has an idempotency plan.
- Record the `request_id` before the next application step.
- Test API errors with local response fixtures.
- Keep paid live tests outside the default test suite.
