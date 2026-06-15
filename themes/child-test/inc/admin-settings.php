<?php
/**
 * Theme admin settings page.
 *
 * Demonstrates nonce verification, input sanitization, and output escaping.
 *
 * @package WordPress
 * @subpackage child-test
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default settings values.
 *
 * @return array<string, mixed>
 */
function child_test_get_default_settings() {
	return array(
		'footer_text'      => '',
		'contact_email'    => '',
		'company_url'      => '',
		'show_footer_text' => 0,
		'footer_layout'    => 'compact',
	);
}

/**
 * Retrieve saved settings merged with defaults.
 *
 * @return array<string, mixed>
 */
function child_test_get_settings() {
	$settings = get_option( 'child_test_settings', array() );

	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	return wp_parse_args( $settings, child_test_get_default_settings() );
}

/**
 * Sanitize settings before saving to the database.
 *
 * @param array<string, mixed> $input Raw submitted settings.
 * @return array<string, mixed>
 */
function child_test_sanitize_settings( $input ) {
	$output    = child_test_get_default_settings();
	$allowed   = array( 'compact', 'full' );
	$sanitized = is_array( $input ) ? $input : array();

	if ( isset( $sanitized['footer_text'] ) ) {
		$output['footer_text'] = sanitize_textarea_field( wp_unslash( $sanitized['footer_text'] ) );
	}

	if ( isset( $sanitized['contact_email'] ) ) {
		$output['contact_email'] = sanitize_email( wp_unslash( $sanitized['contact_email'] ) );
	}

	if ( isset( $sanitized['company_url'] ) ) {
		$output['company_url'] = esc_url_raw( wp_unslash( $sanitized['company_url'] ) );
	}

	$output['show_footer_text'] = ! empty( $sanitized['show_footer_text'] ) ? 1 : 0;

	if ( isset( $sanitized['footer_layout'] ) ) {
		$layout = sanitize_text_field( wp_unslash( $sanitized['footer_layout'] ) );
		if ( in_array( $layout, $allowed, true ) ) {
			$output['footer_layout'] = $layout;
		}
	}

	return $output;
}

/**
 * Register settings, sections, and fields.
 */
function child_test_register_settings() {
	register_setting(
		'child_test_settings_group',
		'child_test_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'child_test_sanitize_settings',
			'default'           => child_test_get_default_settings(),
		)
	);

	add_settings_section(
		'child_test_general_section',
		__( 'General Settings', 'child-test' ),
		'child_test_render_general_section',
		'child-test-settings'
	);

	add_settings_field(
		'child_test_footer_text',
		__( 'Footer Text', 'child-test' ),
		'child_test_render_footer_text_field',
		'child-test-settings',
		'child_test_general_section'
	);

	add_settings_field(
		'child_test_contact_email',
		__( 'Contact Email', 'child-test' ),
		'child_test_render_contact_email_field',
		'child-test-settings',
		'child_test_general_section'
	);

	add_settings_field(
		'child_test_company_url',
		__( 'Company URL', 'child-test' ),
		'child_test_render_company_url_field',
		'child-test-settings',
		'child_test_general_section'
	);

	add_settings_field(
		'child_test_show_footer_text',
		__( 'Display Footer Text', 'child-test' ),
		'child_test_render_show_footer_text_field',
		'child-test-settings',
		'child_test_general_section'
	);

	add_settings_field(
		'child_test_footer_layout',
		__( 'Footer Layout', 'child-test' ),
		'child_test_render_footer_layout_field',
		'child-test-settings',
		'child_test_general_section'
	);
}
add_action( 'admin_init', 'child_test_register_settings' );

/**
 * Add the settings page under Appearance.
 */
function child_test_add_settings_page() {
	add_theme_page(
		__( 'Child Test Settings', 'child-test' ),
		__( 'Child Test Settings', 'child-test' ),
		'manage_options',
		'child-test-settings',
		'child_test_render_settings_page'
	);
}
add_action( 'admin_menu', 'child_test_add_settings_page' );

/**
 * Render the general settings section description.
 */
function child_test_render_general_section() {
	echo '<p>' . esc_html__( 'Configure theme options saved securely with nonces, sanitization, and escaping.', 'child-test' ) . '</p>';
}

/**
 * Render the footer text field.
 */
function child_test_render_footer_text_field() {
	$settings = child_test_get_settings();
	?>
	<textarea
		id="child_test_footer_text"
		name="child_test_settings[footer_text]"
		rows="4"
		class="large-text"
	><?php echo esc_textarea( $settings['footer_text'] ); ?></textarea>
	<p class="description">
		<?php esc_html_e( 'Optional text shown in the site footer when display is enabled.', 'child-test' ); ?>
	</p>
	<?php
}

/**
 * Render the contact email field.
 */
function child_test_render_contact_email_field() {
	$settings = child_test_get_settings();
	?>
	<input
		type="email"
		id="child_test_contact_email"
		name="child_test_settings[contact_email]"
		value="<?php echo esc_attr( $settings['contact_email'] ); ?>"
		class="regular-text"
	/>
	<?php
}

/**
 * Render the company URL field.
 */
function child_test_render_company_url_field() {
	$settings = child_test_get_settings();
	?>
	<input
		type="url"
		id="child_test_company_url"
		name="child_test_settings[company_url]"
		value="<?php echo esc_attr( $settings['company_url'] ); ?>"
		class="regular-text code"
		placeholder="https://example.com"
	/>
	<?php
}

/**
 * Render the show footer text checkbox.
 */
function child_test_render_show_footer_text_field() {
	$settings = child_test_get_settings();
	?>
	<label for="child_test_show_footer_text">
		<input
			type="checkbox"
			id="child_test_show_footer_text"
			name="child_test_settings[show_footer_text]"
			value="1"
			<?php checked( 1, (int) $settings['show_footer_text'] ); ?>
		/>
		<?php esc_html_e( 'Show footer text on the front end.', 'child-test' ); ?>
	</label>
	<?php
}

/**
 * Render the footer layout select field.
 */
function child_test_render_footer_layout_field() {
	$settings = child_test_get_settings();
	$layouts  = array(
		'compact' => __( 'Compact', 'child-test' ),
		'full'    => __( 'Full Width', 'child-test' ),
	);
	?>
	<select id="child_test_footer_layout" name="child_test_settings[footer_layout]">
		<?php foreach ( $layouts as $value => $label ) : ?>
			<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['footer_layout'], $value ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}

/**
 * Render the settings page markup.
 */
function child_test_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<?php if ( isset( $_GET['settings-updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Settings saved.', 'child-test' ); ?></p>
			</div>
		<?php endif; ?>

		<form action="options.php" method="post">
			<?php
			// Outputs nonce, action, and option_group fields for CSRF protection.
			settings_fields( 'child_test_settings_group' );
			do_settings_sections( 'child-test-settings' );
			submit_button( __( 'Save Settings', 'child-test' ) );
			?>
		</form>
	</div>
	<?php
}

/**
 * Output saved footer text on the front end when enabled.
 */
function child_test_output_footer_text() {
	$settings = child_test_get_settings();

	if ( empty( $settings['show_footer_text'] ) || '' === $settings['footer_text'] ) {
		return;
	}

	$layout_class = 'full' === $settings['footer_layout'] ? 'child-test-footer--full' : 'child-test-footer--compact';
	?>
	<div class="child-test-footer <?php echo esc_attr( $layout_class ); ?>">
		<p class="child-test-footer__text"><?php echo esc_html( $settings['footer_text'] ); ?></p>
		<?php if ( ! empty( $settings['contact_email'] ) ) : ?>
			<p class="child-test-footer__email">
				<a href="<?php echo esc_url( 'mailto:' . $settings['contact_email'] ); ?>">
					<?php echo esc_html( $settings['contact_email'] ); ?>
				</a>
			</p>
		<?php endif; ?>
		<?php if ( ! empty( $settings['company_url'] ) ) : ?>
			<p class="child-test-footer__url">
				<a href="<?php echo esc_url( $settings['company_url'] ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html( $settings['company_url'] ); ?>
				</a>
			</p>
		<?php endif; ?>
	</div>
	<?php
}
add_action( 'wp_footer', 'child_test_output_footer_text' );
