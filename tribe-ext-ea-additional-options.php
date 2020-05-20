<?php
/**
 * Plugin Name:       Events Aggregator Extension: Additional Options
 * Plugin URI:        https://theeventscalendar.com/extensions/ea-additional-options/
 * GitHub Plugin URI: https://github.com/mt-support/tribe-ext-ea-additional-options
 * Description:       Adds extra options to Event Aggregator settings and imports
 * Version:           1.0.0
 * Extension Class:   Tribe__Extension__EA_Additional_Options
 * Author:            Modern Tribe, Inc.
 * Author URI:        http://m.tri.be/1971
 * License:           GPL version 3 or any later version
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       tribe-ext-ea-additional-options
 *
 *     This plugin is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     any later version.
 *
 *     This plugin is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *     GNU General Public License for more details.
 */
/**
 * Register and load the service provider for loading the extension.
 *
 * @since 5.0
 */
if (
        class_exists('Tribe__Extension') && !class_exists('Tribe__Extension__EA_Additional_Options')
) {

    /**
     * Extension main class, class begins loading on init() function.
     */
    class Tribe__Extension__EA_Additional_Options extends Tribe__Extension {

        /**
         * Namespace prefix for this extension's database options.
         *
         * @var string
         */
        protected $opts_prefix = 'tribe_ext_ea_opts_';

        /**
         * Setup the Extension's properties.
         *
         * This always executes even if the required plugins are not present.
         */
        public function construct() {
            // Dependency requirements and class properties can be defined here.
            // tested only with TEC 5.0+
            $this->add_required_plugin('Tribe__Events__Main', '5.0');
        }

        /**
         * Adds settings options.
         */
        public function add_settings() {
            if (!class_exists('Tribe__Extension__Settings_Helper')) {
                require_once dirname(__FILE__) . '/src/Tribe/Modules/Settings_Helper.php';
            }

            $setting_helper = new Tribe\Extensions\EA_Additional_Options\Settings_Helper();

            $fields = array(
                $this->opts_prefix . 'heading' => array(
                    'type' => 'html',
                    'html' => '<h3>' . esc_html__('Additional Options', 'tribe-ext-ea-additional-options') . '</h3>',
                ),
                $this->opts_prefix . 'delete_duplicate_removed_events' => array(
                    'type' => 'radio',
                    'label' => esc_html__('Delete Duplicate/Removed Events', 'tribe-ext-ea-additional-options'),
                    'tooltip' => esc_html__('By default, the Events Aggregator does not delete events that are removed from the import source. Check this box to delete these types of events. This will also remove duplicates in the case where the source changes the unique identifier for an event.', 'tribe-ext-ea-additional-options'),
                    'validation_type' => 'options',
                    'default' => 'no',
                    'options' => array(
                        'no' => __('Do not delete duplicate/removed events.', 'tribe-ext-ea-additional-options'),
                        'trash' => __('Send duplicate/removed events to trash.', 'tribe-ext-ea-additional-options'),
                        'permanent' => __('Permanently delete duplicate/removed events.', 'tribe-ext-ea-additional-options'),
                    ),
                ),
                $this->opts_prefix . 'link_directly_to_website_url' => array(
                    'type' => 'radio',
                    'label' => esc_html__('Link Directly to Website URL, Bypassing Default Event Page', 'tribe-ext-ea-additional-options'),
                    'tooltip' => esc_html__('Instead of linking to the Event page within The Events Calendar, enable this option so that visitors can be sent directly to the URL in the Website URL field.', 'tribe-ext-ea-additional-options'),
                    'validation_type' => 'options',
                    'default' => 'no',
                    'options' => array(
                        'no' => __('Link to the default single event page.', 'tribe-ext-ea-additional-options'),
                        'yes' => __('Link directly to the event website URL', 'tribe-ext-ea-additional-options'),
                    ),
                ),
                $this->opts_prefix . 'retain_line_breaks' => array(
                    'type' => 'radio',
                    'label' => esc_html__('Retain Line Breaks in Event Description', 'tribe-ext-ea-additional-options'),
                    'tooltip' => esc_html__('Some import sources allow for linebreaks. Choose whether to remove linebreaks or keep them.', 'tribe-ext-ea-additional-options'),
                    'validation_type' => 'options',
                    'default' => 'no',
                    'options' => array(
                        'no' => __('Remove all line breaks from event descriptions.', 'tribe-ext-ea-additional-options'),
                        'yes' => __('Retain all line breaks within event descirptions.', 'tribe-ext-ea-additional-options'),
                    ),
                ),
            );

            $setting_helper->add_fields(
                    $fields, 'imports', // not the 'event-tickets' ("Tickets" tab) because it doesn't exist without Event Tickets
                    'import-defaults-update_authority', true
            );
        }

        /**
         * Extension initialization and hooks.
         */
        public function init() {
            // Load plugin textdomain
            load_plugin_textdomain('tribe-ext-ea-additional-options', false, basename(dirname(__FILE__)) . '/languages/');

            add_action('admin_init', array($this, 'add_settings'));

            $deletionSetting = tribe_get_option($this->opts_prefix . 'delete_duplicate_removed_events');
            if (!empty($deletionSetting) && $deletionSetting !== 'no') {
                add_action('save_post_tribe-ea-record', array($this, 'record_finalized'), 10, 2);
            }

            add_filter('tribe_aggregator_url_import_range_options', array($this, 'add_other_url_options'));

            add_action('tribe_events_aggregator_import_form_preview_options', array($this, 'add_import_options'));
        }

        /**
         * Process duplicate/removed events after import is complete.
         *
         * @return mixed|string
         */
        public function record_finalized($postId, $post) {
            if ($post->post_status === Tribe__Events__Aggregator__Records::$status->success) {
                $deletionSetting = tribe_get_option($this->opts_prefix . 'delete_duplicate_removed_events');
                $deletePermanently = $deletionSetting == 'permanent';
                $source = get_post_meta($postId, '_tribe_aggregator_source', true);
                $idsToDelete = tribe_get_events([
                    'fields' => 'ids',
                    'posts_per_page' => -1,
                    'post_type' => 'tribe_events',
                    'post_status' => 'publish',
                    'ends_after' => date('Y-m-d H:i:s', time()),
                    'meta_query' => [
                        'relation' => 'AND',
                        [
                            'key' => '_tribe_aggregator_source',
                            'value' => $source,
                            'compare' => '='
                        ],
                        [
                            'key' => '_tribe_aggregator_record',
                            'value' => $postId,
                            'compare' => '<'
                        ]
                    ],
                ]);
                foreach ($idsToDelete as $eventId) {
                    tribe_delete_event($eventId, $deletePermanently);
                }
            }
        }

        /**
         * Adds extra options to the 'Other URL' settings
         * 
         * @param type $options
         * @return array
         */
        public function add_other_url_options($options) {
            $options[MONTH_IN_SECONDS * 6] = array(
                'title' => __('Six months', 'tribe-ext-ea-additional-options'),
                'range' => __('six months', 'tribe-ext-ea-additional-options'),
            );
            $options[YEAR_IN_SECONDS] = array(
                'title' => __('One year', 'tribe-ext-ea-additional-options'),
                'range' => __('one year', 'tribe-ext-ea-additional-options'),
            );
            $options[YEAR_IN_SECONDS * 2] = array(
                'title' => __('Two years', 'tribe-ext-ea-additional-options'),
                'range' => __('two years', 'tribe-ext-ea-additional-options'),
            );
            return $options;
        }

        public function add_import_options() {
            $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
            $timezoneSelect = '<select name="aggregator[timezone]" id="tribe-ea-field-timezone" class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large">';
            $timezoneSelect .= '<option value="">Do not change the timezone.</option>';
            foreach ($tzlist as $tz) {
                $timezoneSelect .= '<option value="' . esc_attr($tz) . '">' . esc_html($tz) . '</option>';
            }
            $timezoneSelect .= '</select>';
            ?>
            <div class="tribe-default-settings">
                <h4>Additional Options</h4>
                <div class="tribe-refine tribe-active">
                    <label for="tribe-ea-field-timezone"><?php esc_html_e('Force Timezone:', 'tribe-ext-ea-additional-options'); ?></label>
                    <?php echo $timezoneSelect; ?>
                    <span
                        class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help"
                        data-bumpdown="<?php echo esc_attr__('You can choose to change the timezones of all events in this import. The times will be modified to match the chosen timezone.', 'tribe-ext-ea-additional-options'); ?>"
                        data-width-rule="all-triggers"
                        ></span>
                </div>
                <div class="tribe-refine tribe-active">
                    <label for="tribe-ea-field-prefix"><?php esc_html_e('Event Title Prefix:', 'tribe-ext-ea-additional-options'); ?></label>
                    <input id="tribe-ea-field-prefix" class="tribe-ea-field tribe-ea-size-large" type="text" />
                    <span
                        class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help"
                        data-bumpdown="<?php echo esc_attr__('Add text before the title of each event.', 'tribe-ext-ea-additional-options'); ?>"
                        data-width-rule="all-triggers"
                        ></span>
                </div>
            </div>
            <?php
        }

    }

    // end class
} // end if class_exists check
