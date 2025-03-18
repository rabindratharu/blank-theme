<?php
/**
 * Theme Functions.
 *
 * @package Blank_Theme
 */


if ( ! defined( 'BLANK_THEME_VERSION' ) ) {
	define( 'BLANK_THEME_VERSION', '1.0.0' );
}

if ( ! defined( 'BLANK_THEME_DIR_PATH' ) ) {
	define( 'BLANK_THEME_DIR_PATH', untrailingslashit( get_template_directory() ) );
}

if ( ! defined( 'BLANK_THEME_DIR_URI' ) ) {
	define( 'BLANK_THEME_DIR_URI', untrailingslashit( get_template_directory_uri() ) );
}

if ( ! defined( 'BLANK_THEME_BUILD_URI' ) ) {
	define( 'BLANK_THEME_BUILD_URI', untrailingslashit( get_template_directory_uri() ) . '/build' );
}

if ( ! defined( 'BLANK_THEME_BUILD_PATH' ) ) {
	define( 'BLANK_THEME_BUILD_PATH', untrailingslashit( get_template_directory() ) . '/build' );
}

require_once BLANK_THEME_DIR_PATH . '/inc/helpers/custom-functions.php';
require_once BLANK_THEME_DIR_PATH . '/inc/helpers/autoloader.php';

/**
 * Returns an instance of the Blank_Theme class.
 *
 * This function returns an instance of the Blank_Theme class. The Blank_Theme class is
 * responsible for setting up the theme, adding support for various features,
 * and registering the necessary hooks.
 *
 * @since 1.0.0
 *
 * @return Blank_Theme The instance of the Blank_Theme class.
 */
function blank_theme_get_instance() {
	/**
	 * Get the instance of the Blank_Theme class.
	 *
	 * The Blank_Theme class is a singleton class, so it can only have one instance.
	 * This function returns the instance of the Blank_Theme class.
	 *
	 * @since 1.0.0
	 *
	 * @return Blank_Theme The instance of the Blank_Theme class.
	 */
	return \Blank_Theme\Inc\Blank_Theme::get_instance();
}

blank_theme_get_instance();