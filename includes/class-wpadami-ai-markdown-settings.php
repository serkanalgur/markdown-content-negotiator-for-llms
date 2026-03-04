<?php
/**
 * Settings for AI Markdown.
 *
 * @package WPADAMI_AI_Markdown
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPADAMI_AI_Markdown_Settings
 *
 * Handles the administration settings page and option registration.
 */
class WPADAMI_AI_Markdown_Settings {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register the settings menu item.
	 */
	public function add_settings_menu() {
		add_options_page(
			'AI Markdown Settings',
			'AI Markdown',
			'manage_options',
			'wpadami-markdown-negotiator',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		register_setting( 'wpadami_markdown_settings', 'wpadami_markdown_post_types', array( 'sanitize_callback' => array( $this, 'sanitize_post_types' ) ) );
		register_setting( 'wpadami_markdown_settings', 'wpadami_markdown_content_signal', array( 'sanitize_callback' => 'sanitize_text_field' ) );

		if ( isset( $_GET['action'] ) && 'regenerate_markdown' === $_GET['action'] && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'wpadami_markdown_regenerate' ) ) {
			$cron = new WPADAMI_AI_Markdown_Cron();
			$cron->process_all_posts();
			add_settings_error( 'wpadami_markdown_messages', 'wpadami_markdown_message', 'Markdown cache regeneration triggered!', 'updated' );
		}
	}

	/**
	 * Sanitize post types array.
	 *
	 * @param array $input The input array of post types.
	 */
	public function sanitize_post_types( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}
		return array_map( 'sanitize_key', $input );
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$selected_types = get_option( 'wpadami_markdown_post_types', array( 'post', 'page' ) );
		$content_signal = get_option( 'wpadami_markdown_content_signal', 'ai-train=yes, search=yes, ai-input=yes' );
		$all_post_types = get_post_types( array( 'public' => true ), 'objects' );

		?>
		<div class="wrap">
			<h1>AI Markdown Settings</h1>
			<form method="post" action="options.php">
		<?php settings_fields( 'wpadami_markdown_settings' ); ?>
		<?php do_settings_sections( 'wpadami_markdown_settings' ); ?>

				<table class="form-table">
					<tr valign="top">
						<th scope="row">Enabled Post Types</th>
						<td>
				<?php foreach ( $all_post_types as $type ) : ?>
							<label>
								<input type="checkbox" name="wpadami_markdown_post_types[]" value="<?php echo esc_attr( $type->name ); ?>" <?php checked( in_array( $type->name, (array) $selected_types, true ) ); ?>>
							<?php echo esc_html( $type->label ); ?>
							</label><br>
				<?php endforeach; ?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">X-Content-Signal Extra</th>
						<td>
							<input type="text" name="wpadami_markdown_content_signal" value="<?php echo esc_attr( $content_signal ); ?>" class="regular-text">
							<p class="description">Additional signals to append to the X-Content-Signal header (e.g., <code>ai-train=yes, search=yes, ai-input=yes</code>).</p>
						</td>
					</tr>
				</table>

		<?php submit_button(); ?>
			</form>

			<hr>

			<h2>Manual Actions</h2>
			<p>Click below to manually trigger the Markdown cache regeneration for all selected post types.</p>
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'options-general.php?page=wpadami-markdown-negotiator&action=regenerate_markdown' ), 'wpadami_markdown_regenerate' ) ); ?>" class="button button-secondary">Regenerate Markdown Cache Now</a>
		</div>
		<?php
	}
}
