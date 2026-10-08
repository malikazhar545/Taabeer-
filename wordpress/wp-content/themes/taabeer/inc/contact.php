<?php
/**
 * Lightweight contact form with honeypot and rate limiting.
 *
 * @package Taabeer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function taabeer_contact_form_shortcode() {
	$status       = isset( $_GET['contact_status'] ) ? sanitize_key( wp_unslash( $_GET['contact_status'] ) ) : '';
	$enquiry      = isset( $_GET['enquiry'] ) ? sanitize_key( wp_unslash( $_GET['enquiry'] ) ) : '';
	$enquiry_map  = array(
		'general'     => __( 'General enquiry', 'taabeer' ),
		'collection'  => __( 'Collection enquiry', 'taabeer' ),
		'partnership' => __( 'Partnership enquiry', 'taabeer' ),
		'press'       => __( 'Press enquiry', 'taabeer' ),
	);
	if ( ! isset( $enquiry_map[ $enquiry ] ) ) {
		$enquiry = 'general';
	}

	ob_start();
	?>
	<div class="contact-form-wrap">
		<?php if ( 'success' === $status ) : ?>
			<div class="form-status form-status--success" role="status" tabindex="-1" data-form-status><?php esc_html_e( 'Thank you for contacting TAABEER. Your message has been received.', 'taabeer' ); ?></div>
		<?php elseif ( 'error' === $status ) : ?>
			<div class="form-status form-status--error" role="alert" tabindex="-1" data-form-status><?php esc_html_e( 'Please review the form and try again. All fields are required and the email address must be valid.', 'taabeer' ); ?></div>
		<?php elseif ( 'limited' === $status ) : ?>
			<div class="form-status form-status--error" role="alert" tabindex="-1" data-form-status><?php esc_html_e( 'Please wait a few minutes before sending another message.', 'taabeer' ); ?></div>
		<?php endif; ?>

		<form class="contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="taabeer_contact">
			<?php wp_nonce_field( 'taabeer_contact_submit', 'taabeer_contact_nonce' ); ?>
			<div class="contact-form__honeypot" aria-hidden="true">
				<label for="taabeer_website"><?php esc_html_e( 'Website', 'taabeer' ); ?></label>
				<input id="taabeer_website" name="website" type="text" tabindex="-1" autocomplete="off">
			</div>
			<div class="form-field">
				<label for="taabeer_name"><?php esc_html_e( 'Name', 'taabeer' ); ?></label>
				<input id="taabeer_name" name="name" type="text" autocomplete="name" required>
			</div>
			<div class="form-field">
				<label for="taabeer_email"><?php esc_html_e( 'Email', 'taabeer' ); ?></label>
				<input id="taabeer_email" name="email" type="email" autocomplete="email" required>
			</div>
			<div class="form-field">
				<label for="taabeer_enquiry_type"><?php esc_html_e( 'Enquiry type', 'taabeer' ); ?></label>
				<select id="taabeer_enquiry_type" name="enquiry_type" required>
					<?php foreach ( $enquiry_map as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $enquiry, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-field form-field--full">
				<label for="taabeer_message"><?php esc_html_e( 'Message', 'taabeer' ); ?></label>
				<textarea id="taabeer_message" name="message" rows="7" required></textarea>
			</div>
			<div class="form-field form-field--full">
				<label class="form-consent"><input type="checkbox" name="privacy_agree" value="1" required> <span><?php printf( wp_kses_post( __( 'I agree that TAABEER may use my details to respond to this enquiry. Read the <a href="%s">Privacy Policy</a>.', 'taabeer' ) ), esc_url( get_privacy_policy_url() ?: home_url( '/privacy-policy/' ) ) ); ?></span></label>
			</div>
			<button class="button" type="submit"><?php esc_html_e( 'Send message', 'taabeer' ); ?></button>
		</form>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'taabeer_contact_form', 'taabeer_contact_form_shortcode' );

function taabeer_handle_contact_form() {
	$redirect = wp_get_referer() ?: home_url( '/contact/' );
	if ( ! isset( $_POST['taabeer_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['taabeer_contact_nonce'] ) ), 'taabeer_contact_submit' ) ) {
		wp_safe_redirect( add_query_arg( 'contact_status', 'error', $redirect ) );
		exit;
	}
	if ( ! empty( $_POST['website'] ) ) {
		wp_safe_redirect( add_query_arg( 'contact_status', 'success', $redirect ) );
		exit;
	}

	$remote_address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$rate_key       = 'taabeer_contact_' . hash( 'sha256', $remote_address . wp_salt( 'nonce' ) );
	if ( get_transient( $rate_key ) ) {
		wp_safe_redirect( add_query_arg( 'contact_status', 'limited', $redirect ) );
		exit;
	}

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$type    = isset( $_POST['enquiry_type'] ) ? sanitize_key( wp_unslash( $_POST['enquiry_type'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$privacy = ! empty( $_POST['privacy_agree'] );
	$types   = array( 'general', 'collection', 'partnership', 'press' );

	if ( ! $name || ! is_email( $email ) || ! in_array( $type, $types, true ) || ! $message || ! $privacy ) {
		wp_safe_redirect( add_query_arg( 'contact_status', 'error', $redirect ) );
		exit;
	}

	$recipient = get_theme_mod( 'taabeer_public_email', get_option( 'admin_email' ) );
	$subject   = sprintf( '[TAABEER] %s enquiry from %s', ucfirst( $type ), $name );
	$body      = "Name: {$name}\nEmail: {$email}\nEnquiry: {$type}\n\n{$message}";
	$headers   = array( 'Reply-To: ' . $name . ' <' . $email . '>' );
	$sent      = wp_mail( $recipient, $subject, $body, $headers );

	if ( $sent ) {
		set_transient( $rate_key, 1, 5 * MINUTE_IN_SECONDS );
	}
	wp_safe_redirect( add_query_arg( 'contact_status', $sent ? 'success' : 'error', $redirect ) );
	exit;
}
add_action( 'admin_post_nopriv_taabeer_contact', 'taabeer_handle_contact_form' );
add_action( 'admin_post_taabeer_contact', 'taabeer_handle_contact_form' );

