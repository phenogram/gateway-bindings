<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings;

use Phenogram\GatewayBindings\Types\Interfaces\RequestStatusInterface;

interface ApiInterface
{
    /**
     * Send a verification message.
     *
     * @param string      $phoneNumber   Destination in E.164 format.
     * @param string|null $requestId     ID from checkSendAbility. The first send with this ID has no second charge.
     * @param string|null $senderUsername Verified channel that sends the code.
     * @param string|null $code          Numeric code with 4 to 8 characters. This value overrides codeLength.
     * @param int|null    $codeLength    Code length from 4 to 8 when Telegram creates the code.
     * @param string|null $callbackUrl   HTTPS URL for delivery reports. Maximum length: 256 bytes.
     * @param string|null $payload       Internal application data. Maximum length: 128 bytes.
     * @param int|null    $ttl           Message lifetime from 30 to 3600 seconds.
     *
     * @throws ResponseException         If Telegram returns a Gateway API error.
     * @throws \UnexpectedValueException If Telegram returns an invalid result.
     */
    public function sendVerificationMessage(
        string $phoneNumber,
        ?string $requestId = null,
        ?string $senderUsername = null,
        ?string $code = null,
        ?int $codeLength = null,
        ?string $callbackUrl = null,
        ?string $payload = null,
        ?int $ttl = null,
    ): RequestStatusInterface;

    /**
     * Check if Telegram can send a verification message.
     *
     * A successful check can charge the account. Use the returned request ID
     * for one later send that has no second charge.
     *
     * @param string $phoneNumber Destination in E.164 format.
     *
     * @throws ResponseException         If Telegram returns a Gateway API error.
     * @throws \UnexpectedValueException If Telegram returns an invalid result.
     */
    public function checkSendAbility(string $phoneNumber): RequestStatusInterface;

    /**
     * Get the request status and optionally verify a code.
     *
     * @param string      $requestId Verification request ID.
     * @param string|null $code      Code that the user entered.
     *
     * @throws ResponseException         If Telegram returns a Gateway API error.
     * @throws \UnexpectedValueException If Telegram returns an invalid result.
     */
    public function checkVerificationStatus(string $requestId, ?string $code = null): RequestStatusInterface;

    /**
     * Ask Telegram to revoke a verification message.
     *
     * A true result does not prove that Telegram removed the message.
     *
     * @param string $requestId Verification request ID.
     *
     * @throws ResponseException         If Telegram returns a Gateway API error.
     * @throws \UnexpectedValueException If Telegram returns an invalid result.
     */
    public function revokeVerificationMessage(string $requestId): bool;
}
