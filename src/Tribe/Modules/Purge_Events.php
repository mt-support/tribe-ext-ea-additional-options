<?php

namespace Tribe\Extensions\EA_Additional_Options\Modules;

use Tribe__Events__Aggregator__Record__Abstract as Record_Abstract;
use Tribe__Events__Aggregator__Record__Queue_Processor;

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

		$prefix = Record_Abstract::$meta_key_prefix;

		$starts_on_or_after = empty( $meta['start'] ) ? date( 'Y-m-d 00:00:00' ) : $meta['start'] . ' 00:00:00';

		$events = tribe_events()
			->where( 'meta_less_than', "{$prefix}origin_record_id", $meta['recent_child'] )
			->where( 'meta', "{$prefix}source", $meta['source'] )
			->where( 'starts_on_or_after', $starts_on_or_after )
			->per_page( (int) apply_filters( 'tribe_aggregator_batch_size', Tribe__Events__Aggregator__Record__Queue_Processor::$batch_size ) * 3 )
			->all();

		foreach ( $events as $post ) {
			tribe_delete_event( $post instanceof \WP_Post ? $post->ID : $post, true );
		}
	}
}
