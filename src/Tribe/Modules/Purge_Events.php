<?php

namespace Tribe\Extensions\EA_Additional_Options\Modules;

class Purge_Events {

	const IMPORT_HASH_META_KEY = '_tribe_aggregator_origin_import_hash_id';
	const RECORD_META_KEY = '_tribe_aggregator_origin_record_id';

	public function hook() {
		add_action( 'tribe_aggregator_before_insert_posts', [ $this, 'purge_items' ], 10, 2 );
		add_filter( 'tribe_aggregator_before_insert_event', [ $this, 'filter_imported_event' ], 10, 2 );
		add_filter( 'tribe_aggregator_before_update_event', [ $this, 'filter_imported_event' ], 10, 2 );
		add_action( 'tribe_aggregator_after_insert_post', [ $this, 'add_event_meta' ] );
	}

	public function filter_imported_event( $event, $record ) {
		if ( empty( $record ) || empty( $record->id ) || empty( $record->meta ) || empty( $record->meta['import_id'] ) ) {
			return $event;
		}

		$event['ImportHashId'] = $record->meta['import_id'];
		$event['RecordId']     = $record->id;

		return $event;
	}

	public function add_event_meta( $event ) {
		if ( empty( $event['ID'] ) ) {
			return;
		}

		if ( ! empty( $event['ImportHashId'] ) ) {
			update_post_meta( $event['ID'], self::IMPORT_HASH_META_KEY, $event['ImportHashId'] );
		}

		if ( ! empty( $event['RecordId'] ) ) {
			update_post_meta( $event['ID'], self::RECORD_META_KEY, $event['RecordId'] );
		}
	}

	public function purge_items( $items, $meta ) {

		if ( empty( $meta['delete_upcoming_events'] ) || empty( $meta['import_id'] ) || empty( $meta['recent_child'] ) ) {
			return;
		}

		$events = tribe_get_events( [
			'fields'         => 'ids',
			'posts_per_page' => apply_filters( 'tribe_ext_ea_additional_options_purge_items_per_page', 250 ),
			'post_status'    => 'any',
			'starts_after'   => empty( $meta['start'] )
				? date( 'Y-m-d H:i:s' )
				// The refine by date only has the Y-m-d values time is provided to follow the required format.
				: $meta['start'] . ' 00:00:00',
			'meta_query'     => [
				[
					'key'     => self::IMPORT_HASH_META_KEY,
					'value'   => $meta['import_id'],
					'compare' => '=',
				],
				[
					'key'     => self::RECORD_META_KEY,
					'value'   => $meta['recent_child'],
					// Pull any record that is before the current one as recent_child is the record prior to the current one.
					'compare' => '<=',
				],
			],
		] );

		foreach ( $events as $id ) {
			tribe_delete_event( $id, true );
		}
	}
}
