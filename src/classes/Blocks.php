<?php
/**
 * Next.js WordPress Plugin: blocks functionality
 *
 * Handles the creation and retrieval of Gutenberg blocks for REST API.
 *
 * @package NextJS_WordPress_Plugin
 * @since 1.0.0
 */

namespace NextJS_WordPress_Plugin;

/**
 * Handles operations related to Gutenberg blocks in REST API.
 *
 * @author Greg Rickaby
 * @since 1.0.6
 */
class Blocks {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Registers hooks for the class.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'rest_api_init', [ $this, 'create_field' ] );
	}

	/**
	 * Registers a custom REST field for Gutenberg blocks.
	 *
	 * This method adds a custom field to WordPress REST API
	 * which returns the Gutenberg blocks for a given post.
	 *
	 * @return void
	 */
	public function create_field(): void {
		// Get all post types that are shown in REST.
		$rest_post_types = array_values( get_post_types( [ 'show_in_rest' => true ] ) );

		// Register the 'gutenberg_blocks' field for all REST post types.
		register_rest_field(
			$rest_post_types,
			'gutenberg_blocks',
			[
				'get_callback' => [ $this, 'get_blocks' ],
			]
		);
	}

	/**
	 * Retrieves the Gutenberg blocks for a post and parses them into an array.
	 *
	 * This method is used as a callback for the 'gutenberg_blocks' REST field,
	 * and it returns an array of parsed block data for a given post.
	 *
	 * @param array $post    The post array from REST response.
	 *
	 * @return array Parsed Gutenberg block data, empty if 'blocks' param not present in request.
	 */
	public function get_blocks( array $post ): array {
		// Removed the check for 'blocks' parameter.

		// Check if post data is valid.
		if ( ! is_array( $post ) || ! isset( $post['id'] ) ) {
			return [];
		}

		// Retrieve the post object based on the ID.
		$post_obj = get_post( absint( $post['id'] ) );

		// If there's an error in retrieving the post or post is null, return empty array.
		if ( is_wp_error( $post_obj ) || is_null( $post_obj ) ) {
			return [];
		}

		// Parse the blocks from the post content and return.
		return parse_blocks( $post_obj->post_content );
	}
}
