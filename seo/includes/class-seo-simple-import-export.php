<?php
/**
 * Import/Export functionality for SEO Simple plugin
 *
 * Handles importing and exporting SEO data in CSV and JSON formats,
 * and migration from Yoast SEO and Rank Math plugins.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Import/Export class.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Import_Export {

	/**
	 * The SEO meta handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Meta    $seo_meta
	 */
	private $seo_meta;

	/**
	 * Initialize the class.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->seo_meta = new Seo_Simple_Meta();
	}

	/**
	 * Export SEO data to CSV.
	 *
	 * @since    1.0.0
	 * @param    array    $post_ids    Array of post IDs to export (empty for all).
	 * @return   string                CSV content.
	 */
	public function export_to_csv( $post_ids = array() ) {
		$args = array(
			'post_type'      => 'any',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		if ( ! empty( $post_ids ) ) {
			$args['post__in'] = $post_ids;
		}

		$posts = get_posts( $args );

		// CSV headers
		$csv_data = array();
		$csv_data[] = array(
			'Post ID',
			'Post Title',
			'Post Type',
			'Post Status',
			'Post URL',
			'Meta Title',
			'Meta Description',
			'Focus Keyword',
			'Canonical URL',
			'Robots Meta',
			'OG Title',
			'OG Description',
			'OG Image',
			'Twitter Title',
			'Twitter Description',
			'Twitter Image',
			'Schema Type',
		);

		// Add data rows
		foreach ( $posts as $post_id ) {
			$post = get_post( $post_id );
			
			$csv_data[] = array(
				$post_id,
				$post->post_title,
				$post->post_type,
				$post->post_status,
				get_permalink( $post_id ),
				get_post_meta( $post_id, '_seo_simple_title', true ),
				get_post_meta( $post_id, '_seo_simple_description', true ),
				get_post_meta( $post_id, '_seo_simple_focus_keyword', true ),
				get_post_meta( $post_id, '_seo_simple_canonical', true ),
				get_post_meta( $post_id, '_seo_simple_robots', true ),
				get_post_meta( $post_id, '_seo_simple_og_title', true ),
				get_post_meta( $post_id, '_seo_simple_og_description', true ),
				get_post_meta( $post_id, '_seo_simple_og_image', true ),
				get_post_meta( $post_id, '_seo_simple_twitter_title', true ),
				get_post_meta( $post_id, '_seo_simple_twitter_description', true ),
				get_post_meta( $post_id, '_seo_simple_twitter_image', true ),
				get_post_meta( $post_id, '_seo_simple_schema_type', true ),
			);
		}

		return $this->array_to_csv( $csv_data );
	}

	/**
	 * Export SEO data to JSON.
	 *
	 * @since    1.0.0
	 * @param    array    $post_ids    Array of post IDs to export (empty for all).
	 * @return   string                JSON content.
	 */
	public function export_to_json( $post_ids = array() ) {
		$args = array(
			'post_type'      => 'any',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		if ( ! empty( $post_ids ) ) {
			$args['post__in'] = $post_ids;
		}

		$posts = get_posts( $args );

		$export_data = array(
			'version'     => '1.0.0',
			'export_date' => current_time( 'mysql' ),
			'site_url'    => get_site_url(),
			'posts'       => array(),
			'settings'    => get_option( 'seo_simple_settings', array() ),
		);

		foreach ( $posts as $post_id ) {
			$post = get_post( $post_id );
			
			$export_data['posts'][] = array(
				'id'                    => $post_id,
				'title'                 => $post->post_title,
				'type'                  => $post->post_type,
				'status'                => $post->post_status,
				'url'                   => get_permalink( $post_id ),
				'meta_title'            => get_post_meta( $post_id, '_seo_simple_title', true ),
				'meta_description'      => get_post_meta( $post_id, '_seo_simple_description', true ),
				'focus_keyword'         => get_post_meta( $post_id, '_seo_simple_focus_keyword', true ),
				'canonical_url'         => get_post_meta( $post_id, '_seo_simple_canonical', true ),
				'robots_meta'           => get_post_meta( $post_id, '_seo_simple_robots', true ),
				'og_title'              => get_post_meta( $post_id, '_seo_simple_og_title', true ),
				'og_description'        => get_post_meta( $post_id, '_seo_simple_og_description', true ),
				'og_image'              => get_post_meta( $post_id, '_seo_simple_og_image', true ),
				'twitter_title'         => get_post_meta( $post_id, '_seo_simple_twitter_title', true ),
				'twitter_description'   => get_post_meta( $post_id, '_seo_simple_twitter_description', true ),
				'twitter_image'         => get_post_meta( $post_id, '_seo_simple_twitter_image', true ),
				'schema_type'           => get_post_meta( $post_id, '_seo_simple_schema_type', true ),
				'noindex'               => get_post_meta( $post_id, '_seo_simple_noindex', true ),
				'nofollow'              => get_post_meta( $post_id, '_seo_simple_nofollow', true ),
			);
		}

		return wp_json_encode( $export_data, JSON_PRETTY_PRINT );
	}

	/**
	 * Import SEO data from CSV.
	 *
	 * @since    1.0.0
	 * @param    string    $csv_content    CSV content.
	 * @return   array                     Import results.
	 */
	public function import_from_csv( $csv_content ) {
		$results = array(
			'success' => 0,
			'failed'  => 0,
			'skipped' => 0,
			'errors'  => array(),
		);

		$rows = $this->csv_to_array( $csv_content );
		
		if ( empty( $rows ) ) {
			$results['errors'][] = __( 'CSV file is empty or invalid.', 'seo-simple' );
			return $results;
		}

		// Remove header row
		$headers = array_shift( $rows );

		foreach ( $rows as $index => $row ) {
			$row_number = $index + 2; // +2 because we removed header and arrays are 0-indexed
			
			// Skip empty rows
			if ( empty( array_filter( $row ) ) ) {
				$results['skipped']++;
				continue;
			}

			$post_id = isset( $row[0] ) ? intval( $row[0] ) : 0;

			if ( ! $post_id || ! get_post( $post_id ) ) {
				$results['failed']++;
				$results['errors'][] = sprintf( __( 'Row %d: Invalid post ID %d', 'seo-simple' ), $row_number, $post_id );
				continue;
			}

			// Import meta data
			$imported = $this->import_post_meta( $post_id, array(
				'meta_title'            => isset( $row[5] ) ? $row[5] : '',
				'meta_description'      => isset( $row[6] ) ? $row[6] : '',
				'focus_keyword'         => isset( $row[7] ) ? $row[7] : '',
				'canonical_url'         => isset( $row[8] ) ? $row[8] : '',
				'robots_meta'           => isset( $row[9] ) ? $row[9] : '',
				'og_title'              => isset( $row[10] ) ? $row[10] : '',
				'og_description'        => isset( $row[11] ) ? $row[11] : '',
				'og_image'              => isset( $row[12] ) ? $row[12] : '',
				'twitter_title'         => isset( $row[13] ) ? $row[13] : '',
				'twitter_description'   => isset( $row[14] ) ? $row[14] : '',
				'twitter_image'         => isset( $row[15] ) ? $row[15] : '',
				'schema_type'           => isset( $row[16] ) ? $row[16] : '',
			) );

			if ( $imported ) {
				$results['success']++;
			} else {
				$results['failed']++;
				$results['errors'][] = sprintf( __( 'Row %d: Failed to import data for post ID %d', 'seo-simple' ), $row_number, $post_id );
			}
		}

		return $results;
	}

	/**
	 * Import SEO data from JSON.
	 *
	 * @since    1.0.0
	 * @param    string    $json_content    JSON content.
	 * @return   array                      Import results.
	 */
	public function import_from_json( $json_content ) {
		$results = array(
			'success' => 0,
			'failed'  => 0,
			'skipped' => 0,
			'errors'  => array(),
		);

		$data = json_decode( $json_content, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$results['errors'][] = __( 'Invalid JSON format.', 'seo-simple' );
			return $results;
		}

		if ( ! isset( $data['posts'] ) || ! is_array( $data['posts'] ) ) {
			$results['errors'][] = __( 'JSON file does not contain posts data.', 'seo-simple' );
			return $results;
		}

		foreach ( $data['posts'] as $post_data ) {
			$post_id = isset( $post_data['id'] ) ? intval( $post_data['id'] ) : 0;

			if ( ! $post_id || ! get_post( $post_id ) ) {
				$results['failed']++;
				$results['errors'][] = sprintf( __( 'Invalid post ID: %d', 'seo-simple' ), $post_id );
				continue;
			}

			$imported = $this->import_post_meta( $post_id, $post_data );

			if ( $imported ) {
				$results['success']++;
			} else {
				$results['failed']++;
				$results['errors'][] = sprintf( __( 'Failed to import data for post ID: %d', 'seo-simple' ), $post_id );
			}
		}

		// Import settings if available
		if ( isset( $data['settings'] ) && is_array( $data['settings'] ) ) {
			update_option( 'seo_simple_settings', $data['settings'] );
		}

		return $results;
	}

	/**
	 * Import SEO data from Yoast SEO plugin.
	 *
	 * @since    1.0.0
	 * @return   array    Import results.
	 */
	public function import_from_yoast() {
		$results = array(
			'success' => 0,
			'failed'  => 0,
			'skipped' => 0,
			'errors'  => array(),
		);

		// Check if Yoast is installed
		if ( ! defined( 'WPSEO_VERSION' ) && ! $this->has_yoast_data() ) {
			$results['errors'][] = __( 'Yoast SEO data not found.', 'seo-simple' );
			return $results;
		}

		$args = array(
			'post_type'      => 'any',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		$posts = get_posts( $args );

		foreach ( $posts as $post_id ) {
			// Get Yoast meta data
			$yoast_title       = get_post_meta( $post_id, '_yoast_wpseo_title', true );
			$yoast_description = get_post_meta( $post_id, '_yoast_wpseo_metadesc', true );
			$yoast_keyword     = get_post_meta( $post_id, '_yoast_wpseo_focuskw', true );
			$yoast_canonical   = get_post_meta( $post_id, '_yoast_wpseo_canonical', true );
			$yoast_noindex     = get_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', true );
			$yoast_nofollow    = get_post_meta( $post_id, '_yoast_wpseo_meta-robots-nofollow', true );
			$yoast_og_title    = get_post_meta( $post_id, '_yoast_wpseo_opengraph-title', true );
			$yoast_og_desc     = get_post_meta( $post_id, '_yoast_wpseo_opengraph-description', true );
			$yoast_og_image    = get_post_meta( $post_id, '_yoast_wpseo_opengraph-image', true );
			$yoast_tw_title    = get_post_meta( $post_id, '_yoast_wpseo_twitter-title', true );
			$yoast_tw_desc     = get_post_meta( $post_id, '_yoast_wpseo_twitter-description', true );
			$yoast_tw_image    = get_post_meta( $post_id, '_yoast_wpseo_twitter-image', true );

			// Skip if no Yoast data
			if ( empty( $yoast_title ) && empty( $yoast_description ) && empty( $yoast_keyword ) ) {
				$results['skipped']++;
				continue;
			}

			// Build robots meta
			$robots = array();
			if ( $yoast_noindex === '1' ) {
				$robots[] = 'noindex';
			}
			if ( $yoast_nofollow === '1' ) {
				$robots[] = 'nofollow';
			}
			$robots_meta = ! empty( $robots ) ? implode( ', ', $robots ) : '';

			// Import to Simple SEO
			$imported = $this->import_post_meta( $post_id, array(
				'meta_title'            => $yoast_title,
				'meta_description'      => $yoast_description,
				'focus_keyword'         => $yoast_keyword,
				'canonical_url'         => $yoast_canonical,
				'robots_meta'           => $robots_meta,
				'og_title'              => $yoast_og_title,
				'og_description'        => $yoast_og_desc,
				'og_image'              => $yoast_og_image,
				'twitter_title'         => $yoast_tw_title,
				'twitter_description'   => $yoast_tw_desc,
				'twitter_image'         => $yoast_tw_image,
				'noindex'               => $yoast_noindex === '1' ? '1' : '',
				'nofollow'              => $yoast_nofollow === '1' ? '1' : '',
			) );

			if ( $imported ) {
				$results['success']++;
			} else {
				$results['failed']++;
			}
		}

		return $results;
	}

	/**
	 * Import SEO data from Rank Math plugin.
	 *
	 * @since    1.0.0
	 * @return   array    Import results.
	 */
	public function import_from_rankmath() {
		$results = array(
			'success' => 0,
			'failed'  => 0,
			'skipped' => 0,
			'errors'  => array(),
		);

		// Check if Rank Math is installed
		if ( ! defined( 'RANK_MATH_VERSION' ) && ! $this->has_rankmath_data() ) {
			$results['errors'][] = __( 'Rank Math data not found.', 'seo-simple' );
			return $results;
		}

		$args = array(
			'post_type'      => 'any',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		$posts = get_posts( $args );

		foreach ( $posts as $post_id ) {
			// Get Rank Math meta data
			$rm_title       = get_post_meta( $post_id, 'rank_math_title', true );
			$rm_description = get_post_meta( $post_id, 'rank_math_description', true );
			$rm_keyword     = get_post_meta( $post_id, 'rank_math_focus_keyword', true );
			$rm_canonical   = get_post_meta( $post_id, 'rank_math_canonical_url', true );
			$rm_robots      = get_post_meta( $post_id, 'rank_math_robots', true );
			$rm_og_title    = get_post_meta( $post_id, 'rank_math_facebook_title', true );
			$rm_og_desc     = get_post_meta( $post_id, 'rank_math_facebook_description', true );
			$rm_og_image    = get_post_meta( $post_id, 'rank_math_facebook_image', true );
			$rm_tw_title    = get_post_meta( $post_id, 'rank_math_twitter_title', true );
			$rm_tw_desc     = get_post_meta( $post_id, 'rank_math_twitter_description', true );
			$rm_tw_image    = get_post_meta( $post_id, 'rank_math_twitter_image', true );
			$rm_schema      = get_post_meta( $post_id, 'rank_math_rich_snippet', true );

			// Skip if no Rank Math data
			if ( empty( $rm_title ) && empty( $rm_description ) && empty( $rm_keyword ) ) {
				$results['skipped']++;
				continue;
			}

			// Parse robots meta
			$robots_meta = '';
			if ( is_array( $rm_robots ) ) {
				$robots_meta = implode( ', ', $rm_robots );
			}

			// Import to Simple SEO
			$imported = $this->import_post_meta( $post_id, array(
				'meta_title'            => $rm_title,
				'meta_description'      => $rm_description,
				'focus_keyword'         => $rm_keyword,
				'canonical_url'         => $rm_canonical,
				'robots_meta'           => $robots_meta,
				'og_title'              => $rm_og_title,
				'og_description'        => $rm_og_desc,
				'og_image'              => $rm_og_image,
				'twitter_title'         => $rm_tw_title,
				'twitter_description'   => $rm_tw_desc,
				'twitter_image'         => $rm_tw_image,
				'schema_type'           => $rm_schema,
				'noindex'               => is_array( $rm_robots ) && in_array( 'noindex', $rm_robots ) ? '1' : '',
				'nofollow'              => is_array( $rm_robots ) && in_array( 'nofollow', $rm_robots ) ? '1' : '',
			) );

			if ( $imported ) {
				$results['success']++;
			} else {
				$results['failed']++;
			}
		}

		return $results;
	}

	/**
	 * Import post meta data.
	 *
	 * @since    1.0.0
	 * @param    int      $post_id      Post ID.
	 * @param    array    $meta_data    Meta data to import.
	 * @return   bool                   True on success, false on failure.
	 */
	private function import_post_meta( $post_id, $meta_data ) {
		$success = true;

		$meta_map = array(
			'meta_title'            => '_seo_simple_title',
			'meta_description'      => '_seo_simple_description',
			'focus_keyword'         => '_seo_simple_focus_keyword',
			'canonical_url'         => '_seo_simple_canonical',
			'robots_meta'           => '_seo_simple_robots',
			'og_title'              => '_seo_simple_og_title',
			'og_description'        => '_seo_simple_og_description',
			'og_image'              => '_seo_simple_og_image',
			'twitter_title'         => '_seo_simple_twitter_title',
			'twitter_description'   => '_seo_simple_twitter_description',
			'twitter_image'         => '_seo_simple_twitter_image',
			'schema_type'           => '_seo_simple_schema_type',
			'noindex'               => '_seo_simple_noindex',
			'nofollow'              => '_seo_simple_nofollow',
		);

		foreach ( $meta_map as $key => $meta_key ) {
			if ( isset( $meta_data[ $key ] ) && ! empty( $meta_data[ $key ] ) ) {
				$value = sanitize_text_field( $meta_data[ $key ] );
				if ( ! update_post_meta( $post_id, $meta_key, $value ) ) {
					$success = false;
				}
			}
		}

		return $success;
	}

	/**
	 * Check if Yoast data exists.
	 *
	 * @since    1.0.0
	 * @return   bool    True if Yoast data exists.
	 */
	private function has_yoast_data() {
		global $wpdb;
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE '_yoast_wpseo_%'" );
		return $count > 0;
	}

	/**
	 * Check if Rank Math data exists.
	 *
	 * @since    1.0.0
	 * @return   bool    True if Rank Math data exists.
	 */
	private function has_rankmath_data() {
		global $wpdb;
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE 'rank_math_%'" );
		return $count > 0;
	}

	/**
	 * Convert array to CSV string.
	 *
	 * @since    1.0.0
	 * @param    array     $data    Array data.
	 * @return   string             CSV string.
	 */
	private function array_to_csv( $data ) {
		$output = fopen( 'php://temp', 'r+' );
		
		foreach ( $data as $row ) {
			fputcsv( $output, $row );
		}
		
		rewind( $output );
		$csv = stream_get_contents( $output );
		fclose( $output );
		
		return $csv;
	}

	/**
	 * Convert CSV string to array.
	 *
	 * @since    1.0.0
	 * @param    string    $csv    CSV string.
	 * @return   array             Array data.
	 */
	private function csv_to_array( $csv ) {
		$rows = array();
		$lines = explode( "\n", $csv );
		
		foreach ( $lines as $line ) {
			if ( empty( trim( $line ) ) ) {
				continue;
			}
			$rows[] = str_getcsv( $line );
		}
		
		return $rows;
	}
}
