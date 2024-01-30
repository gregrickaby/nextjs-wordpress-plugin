<?php
/**
 * Next.js WordPress Plugin: links functionality
 *
 * Handles the modification of various WordPress URLs to integrate with a headless client.
 *
 * @package NextJS_WordPress_Plugin
 * @since 1.0.0
 */

namespace NextJS_WordPress_Plugin;

use WP_Post;
use WP_REST_Response;
use DOMDocument;

/**
 * Modify various WordPress URLs to integrate with a headless client.
 *
 * @author Greg Rickaby
 * @since 1.0.6
 */
class Links {

	/**
	 * Frontend URL.
	 *
	 * @var string|null
	 */
	private $frontend_url;

	/**
	 * Preview secret.
	 *
	 * @var string|null
	 */
	private $preview_secret;

	/**
	 * Constructor.
	 */
	public function __construct() {

		// Set the frontend URL and preview secret.
		$this->frontend_url   = $this->get_frontend_url();
		$this->preview_secret = defined( 'NEXTJS_PREVIEW_SECRET' ) ? NEXTJS_PREVIEW_SECRET : null;

		// Apply the hooks and filters.
		$this->hooks();
	}

	/**
	 * Registers hooks for the class.
	 *
	 * @return void
	 */
	public function hooks() {
		add_filter( 'preview_post_link', [ $this, 'set_headless_preview_link' ], 10, 2 );
		add_filter( 'home_url', [ $this, 'set_headless_home_url' ], 10, 3 );
		add_filter( 'rest_prepare_page', [ $this, 'set_headless_rest_preview_link' ], 10, 2 );
		add_filter( 'rest_prepare_post', [ $this, 'set_headless_rest_preview_link' ], 10, 2 );
		add_action( 'save_post', [ $this, 'override_post_links' ] );
	}

	/**
	 * Customize the preview button in the WordPress admin.
	 *
	 * This method modifies the preview link for a post to point to a headless client setup.
	 *
	 * @param string  $link Original WordPress preview link.
	 * @param WP_Post $post Current post object.
	 * @return string Modified headless preview link.
	 */
	public function set_headless_preview_link( string $link, WP_Post $post ): string {

		// Return the original link if the frontend URL or preview secret are not defined.
		if ( ! $this->frontend_url || ! $this->preview_secret ) {
			return $link;
		}

		// Update the preview link to point to the front-end.
		return add_query_arg(
			[ 'secret' => $this->preview_secret ],
			esc_url_raw( "{$this->frontend_url}/preview/{$post->ID}" )
		);
	}

	/**
	 * Customize the WordPress home URL to point to the headless frontend.
	 *
	 * @param string      $url Original home URL.
	 * @param string      $path Path relative to home URL.
	 * @param string|null $scheme URL scheme.
	 * @return string Modified frontend home URL.
	 */
	public function set_headless_home_url( string $url, string $path, $scheme = null ): string {
		global $current_screen;

		// Do not modify the URL for REST requests.
		if ( 'rest' === $scheme ) {
			return $url;
		}

		// Avoid modifying the URL in the block editor to ensure functionality.
		if ( ( is_string( $current_screen ) || is_object( $current_screen ) ) && method_exists( $current_screen, 'is_block_editor' ) ) {
			return $url;
		}

		// Do not modify the URL outside the WordPress admin.
		if ( ! is_admin() ) {
			return $url;
		}

		// Get the frontend URL.
		$base_url = $this->get_frontend_url();

		// Return the original URL if the frontend URL is not defined.
		if ( ! $base_url ) {
			return $url;
		}

		// Return the modified URL.
		return $path ? "{$base_url}/" . ltrim( $path, '/' ) : $base_url;
	}

	/**
	 * Customize the REST preview link to point to the headless client.
	 *
	 * This function modifies the REST response to change the preview link of a post.
	 * For draft posts, it sets the preview link to a draft preview link.
	 * For published posts, it changes the link to point to the frontend, if the permalink contains the site URL.
	 *
	 * @param WP_REST_Response $response The REST response object.
	 * @param WP_Post          $post     The current post object.
	 * @return WP_REST_Response Modified response object with updated preview link.
	 */
	public function set_headless_rest_preview_link( WP_REST_Response $response, WP_Post $post ): WP_REST_Response {

		// Check if the post status is 'draft' and set the preview link accordingly.
		if ( 'draft' === $post->post_status ) {
			$response->data['link'] = get_preview_post_link( $post );
			return $response;
		}

		// For published posts, modify the permalink to point to the frontend.
		if ( 'publish' === $post->post_status ) {

			// Get the post permalink.
			$permalink = get_permalink( $post );

			// Check if the permalink contains the site URL.
			if ( false !== stristr( $permalink, get_site_url() ) ) {

				// Replace the site URL with the frontend URL.
				$response->data['link'] = str_ireplace(
					get_site_url(),
					$this->get_frontend_url(),
					$permalink
				);
			}
		}

		return $response;
	}


	/**
	 * Override post links.
	 *
	 * In order to link to the headless client, we need to override
	 * the links within the post content except for links that contain
	 * an image.
	 *
	 * @param int $post_id Post ID.
	 */
	public function override_post_links( int $post_id ): void {

		// Remove the action to avoid an infinite loop.
		remove_action( 'save_post', [ $this, 'override_post_links' ] );

		// Get the post.
		$post = get_post( $post_id );

		// No post or post is not a post or page.
		if ( ! $post || 'post' !== $post->post_type || 'page' !== $post->post_type ) {

			// Re-add the action.
			add_action( 'save_post', [ $this, 'override_post_links' ] );

			return;
		}

		// Get the post content.
		$post_content = $post->post_content;

		// Create a DOMDocument and load the HTML content.
		$dom = new DOMDocument();
		$dom->loadHTML( mb_convert_encoding( $post_content, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

		// Get all <a> tags.
		$a_tags = $dom->getElementsByTagName( 'a' );

		// Loop through each <a> tag.
		foreach ( $a_tags as $a_tag ) {

			// Check if the <a> tag contains an <img> tag.
			if ( $a_tag->getElementsByTagName( 'img' )->length > 0 ) {
				continue;
			}

			// Get the original URL.
			$original_url = $a_tag->getAttribute( 'href' );

			// If the URL does not contain the site URL, skip it.
			if ( stripos( $original_url, get_site_url() ) === false ) {
				continue;
			}

			// Replace the URL domain if it matches the site URL.
			$new_url = str_ireplace( get_site_url(), $this->get_frontend_url(), $original_url );

			// Update the href attribute.
			$a_tag->setAttribute( 'href', $new_url );
		}

		// Save the modified HTML back to post_content.
		$new_post_content = $dom->saveHTML();

		// Update the post with the modified content.
		wp_update_post(
			[
				'ID'           => $post_id,
				'post_content' => wp_slash( $new_post_content ),
			]
		);

		// Re-add the action.
		add_action( 'save_post', [ $this, 'override_post_links' ] );
	}

	/**
	 * Get the trimmed frontend URL.
	 *
	 * @return string|null Trimmed frontend URL or null if not defined.
	 */
	private function get_frontend_url(): ?string {

		// Return the frontend URL if defined.
		if ( defined( 'NEXTJS_FRONTEND_URL' ) ) {
			return rtrim( NEXTJS_FRONTEND_URL, '/' );
		}

		return null;
	}
}
