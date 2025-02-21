<?php

namespace Tribe\Extensions\EA_Additional_Options\Modules;

class Maintenance {
	public function hook() {
		add_action( 'init', [ $this, 'cron_setup' ] );
		add_action( 'tec_delete_old_ignored_events_cron', [ $this, 'delete_ignored_events' ] );
	}

	/**
	 * Set up a cron job to delete old ignored events.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function cron_setup() {
		$range        = tribe_get_option( Settings::PREFIX . 'ignore_range', null );
		$schedule     = tribe_get_option( Settings::PREFIX . 'ignore_schedule', 'daily' );
		$set_schedule = wp_get_schedule( 'tec_delete_old_ignored_events_cron' );

		// Delete the cron if setting is disabled or the schedule changed, and it's not manually run.
		if (
			empty( $range )
			|| $schedule !== $set_schedule
			   && $set_schedule
		) {
			$timestamp = wp_next_scheduled('tec_delete_old_ignored_events_cron');
			wp_unschedule_event( $timestamp, 'tec_delete_old_ignored_events_cron' );
		}

		// Bail if setting is disabled.
		if ( empty( $range ) ) {
			return;
		}

		// Set up cron schedule.
		if ( ! wp_next_scheduled( 'tec_delete_old_ignored_events_cron' ) ) {
			wp_schedule_event( time(), $schedule, 'tec_delete_old_ignored_events_cron' );
		}
	}

	/**
	 * Delete ignored events that have been ignored for more than 30 days.
	 *
	 * @return void
	 */
	public function delete_ignored_events() {
		$range = tribe_get_option( Settings::PREFIX . 'ignore_range', 14 );

		// Bail if setting is disabled. Just in case.
		if ( empty( $range ) ) {
			return;
		}

		$range = '-' . $range . ' days';
		$limit = tribe_get_option( Settings::PREFIX . 'ignore_limit', 15 );

		$result = tribe_events()
			->where( 'post_status', 'tribe-ignored')
			->where( 'ends_before', $range )
			->order_by( 'start_date', 'ASC' )
			->per_page( $limit )
			->get_ids();

		foreach ( $result as $event_id ) {
			$event_id = \TEC\Events\Custom_Tables\V1\Models\Occurrence::normalize_id ( $event_id );
			wp_delete_post( $event_id, true );
		}
	}
}