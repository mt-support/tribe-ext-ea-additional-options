<?php


namespace Tribe\Extensions\EA_Additional_Options\Modules;


use DateInterval;
use DateTime;
use DateTimeZone;
use stdClass;
use Tribe\Extensions\EA_Additional_Options\Plugin;

class Options {
	public function hook() {
		add_action( 'tribe_events_aggregator_import_form_preview_options', [ $this, 'add_import_options' ] );

		add_filter( 'tribe_aggregator_before_insert_event', [ $this, 'filter_imported_event' ], 10, 2 );
		add_filter( 'tribe_aggregator_before_save_event', [ $this, 'filter_imported_event' ], 10, 2 );

		add_filter( 'tribe_aggregator_import_submit_meta', [ $this, 'filter_import_meta' ], 10, 2 );

		add_filter( 'tribe_events_aggregator_tabs_new_handle_import_finalize', [
			$this,
			'store_import_meta',
		], 10, 2 );
	}

	/**
	 * HTML for the additional options for individual imports
	 */
	public function add_import_options() {
		$record           = new stdClass;
		$selectedTimezone = '';
		$selectedPrefix   = '';
		$selectedLink     = '';
		if ( ! empty( $_GET['id'] ) ) {
			$get_record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( absint( $_GET['id'] ) );
			if ( ! tribe_is_error( $get_record ) ) {
				$record           = $get_record;
				$selectedTimezone = get_post_meta( $record->post->ID, Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . 'timezone', true );
				$selectedPrefix   = get_post_meta( $record->post->ID, Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . 'prefix', true );
				$selectedLink     = get_post_meta( $record->post->ID, Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . 'link', true );
			}
		}
		$prefixValue = empty( $selectedPrefix ) ? "" : $selectedPrefix;
		$linkValue   = empty( $selectedLink ) ? "" : $selectedLink;
		$timezones   = DateTimeZone::listIdentifiers( DateTimeZone::ALL );

		/** @var Plugin $plugin */
		$plugin = tribe( Plugin::class );

		include_once $plugin->plugin_path . 'views/options.php';
	}

	/**
	 * Filters event info before being saved or updated
	 *
	 * @param array $event
	 *
	 * @return array
	 */
	public function filter_imported_event( $event, $record ) {
		$meta                     = $record->meta;
		$event['EventEAImportId'] = $record->post->post_parent;

		if ( ! empty( $meta['prefix'] ) && strpos( $event['post_title'], $meta['prefix'] ) !== 0 ) {
			$event['post_title'] = $meta['prefix'] . ' ' . $event['post_title'];
		}

		if ( ! empty( $meta['link'] ) ) {
			$event['EventURL'] = $meta['link'];
		}

		if ( ! empty( $meta['timezone'] ) ) {
			$event = $this->adjust_timezone( $event, $meta );
		}

		return $event;
	}

	private function adjust_timezone( $event, $meta ) {
		if ( ! empty( $event['EventAllDay'] ) && tribe_is_truthy( $event['EventAllDay'] ) ) {
			$event['EventTimezone'] = $meta['timezone'];

			return $event;
		}

		try {
			$utc           = new DateTimeZone( "UTC" );
			$target_offset = timezone_offset_get( timezone_open( $meta['timezone'] ), new DateTime( 'now', $utc ) );
			if ( empty( $event['EventUTCStartDate'] ) ) {
				$start          = new DateTime( $event['EventUTCStartDate'], $utc );
				$end            = new DateTime( $event['EventUTCEndDate'], $utc );
				$targetInterval = DateInterval::createFromDateString( $target_offset . ' seconds' );
			} else {
				$event['EventTimezone'] = str_replace( 'UTC', 'Etc/GMT', $event['EventTimezone'] );
				$eventOffset            = timezone_offset_get( timezone_open( $event['EventTimezone'] ), new DateTime( 'now', $utc ) );
				$currTimezone           = new DateTimeZone( $event['EventTimezone'] );
				$start                  = new DateTime( $event['EventStartDate'] . ' ' . $event['EventStartHour'] . ':' . $event['EventStartMinute'], $currTimezone );
				$end                    = new DateTime( $event['EventEndDate'] . ' ' . $event['EventEndHour'] . ':' . $event['EventEndMinute'], $currTimezone );
				$offsetDiff             = (int) $target_offset - (int) $eventOffset;
				$targetInterval         = DateInterval::createFromDateString( $offsetDiff . ' seconds' );
			}
			$start->add( $targetInterval );
			$end->add( $targetInterval );
			$event['EventStartDate']   = $start->format( 'Y-m-d' );
			$event['EventStartHour']   = $start->format( 'H' );
			$event['EventStartMinute'] = $start->format( 'i' );
			$event['EventEndDate']     = $end->format( 'Y-m-d' );
			$event['EventEndHour']     = $end->format( 'H' );
			$event['EventEndMinute']   = $end->format( 'i' );
			$event['EventTimezone']    = $meta['timezone'];

			return $event;
		} catch ( \Exception $e ) {
			return $event;
		}
	}

	public function filter_import_meta( $meta ) {
		$post_data = empty( $_POST['aggregator'] ) ? [] : $_POST['aggregator'];

		$meta['prefix']   = empty( $post_data['prefix'] ) ? '' : sanitize_text_field( $post_data['prefix'] );
		$meta['link']     = empty( $post_data['link'] ) ? '' : sanitize_text_field( $post_data['link'] );
		$meta['timezone'] = empty( $post_data['timezone'] ) ? '' : sanitize_text_field( $post_data['timezone'] );

		return $meta;
	}

	public function store_import_meta( $record, $data ) {
		$record->update_meta( 'prefix', empty( $data['prefix'] ) ? null : $data['prefix'] );
		$record->update_meta( 'link', empty( $data['link'] ) ? null : $data['link'] );
		$record->update_meta( 'timezone', empty( $data['timezone'] ) ? null : $data['timezone'] );
	}
}