<?php

namespace Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer;

class Nullable extends Sanitizer {
	/**
	 * If the value is empty cast it to null otherwise return the value.
	 *
	 * @since TBD
	 *
	 * @param mixed $value
	 *
	 * @return mixed|null
	 */
	public function sanitize( $value ) {
		return empty( $value ) ? null : $value;
	}
}
