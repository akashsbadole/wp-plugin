<?php

/**
 * Handles caching for SEO analysis and other performance-critical operations.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles caching for SEO analysis and other performance-critical operations.
 *
 * This class provides methods to cache analysis results, settings, and other
 * data to improve performance and reduce server load.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Cache {

	/**
	 * Cache key prefix.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $prefix    The prefix for all cache keys.
	 */
	private $prefix = 'seo_simple_';

	/**
	 * Cache expiration time in seconds.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      int    $expiration    Default expiration time in seconds.
	 */
	private $expiration = 3600; // 1 hour

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Schedule cache cleanup
		if ( ! wp_next_scheduled( 'seo_simple_cache_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'seo_simple_cache_cleanup' );
		}

		// Hook into the cleanup action
		add_action( 'seo_simple_cache_cleanup', array( $this, 'cleanup_expired_cache' ) );
	}

	/**
	 * Get cached data.
	 *
	 * @since    1.0.0
	 * @param    string    $key        The cache key.
	 * @param    mixed     $default    Default value if cache is not found.
	 * @return   mixed                 The cached data or default value.
	 */
	public function get( $key, $default = null ) {
		$cache_key = $this->prefix . $key;
		$cached = get_transient( $cache_key );

		if ( false === $cached ) {
			return $default;
		}

		// Check if cached data has expired
		if ( isset( $cached['expires'] ) && time() > $cached['expires'] ) {
			$this->delete( $key );
			return $default;
		}

		return $cached['data'];
	}

	/**
	 * Set cached data.
	 *
	 * @since    1.0.0
	 * @param    string    $key        The cache key.
	 * @param    mixed     $data       The data to cache.
	 * @param    int       $expiration Expiration time in seconds.
	 * @return   bool                  True on success, false on failure.
	 */
	public function set( $key, $data, $expiration = null ) {
		$cache_key = $this->prefix . $key;
		$expiration = is_null( $expiration ) ? $this->expiration : $expiration;

		$cache_data = array(
			'data' => $data,
			'expires' => time() + $expiration,
			'created' => time(),
		);

		return set_transient( $cache_key, $cache_data, $expiration );
	}

	/**
	 * Delete cached data.
	 *
	 * @since    1.0.0
	 * @param    string    $key    The cache key.
	 * @return   bool              True on success, false on failure.
	 */
	public function delete( $key ) {
		$cache_key = $this->prefix . $key;
		return delete_transient( $cache_key );
	}

	/**
	 * Check if cached data exists.
	 *
	 * @since    1.0.0
	 * @param    string    $key    The cache key.
	 * @return   bool              True if cache exists, false otherwise.
	 */
	public function exists( $key ) {
		$cache_key = $this->prefix . $key;
		$cached = get_transient( $cache_key );
		return false !== $cached;
	}

	/**
	 * Get or set cached data using a callback.
	 *
	 * @since    1.0.0
	 * @param    string    $key        The cache key.
	 * @param    callable  $callback   The callback to generate data if not cached.
	 * @param    int       $expiration Expiration time in seconds.
	 * @return   mixed                 The cached or generated data.
	 */
	public function remember( $key, $callback, $expiration = null ) {
		$data = $this->get( $key );

		if ( null === $data ) {
			$data = call_user_func( $callback );
			$this->set( $key, $data, $expiration );
		}

		return $data;
	}

	/**
	 * Get cached analysis results for a post.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @param    string    $type       The analysis type (readability, seo).
	 * @return   array                 The cached analysis results.
	 */
	public function get_analysis( $post_id, $type ) {
		$key = "analysis_{$type}_{$post_id}";
		return $this->get( $key, array() );
	}

	/**
	 * Set cached analysis results for a post.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @param    string    $type       The analysis type (readability, seo).
	 * @param    array     $data       The analysis results.
	 * @return   bool                  True on success, false on failure.
	 */
	public function set_analysis( $post_id, $type, $data ) {
		$key = "analysis_{$type}_{$post_id}";
		return $this->set( $key, $data, $this->expiration );
	}

	/**
	 * Delete cached analysis results for a post.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @param    string    $type       The analysis type (readability, seo).
	 * @return   bool                  True on success, false on failure.
	 */
	public function delete_analysis( $post_id, $type = null ) {
		if ( null === $type ) {
			// Delete all analysis types for this post
			$result1 = $this->delete( "analysis_readability_{$post_id}" );
			$result2 = $this->delete( "analysis_seo_{$post_id}" );
			return $result1 || $result2;
		} else {
			// Delete specific analysis type
			return $this->delete( "analysis_{$type}_{$post_id}" );
		}
	}

	/**
	 * Get cached content hash for a post.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @return   string                The cached content hash.
	 */
	public function get_content_hash( $post_id ) {
		$key = "content_hash_{$post_id}";
		return $this->get( $key, '' );
	}

	/**
	 * Set cached content hash for a post.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @param    string    $hash       The content hash.
	 * @return   bool                  True on success, false on failure.
	 */
	public function set_content_hash( $post_id, $hash ) {
		$key = "content_hash_{$post_id}";
		return $this->set( $key, $hash, $this->expiration * 24 ); // 24 hours
	}

	/**
	 * Check if content has changed since last analysis.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @param    string    $content    The current content.
	 * @return   bool                  True if content has changed, false otherwise.
	 */
	public function has_content_changed( $post_id, $content ) {
		$current_hash = md5( $content );
		$cached_hash = $this->get_content_hash( $post_id );

		return $current_hash !== $cached_hash;
	}

	/**
	 * Get cached global settings.
	 *
	 * @since    1.0.0
	 * @return   array    The cached settings.
	 */
	public function get_settings() {
		return $this->get( 'settings', array() );
	}

	/**
	 * Set cached global settings.
	 *
	 * @since    1.0.0
	 * @param    array     $settings   The settings to cache.
	 * @return   bool                  True on success, false on failure.
	 */
	public function set_settings( $settings ) {
		return $this->set( 'settings', $settings, $this->expiration * 24 ); // 24 hours
	}

	/**
	 * Delete cached global settings.
	 *
	 * @since    1.0.0
	 * @return   bool      True on success, false on failure.
	 */
	public function delete_settings() {
		return $this->delete( 'settings' );
	}

	/**
	 * Get cached sitemap data.
	 *
	 * @since    1.0.0
	 * @param    string    $type       The sitemap type.
	 * @return   array                 The cached sitemap data.
	 */
	public function get_sitemap( $type ) {
		$key = "sitemap_{$type}";
		return $this->get( $key, array() );
	}

	/**
	 * Set cached sitemap data.
	 *
	 * @since    1.0.0
	 * @param    string    $type       The sitemap type.
	 * @param    array     $data       The sitemap data.
	 * @return   bool                  True on success, false on failure.
	 */
	public function set_sitemap( $type, $data ) {
		$key = "sitemap_{$type}";
		return $this->set( $key, $data, $this->expiration * 6 ); // 6 hours
	}

	/**
	 * Delete cached sitemap data.
	 *
	 * @since    1.0.0
	 * @param    string    $type       The sitemap type.
	 * @return   bool                  True on success, false on failure.
	 */
	public function delete_sitemap( $type = null ) {
		if ( null === $type ) {
			// Delete all sitemap types
			return $this->delete( 'sitemap_main' ) && 
			       $this->delete( 'sitemap_news' ) && 
			       $this->delete( 'sitemap_images' );
		} else {
			return $this->delete( "sitemap_{$type}" );
		}
	}

	/**
	 * Get cached redirect data.
	 *
	 * @since    1.0.0
	 * @param    string    $url        The URL to check for redirects.
	 * @return   array                 The cached redirect data.
	 */
	public function get_redirect( $url ) {
		$key = 'redirect_' . md5( $url );
		return $this->get( $key, array() );
	}

	/**
	 * Set cached redirect data.
	 *
	 * @since    1.0.0
	 * @param    string    $url        The URL.
	 * @param    array     $data       The redirect data.
	 * @return   bool                  True on success, false on failure.
	 */
	public function set_redirect( $url, $data ) {
		$key = 'redirect_' . md5( $url );
		return $this->set( $key, $data, $this->expiration * 2 ); // 2 hours
	}

	/**
	 * Delete cached redirect data.
	 *
	 * @since    1.0.0
	 * @param    string    $url        The URL.
	 * @return   bool                  True on success, false on failure.
	 */
	public function delete_redirect( $url ) {
		$key = 'redirect_' . md5( $url );
		return $this->delete( $key );
	}

	/**
	 * Clear all cache.
	 *
	 * @since    1.0.0
	 * @return   bool      True on success, false on failure.
	 */
	public function clear_all() {
		global $wpdb;

		// Get all transients with our prefix
		$prefix = '_transient_' . $this->prefix;
		$sql = $wpdb->prepare(
			"SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
			$prefix . '%'
		);

		$transients = $wpdb->get_col( $sql );

		foreach ( $transients as $transient ) {
			// Get the transient key without the _transient_ prefix
			$key = is_string( $transient ) ? substr( $transient, strlen( '_transient_' ) ) : '';
			if ( $key ) {
				delete_transient( $key );
			}
		}

		return true;
	}

	/**
	 * Clean up expired cache entries.
	 *
	 * @since    1.0.0
	 * @return   int       Number of expired entries cleaned up.
	 */
	public function cleanup_expired_cache() {
		global $wpdb;

		$count = 0;
		$prefix = '_transient_' . $this->prefix;

		// Get all transients with our prefix
		$sql = $wpdb->prepare(
			"SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE %s",
			$prefix . '%'
		);

		$transients = $wpdb->get_results( $sql );

		foreach ( $transients as $transient ) {
			// Get the transient key without the _transient_ prefix
			$key = ( isset( $transient->option_name ) && is_string( $transient->option_name ) )
				? substr( $transient->option_name, strlen( '_transient_' ) )
				: '';
			
			if ( ! $key ) {
				continue;
			}
			
			// Unserialize the data
			$data = isset( $transient->option_value ) ? maybe_unserialize( $transient->option_value ) : null;
			
			// Check if it's our cache format and if it has expired
			if ( is_array( $data ) && isset( $data['expires'] ) && time() > $data['expires'] ) {
				delete_transient( $key );
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Get cache statistics.
	 *
	 * @since    1.0.0
	 * @return   array     Cache statistics.
	 */
	public function get_stats() {
		global $wpdb;

		$prefix = '_transient_' . $this->prefix;
		
		// Get total count
		$sql = $wpdb->prepare(
			"SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE %s",
			$prefix . '%'
		);
		$total = $wpdb->get_var( $sql );

		// Get total size
		$sql = $wpdb->prepare(
			"SELECT SUM(LENGTH(option_value)) FROM $wpdb->options WHERE option_name LIKE %s",
			$prefix . '%'
		);
		$size = $wpdb->get_var( $sql );

		// Get expired count
		$expired = 0;
		$sql = $wpdb->prepare(
			"SELECT option_value FROM $wpdb->options WHERE option_name LIKE %s",
			$prefix . '%'
		);
		$transients = $wpdb->get_col( $sql );

		foreach ( $transients as $value ) {
			$data = maybe_unserialize( $value );
			if ( is_array( $data ) && isset( $data['expires'] ) && time() > $data['expires'] ) {
				$expired++;
			}
		}

		return array(
			'total_entries' => intval( $total ),
			'total_size' => intval( $size ),
			'expired_entries' => $expired,
			'valid_entries' => intval( $total ) - $expired,
		);
	}

	/**
	 * Get cache keys by pattern.
	 *
	 * @since    1.0.0
	 * @param    string    $pattern    The pattern to match.
	 * @return   array                 Array of matching cache keys.
	 */
	public function get_keys_by_pattern( $pattern ) {
		global $wpdb;

		$prefix = '_transient_' . $this->prefix . $pattern;
		$sql = $wpdb->prepare(
			"SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
			$prefix
		);

		$transients = $wpdb->get_col( $sql );
		$keys = array();

		foreach ( $transients as $transient ) {
			// Get the transient key without the _transient_ prefix
			$key = is_string( $transient ) ? substr( $transient, strlen( '_transient_' ) ) : '';
			
			if ( ! $key ) {
				continue;
			}
			
			// Remove our prefix to return the original key
			$original_key = substr( $key, strlen( $this->prefix ) );
			$keys[] = $original_key;
		}

		return $keys;
	}

	/**
	 * Delete cache keys by pattern.
	 *
	 * @since    1.0.0
	 * @param    string    $pattern    The pattern to match.
	 * @return   int       Number of keys deleted.
	 */
	public function delete_by_pattern( $pattern ) {
		$keys = $this->get_keys_by_pattern( $pattern );
		$count = 0;

		foreach ( $keys as $key ) {
			if ( $this->delete( $key ) ) {
				$count++;
			}
		}

		return $count;
	}
}