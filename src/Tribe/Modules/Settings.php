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
	protected $settings_helper;

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
	 * TODO: Update this entire class for your needs, or remove the entire `src` directory this file is in and do not load it in the main plugin file.
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
	 * TODO: Move it to where you want and update this docblock. If you like it here, just delete this TODO.
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
				'tooltip'         => esc_html__( 'Check this box to delete events that are removed from the import source. This will also remove duplicates in the case where the source changes the unique identifier for an event. ** NOTE: If your "Event Update Authority" setting is "Do not re-import events...", this setting will have no effect.', 'tribe-ext-ea-additional-options' ),
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
				'tooltip'         => esc_html__( 'Instead of linking to the Event page within The Events Calendar, enable this option so that visitors can be sent directly to the URL in the Website URL field. ** NOTE: This setting only affects legacy views and will not work in the upgraded views. **', 'tribe-ext-ea-additional-options' ),
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
					'yes' => __( 'Retain all line breaks within event descirptions.', 'tribe-ext-ea-additional-options' ),
				],
			],
		];

		$this->settings_helper->add_fields(
			$fields, 'imports', // not the 'event-tickets' ("Tickets" tab) because it doesn't exist without Event Tickets
			'tribe_aggregator_disable', false
		);
	}
}
