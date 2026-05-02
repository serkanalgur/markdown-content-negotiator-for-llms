<?php
/**
 * Disable W3 Total Cache plugin for serving Markdown content.
 *
 * @package Markdown_Content_Negotiator_For_LLMs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Markdown_Content_Negotiator_For_LLMs_Disable_W3tc
 *
 * Checks Accept headers and disable W3 Total Cache plugin.
 */
class Markdown_Content_Negotiator_For_LLMs_Disable_W3tc {

	/**
	 * Constructor for the Disable W3tc.
	 */
	public function __construct() {
        add_action( 'init', array( $this, 'disable_w3tc_for_custom_header'), 1 );
	}

    public function disable_w3tc_for_custom_header() {

        $accept_header = isset( $_SERVER['HTTP_ACCEPT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT'] ) ) : '';

        if ( false === strpos( $accept_header, 'text/markdown' ) ) {
            return;
        }

        // 1. Page cache (most common)
        if ( function_exists( 'w3tc_prevent_cache' ) ) {
            w3tc_prevent_cache();
        }

        // 2. If using disk-based page cache, W3TC checks this constant
        if ( ! defined( 'DONOTCACHEPAGE' ) ) {
            define( 'DONOTCACHEPAGE', true );
        }

        // 3. Object cache bypass (optional, if you use it)
        if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
            define( 'DONOTCACHEOBJECT', true );
        }

        // 4. Database cache bypass (optional)
        if ( ! defined( 'DONOTCACHEDB' ) ) {
            define( 'DONOTCACHEDB', true );
        }
    }
}