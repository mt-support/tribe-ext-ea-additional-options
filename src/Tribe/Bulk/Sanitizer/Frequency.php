<?php

namespace Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer;

class Frequency extends Sanitizer {
	private $alias = [
		'on demand'  => 'on_demand',
		'ondemand'   => 'on_demand',
		'30 minutes' => 'every30mins',
		'half hour'  => 'every30mins',
		'30 min'     => 'every30mins',
	];

	private $valid_type = [
		'on_demand',
		'every30mins',
		'hourly',
		'daily',
		'weekly',
		'monthly',
	];

	/**
	 * Validate the frequency of an import by making sure only valid paramters are allowed, a set of alias are provided
	 * in case a user uses a different value for the frequency other than the defined types.
	 *
	 * @since TBD
	 *
	 * @param mixed $value
	 *
	 * @return mixed|string|null
	 */
	public function sanitize( $value ) {

		if ( $this->get_value( 'type' ) !== 'schedule' ) {
			return null;
		}

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
