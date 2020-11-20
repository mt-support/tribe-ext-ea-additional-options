<?php

namespace Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer;

class Radius extends Sanitizer {

	private $km_to_miles = [
		1   => 1.6,
		5   => 8,
		10  => 16.1,
		25  => 40.2,
		50  => 80.5,
		100 => 160.9,
	];

	private $miles_to_km = [
		1.6   => 1,
		8     => 5,
		16.1  => 10,
		40.2  => 25,
		80.5  => 50,
		160.9 => 100,
	];

	/**
	 * Allow to define the radius of the import.
	 *
	 * @since TBD
	 *
	 * @param mixed $value
	 *
	 * @return bool|null Null if not defined or boolean if `tribe_is_truthy
	 */
	public function sanitize( $value ) {
		if ( $value === null || empty( $value ) || ! is_numeric( $value ) ) {
			return null;
		}

		$unit = tribe_get_option( 'geoloc_default_unit', 'miles' );
		if ( $unit === 'miles' ) {
			if ( isset( $this->km_to_miles[ $value ] ) ) {
				return $this->km_to_miles[ $value ];
			}

			if ( isset( $this->miles_to_km[ $value ] ) ) {
				return $value;
			}

			return null;
		}

		if ( isset( $this->miles_to_km[ $value ] ) ) {
			return $this->miles_to_km[ $value ];
		}

		if ( isset( $this->km_to_miles[ $value ] ) ) {
			return $value;
		}

		return null;
	}
}
