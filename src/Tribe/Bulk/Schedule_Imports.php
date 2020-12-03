<?php

namespace Tribe\Extensions\EA_Additional_Options\Bulk;

use Tribe__Events__Aggregator__Record__Activity;
use Tribe__Events__Aggregator__Records;
use Tribe__Events__Importer__File_Importer;

class Schedule_Imports {
	public function hook() {
		add_filter( 'tribe_aggregator_csv_post_types', [ $this, 'add_bulk_import_type' ] );
		add_filter( 'tribe_event_import_tribe-ea-record_column_names', [ $this, 'map_columns' ] );
		add_filter( 'tribe_aggregator_csv_column_mapping', [ $this, 'csv_column_mapping' ] );
		add_filter( 'tribe_events_import_tribe-ea-record_importer', [ $this, 'define_importer' ], 10, 2 );
		add_action( 'tribe_aggregator_record_activity_wakeup', [ $this, 'activity_wakeup' ] );
	}

	public function add_bulk_import_type( array $types = [] ) {
		$types[] = get_post_type_object( Tribe__Events__Aggregator__Records::$post_type );

		return $types;
	}

	public function csv_column_mapping( array $mapping = [] ) {
		$mapping['tribe-ea-record'] = [];

		return $mapping;
	}

	public function map_columns() {
		return [
			'origin'                 => __( 'Import Origin', 'tribe-ext-ea-additional-options' ),
			'name'                   => __( 'Import Name', 'tribe-ext-ea-additional-options' ),
			'type'                   => __( 'Import Type', 'tribe-ext-ea-additional-options' ),
			'schedule'               => __( 'Import Schedule', 'tribe-ext-ea-additional-options' ),
			'url'                    => __( 'URL', 'tribe-ext-ea-additional-options' ),
			// Backward compatibility with previous version of TEC.
			'keywords)'              => __( 'Refine by Keyword(s)', 'tribe-ext-ea-additional-options' ),
			'keywords'               => __( 'Refine by Keyword(s)', 'tribe-ext-ea-additional-options' ),
			'date'                   => __( 'Refine by Date', 'tribe-ext-ea-additional-options' ),
			'location'               => __( 'Refine by Location', 'tribe-ext-ea-additional-options' ),
			'radius'                 => __( 'Refine by Radius', 'tribe-ext-ea-additional-options' ),
			'event_status'           => __( 'Event Status', 'tribe-ext-ea-additional-options' ),
			'event_category'         => __( 'Event Category', 'tribe-ext-ea-additional-options' ),
			'event_timezone'         => __( 'Event Time Zone', 'tribe-ext-ea-additional-options' ),
			'title_prefix'           => __( 'Event Title Prefix', 'tribe-ext-ea-additional-options' ),
			'delete_upcoming_events' => __( 'Delete Upcoming Events', 'tribe-ext-ea-additional-options' ),
		];
	}

	public function define_importer( $importer, $file_reader ) {
		if ( $importer instanceof Tribe__Events__Importer__File_Importer ) {
			return $importer;
		}

		return new Importer( $file_reader );
	}

	public function activity_wakeup( Tribe__Events__Aggregator__Record__Activity $activity ) {
		$activity->register( 'tribe-ea-record', [ 'ea' ] );
	}
}
