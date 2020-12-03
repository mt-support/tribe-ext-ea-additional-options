<?php

namespace Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer;

class Boolean extends Sanitizer {
	/**
	 * Cast to a boolean if tribe_is_truthy for the value evaluates to `true` and if the value is not null.
	 *
	 * @since TBD
	 *
	 * @param mixed $value
	 *
	 * @return bool|null Null if not defined or boolean if `tribe_is_truthy
	 */
	public function sanitize( $value ) {
		if ( $value === null ) {
			return null;
		}

		return tribe_is_truthy( $value ) ? true : false;
	}
}
