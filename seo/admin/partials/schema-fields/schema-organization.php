<?php
/**
 * Organization Schema Fields Template
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials/schema-fields
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$organization_name = get_post_meta( $post->ID, '_seo_simple_organization_name', true );
$organization_description = get_post_meta( $post->ID, '_seo_simple_organization_description', true );
$organization_url = get_post_meta( $post->ID, '_seo_simple_organization_url', true );
$organization_logo = get_post_meta( $post->ID, '_seo_simple_organization_logo', true );
$organization_phone = get_post_meta( $post->ID, '_seo_simple_organization_phone', true );
$organization_email = get_post_meta( $post->ID, '_seo_simple_organization_email', true );
$organization_address = get_post_meta( $post->ID, '_seo_simple_organization_address', true );
$organization_city = get_post_meta( $post->ID, '_seo_simple_organization_city', true );
$organization_state = get_post_meta( $post->ID, '_seo_simple_organization_state', true );
$organization_postal = get_post_meta( $post->ID, '_seo_simple_organization_postal', true );
$organization_country = get_post_meta( $post->ID, '_seo_simple_organization_country', true );
$organization_facebook = get_post_meta( $post->ID, '_seo_simple_organization_facebook', true );
$organization_twitter = get_post_meta( $post->ID, '_seo_simple_organization_twitter', true );
$organization_instagram = get_post_meta( $post->ID, '_seo_simple_organization_instagram', true );
$organization_linkedin = get_post_meta( $post->ID, '_seo_simple_organization_linkedin', true );
$organization_youtube = get_post_meta( $post->ID, '_seo_simple_organization_youtube', true );
$organization_pinterest = get_post_meta( $post->ID, '_seo_simple_organization_pinterest', true );
?>

<div class="seo-simple-field-group">
	<h4><?php _e( 'Organization Information', 'seo-simple' ); ?></h4>
	
	<div class="seo-simple-field">
		<label for="_seo_simple_organization_name"><?php _e( 'Organization Name', 'seo-simple' ); ?> <span class="seo-simple-field-required">*</span></label>
		<input 
			type="text" 
			id="_seo_simple_organization_name" 
			name="_seo_simple_organization_name" 
			value="<?php echo esc_attr( $organization_name ); ?>" 
			class="widefat"
			placeholder="<?php esc_attr_e( 'Enter organization name', 'seo-simple' ); ?>">
		<p class="description"><?php _e( 'The official name of the organization.', 'seo-simple' ); ?></p>
	</div>
	
	<div class="seo-simple-field">
		<label for="_seo_simple_organization_description"><?php _e( 'Description', 'seo-simple' ); ?></label>
		<textarea 
			id="_seo_simple_organization_description" 
			name="_seo_simple_organization_description" 
			class="widefat seo-simple-textarea"
			rows="3"
			placeholder="<?php esc_attr_e( 'Enter organization description', 'seo-simple' ); ?>"><?php echo esc_textarea( $organization_description ); ?></textarea>
		<p class="description"><?php _e( 'A brief description of the organization.', 'seo-simple' ); ?></p>
	</div>
	
	<div class="seo-simple-field-row">
		<div class="seo-simple-field">
			<label for="_seo_simple_organization_url"><?php _e( 'Website URL', 'seo-simple' ); ?></label>
			<input 
				type="url" 
				id="_seo_simple_organization_url" 
				name="_seo_simple_organization_url" 
				value="<?php echo esc_attr( $organization_url ); ?>" 
				class="widefat seo-simple-url-input"
				placeholder="https://example.com">
			<p class="description"><?php _e( 'The official website URL of the organization.', 'seo-simple' ); ?></p>
		</div>
		
		<div class="seo-simple-field">
			<label for="_seo_simple_organization_logo"><?php _e( 'Logo URL', 'seo-simple' ); ?></label>
			<div class="seo-simple-field-row">
				<input 
					type="url" 
					id="_seo_simple_organization_logo" 
					name="_seo_simple_organization_logo" 
					value="<?php echo esc_attr( $organization_logo ); ?>" 
					class="widefat seo-simple-url-input"
					placeholder="https://example.com/logo.png">
				<button type="button" class="button seo-simple-media-uploader" data-target="_seo_simple_organization_logo" data-title="<?php esc_attr_e( 'Select Logo', 'seo-simple' ); ?>">
					<?php _e( 'Upload', 'seo-simple' ); ?>
				</button>
			</div>
			<?php if ( ! empty( $organization_logo ) ) : ?>
				<img id="_seo_simple_organization_logo-preview" src="<?php echo esc_url( $organization_logo ); ?>" class="seo-simple-media-preview" alt="<?php esc_attr_e( 'Logo Preview', 'seo-simple' ); ?>">
			<?php else : ?>
				<img id="_seo_simple_organization_logo-preview" src="" class="seo-simple-media-preview" style="display: none;" alt="<?php esc_attr_e( 'Logo Preview', 'seo-simple' ); ?>">
			<?php endif; ?>
			<p class="description"><?php _e( 'The URL of the organization logo.', 'seo-simple' ); ?></p>
		</div>
	</div>
</div>

<div class="seo-simple-field-group">
	<h4><?php _e( 'Contact Information', 'seo-simple' ); ?></h4>
	
	<div class="seo-simple-field-row">
		<div class="seo-simple-field">
			<label for="_seo_simple_organization_phone"><?php _e( 'Phone Number', 'seo-simple' ); ?></label>
			<input 
				type="tel" 
				id="_seo_simple_organization_phone" 
				name="_seo_simple_organization_phone" 
				value="<?php echo esc_attr( $organization_phone ); ?>" 
				class="widefat"
				placeholder="+1 (555) 123-4567">
			<p class="description"><?php _e( 'The main phone number of the organization.', 'seo-simple' ); ?></p>
		</div>
		
		<div class="seo-simple-field">
			<label for="_seo_simple_organization_email"><?php _e( 'Email Address', 'seo-simple' ); ?></label>
			<input 
				type="email" 
				id="_seo_simple_organization_email" 
				name="_seo_simple_organization_email" 
				value="<?php echo esc_attr( $organization_email ); ?>" 
				class="widefat"
				placeholder="contact@example.com">
			<p class="description"><?php _e( 'The main email address of the organization.', 'seo-simple' ); ?></p>
		</div>
	</div>
</div>

<div class="seo-simple-field-group">
	<h4><?php _e( 'Address Information', 'seo-simple' ); ?></h4>
	
	<div class="seo-simple-field">
		<label for="_seo_simple_organization_address"><?php _e( 'Street Address', 'seo-simple' ); ?></label>
		<input 
			type="text" 
			id="_seo_simple_organization_address" 
			name="_seo_simple_organization_address" 
			value="<?php echo esc_attr( $organization_address ); ?>" 
			class="widefat"
			placeholder="123 Main Street">
		<p class="description"><?php _e( 'The street address of the organization.', 'seo-simple' ); ?></p>
	</div>
	
	<div class="seo-simple-field-row">
		<div class="seo-simple-field">
			<label for="_seo_simple_organization_city"><?php _e( 'City', 'seo-simple' ); ?></label>
			<input 
				type="text" 
				id="_seo_simple_organization_city" 
				name="_seo_simple_organization_city" 
				value="<?php echo esc_attr( $organization_city ); ?>" 
				class="widefat"
				placeholder="New York">
		</div>
		
		<div class="seo-simple-field">
			<label for="_seo_simple_organization_state"><?php _e( 'State/Province', 'seo-simple' ); ?></label>
			<input 
				type="text" 
				id="_seo_simple_organization_state" 
				name="_seo_simple_organization_state" 
				value="<?php echo esc_attr( $organization_state ); ?>" 
				class="widefat"
				placeholder="NY">
		</div>
	</div>
	
	<div class="seo-simple-field-row">
		<div class="seo-simple-field">
			<label for="_seo_simple_organization_postal"><?php _e( 'Postal/ZIP Code', 'seo-simple' ); ?></label>
			<input 
				type="text" 
				id="_seo_simple_organization_postal" 
				name="_seo_simple_organization_postal" 
				value="<?php echo esc_attr( $organization_postal ); ?>" 
				class="widefat"
				placeholder="10001">
		</div>
		
		<div class="seo-simple-field">
			<label for="_seo_simple_organization_country"><?php _e( 'Country', 'seo-simple' ); ?></label>
			<input 
				type="text" 
				id="_seo_simple_organization_country" 
				name="_seo_simple_organization_country" 
				value="<?php echo esc_attr( $organization_country ); ?>" 
				class="widefat"
				placeholder="United States">
		</div>
	</div>
</div>

<div class="seo-simple-field-group">
	<h4><?php _e( 'Social Media Profiles', 'seo-simple' ); ?></h4>
	
	<div class="seo-simple-field-row">
		<div class="seo-simple-field">
			<label for="_seo_simple_organization_facebook"><?php _e( 'Facebook', 'seo-simple' ); ?></label>
			<input 
				type="url" 
				id="_seo_simple_organization_facebook" 
				name="_seo_simple_organization_facebook" 
				value="<?php echo esc_attr( $organization_facebook ); ?>" 
				class="widefat seo-simple-url-input"
				placeholder="https://facebook.com/yourpage">
		</div>
		
		<div class="seo-simple-field">
			<label for="_seo_simple_organization_twitter"><?php _e( 'Twitter', 'seo-simple' ); ?></label>
			<input 
				type="url" 
				id="_seo_simple_organization_twitter" 
				name="_seo_simple_organization_twitter" 
				value="<?php echo esc_attr( $organization_twitter ); ?>" 
				class="widefat seo-simple-url-input"
				placeholder="https://twitter.com/yourhandle">
		</div>
	</div>
	
	<div class="seo-simple-field-row">
		<div class="seo-simple-field">
			<label for="_seo_simple_organization_instagram"><?php _e( 'Instagram', 'seo-simple' ); ?></label>
			<input 
				type="url" 
				id="_seo_simple_organization_instagram" 
				name="_seo_simple_organization_instagram" 
				value="<?php echo esc_attr( $organization_instagram ); ?>" 
				class="widefat seo-simple-url-input"
				placeholder="https://instagram.com/yourhandle">
		</div>
		
		<div class="seo-simple-field">
			<label for="_seo_simple_organization_linkedin"><?php _e( 'LinkedIn', 'seo-simple' ); ?></label>
			<input 
				type="url" 
				id="_seo_simple_organization_linkedin" 
				name="_seo_simple_organization_linkedin" 
				value="<?php echo esc_attr( $organization_linkedin ); ?>" 
				class="widefat seo-simple-url-input"
				placeholder="https://linkedin.com/company/yourcompany">
		</div>
	</div>
	
	<div class="seo-simple-field-row">
		<div class="seo-simple-field">
			<label for="_seo_simple_organization_youtube"><?php _e( 'YouTube', 'seo-simple' ); ?></label>
			<input 
				type="url" 
				id="_seo_simple_organization_youtube" 
				name="_seo_simple_organization_youtube" 
				value="<?php echo esc_attr( $organization_youtube ); ?>" 
				class="widefat seo-simple-url-input"
				placeholder="https://youtube.com/channel/yourchannel">
		</div>
		
		<div class="seo-simple-field">
			<label for="_seo_simple_organization_pinterest"><?php _e( 'Pinterest', 'seo-simple' ); ?></label>
			<input 
				type="url" 
				id="_seo_simple_organization_pinterest" 
				name="_seo_simple_organization_pinterest" 
				value="<?php echo esc_attr( $organization_pinterest ); ?>" 
				class="widefat seo-simple-url-input"
				placeholder="https://pinterest.com/yourhandle">
		</div>
	</div>
</div>