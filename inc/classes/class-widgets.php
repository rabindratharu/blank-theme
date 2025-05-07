<?php

/**
 * Theme widgets.
 *
 * @package Classic-Theme
 */

namespace Classic_Theme\Inc;

use Classic_Theme\Inc\Traits\Singleton;

/**
 * Class Widgets
 */
class Widgets
{

	use Singleton;

	/**
	 * Construct method.
	 *
	 * Initializes the class and sets up necessary hooks.
	 */
	protected function __construct()
	{
		// Set up hooks for the class
		$this->setup_hooks();
	}

	/**
	 * Set up hooks for the Widgets class.
	 *
	 * This method sets up necessary WordPress action hooks for the Widgets class,
	 * such as registering widget areas and initializing widgets.
	 *
	 * @return void
	 */
	protected function setup_hooks()
	{
		/**
		 * Actions
		 */
		add_action('widgets_init', [$this, 'register_widgets']);
	}

	/**
	 * Register widgets.
	 *
	 * @action widgets_init
	 */
	public function register_widgets()
	{

		register_sidebar(
			array(
				'name'          => esc_html__('Sidebar', 'classic-theme'),
				'id'            => 'sidebar-1',
				'description'   => esc_html__('Add widgets here.', 'classic-theme'),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);
	}
}
