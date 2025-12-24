<?php
/**
 * Bulk editor table partial for SEO Simple plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Get available post types
$post_types = get_post_types( array( 'public' => true, 'show_ui' => true ), 'objects' );
$available_types = array();
foreach ( $post_types as $post_type ) {
	if ( $post_type->name !== 'attachment' ) {
		$available_types[$post_type->name] = $post_type->labels->singular_name;
	}
}
?>

<tbody>
	<?php if ( empty( $posts ) ) : ?>
		<tr>
			<td colspan="8" class="seo-simple-no-posts">
				<?php _e( 'No posts found matching your filters. Try adjusting your search criteria.', 'seo-simple' ); ?>
			</td>
		</tr>
	<?php else : ?>
		<?php foreach ( $posts as $post ) : ?>
			<tr>
				<th scope="row" class="check-column">
					<input type="checkbox" name="posts[]" value="<?php echo esc_attr( $post['ID'] ); ?>" class="seo-simple-post-checkbox" />
				</th>
				<td>
					<strong>
						<a href="<?php echo esc_url( $post['edit_link'] ); ?>" target="_blank">
							<?php echo esc_html( $post['title'] ); ?>
						</a>
					</strong>
					<div class="seo-simple-post-actions">
						<a href="<?php echo esc_url( $post['view_link'] ); ?>" target="_blank" class="seo-simple-view-link">
							<?php _e( 'View', 'seo-simple' ); ?>
						</a>
					</div>
				</td>
				<td><?php echo isset( $available_types[$post['type']] ) ? esc_html( $available_types[$post['type']] ) : esc_html( $post['type'] ); ?></td>
				<td>
					<div class="seo-simple-field-group">
						<input type="text" 
							   name="seo_title[<?php echo esc_attr( $post['ID'] ); ?>]" 
							   class="seo-simple-title-field seo-simple-field" 
							   value="<?php echo esc_attr( $post['seo_title'] ); ?>"
							   placeholder="<?php _e( 'SEO Title', 'seo-simple' ); ?>"
							   maxlength="60" />
						<div class="seo-simple-char-count">
							<span class="seo-simple-length"><?php echo strlen( $post['seo_title'] ); ?></span>/60
						</div>
						<button type="button" class="button button-small seo-simple-generate-title" data-post-id="<?php echo esc_attr( $post['ID'] ); ?>">
							<?php _e( 'Auto', 'seo-simple' ); ?>
						</button>
					</div>
				</td>
				<td>
					<div class="seo-simple-field-group">
						<textarea name="seo_description[<?php echo esc_attr( $post['ID'] ); ?>]" 
								  class="seo-simple-description-field seo-simple-field" 
								  placeholder="<?php _e( 'SEO Description', 'seo-simple' ); ?>"
								  maxlength="160"><?php echo esc_textarea( $post['seo_description'] ); ?></textarea>
						<div class="seo-simple-char-count">
							<span class="seo-simple-length"><?php echo strlen( $post['seo_description'] ); ?></span>/160
						</div>
						<button type="button" class="button button-small seo-simple-generate-description" data-post-id="<?php echo esc_attr( $post['ID'] ); ?>">
							<?php _e( 'Auto', 'seo-simple' ); ?>
						</button>
					</div>
				</td>
				<td>
					<div class="seo-simple-field-group">
						<input type="text" 
							   name="focus_keyword[<?php echo esc_attr( $post['ID'] ); ?>]" 
							   class="seo-simple-keyword-field seo-simple-field" 
							   value="<?php echo esc_attr( $post['focus_keyword'] ); ?>"
							   placeholder="<?php _e( 'Focus Keyword', 'seo-simple' ); ?>" />
					</div>
				</td>
				<td class="seo-simple-technical-fields">
					<div class="seo-simple-field-group">
						<label>
							<input type="checkbox" 
								   name="noindex[<?php echo esc_attr( $post['ID'] ); ?>]" 
								   <?php checked( $post['noindex'] ); ?> />
							<?php _e( 'No Index', 'seo-simple' ); ?>
						</label>
						<label>
							<input type="checkbox" 
								   name="nofollow[<?php echo esc_attr( $post['ID'] ); ?>]" 
								   <?php checked( $post['nofollow'] ); ?> />
							<?php _e( 'No Follow', 'seo-simple' ); ?>
						</label>
					</div>
				</td>
				<td>
					<div class="seo-simple-field-actions">
						<input type="hidden" name="nonce[<?php echo esc_attr( $post['ID'] ); ?>]" value="<?php echo wp_create_nonce( 'seo_simple_bulk_edit_' . $post['ID'] ); ?>" />
						<button type="button" class="button button-small seo-simple-save-row" data-post-id="<?php echo esc_attr( $post['ID'] ); ?>">
							<?php _e( 'Save', 'seo-simple' ); ?>
						</button>
					</div>
				</td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
</tbody>