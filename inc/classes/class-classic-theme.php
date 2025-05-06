<?php

/**
 * Bootstraps the Theme.
 *
 * @package classic-theme
 */

namespace Classic_Theme\Inc;

use Classic_Theme\Inc\Traits\Singleton;

/**
 * Main theme bootstrap file.
 */
class Classic_Theme
{

	use Singleton;

	/**
	 * Construct method.
	 *
	 * Initializes the theme by loading necessary classes
	 * and setting up hooks.
	 */
	protected function __construct()
	{
		// Load the Assets class instance.
		Assets::get_instance();
		Customizer::get_instance();
		Widgets::get_instance();

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
	protected function setup_hooks()
	{

		/**
		 * Actions
		 */
		add_action('wp_head', [$this, 'add_pingback_link']);
		add_action('after_setup_theme', [$this, 'setup_theme']);

		/**
		 * Filters
		 */
		add_filter('excerpt_more', [$this, 'add_read_more_link']);
		add_filter('body_class', [$this, 'filter_body_classes']);
	}

	/**
	 * Setup theme.
	 *
	 * This method is triggered when the `after_setup_theme` action fires.
	 * It is responsible for setting up the theme features and hooks.
	 *
	 * @return void
	 */
	public function setup_theme()
	{

		/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on classic-theme, use a find and replace
		* to change 'classic-theme' to the name of your theme in all the template files.
		*/
		load_theme_textdomain('classic-theme', CLASSIC_THEME_TEMP_DIR . '/languages');

		// Add default posts and comments RSS feed links to head.
		add_theme_support('automatic-feed-links');

		/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
		add_theme_support('title-tag');

		add_theme_support('jetpack-responsive-videos');

		/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
		add_theme_support('post-thumbnails');

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			[
				'menu-1' => esc_html__('Primary', 'classic-theme'),
			]
		);

		/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
		add_theme_support(
			'html5',
			[
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			]
		);

		// Set up the WordPress core custom background feature.
		add_theme_support(
			'custom-background',
			apply_filters(
				'classic_theme_custom_background_args',
				[
					'default-color' => 'ffffff',
					'default-image' => '',
				]
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support('customize-selective-refresh-widgets');

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			[
				'header-text' => [
					'site-title',
					'site-description',
				],
			]
		);

		if (! isset($content_width)) {
			$content_width = 640;
		}

		//$GLOBALS['content_width'] = apply_filters('classic_theme_content_width', 640);
	}

	/**
	 * Add a ping back url auto-discovery header for single posts, pages, or attachments.
	 *
	 * @action wp_head
	 *
	 * @return void
	 */
	public function add_pingback_link()
	{
		if (is_singular() && pings_open()) {
			printf('<link rel="pingback" href="%s">', esc_url(get_bloginfo('pingback_url')));
		}
	}

	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @param array $classes Classes for the body element.
	 *
	 * @filter body_class
	 *
	 * @return array
	 */
	public function filter_body_classes($classes)
	{

		if (! is_singular()) {
			$classes[] = 'hfeed';
		}

		// Adds a class of no-sidebar when there is no sidebar present.
		if (! is_active_sidebar('sidebar-1')) {
			$classes[] = 'no-sidebar';
		}

		return $classes;
	}

	/**
	 * Add read more link
	 *
	 * @filter excerpt_more
	 *
	 * @return string
	 */
	public function add_read_more_link()
	{
		global $post;

		return sprintf('<a class="moretag" href="%s">%s</a>', get_permalink($post->ID), esc_html__('Read More', 'classic-theme'));
	}
}
