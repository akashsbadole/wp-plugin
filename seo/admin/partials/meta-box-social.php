<?php
/**
 * Social Media Meta Box Template
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$meta_prefix = $this->get_meta_key_prefix();
$social_title = $this->seo_meta->get_social_title( $post->ID );
$social_description = $this->seo_meta->get_social_description( $post->ID );
$social_image = $this->seo_meta->get_social_image( $post->ID );
$social_image_id = $this->seo_meta->get_social_image_id( $post->ID );
$permalink = $this->get_permalink( $post->ID );
$use_seo_data = $this->seo_meta->get_use_seo_data( $post->ID );

// Get fallback values
$fallback_title = $this->get_title_preview( $post->ID );
$fallback_description = $this->get_description_preview( $post->ID );
?>

<div class="seo-simple-meta-box social-tab" id="seo-simple-social-tab">
	<div class="seo-simple-field">
		<label>
			<input type="checkbox" 
				   id="<?php echo esc_attr( $meta_prefix ); ?>use_seo_data" 
				   name="<?php echo esc_attr( $meta_prefix ); ?>use_seo_data" 
				   value="1" 
				   <?php checked( $use_seo_data, 1 ); ?> />
			<?php _e( 'Use SEO title and description for social media', 'seo-simple' ); ?>
		</label>
		<p class="description"><?php _e( 'Check this to use your SEO title and description as defaults for social media sharing.', 'seo-simple' ); ?></p>
	</div>

	<div class="seo-simple-field">
		<label for="<?php echo esc_attr( $meta_prefix ); ?>social_title"><?php _e( 'Social Title', 'seo-simple' ); ?></label>
		<input type="text" 
			   id="<?php echo esc_attr( $meta_prefix ); ?>social_title" 
			   name="<?php echo esc_attr( $meta_prefix ); ?>social_title" 
			   value="<?php echo esc_attr( $social_title ); ?>" 
			   class="widefat seo-simple-social-title" 
			   maxlength="100" 
			   placeholder="<?php echo esc_attr( $fallback_title ); ?>" />
		<p class="description">
			<?php _e( 'Recommended length: 100 characters. Current:', 'seo-simple' ); ?> 
			<span class="seo-simple-social-title-count"><?php echo strlen( $social_title ?: $fallback_title ); ?></span>/100
		</p>
	</div>

	<div class="seo-simple-field">
		<label for="<?php echo esc_attr( $meta_prefix ); ?>social_description"><?php _e( 'Social Description', 'seo-simple' ); ?></label>
		<textarea 
			   id="<?php echo esc_attr( $meta_prefix ); ?>social_description" 
			   name="<?php echo esc_attr( $meta_prefix ); ?>social_description" 
			   class="widefat seo-simple-social-description" 
			   rows="3" 
			   maxlength="200"
			   placeholder="<?php echo esc_attr( $fallback_description ); ?>"><?php echo esc_textarea( $social_description ); ?></textarea>
		<p class="description">
			<?php _e( 'Recommended length: 200 characters. Current:', 'seo-simple' ); ?> 
			<span class="seo-simple-social-description-count"><?php echo strlen( $social_description ?: $fallback_description ); ?></span>/200
		</p>
	</div>

	<div class="seo-simple-field">
		<label for="<?php echo esc_attr( $meta_prefix ); ?>social_image"><?php _e( 'Social Image', 'seo-simple' ); ?></label>
		<div class="seo-simple-image-upload-container">
			<div class="seo-simple-image-preview" id="seo-simple-social-image-preview">
				<?php if ( $social_image_id ) : ?>
					<?php 
					$image_src = wp_get_attachment_image_src( $social_image_id, 'medium' );
					if ( $image_src ) : 
					?>
						<img src="<?php echo esc_url( $image_src[0] ); ?>" alt="<?php _e( 'Social Image Preview', 'seo-simple' ); ?>" />
						<div class="seo-simple-image-actions">
							<button type="button" class="button seo-simple-remove-image" id="seo-simple-remove-social-image">
								<?php _e( 'Remove Image', 'seo-simple' ); ?>
							</button>
						</div>
					<?php endif; ?>
				<?php else : ?>
					<div class="seo-simple-no-image">
						<p><?php _e( 'No image selected', 'seo-simple' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
			<div class="seo-simple-image-upload-actions">
				<input type="hidden" 
					   id="<?php echo esc_attr( $meta_prefix ); ?>social_image_id" 
					   name="<?php echo esc_attr( $meta_prefix ); ?>social_image_id" 
					   value="<?php echo esc_attr( $social_image_id ); ?>" />
				<input type="hidden" 
					   id="<?php echo esc_attr( $meta_prefix ); ?>social_image" 
					   name="<?php echo esc_attr( $meta_prefix ); ?>social_image" 
					   value="<?php echo esc_attr( $social_image ); ?>" />
				<button type="button" class="button seo-simple-upload-image" id="seo-simple-upload-social-image">
					<?php _e( 'Choose Image', 'seo-simple' ); ?>
				</button>
				<p class="description"><?php _e( 'Recommended size: 1200x630 pixels. Minimum size: 600x315 pixels.', 'seo-simple' ); ?></p>
			</div>
		</div>
	</div>

	<div class="seo-simple-social-preview">
		<h3><?php _e( 'Social Media Preview', 'seo-simple' ); ?></h3>
		
		<!-- Facebook Preview -->
		<div class="seo-simple-facebook-preview">
			<h4><?php _e( 'Facebook Preview', 'seo-simple' ); ?></h4>
			<div class="seo-simple-facebook-card">
				<div class="seo-simple-facebook-image" id="seo-simple-facebook-image">
					<?php if ( $social_image_id ) : ?>
						<?php 
						$image_src = wp_get_attachment_image_src( $social_image_id, 'medium' );
						if ( $image_src ) : 
						?>
							<img src="<?php echo esc_url( $image_src[0] ); ?>" alt="" />
						<?php endif; ?>
					<?php else : ?>
						<div class="seo-simple-facebook-no-image"></div>
					<?php endif; ?>
				</div>
				<div class="seo-simple-facebook-content">
					<div class="seo-simple-facebook-title" id="seo-simple-facebook-title">
						<?php echo esc_html( $social_title ?: $fallback_title ); ?>
					</div>
					<div class="seo-simple-facebook-description" id="seo-simple-facebook-description">
						<?php echo esc_html( $social_description ?: $fallback_description ); ?>
					</div>
					<div class="seo-simple-facebook-url" id="seo-simple-facebook-url">
						<?php echo esc_html( parse_url( $permalink, PHP_URL_HOST ) ); ?>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Twitter Preview -->
		<div class="seo-simple-twitter-preview">
			<h4><?php _e( 'Twitter Preview', 'seo-simple' ); ?></h4>
			<div class="seo-simple-twitter-card">
				<div class="seo-simple-twitter-image" id="seo-simple-twitter-image">
					<?php if ( $social_image_id ) : ?>
						<?php 
						$image_src = wp_get_attachment_image_src( $social_image_id, 'medium' );
						if ( $image_src ) : 
						?>
							<img src="<?php echo esc_url( $image_src[0] ); ?>" alt="" />
						<?php endif; ?>
					<?php else : ?>
						<div class="seo-simple-twitter-no-image"></div>
					<?php endif; ?>
				</div>
				<div class="seo-simple-twitter-content">
					<div class="seo-simple-twitter-title" id="seo-simple-twitter-title">
						<?php echo esc_html( $social_title ?: $fallback_title ); ?>
					</div>
					<div class="seo-simple-twitter-description" id="seo-simple-twitter-description">
						<?php echo esc_html( $social_description ?: $fallback_description ); ?>
					</div>
					<div class="seo-simple-twitter-url" id="seo-simple-twitter-url">
						<?php echo esc_html( $permalink ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
.seo-simple-image-upload-container {
	display: flex;
	flex-wrap: wrap;
	gap: 15px;
}
.seo-simple-image-preview {
	width: 150px;
}
.seo-simple-image-preview img {
	max-width: 100%;
	height: auto;
	border: 1px solid #ddd;
}
.seo-simple-image-actions {
	margin-top: 5px;
}
.seo-simple-no-image {
	border: 1px dashed #ddd;
	height: 150px;
	display: flex;
	align-items: center;
	justify-content: center;
	text-align: center;
	color: #666;
}
.seo-simple-image-upload-actions {
	flex: 1;
}
.seo-simple-social-preview {
	margin-top: 20px;
	border-top: 1px solid #ddd;
	padding-top: 15px;
}
.seo-simple-facebook-preview, .seo-simple-twitter-preview {
	margin-bottom: 20px;
}
.seo-simple-facebook-preview h4, .seo-simple-twitter-preview h4 {
	margin-bottom: 10px;
	font-size: 14px;
	color: #23282d;
}
.seo-simple-facebook-card {
	border: 1px solid #ddd;
	border-radius: 8px;
	overflow: hidden;
	background: #fff;
	max-width: 500px;
}
.seo-simple-facebook-image {
	min-height: 262px;
	background-color: #f2f3f5;
	position: relative;
}
.seo-simple-facebook-image img {
	width: 100%;
	height: auto;
	display: block;
}
.seo-simple-facebook-no-image {
	width: 100%;
	height: 262px;
	background-color: #f2f3f5;
	display: flex;
	align-items: center;
	justify-content: center;
	color: #8a8a8a;
}
.seo-simple-facebook-content {
	padding: 12px;
}
.seo-simple-facebook-title {
	font-weight: 600;
	font-size: 16px;
	line-height: 20px;
	margin-bottom: 4px;
	color: #1c1e21;
}
.seo-simple-facebook-description {
	font-size: 15px;
	line-height: 20px;
	color: #606770;
	margin-bottom: 8px;
	overflow: hidden;
	text-overflow: ellipsis;
	display: -webkit-box;
	-webkit-line-clamp: 3;
	-webkit-box-orient: vertical;
}
.seo-simple-facebook-url {
	font-size: 13px;
	line-height: 16px;
	color: #606770;
	text-transform: uppercase;
}
.seo-simple-twitter-card {
	border: 1px solid #e1e8ed;
	border-radius: 8px;
	overflow: hidden;
	background: #fff;
	max-width: 500px;
}
.seo-simple-twitter-image {
	min-height: 262px;
	background-color: #f2f3f5;
	position: relative;
}
.seo-simple-twitter-image img {
	width: 100%;
	height: auto;
	display: block;
}
.seo-simple-twitter-no-image {
	width: 100%;
	height: 262px;
	background-color: #f2f3f5;
	display: flex;
	align-items: center;
	justify-content: center;
	color: #8a8a8a;
}
.seo-simple-twitter-content {
	padding: 12px;
}
.seo-simple-twitter-title {
	font-weight: bold;
	font-size: 16px;
	line-height: 20px;
	margin-bottom: 4px;
	color: #0f1419;
}
.seo-simple-twitter-description {
	font-size: 15px;
	line-height: 20px;
	color: #536471;
	margin-bottom: 8px;
	overflow: hidden;
	text-overflow: ellipsis;
	display: -webkit-box;
	-webkit-line-clamp: 3;
	-webkit-box-orient: vertical;
}
.seo-simple-twitter-url {
	font-size: 13px;
	line-height: 16px;
	color: #536471;
}
</style>
