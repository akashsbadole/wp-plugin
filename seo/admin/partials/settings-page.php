<?php
/**
 * Settings page template for the SEO Simple plugin.
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = $this->get_settings();
?>

<div class="wrap seo-simple-settings">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php if ( isset( $_GET['imported'] ) && $_GET['imported'] === 'true' ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Settings imported successfully!', 'seo-simple' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( isset( $_GET['import_error'] ) && $_GET['import_error'] === 'true' ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Error importing settings. Please check the file and try again.', 'seo-simple' ); ?></p>
		</div>
	<?php endif; ?>

	<div class="nav-tab-wrapper">
		<?php foreach ( $tabs as $tab_id => $tab_label ) : ?>
			<a href="?page=<?php echo esc_attr( $this->page_slug ); ?>&tab=<?php echo esc_attr( $tab_id ); ?>" class="nav-tab <?php echo $active_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
				<?php echo esc_html( $tab_label ); ?>
			</a>
		<?php endforeach; ?>
	</div>

		<!-- Regular Settings Form -->
		<?php if ( $active_tab !== 'code-snippets' ) : ?>
			<form method="post" action="options.php" id="seo-simple-settings-form">
				<?php settings_fields( $this->option_group ); ?>
		<?php endif; ?>
	
		<div class="tab-content">
			<?php if ( $active_tab === 'general' ) : ?>
				<div class="tab-pane active">
					<?php do_settings_sections( $this->page_slug . '_general' ); ?>
					
					<h2 class="title"><?php esc_html_e( 'Custom Post Types', 'seo-simple' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Enable SEO for specific custom post types.', 'seo-simple' ); ?></p>
					
					<table class="form-table" role="presentation">
						<tbody>
							<?php
							$post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' );
							foreach ( $post_types as $post_type ) :
								$option_name = 'seo_enable_' . $post_type->name;
								$enabled = isset( $settings[ $option_name ] ) ? $settings[ $option_name ] : 0;
								$field_id = 'seo-enable-' . $post_type->name;
							?>
								<tr>
									<th scope="row"><?php echo esc_html( $post_type->labels->name ); ?></th>
									<td>
										<input type="checkbox" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $this->option_name . '[' . $option_name . ']' ); ?>" value="1" <?php checked( $enabled, 1 ); ?> />
										<label for="<?php echo esc_attr( $field_id ); ?>"><?php printf( esc_html__( 'Enable SEO for %s', 'seo-simple' ), esc_html( $post_type->labels->name ) ); ?></label>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
	
			<?php if ( $active_tab === 'titles' ) : ?>
				<div class="tab-pane active">
					<?php do_settings_sections( $this->page_slug . '_titles' ); ?>
					
					<h2 class="title"><?php esc_html_e( 'Content Type Templates', 'seo-simple' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Set title and meta description templates for different content types.', 'seo-simple' ); ?></p>
					
					<table class="form-table" role="presentation">
						<tbody>
							<?php
							$content_types = array(
								'post' => __( 'Posts', 'seo-simple' ),
								'page' => __( 'Pages', 'seo-simple' ),
							) + wp_list_pluck( get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' ), 'labels.name' );
							
							foreach ( $content_types as $content_type => $content_type_label ) :
								$title_option = 'seo_' . $content_type . '_title_format';
								$desc_option = 'seo_' . $content_type . '_description_format';
								$title_value = isset( $settings[ $title_option ] ) ? $settings[ $title_option ] : $settings['seo_title_format'];
								$desc_value = isset( $settings[ $desc_option ] ) ? $settings[ $desc_option ] : $settings['seo_description_template'];
							?>
								<tr>
									<th scope="row"><?php echo esc_html( $content_type_label ); ?></th>
									<td>
										<fieldset>
											<label for="<?php echo esc_attr( 'seo-' . $content_type . '-title' ); ?>"><?php esc_html_e( 'Title Template:', 'seo-simple' ); ?></label>
											<input type="text" id="<?php echo esc_attr( 'seo-' . $content_type . '-title' ); ?>" name="<?php echo esc_attr( $this->option_name . '[' . $title_option . ']' ); ?>" value="<?php echo esc_attr( $title_value ); ?>" class="regular-text" />
											<p class="description"><?php esc_html_e( 'Available tags: %post_title%, %site_name%, %separator%, %category%, %author%', 'seo-simple' ); ?></p>
											
											<label for="<?php echo esc_attr( 'seo-' . $content_type . '-description' ); ?>"><?php esc_html_e( 'Description Template:', 'seo-simple' ); ?></label>
											<textarea id="<?php echo esc_attr( 'seo-' . $content_type . '-description' ); ?>" name="<?php echo esc_attr( $this->option_name . '[' . $desc_option . ']' ); ?>" rows="3" class="large-text"><?php echo esc_textarea( $desc_value ); ?></textarea>
											<p class="description"><?php esc_html_e( 'Available tags: %post_content%, %post_excerpt%, %post_title%', 'seo-simple' ); ?></p>
										</fieldset>
									</td>
								</tr>
							<?php endforeach; ?>
							
							<?php
							$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
							foreach ( $taxonomies as $taxonomy ) :
								$title_option = 'seo_' . $taxonomy->name . '_title_format';
								$desc_option = 'seo_' . $taxonomy->name . '_description_format';
								$title_value = isset( $settings[ $title_option ] ) ? $settings[ $title_option ] : '%term_title% | %site_name%';
								$desc_value = isset( $settings[ $desc_option ] ) ? $settings[ $desc_option ] : '';
							?>
								<tr>
									<th scope="row"><?php echo esc_html( $taxonomy->labels->name ); ?></th>
									<td>
										<fieldset>
											<label for="<?php echo esc_attr( 'seo-' . $taxonomy->name . '-title' ); ?>"><?php esc_html_e( 'Title Template:', 'seo-simple' ); ?></label>
											<input type="text" id="<?php echo esc_attr( 'seo-' . $taxonomy->name . '-title' ); ?>" name="<?php echo esc_attr( $this->option_name . '[' . $title_option . ']' ); ?>" value="<?php echo esc_attr( $title_value ); ?>" class="regular-text" />
											<p class="description"><?php esc_html_e( 'Available tags: %term_title%, %site_name%, %separator%', 'seo-simple' ); ?></p>
											
											<label for="<?php echo esc_attr( 'seo-' . $taxonomy->name . '-description' ); ?>"><?php esc_html_e( 'Description Template:', 'seo-simple' ); ?></label>
											<textarea id="<?php echo esc_attr( 'seo-' . $taxonomy->name . '-description' ); ?>" name="<?php echo esc_attr( $this->option_name . '[' . $desc_option . ']' ); ?>" rows="3" class="large-text"><?php echo esc_textarea( $desc_value ); ?></textarea>
											<p class="description"><?php esc_html_e( 'Available tags: %term_description%, %term_title%', 'seo-simple' ); ?></p>
										</fieldset>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
	
			<?php if ( $active_tab === 'social' ) : ?>
				<div class="tab-pane active">
					<?php do_settings_sections( $this->page_slug . '_social' ); ?>
					
					<h2 class="title"><?php esc_html_e( 'Open Graph Settings', 'seo-simple' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Configure Open Graph and social media settings.', 'seo-simple' ); ?></p>
					
					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><?php esc_html_e( 'Default OG Image', 'seo-simple' ); ?></th>
								<td>
									<div class="image-field-container">
										<input type="text" id="seo-og-image" name="<?php echo esc_attr( $this->option_name . '[seo_og_image]' ); ?>" value="<?php echo esc_attr( isset( $settings['seo_og_image'] ) ? $settings['seo_og_image'] : '' ); ?>" class="regular-text image-url" />
										<button type="button" class="button image-upload-button" data-target="seo-og-image"><?php esc_html_e( 'Select Image', 'seo-simple' ); ?></button>
										
										<?php if ( ! empty( $settings['seo_og_image'] ) ) : ?>
											<div class="image-preview">
												<img src="<?php echo esc_url( $settings['seo_og_image'] ); ?>" alt="<?php esc_attr_e( 'Preview', 'seo-simple' ); ?>" style="max-width: 100px; max-height: 100px; margin-top: 10px;" />
											</div>
										<?php endif; ?>
									</div>
									<p class="description"><?php esc_html_e( 'Default image for Open Graph when no post-specific image is set.', 'seo-simple' ); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
					
					<h2 class="title"><?php esc_html_e( 'Social Media Profiles', 'seo-simple' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Add your social media profiles for structured data.', 'seo-simple' ); ?></p>
					
					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><?php esc_html_e( 'Facebook Page URL', 'seo-simple' ); ?></th>
								<td>
									<input type="text" name="<?php echo esc_attr( $this->option_name . '[seo_facebook_url]' ); ?>" value="<?php echo esc_attr( isset( $settings['seo_facebook_url'] ) ? $settings['seo_facebook_url'] : '' ); ?>" class="regular-text" />
									<p class="description"><?php esc_html_e( 'URL to your Facebook page.', 'seo-simple' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Twitter Username', 'seo-simple' ); ?></th>
								<td>
									<input type="text" name="<?php echo esc_attr( $this->option_name . '[seo_twitter_username]' ); ?>" value="<?php echo esc_attr( isset( $settings['seo_twitter_username'] ) ? $settings['seo_twitter_username'] : '' ); ?>" class="regular-text" />
									<p class="description"><?php esc_html_e( 'Your Twitter username (without @).', 'seo-simple' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Instagram URL', 'seo-simple' ); ?></th>
								<td>
									<input type="text" name="<?php echo esc_attr( $this->option_name . '[seo_instagram_url]' ); ?>" value="<?php echo esc_attr( isset( $settings['seo_instagram_url'] ) ? $settings['seo_instagram_url'] : '' ); ?>" class="regular-text" />
									<p class="description"><?php esc_html_e( 'URL to your Instagram profile.', 'seo-simple' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'LinkedIn URL', 'seo-simple' ); ?></th>
								<td>
									<input type="text" name="<?php echo esc_attr( $this->option_name . '[seo_linkedin_url]' ); ?>" value="<?php echo esc_attr( isset( $settings['seo_linkedin_url'] ) ? $settings['seo_linkedin_url'] : '' ); ?>" class="regular-text" />
									<p class="description"><?php esc_html_e( 'URL to your LinkedIn company page.', 'seo-simple' ); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
	
			<?php if ( $active_tab === 'sitemap' ) : ?>
				<div class="tab-pane active">
					<?php do_settings_sections( $this->page_slug . '_sitemap' ); ?>
					
					<h2 class="title"><?php esc_html_e( 'Sitemap Information', 'seo-simple' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Your XML sitemap is available at the following URL:', 'seo-simple' ); ?></p>
					
					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><?php esc_html_e( 'Sitemap URL', 'seo-simple' ); ?></th>
								<td>
									<input type="text" readonly value="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>" class="regular-text" />
									<button type="button" class="button copy-sitemap-url" data-url="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>"><?php esc_html_e( 'Copy URL', 'seo-simple' ); ?></button>
									<p class="description"><?php esc_html_e( 'Submit this URL to Google Search Console and other search engines.', 'seo-simple' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Last Generated', 'seo-simple' ); ?></th>
								<td>
									<?php
									$last_generated = get_option( 'seo_simple_sitemap_last_generated', 0 );
									if ( $last_generated ) {
										echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_generated ) );
									} else {
										esc_html_e( 'Not generated yet', 'seo-simple' );
									}
									?>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Regenerate Sitemap', 'seo-simple' ); ?></th>
								<td>
									<button type="button" class="button regenerate-sitemap"><?php esc_html_e( 'Regenerate Sitemap', 'seo-simple' ); ?></button>
									<p class="description"><?php esc_html_e( 'Manually regenerate your XML sitemap.', 'seo-simple' ); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
	
			<?php if ( $active_tab === 'code-snippets' ) : ?>
				<div class="tab-pane active">
					<?php
					$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';
					$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
					$preset = isset( $_GET['preset'] ) ? sanitize_text_field( $_GET['preset'] ) : '';
					$message = '';
					
					if ( isset( $_GET['message'] ) ) {
						switch ( $_GET['message'] ) {
							case 'added':
								$message = __( 'Code snippet added successfully!', 'seo-simple' );
								break;
							case 'updated':
								$message = __( 'Code snippet updated successfully!', 'seo-simple' );
								break;
							case 'deleted':
								$message = __( 'Code snippet deleted successfully!', 'seo-simple' );
								break;
						}
					}
					
					switch ( $action ) {
						case 'add':
							$this->display_snippet_form( null, $preset );
							break;
						case 'edit':
							$snippet = $this->get_snippet( $id );
							$this->display_snippet_form( $snippet );
							break;
						default:
							$this->display_snippets_list( $message );
							break;
					}
					?>
				</div>
			<?php endif; ?>
	
			<?php if ( $active_tab === 'advanced' ) : ?>
				<div class="tab-pane active">
					<?php do_settings_sections( $this->page_slug . '_advanced' ); ?>
					
					<h2 class="title"><?php esc_html_e( 'Import/Export Settings', 'seo-simple' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Import or export your SEO settings.', 'seo-simple' ); ?></p>
					
					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><?php esc_html_e( 'Export Settings', 'seo-simple' ); ?></th>
								<td>
									<button type="button" class="button export-settings"><?php esc_html_e( 'Export Settings', 'seo-simple' ); ?></button>
									<p class="description"><?php esc_html_e( 'Download your current SEO settings as a JSON file.', 'seo-simple' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Import Settings', 'seo-simple' ); ?></th>
								<td>
									<input type="file" name="settings_file" accept=".json" />
									<button type="button" class="button import-settings"><?php esc_html_e( 'Import Settings', 'seo-simple' ); ?></button>
									<p class="description"><?php esc_html_e( 'Upload a JSON file with your SEO settings.', 'seo-simple' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Reset Settings', 'seo-simple' ); ?></th>
								<td>
									<button type="button" class="button button-secondary reset-settings"><?php esc_html_e( 'Reset to Defaults', 'seo-simple' ); ?></button>
									<p class="description"><?php esc_html_e( 'Reset all settings to their default values.', 'seo-simple' ); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	
		<?php if ( $active_tab !== 'code-snippets' ) : ?>
			<?php submit_button(); ?>
			</form>
		<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	// Image upload functionality
	$('.image-upload-button').on('click', function(e) {
		e.preventDefault();
		var target = $(this).data('target');
		var button = $(this);
		
		var frame = wp.media({
			title: seoSimpleSettings.i18n.selectImage,
			multiple: false,
			library: {
				type: 'image'
			},
			button: {
				text: seoSimpleSettings.i18n.useThisImage
			}
		});
		
		frame.on('select', function() {
			var attachment = frame.state().get('selection').first().toJSON();
			$('#' + target).val(attachment.url);
			
			// Show preview
			var preview = $('#' + target).parent().find('.image-preview');
			if (preview.length === 0) {
				preview = $('<div class="image-preview"></div>');
				$('#' + target).parent().append(preview);
			}
			preview.html('<img src="' + attachment.url + '" alt="' + seoSimpleSettings.i18n.preview + '" style="max-width: 100px; max-height: 100px; margin-top: 10px;" />');
		});
		
		frame.open();
	});
	
	// Copy sitemap URL
	$('.copy-sitemap-url').on('click', function() {
		var url = $(this).data('url');
		var dummy = $('<input>').val(url);
		$('body').append(dummy);
		dummy.select();
		document.execCommand('copy');
		dummy.remove();
		
		$(this).text(seoSimpleSettings.i18n.copied);
		setTimeout(function() {
			$('.copy-sitemap-url').text(seoSimpleSettings.i18n.copyUrl);
		}, 2000);
	});
	
	// Regenerate sitemap
	$('.regenerate-sitemap').on('click', function() {
		var button = $(this);
		button.prop('disabled', true).text(seoSimpleSettings.i18n.regenerating);
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'seo_simple_regenerate_sitemap',
				nonce: seoSimpleSettings.nonce
			},
			success: function(response) {
				if (response.success) {
					location.reload();
				} else {
					button.prop('disabled', false).text(seoSimpleSettings.i18n.regenerateSitemap);
					alert(seoSimpleSettings.i18n.errorRegenerate);
				}
			},
			error: function() {
				button.prop('disabled', false).text(seoSimpleSettings.i18n.regenerateSitemap);
				alert(seoSimpleSettings.i18n.errorRegenerate);
			}
		});
	});
	
	// Export settings
	$('.export-settings').on('click', function() {
		window.location.href = '?page=' + seoSimpleSettings.pageSlug + '&action=export_settings&nonce=' + seoSimpleSettings.nonce;
	});
	
	// Import settings
	$('.import-settings').on('click', function(e) {
		e.preventDefault();
		
		var fileInput = $('input[name="settings_file"]')[0];
		if (!fileInput.files || fileInput.files.length === 0) {
			alert(seoSimpleSettings.i18n.selectFile);
			return;
		}
		
		var formData = new FormData();
		formData.append('action', 'seo_simple_import_settings');
		formData.append('nonce', seoSimpleSettings.nonce);
		formData.append('settings_file', fileInput.files[0]);
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				if (response.success) {
					location.href = '?page=' + seoSimpleSettings.pageSlug + '&imported=true';
				} else {
					alert(seoSimpleSettings.i18n.errorImport);
				}
			},
			error: function() {
				alert(seoSimpleSettings.i18n.errorImport);
			}
		});
	});
	
	// Reset settings
	$('.reset-settings').on('click', function(e) {
		e.preventDefault();
		
		if (confirm(seoSimpleSettings.i18n.resetConfirm)) {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'seo_simple_reset_settings',
					nonce: seoSimpleSettings.nonce
				},
				success: function(response) {
					if (response.success) {
						location.reload();
					} else {
						alert(seoSimpleSettings.i18n.errorReset);
					}
				},
				error: function() {
					alert(seoSimpleSettings.i18n.errorReset);
				}
			});
		}
	});
	
	// Code snippets functionality - simplified to avoid complex string issues
	$('#snippet_preset').on('change', function() {
		var preset = $(this).val();
		if (!preset) return;
		
		if (confirm(seoSimpleSettings.i18n.presetConfirm)) {
			// Use the preset data from the localized variable
			var presetData = seoSimpleSettings.presets[preset] || {};
			
			if (presetData.name) $('#snippet_name').val(presetData.name);
			if (presetData.code) $('#snippet_code').val(presetData.code);
			if (presetData.location) $('#snippet_location').val(presetData.location);
		} else {
			$(this).val('');
		}
	});
	
	// Form validation for code snippets
	$('#seo-simple-settings-form').on('submit', function(e) {
		var activeTab = $('.nav-tab-active').attr('href');
		if (activeTab && activeTab.indexOf('code-snippets') !== -1) {
			var name = $('#snippet_name').val().trim();
			var code = $('#snippet_code').val().trim();
			
			if (!name) {
				alert(seoSimpleSettings.i18n.nameRequired);
				e.preventDefault();
				return false;
			}
			
			if (!code) {
				alert(seoSimpleSettings.i18n.codeRequired);
				e.preventDefault();
				return false;
			}
		}
	});
});
</script>
