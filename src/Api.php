<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings;

use Phenogram\GatewayBindings\Types\Interfaces\RequestStatusInterface;

class Api implements ApiInterface
{
    public function __construct(
        protected ClientInterface $client,
        protected SerializerInterface $serializer = new Serializer(),
    ) {}

    /**
     * @param array<string, mixed>                        $args
     * @param class-string|'bool'|'float'|'int'|'string' $returnType
     */
    protected function doRequest(string $method, array $args, string $returnType, bool $returnsArray = false): mixed
    {
        $response = $this->client->sendRequest(
            $method,
            $this->serializer->serialize($args)
        );

        if (!$response->ok) {
            throw new ResponseException($response);
        }

        if ($response->result === null) {
            throw new \UnexpectedValueException(sprintf(
                'Telegram Gateway API returned no result for %s.',
                $method,
            ));
        }

        return $this->serializer->deserialize(
            $response->result,
            $returnType,
            $returnsArray
        );
    }

    /** @inheritDoc */
    public function sendVerificationMessage(
        string $phoneNumber,
        ?string $requestId = null,
        ?string $senderUsername = null,
        ?string $code = null,
        ?int $codeLength = null,
        ?string $callbackUrl = null,
        ?string $payload = null,
        ?int $ttl = null,
    ): RequestStatusInterface {
        $result = $this->doRequest(
            method: 'sendVerificationMessage',
            args: get_defined_vars(),
            returnType: RequestStatusInterface::class,
        );

        if (!$result instanceof RequestStatusInterface) {
            throw new \UnexpectedValueException('The API returned an invalid request status.');
        }

        return $result;
    }

    /** @inheritDoc */
    public function checkSendAbility(string $phoneNumber): RequestStatusInterface
    {
        $result = $this->doRequest(
            method: 'checkSendAbility',
            args: get_defined_vars(),
            returnType: RequestStatusInterface::class,
        );

        if (!$result instanceof RequestStatusInterface) {
            throw new \UnexpectedValueException('The API returned an invalid request status.');
        }

        return $result;
    }

    /** @inheritDoc */
    public function checkVerificationStatus(string $requestId, ?string $code = null): RequestStatusInterface
    {
        $result = $this->doRequest(
            method: 'checkVerificationStatus',
            args: get_defined_vars(),
            returnType: RequestStatusInterface::class,
        );

        if (!$result instanceof RequestStatusInterface) {
            throw new \UnexpectedValueException('The API returned an invalid request status.');
        }

        return $result;
    }

    /** @inheritDoc */
    public function revokeVerificationMessage(string $requestId): bool
    {
        $result = $this->doRequest(
            method: 'revokeVerificationMessage',
            args: get_defined_vars(),
            returnType: 'bool',
        );

        if (!is_bool($result)) {
            throw new \UnexpectedValueException('The API returned an invalid revocation result.');
        }

        return $result;
    }
}
