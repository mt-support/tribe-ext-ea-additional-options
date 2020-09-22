<?php
/**
 * Plugin Name:       Events Aggregator Extension: Additional Options
 * Plugin URI:        https://theeventscalendar.com/extensions/ea-additional-options/
 * GitHub Plugin URI: https://github.com/mt-support/tribe-ext-ea-additional-options
 * Description:       Adds extra options to Event Aggregator settings and imports
 * Version:           1.1.1
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

define( 'EA_ADDITIONAL_OPTIONS_FILE', __FILE__ );


function tribe_extension_ea_additional_options() {
	// When we dont have autoloader from common we bail.
	if ( ! class_exists( 'Tribe__Autoloader' ) ) {
		return;
	}

	// Register the namespace so we can the plugin on the service provider registration.
	Tribe__Autoloader::instance()->register_prefix(
		'\\Tribe\\Extensions\\EA_Additional_Options\\',
		__DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Tribe',
		'ea_additional_options'
	);

	// Deactivates the plugin in case of the main class didn't autoload.
	if ( ! class_exists( '\Tribe\Extensions\EA_Additional_Options\Plugin' ) ) {
		tribe_transient_notice(
			'__TRIBE_SLUG__',
			'<p>' . esc_html__( 'Couldn\'t properly load "__TRIBE_BASE__ Extension: EA_Additional_Options" the extension was deactivated.', '__TRIBE_SLUG__' ) . '</p>',
			[],
			MINUTE_IN_SECONDS
		);

		deactivate_plugins( __FILE__, true );

		return;
	}

	tribe_register_provider( '\Tribe\Extensions\EA_Additional_Options\Plugin' );
}

// Loads after common is already properly loaded.
add_action( 'tribe_common_loaded', 'tribe_extension_ea_additional_options' );
