<?php

namespace Tribe\Extensions\EA_Additional_Options\Modules;

class Other_Url {
	public function hook() {
		add_filter( 'tribe_aggregator_url_import_range_options', [ $this, 'add_other_url_options' ] );
		add_filter( 'tribe_aggregator_service_post_import_args', [ $this, 'remove_end_param' ] );
	}

	/**
	 * Adds extra options to the 'Other URL' settings
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function add_other_url_options( $options ) {
		$options[ MONTH_IN_SECONDS * 6 ] = [
			'title' => __( 'Six months', 'tribe-ext-ea-additional-options' ),
			'range' => __( 'six months', 'tribe-ext-ea-additional-options' ),
		];
		$options[ YEAR_IN_SECONDS ]      = [
			'title' => __( 'One year', 'tribe-ext-ea-additional-options' ),
			'range' => __( 'one year', 'tribe-ext-ea-additional-options' ),
		];
		$options[ YEAR_IN_SECONDS * 2 ]  = [
			'title' => __( 'Two years', 'tribe-ext-ea-additional-options' ),
			'range' => __( 'two years', 'tribe-ext-ea-additional-options' ),
		];

		return $options;
	}

	/**
	 * Removes the 'end' parameter for 'Other URL' imports
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function remove_end_param( $args ) {
		if ( $args['origin'] === 'url' ) {
			unset( $args['end'] );
		}

		return $args;
	}
}
