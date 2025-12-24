<?php
/**
 * Bulk editor page template.
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get current post type
$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : 'post';

// Get available post types
$post_types = get_post_types( array( 'public' => true ), 'objects' );

// Get posts for the current post type
$posts_per_page = 50;
$paged = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;

$args = array(
	'post_type' => $post_type,
	'post_status' => 'publish',
	'posts_per_page' => $posts_per_page,
	'paged' => $paged,
	'orderby' => 'date',
	'order' => 'DESC',
);

$posts_query = new WP_Query( $args );
$total_posts = $posts_query->found_posts;
$total_pages = ceil( $total_posts / $posts_per_page );
?>

<div class="seo-simple-bulk-edit-form">
	<h3><?php _e( 'Bulk Edit SEO', 'seo-simple' ); ?></h3>
	<p><?php _e( 'Select posts below and update their SEO data in bulk.', 'seo-simple' ); ?></p>
	
	<form id="seo-simple-bulk-edit-form" method="post">
		<div class="seo-simple-filter-container">
			<label for="seo-simple-post-type"><?php _e( 'Post Type:', 'seo-simple' ); ?></label>
			<select id="seo-simple-post-type" name="post_type">
				<?php foreach ( $post_types as $pt ) : ?>
					<option value="<?php echo esc_attr( $pt->name ); ?>" <?php selected( $post_type, $pt->name ); ?>>
						<?php echo esc_html( $pt->labels->singular_name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			
			<label for="seo-simple-filter"><?php _e( 'Filter:', 'seo-simple' ); ?></label>
			<select id="seo-simple-filter">
				<option value="all"><?php _e( 'All Posts', 'seo-simple' ); ?></option>
				<option value="missing_title"><?php _e( 'Missing SEO Title', 'seo-simple' ); ?></option>
				<option value="missing_description"><?php _e( 'Missing SEO Description', 'seo-simple' ); ?></option>
				<option value="missing_keyword"><?php _e( 'Missing Focus Keyword', 'seo-simple' ); ?></option>
				<option value="low_score"><?php _e( 'Low SEO Score', 'seo-simple' ); ?></option>
			</select>
		</div>
		
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="seo-simple-bulk-title"><?php _e( 'SEO Title', 'seo-simple' ); ?></label>
					</th>
					<td>
						<input type="text" id="seo-simple-bulk-title" name="seo_title" class="regular-text" />
						<p class="description">
							<?php _e( 'Leave empty to keep current values. Use placeholders: %post_title%, %site_name%', 'seo-simple' ); ?>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="seo-simple-bulk-description"><?php _e( 'SEO Description', 'seo-simple' ); ?></label>
					</th>
					<td>
						<textarea id="seo-simple-bulk-description" name="seo_description" rows="4" class="large-text"></textarea>
						<p class="description">
							<?php _e( 'Leave empty to keep current values. Use placeholders: %post_title%, %post_content%', 'seo-simple' ); ?>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="seo-simple-bulk-keyword"><?php _e( 'Focus Keyword', 'seo-simple' ); ?></label>
					</th>
					<td>
						<input type="text" id="seo-simple-bulk-keyword" name="focus_keyword" class="regular-text" />
						<p class="description">
							<?php _e( 'Leave empty to keep current values.', 'seo-simple' ); ?>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="seo-simple-bulk-noindex"><?php _e( 'Robots Meta', 'seo-simple' ); ?></label>
					</th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="noindex" value="1" />
								<?php _e( 'Noindex', 'seo-simple' ); ?>
							</label>
							<br />
							<label>
								<input type="checkbox" name="nofollow" value="1" />
								<?php _e( 'Nofollow', 'seo-simple' ); ?>
							</label>
						</fieldset>
						<p class="description">
							<?php _e( 'Check to apply to selected posts. Uncheck to keep current values.', 'seo-simple' ); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		
		<p class="submit">
			<button type="submit" class="button button-primary" data-original-text="<?php esc_attr_e( 'Update SEO', 'seo-simple' ); ?>">
				<?php _e( 'Update SEO', 'seo-simple' ); ?>
			</button>
		</p>
	</form>
</div>

<div class="seo-simple-selection-info">
	<span class="selected-count"><?php echo esc_html( count( $posts_query->posts ) ); ?></span> <?php _e( 'posts shown', 'seo-simple' ); ?> | 
	<span id="seo-simple-selected-count">0</span> <?php _e( 'selected', 'seo-simple' ); ?>
</div>

<div class="seo-simple-post-list">
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" class="column-cb">
					<input type="checkbox" id="seo-simple-select-all" />
				</th>
				<th scope="col" class="column-title"><?php _e( 'Title', 'seo-simple' ); ?></th>
				<th scope="col" class="column-seo_title"><?php _e( 'SEO Title', 'seo-simple' ); ?></th>
				<th scope="col" class="column-seo_description"><?php _e( 'SEO Description', 'seo-simple' ); ?></th>
				<th scope="col" class="column-seo_focus_keyword"><?php _e( 'Focus Keyword', 'seo-simple' ); ?></th>
				<th scope="col" class="column-seo_score"><?php _e( 'SEO Score', 'seo-simple' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( $posts_query->have_posts() ) : ?>
				<?php while ( $posts_query->have_posts() ) : $posts_query->the_post(); ?>
					<?php
					$post_id = get_the_ID();
					$seo_title = $this->seo_meta->get_title( $post_id );
					$seo_description = $this->seo_meta->get_description( $post_id );
					$focus_keyword = $this->seo_meta->get_focus_keyword( $post_id );
					$seo_score = $this->seo_meta->get_overall_seo_score( $post_id );
					
					// Add WooCommerce fields if applicable
					if ( $post_type === 'product' ) {
						$product_title = get_post_meta( $post_id, '_seo_simple_product_title', true );
						$product_description = get_post_meta( $post_id, '_seo_simple_product_description', true );
						$product_keyword = get_post_meta( $post_id, '_seo_simple_product_focus_keyword', true );
						
						if ( ! $seo_title && $product_title ) {
							$seo_title = $product_title;
						}
						if ( ! $seo_description && $product_description ) {
							$seo_description = $product_description;
						}
						if ( ! $focus_keyword && $product_keyword ) {
							$focus_keyword = $product_keyword;
						}
					}
					?>
					<tr class="seo-simple-post-row" 
						data-post-id="<?php echo esc_attr( $post_id ); ?>"
						data-has-title="<?php echo esc_attr( $seo_title ? '1' : '0' ); ?>"
						data-has-description="<?php echo esc_attr( $seo_description ? '1' : '0' ); ?>"
						data-has-keyword="<?php echo esc_attr( $focus_keyword ? '1' : '0' ); ?>"
						data-score="<?php echo esc_attr( is_array( $seo_score ) ? implode( ',', $seo_score ) : $seo_score ); ?>">
						<th scope="row" class="column-cb">
							<input type="checkbox" name="post_ids[]" value="<?php echo esc_attr( $post_id ); ?>" class="seo-simple-post-checkbox" />
						</th>
						<td class="column-title">
							<strong><a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>"><?php the_title(); ?></a></strong>
							<div class="row-actions">
								<span class="edit"><a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>"><?php _e( 'Edit', 'seo-simple' ); ?></a> | </span>
								<span class="view"><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php _e( 'View', 'seo-simple' ); ?></a></span>
							</div>
						</td>
						<td class="column-seo_title">
							<?php if ( $seo_title && is_string( $seo_title ) ) : ?>
								<?php echo esc_html( substr( $seo_title, 0, 50 ) . ( strlen( $seo_title ) > 50 ? '...' : '' ) ); ?>
							<?php else : ?>
								<span class="seo-simple-missing"><?php _e( 'Default', 'seo-simple' ); ?></span>
							<?php endif; ?>
						</td>
						<td class="column-seo_description">
							<?php if ( $seo_description && is_string( $seo_description ) ) : ?>
								<?php echo esc_html( substr( $seo_description, 0, 50 ) . ( strlen( $seo_description ) > 50 ? '...' : '' ) ); ?>
							<?php else : ?>
								<span class="seo-simple-missing"><?php _e( 'Default', 'seo-simple' ); ?></span>
							<?php endif; ?>
						</td>
						<td class="column-seo_focus_keyword">
							<?php if ( $focus_keyword ) : ?>
								<?php echo esc_html( $focus_keyword ); ?>
							<?php else : ?>
								<span class="seo-simple-missing"><?php _e( 'Not set', 'seo-simple' ); ?></span>
							<?php endif; ?>
						</td>
						<td class="column-seo_score">
							<?php
							$score_value = is_array( $seo_score ) ? ( isset( $seo_score[0] ) ? (int) $seo_score[0] : 0 ) : (int) $seo_score;
							$score_display = is_array( $seo_score ) ? implode( ',', $seo_score ) : $seo_score;
							?>
							<span class="seo-simple-score seo-simple-score-<?php echo $score_value >= 80 ? 'green' : ( $score_value >= 50 ? 'orange' : 'red' ); ?>">
								<?php echo esc_html( $score_display ); ?>
							</span>
						</td>
					</tr>
				<?php endwhile; ?>
			<?php else : ?>
				<tr>
					<td colspan="6"><?php _e( 'No posts found.', 'seo-simple' ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
	
	<?php if ( $total_pages > 1 ) : ?>
	<div class="tablenav bottom">
		<div class="tablenav-pages">
			<span class="displaying-num">
				<?php
				printf(
					_n( '%s item', '%s items', $total_posts, 'seo-simple' ),
					number_format_i18n( $total_posts )
				);
				?>
			</span>
			<span class="pagination-links">
				<?php
				$current_url = remove_query_arg( 'paged', $_SERVER['REQUEST_URI'] );
				
				if ( $paged > 1 ) {
					$prev_url = add_query_arg( 'paged', $paged - 1, $current_url );
					echo '<a class="prev-page" href="' . esc_url( $prev_url ) . '">&laquo;</a>';
				}
				
				for ( $i = 1; $i <= $total_pages; $i++ ) {
					if ( $i == $paged ) {
						echo '<span class="paging-input">' . $i . '</span>';
					} else {
						$page_url = add_query_arg( 'paged', $i, $current_url );
						echo '<a class="page-numbers" href="' . esc_url( $page_url ) . '">' . $i . '</a>';
					}
				}
				
				if ( $paged < $total_pages ) {
					$next_url = add_query_arg( 'paged', $paged + 1, $current_url );
					echo '<a class="next-page" href="' . esc_url( $next_url ) . '">&raquo;</a>';
				}
				?>
			</span>
		</div>
	</div>
	<?php endif; ?>
</div>

<div class="seo-simple-bulk-actions">
	<button type="button" id="seo-simple-bulk-analyze" class="button">
		<?php _e( 'Analyze SEO', 'seo-simple' ); ?>
	</button>
	
	<button type="button" id="seo-simple-bulk-optimize" class="button">
		<?php _e( 'Auto Optimize SEO', 'seo-simple' ); ?>
	</button>
</div>

<div id="seo-simple-bulk-results" class="seo-simple-results" style="display: none;"></div>

<?php wp_reset_postdata(); ?>