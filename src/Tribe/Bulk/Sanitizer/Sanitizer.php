<?php

namespace Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer;

use InvalidArgumentException;

abstract class Sanitizer {
	/**
	 * @var array
	 */
	protected $meta;

	public function __construct( array $meta = [] ) {
		$this->meta = $meta;
	}

	protected function has_key( string $key = '' ) {
		return isset( $this->meta[ $key ] );
	}

	protected function get_value( string $key = '', $fallback = null ) {
		return $this->has_key( $key ) ? $this->meta[ $key ] : $fallback;
	}

	protected function validate_string( $value ) {
		if ( $value === null || ! is_string( $value ) ) {
			throw new \InvalidArgumentException( 'This field is a required parameter. ' . __CLASS__ );
		}

		if ( ! is_string( $value ) ) {
			throw new \InvalidArgumentException( 'The type needs to be a string. ' . __CLASS__ );
		}

		return $value;
	}

	/**
	 * Sanitize the value into a normalized format.
	 *
	 * @throws InvalidArgumentException
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	abstract public function sanitize( $value );
}
