<?php
/**
 * Contains custom functions used for the theme
 *
  * @package Blank-Theme-Child
 */

// Registers block binding callback function for the post format name.
if ( ! function_exists( 'blank_theme_child_format_binding' ) ) :
	/**
	 * Callback function for the post format name block binding source.
	 *
	 * @since Blank Theme 1.0
	 *
	 * @return string|void Post format name, or nothing if the format is 'standard'.
	 */
	function blank_theme_child_format_binding() {
		$post_format_slug = get_post_format();

		if ( $post_format_slug && 'standard' !== $post_format_slug ) {
			// TODO: Make sure the translation works.
			return get_post_format_string( $post_format_slug );
		}
	}
endif;