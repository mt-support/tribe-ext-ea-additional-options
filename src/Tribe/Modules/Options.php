<?php


namespace Tribe\Extensions\EA_Additional_Options\Modules;


use DateInterval;
use DateTime;
use DateTimeZone;
use stdClass;
use Tribe\Extensions\EA_Additional_Options\Plugin;
use Tribe__Events__Aggregator__Record__Abstract;
use Tribe__Events__Aggregator__Records;

class Options {
	public function hook() {
		add_action( 'tribe_events_aggregator_import_form_preview_options', [ $this, 'add_import_options' ] );

		add_filter( 'tribe_aggregator_before_insert_event', [ $this, 'filter_imported_event' ], 10, 2 );
		add_filter( 'tribe_aggregator_before_update_event', [ $this, 'filter_imported_event' ], 10, 2 );
		add_filter( 'tribe_aggregator_before_save_event', [ $this, 'filter_imported_event' ], 10, 2 );

		add_filter( 'tribe_aggregator_import_submit_meta', [ $this, 'filter_import_meta' ], 10, 2 );

		add_filter( 'tribe_events_aggregator_tabs_new_handle_import_finalize', [
			$this,
			'store_import_meta',
		], 10, 2 );
		add_filter( 'tribe_events_mu_defaults', [ $this, 'mu_defaults' ] );
	}

	/**
	 * HTML for the additional options for individual imports
	 */
	public function add_import_options() {
		$record           = new stdClass;
		$selectedTimezone = '';
		$selectedPrefix   = '';
		$selectedLink     = '';
		$delete_upcoming  = false;
		if ( ! empty( $_GET['id'] ) ) {
			$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( absint( $_GET['id'] ) );
			if ( $record instanceof Tribe__Events__Aggregator__Record__Abstract ) {
				$record           = $record;
				$selectedTimezone = get_post_meta( $record->post->ID, Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . 'timezone', true );
				$selectedPrefix   = get_post_meta( $record->post->ID, Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . 'prefix', true );
				$selectedLink     = get_post_meta( $record->post->ID, Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . 'link', true );
				$delete_upcoming  = tribe_is_truthy(
					get_post_meta( $record->post->ID, Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . 'delete_upcoming_events', true )
				);
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
			$event['post_name']  = trim( sanitize_title( $event['post_title'] ), '-' );
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
			$utc      = new DateTimeZone( "UTC" );
			$timezone = str_replace( ' ', '_', trim( $meta['timezone'] ) );
			$tz = new DateTimeZone( $timezone );

			$target_offset = timezone_offset_get( $tz, new DateTime( $event['EventStartDate'], $utc ) );

			$use_utc = ! empty( $event['EventUTCStartDate'] )
			           && ! empty( $event['EventUTCEndDate'] );

			$missing_event_details = empty( $event['EventStartDate'] )
			                         && empty( $event['EventEndDate'] )
			                         && empty( $event['EventStartHour'] )
			                         && empty( $event['EventEndHour'] )
			                         && empty( $event['EventStartMinute'] )
			                         && empty( $event['EventEndMinute'] )
			                         && empty( $event['EventTimezone'] );

			if ( $use_utc ) {
				$start          = new DateTime( $event['EventUTCStartDate'], $utc );
				$end            = new DateTime( $event['EventUTCEndDate'], $utc );
				$targetInterval = DateInterval::createFromDateString( $target_offset . ' seconds' );
			} else if ( $missing_event_details ) {
				return $event;
			} else {
				// If there is a meridian, and it's "pm" then adjust times for 24h format.
				if ( isset( $event['EventStartMeridian'] ) && isset( $event['EventEndMeridian'] ) ) {
					if ( strtolower( $event['EventStartMeridian'] ) === 'pm' && $event['EventStartHour'] < 12 ) {
						$event['EventStartHour'] += 12;
					}
					if ( strtolower( $event['EventEndMeridian'] ) === 'pm' && $event['EventEndHour'] < 12 ) {
						$event['EventEndHour'] += 12;
					}
				}

				$event['EventTimezone'] = str_replace( 'UTC', 'Etc/GMT', $event['EventTimezone'] );
				$eventOffset            = timezone_offset_get( timezone_open( $event['EventTimezone'] ), new DateTime( $event['EventStartDate'], $utc ) );
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
			$event['EventTimezone']    = $timezone;

			return $event;
		} catch ( \Exception $e ) {
			return $event;
		}
	}

	public function filter_import_meta( $meta ) {
		$post_data = empty( $_POST['aggregator'] ) ? [] : $_POST['aggregator'];

		$meta['prefix']                 = empty( $post_data['prefix'] ) ? '' : sanitize_text_field( $post_data['prefix'] );
		$meta['link']                   = empty( $post_data['link'] ) ? '' : sanitize_text_field( $post_data['link'] );
		$meta['timezone']               = empty( $post_data['timezone'] ) ? '' : sanitize_text_field( $post_data['timezone'] );
		$meta['delete_upcoming_events'] = ! empty( $post_data['delete_upcoming_events'] );

		return $meta;
	}

	public function store_import_meta( $record, $data ) {
		$record->update_meta( 'prefix', empty( $data['prefix'] ) ? null : $data['prefix'] );
		$record->update_meta( 'link', empty( $data['link'] ) ? null : $data['link'] );
		$record->update_meta( 'timezone', empty( $data['timezone'] ) ? null : $data['timezone'] );
		$record->update_meta( 'delete_upcoming_events', ! empty( $data['delete_upcoming_events'] ) );
	}

	public function mu_defaults( $tribe_events_mu_defaults ) {
		$tribe_events_mu_defaults[ Settings::PREFIX . 'delete_duplicate_removed_events' ] = 'no';
		$tribe_events_mu_defaults[ Settings::PREFIX . 'link_directly_to_website_url' ]    = 'no';
		$tribe_events_mu_defaults[ Settings::PREFIX . 'retain_line_breaks' ]              = 'no';

		return $tribe_events_mu_defaults;
	}
}
