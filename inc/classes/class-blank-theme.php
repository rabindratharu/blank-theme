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
		 * - `init`: Called after WordPress has finished loading but before any
		 *   headers are sent.
		 */
		add_action( 'after_setup_theme', [ $this, 'setup_theme' ] );
		add_action( 'init', [ $this, 'block_styles' ] );
		add_action( 'init', [ $this, 'pattern_categories' ] );
		add_action( 'init', [ $this, 'block_bindings' ] );
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
		load_theme_textdomain( 'blank-theme', BLANK_THEME_TEMP_DIR . '/languages' );

		/**
		 * Enable post-formats feature.
		 *
		 * This allows users to select a post format when creating a post.
		 * The post formats that are available are:
		 * - aside
		 * - audio
		 * - chat
		 * - gallery
		 * - image
		 * - link
		 * - quote
		 * - status
		 * - video
		 */
		add_theme_support( 'post-formats', array(
			'aside',
			'audio',
			'chat',
			'gallery',
			'image',
			'link',
			'quote',
			'status',
			'video'
		) );
	}

	/**
	 * Registers a block style for the core/list block.
	 *
	 * This method registers a block style for the core/list block.
	 * The block style is a list with a checkmark instead of a bullet.
	 * The block style can be selected when inserting a list block.
	 *
	 * @param string $link The link to the stylesheet.
	 * @return string The modified link.
	 */
	public function block_styles( $link ) {
		/**
		 * Register a block style for the core/list block.
		 *
		 * The block style is a list with a checkmark instead of a bullet.
		 * The block style can be selected when inserting a list block.
		 */
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list', // Unique name for the block style.
				'label'        => __( 'Checkmark', 'blank-theme' ), // Display label for the block style.
				'inline_style' => '
				/*
				 * Set the list style type to a checkmark.
				 * The checkmark is a Unicode character.
				 */
				ul.is-style-checkmark-list {
					list-style-type: "\2713"; // Use checkmark as list bullet.
				}

				/*
				 * Add padding to list items.
				 * This is done to align the list items with the text.
				 */
				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch; // Add padding to list items.
				}',
			)
		);

		return $link; // Return the modified link.
	}

	
	/**
	 * Registers block pattern categories for pages and post formats.
	 *
	 * This method registers two block pattern categories:
	 * - A category for pages.
	 * - A category for post formats.
	 *
	 * The categories are used in the editor for categorizing block patterns.
	 *
	 * @return void
	 */
	public function pattern_categories() {
		// Register a block pattern category for pages.
		// The category is used in the editor for categorizing block patterns.
		register_block_pattern_category(
			'blank_theme_page',
			array(
				/**
				 * The label for the block pattern category.
				 *
				 * This label is used in the editor for displaying the category name.
				 *
				 * @var string
				 */
				'label'       => __( 'Pages', 'blank-theme' ),

				/**
				 * The description for the block pattern category.
				 *
				 * This description is used in the editor for displaying the category description.
				 *
				 * @var string
				 */
				'description' => __( 'A collection of full page layouts.', 'blank-theme' ),
			)
		);

		// Register a block pattern category for post formats.
		// The category is used in the editor for categorizing block patterns.
		register_block_pattern_category(
			'blank_theme_post-format',
			array(
				/**
				 * The label for the block pattern category.
				 *
				 * This label is used in the editor for displaying the category name.
				 *
				 * @var string
				 */
				'label'       => __( 'Post formats', 'blank-theme' ),

				/**
				 * The description for the block pattern category.
				 *
				 * This description is used in the editor for displaying the category description.
				 *
				 * @var string
				 */
				'description' => __( 'A collection of post format patterns.', 'blank-theme' ),
			)
		);
	}

	
	/**
	 * Registers a block bindings source for the post format name.
	 *
	 * This method registers a block bindings source for the post format name.
	 * The block bindings source is used in the editor for displaying the post format name.
	 *
	 * @return void
	 */
	public function block_bindings() {
		/**
		 * Register a block bindings source for the post format name.
		 *
		 * The block bindings source is used in the editor for displaying the post format name.
		 *
		 * @param string $name The name of the block bindings source.
		 * @param array  $args The arguments for the block bindings source.
		 */
		register_block_bindings_source(
			'blank-theme/format',
			array(
				/**
				 * The label for the block bindings source.
				 *
				 * This label is used in the editor for displaying the block bindings source name.
				 *
				 * @var string
				 */
				'label'              => _x( 'Post format name', 'Label for the block binding placeholder in the editor', 'blank-theme' ),

				/**
				 * The callback function to get the post format name.
				 *
				 * This function is used in the editor for displaying the post format name.
				 * The function takes an array of arguments, which are the attributes of the block.
				 * The function must return the post format name as a string.
				 *
				 * @var callable
				 */
				'get_value_callback' => 'blank_theme_format_binding', // Callback function to get the post format name.
			)
		);
	}

}