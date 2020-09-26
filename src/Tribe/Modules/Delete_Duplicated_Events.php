<?php

namespace Tribe\Extensions\EA_Additional_Options\Modules;

use Tribe__Events__Aggregator__Records;
use Tribe__Events__Aggregator__Settings;

class Delete_Duplicated_Events {

	public function hook() {
		add_filter( 'tribe_aggregator_before_insert_event', [ $this, 'filter_imported_event' ], 10, 2 );
		add_filter( 'tribe_aggregator_before_update_event', [ $this, 'filter_imported_event' ], 10, 2 );
		add_action( 'tribe_aggregator_after_insert_post', [ $this, 'add_event_meta' ] );

		$import_setting   = tribe_get_option( 'tribe_aggregator_default_update_authority', Tribe__Events__Aggregator__Settings::$default_update_authority );
		$deletion_setting = tribe_get_option( Settings::PREFIX . 'delete_duplicate_removed_events' );

		if ( 'retain' !== $import_setting && ! empty( $deletion_setting ) && $deletion_setting !== 'no' ) {
			add_action( 'save_post_tribe-ea-record', [ $this, 'record_finalized' ], 10, 2 );
		}
	}

	public function filter_imported_event( $event, $record ) {
		$event['EventEAImportId'] = $record->post->post_parent;

		return $event;
	}

	public function add_event_meta( $event ) {
		if ( empty( $event['EventEAImportId'] ) ) {
			return;
		}

		update_post_meta( $event['ID'], '_tribe_aggregator_parent_record', $event['EventEAImportId'] );
	}

	/**
	 * Process duplicate/removed events after import is complete.
	 */
	public function record_finalized( $post_id, $post ) {
		if ( $post->post_status !== Tribe__Events__Aggregator__Records::$status->success ) {
			return;
		}

		$deletion_setting = tribe_get_option( Settings::PREFIX . 'delete_duplicate_removed_events' );
		$pemanent_removal = $deletion_setting === 'permanent';
		$ids_to_delete    = tribe_get_events( [
			'fields'         => 'ids',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'ends_after'     => date( 'Y-m-d H:i:s' ),
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'     => '_tribe_aggregator_parent_record',
					'value'   => $post->post_parent,
					'compare' => '=',
				],
				[
					'key'     => '_tribe_aggregator_record',
					'value'   => $post_id,
					'compare' => '<',
				],
			],
		] );

		foreach ( $ids_to_delete as $eventId ) {
			tribe_delete_event( $eventId, $pemanent_removal );
		}
	}
}
