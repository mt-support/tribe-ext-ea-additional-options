<?php
/**
 * Handles hooking all the actions and filters used by the module.
 *
 * To remove a filter:
 * ```php
 *  remove_filter( 'some_filter', [ tribe( Tribe\Extensions\EA_Additional_Options\Hooks::class ), 'some_filtering_method' ] );
 *  remove_filter( 'some_filter', [ tribe( 'events-virtual.hooks' ), 'some_filtering_method' ] );
 * ```
 *
 * To remove an action:
 * ```php
 *  remove_action( 'some_action', [ tribe( Tribe\Extensions\EA_Additional_Options\Hooks::class ), 'some_method' ] );
 *  remove_action( 'some_action', [ tribe( 'events-virtual.hooks' ), 'some_method' ] );
 * ```
 *
 * @since   __TRIBE_VERSION__
 *
 * @package Tribe\Extensions\EA_Additional_Options;
 */

namespace Tribe\Extensions\EA_Additional_Options;

use Tribe\Extensions\EA_Additional_Options\Modules\Delete_Duplicated_Events;
use Tribe\Extensions\EA_Additional_Options\Modules\Line_Breaks;
use Tribe\Extensions\EA_Additional_Options\Modules\Options;
use Tribe\Extensions\EA_Additional_Options\Modules\Other_Url;
use Tribe\Extensions\EA_Additional_Options\Modules\Settings;
use Tribe\Extensions\EA_Additional_Options\Modules\Website_link;

/**
 * Class Hooks.
 *
 * @since   __TRIBE_VERSION__
 *
 * @package Tribe\Extensions\EA_Additional_Options;
 */
class Hooks extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since __TRIBE_VERSION__
	 */
	public function register() {
		$this->container->singleton( static::class, $this );
		$this->container->singleton( 'extension.ea-additional-options.hooks', $this );
		$this->container->singleton( Delete_Upcoming_Events::class, Delete_Upcoming_Events::class, [ 'hook' ] );
		$this->container->singleton( Settings::class, Settings::class, [ 'hook' ] );
		$this->container->singleton( Delete_Duplicated_Events::class, Delete_Duplicated_Events::class, [ 'hook' ] );
		$this->container->singleton( Other_Url::class, Other_Url::class, [ 'hook' ] );
		$this->container->singleton( Website_link::class, Website_link::class, [ 'hook' ] );
		$this->container->singleton( Options::class, Options::class, [ 'hook' ] );
		$this->container->singleton( Line_Breaks::class, Line_Breaks::class, [ 'hook' ] );

		$this->add_actions();
		$this->add_filters();
		// Additional hooks
		tribe( Settings::class );
		tribe( Delete_Duplicated_Events::class );
		tribe( Other_Url::class );
		tribe( Website_link::class );
		tribe( Options::class );
		tribe( Line_Breaks::class );
	}

	/**
	 * Adds the actions required by the plugin.
	 *
	 * @since __TRIBE_VERSION__
	 */
	protected function add_actions() {
		add_action( 'tribe_load_text_domains', [ $this, 'load_text_domains' ] );
	}

	/**
	 * Adds the filters required by the plugin.
	 *
	 * @since __TRIBE_VERSION__
	 */
	protected function add_filters() {
		tribe( Delete_Upcoming_Events::class );
	}

	/**
	 * Load text domain for localization of the plugin.
	 *
	 * @since __TRIBE_VERSION__
	 */
	public function load_text_domains() {
		$mo_path = tribe( Plugin::class )->plugin_dir . 'languages/';

		// This will load `wp-content/languages/plugins` files first.
		\Tribe__Main::instance()->load_text_domain( 'tribe-ext-extension-template', $mo_path );
	}
}
