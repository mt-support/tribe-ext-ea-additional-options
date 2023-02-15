<?php

namespace Tribe\Extensions\EA_Additional_Options\Modules;

/**
 * Do the Settings.
 */
class Settings {
	/**
	 * The Settings Helper class.
	 *
	 * @var Settings_Helper
	 */
	protected Settings_Helper $settings_helper;

	/**
	 * The prefix for our settings keys.
	 *
	 * @see get_options_prefix() Use this method to get this property's value.
	 *
	 * @var string
	 */
	const PREFIX = 'tribe_ext_ea_opts_';

	/**
	 * Settings constructor.
	 *
	 * @param Settings_Helper $settings_helper
	 */
	public function __construct( Settings_Helper $settings_helper ) {
		$this->settings_helper = $settings_helper;
	}

	public function hook() {
		add_action( 'admin_init', [ $this, 'add_settings' ] );
	}

	/**
	 * Adds a new section of fields to Events > Settings > General tab, appearing after the "Map Settings" section
	 * and before the "Miscellaneous Settings" section.
	 *
	 */
	public function add_settings() {
		$fields = [
			self::PREFIX . 'heading'                         => [
				'type' => 'html',
				'html' => '<h3>' . esc_html__( 'Additional Options', 'tribe-ext-ea-additional-options' ) . '</h3>',
			],
			self::PREFIX . 'delete_duplicate_removed_events' => [
				'type'            => 'radio',
				'label'           => esc_html__( 'Delete Duplicate/Removed Events for Scheduled Imports', 'tribe-ext-ea-additional-options' ),
				'tooltip' => sprintf(
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
				'tooltip' => sprintf(
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

		$this->settings_helper->add_fields(
			$fields, 'imports',
			'tribe_aggregator_disable',
			false
		);
	}

	/**
	 * Get all draft events to show them as options in the dropdown.
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
	 * Retrieve the description of the setting.
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
		} else {
			$tooltip = sprintf(
			/* translators: %1$s: opening anchor tag; %2$s: closing anchor tag */
				esc_html__( 'Please enable the block editor for events under %1$sEvents > Settings > General%2$s to be able to use this feature.', 'tribe-ext-ea-additional-options' ),
				'<a href="' . admin_url( 'edit.php?page=tec-events-settings&tab=general&post_type=tribe_events#tec-settings-general-editing' ) . '">',
				'</a>'
			);
		}

		return $tooltip;
	}
}
