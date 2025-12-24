<?php
/**
 * User profile security fields template.
 *
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<h3><?php _e( 'Two-Factor Authentication', 'seo-simple' ); ?></h3>

<table class="form-table">
	<tr>
		<th scope="row">
			<label for="seo_simple_2fa_enabled"><?php _e( 'Enable 2FA', 'seo-simple' ); ?></label>
		</th>
		<td>
			<input type="checkbox" id="seo_simple_2fa_enabled" name="seo_simple_2fa_enabled" value="1" <?php checked( $two_fa_enabled ); ?> />
			<label for="seo_simple_2fa_enabled"><?php _e( 'Enable two-factor authentication for this user', 'seo-simple' ); ?></label>
			<p class="description"><?php _e( 'When enabled, this user will need to provide a two-factor authentication code when logging in.', 'seo-simple' ); ?></p>
		</td>
	</tr>
	
	<?php if ( $two_fa_enabled ) : ?>
		<tr>
			<th scope="row">
				<label for="seo_simple_2fa_secret"><?php _e( 'Secret Key', 'seo-simple' ); ?></label>
			</th>
			<td>
				<input type="text" id="seo_simple_2fa_secret" name="seo_simple_2fa_secret" value="<?php echo esc_attr( $two_fa_secret ); ?>" readonly class="regular-text" />
				<p class="description"><?php _e( 'This secret key is used to generate authentication codes. Keep it safe!', 'seo-simple' ); ?></p>
				
				<p>
					<button type="button" id="seo-simple-generate-2fa-secret" class="button">
						<?php _e( 'Generate New Secret', 'seo-simple' ); ?>
					</button>
				</p>
			</td>
		</tr>
		
		<tr>
			<th scope="row">
				<label><?php _e( 'QR Code', 'seo-simple' ); ?></label>
			</th>
			<td>
				<p><?php _e( 'Scan this QR code with your authenticator app:', 'seo-simple' ); ?></p>
				<div id="seo-simple-2fa-qr-code">
					<!-- QR code would be generated here in a real implementation -->
					<p><?php _e( 'QR code would be displayed here', 'seo-simple' ); ?></p>
				</div>
			</td>
		</tr>
	<?php endif; ?>
</table>

<?php if ( $two_fa_enabled ) : ?>
	<p class="description">
		<?php _e( 'To set up two-factor authentication:', 'seo-simple' ); ?>
	</p>
	<ol>
		<li><?php _e( 'Install an authenticator app on your mobile device (Google Authenticator, Authy, etc.)', 'seo-simple' ); ?></li>
		<li><?php _e( 'Scan the QR code or manually enter the secret key in the app', 'seo-simple' ); ?></li>
		<li><?php _e( 'Enter the 6-digit code from the app when logging in', 'seo-simple' ); ?></li>
	</ol>
<?php endif; ?>

<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#seo-simple-generate-2fa-secret').on('click', function(e) {
		e.preventDefault();
		
		if (confirm('<?php _e( 'Are you sure you want to generate a new secret key? This will invalidate the current secret.', 'seo-simple' ); ?>')) {
			// Add a hidden field to indicate that a new secret should be generated
			$('#seo_simple_2fa_secret').after('<input type="hidden" name="seo_simple_generate_2fa_secret" value="1" />');
			
			// Show a message
			$('#seo-simple-generate-2fa-secret').after('<p class="description"><?php _e( 'A new secret key will be generated when you save your profile.', 'seo-simple' ); ?></p>');
			
			// Disable the button
			$(this).prop('disabled', true);
		}
	});
});
</script>