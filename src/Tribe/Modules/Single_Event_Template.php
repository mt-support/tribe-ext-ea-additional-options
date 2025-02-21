<?php

namespace Tribe\Extensions\EA_Additional_Options\Modules;

class Single_Event_Template {

	public function hook() {
		$template_setting     = tribe_get_option( Settings::PREFIX . 'default_template', false );
		$block_editor_enabled = tribe_get_option( 'toggle_blocks_editor', null );

		/**
		 * Make sure it only runs if:
		 * 1. The block editor for events is enabled.
		 * 2. A template is set up.
		 */
		if ( tribe_is_truthy( $block_editor_enabled ) && $template_setting ) {
			add_filter( 'tribe_aggregator_event_translate_service_data_field_map', [ $this, 'adjust_field_map' ], 10, 2 );
			add_filter( 'tribe_aggregator_before_save_event', [ $this, 'tec_ea_single_event_template' ], 11, 2 );
		}
	}

	/**
	 * Adjust the field map.
	 *
	 * @param array  $field_map The field map from the source to the event data.
	 * @param object $item      Item being translated.
	 *
	 * @return array
	 */
	public function adjust_field_map( array $field_map, object $item ): array {
		if (
			isset( $field_map['description'] )
			&& isset( $item->unsafe_description )
		) {
			unset( $field_map['description'] );
			$field_map['unsafe_description'] = 'post_content';
		}

		$field_map['start_date_utc'] = 'EventUTCStartDate';
		$field_map['end_date_utc']   = 'EventUTCEndDate';

		return $field_map;
	}

	/**
	 * Create a block editor version of the imported event(s) based on the draft event defined below by post ID.
	 *
	 * @param array $event The event array being handled.
	 * @param \Tribe__Events__Aggregator__Record__Abstract $data  The import metadata object.
	 *
	 * @return array The event array.
	 */
	public function tec_ea_single_event_template( array $event, \Tribe__Events__Aggregator__Record__Abstract $data ): array {

		// Make sure this is only done on valid origins.
		$valid_origins = [
			'ical',
			'gcal',
			'ics',
			'meetup',
		];

		/**
		 * Allows filtering the accepted origins.
		 * Useful if you want to limit it to certain type(s) of source(s).
		 *
		 * @var array $valid_origins The array of origins.
		 */
		$valid_origins = apply_filters( 'tribe_ext_ea_additional_options_valid_origins', $valid_origins );

		if ( ! in_array( $data->meta['origin'], $valid_origins ) ) {
			return $event;
		}

		$valid_sources = [];

		/**
		 * Allows filtering the source URLs to which the template should be applied to.
		 * If you want to limit this to a certain source URL, set it here.
		 *
		 * @var array $valid_sources The array of the import URLs
		 */
		$valid_sources = apply_filters( 'tribe_ext_ea_additional_options_valid_sources', $valid_sources );

		if (
			! empty( $valid_sources )
			&& ! in_array( $data->meta['source'], $valid_sources )
		) {
			return $event;
		}

		// Get the template from the saved setting.
		$template_post_id = tribe_get_option( Settings::PREFIX . 'default_template', false );

		if ( $template_post_id == 'enable' ) {
			$blocks = [
				'datetime'       => '<!-- wp:tribe/event-datetime /-->',
				'featured_image' => '<!-- wp:tribe/featured-image /-->',
				'content_start'  => '<!-- wp:paragraph {"placeholder":"Add Description..."} -->',
				'content'        => '<p>[tec_ea_content]</p>',
				'content_end'    => '<!-- /wp:paragraph -->',
				'organizer'      => '<!-- wp:tribe/event-organizer /-->',
				'venue'          => '<!-- wp:tribe/event-venue /-->',
				'sharing'        => '<!-- wp:tribe/event-links /-->',
				'related'        => '<!-- wp:tribe/related-events /-->',
				'comments'       => '<!-- wp:post-comments-form /-->',
			];

			/**
			 * Allows filtering the Simple block template.
			 *
			 * @var array $blocks The HTML markup of block elements in an array.
			 */
			$blocks = apply_filters( 'tribe_ext_ea_additional_options_simple_template', $blocks );

			$template = implode( "\n", $blocks );
		} else {
			$post = get_post( $template_post_id );

			// If there is no template, or not the right format then skip.
			if (
				! $post instanceof \WP_Post
				|| $post->post_type != 'tribe_events'
				|| $post->post_status != 'draft'
			) {
				return $event;
			}

			// Get the post content template.
			$template = $post->post_content;
		}

		// The post content from the source site.
		$description_from_source = $event['post_content'];

		// If there is an organizer in the source, then change it in the template.
		if ( isset( $event['Organizer'] ) ) {
			$template = str_replace(
				'<!-- wp:tribe/event-organizer /-->',
				'<!-- wp:tribe/event-organizer {"organizer":' . $event['Organizer']['OrganizerID'][0] . '} /-->',
				$template
			);
		}

		// If there is a venue in the source, then change it in the template.
		if (
			! empty( $event['EventVenueID'] )
			&& intval( $event['EventVenueID'] )
			&& get_post_type( $event['EventVenueID'] ) === 'tribe_venue'
		) {
			$template = str_replace(
				'<!-- wp:tribe/event-venue /-->',
				'<!-- wp:tribe/event-venue {"venue":' . $event['EventVenueID'] . '} /-->',
				$template
			);
		}

		// Convert line breaks to paragraphs.
		$search_line_breaks      = [
			'(\n\n)',
			'(\n)',
		];
		$replace_line_breaks     = [
			'</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>',
			'<br>',
		];
		$description_from_source = preg_replace(
			$search_line_breaks,
			$replace_line_breaks,
			$description_from_source
		);

		// Replace the placeholder with the content from the source.
		// Replace double paragraphs, just in case.
		$template = str_replace(
			[
				'[tec_ea_content]',
				'<p><p>',
				'</p></p>',
			],
			[
				$description_from_source,
				'<p>',
				'</p>',
			],
			$template
		);

		// Add new post_content based on the template.
		$event['post_content'] = $template;

		return $event;
	}
}
