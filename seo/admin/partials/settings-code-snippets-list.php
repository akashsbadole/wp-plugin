<?php
/**
 * Provide a view for the code snippets list in settings
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

?>

<?php if ( ! empty( $message ) ) : ?>
	<div class="notice notice-success is-dismissible">
		<p><?php echo esc_html( $message ); ?></p>
	</div>
<?php endif; ?>

<div class="seo-simple-code-snippets-header">
	<a href="<?php echo esc_url( admin_url( 'options-general.php?page=seo-simple-settings&tab=code-snippets&action=add' ) ); ?>" class="page-title-action">
		<?php _e( 'Add New Snippet', 'seo-simple' ); ?>
	</a>
</div>

<div class="seo-simple-code-snippets-quick-add">
	<h3><?php _e( 'Quick Add Common Scripts', 'seo-simple' ); ?></h3>
	<p><?php _e( 'Quickly add common tracking scripts to your site:', 'seo-simple' ); ?></p>
	<div class="quick-add-buttons">
		<a href="<?php echo esc_url( admin_url( 'options-general.php?page=seo-simple-settings&tab=code-snippets&action=add&preset=google-analytics' ) ); ?>" class="button button-secondary">
			<?php _e( 'Add Google Analytics', 'seo-simple' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'options-general.php?page=seo-simple-settings&tab=code-snippets&action=add&preset=facebook-pixel' ) ); ?>" class="button button-secondary">
			<?php _e( 'Add Facebook Pixel', 'seo-simple' ); ?>
		</a>
	</div>
</div>

<div class="seo-simple-code-snippets-container">
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" class="manage-column column-name"><?php _e( 'Name', 'seo-simple' ); ?></th>
				<th scope="col" class="manage-column column-location"><?php _e( 'Location', 'seo-simple' ); ?></th>
				<th scope="col" class="manage-column column-status"><?php _e( 'Status', 'seo-simple' ); ?></th>
				<th scope="col" class="manage-column column-priority"><?php _e( 'Priority', 'seo-simple' ); ?></th>
				<th scope="col" class="manage-column column-date"><?php _e( 'Updated', 'seo-simple' ); ?></th>
				<th scope="col" class="manage-column column-actions"><?php _e( 'Actions', 'seo-simple' ); ?></th>
			</tr>
		</thead>
		
		<tbody>
			<?php if ( ! empty( $snippets ) ) : ?>
				<?php foreach ( $snippets as $snippet ) : ?>
					<tr>
						<td class="column-name">
							<strong><?php echo esc_html( $snippet->name ); ?></strong>
							<div class="row-actions visible">
								<span class="edit">
									<a href="<?php echo esc_url( admin_url( 'options-general.php?page=seo-simple-settings&tab=code-snippets&action=edit&id=' . $snippet->id ) ); ?>">
										<?php _e( 'Edit', 'seo-simple' ); ?>
									</a> |
								</span>
								<span class="delete">
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'options-general.php?page=seo-simple-settings&tab=code-snippets&action=delete_snippet&id=' . $snippet->id ), 'seo_simple_delete_snippet_' . $snippet->id ) ); ?>" 
									   onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this snippet?', 'seo-simple' ) ); ?>');">
										<?php _e( 'Delete', 'seo-simple' ); ?>
									</a>
								</span>
							</div>
						</td>
						<td class="column-location">
							<?php
							$location_labels = array(
								'header' => __( 'Header', 'seo-simple' ),
								'body' => __( 'Body', 'seo-simple' ),
								'footer' => __( 'Footer', 'seo-simple' ),
							);
							echo isset( $location_labels[$snippet->location] ) ? esc_html( $location_labels[$snippet->location] ) : esc_html( $snippet->location );
							?>
						</td>
						<td class="column-status">
							<?php 
							$status_class = $snippet->status === 'active' ? 'active' : 'inactive';
							$status_text = $snippet->status === 'active' ? __( 'Active', 'seo-simple' ) : __( 'Inactive', 'seo-simple' );
							?>
							<span class="status-<?php echo esc_attr( $status_class ); ?>">
								<?php echo esc_html( $status_text ); ?>
							</span>
						</td>
						<td class="column-priority">
							<?php echo esc_html( $snippet->priority ); ?>
						</td>
						<td class="column-date">
							<?php 
							$updated_time = strtotime( $snippet->updated_at );
							echo esc_html( date_i18n( get_option( 'date_format' ), $updated_time ) );
							?>
						</td>
						<td class="column-actions">
							<a href="<?php echo esc_url( admin_url( 'options-general.php?page=seo-simple-settings&tab=code-snippets&action=edit&id=' . $snippet->id ) ); ?>" 
							   class="button button-small"><?php _e( 'Edit', 'seo-simple' ); ?></a>
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'options-general.php?page=seo-simple-settings&tab=code-snippets&action=delete_snippet&id=' . $snippet->id ), 'seo_simple_delete_snippet_' . $snippet->id ) ); ?>" 
							   class="button button-small" 
							   onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this snippet?', 'seo-simple' ) ); ?>');">
								<?php _e( 'Delete', 'seo-simple' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="6">
						<?php _e( 'No code snippets found. Would you like to add one?', 'seo-simple' ); ?>
						<a href="<?php echo esc_url( admin_url( 'options-general.php?page=seo-simple-settings&tab=code-snippets&action=add' ) ); ?>">
							<?php _e( 'Add New Snippet', 'seo-simple' ); ?>
						</a>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>

<div class="seo-simple-code-snippets-help">
	<h3><?php _e( 'How to use Code Snippets', 'seo-simple' ); ?></h3>
	<p><?php _e( 'Code snippets allow you to add custom HTML, CSS, or JavaScript to your website without editing theme files.', 'seo-simple' ); ?></p>
	<ul>
		<li><?php _e( '<strong>Header:</strong> Code will be inserted in the <head> section of your site.', 'seo-simple' ); ?></li>
		<li><?php _e( '<strong>Body:</strong> Code will be inserted after the opening <body> tag.', 'seo-simple' ); ?></li>
		<li><?php _e( '<strong>Footer:</strong> Code will be inserted before the closing </body> tag.', 'seo-simple' ); ?></li>
		<li><?php _e( '<strong>Priority:</strong> Lower numbers load first. Use this to control the order of your snippets.', 'seo-simple' ); ?></li>
	</ul>
</div>