<?php

namespace Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer;

class Type extends Sanitizer {
	private $alias = [
		'one time'  => 'manual',
		'once'      => 'manual',
		'scheduled' => 'schedule',
	];

	private $valid_type = [
		'schedule',
		'manual',
	];

	public function sanitize( $value ) {
		$value = strtolower( $this->validate_string( $value ) );

		if ( isset( $this->alias[ $value ] ) ) {
			$value = $this->alias[ $value ];
		}

		if ( in_array( $value, $this->valid_type, true ) ) {
			return $value;
		}

		throw new \InvalidArgumentException( "The {$value} is not a valid type." );
	}
}
