<?php

namespace Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer;

class Nullable extends Sanitizer {
	public function sanitize( $value ) {
		return empty( $value ) ? null : $value;
	}
}
