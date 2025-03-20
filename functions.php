<?php
/**
 * Blank Theme file includes and definitions
 *
  * @package Blank-Theme-Child
 */

if ( ! defined( 'BLANK_THEME_CHILD_VERSION' ) ) {
	define( 'BLANK_THEME_CHILD_VERSION', 1.0 );
}
if ( ! defined( 'BLANK_THEME_CHILD_TEMP_DIR' ) ) {
	define( 'BLANK_THEME_CHILD_TEMP_DIR', untrailingslashit( get_template_directory() ) );
}
if ( ! defined( 'BLANK_THEME_CHILD_BUILD_URI' ) ) {
	define( 'BLANK_THEME_CHILD_BUILD_URI', untrailingslashit( get_template_directory_uri() ) . '/assets/build' );
}
if ( ! defined( 'BLANK_THEME_CHILD_BUILD_DIR' ) ) {
	define( 'BLANK_THEME_CHILD_BUILD_DIR', untrailingslashit( get_template_directory() ) . '/assets/build' );
}

require_once BLANK_THEME_CHILD_TEMP_DIR . '/inc/helpers/autoloader.php';
require_once BLANK_THEME_CHILD_TEMP_DIR . '/inc/helpers/custom-functions.php';


/**
 * Retrieves the main instance of Blank_Theme_Child to prevent the need to use globals.
 *
 * @return object Blank_Theme_Child
 */
function blank_theme_child_get_theme_instance() {
	return Blank_Theme_Child\Inc\Blank_Theme_Child::get_instance();
}

blank_theme_child_get_theme_instance();