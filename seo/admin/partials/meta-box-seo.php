<?php
/**
 * SEO Meta Box Template
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$meta_prefix = $this->get_meta_key_prefix();
$meta_data = $this->seo_meta->get_all_meta( $post->ID );
$title_preview = $this->get_title_preview( $post->ID );
$description_preview = $this->get_description_preview( $post->ID );
$permalink = $this->get_permalink( $post->ID );
$focus_keyword = $this->seo_meta->get_focus_keyword( $post->ID );
$seo_analysis = $this->seo_meta->get_seo_analysis( $post->ID );
$canonical_url = $this->seo_meta->get_canonical_url( $post->ID );
?>

<div class="seo-simple-meta-box">
	<!-- Hidden element to store site name for JavaScript -->
	<div id="seo-simple-site-name" data-site-name="<?php echo esc_attr(get_bloginfo('name')); ?>" style="display: none;"></div>
	
	<div class="seo-simple-field">
		<label for="<?php echo esc_attr( $meta_prefix ); ?>focus_keyword"><?php _e( 'Focus Keyword', 'seo-simple' ); ?></label>
		<input type="text" 
			   id="<?php echo esc_attr( $meta_prefix ); ?>focus_keyword" 
			   name="<?php echo esc_attr( $meta_prefix ); ?>focus_keyword" 
			   value="<?php echo esc_attr( $focus_keyword ); ?>" 
			   class="widefat seo-simple-focus-keyword" />
		<p class="description"><?php _e( 'Enter the main keyword or phrase you want to optimize this content for.', 'seo-simple' ); ?></p>
	</div>

	<div class="seo-simple-field">
		<label for="<?php echo esc_attr( $meta_prefix ); ?>title"><?php _e( 'SEO Title', 'seo-simple' ); ?></label>
		<input type="text" 
			   id="<?php echo esc_attr( $meta_prefix ); ?>title" 
			   name="<?php echo esc_attr( $meta_prefix ); ?>title" 
			   value="<?php echo esc_attr( $meta_data['title'] ); ?>" 
			   class="widefat seo-simple-title" 
			   maxlength="60" />
		<p class="description">
			<?php _e( 'Recommended length: 60 characters. Current:', 'seo-simple' ); ?> 
			<span class="seo-simple-title-count"><?php echo strlen( $title_preview ); ?></span>/60
		</p>
	</div>

	<div class="seo-simple-field">
		<label for="<?php echo esc_attr( $meta_prefix ); ?>description"><?php _e( 'Meta Description', 'seo-simple' ); ?></label>
		<textarea 
			   id="<?php echo esc_attr( $meta_prefix ); ?>description" 
			   name="<?php echo esc_attr( $meta_prefix ); ?>description" 
			   class="widefat seo-simple-description" 
			   rows="3" 
			   maxlength="160"><?php echo esc_textarea( $meta_data['description'] ); ?></textarea>
		<p class="description">
			<?php _e( 'Recommended length: 160 characters. Current:', 'seo-simple' ); ?> 
			<span class="seo-simple-description-count"><?php echo strlen( $description_preview ); ?></span>/160
		</p>
	</div>

	<div class="seo-simple-field">
		<label for="<?php echo esc_attr( $meta_prefix ); ?>keywords"><?php _e( 'Meta Keywords', 'seo-simple' ); ?></label>
		<input type="text" 
			   id="<?php echo esc_attr( $meta_prefix ); ?>keywords" 
			   name="<?php echo esc_attr( $meta_prefix ); ?>keywords" 
			   value="<?php echo esc_attr( $meta_data['keywords'] ); ?>" 
			   class="widefat" />
		<p class="description"><?php _e( 'Comma separated list of keywords', 'seo-simple' ); ?></p>
	</div>

	<div class="seo-simple-field">
		<label for="<?php echo esc_attr( $meta_prefix ); ?>canonical"><?php _e( 'Canonical URL', 'seo-simple' ); ?></label>
		<input type="text"
			   id="<?php echo esc_attr( $meta_prefix ); ?>canonical"
			   name="<?php echo esc_attr( $meta_prefix ); ?>canonical"
			   value="<?php echo esc_url( $canonical_url ); ?>"
			   class="widefat"
			   placeholder="<?php echo esc_attr( $permalink ); ?>" />
		<p class="description"><?php _e( 'Enter a custom canonical URL or leave blank to use the permalink', 'seo-simple' ); ?></p>
	</div>

	<div class="seo-simple-field-group">
		<h4><?php _e( 'Search Engine Visibility', 'seo-simple' ); ?></h4>
		
		<div class="seo-simple-field">
			<label>
				<input type="checkbox"
					   id="<?php echo esc_attr( $meta_prefix ); ?>noindex"
					   name="<?php echo esc_attr( $meta_prefix ); ?>noindex"
					   value="1"
					   <?php checked( $meta_data['noindex'], 1 ); ?> />
				<?php _e( 'NOINDEX this post', 'seo-simple' ); ?>
			</label>
			<p class="description"><?php _e( 'Tell search engines not to index this post', 'seo-simple' ); ?></p>
		</div>

		<div class="seo-simple-field">
			<label>
				<input type="checkbox"
					   id="<?php echo esc_attr( $meta_prefix ); ?>nofollow"
					   name="<?php echo esc_attr( $meta_prefix ); ?>nofollow"
					   value="1"
					   <?php checked( $meta_data['nofollow'], 1 ); ?> />
				<?php _e( 'NOFOLLOW this post', 'seo-simple' ); ?>
			</label>
			<p class="description"><?php _e( 'Tell search engines not to follow links on this post', 'seo-simple' ); ?></p>
		</div>
	</div>

	<div class="seo-simple-serp-preview">
		<h3><?php _e( 'SERP Preview', 'seo-simple' ); ?></h3>
		<div class="seo-simple-serp-preview-inner">
			<div class="seo-simple-serp-title" id="seo-simple-title-preview"><?php echo esc_html( $title_preview ); ?></div>
			<div class="seo-simple-serp-url" id="seo-simple-url-preview"><?php echo esc_html( $permalink ); ?></div>
			<div class="seo-simple-serp-description" id="seo-simple-description-preview"><?php echo esc_html( $description_preview ); ?></div>
		</div>
	</div>

	<div class="seo-simple-analysis-section">
		<h3><?php _e( 'SEO Analysis', 'seo-simple' ); ?></h3>
		
		<div class="seo-simple-overall-score">
			<div class="seo-simple-score-header">
				<span class="seo-simple-score-label"><?php _e( 'Overall SEO Score:', 'seo-simple' ); ?></span>
				<span class="seo-simple-score-number"><?php echo isset( $seo_analysis['overall']['score'] ) ? esc_html( $seo_analysis['overall']['score'] ) : '0'; ?></span>
			</div>
			<div class="seo-simple-score-bar">
				<div class="seo-simple-score-fill" id="seo-simple-overall-score-fill" style="width: <?php echo isset( $seo_analysis['overall']['score'] ) ? esc_attr( $seo_analysis['overall']['score'] ) : '0'; ?>%; background-color: <?php echo isset( $seo_analysis['overall']['status'] ) ? esc_attr( $this->get_status_color( $seo_analysis['overall']['status'] ) ) : '#dc3232'; ?>;"></div>
			</div>
			<div class="seo-simple-score-status seo-simple-status-<?php echo isset( $seo_analysis['overall']['status'] ) ? esc_attr( $seo_analysis['overall']['status'] ) : 'bad'; ?>">
				<?php echo isset( $seo_analysis['overall']['recommendation'] ) ? esc_html( $seo_analysis['overall']['recommendation'] ) : __( 'Set a focus keyword to analyze your content.', 'seo-simple' ); ?>
			</div>
		</div>

		<div class="seo-simple-analysis-checks">
			<div class="seo-simple-check-item">
				<div class="seo-simple-check-header">
					<span class="seo-simple-check-title"><?php _e( 'Keyword in Title', 'seo-simple' ); ?></span>
					<span class="seo-simple-check-indicator" id="seo-simple-check-keyword-title">
						<span class="seo-simple-indicator-dot seo-simple-status-<?php echo isset( $seo_analysis['keyword_in_title']['status'] ) ? esc_attr( $seo_analysis['keyword_in_title']['status'] ) : 'bad'; ?>"></span>
					</span>
				</div>
				<div class="seo-simple-check-details">
					<?php echo isset( $seo_analysis['keyword_in_title']['recommendation'] ) ? esc_html( $seo_analysis['keyword_in_title']['recommendation'] ) : __( 'Add your focus keyword to the title.', 'seo-simple' ); ?>
				</div>
			</div>

			<div class="seo-simple-check-item">
				<div class="seo-simple-check-header">
					<span class="seo-simple-check-title"><?php _e( 'Keyword in Meta Description', 'seo-simple' ); ?></span>
					<span class="seo-simple-check-indicator" id="seo-simple-check-keyword-description">
						<span class="seo-simple-indicator-dot seo-simple-status-<?php echo isset( $seo_analysis['keyword_in_description']['status'] ) ? esc_attr( $seo_analysis['keyword_in_description']['status'] ) : 'bad'; ?>"></span>
					</span>
				</div>
				<div class="seo-simple-check-details">
					<?php echo isset( $seo_analysis['keyword_in_description']['recommendation'] ) ? esc_html( $seo_analysis['keyword_in_description']['recommendation'] ) : __( 'Add your focus keyword to the meta description.', 'seo-simple' ); ?>
				</div>
			</div>

			<div class="seo-simple-check-item">
				<div class="seo-simple-check-header">
					<span class="seo-simple-check-title"><?php _e( 'Keyword in URL', 'seo-simple' ); ?></span>
					<span class="seo-simple-check-indicator" id="seo-simple-check-keyword-url">
						<span class="seo-simple-indicator-dot seo-simple-status-<?php echo isset( $seo_analysis['keyword_in_url']['status'] ) ? esc_attr( $seo_analysis['keyword_in_url']['status'] ) : 'bad'; ?>"></span>
					</span>
				</div>
				<div class="seo-simple-check-details">
					<?php echo isset( $seo_analysis['keyword_in_url']['recommendation'] ) ? esc_html( $seo_analysis['keyword_in_url']['recommendation'] ) : __( 'Include your focus keyword in the URL.', 'seo-simple' ); ?>
				</div>
			</div>

			<div class="seo-simple-check-item">
				<div class="seo-simple-check-header">
					<span class="seo-simple-check-title"><?php _e( 'Keyword in First Paragraph', 'seo-simple' ); ?></span>
					<span class="seo-simple-check-indicator" id="seo-simple-check-keyword-first-paragraph">
						<span class="seo-simple-indicator-dot seo-simple-status-<?php echo isset( $seo_analysis['keyword_in_first_paragraph']['status'] ) ? esc_attr( $seo_analysis['keyword_in_first_paragraph']['status'] ) : 'bad'; ?>"></span>
					</span>
				</div>
				<div class="seo-simple-check-details">
					<?php echo isset( $seo_analysis['keyword_in_first_paragraph']['recommendation'] ) ? esc_html( $seo_analysis['keyword_in_first_paragraph']['recommendation'] ) : __( 'Add your focus keyword to the first paragraph.', 'seo-simple' ); ?>
				</div>
			</div>

			<div class="seo-simple-check-item">
				<div class="seo-simple-check-header">
					<span class="seo-simple-check-title"><?php _e( 'Keyword in Headings', 'seo-simple' ); ?></span>
					<span class="seo-simple-check-indicator" id="seo-simple-check-keyword-headings">
						<span class="seo-simple-indicator-dot seo-simple-status-<?php echo isset( $seo_analysis['keyword_in_headings']['status'] ) ? esc_attr( $seo_analysis['keyword_in_headings']['status'] ) : 'bad'; ?>"></span>
					</span>
				</div>
				<div class="seo-simple-check-details">
					<?php echo isset( $seo_analysis['keyword_in_headings']['recommendation'] ) ? esc_html( $seo_analysis['keyword_in_headings']['recommendation'] ) : __( 'Use your focus keyword in headings (H1, H2, or H3).', 'seo-simple' ); ?>
				</div>
			</div>

			<div class="seo-simple-check-item">
				<div class="seo-simple-check-header">
					<span class="seo-simple-check-title"><?php _e( 'Word Count', 'seo-simple' ); ?></span>
					<span class="seo-simple-check-indicator" id="seo-simple-check-word-count">
						<span class="seo-simple-indicator-dot seo-simple-status-<?php echo isset( $seo_analysis['word_count']['status'] ) ? esc_attr( $seo_analysis['word_count']['status'] ) : 'bad'; ?>"></span>
					</span>
				</div>
				<div class="seo-simple-check-details">
					<?php 
					if ( isset( $seo_analysis['word_count']['word_count'] ) ) {
						printf( 
							__( 'Word count: %d. %s', 'seo-simple' ), 
							intval( $seo_analysis['word_count']['word_count'] ),
							esc_html( $seo_analysis['word_count']['recommendation'] )
						);
					} else {
						_e( 'Add more content to reach the recommended word count.', 'seo-simple' );
					}
					?>
				</div>
			</div>

			<div class="seo-simple-check-item">
				<div class="seo-simple-check-header">
					<span class="seo-simple-check-title"><?php _e( 'Keyword Density', 'seo-simple' ); ?></span>
					<span class="seo-simple-check-indicator" id="seo-simple-check-keyword-density">
						<span class="seo-simple-indicator-dot seo-simple-status-<?php echo isset( $seo_analysis['keyword_density']['status'] ) ? esc_attr( $seo_analysis['keyword_density']['status'] ) : 'bad'; ?>"></span>
					</span>
				</div>
				<div class="seo-simple-check-details">
					<?php 
					if ( isset( $seo_analysis['keyword_density']['density'] ) ) {
						printf( 
							__( 'Density: %1$s%%. %2$s', 'seo-simple' ), 
							esc_html( $seo_analysis['keyword_density']['density'] ),
							esc_html( $seo_analysis['keyword_density']['recommendation'] )
						);
					} else {
						_e( 'Use your focus keyword more in your content.', 'seo-simple' );
					}
					?>
				</div>
			</div>

			<div class="seo-simple-check-item">
				<div class="seo-simple-check-header">
					<span class="seo-simple-check-title"><?php _e( 'Internal Links', 'seo-simple' ); ?></span>
					<span class="seo-simple-check-indicator" id="seo-simple-check-links">
						<span class="seo-simple-indicator-dot seo-simple-status-<?php echo isset( $seo_analysis['links']['status'] ) ? esc_attr( $seo_analysis['links']['status'] ) : 'bad'; ?>"></span>
					</span>
				</div>
				<div class="seo-simple-check-details">
					<?php 
					if ( isset( $seo_analysis['links']['internal_links'] ) ) {
						printf( 
							__( 'Internal links: %d. %s', 'seo-simple' ), 
							intval( $seo_analysis['links']['internal_links'] ),
							esc_html( $seo_analysis['links']['recommendation'] )
						);
					} else {
						_e( 'Add internal links to related content on your site.', 'seo-simple' );
					}
					?>
				</div>
			</div>

			<div class="seo-simple-check-item">
				<div class="seo-simple-check-header">
					<span class="seo-simple-check-title"><?php _e( 'Image Alt Text', 'seo-simple' ); ?></span>
					<span class="seo-simple-check-indicator" id="seo-simple-check-image-alt">
						<span class="seo-simple-indicator-dot seo-simple-status-<?php echo isset( $seo_analysis['image_alt_text']['status'] ) ? esc_attr( $seo_analysis['image_alt_text']['status'] ) : 'good'; ?>"></span>
					</span>
				</div>
				<div class="seo-simple-check-details">
					<?php 
					if ( isset( $seo_analysis['image_alt_text']['total_images'] ) && $seo_analysis['image_alt_text']['total_images'] > 0 ) {
						printf( 
							__( 'Images with alt text: %1$d/%2$d (%3$s%%). %4$s', 'seo-simple' ), 
							intval( $seo_analysis['image_alt_text']['images_with_alt'] ),
							intval( $seo_analysis['image_alt_text']['total_images'] ),
							esc_html( $seo_analysis['image_alt_text']['percentage_with_alt'] ),
							esc_html( $seo_analysis['image_alt_text']['recommendation'] )
						);
					} else {
						echo isset( $seo_analysis['image_alt_text']['recommendation'] ) ? esc_html( $seo_analysis['image_alt_text']['recommendation'] ) : __( 'Add images with descriptive alt text to improve accessibility and SEO.', 'seo-simple' );
					}
					?>
				</div>
			</div>
		</div>

		<div class="seo-simple-analyze-button-container">
			<button type="button" class="button seo-simple-analyze-seo" id="seo-simple-analyze-seo">
				<?php _e( 'Analyze SEO', 'seo-simple' ); ?>
			</button>
			<span class="spinner" id="seo-simple-seo-spinner" style="display: none;"></span>
		</div>
	</div>

	<div class="seo-simple-schema-section">
		<h3><?php _e( 'Schema Settings', 'seo-simple' ); ?></h3>
		<p class="description"><?php _e( 'Configure structured data schema for this content.', 'seo-simple' ); ?></p>
		
		<div class="seo-simple-field">
			<label for="<?php echo esc_attr( $meta_prefix ); ?>schema_type"><?php _e( 'Schema Type', 'seo-simple' ); ?></label>
			<select
				id="<?php echo esc_attr( $meta_prefix ); ?>schema_type"
				name="<?php echo esc_attr( $meta_prefix ); ?>schema_type"
				class="widefat">
				<option value=""><?php _e( 'Auto-detect', 'seo-simple' ); ?></option>
				<option value="article" <?php selected( get_post_meta( $post->ID, '_seo_simple_schema_type', true ), 'article' ); ?>><?php _e( 'Article', 'seo-simple' ); ?></option>
				<option value="webpage" <?php selected( get_post_meta( $post->ID, '_seo_simple_schema_type', true ), 'webpage' ); ?>><?php _e( 'WebPage', 'seo-simple' ); ?></option>
				<?php if ( class_exists( 'WooCommerce' ) && get_post_type( $post->ID ) === 'product' ) : ?>
					<option value="product" <?php selected( get_post_meta( $post->ID, '_seo_simple_schema_type', true ), 'product' ); ?>><?php _e( 'Product', 'seo-simple' ); ?></option>
				<?php endif; ?>
				<option value="faq" <?php selected( get_post_meta( $post->ID, '_seo_simple_schema_type', true ), 'faq' ); ?>><?php _e( 'FAQ', 'seo-simple' ); ?></option>
				<option value="custom"><?php _e( 'Custom JSON-LD', 'seo-simple' ); ?></option>
			</select>
			<p class="description"><?php _e( 'Select the type of structured data to generate for this content.', 'seo-simple' ); ?></p>
		</div>

		<div class="seo-simple-field" id="seo-simple-custom-schema-field" style="display: none;">
			<label for="<?php echo esc_attr( $meta_prefix ); ?>custom_schema"><?php _e( 'Custom JSON-LD Schema', 'seo-simple' ); ?></label>
			<textarea
				id="<?php echo esc_attr( $meta_prefix ); ?>custom_schema"
				name="<?php echo esc_attr( $meta_prefix ); ?>custom_schema"
				class="widefat"
				rows="10"><?php echo esc_textarea( get_post_meta( $post->ID, '_seo_simple_custom_schema', true ) ); ?></textarea>
			<p class="description"><?php _e( 'Enter your custom JSON-LD schema. Must be valid JSON format.', 'seo-simple' ); ?></p>
			
			<button type="button" class="button" id="seo-simple-validate-schema">
				<?php _e( 'Validate Schema', 'seo-simple' ); ?>
			</button>
			<span class="spinner" id="seo-simple-schema-spinner" style="display: none;"></span>
			<div id="seo-simple-schema-validation-result"></div>
		</div>

		<div class="seo-simple-field" id="seo-simple-faq-schema-field" style="display: none;">
			<label><?php _e( 'FAQ Schema Management', 'seo-simple' ); ?></label>
			<p class="description"><?php _e( 'Add FAQ content to generate FAQ schema. You can use the FAQ shortcode or block in your content.', 'seo-simple' ); ?></p>
			
			<button type="button" class="button" id="seo-simple-add-faq">
				<?php _e( 'Add FAQ Shortcode', 'seo-simple' ); ?>
			</button>
			<p class="description">
				<?php _e( 'Use the shortcode:', 'seo-simple' ); ?>
				<code>[seo_simple_faq question="Your Question"]Your Answer[/seo_simple_faq]</code>
			</p>
		</div>
	</div>
</div>

<style>
.seo-simple-meta-box .seo-simple-field {
	margin-bottom: 15px;
}
.seo-simple-meta-box label {
	display: block;
	font-weight: bold;
	margin-bottom: 5px;
}
.seo-simple-meta-box .description {
	font-size: 12px;
	color: #666;
	margin: 5px 0 0 0;
}
.seo-simple-serp-preview {
	margin-top: 20px;
	border-top: 1px solid #ddd;
	padding-top: 15px;
}
.seo-simple-serp-preview-inner {
	border: 1px solid #ddd;
	padding: 10px;
	background-color: #f9f9f9;
}
.seo-simple-serp-title {
	color: #1a0dab;
	font-size: 18px;
	font-weight: normal;
	margin-bottom: 2px;
	cursor: pointer;
}
.seo-simple-serp-url {
	color: #006621;
	font-size: 14px;
	margin-bottom: 2px;
}
.seo-simple-serp-description {
	color: #545454;
	font-size: 13px;
	line-height: 1.4;
}
.seo-simple-analysis-section {
	margin-top: 20px;
	border-top: 1px solid #ddd;
	padding-top: 15px;
}
.seo-simple-overall-score {
	margin-bottom: 20px;
}
.seo-simple-score-header {
	display: flex;
	justify-content: space-between;
	margin-bottom: 5px;
}
.seo-simple-score-label {
	font-weight: bold;
}
.seo-simple-score-number {
	font-weight: bold;
}
.seo-simple-score-bar {
	height: 10px;
	background-color: #eee;
	border-radius: 5px;
	margin-bottom: 5px;
}
.seo-simple-score-fill {
	height: 100%;
	border-radius: 5px;
}
.seo-simple-score-status {
	font-size: 12px;
}
.seo-simple-analysis-checks {
	margin-bottom: 15px;
}
.seo-simple-check-item {
	margin-bottom: 10px;
	padding: 8px;
	border: 1px solid #eee;
	border-radius: 4px;
}
.seo-simple-check-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 5px;
}
.seo-simple-check-title {
	font-weight: bold;
}
.seo-simple-check-indicator {
	display: flex;
	align-items: center;
}
.seo-simple-indicator-dot {
	width: 12px;
	height: 12px;
	border-radius: 50%;
	display: inline-block;
	margin-right: 5px;
}
.seo-simple-status-good {
	color: #46b450;
}
.seo-simple-status-ok {
	color: #ffb900;
}
.seo-simple-status-bad {
	color: #dc3232;
}
.seo-simple-indicator-dot.seo-simple-status-good {
	background-color: #46b450;
}
.seo-simple-indicator-dot.seo-simple-status-ok {
	background-color: #ffb900;
}
.seo-simple-indicator-dot.seo-simple-status-bad {
	background-color: #dc3232;
}
.seo-simple-check-details {
	font-size: 12px;
	color: #666;
}
.seo-simple-analyze-button-container {
	margin-top: 15px;
}
.seo-simple-field-group {
	margin-bottom: 15px;
	border: 1px solid #ddd;
	padding: 10px;
	border-radius: 4px;
	background-color: #f9f9f9;
}
.seo-simple-field-group h4 {
	margin-top: 0;
	margin-bottom: 10px;
	font-size: 13px;
	text-transform: uppercase;
	color: #23282d;
}
</style>
