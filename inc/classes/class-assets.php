<?php
/**
 * Enqueue theme assets
 *
 * @package Blank_Theme
 */

namespace Blank_Theme\Inc;

use Blank_Theme\Inc\Traits\Singleton;

class Assets {

	use Singleton;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function __construct() {
		/**
		 * Set up hooks.
		 *
		 * This method sets up all the hooks related to the assets.
		 */
		$this->setup_hooks();
	}

	/**
	 * Set up hooks.
	 *
	 * This method sets up all the hooks related to the assets,
	 * such as styles and scripts.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function setup_hooks() {
		// Hook to register styles
		add_action( 'wp_enqueue_scripts', [ $this, 'register_styles' ] );

		// Hook to register scripts
		add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ] );

		add_action( 'init', [ $this, 'enqueue_block_styles' ] );
	}

	/**
	 * Register styles.
	 *
	 * This method registers the theme's styles.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_styles() {
		// Register the theme's public stylesheet.
		wp_register_style(
			'select2',
			BLANK_THEME_BUILD_URI . '/library/select2.min.css',
			// Dependencies.
			[],
			// Version.
			'4.1.0',
			// Media.
			'all'
		);

		wp_register_style(
			'public-css',
			BLANK_THEME_BUILD_URI . '/css/public.css',
			// Dependencies.
			[],
			// Version.
			filemtime( BLANK_THEME_BUILD_PATH . '/css/public.css' ),
			// Media.
			'all'
		);

		// Register the theme's filter stylesheet.
		wp_register_style(
			'filter-css',
			BLANK_THEME_BUILD_URI . '/css/filter.css',
			// Dependencies.
			[],
			// Version.
			filemtime( BLANK_THEME_BUILD_PATH . '/css/filter.css' ),
			// Media.
			'all'
		);

		wp_register_style(
			'popup-css',
			BLANK_THEME_BUILD_URI . '/css/popup.css',
			// Dependencies.
			[],
			// Version.
			filemtime( BLANK_THEME_BUILD_PATH . '/css/popup.css' ),
			// Media.
			'all'
		);

		// Enqueue the stylesheet.
		wp_enqueue_style( 'public-css' );

		// If search page.
		if ( is_search() ) {
			wp_enqueue_style( 'filter-css' );
		}

		/*
		* Load additional block styles.
		*/
		$styled_blocks = ['button','list'];
		foreach ( $styled_blocks as $block_name ) {
			$args = array(
				'handle' => "blank-theme-$block_name",
				'src'    => get_theme_file_uri( "build/css/core/$block_name.css" ),
			);
			wp_enqueue_block_style( "core/$block_name", $args );
		}
	}

	/**
	 * Register scripts.
	 *
	 * This method registers the theme's scripts.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_scripts() {
		// Register the theme's public script.
		wp_register_script(
			// Handle.
			'select2',
			// Source.
			BLANK_THEME_BUILD_URI . '/library/select2.min.js',
			// Dependencies.
			['jquery'],
			// Version.
			'4.1.0',
			// Enqueue in footer.
			true
		);

		wp_register_script(
			// Handle.
			'public-js',
			// Source.
			BLANK_THEME_BUILD_URI . '/js/public.js',
			// Dependencies.
			[],
			// Version.
			filemtime( BLANK_THEME_BUILD_PATH . '/js/public.js' ),
			// Enqueue in footer.
			true
		);

		wp_register_script(
			// Handle.
			'search-js',
			// Source.
			BLANK_THEME_BUILD_URI . '/js/search.js',
			// Dependencies.
			['public-js'],
			// Version.
			filemtime( BLANK_THEME_BUILD_PATH . '/js/search.js' ),
			// Enqueue in footer.
			true
		);

		wp_register_script(
			// Handle.
			'popup-js',
			// Source.
			BLANK_THEME_BUILD_URI . '/js/popup.js',
			// Dependencies.
			['jquery'],
			// Version.
			filemtime( BLANK_THEME_BUILD_PATH . '/js/popup.js' ),
			// Enqueue in footer.
			true
		);

		// Enqueue the script.
		wp_enqueue_script( 'public-js' );

		// If search page.
		if ( is_search() ) {
			wp_enqueue_script( 'search-js' );
			wp_localize_script( 'search-js', 'search_settings',
				[
					'search_api' 	=> home_url( '/wp-json/blank-theme/v2/search' ),
					'rest_api' 		=> home_url( 'wp-json/wp/v2/' ),
					'root_url'     	=> home_url('/?s='.sanitize_text_field(get_search_query())),
					'search_text'  	=> sanitize_text_field(get_search_query()),
				]
			);
		}
	}

	/**
	 * Register styles.
	 *
	 * This method registers the theme's styles.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_block_styles() {

		/*
		* Load additional block styles.
		*/
		$styled_blocks = ['button','list'];
		foreach ( $styled_blocks as $block_name ) {
			$args = array(
				'handle' => "blank-theme-$block_name",
				'src'    => get_theme_file_uri( "build/css/core/$block_name.css" ),
			);
			wp_enqueue_block_style( "core/$block_name", $args );
		}
	}

	/**
	 * Enqueues an individual block stylesheet based on a given block
	 * namespace and slug.
	 *
	 * @since 1.0.0
	 */
	private function enqueueStyle(string $namespace, string $slug): void
	{
		// Build a relative path and URL string.
		$relative = "public/css/{$namespace}/{$slug}";

		// Bail if the asset file doesn't exist.
		if (! file_exists(get_parent_theme_file_path("{$relative}.asset.php"))) {
			return;
		}

		// Get the asset file.
		$asset = include get_parent_theme_file_path("{$relative}.asset.php");

		// Register the block style.
		wp_enqueue_block_style("{$namespace}/{$slug}", [
			'handle' => "blank-theme-{$namespace}-{$slug}",
			'src'    => get_parent_theme_file_uri("{$relative}.css"),
			'path'   => get_parent_theme_file_path("{$relative}.css"),
			'deps'   => $asset['dependencies'],
			'ver'    => $asset['version']
		]);
	}
}