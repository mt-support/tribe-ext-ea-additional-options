<?php

namespace Tribe\Extensions\EA_Additional_Options\Modules;

use TEC\Common\Admin\Entities\Div;
use TEC\Common\Admin\Entities\Heading;
use TEC\Common\Admin\Entities\Container;
use TEC\Common\Admin\Entities\Field_Wrapper;
use Tribe\Utils\Element_Classes as Classes;

/**
 * Do the Settings.
 *
 * @since 1.0.0
 */
class Settings {
	/**
	 * The Settings Helper class.
	 *
	 * @since 1.0.0
	 *
	 * @var Settings_Helper
	 */
	protected Settings_Helper $settings_helper;

	/**
	 * The prefix for our settings keys.
	 *
	 * @since 1.0.0
	 *
	 * @see get_options_prefix() Use this method to get this property's value.
	 *
	 * @var string
	 */
	const PREFIX = 'tribe_ext_ea_opts_';

	/**
	 * Settings constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Settings_Helper $settings_helper
	 */
	public function __construct( Settings_Helper $settings_helper ) {
		$this->settings_helper = $settings_helper;
	}

	/**
	 * Running the hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function hook() {
		add_filter( 'tec_events_settings_tab_imports_fields', [ $this, 'add_additional_options' ] );
		add_filter( 'tribe_general_settings_maintenance_section', [ $this, 'add_maintenance_settings' ] );
	}

	/**
	 * Add the settings fields for the additional options.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 Added setting for Block editor template.
	 * @since 1.5.0 Renamed from `add_settings`.
	 *              Updated logic for the new admin UI.
	 *
	 * @param array $fields The fields for the imports settings tab.
	 *
	 * @return array
	 */
	public function add_additional_options( $fields ) {
		$content_block          = new Div( new Classes( [ 'tec-settings-form__content-section' ] ) );
		$section_header_classes = new Classes( [ 'tec-settings-form__section-header', 'tec-settings-form__section-header--sub' ] );

		/**
		 * Helper function for wrapping fields.
		 *
		 * This will take the container and an array of fields, and the fields will all be
		 * wrapped in a Field_Wrapper object and added to the container.
		 *
		 * @param Container $container The container to add the fields to.
		 * @param array     $fields    Array of field data.
		 *
		 * @return void
		 */
		$wrap_fields = function ( Container $container, array $fields ) {
			foreach ( $fields as $field_id => $field ) {
				$container->add_child(
					new Field_Wrapper(
						new \Tribe__Field(
							$field_id,
							$field
						)
					)
				);
			}
		};

		$additional_options = ( clone $content_block )->add_child(
			new Heading( __( 'Additional Options', 'tribe-ext-ea-additional-options' ), 3, $section_header_classes ),
		);

		$fields_setup = [
			self::PREFIX . 'delete_duplicate_removed_events' => [
				'type'            => 'radio',
				'label'           => esc_html__( 'Delete Duplicate/Removed Events for Scheduled Imports', 'tribe-ext-ea-additional-options' ),
				'tooltip'         => sprintf(
				/* translators: %1$s: opening strong tag; %2$s: closing strong tag */
					esc_html__(
						'Check this box to delete events that are removed from the import source. This will also remove duplicates in the case where the source changes the unique identifier for an event. %1$sNOTE: If your "Event Update Authority" setting is "Do not re-import events...", this setting will have no effect.%2$s', 'tribe-ext-ea-additional-options'
					),
					'<strong>',
					'</strong>'
				),
				'validation_type' => 'options',
				'default'         => 'no',
				'options'         => [
					'no'        => __( 'Do not delete duplicate/removed events.', 'tribe-ext-ea-additional-options' ),
					'trash'     => __( 'Send duplicate/removed events to trash.', 'tribe-ext-ea-additional-options' ),
					'permanent' => __( 'Permanently delete duplicate/removed events.', 'tribe-ext-ea-additional-options' ),
				],
			],
			self::PREFIX . 'link_directly_to_website_url'    => [
				'type'            => 'radio',
				'label'           => esc_html__( 'Link Directly to Website URL, Bypassing Default Event Page', 'tribe-ext-ea-additional-options' ),
				'tooltip'         => sprintf(
				/* translators: %1$s: opening strong tag; %2$s: closing strong tag */
					esc_html__( 'Instead of linking to the Event page within The Events Calendar, enable this option so that visitors can be sent directly to the URL in the Website URL field. %1$sNOTE: This setting only affects legacy views and will not work in the upgraded views.%2$s', 'tribe-ext-ea-additional-options' ),
					'<strong>',
					'</strong>'
				),
				'validation_type' => 'options',
				'default'         => 'no',
				'options'         => [
					'no'  => __( 'Link to the default single event page.', 'tribe-ext-ea-additional-options' ),
					'yes' => __( 'Link directly to the event website URL', 'tribe-ext-ea-additional-options' ),
				],
			],
			self::PREFIX . 'retain_line_breaks'              => [
				'type'            => 'radio',
				'label'           => esc_html__( 'Retain Line Breaks in Event Description', 'tribe-ext-ea-additional-options' ),
				'tooltip'         => esc_html__( 'Some import sources allow for linebreaks. Choose whether to remove linebreaks or keep them.', 'tribe-ext-ea-additional-options' ),
				'validation_type' => 'options',
				'default'         => 'no',
				'options'         => [
					'no'  => __( 'Remove all line breaks from event descriptions.', 'tribe-ext-ea-additional-options' ),
					'yes' => __( 'Retain all line breaks within event descriptions.', 'tribe-ext-ea-additional-options' ),
				],
			],
			self::PREFIX . 'default_template'                => [
				'type'            => 'dropdown',
				'label'           => esc_html__( 'Block editor template', 'tribe-ext-ea-additional-options' ),
				'tooltip'         => $this->get_template_tooltip(),
				'default'         => false,
				'validation_type' => 'options',
				'options'         => $this->get_template_options(),
				'can_be_empty'    => true,
				'size'            => 'small',
				'attributes'      => $this->get_template_attributes(),
			],
		];

		$wrap_fields(
			$additional_options,
			$fields_setup
		);

		$fields[] = $additional_options;

		return $fields;
	}

	/**
	 * Get all draft events to show them as options in the Block editor template dropdown.
	 *
	 * @since 1.4.0
	 *
	 * @return array
	 */
	public function get_template_options(): array {
		$args   = [
			'status' => 'draft',
		];
		$events = tribe_get_events( $args, false );

		// If the block editor is not enabled, then disable the setting.
		$block_editor = tribe_get_option( 'toggle_blocks_editor', null );
		if ( ! tribe_is_truthy( $block_editor ) ) {
			return [ esc_html__( 'The block editor for events is disabled!', 'tribe-ext-ea-additional-options' ) ];
		}

		$options = [
			''       => esc_html__( 'None (default)', 'tribe-ext-ea-additional-options' ),
			'enable' => esc_html__( 'Simple', 'tribe-ext-ea-additional-options' ),
		];

		foreach ( $events as $event ) {
			$options[ $event->ID ] = $event->post_title;
		}

		return $options;
	}

	/**
	 * Retrieve the attributes for the setting.
	 * If block editor for events is not enabled, then disable the field.
	 *
	 * @since 1.4.0
	 *
	 * @return array
	 */
	private function get_template_attributes(): array {
		$block_editor = tribe_get_option( 'toggle_blocks_editor', null );

		if ( tribe_is_truthy( $block_editor ) ) {
			return [];
		} else {
			return [
				'readonly' => 'readonly',
				'disabled' => 'disabled',
			];
		}
	}

	/**
	 * Retrieve the description of the Block editor template setting.
	 *
	 * @since 1.4.0
	 *
	 * @return string
	 */
	private function get_template_tooltip(): string {
		$block_editor = tribe_get_option( 'toggle_blocks_editor', null );

		if ( tribe_is_truthy( $block_editor ) ) {
			$tooltip = sprintf(
			/* translators: %1$s: opening code tag; %2$s: closing code tag; %3$s: opening strong tag; %s$4: closing strong tag */
				esc_html__( 'Select the draft event post to be used as a template. The post should only have the block structure and no content. Place %1$s[tec_ea_content]%2$s in a %3$sparagraph block%4$s where you want the description of the imported event to show up.', 'tribe-ext-ea-additional-options' ),
				'<code>',
				'</code>',
				'<strong>',
				'</strong>'
			);
			$tooltip .= " ";
			$tooltip .= sprintf(
			/* translators: %1$s: opening strong tag; %2$s: closing strong tag */
				esc_html__( '%1$sNOTE: If you are using a template, the "Retain Line Breaks ..." setting will have no effect. Line breaks will be retained.%2$s', 'tribe-ext-ea-additional-options' ),
				'<strong>',
				'</strong>'
			);
		} else {
			$tooltip = sprintf(
			/* translators: %1$s: opening anchor tag; %2$s: closing anchor tag */
				esc_html__( 'Please enable the block editor for events under %1$sEvents > Settings > General > Editing%2$s to be able to use this feature.', 'tribe-ext-ea-additional-options' ),
				'<a href="' . admin_url( 'edit.php?page=tec-events-settings&tab=general-editing-tab&post_type=tribe_events' ) . '">',
				'</a>'
			);
		}

		return $tooltip;
	}

	/**
	 * Add the extension's settings to the Maintenance page at the right spot.
	 *
	 * @since 1.5.0
	 *
	 * @param array $settings Array of settings.
	 *
	 * @return array
	 */
	public function add_maintenance_settings( array $settings ): array {
		$new_settings = [];

		foreach ( $settings as $key => $value ) {
			$new_settings[ $key ] = $value;

			if ( $key === 'trash-past-events' ) {
				$new_settings = array_merge( $new_settings, $this->add_maintenance_settings_fields() );
			}
		}

		return $new_settings;
	}

	/**
	 * Compile new settings fields for the Maintenance tab.
	 *
	 * @since 1.5.0
	 *
	 * @return array
	 */
	public function add_maintenance_settings_fields(): array {
		return [
			self::PREFIX . 'ignore_range' => [
				'type'            => 'dropdown',
				'label'           => esc_html__( 'Delete ignored events older than', 'tec-labs-remove-past-ignored-events' ),
				'tooltip'         => 'Ignored events that are more than this many days past will be permanently deleted.',
				'validation_type' => 'options',
				'size'            => 'small',
				'default'         => null,
				'options'         => [
					null => esc_html__( 'Disabled', 'tec-labs-remove-past-ignored-events' ),
					1    => esc_html__( '1 day', 'tec-labs-remove-past-ignored-events' ),
					7    => esc_html__( '7 days', 'tec-labs-remove-past-ignored-events' ),
					14   => esc_html__( '14 days', 'tec-labs-remove-past-ignored-events' ),
					30   => esc_html__( '30 days', 'tec-labs-remove-past-ignored-events' ),
				],
			],

			self::PREFIX . 'ignore_limit' => [
				'type'            => 'text',
				'label'           => esc_html__( 'Ignored events deleted in one run', 'tec-labs-remove-past-ignored-events' ),
				'tooltip'         => 'The number of ignored events that will be permanently deleted in one cron run. Note, setting this too high could exhaust server resources.',
				'validation_type' => 'positive_int',
				'size'            => 'small',
				'default'         => '15',
			],

			self::PREFIX . 'ignore_schedule' => [
				'type'            => 'dropdown',
				'label'           => esc_html__( 'Cron schedule', 'tec-labs-remove-past-ignored-events' ),
				'tooltip'         => 'A batch of ignored events will be deleted this often.',
				'validation_type' => 'options',
				'size'            => 'small',
				'default'         => 'daily',
				'options'         => [
					'hourly'     => esc_html__( 'Hourly', 'tec-labs-remove-past-ignored-events' ),
					'twicedaily' => esc_html__( 'Twice a day', 'tec-labs-remove-past-ignored-events' ),
					'daily'      => esc_html__( 'Daily', 'tec-labs-remove-past-ignored-events' ),
					'weekly'     => esc_html__( 'Weekly', 'tec-labs-remove-past-ignored-events' ),
				],
			],
		];
	}
}
