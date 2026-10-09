<?php
/**
 * Lightweight contact form with honeypot and rate limiting.
 *
 * @package Taabeer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function taabeer_contact_form_shortcode( $atts = array() ) {
	$extra = shortcode_atts(array('recipient_email'=>'','consent_label'=>'I agree that TAABEER may use my details to respond to this enquiry.','privacy_label'=>'Privacy Policy','error_message'=>'Please review the form and try again. All fields are required and the email address must be valid.','limited_message'=>'Please wait a few minutes before sending another message.'),(array)$atts);
	$labels = shortcode_atts( array( 'name_label'=>'Name', 'email_label'=>'Email', 'enquiry_label'=>'Enquiry type', 'message_label'=>'Message', 'submit_label'=>'Send message', 'success_message'=>'Thank you for contacting TAABEER. Your message has been received.', 'general_label'=>'General enquiry', 'collection_label'=>'Collection enquiry', 'partnership_label'=>'Partnership enquiry', 'press_label'=>'Press enquiry' ), (array) $atts );
	$status       = isset( $_GET['contact_status'] ) ? sanitize_key( wp_unslash( $_GET['contact_status'] ) ) : '';
	$enquiry      = isset( $_GET['enquiry'] ) ? sanitize_key( wp_unslash( $_GET['enquiry'] ) ) : '';
	$enquiry_map  = array(
		'general'     => $labels['general_label'],
		'collection'  => $labels['collection_label'],
		'partnership' => $labels['partnership_label'],
		'press'       => $labels['press_label'],
	);
	if ( ! isset( $enquiry_map[ $enquiry ] ) ) {
		$enquiry = 'general';
	}

	ob_start();
	?>
	<div class="contact-form-wrap">
		<?php if ( 'success' === $status ) : ?>
			<div class="form-status form-status--success" role="status" tabindex="-1" data-form-status><?php echo esc_html( $labels['success_message'] ); ?></div>
		<?php elseif ( 'error' === $status ) : ?>
			<div class="form-status form-status--error" role="alert" tabindex="-1" data-form-status><?php echo esc_html($extra['error_message']); ?></div>
		<?php elseif ( 'limited' === $status ) : ?>
			<div class="form-status form-status--error" role="alert" tabindex="-1" data-form-status><?php echo esc_html($extra['limited_message']); ?></div>
		<?php endif; ?>

		<form class="contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="taabeer_contact">
			<?php wp_nonce_field( 'taabeer_contact_submit', 'taabeer_contact_nonce' ); ?>
			<?php $recipient=sanitize_email($extra['recipient_email']); if(is_email($recipient)): ?>
			<input type="hidden" name="recipient" value="<?php echo esc_attr($recipient); ?>"><input type="hidden" name="recipient_signature" value="<?php echo esc_attr(hash_hmac('sha256',$recipient,wp_salt('auth'))); ?>">
			<?php endif; ?>
			<div class="contact-form__honeypot" aria-hidden="true">
				<label for="taabeer_website"><?php esc_html_e( 'Website', 'taabeer' ); ?></label>
				<input id="taabeer_website" name="website" type="text" tabindex="-1" autocomplete="off">
			</div>
			<div class="form-field">
				<label for="taabeer_name"><?php echo esc_html( $labels['name_label'] ); ?></label>
				<input id="taabeer_name" name="name" type="text" autocomplete="name" required>
			</div>
			<div class="form-field">
				<label for="taabeer_email"><?php echo esc_html( $labels['email_label'] ); ?></label>
				<input id="taabeer_email" name="email" type="email" autocomplete="email" required>
			</div>
			<div class="form-field">
				<label for="taabeer_enquiry_type"><?php echo esc_html( $labels['enquiry_label'] ); ?></label>
				<select id="taabeer_enquiry_type" name="enquiry_type" required>
					<?php foreach ( $enquiry_map as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $enquiry, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-field form-field--full">
				<label for="taabeer_message"><?php echo esc_html( $labels['message_label'] ); ?></label>
				<textarea id="taabeer_message" name="message" rows="7" required></textarea>
			</div>
			<div class="form-field form-field--full">
				<label class="form-consent"><input type="checkbox" name="privacy_agree" value="1" required> <span><?php echo esc_html($extra['consent_label']); ?> <a href="<?php echo esc_url(get_privacy_policy_url() ?: home_url('/privacy-policy/')); ?>"><?php echo esc_html($extra['privacy_label']); ?></a></span></label>
			</div>
			<button class="button" type="submit"><?php echo esc_html( $labels['submit_label'] ); ?></button>
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
	$custom_recipient=isset($_POST['recipient'])?sanitize_email(wp_unslash($_POST['recipient'])):'';
	$signature=isset($_POST['recipient_signature'])?sanitize_text_field(wp_unslash($_POST['recipient_signature'])):'';
	if(is_email($custom_recipient) && hash_equals(hash_hmac('sha256',$custom_recipient,wp_salt('auth')),$signature)) {$recipient=$custom_recipient;}
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

