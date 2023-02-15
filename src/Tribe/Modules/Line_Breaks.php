<?php


namespace Tribe\Extensions\EA_Additional_Options\Modules;


class Line_Breaks {
	/**
	 * Run only if we are not using a template.
	 * When using a template that code will handle line breaks.
	 *
	 * @see Single_Event_Template::tec_ea_single_event_template()
	 *
	 * @return void
	 */
	public function hook() {
		$use_template = tribe_get_option( Settings::PREFIX . 'default_template' );
		if ( ! isset( $use_template ) || ! $use_template ) {
			add_filter( 'tribe_aggregator_event_translate_service_data_field_map', [ $this, 'remove_line_breaks' ] );
			add_filter( 'tribe_aggregator_before_update_event', [ $this, 'filter_imported_event' ], 10, 2 );
			add_filter( 'tribe_aggregator_before_save_event', [ $this, 'filter_imported_event' ], 10, 2 );
		}
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
	 * @param array $event Array containing the event details.
	 * @param       $record
	 *
	 * @return array
	 */
	public function filter_imported_event( array $event, $record ): array {
		$lineBreakOpt = tribe_get_option( Settings::PREFIX . 'retain_line_breaks' );
		if ( tribe_is_truthy( $lineBreakOpt ) ) {
			$event['post_content'] = str_replace( [ '\\n', '\n', "\n", "\\n" ], '<br>', $event['post_content'] );
		}

		return $event;
	}
}
