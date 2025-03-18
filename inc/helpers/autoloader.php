<?php
/**
 * Autoloader file for theme.
 *
 * @package Blank_Theme
 */

namespace Blank_Theme\Inc\Helpers;

/**
 * Auto loader function for the theme.
 *
 * This function dynamically loads classes, interfaces, traits, and other PHP files
 * based on the namespace and class name. It follows the PSR-4 autoloading standard.
 *
 * @param string $resource Source namespace.
 *
 * @return void
 */
function autoloader( $resource = '' ) {
	$resource_path  = false;
	$namespace_root = 'Blank_Theme\\';
	$resource       = trim( $resource, '\\' );

	// Check if the resource is empty, does not contain a namespace separator, 
	// or does not start with the root namespace. If so, return early.
	if ( empty( $resource ) || strpos( $resource, '\\' ) === false || strpos( $resource, $namespace_root ) !== 0 ) {
		return;
	}

	// Remove the root namespace from the resource string.
	$resource = str_replace( $namespace_root, '', $resource );

	// Convert the resource string into an array of path components.
	$path = explode(
		'\\',
		str_replace( '_', '-', strtolower( $resource ) )
	);

	// If the path array does not have enough components, return early.
	if ( empty( $path[0] ) || empty( $path[1] ) ) {
		return;
	}

	$directory = '';
	$file_name = '';

	// Determine the directory and file name based on the first path component.
	if ( 'inc' === $path[0] ) {
		switch ( $path[1] ) {
			case 'traits':
				$directory = 'traits';
				$file_name = sprintf( 'trait-%s', trim( strtolower( $path[2] ) ) );
				break;

			case 'widgets':
			case 'blocks': // phpcs:ignore PSR2.ControlStructures.SwitchDeclaration.TerminatingComment
				// If there is a class name provided for a specific directory, load it.
				// Otherwise, find it in the inc/ directory.
				if ( ! empty( $path[2] ) ) {
					$directory = sprintf( 'classes/%s', $path[1] );
					$file_name = sprintf( 'class-%s', trim( strtolower( $path[2] ) ) );
					break;
				}
			default:
				$directory = 'classes';
				$file_name = sprintf( 'class-%s', trim( strtolower( $path[1] ) ) );
				break;
		}

		// Construct the full path to the resource file.
		$resource_path = sprintf( '%s/inc/%s/%s.php', untrailingslashit( BLANK_THEME_DIR_PATH ), $directory, $file_name );
	}

	// Validate the resource file path.
	$is_valid_file = validate_file( $resource_path );

	// If the path is valid and the file exists, require it.
	if ( ! empty( $resource_path ) && file_exists( $resource_path ) && ( 0 === $is_valid_file || 2 === $is_valid_file ) ) {
		/**
		 * The require_once statement is used to include the file containing the class definition.
		 * This is done to ensure that the class is loaded only once, as required by the PSR-4 standard.
		 */
		require_once( $resource_path ); // phpcs:ignore
	}
}

spl_autoload_register( '\Blank_Theme\Inc\Helpers\autoloader' );