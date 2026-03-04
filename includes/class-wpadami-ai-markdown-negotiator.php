<?php
/**
 * Negotiator for serving Markdown content.
 *
 * @package WPADAMI_AI_Markdown
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPADAMI_AI_Markdown_Negotiator
 *
 * Checks Accept headers and serves Markdown content instead of HTML or JSON.
 */
class WPADAMI_AI_Markdown_Negotiator {


	/**
	 * Constructor for the Negotiator.
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'detect_and_serve_markdown' ), 5 );
		add_filter( 'rest_pre_dispatch', array( $this, 'handle_rest_markdown' ), 10, 3 );
	}

	/**
	 * Handle Markdown requests via REST API.
	 *
	 * @param mixed           $result  Response to return.
	 * @param WP_REST_Server  $server  REST server object.
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return mixed
	 */
	public function handle_rest_markdown( $result, $server, $request ) {
		$accept_header = $request->get_header( 'accept' );

		if ( $accept_header && strpos( $accept_header, 'text/markdown' ) !== false ) {
			return $result;
		}
		return $result;
	}

	/**
	 * Detect Accept: text/markdown header and serve cached markdown content.
	 */
	public function detect_and_serve_markdown() {
		$accept_header = isset( $_SERVER['HTTP_ACCEPT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT'] ) ) : '';

		if ( false === strpos( $accept_header, 'text/markdown' ) ) {
			return;
		}

		if ( ! is_singular() ) {
			return;
		}

		$post_id  = get_queried_object_id();
		$markdown = get_post_meta( $post_id, WPADAMI_AI_Markdown_Cron::META_KEY_MARKDOWN, true );
		$tokens   = get_post_meta( $post_id, WPADAMI_AI_Markdown_Cron::META_KEY_TOKENS, true );

		if ( empty( $markdown ) ) {
			$generator = new WPADAMI_AI_Markdown_Generator();
			$post      = get_post( $post_id );
			$markdown  = $generator->generate_markdown( $post );
			$tokens    = WPADAMI_AI_Markdown_Generator::estimate_markdown_tokens( $markdown );
		}

		$signal = $this->get_content_signal( $post_id );

		header( 'Content-Type: text/markdown; charset=UTF-8' );
		header( 'X-Markdown-Tokens: ' . (int) $tokens );
		header( 'X-Content-Signal: ' . esc_attr( $signal ) );
		header( 'Vary: Accept' );

		echo esc_html( $markdown );
		exit;
	}

	/**
	 * Generate dynamic X-Content-Signal header.
	 *
	 * @param int $post_id The post ID.
	 */
	private function get_content_signal( $post_id ) {
		$post       = get_post( $post_id );
		$type       = $post->post_type;
		$categories = wp_get_post_categories( $post_id, array( 'fields' => 'names' ) );

		$depth = 'general';
		if ( in_array( 'Technical', $categories, true ) || in_array( 'Code', $categories, true ) ) {
			$depth = 'technical';
		}

		$priority = 'standard';
		if ( is_sticky( $post_id ) ) {
			$priority = 'high';
		}

		$signal = "type={$type}, depth={$depth}, priority={$priority}";

		$extra_signal = get_option( 'wpadami_markdown_content_signal', 'ai-train=yes, search=yes, ai-input=yes' );
		if ( ! empty( $extra_signal ) ) {
			$signal .= ', ' . $extra_signal;
		}

		return $signal;
	}
}
