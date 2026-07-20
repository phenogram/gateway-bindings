[Documentation](../../README.md) · [Русская версия](../ru/api.md)

# Gateway API guide

This guide describes the PHP binding methods. The
[Telegram Gateway API reference](https://core.telegram.org/gateway/api) is the
source of truth for server behavior and prices.

## Send a verification message

Use:

```php
$status = $api->sendVerificationMessage(
    phoneNumber: '+12025550123',
    codeLength: 6,
    ttl: 60,
);
```

The method accepts these arguments:

| Argument | Required | Rule |
| --- | --- | --- |
| `phoneNumber` | Yes | Use E.164 format. |
| `requestId` | No | Use the ID from a successful `checkSendAbility` call. The first send with this ID has no second charge. |
| `senderUsername` | No | Use a verified channel that belongs to the token owner. |
| `code` | No | Use 4 to 8 numeric characters. This value overrides `codeLength`. |
| `codeLength` | No | Use an integer from 4 to 8 when Telegram must create the code. |
| `callbackUrl` | No | Use an HTTPS URL. Telegram sends delivery reports to it. |
| `payload` | No | Use this field for internal data. Telegram does not show it to the user. |
| `ttl` | No | Use 30 to 3600 seconds. |

The result is `RequestStatusInterface`.

## Check send ability

Use:

```php
$status = $api->checkSendAbility('+12025550123');
```

This method is optional. Do not use it as a free dry run. Telegram can charge
the account when it confirms that it can send to the number.

The successful response contains a `requestId`. Pass this value to one later
`sendVerificationMessage` call. That send has no second charge. A repeated send
with the same ID returns an error.

## Check verification status

Read the current status:

```php
$status = $api->checkVerificationStatus($requestId);
```

Let Telegram verify a user-entered code:

```php
$status = $api->checkVerificationStatus($requestId, $code);

if ($status->verificationStatus?->status === 'code_valid') {
    // Continue the application sign-in flow.
}
```

Call this method after the user enters a code. Telegram recommends this call
even when your application created the code.

## Revoke a message

Use:

```php
$accepted = $api->revokeVerificationMessage($requestId);
```

A `true` result means that Telegram accepted the revoke request. It does not
prove that Telegram removed the message. Telegram does not remove a message
that was already delivered or read.

## Request status

| Property | Type | Meaning |
| --- | --- | --- |
| `requestId` | `string` | Request identifier. |
| `phoneNumber` | `string` | Destination in E.164 format. |
| `requestCost` | `float` | Cost that the check or send operation caused. |
| `isRefunded` | `?bool` | Whether Telegram refunded the fee. |
| `remainingBalance` | `?float` | Balance after an operation that caused a charge. |
| `deliveryStatus` | `?DeliveryStatusInterface` | Delivery state when Telegram sent a message. |
| `verificationStatus` | `?VerificationStatusInterface` | Code verification state. |
| `payload` | `?string` | Application payload from the request. |

Telegram can omit each optional field. The PHP value is then `null`.

## Delivery status values

- `sent`
- `delivered`
- `read`
- `expired`
- `revoked`

Telegram can add a new value. Handle an unknown string safely.

## Verification status values

- `code_valid`
- `code_invalid`
- `code_max_attempts_exceeded`
- `expired`

Telegram can add a new value. Handle an unknown string safely.

## Charges and refunds

The price rules can change. Read the official reference before a production
release.

Current rules that affect application flow:

1. A successful `checkSendAbility` call can cause the charge.
2. One send with its `request_id` has no second charge.
3. A send without that ID creates a different request.
4. A message that does not meet the delivery conditions within `ttl` can qualify
   for an automatic refund.
5. A revoke operation does not cause a refund.

Do not use a live check in an automated test. Use an injected local response as
shown in [`examples/offline.php`](../../examples/offline.php).
