<?php
/**
 * Bootstraps the Theme.
 *
 * @package Blank_Theme
 */

namespace Blank_Theme\Inc;

use Blank_Theme\Inc\Traits\Singleton;

class Blank_Theme {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 * The class constructor is responsible for instantiating any necessary
	 * classes and setting up the WordPress hooks.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		// Instantiate necessary classes.
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$assets = Assets::get_instance();
		$utils = Utils::get_instance();
		
		// Set up WordPress hooks.
		$this->setup_hooks();
	}

	/**
	 * Set up hooks.
	 *
	 * This method sets up all the hooks related to the theme,
	 * such as adding support for core block styles, adding custom
	 * styles for paginated pages, and enabling SVG uploads.
	 *
	 * @since 1.0.0
	 */
	protected function setup_hooks() {
		// Theme setup.
		// The `after_setup_theme` action hook is used to add support for
		// core block styles and remove core block patterns.
		add_action( 'after_setup_theme', [ $this, 'setup_theme' ] );

		// Add custom styles for paginated pages.
		// The `wp_head` action hook is used to add custom styles for paginated
		// pages. The styles are added to the `<head>` of the page.
		add_action( 'wp_head', [ $this, 'add_paginated_styles' ] );

		add_action( 'wp_body_open', [ $this, 'add_gtm_noscript' ], 1 );

		add_action( 'pre_get_posts', [ $this, 'modify_search_query' ] );

		add_action( 'acf/include_fields', [ $this, 'register_acf_fields' ] );

		// Filter for template part areas.
		// The `default_wp_template_part_areas` filter is used to add areas to
		// the page that can be used for template parts.
		add_filter( 'default_wp_template_part_areas', [ $this, 'template_part_areas' ] );

		// Enable SVG uploads.
		// The `upload_mimes` filter is used to enable SVG uploads.
		add_filter( 'upload_mimes', [ $this, 'allow_svg_uploads' ] );
		// The `wp_check_filetype_and_ext` filter is used to sanitize SVG uploads.
		add_filter( 'wp_check_filetype_and_ext', [ $this, 'sanitize_svg' ], 10, 4 );

		// Adjust SVG display in media library.
		// The `wp_prepare_attachment_for_js` filter is used to add SVGs to the
		// media library.
		add_filter( 'wp_prepare_attachment_for_js', [ $this, 'add_svg_to_media_library' ], 10, 3 );
		add_filter( 'the_title', [ $this, 'remove_trailing_dot_from_title' ] );

		add_filter( 'posts_search', [ $this, 'modify_search_sql' ], 10, 2 );
	}

	/**
	 * Set up the theme.
	 *
	 * This method is responsible for setting up the theme. It adds support
	 * for core block styles and removes core block patterns.
	 *
	 * @since 1.0.0
	 */
	public function setup_theme() {

		global $wpdb;

		// Add support for core block styles.
		// The `wp-block-styles` feature adds support for block styles.
		// Block styles are used to customize the look and feel of blocks.
		// They are defined in the theme.json file.
		add_theme_support( 'wp-block-styles' );

		// Enqueue editor styles.
		// add_theme_support( 'editor-styles' );
		// add_editor_style(
		// 	array(
		// 		'./build/css/public.css',
		// 	)
		// );

		// Remove core block patterns.
		// The `core-block-patterns` feature adds support for block patterns.
		// Block patterns are pre-designed blocks that can be used to create
		// content. They are defined in the theme.json file.
		// We are removing this feature because we are not using block patterns
		// in this theme.
		remove_theme_support( 'core-block-patterns' );

		// Check if the permalink structure is already set
		// if (get_option('permalink_structure') !== '/%postname%/') {
		// 	// Update the permalink structure to "Post Name"
		// 	update_option('permalink_structure', '/%postname%/');
			
		// 	// Flush rewrite rules to apply the changes
		// 	flush_rewrite_rules();
		// }
	}

	/**
	 * Add custom styles to the head for paginated pages.
	 *
	 * This method adds custom styles to the head for paginated pages.
	 * The styles are used to hide the separator between posts on the last
	 * page of a paginated page.
	 *
	 * @since 1.0.0
	 */
	public function add_paginated_styles() {
		// Get the current query.
		global $wp_query;

		// Check if we are on a paginated page.
		if ( $wp_query->max_num_pages < 2 ) {
			// If we are not on a paginated page, add the custom styles.
			echo '<style>
				/*
				 * Hide the separator between posts on the last page of a
				 * paginated page.
				 */
				.blog .wp-block-post-template .wp-block-post:last-child .entry-content + .wp-block-separator,
				.archive .wp-block-post-template .wp-block-post:last-child .entry-content + .wp-block-separator,
				.search .wp-block-post-template .wp-block-post:last-child .entry-content + .wp-block-separator {
					display: none;
				}
			</style>';
		}
	}

	/**
	 * Adds the Google Tag Manager (GTM) noscript code to the page.
	 *
	 * This method adds the GTM noscript code to the page. The code is used
	 * to track page views and other events if the user has JavaScript disabled.
	 *
	 * @since 1.0.0
	 */
	public function add_gtm_noscript() {
		// Add the GTM noscript code to the page.
		// This code is used to track page views and other events if the user
		// has JavaScript disabled.
		?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NK39RH6L" height="0" width="0"
        style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<?php
	}

	/**
	 * Modify the search query to search for a custom post type.
	 *
	 * This method is attached to the `pre_get_posts` action hook and is
	 * responsible for modifying the search query to search for a custom
	 * post type. It allows the post type to be filtered or extended by
	 * other plugins or themes.
	 *
	 * @since 1.0.0

	 */
	public function modify_search_query( $query ) {
		// If we are not on the admin side and the query is a search query
		// and it is the main query, modify the search query to search for
		// a custom post type.
		if ( ! is_admin() && $query->is_search && $query->is_main_query() ) {
			// Set the post type to "video" and the post status to "publish".
			// This will allow the search query to search for published video
			// posts.
			$query->set( 'post_type', ['video'] );
			$query->set( 'post_status', 'publish' );
		}
	}

	public function register_acf_fields() {

		if ( function_exists( 'acf_import_field_group' ) ) {

			// Get all json files from the /acf-field-groups directory.
			$files = glob( get_template_directory() . '/inc/acf-field-groups/*.json' );

			// If no files, bail.
			if ( ! $files ) {
				return;
			}

			// Loop through each file.
			foreach ( $files as $file ) {
				// Grab the JSON file.
				$group = file_get_contents( $file );

				// Decode the JSON.
				$group = json_decode( $group, true );

				// If not empty, import it.
				if ( is_array( $group ) && ! empty( $group ) && ! acf_get_field_group( $group[0]['key'] ) ) {
					acf_import_field_group( $group [0] );
				}
			}
		}
	}

	/**
	 * Add a sidebar template part area.
	 *
	 * This method adds a sidebar template part area to the areas.
	 * The sidebar template part area is used to add content to the sidebar
	 * of the page with sidebar template.
	 *
	 * @since 1.0.0
	 *
	 * @param array $areas Default template part areas.
	 * @return array Modified areas with sidebar added.
	 */
	public function template_part_areas( array $areas ) {
		// Add sidebar template part area.
		$areas[] = [
			'area'        => 'sidebar',
			'area_tag'    => 'section',
			'label'       => __( 'Sidebar', 'blank-theme' ),
			'description' => __( 'Sidebar for the Page (With Sidebar) template.', 'blank-theme' ),
			'icon'        => 'sidebar',
		];

		// Return the modified areas.
		return $areas;
	}

	/**
	 * Allow SVG uploads.
	 *
	 * This method adds SVG mime type to the allowed mime types,
	 * so users can upload SVG files.
	 *
	 * @since 1.0.0
	 *
	 * @param array $mimes Allowed mime types.
	 * @return array Modified mime types with SVG support.
	 */
	public function allow_svg_uploads( $mimes ) {
		// Add SVG mime type to the allowed mime types.
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	}

	/**
	 * Sanitize SVG uploads.
	 *
	 * SVG files uploaded to WordPress are not natively supported. This method
	 * works around this limitation by modifying the file type data so that
	 * it is treated as an image.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $data File type data.
	 * @param string $file Full path to the file.
	 * @param string $filename Name of the file.
	 * @param array  $mimes Allowed mime types.
	 * @return array Sanitized file data.
	 */
	public function sanitize_svg( $data, $file, $filename, $mimes ) {
		// If the file extension is SVG, set the type to SVG.
		if ( 'svg' === strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) ) {
			$data['type'] = 'image/svg+xml';
		}

		// Return the sanitized data.
		return $data;
	}

	/**
	 * Add SVG support to the media library.
	 *
	 * This method adds SVG support to the media library by modifying the attachment
	 * response data. It checks if the attachment is an image and if it is an SVG file.
	 * If the file is an SVG file, it sets the file path as the image source.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $response Attachment response data.
	 * @param object $attachment Attachment object.
	 * @param array  $meta Attachment metadata.
	 * @return array Modified response data.
	 */
	public function add_svg_to_media_library( $response, $attachment, $meta ) {
		if ( $response['type'] === 'image' && $response['subtype'] === 'svg+xml' && class_exists( 'SimpleXMLElement' ) ) {
			// Get the file path of the attachment.
			$svg_path = get_attached_file( $attachment->ID );

			// Check if the file exists.
			if ( file_exists( $svg_path ) ) {
				// Load the SVG file.
				$svg = simplexml_load_file( $svg_path );

				// Check if the file was loaded successfully.
				if ( $svg ) {
					// Set the image source to the file path.
					$response['image']['src'] = wp_get_attachment_url( $attachment->ID );
				}
			}
		}

		// Return the modified response data.
		return $response;
	}

	/**
	 * Removes trailing dot from the title.
	 *
	 * This method removes any trailing full stop (.) from the given title.
	 *
	 * @since 1.0.0
	 *
	 * @param string $title The title to be filtered.
	 *
	 * @return string The filtered title.
	 */
	public function remove_trailing_dot_from_title( $title ) {
		/**
		 * rtrim removes the trailing dot from the title.
		 *
		 * @see https://www.php.net/manual/en/function.rtrim.php
		 */
		return rtrim( $title, '.' );
	}

	/**
	 * Modify the search SQL query.
	 *
	 * This method modifies the default search SQL query to include
	 * post titles and content matching the search query.
	 *
	 * @since 1.0.0
	 *
	 * @param string $search The existing search SQL.
	 * @param WP_Query $query The WordPress query object.
	 * @return string The modified search SQL.
	 */
	public function modify_search_sql($search, $query) {
		global $wpdb;
	
		// Check if it's a search query and not in the admin area.
		if ($query->is_search && !is_admin()) {
			$search_query = esc_sql(get_search_query());
	
			// Only modify the search query if the search term is not empty.
			if (!empty($search_query)) {
				// Replace default WHERE conditions with custom search logic.
				// Only search in the post title.
				$search = " AND ({$wpdb->posts}.post_title LIKE '%{$search_query}%')";
			}
		}
	
		return $search;
	}
}