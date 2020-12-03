<?php

namespace Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer;

use Tribe__Events__Main;

class Category extends Sanitizer {
	/**
	 * Try to find a category by name if a string was provided while creating the EA record. The field to find the
	 * taxonomy is the `name` as that's the default behavior on EA UI client. If the taxonomy is found the ID of the
	 * taxonomy is returned instead.
	 *
	 * @since TBD
	 *
	 * @param mixed $value
	 *
	 * @return int|null ID of the taxonomy if found otherwise the current value of the input.
	 */
	public function sanitize( $value ) {
		// If the input is a number use it as the ID of the taxonomy or if the value is not defined.
		if ( $value === null || empty( $value ) || is_numeric( $value ) ) {
			return $value;
		}

		$term = get_term_by( 'name', $value, Tribe__Events__Main::TAXONOMY );

		if ( $term instanceof \WP_Term ) {
			return $term->term_id;
		}

		return null;
	}
}
