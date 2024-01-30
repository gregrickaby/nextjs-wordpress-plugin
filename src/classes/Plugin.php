<?php
/**
 * Next.js WordPress Plugin: main plugin
 *
 * Initializes all necessary components for the Next.js WordPress plugin.
 * This class is responsible for loading and managing all other classes.
 *
 * @package NextJS_WordPress_Plugin
 * @since 1.0.0
 */

namespace NextJS_WordPress_Plugin;

use NextJS_WordPress_Plugin\Links;
use NextJS_WordPress_Plugin\Blocks;
use NextJS_WordPress_Plugin\Revalidation;
use NextJS_WordPress_Plugin\YoastSEO;

/**
 * Main class for initializing the Next.js WordPress integration plugin.
 *
 * @author Greg Rickaby
 * @since 1.0.6
 */
class Plugin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		new Links();
		new Blocks();
		new Revalidation();
		new YoastSEO();
	}
}
