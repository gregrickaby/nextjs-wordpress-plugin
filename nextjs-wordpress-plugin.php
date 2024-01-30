<?php
/**
 * Plugin Name: Next.js WordPress Plugin
 * Plugin URI:  https://github.com/gregrickaby/nextjs-wordpress-plugin
 * Description: A plugin to help turn WordPress into a headless CMS.
 * Version:     1.0.6
 * Author:      Greg Rickaby <greg@gregrickaby.com>
 * Author URI:  https://gregrickaby.com
 * License:     MIT
 *
 * @package NextJS_WordPress_Plugin
 */

namespace NextJS_WordPress_Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Define constants.
define( 'NEXTJS_WORDPRESS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NEXTJS_WORDPRESS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NEXTJS_WORDPRESS_PLUGIN_VERSION', '1.0.6' );

// Require files.
$autoload_file = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $autoload_file ) ) {
	require $autoload_file;
} else {
	require_once 'src/classes/Blocks.php';
	require_once 'src/classes/Links.php';
	require_once 'src/classes/Plugin.php';
	require_once 'src/classes/Revalidation.php';
	require_once 'src/classes/YoastSEO.php';
}

// Initialize the plugin.
$nextjs_wordpress_plugin = new Plugin();
