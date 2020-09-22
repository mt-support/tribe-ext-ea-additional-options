<?php


namespace Tribe\Extensions\EA_Additional_Options\Modules;


class Line_Breaks {
	public function hook() {
		add_filter( 'tribe_aggregator_event_translate_service_data_field_map', [ $this, 'remove_line_breaks' ] );
		add_filter( 'tribe_aggregator_before_insert_event', [ $this, 'filter_imported_event' ], 10, 2 );
		add_filter( 'tribe_aggregator_before_save_event', [ $this, 'filter_imported_event' ], 10, 2 );
	}

	public function remove_line_breaks( $fieldMap ) {
		$lineBreakOpt = tribe_get_option( Settings::PREFIX . 'retain_line_breaks' );
		if ( tribe_is_truthy( $lineBreakOpt ) ) {
			if ( isset( $fieldMap['description'] ) ) {
				unset( $fieldMap['description'] );
			}
			$fieldMap['unsafe_description'] = 'post_content';
		}
		$fieldMap['start_date_utc'] = 'EventUTCStartDate';
		$fieldMap['end_date_utc']   = 'EventUTCEndDate';

		return $fieldMap;
	}

	/**
	 * Filters event info before being saved or updated
	 *
	 * @param array $event
	 *
	 * @return array
	 */
	public function filter_imported_event( $event, $record ) {
		$lineBreakOpt             = tribe_get_option( Settings::PREFIX . 'retain_line_breaks' );
		if ( tribe_is_truthy( $lineBreakOpt ) ) {
			$event['post_content'] = str_replace( [ '\\n', '\n', "\n", "\\n" ], '<br>', $event['post_content'] );
		}

		return $event;
	}
}
