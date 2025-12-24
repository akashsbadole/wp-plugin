<?php

/**
 * Handles XML sitemap generation for the SEO plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles XML sitemap generation.
 *
 * This class handles all operations related to XML sitemap generation including
 * creating sitemaps for posts, pages, categories, and managing sitemap index.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Sitemap {

	/**
	 * The sitemap cache expiration time in seconds.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      int    $cache_expiration    The cache expiration time.
	 */
	private $cache_expiration = 3600; // 1 hour

	/**
	 * Constructor.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Hook into init to register rewrite rules
		add_action( 'init', array( $this, 'register_rewrite_rules' ) );
		
		// Hook into template_redirect to handle sitemap requests
		add_action( 'template_redirect', array( $this, 'handle_sitemap_request' ) );
		
		// Hook into publish post to clear cache
		add_action( 'publish_post', array( $this, 'clear_sitemap_cache' ) );
		add_action( 'publish_page', array( $this, 'clear_sitemap_cache' ) );
		add_action( 'save_post', array( $this, 'clear_sitemap_cache' ) );
	}

	/**
	 * Register rewrite rules for sitemaps.
	 *
	 * @since    1.0.0
	 */
	public function register_rewrite_rules() {
		// Add rewrite rule for sitemap_index.xml
		add_rewrite_rule( '^sitemap_index\.xml$', 'index.php?seo_sitemap=index', 'top' );
		
		// Add rewrite rule for post sitemap
		add_rewrite_rule( '^sitemap_posts\.xml$', 'index.php?seo_sitemap=posts', 'top' );
		
		// Add rewrite rule for page sitemap
		add_rewrite_rule( '^sitemap_pages\.xml$', 'index.php?seo_sitemap=pages', 'top' );
		
		// Add rewrite rule for category sitemap
		add_rewrite_rule( '^sitemap_categories\.xml$', 'index.php?seo_sitemap=categories', 'top' );
		
		// Add query var
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
	}

	/**
	 * Add custom query vars for sitemap handling.
	 *
	 * @since    1.0.0
	 * @param    array    $query_vars    The existing query vars.
	 * @return   array                   The modified query vars.
	 */
	public function add_query_vars( $query_vars ) {
		$query_vars[] = 'seo_sitemap';
		return $query_vars;
	}

	/**
	 * Handle sitemap requests.
	 *
	 * @since    1.0.0
	 */
	public function handle_sitemap_request() {
		$sitemap_type = get_query_var( 'seo_sitemap' );
		
		if ( empty( $sitemap_type ) ) {
			return;
		}
		
		// Set appropriate headers
		header( 'Content-Type: application/xml; charset=UTF-8' );
		header( 'X-Robots-Tag: noindex, follow' );
		
		// Generate and output the requested sitemap
		switch ( $sitemap_type ) {
			case 'index':
				$this->generate_sitemap_index();
				break;
			case 'posts':
				$this->generate_posts_sitemap();
				break;
			case 'pages':
				$this->generate_pages_sitemap();
				break;
			case 'categories':
				$this->generate_categories_sitemap();
				break;
		}
		
		exit;
	}

	/**
	 * Generate sitemap index.
	 *
	 * @since    1.0.0
	 */
	public function generate_sitemap_index() {
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		$cache_key = 'seo_simple_sitemap_index';
		
		$xml = $cache_manager->remember( $cache_key, function() {
			// Get site URL
			$site_url = get_site_url();
			$lastmod = current_time( 'Y-m-d\TH:i:s\Z' );
			
			// Start XML output
			$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			$xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
			
			// Add posts sitemap
			$xml .= "\t" . '<sitemap>' . "\n";
			$xml .= "\t\t" . '<loc>' . esc_url( $site_url . '/sitemap_posts.xml' ) . '</loc>' . "\n";
			$xml .= "\t\t" . '<lastmod>' . esc_html( $lastmod ) . '</lastmod>' . "\n";
			$xml .= "\t" . '</sitemap>' . "\n";
			
			// Add pages sitemap
			$xml .= "\t" . '<sitemap>' . "\n";
			$xml .= "\t\t" . '<loc>' . esc_url( $site_url . '/sitemap_pages.xml' ) . '</loc>' . "\n";
			$xml .= "\t\t" . '<lastmod>' . esc_html( $lastmod ) . '</lastmod>' . "\n";
			$xml .= "\t" . '</sitemap>' . "\n";
			
			// Add categories sitemap
			$xml .= "\t" . '<sitemap>' . "\n";
			$xml .= "\t\t" . '<loc>' . esc_url( $site_url . '/sitemap_categories.xml' ) . '</loc>' . "\n";
			$xml .= "\t\t" . '<lastmod>' . esc_html( $lastmod ) . '</lastmod>' . "\n";
			$xml .= "\t" . '</sitemap>' . "\n";
			
			$xml .= '</sitemapindex>';
			
			return $xml;
		}, $this->cache_expiration );
		
		echo $xml;
	}

	/**
	 * Generate posts sitemap.
	 *
	 * @since    1.0.0
	 */
	public function generate_posts_sitemap() {
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		$cache_key = 'seo_simple_sitemap_posts';
		
		$xml = $cache_manager->remember( $cache_key, function() {
			// Get published posts with optimized query
			$args = array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'orderby' => 'modified',
				'order' => 'DESC',
				'fields' => 'ids', // Only get IDs to reduce memory usage
				'update_post_meta_cache' => false, // Don't update meta cache
				'update_post_term_cache' => false, // Don't update term cache
			);
			
			$post_ids = get_posts( $args );
			
			// Start XML output
			$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
			
			if ( ! empty( $post_ids ) ) {
				// Get post data in bulk for efficiency
				$post_data = $this->get_posts_data_bulk( $post_ids );
				
				// Get noindex posts in bulk
				$noindex_posts = $this->get_noindex_posts( $post_ids );
				
				foreach ( $post_ids as $post_id ) {
					if ( ! isset( $post_data[$post_id] ) ) {
						continue;
					}
					
					// Skip if noindex is set
					if ( in_array( $post_id, $noindex_posts ) ) {
						continue;
					}
					
					$post = $post_data[$post_id];
					$url = get_permalink( $post_id );
					$lastmod = date( 'Y-m-d\TH:i:s\Z', strtotime( $post->post_modified_gmt ) );
					
					$xml .= "\t" . '<url>' . "\n";
					$xml .= "\t\t" . '<loc>' . esc_url( $url ) . '</loc>' . "\n";
					$xml .= "\t\t" . '<lastmod>' . esc_html( $lastmod ) . '</lastmod>' . "\n";
					$xml .= "\t\t" . '<changefreq>weekly</changefreq>' . "\n";
					$xml .= "\t\t" . '<priority>0.8</priority>' . "\n";
					$xml .= "\t" . '</url>' . "\n";
				}
			}
			
			$xml .= '</urlset>';
			
			return $xml;
		}, $this->cache_expiration );
		
		echo $xml;
	}

	/**
	 * Generate pages sitemap.
	 *
	 * @since    1.0.0
	 */
	public function generate_pages_sitemap() {
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		$cache_key = 'seo_simple_sitemap_pages';
		
		$xml = $cache_manager->remember( $cache_key, function() {
			// Get published pages with optimized query
			$args = array(
				'post_type' => 'page',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'orderby' => 'modified',
				'order' => 'DESC',
				'fields' => 'ids', // Only get IDs to reduce memory usage
				'update_post_meta_cache' => false, // Don't update meta cache
				'update_post_term_cache' => false, // Don't update term cache
			);
			
			$page_ids = get_posts( $args );
			
			// Start XML output
			$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
			
			// Add homepage
			$xml .= "\t" . '<url>' . "\n";
			$xml .= "\t\t" . '<loc>' . esc_url( get_site_url() ) . '</loc>' . "\n";
			$xml .= "\t\t" . '<lastmod>' . esc_html( current_time( 'Y-m-d\TH:i:s\Z' ) ) . '</lastmod>' . "\n";
			$xml .= "\t\t" . '<changefreq>daily</changefreq>' . "\n";
			$xml .= "\t\t" . '<priority>1.0</priority>' . "\n";
			$xml .= "\t" . '</url>' . "\n";
			
			if ( ! empty( $page_ids ) ) {
				// Get page data in bulk for efficiency
				$page_data = $this->get_posts_data_bulk( $page_ids );
				
				// Get noindex pages in bulk
				$noindex_pages = $this->get_noindex_posts( $page_ids );
				
				foreach ( $page_ids as $page_id ) {
					if ( ! isset( $page_data[$page_id] ) ) {
						continue;
					}
					
					// Skip if noindex is set
					if ( in_array( $page_id, $noindex_pages ) ) {
						continue;
					}
					
					$page = $page_data[$page_id];
					$url = get_permalink( $page_id );
					$lastmod = date( 'Y-m-d\TH:i:s\Z', strtotime( $page->post_modified_gmt ) );
					
					$xml .= "\t" . '<url>' . "\n";
					$xml .= "\t\t" . '<loc>' . esc_url( $url ) . '</loc>' . "\n";
					$xml .= "\t\t" . '<lastmod>' . esc_html( $lastmod ) . '</lastmod>' . "\n";
					$xml .= "\t\t" . '<changefreq>monthly</changefreq>' . "\n";
					$xml .= "\t\t" . '<priority>0.9</priority>' . "\n";
					$xml .= "\t" . '</url>' . "\n";
				}
			}
			
			$xml .= '</urlset>';
			
			return $xml;
		}, $this->cache_expiration );
		
		echo $xml;
	}

	/**
	 * Generate categories sitemap.
	 *
	 * @since    1.0.0
	 */
	public function generate_categories_sitemap() {
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		$cache_key = 'seo_simple_sitemap_categories';
		
		$xml = $cache_manager->remember( $cache_key, function() {
			// Get categories with optimized query
			$categories = get_categories( array(
				'orderby' => 'name',
				'order' => 'ASC',
				'hide_empty' => true,
				'fields' => 'ids', // Only get IDs to reduce memory usage
			) );
			
			// Get category data in bulk
			$category_data = $this->get_terms_data_bulk( $categories );
			
			// Start XML output
			$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
			
			if ( ! empty( $categories ) ) {
				foreach ( $categories as $category_id ) {
					if ( ! isset( $category_data[$category_id] ) ) {
						continue;
					}
					
					$category = $category_data[$category_id];
					$url = get_term_link( (int) $category_id, $category->taxonomy );
					
					if ( is_wp_error( $url ) ) {
						continue;
					}
					
					$xml .= "\t" . '<url>' . "\n";
					$xml .= "\t\t" . '<loc>' . esc_url( $url ) . '</loc>' . "\n";
					$xml .= "\t\t" . '<changefreq>weekly</changefreq>' . "\n";
					$xml .= "\t\t" . '<priority>0.7</priority>' . "\n";
					$xml .= "\t" . '</url>' . "\n";
				}
			}
			
			$xml .= '</urlset>';
			
			return $xml;
		}, $this->cache_expiration );
		
		echo $xml;
	}

	/**
	 * Check if noindex is set for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   bool                True if noindex is set.
	 */
	private function is_noindex( $post_id ) {
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		$cache_key = "seo_simple_noindex_{$post_id}";
		
		return $cache_manager->remember( $cache_key, function() use ( $post_id ) {
			return (bool) get_post_meta( $post_id, '_seo_simple_noindex', true );
		}, $this->cache_expiration );
	}

	/**
	 * Check if multiple posts have noindex set.
	 *
	 * @since    1.0.0
	 * @param    array   $post_ids   Array of post IDs.
	 * @return   array               Array of post IDs that have noindex set.
	 */
	private function get_noindex_posts( $post_ids ) {
		return $this->get_noindex_posts_bulk( $post_ids );
	}

	/**
	 * Clear sitemap cache.
	 *
	 * @since    1.0.0
	 */
	public function clear_sitemap_cache() {
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		
		// Clear all sitemap cache
		$cache_manager->delete( 'seo_simple_sitemap_index' );
		$cache_manager->delete( 'seo_simple_sitemap_posts' );
		$cache_manager->delete( 'seo_simple_sitemap_pages' );
		$cache_manager->delete( 'seo_simple_sitemap_categories' );
		
		// Clear bulk data caches
		wp_cache_flush_group( 'seo_simple' );
	}

	/**
	 * Get sitemap URL for a specific type.
	 *
	 * @since    1.0.0
	 * @param    string    $type    The sitemap type.
	 * @return   string             The sitemap URL.
	 */
	public function get_sitemap_url( $type ) {
		$site_url = get_site_url();
		
		switch ( $type ) {
			case 'index':
				return $site_url . '/sitemap_index.xml';
			case 'posts':
				return $site_url . '/sitemap_posts.xml';
			case 'pages':
				return $site_url . '/sitemap_pages.xml';
			case 'categories':
				return $site_url . '/sitemap_categories.xml';
			default:
				return '';
		}
	}

	/**
		* Get post data in bulk for efficiency.
		*
		* @since    1.0.0
		* @access   private
		* @param    array    $post_ids    Array of post IDs.
		* @return   array                 Post data indexed by ID.
		*/
	private function get_posts_data_bulk( $post_ids ) {
		if ( empty( $post_ids ) ) {
			return array();
		}
		
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		$cache_key = 'seo_simple_posts_data_' . md5( implode( ',', $post_ids ) );
		
		return $cache_manager->remember( $cache_key, function() use ( $post_ids ) {
			global $wpdb;
			
			// Prepare placeholders for IN clause
			$placeholders = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );
			
			$sql = $wpdb->prepare(
				"SELECT ID, post_modified_gmt FROM {$wpdb->posts} WHERE ID IN ($placeholders)",
				$post_ids
			);
			
			$results = $wpdb->get_results( $sql );
			
			$post_data = array();
			foreach ( $results as $result ) {
				$post_data[$result->ID] = $result;
			}
			
			return $post_data;
		}, $this->cache_expiration );
	}

	/**
		* Get term data in bulk for efficiency.
		*
		* @since    1.0.0
		* @access   private
		* @param    array    $term_ids    Array of term IDs.
		* @return   array                 Term data indexed by ID.
		*/
	private function get_terms_data_bulk( $term_ids ) {
		if ( empty( $term_ids ) ) {
			return array();
		}
		
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		$cache_key = 'seo_simple_terms_data_' . md5( implode( ',', $term_ids ) );
		
		return $cache_manager->remember( $cache_key, function() use ( $term_ids ) {
			global $wpdb;
			
			// Prepare placeholders for IN clause
			$placeholders = implode( ',', array_fill( 0, count( $term_ids ), '%d' ) );
			
			$sql = $wpdb->prepare(
				"SELECT t.term_id, t.name, t.slug, tt.taxonomy FROM {$wpdb->terms} t
				 INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
				 WHERE t.term_id IN ($placeholders)",
				$term_ids
			);
			
			$results = $wpdb->get_results( $sql );
			
			$term_data = array();
			foreach ( $results as $result ) {
				$term_data[$result->term_id] = $result;
			}
			
			return $term_data;
		}, $this->cache_expiration );
	}

	/**
		* Get noindex posts in bulk for efficiency.
		*
		* @since    1.0.0
		* @access   private
		* @param    array    $post_ids    Array of post IDs.
		* @return   array                 Array of post IDs that have noindex set.
		*/
	private function get_noindex_posts_bulk( $post_ids ) {
		if ( empty( $post_ids ) ) {
			return array();
		}
		
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		$cache_key = 'seo_simple_noindex_posts_' . md5( implode( ',', $post_ids ) );
		
		return $cache_manager->remember( $cache_key, function() use ( $post_ids ) {
			global $wpdb;
			
			// Prepare placeholders for IN clause
			$placeholders = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );
			
			$sql = $wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE post_id IN ($placeholders) AND meta_key = '_seo_simple_noindex' AND meta_value = '1'",
				$post_ids
			);
			
			$results = $wpdb->get_col( $sql );
			
			return $results;
		}, $this->cache_expiration );
	}

	/**
		* Get all post types that should be included in sitemap.
		*
		* @since    1.0.0
		* @access   private
		* @return   array    Post types to include.
		*/
	private function get_sitemap_post_types() {
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		
		return $cache_manager->remember( 'seo_simple_sitemap_post_types', function() {
			$post_types = get_post_types( array(
				'public' => true,
				'has_archive' => false,
			), 'objects' );
			
			$allowed_types = array();
			foreach ( $post_types as $post_type ) {
				if ( $post_type->name !== 'attachment' ) {
					$allowed_types[] = $post_type->name;
				}
			}
			
			return $allowed_types;
		}, 86400 ); // Cache for 24 hours
	}

	/**
		* Get all taxonomies that should be included in sitemap.
		*
		* @since    1.0.0
		* @access   private
		* @return   array    Taxonomies to include.
		*/
	private function get_sitemap_taxonomies() {
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		
		return $cache_manager->remember( 'seo_simple_sitemap_taxonomies', function() {
			$taxonomies = get_taxonomies( array(
				'public' => true,
				'show_ui' => true,
			), 'objects' );
			
			$allowed_taxonomies = array();
			foreach ( $taxonomies as $taxonomy ) {
				if ( $taxonomy->name !== 'post_format' ) {
					$allowed_taxonomies[] = $taxonomy->name;
				}
			}
			
			return $allowed_taxonomies;
		}, 86400 ); // Cache for 24 hours
	}

	/**
		* Add sitemap URL to robots.txt.
		*
		* @since    1.0.0
		* @param    string    $output    The robots.txt output.
		* @return   string               The modified robots.txt output.
		*/
	public function add_to_robots_txt( $output ) {
		$sitemap_url = $this->get_sitemap_url( 'index' );
		
		if ( ! empty( $sitemap_url ) ) {
			$output .= "\n\n# XML Sitemap\nSitemap: " . $sitemap_url . "\n";
		}
		
		return $output;
	}
}