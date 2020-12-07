<?php
namespace Tribe\Extensions\EA_Additional_Options\Bulk;

use Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer\Boolean;
use Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer\Category;
use Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer\Frequency;
use Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer\Nullable;
use Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer\Origin;
use Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer\Radius;
use Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer\Type;
use Tribe\Extensions\EA_Additional_Options\Bulk\Sanitizer\Url;
use Tribe__Events__Aggregator__Records;

class Importer extends \Tribe__Events__Importer__File_Importer {
	protected $required_fields = [
		'origin',
		'type',
		'url',
	];

	protected $sanitizer = [
		'type'                   => Type::class,
		'origin'                 => Origin::class,
		'frequency'              => Frequency::class,
		'category'               => Category::class,
		'delete_upcoming_events' => Boolean::class,
		'source'                 => Url::class,
		'link'                   => Url::class,
		'radius'                 => Radius::class,
	];

	/**
	 * If a new record is create only try to find if existing for schedule imports one time imports always returns
	 * `false` as those are not saved so those can't be updated so any new one time import is create from scratch,
	 * using the current system to match imports used by TEC with the import hash to find if a record already exists.
	 *
	 * @since TBD
	 *
	 * @param array $record
	 *
	 * @return int|boolean Return the ID of the record if found otherwise false.
	 */
	protected function match_existing_post( array $record ) {
		$meta = $this->populate_meta( $record );

		// If meta does not exists or this is a one time import no match is required.
		if ( empty( $meta ) || empty( $meta['type'] ) || empty( $meta['source'] ) || $meta['type'] === 'manual' ) {
			return false;
		}

		$hash = $this->get_hash( $meta );

		/** @var Tribe__Events__Aggregator__Records $records */
		$records = tribe( 'events-aggregator.records' );
		$matches = $records->query( [
			'post_status'    => Tribe__Events__Aggregator__Records::$status->schedule,
			'meta_query'     => [
				[
					'key'   => $records->prefix_meta( 'source' ),
					'value' => $meta['source'],
				],
				[
					'key'   => $records->prefix_meta( 'hash' ),
					'value' => $hash,
				],
			],
			'fields'         => 'ids',
			'posts_per_page' => 1,
		] );

		if ( $matches->have_posts() ) {
			return reset( $matches->posts );
		}

		return false;
	}

	/**
	 * Create a new hash based on the meta values from the record. The hash value is used to match
	 * repeated imports.
	 *
	 * @since TBD
	 *
	 * @param array $meta
	 *
	 * @return string The hash value
	 */
	private function get_hash( array $meta ): string {
		$hash = array_filter( $meta );

		$valid_keys = [
			'import_name',
			'origin',
			'type',
			'source',
			'frequency',
			'radius',
			'location',
			'start',
			'keywords',
		];

		// remove non-needed data from the Hash of the Record.
		$meta = array_filter(
			$hash,
			static function ( $key ) use ( $valid_keys ) {
				return in_array( $key, $valid_keys, true );
			},
			ARRAY_FILTER_USE_KEY
		);

		ksort( $meta );

		return md5( maybe_serialize( $meta ) );
	}

	/**
	 * If the record already exists on the set of imports to be created just return the ID and don't
	 * update the values of the import.
	 *
	 * @param       $post_id
	 * @param array $record
	 *
	 * @return integer The ID of the updated post.
	 */
	protected function update_post( $post_id, array $record ) {

		$meta = $this->populate_meta( $record );

		/** @var Tribe__Events__Aggregator__Records $records */
		$records = tribe( 'events-aggregator.records' );

		foreach ( $meta as $key => $value ) {
			if ( $value === null ) {
				delete_post_meta( $post_id, $records->prefix_meta( $key ) );
			} else {
				update_post_meta( $post_id, $records->prefix_meta( $key ), $value );
			}
		}

		$this->log( 'updated', $post_id );

		return $post_id;
	}

	/**
	 * Create a new EA record if all the details of the record are correct. A record only is created ia match was not
	 * found.
	 *
	 * @since TBD
	 *
	 * @param array<string, mixed> $record An array with the details of the record.
	 *
	 * @return boolean|integer False if the record can't be created or the ID of the record if successfully created.
	 */
	protected function create_post( array $record ) {
		$meta = $this->populate_meta( $record );

		if ( empty( $meta ) ) {
			$this->log( 'skipped', md5( maybe_serialize( $meta ) ) );

			return false;
		}

		$this->log( 'created', md5( maybe_serialize( $meta ) ) );

		return $this->process_import( $meta );
	}

	/**
	 * Create a new array with all the required data to create a new import for EA.
	 *
	 * @since TBD
	 *
	 * @param array $record An array with the details of the CSV Row.
	 *
	 * @return array
	 */
	private function populate_meta( $record ): array {
		$meta = [
			'import_name'            => sanitize_text_field( trim( $this->get_value_by_key( $record, 'name' ) ) ),
			'origin'                 => $this->get_value_by_key( $record, 'origin' ),
			'type'                   => $this->get_value_by_key( $record, 'type' ),
			'source'                 => $this->get_value_by_key( $record, 'url' ),
			'frequency'              => $this->get_value_by_key( $record, 'schedule' ),
			'radius'                 => $this->get_value_by_key( $record, 'radius' ),
			'location'               => $this->get_value_by_key( $record, 'location' ),
			'start'                  => $this->get_value_by_key( $record, 'date' ),
			'prefix'                 => $this->get_value_by_key( $record, 'title_prefix' ),
			'timezone'               => $this->get_value_by_key( $record, 'event_timezone' ),
			'link'                   => $this->get_value_by_key( $record, 'event_url' ),
			'delete_upcoming_events' => $this->get_value_by_key( $record, 'delete_upcoming_events' ),
			'post_status'            => $this->get_value_by_key( $record, 'event_status' ),
			'category'               => $this->get_value_by_key( $record, 'event_category' ),
		];

		$keywords = $this->get_value_by_key( $record, 'keywords' );
		// Fallback to the "keywords)" if keywords does not exists.
		if ( empty( $keywords ) ) {
			$keywords = $this->get_value_by_key( $record, 'keywords)' );
		}

		$meta['keywords'] = $keywords;

		return $this->sanitize( $meta );
	}

	/**
	 * Sanitize the input according to the sanitizer map if a sanitizer is defined for a specific key, the sanitizer is
	 * used in order to make sure the value used during the creation of the record is correctly defined.
	 *
	 * @param array $meta
	 *
	 * @return array An array with the results of the sanitization empty array if error presented.
	 */
	private function sanitize( array $meta ): array {
		foreach ( $meta as $key => $value ) {
			try {
				if ( isset( $this->sanitizer[ $key ] ) ) {
					$meta[ $key ] = ( new $this->sanitizer[$key]( $meta ) )->sanitize( $value );
				} else {
					$meta[ $key ] = ( new Nullable( $meta ) )->sanitize( $value );
				}
			} catch ( \InvalidArgumentException $exception ) {
				do_action( 'tribe_log', 'debug', __CLASS__, [
					'key'   => $key,
					'value' => $value,
					'meta'  => $meta,
					'error' => $exception->getMessage(),
				] );

				return [];
			}
		}

		return $meta;
	}

	/**
	 * Log into the activity if present the details of the import to have a number of how many records were
	 * created after the import is completed.
	 *
	 * @since TBD
	 *
	 * @param string $action The type of action to be reported.
	 * @param mixed  $item   Details or unique identifier of the item being logged.
	 */
	private function log( $action, $item ) {
		if ( $this->aggregator_record instanceof \Tribe__Events__Aggregator__Record__Abstract ) {
			$this->aggregator_record->meta['activity']->add( 'ea', $action, $item );
		}
	}

	/**
	 * Process and import by creating a new record with the provided details.
	 *
	 * @since TBD
	 *
	 * @param array<string, mixed> $meta An array with the meta information to create a new EA Record.
	 *
	 * @return boolean|integer False if the import can't be created or the ID of the created record.
	 */
	private function process_import( array $meta = [] ) {
		$record = Tribe__Events__Aggregator__Records::instance()->get_by_origin( $meta['origin'] );

		$meta['hash']             = $this->get_hash( $meta );
		$meta['allow_batch_push'] = true;

		$record->create( $meta['type'], [], $meta );

		if ( ! $record->post instanceof \WP_Post ) {
			return false;
		}

		$record->set_status_as_pending();
		$record->finalize();

		if ( 'schedule' === $meta['type'] ) {
			$create_schedule_result = $record->create_schedule_record();

			if ( is_wp_error( $create_schedule_result ) ) {
				$record->set_status_as_failed( $create_schedule_result );

				return $create_schedule_result;
			}
		}

		$record->queue_import();
		$record->process_posts( [], true );

		return $record->id;
	}
}
