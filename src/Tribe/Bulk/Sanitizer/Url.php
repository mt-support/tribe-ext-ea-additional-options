<?php

namespace Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer;

class Url extends Sanitizer {
	public function sanitize( $value ) {
		if ( $value === null || empty( $value ) ) {
			return null;
		}

		return esc_url_raw( $value );
	}
}
