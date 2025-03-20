<?php
/**
 * Bootstraps the Theme.
 *
  * @package Blank-Theme
 */

namespace Blank_Theme\Inc;

use Blank_Theme\Inc\Traits\Singleton;

/**
 * Main theme bootstrap file.
 */
class Blank_Theme {

	use Singleton;

	/**
	 * Construct method.
	 */
	protected function __construct() {

		// Load classes.
		Assets::get_instance();

		$this->setup_hooks();

	}

	/**
	 * To setup action/filter.
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		/**
		 * Actions
		 */
		add_action( 'after_setup_theme', [ $this, 'setup_theme' ] );
		add_action( 'init', [ $this, 'block_styles' ] );
		add_action( 'init', [ $this, 'pattern_categories' ] );
		add_action( 'init', [ $this, 'block_bindings' ] );
	}

	/**
	 * Setup theme.
	 *
	 * @return void
	 */
	public function setup_theme() {

		load_theme_textdomain( 'blank-theme', BLANK_THEME_TEMP_DIR . '/languages' );

		add_theme_support( 'post-formats', array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ) );

		add_editor_style( get_parent_theme_file_uri( 'assets/buildd/css/editor.css' ) );
	}

	/**
	 * Add read more link
	 *
	 * @filter excerpt_more
	 *
	 * @return string
	 */
	public function block_styles() {
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'blank-theme' ),
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
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
	public function pattern_categories() {
		register_block_pattern_category(
			'twentytwentyfive_page',
			array(
				'label'       => __( 'Pages', 'blank-theme' ),
				'description' => __( 'A collection of full page layouts.', 'blank-theme' ),
			)
		);

		register_block_pattern_category(
			'twentytwentyfive_post-format',
			array(
				'label'       => __( 'Post formats', 'blank-theme' ),
				'description' => __( 'A collection of post format patterns.', 'blank-theme' ),
			)
		);
	}

	/**
	 * Add a ping back url auto-discovery header for single posts, pages, or attachments.
	 *
	 * @action wp_head
	 *
	 * @return void
	 */
	public function block_bindings() {
		register_block_bindings_source(
			'blank-theme/format',
			array(
				'label'              => _x( 'Post format name', 'Label for the block binding placeholder in the editor', 'blank-theme' ),
				'get_value_callback' => 'blank_theme_format_binding',
			)
		);
	}

}