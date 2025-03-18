<?php
/**
 * Register Meta Boxes
 *
 * @package Blank_Theme
 */

namespace Blank_Theme\Inc;

use Blank_Theme\Inc\Traits\Singleton;

/**
 * Class Utils
 */
class Utils {

    use Singleton;

    /**
     * Fetches data from an API.
     *
     * @param string $url The API URL.
     * @param array  $params The parameters to be appended to the URL as a query string.
     *
     * @return array|string The API response as an associative array, or an error message.
     */
    public static function get_api_data( $title, $type ) {

        $type   = ( $type === 'experimentResponse' ) ? 'experimentResponse' : 'conceptResponse';
        $output = [];
        $params = [
            'query'             => esc_html( $title ),
            'page'              => 1,
            'per_page'          => ( $type === 'experimentResponse' ) ? 3 : 6,
            'category_filter'   => ( $type === 'experimentResponse' ) ? ["journal"] : ["blank_theme_core"]
        ];

        /**
         * Initialize the cURL session
         */
        $ch = curl_init();

        /**
         * Set the URL and parameters
         */
        curl_setopt( $ch, CURLOPT_URL, 'https://api.blank-theme.com/api/free/search/search_ai' );
        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );

        /**
         * Set the return type to a string
         */
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        /**
         * Set the Content-Type header to application/x-www-form-urlencoded
         */
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/x-www-form-urlencoded',
            ]
        );

        /**
         * Execute the cURL request
         */
        $response = curl_exec( $ch );

        /**
         * Check for cURL errors
         */
        if ( $response === false ) {
            $error_msg = curl_error( $ch );
            curl_close( $ch );
            return 'cURL error: ' . $error_msg;
        }

        /**
         * Close the cURL session
         */
        curl_close( $ch );

        /**
         * Decode the JSON response
         */
        $decoded_response = json_decode( $response, true );

        /**
         * Check for JSON decoding errors
         */
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return 'JSON decode error: ' . json_last_error_msg();
        }

        if ( !empty( $decoded_response['content']['result'] ) ) {
            foreach ($decoded_response['content']['result'] as $key => $value) {
                $output['data'][$type][$key]['blank_themeArticleId'] = $value['id'];
                $output['data'][$type][$key]['blank_themeTitle'] = $value['title'];
                $output['data'][$type][$key]['seoTitle'] = $value['seoTitle'];
                $output['data'][$type][$key]['excerpt'] = $value['excerpt'];
                $output['data'][$type][$key]['publicationDate'] = $value['published_at'];
                $output['data'][$type][$key]['thumbnail'] = $value['header_image'];
                $output['data'][$type][$key]['video'] = "https://app.blank-theme.com/v/" . $value['id'];
                $output['data'][$type][$key]['views'] = $value['total_count_views'];
                $output['data'][$type][$key]['lengthMinutes'] = $value['lengthMinutes'];
            }
        }

        /**
         * Return the decoded response
         */
        return $output;
    }

    public static function fetch_visualize_data( $id ) {
        $api_url = 'https://visualize-api.blank-theme.com/blank-theme/expriment-concept/' . $id; // production
        //$api_url = 'https://visualize-api.dev.test.blank-theme.com/blank-theme/expriment-concept/' . $id; // dev
    
        // Fetch API response
        $response = wp_remote_get($api_url);
    
        // Check for errors
        if (is_wp_error($response)) {
            return 'Error fetching data';
        }
    
        // Get the response body
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true); // Convert JSON to an associative array

        return $data??[];
    }
    
    /**
     * Retrieves the term ID by its slug and taxonomy.
     *
     * This function looks up a term by its slug within a specified taxonomy
     * and returns the term ID if found. It handles errors by returning null
     * when the term is not found or if an error occurs.
     *
     * @param string $slug     The slug of the term to find.
     * @param string $taxonomy The taxonomy to search within.
     *
     * @return int|null The term ID if found, null otherwise.
     */
    public static function get_term_id_by_slug( $slug, $taxonomy ) {
        // Attempt to retrieve the term by its slug and taxonomy
        $term = get_term_by( 'slug', $slug, $taxonomy );

        // Check if the term was found and not an error
        if ( $term && ! is_wp_error( $term ) ) {
            // Return the term ID
            return $term->term_id;
        }

        // Return null if the term was not found or an error occurred
        return null;
    }

    /**
     * Trims a string to a specified length without breaking words, appending a suffix if trimmed.
     *
     * @param string $string The input string.
     * @param int    $limit  The maximum length of the trimmed string.
     * @param string $suffix The suffix to append if the string is trimmed. Defaults to '...'.
     *
     * @return string The trimmed string with the suffix if applicable.
     */
    public static function strlen( $string, $limit, $suffix = '...' ) {
        $string = trim( $string );

        if ( mb_strlen( $string ) <= $limit ) {
            return $string;
        }

        $trimmed = mb_substr( $string, 0, $limit );
        $last_space = mb_strrpos( $trimmed, ' ' );

        if ( $last_space !== false ) {
            $trimmed = mb_substr( $trimmed, 0, $last_space );
        }

        return $trimmed . $suffix;
    }

    /**
     * Decodes a URL-encoded string while preserving special characters.
     *
     * @param string $str The URL-encoded string to decode.
     * @return string The decoded string.
     */
    public static function urldecode( $str ) {
        $revert = [
            '%21' => '!', // exclamation mark
            '%2A' => '*', // asterisk
            '%27' => "'", // single quote
            '%28' => '(', // left parenthesis
            '%29' => ')', // right parenthesis
        ];

        return strtr( rawurldecode( $str ), $revert );
    }

    /**
     * Removes HTML tags from a string.
     *
     * This function uses a regular expression to remove any HTML tags from the input string.
     *
     * @param string $string The input string.
     * @return string The string with HTML tags removed.
     */
    public static function remove_html_tags( $string ) {

        // Use a regular expression to remove HTML tags
        return preg_replace('/<\/?[^>]+>/', '', $string);

    }
}