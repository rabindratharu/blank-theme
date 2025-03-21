<?php
/**
 * Enqueue theme assets.
 *
  * @package Blank-Theme-Child
 */

namespace Blank_Theme_Child\Inc;

use Blank_Theme_Child\Inc\Traits\Singleton;

/**
 * Class Assets
 */
class Assets {

	use Singleton;

	/**
	 * Construct method.
	 *
	 * Initializes the class and sets up necessary hooks.
	 */
	protected function __construct() {
		// Set up hooks for the class
		$this->setup_hooks();
	}

	
	/**
	 * Set up hooks for the class.
	 *
	 * This method sets up necessary hooks for the class, such as registering
	 * scripts and styles to be enqueued.
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		// Register and enqueue scripts.
		add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ] );

		// Register and enqueue styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'register_styles' ] );
	}

	/**
	 * Register and enqueue theme scripts.
	 *
	 * This method registers the main JavaScript file for the theme and enqueues
	 * it to be included in the front-end. It includes necessary dependencies
	 * such as jQuery.
	 *
	 * @return void
	 */
	public function register_scripts() {

		// Register the main JavaScript file for the theme with jQuery dependency.
		$this->register_script( 'blank-theme-child-main', 'js/main.js', [ 'jquery' ] );

		// Enqueue the registered JavaScript file to be included in the front-end.
		wp_enqueue_script( 'blank-theme-child-main' );
	}

	/**
	 * Register and enqueue styles for the theme.
	 *
	 * This method registers the main CSS file for the theme and enqueues it
	 * to be included in the front-end of the site. It utilizes WordPress's
	 * style enqueueing functions to add styles to the front-end.
	 *
	 * @action wp_enqueue_scripts
	 * @return void
	 */
	public function register_styles() {
		// Register the main CSS file for the theme.
		$this->register_style( 'blank-theme-child-main', 'css/main.css', ['parent-template-theme-css'] );

		// Enqueue the registered CSS file to be included in the front-end.
		wp_enqueue_style( 'blank-theme-child-main' );
	}

	/**
	 * Retrieves asset dependencies and version info from {handle}.asset.php if exists.
	 *
	 * This method reads the asset meta data from the asset.php file if it exists.
	 * The asset.php file is generated by the build process and contains information
	 * about the dependencies and version of the asset.
	 *
	 * @param string $file File name.
	 * @param array  $deps Script dependencies to merge with.
	 * @param string $ver  Asset version string.
	 *
	 * @return array {
	 *     Array of asset dependencies and version.
	 *
	 *     @type array  $dependencies Array of registered script handles this script depends on.
	 *     @type string $version      String specifying script version number.
	 * }
	 */
	public function get_asset_meta( $file, $deps = array(), $ver = false ) {
		// Get the asset meta file path.
		$asset_meta_file = sprintf( '%s/js/%s.asset.php', untrailingslashit( BLANK_THEME_CHILD_BUILD_DIR ), basename( $file, '.' . pathinfo( $file )['extension'] ) );

		// If the file is readable, read the asset meta data from the file.
		if ( is_readable( $asset_meta_file ) ) {
			$asset_meta = require $asset_meta_file;
		} else {
			// If the file is not readable, set the asset meta data to an empty array.
			$asset_meta = array();
		}

		// Add the dependencies to the asset meta data if they are set.
		if ( ! empty( $deps ) ) {
			$asset_meta['dependencies'] = array_merge( $asset_meta['dependencies'], $deps );
		}

		// Add the version to the asset meta data if it is set.
		if ( $ver ) {
			$asset_meta['version'] = $ver;
		}

		// Return the asset meta data.
		return $asset_meta;
	}

	/**
	 * Register a new script.
	 *
	 * This method registers a new script with the WordPress script enqueueing API.
	 *
	 * @param string           $handle    Name of the script. Should be unique.
	 * @param string|bool      $file       script file, path of the script relative to the assets/build/ directory.
	 * @param array            $deps      Optional. An array of registered script handles this script depends on. Default empty array.
	 * @param string|bool|null $ver       Optional. String specifying script version number, if not set, filetime will be used as version number.
	 * @param bool             $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>.
	 *                                    Default 'false'.
	 * @return bool Whether the script has been registered. True on success, false on failure.
	 */
	public function register_script( $handle, $file, $deps = array(), $ver = false, $in_footer = true ) {
		/**
		 * Get the URL of the script file.
		 *
		 * @var string $src The URL of the script file.
		 */
		$src = sprintf( BLANK_THEME_CHILD_BUILD_URI . '/%s', $file );

		/**
		 * Get the asset meta data.
		 *
		 * @var array $asset_meta {
		 *     Array of asset dependencies and version.
		 *
		 *     @type array  $dependencies Array of registered script handles this script depends on.
		 *     @type string $version      String specifying script version number.
		 * }
		 */
		$asset_meta = $this->get_asset_meta( $file, $deps );

		/**
		 * Register the script with the WordPress script enqueueing API.
		 *
		 * @var bool $return Whether the script has been registered. True on success, false on failure.
		 */
		return wp_register_script( $handle, $src, $asset_meta['dependencies'], $asset_meta['version'], $in_footer );
	}

	/**
	 * Register a CSS stylesheet.
	 *
	 * Registers a CSS stylesheet with WordPress.
	 *
	 * @param string           $handle Name of the stylesheet. Should be unique.
	 * @param string|bool      $file    style file, path of the script relative to the assets/build/ directory.
	 * @param array            $deps   Optional. An array of registered stylesheet handles this stylesheet depends on.
	 *                                 Default empty array.
	 * @param string|bool|null $ver    Optional. String specifying script version number.
	 *                                 If not set, filetime will be used as version number.
	 * @param string           $media  Optional. The media for which this stylesheet has been defined.
	 *                                 Default 'all'. Accepts media types like 'all', 'print' and 'screen', or media queries like
	 *                                 '(orientation: portrait)' and '(max-width: 640px)'.
	 *
	 * @return bool Whether the style has been registered. True on success, false on failure.
	 */
	public function register_style( $handle, $file, $deps = array(), $ver = false, $media = 'all' ) {

		/**
		 * Get the URL of the style file.
		 *
		 * @var string $src The URL of the style file.
		 */
		$src = sprintf( BLANK_THEME_CHILD_BUILD_URI . '/%s', $file );

		/**
		 * Get the asset meta data.
		 *
		 * @var array $asset_meta {
		 *     Array of asset dependencies and version.
		 *
		 *     @type array  $dependencies Array of registered script handles this script depends on.
		 *     @type string $version      String specifying script version number.
		 * }
		 */
		$asset_meta = $this->get_asset_meta( $file, $deps );

		/**
		 * Register the style with the WordPress script enqueueing API.
		 *
		 * @var bool $return Whether the style has been registered. True on success, false on failure.
		 */
		return wp_register_style( $handle, $src, $asset_meta['dependencies'], $asset_meta['version'], $media );
	}


	/**
	 * Get file version.
	 *
	 * Retrieves a file version. If the $ver parameter is provided, it returns that version.
	 * If the $ver parameter is not provided, it attempts to retrieve the modification time of the file.
	 * If the file does not exist, it returns false.
	 *
	 * @param string             $file File path.
	 * @param int|string|boolean $ver  File version.
	 *
	 * @return bool|false|int
	 */
	public function get_file_version( $file, $ver = false ) {
		// If a version is provided, return it.
		if ( ! empty( $ver ) ) {
			return $ver;
		}

		// Get the file path.
		$file_path = sprintf( '%s/%s', BLANK_THEME_CHILD_BUILD_DIR, $file );

		// Check if the file exists.
		if ( file_exists( $file_path ) ) {
			// If the file exists, get the modification time.
			$file_time = filemtime( $file_path );

			// Return the modification time.
			return $file_time;
		} else {
			// If the file does not exist, return false.
			return false;
		}
	}

}