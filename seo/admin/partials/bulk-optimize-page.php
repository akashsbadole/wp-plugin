<?php
/**
 * Bulk optimize page template.
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

<div class="seo-simple-bulk-optimize-form">
	<h3><?php _e( 'Bulk SEO Optimization', 'seo-simple' ); ?></h3>
	<p><?php _e( 'Automatically optimize SEO elements for multiple posts. Select posts below and choose optimization options.', 'seo-simple' ); ?></p>
	
	<div class="seo-simple-filter-container">
		<label for="seo-simple-optimize-post-type"><?php _e( 'Post Type:', 'seo-simple' ); ?></label>
		<select id="seo-simple-optimize-post-type">
			<?php foreach ( $post_types as $pt ) : ?>
				<option value="<?php echo esc_attr( $pt->name ); ?>" <?php selected( $post_type, $pt->name ); ?>>
					<?php echo esc_html( $pt->labels->singular_name ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		
		<label for="seo-simple-optimize-filter"><?php _e( 'Filter:', 'seo-simple' ); ?></label>
		<select id="seo-simple-optimize-filter">
			<option value="all"><?php _e( 'All Posts', 'seo-simple' ); ?></option>
			<option value="missing_title"><?php _e( 'Missing SEO Title', 'seo-simple' ); ?></option>
			<option value="missing_description"><?php _e( 'Missing SEO Description', 'seo-simple' ); ?></option>
			<option value="low_score"><?php _e( 'Low SEO Score', 'seo-simple' ); ?></option>
			<option value="long_titles"><?php _e( 'Titles Too Long', 'seo-simple' ); ?></option>
			<option value="short_descriptions"><?php _e( 'Descriptions Too Short', 'seo-simple' ); ?></option>
		</select>
		
		<button type="button" id="seo-simple-optimize-filter-btn" class="button">
			<?php _e( 'Apply Filter', 'seo-simple' ); ?>
		</button>
	</div>
	
	<div class="seo-simple-optimization-options">
		<h4><?php _e( 'Optimization Options', 'seo-simple' ); ?></h4>
		
		<div class="optimization-section">
			<h5><?php _e( 'Title Optimization', 'seo-simple' ); ?></h5>
			<fieldset>
				<label>
					<input type="checkbox" id="optimize-generate-titles" />
					<?php _e( 'Auto-generate missing titles', 'seo-simple' ); ?>
				</label>
				<label>
					<input type="checkbox" id="optimize-truncate-titles" />
					<?php _e( 'Truncate long titles (60 chars)', 'seo-simple' ); ?>
				</label>
				<label>
					<input type="checkbox" id="optimize-add-keywords-titles" />
					<?php _e( 'Add focus keyword to titles', 'seo-simple' ); ?>
				</label>
			</fieldset>
		</div>
		
		<div class="optimization-section">
			<h5><?php _e( 'Description Optimization', 'seo-simple' ); ?></h5>
			<fieldset>
				<label>
					<input type="checkbox" id="optimize-generate-descriptions" />
					<?php _e( 'Auto-generate missing descriptions', 'seo-simple' ); ?>
				</label>
				<label>
					<input type="checkbox" id="optimize-truncate-descriptions" />
					<?php _e( 'Truncate long descriptions (160 chars)', 'seo-simple' ); ?>
				</label>
				<label>
					<input type="checkbox" id="optimize-expand-descriptions" />
					<?php _e( 'Expand short descriptions (120 chars min)', 'seo-simple' ); ?>
				</label>
			</fieldset>
		</div>
		
		<div class="optimization-section">
			<h5><?php _e( 'Content Optimization', 'seo-simple' ); ?></h5>
			<fieldset>
				<label>
					<input type="checkbox" id="optimize-keywords" />
					<?php _e( 'Suggest focus keywords', 'seo-simple' ); ?>
				</label>
				<label>
					<input type="checkbox" id="optimize-headings" />
					<?php _e( 'Optimize heading structure', 'seo-simple' ); ?>
				</label>
				<label>
					<input type="checkbox" id="optimize-images" />
					<?php _e( 'Add alt text to images', 'seo-simple' ); ?>
				</label>
			</fieldset>
		</div>
		
		<div class="optimization-section">
			<h5><?php _e( 'Schema & Meta', 'seo-simple' ); ?></h5>
			<fieldset>
				<label>
					<input type="checkbox" id="optimize-schema" />
					<?php _e( 'Generate schema markup', 'seo-simple' ); ?>
				</label>
				<label>
					<input type="checkbox" id="optimize-opengraph" />
					<?php _e( 'Optimize Open Graph tags', 'seo-simple' ); ?>
				</label>
				<label>
					<input type="checkbox" id="optimize-twitter" />
					<?php _e( 'Optimize Twitter Card tags', 'seo-simple' ); ?>
				</label>
			</fieldset>
		</div>
	</div>
	
	<div class="seo-simple-optimization-presets">
		<h4><?php _e( 'Quick Presets', 'seo-simple' ); ?></h4>
		<button type="button" class="button seo-simple-preset-btn" data-preset="basic">
			<?php _e( 'Basic Optimization', 'seo-simple' ); ?>
		</button>
		<button type="button" class="button seo-simple-preset-btn" data-preset="comprehensive">
			<?php _e( 'Comprehensive Optimization', 'seo-simple' ); ?>
		</button>
		<button type="button" class="button seo-simple-preset-btn" data-preset="content-focus">
			<?php _e( 'Content Focus', 'seo-simple' ); ?>
		</button>
	</div>
</div>

<div class="seo-simple-selection-info">
	<span class="selected-count"><?php echo esc_html( count( $posts_query->posts ) ); ?></span> <?php _e( 'posts shown', 'seo-simple' ); ?> | 
	<span id="seo-simple-optimize-selected-count">0</span> <?php _e( 'selected', 'seo-simple' ); ?>
</div>

<div class="seo-simple-post-list">
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" class="column-cb">
					<input type="checkbox" id="seo-simple-optimize-select-all" />
				</th>
				<th scope="col" class="column-title"><?php _e( 'Title', 'seo-simple' ); ?></th>
				<th scope="col" class="column-seo_title"><?php _e( 'SEO Title', 'seo-simple' ); ?></th>
				<th scope="col" class="column-seo_description"><?php _e( 'SEO Description', 'seo-simple' ); ?></th>
				<th scope="col" class="column-seo_score"><?php _e( 'SEO Score', 'seo-simple' ); ?></th>
				<th scope="col" class="column-optimization_potential"><?php _e( 'Potential', 'seo-simple' ); ?></th>
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
					
					// Calculate optimization potential
					$potential = 0;
					if ( ! $seo_title ) $potential += 30;
					if ( ! $seo_description ) $potential += 30;
					if ( ! $focus_keyword ) $potential += 20;
					if ( $seo_score < 50 ) $potential += 20;
					
					$potential_class = $potential >= 60 ? 'high' : ( $potential >= 30 ? 'medium' : 'low' );
					?>
					<tr class="seo-simple-optimize-row" 
						data-post-id="<?php echo esc_attr( $post_id ); ?>"
						data-score="<?php echo esc_attr( is_array( $seo_score ) ? implode( ',', $seo_score ) : $seo_score ); ?>"
						data-potential="<?php echo esc_attr( $potential ); ?>">
						<th scope="row" class="column-cb">
							<input type="checkbox" name="optimize_post_ids[]" value="<?php echo esc_attr( $post_id ); ?>" class="seo-simple-optimize-checkbox" />
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
								<br>
								<small class="seo-simple-char-count"><?php echo strlen( $seo_title ); ?> chars</small>
							<?php else : ?>
								<span class="seo-simple-missing"><?php _e( 'Missing', 'seo-simple' ); ?></span>
							<?php endif; ?>
						</td>
						<td class="column-seo_description">
							<?php if ( $seo_description && is_string( $seo_description ) ) : ?>
								<?php echo esc_html( substr( $seo_description, 0, 50 ) . ( strlen( $seo_description ) > 50 ? '...' : '' ) ); ?>
								<br>
								<small class="seo-simple-char-count"><?php echo strlen( $seo_description ); ?> chars</small>
							<?php else : ?>
								<span class="seo-simple-missing"><?php _e( 'Missing', 'seo-simple' ); ?></span>
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
						<td class="column-optimization_potential">
							<div class="seo-simple-potential-meter">
								<div class="potential-fill potential-<?php echo esc_attr( $potential_class ); ?>" style="width: <?php echo esc_attr( $potential ); ?>%;"></div>
							</div>
							<span class="potential-text"><?php echo esc_html( $potential ); ?>%</span>
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

<div class="seo-simple-bulk-optimize-actions">
	<button type="button" id="seo-simple-bulk-optimize-btn" class="button button-primary" data-original-text="<?php esc_attr_e( 'Optimize Selected', 'seo-simple' ); ?>">
		<?php _e( 'Optimize Selected', 'seo-simple' ); ?>
	</button>
	
	<button type="button" id="seo-simple-optimize-all-btn" class="button">
		<?php _e( 'Optimize All', 'seo-simple' ); ?>
	</button>
	
	<button type="button" id="seo-simple-preview-changes-btn" class="button">
		<?php _e( 'Preview Changes', 'seo-simple' ); ?>
	</button>
</div>

<div id="seo-simple-optimize-results" class="seo-simple-results" style="display: none;"></div>

<div class="seo-simple-optimization-progress" id="seo-simple-optimization-progress" style="display: none;">
	<h4><?php _e( 'Optimization Progress', 'seo-simple' ); ?></h4>
	<div class="progress-bar">
		<div class="progress-bar-fill" style="width: 0%;"></div>
	</div>
	<div class="progress-text">
		<span class="current-post">0</span> / <span class="total-posts">0</span> <?php _e( 'posts optimized', 'seo-simple' ); ?>
	</div>
	<div class="current-optimizing">
		<?php _e( 'Currently optimizing:', 'seo-simple' ); ?> <span class="post-title"></span>
	</div>
	<div class="optimization-summary">
		<div class="summary-item">
			<span class="label"><?php _e( 'Titles optimized:', 'seo-simple' ); ?></span>
			<span class="count titles-count">0</span>
		</div>
		<div class="summary-item">
			<span class="label"><?php _e( 'Descriptions optimized:', 'seo-simple' ); ?></span>
			<span class="count descriptions-count">0</span>
		</div>
		<div class="summary-item">
			<span class="label"><?php _e( 'Keywords added:', 'seo-simple' ); ?></span>
			<span class="count keywords-count">0</span>
		</div>
	</div>
</div>

<div id="seo-simple-preview-modal" class="seo-simple-modal" style="display: none;">
	<div class="modal-content">
		<div class="modal-header">
			<h3><?php _e( 'Optimization Preview', 'seo-simple' ); ?></h3>
			<button type="button" class="modal-close">&times;</button>
		</div>
		<div class="modal-body">
			<div id="preview-content"></div>
		</div>
		<div class="modal-footer">
			<button type="button" class="button modal-close"><?php _e( 'Close', 'seo-simple' ); ?></button>
		</div>
	</div>
</div>

<?php wp_reset_postdata(); ?>
