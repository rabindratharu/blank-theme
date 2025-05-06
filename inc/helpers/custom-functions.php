<?php
/**
 * Contains custom functions used for the theme
 *
  * @package Classic-Theme
 */

// Registers block binding callback function for the post format name.
if ( ! function_exists( 'classic_theme_format_binding' ) ) :
	/**
	 * Callback function for the post format name block binding source.
	 *
	 * @since Classic Theme 1.0
	 *
	 * @return string|void Post format name, or nothing if the format is 'standard'.
	 */
	function classic_theme_format_binding() {
		$post_format_slug = get_post_format();

		if ( $post_format_slug && 'standard' !== $post_format_slug ) {
			// TODO: Make sure the translation works.
			return get_post_format_string( $post_format_slug );
		}
	}
endif;