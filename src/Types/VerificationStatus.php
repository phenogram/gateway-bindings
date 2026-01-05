<?php

namespace Phenogram\GatewayBindings\Types;

/**
 * This object represents the verification status of a code.
 */
class VerificationStatus implements Interfaces\VerificationStatusInterface
{
	/**
	 * @param string $status The current status of the verification process. One of the following:- code_valid – the code entered by the user is correct,- code_invalid – the code entered by the user is incorrect,- code_max_attempts_exceeded – the maximum number of attempts to enter the code has been exceeded,- expired – the code has expired and can no longer be used for verification.
	 * @param int $updatedAt The timestamp for this particular status. Represents the time when the status was last updated.
	 * @param string|null $codeEntered Optional. The code entered by the user.
	 */
	public function __construct(
		public string $status,
		public int $updatedAt,
		public ?string $codeEntered = null,
	) {
	}
}
