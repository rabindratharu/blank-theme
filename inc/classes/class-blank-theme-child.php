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
}