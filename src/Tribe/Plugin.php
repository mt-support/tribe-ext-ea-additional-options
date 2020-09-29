<?php

namespace Tribe\Extensions\EA_Additional_Options;

/**
 * Class Plugin
 *
 * @since   __TRIBE_VERSION__
 *
 * @package Tribe\Extensions\EA_Additional_Options
 */
class Plugin extends \tad_DI52_ServiceProvider {
	/**
	 * Stores the version for the plugin.
	 *
	 * @since __TRIBE_VERSION__
	 *
	 * @var string
	 */
	const VERSION = '1.2.0';

	/**
	 * Stores the base slug for the plugin.
	 *
	 * @since __TRIBE_VERSION__
	 *
	 * @var string
	 */
	const SLUG = 'ea_additional_options';

	/**
	 * Stores the base slug for the extension.
	 *
	 * @since __TRIBE_VERSION__
	 *
	 * @var string
	 */
	const FILE = EA_ADDITIONAL_OPTIONS_FILE;

	/**
	 * @since __TRIBE_VERSION__
	 *
	 * @var string Plugin Directory.
	 */
	public $plugin_dir;

	/**
	 * @since __TRIBE_VERSION__
	 *
	 * @var string Plugin path.
	 */
	public $plugin_path;

	/**
	 * @since __TRIBE_VERSION__
	 *
	 * @var string Plugin URL.
	 */
	public $plugin_url;

	/**
	 * Setup the Extension's properties.
	 *
	 * This always executes even if the required plugins are not present.
	 *
	 * @since __TRIBE_VERSION__
	 */
	public function register() {
		// Set up the plugin provider properties.
		$this->plugin_path = trailingslashit( dirname( static::FILE ) );
		$this->plugin_dir  = trailingslashit( basename( $this->plugin_path ) );
		$this->plugin_url  = plugins_url( $this->plugin_dir, $this->plugin_path );

		// Register this provider as the main one and use a bunch of aliases.
		$this->container->singleton( static::class, $this );
		$this->container->singleton( 'extension.__TRIBE_SLUG_CLEAN__', $this );
		$this->container->singleton( 'extension.__TRIBE_SLUG_CLEAN__.plugin', $this );

		if ( ! $this->check_plugin_dependencies() ) {
			// If the plugin dependency manifest is not met, then bail and stop here.
			return;
		}

		$this->container->register( Hooks::class );
	}

	/**
	 * Checks whether the plugin dependency manifest is satisfied or not.
	 *
	 * @since __TRIBE_VERSION__
	 *
	 * @return bool Whether the plugin dependency manifest is satisfied or not.
	 */
	protected function check_plugin_dependencies() {
		$this->register_plugin_dependencies();

		return tribe_check_plugin( static::class );
	}

	/**
	 * Registers the plugin and dependency manifest among those managed by Tribe Common.
	 *
	 * @since __TRIBE_VERSION__
	 */
	protected function register_plugin_dependencies() {
		$plugin_register = new Plugin_Register();
		$plugin_register->register_plugin();

		$this->container->singleton( Plugin_Register::class, $plugin_register );
		$this->container->singleton( 'extension.ea_additional_options', $plugin_register );
	}
}
