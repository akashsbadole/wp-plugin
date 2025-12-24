<?php

/**
 * Centralized cache management for SEO Simple plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Centralized cache management for SEO Simple plugin.
 *
 * This class handles all caching operations for the SEO Simple plugin,
 * providing a unified interface for setting, getting, and clearing cached data.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Cache_Manager {

	/**
	 * Cache group name.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $cache_group    The cache group name.
	 */
	private $cache_group = 'seo_simple';

	/**
	 * Default cache expiration time in seconds.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      int       $default_expiration    Default cache expiration time.
	 */
	private $default_expiration = 3600; // 1 hour

	/**
	 * Initialize the cache manager.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Hook into save_post to clear relevant caches
		add_action( 'save_post', array( $this, 'clear_post_related_cache' ) );
		add_action( 'delete_post', array( $this, 'clear_post_related_cache' ) );
		
		// Hook into settings changes
		add_action( 'update_option_seo_simple_settings', array( $this, 'clear_settings_cache' ) );
		
		// Schedule cache cleanup if not already scheduled
		if ( ! wp_next_scheduled( 'seo_simple_cache_cleanup' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'seo_simple_cache_cleanup' );
		}
		
		// Hook into the cleanup action
		add_action( 'seo_simple_cache_cleanup', array( $this, 'cleanup_expired_cache' ) );
	}

	/**
	 * Get cached data.
	 *
	 * @since    1.0.0
	 * @param    string    $key    The cache key.
	 * @return   mixed             The cached data or false if not found.
	 */
	public function get( $key ) {
		return wp_cache_get( $key, $this->cache_group );
	}

	/**
	 * Set cached data.
	 *
	 * @since    1.0.0
	 * @param    string    $key         The cache key.
	 * @param    mixed     $value       The data to cache.
	 * @param    int       $expiration  Optional. Expiration time in seconds.
	 * @return   bool                   True on success, false on failure.
	 */
	public function set( $key, $value, $expiration = null ) {
		if ( null === $expiration ) {
			$expiration = $this->default_expiration;
		}
		
		return wp_cache_set( $key, $value, $this->cache_group, $expiration );
	}

	/**
	 * Delete cached data.
	 *
	 * @since    1.0.0
	 * @param    string    $key    The cache key.
	 * @return   bool              True on success, false on failure.
	 */
	public function delete( $key ) {
		return wp_cache_delete( $key, $this->cache_group );
	}

	/**
	 * Get multiple cached values in one operation.
	 *
	 * @since    1.0.0
	 * @param    array     $keys    Array of cache keys.
	 * @return   array              Array of cached values indexed by key.
	 */
	public function get_multiple( $keys ) {
		$values = array();
		
		foreach ( $keys as $key ) {
			$values[$key] = $this->get( $key );
		}
		
		return $values;
	}

	/**
	 * Set multiple cached values in one operation.
	 *
	 * @since    1.0.0
	 * @param    array     $data        Array of data indexed by cache key.
	 * @param    int       $expiration  Optional. Expiration time in seconds.
	 * @return   bool                   True on success, false on failure.
	 */
	public function set_multiple( $data, $expiration = null ) {
		if ( null === $expiration ) {
			$expiration = $this->default_expiration;
		}
		
		$success = true;
		
		foreach ( $data as $key => $value ) {
			if ( ! $this->set( $key, $value, $expiration ) ) {
				$success = false;
			}
		}
		
		return $success;
	}

	/**
	 * Clear all caches for this plugin.
	 *
	 * @since    1.0.0
	 * @return   bool    True on success, false on failure.
	 */
	public function clear_all() {
		return wp_cache_flush_group( $this->cache_group );
	}

	/**
	 * Clear post-related caches.
	 *
	 * @since    1.0.0
	 * @param    int    $post_id    The post ID.
	 * @return   bool               True on success, false on failure.
	 */
	public function clear_post_related_cache( $post_id ) {
		// Clear sitemap caches
		$this->delete( 'seo_simple_sitemap_index' );
		$this->delete( 'seo_simple_sitemap_posts' );
		$this->delete( 'seo_simple_sitemap_pages' );
		$this->delete( 'seo_simple_sitemap_categories' );
		
		// Clear dashboard caches
		$this->delete( 'seo_simple_dashboard_stats' );
		$this->delete( 'seo_simple_missing_meta_count' );
		
		// Clear posts needing improvement caches
		for ( $i = 5; $i <= 50; $i += 5 ) {
			$this->delete( 'seo_simple_posts_needing_improvement_' . $i );
		}
		
		// Clear bulk editor caches
		$this->delete( 'seo_simple_bulk_edit_count_all' );
		$this->delete( 'seo_simple_bulk_edit_all_20_0' );
		
		// Clear meta caches for this post
		$meta_fields = array( 'title', 'description', 'focus_keyword', 'noindex', 'nofollow', 'canonical' );
		foreach ( $meta_fields as $field ) {
			$this->delete( 'seo_simple_meta_' . $field . '_' . $post_id );
		}
		
		return true;
	}

	/**
	 * Clear settings-related caches.
	 *
	 * @since    1.0.0
	 * @return   bool    True on success, false on failure.
	 */
	public function clear_settings_cache() {
		// Clear global settings cache
		$this->delete( 'seo_simple_global_settings' );
		
		// Clear all sitemap caches as they depend on settings
		$this->delete( 'seo_simple_sitemap_index' );
		$this->delete( 'seo_simple_sitemap_posts' );
		$this->delete( 'seo_simple_sitemap_pages' );
		$this->delete( 'seo_simple_sitemap_categories' );
		
		return true;
	}

	/**
	 * Get or set cached value using a callback.
	 *
	 * @since    1.0.0
	 * @param    string    $key         The cache key.
	 * @param    callable  $callback    The callback to generate the value.
	 * @param    int       $expiration  Optional. Expiration time in seconds.
	 * @return   mixed                  The cached or generated value.
	 */
	public function remember( $key, $callback, $expiration = null ) {
		$value = $this->get( $key );
		
		if ( false === $value ) {
			$value = call_user_func( $callback );
			$this->set( $key, $value, $expiration );
		}
		
		return $value;
	}

	/**
	 * Generate a cache key based on parameters.
	 *
	 * @since    1.0.0
	 * @param    string    $prefix      The key prefix.
	 * @param    array     $params      Parameters to include in the key.
	 * @return   string                 The generated cache key.
	 */
	public function generate_key( $prefix, $params = array() ) {
		if ( empty( $params ) ) {
			return $prefix;
		}
		
		return $prefix . '_' . md5( serialize( $params ) );
	}

	/**
	 * Set cache expiration time.
	 *
	 * @since    1.0.0
	 * @param    int    $expiration    The expiration time in seconds.
	 */
	public function set_expiration( $expiration ) {
		$this->default_expiration = (int) $expiration;
	}

	/**
	 * Get cache expiration time.
	 *
	 * @since    1.0.0
	 * @return   int    The expiration time in seconds.
	 */
	public function get_expiration() {
		return $this->default_expiration;
	}

	/**
	 * Set up hooks for cache management.
	 *
	 * @since    1.0.0
	 */
	public function setup_hooks() {
		// This method is intentionally empty as hooks are already set up in the constructor
		// Keeping this method for backward compatibility
	}

	/**
	 * Get singleton instance.
	 *
	 * @since    1.0.0
	 * @return   Seo_Simple_Cache_Manager    The cache manager instance.
	 */
	public static function get_instance() {
		static $instance = null;
		
		if ( null === $instance ) {
			$instance = new self();
		}
		
		return $instance;
	}

	/**
	 * Clear cache group with pattern matching.
	 *
	 * @since    1.0.0
	 * @param    string    $pattern    The pattern to match.
	 * @return   int       Number of keys deleted.
	 */
	public function clear_cache_group( $pattern ) {
		global $wp_object_cache;
		
		if ( ! is_object( $wp_object_cache ) ) {
			return 0;
		}
		
		$count = 0;
		
		// Get all cache keys in our group
		if ( isset( $wp_object_cache->cache[ $this->cache_group ] ) ) {
			$cache_keys = array_keys( $wp_object_cache->cache[ $this->cache_group ] );
			
			foreach ( $cache_keys as $key ) {
				if ( false !== strpos( $key, $pattern ) ) {
					$this->delete( $key );
					$count++;
				}
			}
		}
		
		return $count;
	}

	/**
	 * Warm up cache with commonly accessed data.
	 *
	 * @since    1.0.0
	 * @return   array    Results of cache warming.
	 */
	public function warm_up_cache() {
		$results = array(
			'success' => 0,
			'failed' => 0,
			'items' => array(),
		);
		
		// Warm up global settings
		try {
			if ( class_exists( 'Seo_Simple_Meta' ) ) {
				$meta = new Seo_Simple_Meta();
				$settings = $meta->get_global_settings();
				$this->set( 'seo_simple_global_settings', $settings, 3600 );
				$results['success']++;
				$results['items'][] = 'Global settings';
			}
		} catch ( Exception $e ) {
			$results['failed']++;
		}
		
		// Warm up sitemap post types
		try {
			if ( class_exists( 'Seo_Simple_Sitemap' ) ) {
				$sitemap = new Seo_Simple_Sitemap();
				$post_types = $sitemap->get_sitemap_post_types();
				$this->set( 'seo_simple_sitemap_post_types', $post_types, 86400 );
				$results['success']++;
				$results['items'][] = 'Sitemap post types';
			}
		} catch ( Exception $e ) {
			$results['failed']++;
		}
		
		return $results;
	}

	/**
	 * Clean up expired cache entries.
	 *
	 * @since    1.0.0
	 * @return   int    Number of expired entries cleaned up.
	 */
	public function cleanup_expired_cache() {
		// Since we're using WordPress object cache, we don't have direct control
		// over expiration. Instead, we'll clear some old caches periodically.
		$cleared = 0;
		
		// Clear old dashboard stats (older than 6 hours)
		$this->delete( 'seo_simple_dashboard_stats' );
		$cleared++;
		
		// Clear old bulk editor caches
		for ( $i = 5; $i <= 50; $i += 5 ) {
			$this->delete( 'seo_simple_posts_needing_improvement_' . $i );
			$cleared++;
		}
		
		return $cleared;
	}

	/**
	 * Get cache statistics.
	 *
	 * @since    1.0.0
	 * @return   array    Cache statistics.
	 */
	public function get_cache_stats() {
		global $wp_object_cache;
		
		$stats = array(
			'total_entries' => 0,
			'group_size' => 0,
			'hit_rate' => 0,
			'misses' => 0,
			'hits' => 0,
		);
		
		if ( is_object( $wp_object_cache ) && isset( $wp_object_cache->cache[ $this->cache_group ] ) ) {
			$stats['total_entries'] = count( $wp_object_cache->cache[ $this->cache_group ] );
			$stats['group_size'] = strlen( serialize( $wp_object_cache->cache[ $this->cache_group ] ) );
		}
		
		// Get cache hit/miss stats if available
		if ( is_object( $wp_object_cache ) && isset( $wp_object_cache->cache_hits ) ) {
			$stats['hits'] = $wp_object_cache->cache_hits;
		}
		
		if ( is_object( $wp_object_cache ) && isset( $wp_object_cache->cache_misses ) ) {
			$stats['misses'] = $wp_object_cache->cache_misses;
		}
		
		if ( $stats['hits'] > 0 || $stats['misses'] > 0 ) {
			$stats['hit_rate'] = round( ( $stats['hits'] / ( $stats['hits'] + $stats['misses'] ) ) * 100, 2 );
		}
		
		return $stats;
	}

	/**
	 * Delete cache by pattern.
	 *
	 * @since    1.0.0
	 * @param    string    $pattern    The pattern to match.
	 * @return   int       Number of keys deleted.
	 */
	public function delete_by_pattern( $pattern ) {
		return $this->clear_cache_group( $pattern );
	}
}