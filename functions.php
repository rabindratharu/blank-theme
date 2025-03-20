<?php
/**
 * Blank Theme file includes and definitions
 *
 * @package Blank-Theme
 */

if ( ! defined( 'BLANK_THEME_VERSION' ) ) {
	define( 'BLANK_THEME_VERSION', 1.0 );
}
if ( ! defined( 'BLANK_THEME_TEMP_DIR' ) ) {
	define( 'BLANK_THEME_TEMP_DIR', untrailingslashit( get_template_directory() ) );
}
if ( ! defined( 'BLANK_THEME_BUILD_URI' ) ) {
	define( 'BLANK_THEME_BUILD_URI', untrailingslashit( get_template_directory_uri() ) . '/assets/build' );
}
if ( ! defined( 'BLANK_THEME_BUILD_DIR' ) ) {
	define( 'BLANK_THEME_BUILD_DIR', untrailingslashit( get_template_directory() ) . '/assets/build' );
}

require_once BLANK_THEME_TEMP_DIR . '/inc/helpers/autoloader.php';
require_once BLANK_THEME_TEMP_DIR . '/inc/helpers/custom-functions.php';
require_once BLANK_THEME_TEMP_DIR . '/inc/helpers/template-tags.php';

/**
 * Get blank theme instance.
 *
 * @return object \Blank_Theme\Inc\Blank_Theme
 */
function blank_theme_get_theme_instance() {
	return Blank_Theme\Inc\Blank_Theme::get_instance();
}

blank_theme_get_theme_instance();
