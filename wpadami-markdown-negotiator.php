<?php
/**
 * Plugin Name:       Wpadami Markdown Content Negotiator for LLMs
 * Plugin URI:        https://github.com/serkanalgur/wpadami-markdown-negotiator
 * Description:       Detects Accept: text/markdown and serves pre-generated Markdown versions of posts/pages.
 * Version:           1.0.4
 * Requires at least: 6.0
 * Tested up to:      6.9
 * Requires PHP:      7.3
 * Author:            Serkan Algur
 * Author URI:        https://github.com/serkanalgur
 * License:           GPL-2.0+
 * Text Domain:       wpadami-markdown-negotiator
 *
 * @package           WPADAMI_AI_Markdown
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define constants
 */
define( 'WPADAMI_AI_MARKDOWN_VERSION', '1.0.4' );
define( 'WPADAMI_AI_MARKDOWN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPADAMI_AI_MARKDOWN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Include classes
 */
require_once WPADAMI_AI_MARKDOWN_PATH . 'includes/class-wpadami-ai-markdown-generator.php';
require_once WPADAMI_AI_MARKDOWN_PATH . 'includes/class-wpadami-ai-markdown-cron.php';
require_once WPADAMI_AI_MARKDOWN_PATH . 'includes/class-wpadami-ai-markdown-negotiator.php';
require_once WPADAMI_AI_MARKDOWN_PATH . 'includes/class-wpadami-ai-markdown-settings.php';

/**
 * Initialize the plugin
 */
function wpadami_ai_markdown_init() {
	new WPADAMI_AI_Markdown_Generator();
	new WPADAMI_AI_Markdown_Cron();
	new WPADAMI_AI_Markdown_Negotiator();
	new WPADAMI_AI_Markdown_Settings();
}
add_action( 'plugins_loaded', 'wpadami_ai_markdown_init' );

/**
 * Activation hook
 */
register_activation_hook( __FILE__, array( 'WPADAMI_AI_Markdown_Cron', 'activate' ) );

/**
 * Deactivation hook
 */
register_deactivation_hook( __FILE__, array( 'WPADAMI_AI_Markdown_Cron', 'deactivate' ) );
