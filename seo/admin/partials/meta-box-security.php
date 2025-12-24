<?php
/**
 * Security meta box template.
 *
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="seo-simple-security-meta-box">
	<p><strong><?php _e( 'Visitor IP:', 'seo-simple' ); ?></strong> <?php echo esc_html( $visitor_ip ); ?></p>
	
	<p>
		<button type="button" class="button seo-simple-block-ip" data-ip="<?php echo esc_attr( $visitor_ip ); ?>">
			<?php _e( 'Block IP', 'seo-simple' ); ?>
		</button>
	</p>
	
	<hr />
	
	<p><strong><?php _e( 'Post Security:', 'seo-simple' ); ?></strong></p>
	
	<p>
		<label>
			<input type="checkbox" name="seo_simple_security_protected" value="1" <?php checked( isset( $security_data['protected'] ) ? $security_data['protected'] : false ); ?> />
			<?php _e( 'Protect this post', 'seo-simple' ); ?>
		</label>
	</p>
	
	<p class="description"><?php _e( 'Enable additional security measures for this post.', 'seo-simple' ); ?></p>
</div>