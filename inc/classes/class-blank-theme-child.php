<?php
/**
 * Bootstraps the Theme.
 *
 * @package Blank-Theme-Child
 */

namespace Blank_Theme_Child\Inc;

use Blank_Theme_Child\Inc\Traits\Singleton;

/**
 * Main theme bootstrap file.
 *
 * Boots the theme by loading the assets class and registering theme hooks.
 */
class Blank_Theme_Child {


	use Singleton;

	/**
	 * Construct method.
	 *
	 * Initializes the theme by loading necessary classes
	 * and setting up hooks.
	 */
	protected function __construct() {
		// Load the Assets class instance.
		Assets::get_instance();

		// Set up theme hooks.
		$this->setup_hooks();
	}

	/**
	 * Set up action and filter hooks.
	 *
	 * This method is triggered when the `after_setup_theme` action fires.
	 * It is responsible for setting up all action and filter hooks used in the theme.
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		/**
		 * Actions
		 *
		 * - `after_setup_theme`: Called after the theme has been activated.
		 *   It is used to set up theme features and hooks.
		 */
		add_action( 'after_setup_theme', [ $this, 'setup_theme' ] );
		add_action( 'after_setup_theme', [ $this, 'setup_migrate_theme_mods' ] );
	}

	/**
	 * Setup theme.
	 *
	 * This method is triggered when the `after_setup_theme` action fires.
	 * It is responsible for setting up the theme features and hooks.
	 *
	 * @return void
	 */
	public function setup_theme() {

		/**
		 * Make theme available for translation.
		 *
		 * Translations can be added to the `/languages` directory.
		 */
		load_theme_textdomain( 'blank-theme-child', BLANK_THEME_CHILD_TEMP_DIR . '/languages' );
	}

	/**
	 * Migrate the parent theme's Customizer settings to the child theme.
	 *
	 * Copies the parent theme's `theme_mods_{template}` option into the child
	 * theme's `theme_mods_{stylesheet}` option via the `after_setup_theme`
	 * hook, so the child inherits the parent's saved settings on activation.
	 *
	 * Runs once per child theme: guarded by the
	 * `blank_theme_child_migrated_{stylesheet}` option. No-ops for non-child
	 * themes, when the migration already ran, when the parent has no saved
	 * settings (still marks the migration done), or when the child already
	 * has Customizer settings of its own.
	 *
	 * @return void
	 */
	public function setup_migrate_theme_mods() {
		// Make sure this is a child theme.
		if ( ! is_child_theme() ) {
			return;
		}

		$child_theme  = get_stylesheet();
		$parent_theme = get_template();

		// Prevent running the migration more than once.
		$migration_key = 'migrated_' . $child_theme;

		if ( get_option( $migration_key ) ) {
			return;
		}

		// Get parent theme Customizer settings.
		$parent_mods = get_option( 'theme_mods_' . $parent_theme );

		if ( ! is_array( $parent_mods ) || empty( $parent_mods ) ) {
			update_option( $migration_key, true );
			return;
		}

		// Get existing child theme settings.
		$child_mods = get_option( 'theme_mods_' . $child_theme );

		/*
		* Only migrate if the child theme doesn't already have
		* Customizer settings.
		*/
		if ( false === $child_mods || empty( $child_mods ) ) {

			update_option(
				'theme_mods_' . $child_theme,
				$parent_mods
			);
		}

		// Mark migration as completed.
		update_option( $migration_key, true );
	}
}
