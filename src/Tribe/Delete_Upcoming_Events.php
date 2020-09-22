<?php


namespace Tribe\Extensions\EA_Additional_Options;


class Delete_Upcoming_Events {
	public function hook() {
		add_action( 'tribe_extension_ea_additional_options_ui', [ $this, 'add_ui' ] );
		add_filter( 'tribe_aggregator_import_submit_meta', [ $this, 'filter_import_meta' ] );
	}

	public function add_ui() {
	}

	public function delete_events() {}

	public function filter_import_meta( $meta ) {
		if ( empty( $_POST['aggregator'] ) ) {
			return $meta;
		}

		$meta['delete_upcoming_events'] = ! empty( $_POST['aggregator']['delete_upcoming_events'] );

		return $meta;
	}
}
