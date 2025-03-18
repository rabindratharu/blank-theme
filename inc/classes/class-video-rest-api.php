<?php
/**
 * REST API Video controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Blank_Theme\Inc\Utils;

/**
 * REST API Video controller class.
 */
class Jove_Video_REST_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = 'blank-theme/v1';
		$this->rest_base = 'video';
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {
		/**
		 * Endpoint to create a video post.
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return WP_Error|WP_REST_Response
		 */
		register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_video' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                ],
            ]
        );
	}
	/**
	 * Permission check for creating a video post.
	 *
	 * Validates whether the current user has the capability to edit posts,
	 * which is required to create a video post via the REST API.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has edit access, WP_Error object otherwise.
	 */
	public function permission_check( $request ) {
		// Check if the current user has permission to edit posts.
		if ( current_user_can( 'edit_posts' ) ) {
			return true;
		}

		// Return an error if the user does not have permission.
		return new WP_Error( 'rest_forbidden', __( 'You do not have permissions to create video posts.','blank-theme' ), array( 'status' => 403 ) );
	}

	/**
	 * Create a lottie animation post from object
	 *
	 * This endpoint takes a JSON object with data and saves it as a Lottie Animation post.
	 * The JSON object should have the following structure:
	 * {
	 *   "articleId":  string,
	 *   "title":      string,
	 *   "abstract":   [
	 *     {
	 *       "type": string,
	 *       "text": string
	 *     }
	 *   ],
	 *   "publicationDate": string
	 * }
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return Array
	 */
	public function create_video( $request ) {
		$data = $request->get_json_params();

		if ( empty( $data ) ) {
			return new WP_Error(
				'no_data',
				'No valid data provided.',
				[ 'status' => 400 ]
			);
		}

		foreach ( $data as $item ) {
			$this->insert_post( $item );
		}

		return new WP_REST_Response(
			'Data inserted successfully',
			201
		);
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
				// if (strlen($affiliation) > 200) {
				// 	$affiliation = substr($affiliation, 0, 200);
				// }
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