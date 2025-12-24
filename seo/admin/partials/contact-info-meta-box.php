<?php
/**
 * Contact information meta box template
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="seo-contact-info">
	<div class="form-field">
		<label for="seo_phone"><?php esc_html_e( 'Phone Number', 'seo-simple' ); ?></label>
		<input type="tel" id="seo_phone" name="seo_phone" value="<?php echo esc_attr( $phone ); ?>" class="widefat" />
		<p class="description"><?php esc_html_e( 'Enter the business phone number.', 'seo-simple' ); ?></p>
	</div>

	<div class="form-field">
		<label for="seo_email"><?php esc_html_e( 'Email Address', 'seo-simple' ); ?></label>
		<input type="email" id="seo_email" name="seo_email" value="<?php echo esc_attr( $email ); ?>" class="widefat" />
		<p class="description"><?php esc_html_e( 'Enter the business email address.', 'seo-simple' ); ?></p>
	</div>

	<div class="form-field">
		<label for="seo_website"><?php esc_html_e( 'Website URL', 'seo-simple' ); ?></label>
		<input type="url" id="seo_website" name="seo_website" value="<?php echo esc_attr( $website ); ?>" class="widefat" />
		<p class="description"><?php esc_html_e( 'Enter the business website URL.', 'seo-simple' ); ?></p>
	</div>

	<div class="form-field">
		<label for="seo_fax"><?php esc_html_e( 'Fax Number', 'seo-simple' ); ?></label>
		<input type="tel" id="seo_fax" name="seo_fax" value="<?php echo esc_attr( $fax ); ?>" class="widefat" />
		<p class="description"><?php esc_html_e( 'Enter the business fax number (optional).', 'seo-simple' ); ?></p>
	</div>
</div>