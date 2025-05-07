<?php

/**
 * Classic Theme file includes and definitions
 *
 * @package Classic-Theme
 */

if (! defined('CLASSIC_THEME_VERSION')) {
	define('CLASSIC_THEME_VERSION', 1.0);
}
if (! defined('CLASSIC_THEME_TEMP_DIR')) {
	define('CLASSIC_THEME_TEMP_DIR', untrailingslashit(get_template_directory()));
}
if (! defined('CLASSIC_THEME_BUILD_URI')) {
	define('CLASSIC_THEME_BUILD_URI', untrailingslashit(get_template_directory_uri()) . '/assets/build');
}
if (! defined('CLASSIC_THEME_BUILD_DIR')) {
	define('CLASSIC_THEME_BUILD_DIR', untrailingslashit(get_template_directory()) . '/assets/build');
}

require_once CLASSIC_THEME_TEMP_DIR . '/inc/helpers/autoloader.php';
require_once CLASSIC_THEME_TEMP_DIR . '/inc/helpers/custom-functions.php';
require_once CLASSIC_THEME_TEMP_DIR . '/inc/helpers/template-tags.php';


/**
 * Retrieves the main instance of Classic_Theme to prevent the need to use globals.
 *
 * @return object Classic_Theme
 */
function classic_theme_get_theme_instance()
{
	return Classic_Theme\Inc\Classic_Theme::get_instance();
}

classic_theme_get_theme_instance();
