<?php

/**
 * Enqueue theme assets.
 *
 * @package Classic-Theme
 */

namespace Classic_Theme\Inc;

use Classic_Theme\Inc\Traits\Singleton;

/**
 * Class Assets
 */
class Assets
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
	 * Set up hooks for the class.
	 *
	 * This method sets up necessary hooks for the class, such as registering
	 * scripts and styles to be enqueued.
	 *
	 * @return void
	 */
	protected function setup_hooks()
	{

		// Register and enqueue scripts.
		add_action('wp_enqueue_scripts', [$this, 'register_scripts']);

		// Register and enqueue styles.
		add_action('wp_enqueue_scripts', [$this, 'register_styles']);

		// Register and enqueue editor styles.
		add_action('enqueue_block_editor_assets', [$this, 'register_editor_styles']);
	}

	/**
	 * Register and enqueue theme scripts.
	 *
	 * This method registers the main JavaScript file for the theme and enqueues
	 * it to be included in the front-end. It includes necessary dependencies
	 * such as jQuery.
	 *
	 * @return void
	 */
	public function register_scripts()
	{
		// Register Library styles
		$this->register_script('swiper', 'library/swiper/swiper-bundle.min.js', [], '11.2.6');

		// Register the main JavaScript file for the theme with jQuery dependency.
		$this->register_script('classic-theme-main', 'js/main.js', ['jquery', 'swiper']);

		// Enqueue the registered JavaScript file to be included in the front-end.
		wp_enqueue_script('classic-theme-main');
	}

	/**
	 * Register and enqueue styles for the theme.
	 *
	 * This method registers the main CSS file for the theme and enqueues it
	 * to be included in the front-end of the site. It utilizes WordPress's
	 * style enqueueing functions to add styles to the front-end.
	 *
	 * @action wp_enqueue_scripts
	 * @return void
	 */
	public function register_styles()
	{
		// Register the main CSS file for the theme.
		$suffix = is_rtl() ? '-rtl' : '';

		// Register Library styles
		$this->register_style('font-awesome', "library/fontawesome/all{$suffix}.min.css", [], '6.7.2');
		$this->register_style('swiper', "library/swiper/swiper-bundle{$suffix}.min.css", [], '11.2.6');

		$this->register_style('classic-theme-main', "css/main{$suffix}.css", ['font-awesome', 'swiper']);

		// Enqueue the registered CSS file to be included in the front-end.
		$fonts = $this->get_font_url();
		if ($fonts) {
			require_once CLASSIC_THEME_TEMP_DIR . '/inc/helpers/wptt-webfont-loader.php';
			wp_enqueue_style(
				'classic-theme-font',
				wptt_get_webfont_url($fonts),
				[],
				CLASSIC_THEME_VERSION
			);
		}
		wp_enqueue_style('classic-theme-main');

		// Register the main CSS file for the theme.
		$this->register_style('classic-theme-main', 'css/main.css');

		// Enqueue the registered CSS file to be included in the front-end.
		wp_enqueue_style('classic-theme-main');
	}

	/**
	 * Register and enqueue editor styles.
	 *
	 * This method registers and enqueues the CSS file specific to the block editor.
	 * It ensures that the editor styles match the front-end styles for a consistent editing experience.
	 *
	 * @action enqueue_block_editor_assets
	 */
	public function register_editor_styles()
	{
		// Register the editor CSS file.
		$this->register_style('classic-theme-editor', 'css/editor.css');

		// Enqueue the registered editor CSS file to be included in the block editor.
		wp_enqueue_style('classic-theme-editor');
	}

	/**
	 * Retrieves asset dependencies and version info from assets.php.
	 *
	 * @since 1.0.0
	 * @param string      $file File name or path relative to assets/build/.
	 * @param string[]    $deps Additional script dependencies to merge.
	 * @param string|bool $ver  Asset version string or false for default.
	 * @return array {
	 *     @type string[] $dependencies Array of script dependencies.
	 *     @type string   $version      Script version number.
	 * }
	 */
	public function get_asset_meta(string $file, array $deps = [], $ver = false): array
	{
		$file = ltrim($file, '/');
		$asset_meta_file = CLASSIC_THEME_BUILD_DIR . '/assets.php';

		$default_meta = [
			'dependencies' => $deps,
			'version'      => $ver ?: (file_exists(CLASSIC_THEME_BUILD_DIR . '/' . $file) ? filemtime(CLASSIC_THEME_BUILD_DIR . '/' . $file) : CLASSIC_THEME_VERSION),
		];

		if (! is_readable($asset_meta_file)) {
			if (defined('WP_DEBUG') && WP_DEBUG) {
				error_log(sprintf('Elementify Addons: Asset meta file %s not readable.', $asset_meta_file));
			}
			return $default_meta;
		}

		$asset_meta = include $asset_meta_file;

		if (! is_array($asset_meta) || ! isset($asset_meta[$file])) {
			return $default_meta;
		}

		$meta = $asset_meta[$file];
		$meta['dependencies'] = array_unique(array_merge($meta['dependencies'] ?? [], $deps));
		$meta['version'] = $ver ?: ($meta['version'] ?? $default_meta['version']);

		return $meta;
	}

	/**
	 * Registers a new script with WordPress.
	 *
	 * @since 1.0.0
	 * @param string      $handle    Unique script handle.
	 * @param string      $file      Script file path relative to assets/build/.
	 * @param string[]    $deps      Array of dependent script handles.
	 * @param string|bool $ver       Script version number or false for default.
	 * @param bool        $in_footer Whether to load script in footer.
	 * @return bool True on successful registration, false otherwise.
	 */
	public function register_script(string $handle, string $file, array $deps = [], $ver = false, bool $in_footer = true): bool
	{
		if (empty($handle) || empty($file)) {
			return false;
		}

		$src = esc_url_raw(CLASSIC_THEME_BUILD_URI . '/' . ltrim($file, '/'));
		$asset_meta = $this->get_asset_meta($file, $deps, $ver);

		return wp_register_script(
			sanitize_key($handle),
			$src,
			array_map('sanitize_key', $asset_meta['dependencies']),
			sanitize_text_field($asset_meta['version']),
			$in_footer
		);
	}

	/**
	 * Registers a new stylesheet with WordPress.
	 *
	 * @since 1.0.0
	 * @param string      $handle Unique stylesheet handle.
	 * @param string      $file   Style file path relative to assets/build/.
	 * @param string[]    $deps   Array of dependent stylesheet handles.
	 * @param string|bool $ver    Stylesheet version number or false for default.
	 * @param string      $media  Media type for the stylesheet.
	 * @return bool True on successful registration, false otherwise.
	 */
	public function register_style(string $handle, string $file, array $deps = [], $ver = false, string $media = 'all'): bool
	{
		if (empty($handle) || empty($file)) {
			return false;
		}

		$src = esc_url_raw(CLASSIC_THEME_BUILD_URI . '/' . ltrim($file, '/'));
		$asset_meta = $this->get_asset_meta($file, $deps, $ver);

		return wp_register_style(
			sanitize_key($handle),
			$src,
			array_map('sanitize_key', $asset_meta['dependencies']),
			sanitize_text_field($asset_meta['version']),
			sanitize_key($media)
		);
	}

	/**
	 * Gets the URL of the Google Fonts stylesheet.
	 *
	 * @since 1.0.0
	 * @return string The URL of the Google Fonts stylesheet.
	 */
	public function get_font_url()
	{
		$fonts = array(
			'Albert+Sans:ital,wght@0,100..900;1,100..900'
		);

		$uri = add_query_arg(array(
			'family' 	=> implode('&family=', $fonts),
			'display' 	=> 'swap',
		), 'https://fonts.googleapis.com/css2');

		return esc_url_raw($uri);
	}
}