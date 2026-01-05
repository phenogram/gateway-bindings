<?php

namespace Phenogram\GatewayBindings\Factories;

use Phenogram\GatewayBindings\Types\Interfaces\VerificationStatusInterface;
use Phenogram\GatewayBindings\Types\VerificationStatus;

class VerificationStatusFactory extends AbstractFactory
{
	/**
	 * Creates a new VerificationStatus instance with default fake data.
	 *
	 * @param string|null $status Optional. The current status of the verification process. One of the following:- code_valid – the code entered by the user is correct,- code_invalid – the code entered by the user is incorrect,- code_max_attempts_exceeded – the maximum number of attempts to enter the code has been exceeded,- expired – the code has expired and can no longer be used for verification.
	 * @param int|null $updatedAt Optional. The timestamp for this particular status. Represents the time when the status was last updated.
	 * @param string|null $codeEntered Optional. Optional. The code entered by the user.
	 */
	public static function make(
		?string $status = null,
		?int $updatedAt = null,
		?string $codeEntered = null,
	): VerificationStatusInterface
	{
		return self::factory()->makeVerificationStatus(
		    status: $status ?? self::fake()->word(),
		    updatedAt: $updatedAt ?? self::fake()->randomNumber(),
		    codeEntered: $codeEntered,
		);
	}
}
