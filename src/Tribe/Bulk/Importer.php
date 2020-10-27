<?php
namespace Tribe\Extensions\EA_Additional_Options\Bulk;


// Tribe__Events__Importer__File_Importer
use Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer\Frequency;
use Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer\Nullable;
use Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer\Origin;
use Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer\Type;
use Tribe__Events__Aggregator__Records;

class Importer extends \Tribe__Events__Importer__File_Importer {
	protected $required_fields = [
		'origin',
		'type',
		'url',
	];

	protected $sanitizer = [
		'type'      => Type::class,
		'origin'    => Origin::class,
		'frequency' => Frequency::class,
	];

	protected function match_existing_post( array $record ) {
		return false;
	}

	protected function update_post( $post_id, array $record ) {
		// TODO:
	}

	protected function create_post( array $record ) {
		$meta = [
			'import_name' => $this->get_value_by_key( $record, 'name' ),
			'origin'      => $this->get_value_by_key( $record, 'origin' ),
			'type'        => $this->get_value_by_key( $record, 'type' ),
			'source'      => $this->get_value_by_key( $record, 'url' ),
			'frequency'   => $this->get_value_by_key( $record, 'schedule' ),
			'radius'      => $this->get_value_by_key( $record, 'radius' ),
			'location'    => $this->get_value_by_key( $record, 'location' ),
			'start'       => $this->get_value_by_key( $record, 'date' ),
		];

		$keywords = $this->get_value_by_key( $record, 'keywords' );

		if ( empty( $keywords ) ) {
			$keywords = $this->get_value_by_key( $record, 'keywords)' );
		}

		$meta['import_name'] = uniqid( 'My_test_', true );

		$meta['keywords'] = $keywords;

		foreach ( $meta as $key => $value ) {
			try {
				if ( isset( $this->sanitizer[ $key ] ) ) {
					$meta[ $key ] = ( new $this->sanitizer[$key]( $meta ) )->sanitize( $value );
				} else {
					$meta[ $key ] = ( new Nullable( $meta ) )->sanitize( $value );
				}
			} catch ( \InvalidArgumentException $exception ) {
				if ( $this->aggregator_record instanceof \Tribe__Events__Aggregator__Record__Abstract ) {
					$this->aggregator_record->meta['activity']->add( 'skipped', [ uniqid( 'ea_bulk_importer', true ) ] );
				}

				do_action( 'tribe_log', 'debug', __CLASS__, [
					'key'   => $key,
					'value' => $value,
					'meta'  => $meta,
					'error' => $exception->getMessage(),
				] );

				return false;
			}
		}


		$record = Tribe__Events__Aggregator__Records::instance()->get_by_origin( $meta['origin'] );
		$record->create( $meta['type'], [], $meta );

		if ( $record->post instanceof \WP_Post ) {
			$record->queue_import();
			$record->set_status_as_pending();

			$record->finalize();

			if ( 'schedule' === $record->meta['type'] ) {
				$create_schedule_result = $record->create_schedule_record();

				if ( is_wp_error( $create_schedule_result ) ) {
					$record->set_status_as_failed( $create_schedule_result );

					return false;
				}
			}

			$record->update_meta( 'interactive', true );
			$record->process_posts( [], true );

			return $record->id;
		}

		return false;
	}
}
