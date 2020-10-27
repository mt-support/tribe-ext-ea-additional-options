<?php

namespace Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer;

class Origin extends Sanitizer {
	private $alias = [
		'google calendar' => 'gcal',
		'icalendar'       => 'ical',
	];

	private $valid_origins = [
		'eventbrite',
		'gcal',
		'ical',
		'meetup',
		'url',
	];

	public function sanitize( $value ) {
		$value = strtolower( $this->validate_string( $value ) );

		if ( isset( $this->alias[ $value ] ) ) {
			$value = $this->alias[ $value ];
		}

		if ( in_array( $value, $this->valid_origins, true ) ) {
			return $value;
		}

		throw new \InvalidArgumentException( "The {$value} is not a valid origin." );
	}
}
