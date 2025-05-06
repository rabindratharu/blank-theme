<?php

/**
 * Autoloader file for plugin.
 *
 * @package classic-theme
 */

namespace Classic_Theme\Inc\Helpers;

/**
 * Auto loader function.
 *
 * @param string $resource Source namespace.
 *
 * @return void
 */
function autoloader($resource = '')
{
	/**
	 * If the resource is empty, or the resource does not have our namespace
	 * we don't need to proceed further.
	 */
	$namespace_root = 'Classic_Theme\\';
	$resource       = trim($resource, '\\');
	if (empty($resource) || strpos($resource, '\\') === false || strpos($resource, $namespace_root) !== 0) {
		// Not our namespace, bail out.
		return;
	}

	// Remove our root namespace.
	$resource = str_replace($namespace_root, '', $resource);

	/**
	 * We need to convert the namespace to a path.
	 * We will use the explode method to convert the namespace to array.
	 * Then we will use the array to generate the file path.
	 */
	$path = explode(
		'\\',
		str_replace('_', '-', strtolower($resource))
	);

	/**
	 * Time to determine which type of resource path it is,
	 * so that we can deduce the correct file path for it.
	 */
	if (empty($path[0]) || empty($path[1])) {
		return;
	}

	$directory = '';
	$file_name = '';

	if ('inc' === $path[0]) {

		/**
		 * The first item in the $path array is 'inc'.
		 * Now we need to determine which type of resource it is.
		 * We will use the second item in the array to determine the type.
		 */
		switch ($path[1]) {
			case 'traits':
				$directory = 'traits';
				$file_name = sprintf('trait-%s', trim(strtolower($path[2])));
				break;

			case 'widgets':
			case 'blocks':
				/**
				 * If there is class name provided for specific directory then load that.
				 * otherwise find in inc/ directory.
				 */
				if (! empty($path[2])) {
					$directory = sprintf('classes/%s', $path[1]);
					$file_name = sprintf('class-%s', trim(strtolower($path[2])));
					break;
				}
				// TODO: Implement the code for the TODO comment.
				$directory = 'classes';
				$file_name = sprintf('class-%s', trim(strtolower($path[1])));
				break;
			default:
				$directory = 'classes';
				$file_name = sprintf('class-%s', trim(strtolower($path[1])));
				break;
		}
		/**
		 * Now we need to generate the file path for the resource.
		 */
		$resource_path = sprintf('%s/inc/%s/%s.php', untrailingslashit(CLASSIC_THEME_TEMP_DIR), $directory, $file_name);
	}

	/**
	 * We need to check if the file path is valid.
	 */
	$resource_path_valid = validate_file($resource_path);
	// For Windows platform, validate_file returns 2 so we've added this condition as well.
	if (! empty($resource_path) && file_exists($resource_path) && (0 === $resource_path_valid || 2 === $resource_path_valid)) {
		// We are already making sure that the file exists and it's valid.
		require_once $resource_path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
	}
}

spl_autoload_register('\Classic_Theme\Inc\Helpers\autoloader');