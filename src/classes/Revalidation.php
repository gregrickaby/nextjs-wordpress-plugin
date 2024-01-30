<?php
/**
 * Next.js WordPress Plugin: revalidation functionality
 *
 * Handles the revalidation of Next.js pages when WordPress content changes.
 * This class manages the transition of post status and triggers revalidation
 * on the Next.js frontend.
 *
 * @package NextJS_WordPress_Plugin
 * @since 1.0.0
 */

namespace NextJS_WordPress_Plugin;

/**
 * Manages the revalidation of Next.js pages in response to WordPress post updates.
 *
 * @author Greg Rickaby
 * @since 1.0.6
 */
class Revalidation {

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
		add_action( 'transition_post_status', [ $this, 'transition_handler' ], 10, 3 );
	}

	/**
	 * Handles the post status transition for revalidation purposes.
	 *
	 * This method is triggered when a post's status transitions. It determines
	 * the appropriate slug for revalidation based on the post type and initiates
	 * the revalidation process.
	 *
	 * @param string $new_status New status of the post.
	 * @param string $old_status Old status of the post.
	 * @param object $post       The post object.
	 *
	 * @return void
	 */
	public function transition_handler( string $new_status, string $old_status, object $post ): void {
		// Do not run on autosave or cron.
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			return;
		}

		// Ignore drafts and inherited posts.
		if ( ( 'draft' === $new_status && 'draft' === $old_status ) || 'inherit' === $new_status ) {
			return;
		}

		// Determine the slug based on post type.
		$post_type = $post->post_type;
		$post_name = $post->post_name;

		/**
		 * Configure the $slug based on your post types and front-end routing.
		 */
		switch ( $post_type ) {
			case 'post':
				$slug = "/blog/{$post_name}";
				break;
			case 'book':
				$slug = "/books/{$post_name}";
				break;
			default:
				$slug = $post_name;
				break;
		}

		// Trigger revalidation.
		$this->on_demand_revalidation( $slug );
	}

	/**
	 * Performs on-demand revalidation of a Next.js page.
	 *
	 * Sends a request to the Next.js revalidation endpoint to update the static
	 * content for a given slug.
	 *
	 * @param string $slug The slug of the post to revalidate.
	 *
	 * @return void
	 */
	public function on_demand_revalidation( string $slug ): void {
		// Check necessary constants and slug.
		if ( ! defined( 'NEXTJS_FRONTEND_URL' ) || ! defined( 'NEXTJS_REVALIDATION_SECRET' ) || ! $slug ) {
			return;
		}

		// Construct the revalidation URL.
		$revalidation_url = add_query_arg( 'slug', $slug, esc_url_raw( rtrim( NEXTJS_FRONTEND_URL, '/' ) . '/api/revalidate' ) );

		// Make a GET request to the revalidation endpoint.
		$response = wp_remote_get(
			$revalidation_url,
			[
				'headers' => [
					'x-vercel-revalidation-secret' => NEXTJS_REVALIDATION_SECRET,
				],
			]
		);

		// Handle response errors.
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			error_log( 'Revalidation error: ' . wp_remote_retrieve_response_message( $response ) ); // phpcs:ignore
		}
	}
}
