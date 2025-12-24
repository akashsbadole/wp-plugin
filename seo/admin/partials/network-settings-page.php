<?php
/**
 * Network settings page template.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Exit if accessed directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$network_settings = $this->get_network_settings();
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
?>

<div class="wrap">
	<h1><?php esc_html_e( 'SEO Simple Network Settings', 'seo-simple' ); ?></h1>
	
	<?php if ( $updated ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Network settings saved successfully.', 'seo-simple' ); ?></p>
		</div>
	<?php endif; ?>
	
	<nav class="nav-tab-wrapper">
		<a href="?page=seo-simple-network&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'General', 'seo-simple' ); ?>
		</a>
		<a href="?page=seo-simple-network&tab=seo" class="nav-tab <?php echo $active_tab === 'seo' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'SEO Settings', 'seo-simple' ); ?>
		</a>
		<a href="?page=seo-simple-network&tab=sitemap" class="nav-tab <?php echo $active_tab === 'sitemap' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Sitemap', 'seo-simple' ); ?>
		</a>
		<a href="?page=seo-simple-network&tab=analytics" class="nav-tab <?php echo $active_tab === 'analytics' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Analytics', 'seo-simple' ); ?>
		</a>
		<a href="?page=seo-simple-network&tab=sites" class="nav-tab <?php echo $active_tab === 'sites' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Sites', 'seo-simple' ); ?>
		</a>
	</nav>
	
	<form method="post" action="" id="seo-simple-network-settings-form">
		<?php wp_nonce_field( 'seo_simple_network_settings' ); ?>
		
		<?php if ( $active_tab === 'general' ) : ?>
			<div class="seo-simple-network-tab">
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row">
								<label for="network_enable"><?php esc_html_e( 'Enable Network Features', 'seo-simple' ); ?></label>
							</th>
							<td>
								<input type="checkbox" id="network_enable" name="network_settings[network_enable]" value="1" <?php checked( $network_settings['network_enable'], 1 ); ?>>
								<p class="description"><?php esc_html_e( 'Enable network-wide SEO features and settings.', 'seo-simple' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="inherit_network_settings"><?php esc_html_e( 'Inherit Network Settings', 'seo-simple' ); ?></label>
							</th>
							<td>
								<input type="checkbox" id="inherit_network_settings" name="network_settings[inherit_network_settings]" value="1" <?php checked( $network_settings['inherit_network_settings'], 1 ); ?>>
								<p class="description"><?php esc_html_e( 'New sites will automatically inherit network settings as defaults.', 'seo-simple' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="allow_site_override"><?php esc_html_e( 'Allow Site Override', 'seo-simple' ); ?></label>
							</th>
							<td>
								<input type="checkbox" id="allow_site_override" name="network_settings[allow_site_override]" value="1" <?php checked( $network_settings['allow_site_override'], 1 ); ?>>
								<p class="description"><?php esc_html_e( 'Allow individual sites to override network settings.', 'seo-simple' ); ?></p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		<?php elseif ( $active_tab === 'seo' ) : ?>
			<div class="seo-simple-network-tab">
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row">
								<label for="network_title_separator"><?php esc_html_e( 'Title Separator', 'seo-simple' ); ?></label>
							</th>
							<td>
								<input type="text" id="network_title_separator" name="network_settings[network_title_separator]" value="<?php echo esc_attr( $network_settings['network_title_separator'] ); ?>" class="regular-text">
								<p class="description"><?php esc_html_e( 'Default separator for page titles.', 'seo-simple' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="network_title_format"><?php esc_html_e( 'Title Format', 'seo-simple' ); ?></label>
							</th>
							<td>
								<input type="text" id="network_title_format" name="network_settings[network_title_format]" value="<?php echo esc_attr( $network_settings['network_title_format'] ); ?>" class="regular-text">
								<p class="description"><?php esc_html_e( 'Default title format. Available placeholders: %post_title%, %site_name%, %tagline%, %category%', 'seo-simple' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="network_description_template"><?php esc_html_e( 'Description Template', 'seo-simple' ); ?></label>
							</th>
							<td>
								<textarea id="network_description_template" name="network_settings[network_description_template]" rows="3" class="large-text"><?php echo esc_textarea( $network_settings['network_description_template'] ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Default meta description template.', 'seo-simple' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label><?php esc_html_e( 'Social Media Integration', 'seo-simple' ); ?></label>
							</th>
							<td>
								<fieldset>
									<label>
										<input type="checkbox" name="network_settings[network_enable_og]" value="1" <?php checked( $network_settings['network_enable_og'], 1 ); ?>>
										<?php esc_html_e( 'Enable Open Graph meta tags', 'seo-simple' ); ?>
									</label><br>
									<label>
										<input type="checkbox" name="network_settings[network_enable_twitter]" value="1" <?php checked( $network_settings['network_enable_twitter'], 1 ); ?>>
										<?php esc_html_e( 'Enable Twitter Card meta tags', 'seo-simple' ); ?>
									</label><br>
									<label>
										<input type="checkbox" name="network_settings[network_enable_json_ld]" value="1" <?php checked( $network_settings['network_enable_json_ld'], 1 ); ?>>
										<?php esc_html_e( 'Enable JSON-LD structured data', 'seo-simple' ); ?>
									</label>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		<?php elseif ( $active_tab === 'sitemap' ) : ?>
			<div class="seo-simple-network-tab">
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row">
								<label for="network_enable_sitemap"><?php esc_html_e( 'Enable Network Sitemap', 'seo-simple' ); ?></label>
							</th>
							<td>
								<input type="checkbox" id="network_enable_sitemap" name="network_settings[network_enable_sitemap]" value="1" <?php checked( $network_settings['network_enable_sitemap'], 1 ); ?>>
								<p class="description"><?php esc_html_e( 'Generate a network-wide sitemap index.', 'seo-simple' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label><?php esc_html_e( 'Include Sites', 'seo-simple' ); ?></label>
							</th>
							<td>
								<fieldset>
									<p class="description"><?php esc_html_e( 'Select which sites to include in the network sitemap. Leave empty to include all sites.', 'seo-simple' ); ?></p>
									<?php foreach ( $sites as $site ) : ?>
										<?php if ( $this->is_site_active( $site['blog_id'] ) ) : ?>
											<label>
												<input type="checkbox" name="network_settings[network_sitemap_include_sites][]" value="<?php echo $site['blog_id']; ?>" <?php checked( in_array( $site['blog_id'], $network_settings['network_sitemap_include_sites'] ) ); ?>>
												<?php echo esc_html( $site['site_name'] ); ?> (<?php echo esc_html( $site['domain'] . $site['path'] ); ?>)
											</label><br>
										<?php endif; ?>
									<?php endforeach; ?>
								</fieldset>
								<p>
									<button type="button" class="button" id="generate-network-sitemap">
										<?php esc_html_e( 'Generate Network Sitemap', 'seo-simple' ); ?>
									</button>
									<span class="spinner"></span>
								</p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		<?php elseif ( $active_tab === 'analytics' ) : ?>
			<div class="seo-simple-network-tab">
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row">
								<label for="network_google_analytics"><?php esc_html_e( 'Google Analytics Code', 'seo-simple' ); ?></label>
							</th>
							<td>
								<input type="text" id="network_google_analytics" name="network_settings[network_google_analytics]" value="<?php echo esc_attr( $network_settings['network_google_analytics'] ); ?>" class="regular-text" placeholder="UA-XXXXXXXXX-X">
								<p class="description"><?php esc_html_e( 'Google Analytics tracking ID for the entire network.', 'seo-simple' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="network_google_site_verification"><?php esc_html_e( 'Google Site Verification', 'seo-simple' ); ?></label>
							</th>
							<td>
								<input type="text" id="network_google_site_verification" name="network_settings[network_google_site_verification]" value="<?php echo esc_attr( $network_settings['network_google_site_verification'] ); ?>" class="regular-text">
								<p class="description"><?php esc_html_e( 'Google Search Console verification code.', 'seo-simple' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="network_bing_site_verification"><?php esc_html_e( 'Bing Site Verification', 'seo-simple' ); ?></label>
							</th>
							<td>
								<input type="text" id="network_bing_site_verification" name="network_settings[network_bing_site_verification]" value="<?php echo esc_attr( $network_settings['network_bing_site_verification'] ); ?>" class="regular-text">
								<p class="description"><?php esc_html_e( 'Bing Webmaster Tools verification code.', 'seo-simple' ); ?></p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		<?php elseif ( $active_tab === 'sites' ) : ?>
			<div class="seo-simple-network-tab">
				<div class="tab-description">
					<p><?php esc_html_e( 'Manage SEO Simple plugin activation and settings across all sites in the network.', 'seo-simple' ); ?></p>
				</div>
				
				<div class="sites-management">
					<div class="toolbar">
						<button type="button" class="button" id="activate-selected">
							<?php esc_html_e( 'Activate on Selected', 'seo-simple' ); ?>
						</button>
						<button type="button" class="button" id="deactivate-selected">
							<?php esc_html_e( 'Deactivate on Selected', 'seo-simple' ); ?>
						</button>
						<button type="button" class="button" id="sync-settings-selected">
							<?php esc_html_e( 'Sync Settings to Selected', 'seo-simple' ); ?>
						</button>
						<span class="spinner"></span>
					</div>
					
					<table class="wp-list-table widefat fixed striped sites-list">
						<thead>
							<tr>
								<td class="manage-column column-cb check-column">
									<input type="checkbox" id="cb-select-all-1">
								</td>
								<th scope="col" class="manage-column column-blogname"><?php esc_html_e( 'Site Name', 'seo-simple' ); ?></th>
								<th scope="col" class="manage-column column-domain"><?php esc_html_e( 'Domain', 'seo-simple' ); ?></th>
								<th scope="col" class="manage-column column-status"><?php esc_html_e( 'Status', 'seo-simple' ); ?></th>
								<th scope="col" class="manage-column column-actions"><?php esc_html_e( 'Actions', 'seo-simple' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $sites as $site ) : ?>
								<tr>
									<th scope="row" class="check-column">
										<input type="checkbox" name="sites[]" value="<?php echo $site['blog_id']; ?>" class="site-checkbox">
									</th>
									<td class="column-blogname">
										<strong><?php echo esc_html( $site['site_name'] ); ?></strong>
									</td>
									<td class="column-domain">
										<?php echo esc_html( $site['domain'] . $site['path'] ); ?>
									</td>
									<td class="column-status">
										<?php if ( $this->is_site_active( $site['blog_id'] ) ) : ?>
											<span class="active"><?php esc_html_e( 'Active', 'seo-simple' ); ?></span>
										<?php else : ?>
											<span class="inactive"><?php esc_html_e( 'Inactive', 'seo-simple' ); ?></span>
										<?php endif; ?>
									</td>
									<td class="column-actions">
										<?php if ( $this->is_site_active( $site['blog_id'] ) ) : ?>
											<a href="<?php echo esc_url( get_admin_url( $site['blog_id'], 'admin.php?page=seo-simple-settings' ) ); ?>" class="button">
												<?php esc_html_e( 'Settings', 'seo-simple' ); ?>
											</a>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php endif; ?>
		
		<p class="submit">
			<input type="submit" name="seo_simple_network_settings" class="button-primary" value="<?php esc_attr_e( 'Save Network Settings', 'seo-simple' ); ?>">
		</p>
	</form>
</div>

<style>
.seo-simple-network-tab {
	margin-top: 20px;
}

.tab-description {
	background: #fff;
	padding: 15px;
	border: 1px solid #c3c4c7;
	border-radius: 4px;
	margin-bottom: 20px;
}

.sites-management .toolbar {
	margin-bottom: 15px;
}

.sites-management .toolbar .spinner {
	float: none;
	vertical-align: middle;
	margin-left: 10px;
}

.sites-list th.check-column {
	padding: 8px 10px 0 0;
}

.sites-list .active {
	color: #00a32a;
	font-weight: bold;
}

.sites-list .inactive {
	color: #d63638;
	font-weight: bold;
}

.sites-list .column-actions {
	width: 120px;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Generate network sitemap
	$('#generate-network-sitemap').on('click', function() {
		var $button = $(this);
		var $spinner = $button.next('.spinner');
		
		$button.prop('disabled', true);
		$spinner.addClass('is-active');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'seo_simple_generate_network_sitemap',
				nonce: '<?php echo wp_create_nonce( 'seo_simple_network_nonce' ); ?>'
			},
			success: function(response) {
				if (response.success) {
					$button.after('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
				} else {
					$button.after('<div class="notice notice-error inline"><p>' + response.data.message + '</p></div>');
				}
			},
			error: function() {
				$button.after('<div class="notice notice-error inline"><p><?php esc_html_e( 'Error generating sitemap.', 'seo-simple' ); ?></p></div>');
			},
			complete: function() {
				$button.prop('disabled', false);
				$spinner.removeClass('is-active');
				
				// Remove notice after 3 seconds
				setTimeout(function() {
					$button.next('.notice').fadeOut(function() {
						$(this).remove();
					});
				}, 3000);
			}
		});
	});
	
	// Select all checkboxes
	$('#cb-select-all-1').on('change', function() {
		$('.site-checkbox').prop('checked', $(this).prop('checked'));
	});
	
	// Site management actions
	function performSiteAction(action) {
		var $selected = $('.site-checkbox:checked');
		
		if ($selected.length === 0) {
			alert('<?php esc_html_e( 'Please select at least one site.', 'seo-simple' ); ?>');
			return;
		}
		
		var sites = [];
		$selected.each(function() {
			sites.push($(this).val());
		});
		
		var $spinner = $('.toolbar .spinner');
		$spinner.addClass('is-active');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'seo_simple_network_action',
				network_action: action,
				sites: sites,
				nonce: '<?php echo wp_create_nonce( 'seo_simple_network_nonce' ); ?>'
			},
			success: function(response) {
				if (response.success) {
					alert(response.data.message);
					location.reload();
				} else {
					alert(response.data.message);
				}
			},
			error: function() {
				alert('<?php esc_html_e( 'Error performing action.', 'seo-simple' ); ?>');
			},
			complete: function() {
				$spinner.removeClass('is-active');
			}
		});
	}
	
	$('#activate-selected').on('click', function() {
		if (confirm('<?php esc_html_e( 'Are you sure you want to activate SEO Simple on the selected sites?', 'seo-simple' ); ?>')) {
			performSiteAction('activate_on_sites');
		}
	});
	
	$('#deactivate-selected').on('click', function() {
		if (confirm('<?php esc_html_e( 'Are you sure you want to deactivate SEO Simple on the selected sites?', 'seo-simple' ); ?>')) {
			performSiteAction('deactivate_on_sites');
		}
	});
	
	$('#sync-settings-selected').on('click', function() {
		if (confirm('<?php esc_html_e( 'Are you sure you want to sync network settings to the selected sites? This will override existing site settings.', 'seo-simple' ); ?>')) {
			performSiteAction('sync_settings');
		}
	});
});
</script>