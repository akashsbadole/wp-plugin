<?php
/**
 * WooCommerce SEO dashboard widget template.
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="seo-simple-woocommerce-dashboard">
	<div class="seo-simple-dashboard-stats">
		<div class="seo-simple-stat">
			<div class="seo-simple-stat-number"><?php echo esc_html( $total_products ); ?></div>
			<div class="seo-simple-stat-label"><?php _e( 'Total Products', 'seo-simple' ); ?></div>
		</div>
		
		<div class="seo-simple-stat">
			<div class="seo-simple-stat-number seo-simple-stat-<?php echo $products_with_seo > 0 ? 'good' : 'warning'; ?>">
				<?php echo esc_html( $products_with_seo ); ?>
			</div>
			<div class="seo-simple-stat-label"><?php _e( 'Products with SEO', 'seo-simple' ); ?></div>
		</div>
		
		<div class="seo-simple-stat">
			<div class="seo-simple-stat-number seo-simple-stat-<?php echo $products_missing_seo === 0 ? 'good' : 'bad'; ?>">
				<?php echo esc_html( $products_missing_seo ); ?>
			</div>
			<div class="seo-simple-stat-label"><?php _e( 'Missing SEO', 'seo-simple' ); ?></div>
		</div>
		
		<div class="seo-simple-stat">
			<div class="seo-simple-stat-number">
				<?php 
				$percentage = $total_products > 0 ? round( ( $products_with_seo / $total_products ) * 100 ) : 0;
				echo esc_html( $percentage . '%' );
				?>
			</div>
			<div class="seo-simple-stat-label"><?php _e( 'Optimization', 'seo-simple' ); ?></div>
		</div>
	</div>
	
	<div class="seo-simple-dashboard-categories">
		<div class="seo-simple-stat">
			<div class="seo-simple-stat-number"><?php echo esc_html( $total_categories ); ?></div>
			<div class="seo-simple-stat-label"><?php _e( 'Categories', 'seo-simple' ); ?></div>
		</div>
		
		<div class="seo-simple-stat">
			<div class="seo-simple-stat-number seo-simple-stat-<?php echo $categories_with_seo > 0 ? 'good' : 'warning'; ?>">
				<?php echo esc_html( $categories_with_seo ); ?>
			</div>
			<div class="seo-simple-stat-label"><?php _e( 'With SEO', 'seo-simple' ); ?></div>
		</div>
		
		<div class="seo-simple-stat">
			<div class="seo-simple-stat-number seo-simple-stat-<?php echo $categories_missing_seo === 0 ? 'good' : 'bad'; ?>">
				<?php echo esc_html( $categories_missing_seo ); ?>
			</div>
			<div class="seo-simple-stat-label"><?php _e( 'Missing SEO', 'seo-simple' ); ?></div>
		</div>
	</div>
	
	<div class="seo-simple-dashboard-actions">
		<?php if ( $products_missing_seo > 0 ) : ?>
			<p>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product&seo_status=missing' ) ); ?>" class="button">
					<?php _e( 'Fix Products Missing SEO', 'seo-simple' ); ?>
				</a>
			</p>
		<?php endif; ?>
		
		<?php if ( $categories_missing_seo > 0 ) : ?>
			<p>
				<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=product_cat&post_type=product&seo_status=missing' ) ); ?>" class="button">
					<?php _e( 'Fix Categories Missing SEO', 'seo-simple' ); ?>
				</a>
			</p>
		<?php endif; ?>
		
		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=seo-simple-settings&tab=woocommerce' ) ); ?>" class="button button-primary">
				<?php _e( 'WooCommerce SEO Settings', 'seo-simple' ); ?>
			</a>
		</p>
	</div>
	
	<div class="seo-simple-dashboard-recent">
		<h4><?php _e( 'Recently Updated Products', 'seo-simple' ); ?></h4>
		<ul>
			<?php
			$recent_products = get_posts( array(
				'post_type' => 'product',
				'post_status' => 'publish',
				'posts_per_page' => 5,
				'orderby' => 'modified',
				'order' => 'DESC',
			) );
			
			if ( $recent_products ) :
				foreach ( $recent_products as $product ) :
					$seo_score = 0; // This would be calculated in a real implementation
					?>
					<li>
						<a href="<?php echo esc_url( get_edit_post_link( $product->ID ) ); ?>">
							<?php echo esc_html( $product->post_title ); ?>
						</a>
						<span class="seo-simple-score seo-simple-score-<?php echo $seo_score >= 80 ? 'good' : ( $seo_score >= 50 ? 'warning' : 'bad' ); ?>">
							<?php echo esc_html( $seo_score ); ?>
						</span>
					</li>
					<?php
				endforeach;
			else :
				?>
				<li><?php _e( 'No products found.', 'seo-simple' ); ?></li>
				<?php
			endif;
			?>
		</ul>
	</div>
</div>

<style>
.seo-simple-woocommerce-dashboard {
	padding: 10px 0;
}

.seo-simple-dashboard-stats,
.seo-simple-dashboard-categories {
	display: flex;
	justify-content: space-between;
	margin-bottom: 15px;
}

.seo-simple-stat {
	text-align: center;
	flex: 1;
}

.seo-simple-stat-number {
	font-size: 1.5em;
	font-weight: bold;
	color: #23282d;
}

.seo-simple-stat-good {
	color: #46b450;
}

.seo-simple-stat-warning {
	color: #ffb900;
}

.seo-simple-stat-bad {
	color: #dc3232;
}

.seo-simple-stat-label {
	font-size: 0.8em;
	color: #666;
}

.seo-simple-dashboard-actions {
	margin: 15px 0;
	padding-top: 15px;
	border-top: 1px solid #eee;
}

.seo-simple-dashboard-actions p {
	margin: 5px 0;
}

.seo-simple-dashboard-recent h4 {
	margin: 15px 0 10px;
	font-size: 1em;
}

.seo-simple-dashboard-recent ul {
	margin: 0;
	padding-left: 20px;
}

.seo-simple-dashboard-recent li {
	margin-bottom: 5px;
}

.seo-simple-score {
	display: inline-block;
	width: 24px;
	height: 24px;
	line-height: 24px;
	text-align: center;
	border-radius: 50%;
	font-size: 0.7em;
	margin-left: 5px;
	color: #fff;
}

.seo-simple-score-good {
	background-color: #46b450;
}

.seo-simple-score-warning {
	background-color: #ffb900;
}

.seo-simple-score-bad {
	background-color: #dc3232;
}
</style>