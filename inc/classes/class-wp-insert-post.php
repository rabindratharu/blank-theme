<?php
/**
 * Wp Insert Post
 *
 * @package Blank_Theme
 */

namespace Blank_Theme\Inc;

use Blank_Theme\Inc\Traits\Singleton;
use Blank_Theme\Inc\Utils;

/**
 * Handles API data loading.
 *
 * @package Blank_Theme
 */
class Wp_Insert_Post {

	use Singleton;

	/**
	 * API URL
	 *
	 * @var string
	 */
	private $url = 'https://raw.githubusercontent.com/rabindratharu/pubmed-data/refs/heads/main/data-2025-01-16.json';

	/**
	 * Option name for storing file size.
	 *
	 * @var string
	 */
	private $file_size_option = 'jove_api_file_size';

	/**
	 * The final data.
	 *
	 * @access protected
	 * @var string
	 */
	protected $data;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Setup hooks.
	 */
	protected function setup_hooks() {

		add_action( 'wp_loaded', [ $this, 'fetch_and_insert_posts' ] );
	}

	public function fetch_and_insert_posts() {

		// Get the current file size from the remote source.
		$current_file_size = $this->get_remote_file_size();

		if ( $current_file_size === false ) {
			error_log( 'Failed to retrieve remote file size. Skipping fetch.' );
			return;
		}

		// Retrieve the previously stored file size from the database.
		$stored_file_size = get_option( $this->file_size_option, 0 );

		//If the file size has not changed, skip fetching and inserting posts.
		if ( $current_file_size == $stored_file_size ) {
			error_log( 'File size unchanged. Skipping data fetch and insertion.' );
			return;
		}

		// Fetch and insert posts if the file size has changed.
		$this->data = $this->get_remote_url_contents();

		if ( ! empty( $this->data ) ) {
			foreach ( $this->data as $data ) {
				$this->insert_post( $data );
			}
			// Update the stored file size after processing.
			update_option( $this->file_size_option, $current_file_size );
		}
	}

	/**
	 * Get the size of the remote file.
	 *
	 * @return int|false File size in bytes, or false on failure.
	 */
	protected function get_remote_file_size() {
		$response = wp_remote_head( $this->url );
		if ( is_wp_error( $response ) ) {
			error_log( 'Failed to get remote file size: ' . $response->get_error_message() );
			return false;
		}
		return isset( $response['headers']['content-length'] ) ? (int) $response['headers']['content-length'] : false;
	}

	/**
	 * Get remote file contents.
	 *
	 * @access private
	 * @return string Returns the remote URL contents.
	 */
	private function get_remote_url_contents() {
		if ( is_callable( 'network_home_url' ) ) {
			$site_url = network_home_url( '', 'http' );
		} else {
			$site_url = get_bloginfo( 'url' );
		}
		$site_url = preg_replace( '/^https/', 'http', $site_url );
		$site_url = preg_replace( '|/$|', '', $site_url );
		$args = array(
			'site' => $site_url,
		);

		// Get the response.
		$api_url  = add_query_arg( $args, $this->url );

		$response = wp_remote_get(
			$api_url,
			array(
				'timeout' => 20,
			)
		);
		// Early exit if there was an error.
		if ( is_wp_error( $response ) ) {
			return '';
		}

		// Get the CSS from our response.
		$contents = wp_remote_retrieve_body( $response );

		if (is_wp_error($contents)) {
			error_log('Error retrieving remote content.');
			return [];
		}

		$data = json_decode($contents, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			error_log('JSON decoding error: ' . json_last_error_msg());
			return [];
		}

		return $data;

	}

	/**
	 * Insert or update a post with a specific post ID.
	 *
	 * @param int   $post_id Custom post ID.
	 * @param array $data    Post data array.
	 */
	private function insert_post( $data ) {
		// Ensure required WordPress functions are loaded.
		if ( ! function_exists( 'wp_insert_post' ) ) {
			require_once ABSPATH . 'wp-admin/includes/post.php';
		}

		// Check if the post already exists.
		if ( ! function_exists( 'post_exists' ) ) {
			require_once ABSPATH . 'wp-admin/includes/post.php';
		}

		// Validate required fields.
		if ( empty( $data['articleId'] ) || empty( $data['title'] ) ) {
			error_log( 'Missing ID and Title in item.' );
			return;
		}

		$content = '';
		if ( ! empty( $data['abstract'] ) ) {
			foreach ( $data['abstract'] as $abstract ) {
				if ( ! empty( $abstract['level'] ) ) {
					$content .= '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html($abstract['level']) . '</h3><!-- /wp:heading -->';
				}
				if ( ! empty( $abstract['text'] ) ) {
					$content .= '<!-- wp:paragraph --><p>' . wp_kses_post($abstract['text']) . '</p><!-- /wp:paragraph -->';
				}
			}
		}
		$post_args = [
            'post_title'   => sanitize_text_field( $data['title'] ),
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => 'video',
            'post_date'    => ! empty( $data['publicationDate'] ) ? date( 'Y-m-d H:i:s', strtotime( $data['publicationDate'] ) ) : current_time( 'mysql' ),
        ];

		$existing_post_id = post_exists( $post_args['post_title'], '', '', 'video' );

		if ( $existing_post_id ) {
            $post_args['ID'] = $existing_post_id;
            wp_update_post( $post_args );
            error_log( 'Post updated with ID: ' . $existing_post_id );
        } else {
			$post_args['import_id'] = (int) $data['articleId'];
            $result = wp_insert_post( $post_args );

            if ( is_wp_error( $result ) ) {
                error_log( 'Error inserting post: ' . $result->get_error_message() );
            } else {
                error_log( 'Post inserted with ID: ' . $result );
            }
        }

		$this->handle_terms( $result ?? $existing_post_id, $data );
	}

	/**
	 * Handle term assignments for a post.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    Post data array.
	 */
	private function handle_terms( $post_id, $data ) {
		if ( ! empty( $data['journalTitle'] ) ) {
			$this->assign_category_terms( $post_id, $data['journalTitle'], 'journal' );
		}

		if ( ! empty( $data['KeywordList'] ) ) {
			wp_set_post_terms( $post_id, $data['KeywordList'], 'keyword' );
		}

		if ( ! empty( $data['authors'] ) ) {
			$authors      = wp_list_pluck( $data['authors'], 'name' );
			$affiliations = array_merge( ...wp_list_pluck( $data['authors'], 'affiliation' ) );
			$unique_affiliations = array_unique( $affiliations );

			foreach ( $unique_affiliations as $affiliation ) {
				// Trim the term name to avoid exceeding database limits (200 characters for the name).
				if (strlen($affiliation) > 200) {
					$affiliation = substr($affiliation, 0, 200);
				}
				$this->assign_category_terms( $post_id, $affiliation, 'institution' );
			}
			wp_set_post_terms( $post_id, $authors, 'author' );

			$this->insert_meta_fields( $post_id, $data['authors'] );
		}
	}

	/**
	 * Assign terms to a post with descriptions.
	 *
	 * @param int    $post_id  Post ID.
	 * @param array  $data     Data array.
	 * @param string $key      Data key for terms.
	 * @param string $taxonomy Taxonomy name.
	 */
	private function assign_category_terms($post_id, $term_name, $taxonomy) {

		// Check if the term exists
		$term = get_term_by('name', $term_name, $taxonomy);

		if (!$term) {
			// Create the term if it doesn't exist
			$result = wp_insert_term(
				$term_name,
				$taxonomy,
				['description' => '']
			);

			if (is_wp_error($result)) {
				error_log('Error creating term: ' . $result->get_error_message());
				return;
			}

			$term_id = $result['term_id'];
		} else {
			$term_id = $term->term_id;
		}

		// Assign the term to the post
		wp_set_post_terms($post_id, [$term_id], $taxonomy);
		error_log('Term assigned to post ID ' . $post_id . ': ' . $term_name);
	}


	/**
     * Insert ACF repeater fields.
     */
    private function insert_meta_fields($post_id, $data) {
        if (!function_exists('update_field')) {
            error_log('ACF plugin is not active.');
            return;
        }

		$repeater_data = array_map(function ($row) {
            $author = Utils::get_term_id_by_slug($row['name'], 'author');
			// Extract author term IDs from the authors array
			$affiliation = array_map(function($term) {
				return Utils::get_term_id_by_slug($term, 'institution');
			}, $row['affiliation']);

            return compact('author', 'affiliation');
        }, $data);

        update_field('author_affiliation', $repeater_data, $post_id);
    }
}