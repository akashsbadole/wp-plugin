<?php

/**
 * Provide a view for the code snippets form
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$form_title = $is_edit ? __( 'Edit Code Snippet', 'seo-simple' ) : __( 'Add New Code Snippet', 'seo-simple' );
$submit_text = $is_edit ? __( 'Update Snippet', 'seo-simple' ) : __( 'Add Snippet', 'seo-simple' );

// Use preset data if available, otherwise use snippet data or defaults
$name = $is_edit ? $snippet->name : ( isset( $preset_data['name'] ) ? $preset_data['name'] : '' );
$code = $is_edit ? $snippet->code : ( isset( $preset_data['code'] ) ? $preset_data['code'] : '' );
$location = $is_edit ? $snippet->location : ( isset( $preset_data['location'] ) ? $preset_data['location'] : 'header' );
$status = $is_edit ? $snippet->status : 'active';
$priority = $is_edit ? $snippet->priority : 10;

// If there was a validation error, use the submitted data instead
if ( isset( $submited_data ) ) {
	$name = $submited_data['name'];
	$code = $submited_data['code'];
	$location = $submited_data['location'];
	$status = $submited_data['status'];
	$priority = $submited_data['priority'];
}

?>

<div class="wrap">
	<h1><?php echo esc_html( $form_title ); ?></h1>
	
	<?php if ( isset( $form_error ) ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo esc_html( $form_error ); ?></p>
		</div>
	<?php endif; ?>
	
	<div id="seo-simple-code-snippets-form-container">
		<form method="post" action="<?php echo esc_url( admin_url( 'options-general.php?page=seo-simple-code-snippets' ) ); ?>">
			<?php wp_nonce_field( 'seo_simple_code_snippet_action', 'seo_simple_code_snippet_nonce' ); ?>
			
			<?php if ( $is_edit ) : ?>
				<input type="hidden" name="snippet_id" value="<?php echo esc_attr( $snippet->id ); ?>">
			<?php endif; ?>
			
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<label for="snippet_preset"><?php _e( 'Quick Add Preset', 'seo-simple' ); ?></label>
						</th>
						<td>
							<select name="snippet_preset" id="snippet_preset">
								<option value=""><?php _e( 'Select a preset (optional)', 'seo-simple' ); ?></option>
								<option value="google-analytics"><?php _e( 'Google Analytics', 'seo-simple' ); ?></option>
								<option value="facebook-pixel"><?php _e( 'Facebook Pixel', 'seo-simple' ); ?></option>
							</select>
							<p class="description"><?php _e( 'Select a preset to auto-fill the form. You can modify the code after selection.', 'seo-simple' ); ?></p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="snippet_name"><?php _e( 'Snippet Name', 'seo-simple' ); ?></label>
						</th>
						<td>
							<input name="snippet_name" type="text" id="snippet_name" class="regular-text"
								   value="<?php echo esc_attr( $name ); ?>" required>
							<p class="description"><?php _e( 'Enter a descriptive name for this code snippet.', 'seo-simple' ); ?></p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="snippet_code"><?php _e( 'Code', 'seo-simple' ); ?></label>
						</th>
						<td>
							<textarea name="snippet_code" id="snippet_code" rows="15" class="large-text" required><?php echo esc_textarea( $code ); ?></textarea>
							<p class="description">
								<?php _e( 'Enter your HTML, CSS, or JavaScript code. The code will be validated for security before saving.', 'seo-simple' ); ?>
								<br>
								<strong><?php _e( 'Note:', 'seo-simple' ); ?></strong> 
								<?php _e( 'For security reasons, only safe code patterns are allowed. Dangerous functions like eval(), document.write(), and others are blocked.', 'seo-simple' ); ?>
							</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="snippet_location"><?php _e( 'Location', 'seo-simple' ); ?></label>
						</th>
						<td>
							<select name="snippet_location" id="snippet_location">
								<option value="header" <?php selected( $location, 'header' ); ?>><?php _e( 'Header (in <head>)', 'seo-simple' ); ?></option>
								<option value="body" <?php selected( $location, 'body' ); ?>><?php _e( 'Body (after opening <body> tag)', 'seo-simple' ); ?></option>
								<option value="footer" <?php selected( $location, 'footer' ); ?>><?php _e( 'Footer (before </body>)', 'seo-simple' ); ?></option>
							</select>
							<p class="description"><?php _e( 'Select where this code snippet should be inserted.', 'seo-simple' ); ?></p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="snippet_status"><?php _e( 'Status', 'seo-simple' ); ?></label>
						</th>
						<td>
							<select name="snippet_status" id="snippet_status">
								<option value="active" <?php selected( $status, 'active' ); ?>><?php _e( 'Active', 'seo-simple' ); ?></option>
								<option value="inactive" <?php selected( $status, 'inactive' ); ?>><?php _e( 'Inactive', 'seo-simple' ); ?></option>
							</select>
							<p class="description"><?php _e( 'Only active snippets will be output on your site.', 'seo-simple' ); ?></p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="snippet_priority"><?php _e( 'Priority', 'seo-simple' ); ?></label>
						</th>
						<td>
							<input name="snippet_priority" type="number" id="snippet_priority" class="small-text" 
								   value="<?php echo esc_attr( $priority ); ?>" min="1" max="999">
							<p class="description"><?php _e( 'Lower numbers load first. Use this to control the order of your snippets.', 'seo-simple' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
			
			<div class="seo-simple-code-snippets-form-actions">
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" 
						   value="<?php echo esc_attr( $submit_text ); ?>">
					<a href="<?php echo esc_url( admin_url( 'options-general.php?page=seo-simple-code-snippets' ) ); ?>" 
					   class="button"><?php _e( 'Cancel', 'seo-simple' ); ?></a>
				</p>
			</div>
		</form>
	</div>
	
	<div class="seo-simple-code-snippets-help">
	<h3><?php _e( 'Code Snippet Examples', 'seo-simple' ); ?></h3>
	
	<h4><?php _e( 'Google Analytics Tracking Code', 'seo-simple' ); ?></h4>
	<p><em><?php _e( 'Best placed in Header', 'seo-simple' ); ?></em></p>
	<pre><code><!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_TRACKING_ID"></script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	gtag('config', 'GA_TRACKING_ID');
</script></code></pre>
	
	<h4><?php _e( 'Custom CSS', 'seo-simple' ); ?></h4>
	<p><em><?php _e( 'Best placed in Header', 'seo-simple' ); ?></em></p>
	<pre><code><style>
.custom-class {
	  color: #ff0000;
	  font-weight: bold;
}
</style></code></pre>
	
	<h4><?php _e( 'Facebook Pixel', 'seo-simple' ); ?></h4>
	<p><em><?php _e( 'Best placed in Header', 'seo-simple' ); ?></em></p>
	<pre><code><!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', 'YOUR_PIXEL_ID');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=YOUR_PIXEL_ID&ev=PageView&noscript=1"
/></noscript></code></pre>
	
	<h4><?php _e( 'Body Content Script', 'seo-simple' ); ?></h4>
	<p><em><?php _e( 'Best placed in Body', 'seo-simple' ); ?></em></p>
	<pre><code><!-- Example of a script that should run after body starts -->
<script>
// Initialize components that need the DOM to be ready
</script></code></pre>
	
	<h3><?php _e( 'Security Guidelines', 'seo-simple' ); ?></h3>
	<p><?php _e( 'For security reasons, the following code patterns are blocked:', 'seo-simple' ); ?></p>
	<ul>
		<li><?php _e( 'PHP code (<?php, ?>)', 'seo-simple' ); ?></li>
		<li><?php _e( 'Dangerous JavaScript functions (eval, Function, setTimeout with string)', 'seo-simple' ); ?></li>
		<li><?php _e( 'Document write methods (document.write, document.writeln)', 'seo-simple' ); ?></li>
		<li><?php _e( 'Direct HTML manipulation (innerHTML, outerHTML, insertAdjacentHTML)', 'seo-simple' ); ?></li>
		<li><?php _e( 'Cookie and storage manipulation', 'seo-simple' ); ?></li>
		<li><?php _e( 'Form submission and location manipulation', 'seo-simple' ); ?></li>
		<li><?php _e( 'Base64 encoding/decoding functions', 'seo-simple' ); ?></li>
		<li><?php _e( 'Non-HTTPS script sources', 'seo-simple' ); ?></li>
		<li><?php _e( 'Inline event handlers (onclick, onload, etc.)', 'seo-simple' ); ?></li>
		<li><?php _e( 'Data URLs and JavaScript pseudo-protocol', 'seo-simple' ); ?></li>
	</ul>
	<p><?php _e( 'If your legitimate code is being blocked, please contact the plugin administrator.', 'seo-simple' ); ?></p>
	</div>
</div>