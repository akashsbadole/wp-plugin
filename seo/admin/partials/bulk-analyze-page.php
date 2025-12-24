<?php
/**
 * Bulk analyze page template.
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

<div class="seo-simple-bulk-analyze-form">
	<h3><?php _e( 'Bulk SEO Analysis', 'seo-simple' ); ?></h3>
	<p><?php _e( 'Analyze SEO performance for multiple posts at once. Select posts below and run comprehensive SEO analysis.', 'seo-simple' ); ?></p>
	
	<div class="seo-simple-filter-container">
		<label for="seo-simple-analyze-post-type"><?php _e( 'Post Type:', 'seo-simple' ); ?></label>
		<select id="seo-simple-analyze-post-type">
			<?php foreach ( $post_types as $pt ) : ?>
				<option value="<?php echo esc_attr( $pt->name ); ?>" <?php selected( $post_type, $pt->name ); ?>>
					<?php echo esc_html( $pt->labels->singular_name ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		
		<label for="seo-simple-analyze-filter"><?php _e( 'Filter:', 'seo-simple' ); ?></label>
		<select id="seo-simple-analyze-filter">
			<option value="all"><?php _e( 'All Posts', 'seo-simple' ); ?></option>
			<option value="not_analyzed"><?php _e( 'Not Analyzed Recently', 'seo-simple' ); ?></option>
			<option value="low_score"><?php _e( 'Low SEO Score', 'seo-simple' ); ?></option>
			<option value="missing_meta"><?php _e( 'Missing Meta Data', 'seo-simple' ); ?></option>
			<option value="no_focus_keyword"><?php _e( 'No Focus Keyword', 'seo-simple' ); ?></option>
		</select>
		
		<button type="button" id="seo-simple-analyze-filter-btn" class="button">
			<?php _e( 'Apply Filter', 'seo-simple' ); ?>
		</button>
	</div>
	
	<div class="seo-simple-analysis-options">
		<h4><?php _e( 'Analysis Options', 'seo-simple' ); ?></h4>
		<fieldset>
			<label>
				<input type="checkbox" id="analyze-title" checked />
				<?php _e( 'Title Analysis', 'seo-simple' ); ?>
			</label>
			<label>
				<input type="checkbox" id="analyze-description" checked />
				<?php _e( 'Description Analysis', 'seo-simple' ); ?>
			</label>
			<label>
				<input type="checkbox" id="analyze-content" checked />
				<?php _e( 'Content Analysis', 'seo-simple' ); ?>
			</label>
			<label>
				<input type="checkbox" id="analyze-keywords" checked />
				<?php _e( 'Keyword Analysis', 'seo-simple' ); ?>
			</label>
			<label>
				<input type="checkbox" id="analyze-readability" checked />
				<?php _e( 'Readability Analysis', 'seo-simple' ); ?>
			</label>
			<label>
				<input type="checkbox" id="analyze-schema" />
				<?php _e( 'Schema Analysis', 'seo-simple' ); ?>
			</label>
		</fieldset>
	</div>
</div>

<div class="seo-simple-selection-info">
	<span class="selected-count"><?php echo esc_html( count( $posts_query->posts ) ); ?></span> <?php _e( 'posts shown', 'seo-simple' ); ?> | 
	<span id="seo-simple-analyze-selected-count">0</span> <?php _e( 'selected', 'seo-simple' ); ?>
</div>

<div class="seo-simple-post-list">
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" class="column-cb">
					<input type="checkbox" id="seo-simple-analyze-select-all" />
				</th>
				<th scope="col" class="column-title"><?php _e( 'Title', 'seo-simple' ); ?></th>
				<th scope="col" class="column-seo_score"><?php _e( 'SEO Score', 'seo-simple' ); ?></th>
				<th scope="col" class="column-readability"><?php _e( 'Readability', 'seo-simple' ); ?></th>
				<th scope="col" class="column-last_analyzed"><?php _e( 'Last Analyzed', 'seo-simple' ); ?></th>
				<th scope="col" class="column-issues"><?php _e( 'Issues Found', 'seo-simple' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( $posts_query->have_posts() ) : ?>
				<?php while ( $posts_query->have_posts() ) : $posts_query->the_post(); ?>
					<?php
					$post_id = get_the_ID();
					$seo_score = $this->seo_meta->get_seo_score( $post_id );
					$readability_score = $this->seo_meta->get_readability_score( $post_id );
					$last_analyzed = get_post_meta( $post_id, '_seo_simple_last_analyzed', true );
					$issues = get_post_meta( $post_id, '_seo_simple_analysis_issues', true );
					$issue_count = is_array( $issues ) ? count( $issues ) : 0;
					?>
					<tr class="seo-simple-analyze-row"
						data-post-id="<?php echo esc_attr( $post_id ); ?>"
						data-score="<?php echo esc_attr( $seo_score ); ?>">
						<th scope="row" class="column-cb">
							<input type="checkbox" name="analyze_post_ids[]" value="<?php echo esc_attr( $post_id ); ?>" class="seo-simple-analyze-checkbox" />
						</th>
						<td class="column-title">
							<strong><a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>"><?php the_title(); ?></a></strong>
							<div class="row-actions">
								<span class="edit"><a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>"><?php _e( 'Edit', 'seo-simple' ); ?></a> | </span>
								<span class="view"><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php _e( 'View', 'seo-simple' ); ?></a></span>
							</div>
						</td>
						<td class="column-seo_score">
							<span class="seo-simple-score seo-simple-score-<?php echo $seo_score >= 80 ? 'green' : ( $seo_score >= 50 ? 'orange' : 'red' ); ?>">
								<?php echo esc_html( $seo_score ); ?>
							</span>
						</td>
						<td class="column-readability">
							<span class="seo-simple-score seo-simple-score-<?php echo $readability_score >= 80 ? 'green' : ( $readability_score >= 50 ? 'orange' : 'red' ); ?>">
								<?php echo esc_html( $readability_score ); ?>
							</span>
						</td>
						<td class="column-last_analyzed">
							<?php if ( $last_analyzed ) : ?>
								<?php echo esc_html( human_time_diff( strtotime( $last_analyzed ), current_time( 'timestamp' ) ) ) . ' ' . __( 'ago', 'seo-simple' ); ?>
							<?php else : ?>
								<span class="seo-simple-missing"><?php _e( 'Never', 'seo-simple' ); ?></span>
							<?php endif; ?>
						</td>
						<td class="column-issues">
							<?php if ( $issue_count > 0 ) : ?>
								<span class="seo-simple-issue-count" style="color: #dc3232; font-weight: bold;">
									<?php echo esc_html( $issue_count ); ?>
								</span>
							<?php else : ?>
								<span style="color: #46b450;"><?php _e( 'None', 'seo-simple' ); ?></span>
							<?php endif; ?>
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

<div class="seo-simple-bulk-analyze-actions">
	<button type="button" id="seo-simple-bulk-analyze-btn" class="button button-primary" data-original-text="<?php esc_attr_e( 'Analyze Selected', 'seo-simple' ); ?>">
		<?php _e( 'Analyze Selected', 'seo-simple' ); ?>
	</button>
	
	<button type="button" id="seo-simple-analyze-all-btn" class="button">
		<?php _e( 'Analyze All', 'seo-simple' ); ?>
	</button>
</div>

<div id="seo-simple-analyze-results" class="seo-simple-results" style="display: none;"></div>

<div class="seo-simple-analysis-progress" id="seo-simple-analysis-progress" style="display: none;">
	<h4><?php _e( 'Analysis Progress', 'seo-simple' ); ?></h4>
	<div class="progress-bar">
		<div class="progress-bar-fill" style="width: 0%;"></div>
	</div>
	<div class="progress-text">
		<span class="current-post">0</span> / <span class="total-posts">0</span> <?php _e( 'posts analyzed', 'seo-simple' ); ?>
	</div>
	<div class="current-analyzing">
		<?php _e( 'Currently analyzing:', 'seo-simple' ); ?> <span class="post-title"></span>
	</div>
</div>

<?php wp_reset_postdata(); ?>
