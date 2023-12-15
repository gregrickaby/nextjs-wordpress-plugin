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
 * @param object $post       The post object.
 */
function transition_handler( $new_status, $old_status, $post ): void {

	// Do not run on autosave or cron.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || defined( 'DOING_CRON' ) && DOING_CRON ) {
		return;
	}

	// If the post is a draft, bail.
	if ( 'draft' === $new_status && 'draft' === $old_status || 'inherit' === $new_status ) {
		return;
	}

	// Set post type and slug.
	$post_type = $post->post_type;
	$post_name = $post->post_name;

	/**
	 * Next.js requires a path + slug for revalidation.
	 *
	 * @see https://nextjs.org/docs/app/api-reference/functions/revalidatePath#revalidating-a-page-path
	 */
	switch ( $post_type ) :
		case 'post':
			$slug = '/blog/' . $post_name;
			break;
		case 'book':
			$slug = '/books/' . $post_name;
			break;
		default:
			$slug = $post_name;
			break;
	endswitch;

	// Revalidate.
	on_demand_revalidation( $slug );
}
add_action( 'transition_post_status', __NAMESPACE__ . '\transition_handler', 10, 3 );

/**
 * Flush the frontend cache when a post is updated.
 *
 * This function will fire anytime a post is updated.
 * Including: the post status, comments, meta, terms, etc.
 *
 * @usage https://nextjswp.com/api/revalidate?slug=foo-bar-baz
 *
 * @see https://nextjs.org/docs/app/building-your-application/data-fetching/fetching-caching-and-revalidating#on-demand-revalidation
 *
 * @param string $slug The post slug.
 *
 * @return void.
 */
function on_demand_revalidation( $slug ): void {

	// No constants or slug? Bail.
	if ( ! defined( 'NEXTJS_FRONTEND_URL' ) || ! defined( 'NEXTJS_REVALIDATION_SECRET' ) || ! $slug ) {
		return;
	}

	// Build the revalidation URL.
	$revalidation_url = add_query_arg(
		'slug',
		$slug,
		esc_url_raw( rtrim( NEXTJS_FRONTEND_URL, '/' ) . '/api/revalidate' )
	);

	// GET request to the revalidation endpoint with our secret.
	$response = wp_remote_get(
		$revalidation_url,
		[
			'headers' => [
				'x-vercel-revalidation-secret' => NEXTJS_REVALIDATION_SECRET,
			],
		]
	);

	// Check response code.
	if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
		error_log( 'Revalidation error: ' . wp_remote_retrieve_response_message( $response ) ); // phpcs:ignore
	}

	// Check the response.
	if ( is_wp_error( $response ) ) {
		$error_message = $response->get_error_message();
		error_log( 'Revalidation error: ' . $error_message ); // phpcs:ignore
	}
}
