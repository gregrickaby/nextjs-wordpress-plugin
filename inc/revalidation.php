<?php
/**
 * Revalidation functionality.
 *
 * @author Greg Rickaby
 * @since 1.0.0
 * @package NEXTJS_WORDPRESS_PLUGIN
 */

namespace NEXTJS_WORDPRESS_PLUGIN;

/**
 * Handle post status transition.
 *
 * @param string $new_status New status.
 * @param string $old_status Old status.
 */
function transition_handler( $new_status, $old_status ): void {
	// If the post is a draft, bail.
	if ( 'draft' === $new_status && 'draft' === $old_status ) {
		return;
	}

	// Otherwise, revalidate.
	on_demand_revalidation();
}
add_action( 'transition_post_status', __NAMESPACE__ . '\transition_handler', 10, 3 );

/**
 * Flush the frontend cache when a post is updated.
 *
 * This function will fire anytime a post is updated.
 * Including: the post status, comments, meta, terms, etc.
 *
 * @usage https://nextjswp.com/api/revalidate
 *
 * @see https://nextjs.org/docs/app/building-your-application/data-fetching/fetching-caching-and-revalidating#on-demand-revalidation
 */
function on_demand_revalidation(): void {

	// Do not run on autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// No constants or Post ID? Bail...
	if ( ! defined( 'NEXTJS_FRONTEND_URL' ) || ! defined( 'NEXTJS_REVALIDATION_SECRET' ) ) {
		return;
	}

	// Build the API URL.
	$revalidation_url = esc_url_raw( NEXTJS_FRONTEND_URL . 'api/revalidate' );

	// Make API request.
	$response = wp_remote_post(
		$revalidation_url,
		[
			'headers' => [
				'x-vercel-revalidation-secret' => NEXTJS_REVALIDATION_SECRET,
			],
		]
	);

	// Check the response.
	if ( is_wp_error( $response ) ) {
		$error_message = $response->get_error_message();
		error_log( 'Revalidation error: ' . $error_message );
	}
}
