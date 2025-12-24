<?php

/**
 * SEO Optimized Breadcrumbs functionality
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * SEO Optimized Breadcrumbs class
 *
 * This class handles all breadcrumb functionality including generation,
 * display, and schema markup for breadcrumbs.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Breadcrumbs {

	/**
	 * The single instance of the class.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @static
	 * @var      Seo_Simple_Breadcrumbs    $instance    The single instance of the class.
	 */
	private static $instance = null;

	/**
	 * Get the single instance of the class.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @static
	 * @return   Seo_Simple_Breadcrumbs    The single instance of the class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function __construct() {
		// Register hooks - ensure initialization happens after textdomain is loaded
		add_action( 'init', array( $this, 'init' ), 0 ); // Priority 0, after textdomain (-5)
		add_action( 'wp_head', array( $this, 'output_breadcrumb_schema' ), 8 );
		add_filter( 'seo_simple_schema_data', array( $this, 'add_breadcrumb_schema' ), 10, 2 );
		
		// Register shortcode
		add_shortcode( 'seo_breadcrumbs', array( $this, 'breadcrumb_shortcode' ) );
		
		// Register widget after textdomain is loaded
		add_action( 'init', array( $this, 'register_widgets' ), 20 );
	}

	/**
	 * Initialize the breadcrumbs functionality.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function init() {
		// Get settings
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Check if breadcrumbs are enabled
		if ( isset( $settings['seo_enable_breadcrumbs'] ) && $settings['seo_enable_breadcrumbs'] ) {
			// Add automatic breadcrumbs to theme
			$this->add_automatic_breadcrumbs();
		}
	}

	/**
	 * Add automatic breadcrumbs to theme.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function add_automatic_breadcrumbs() {
		$settings = get_option( 'seo_simple_settings', array() );
		$position = isset( $settings['seo_breadcrumb_position'] ) ? $settings['seo_breadcrumb_position'] : 'before_content';
		
		switch ( $position ) {
			case 'before_content':
				add_action( 'the_content', array( $this, 'add_breadcrumbs_before_content' ) );
				break;
			case 'after_title':
				add_action( 'the_title', array( $this, 'add_breadcrumbs_after_title' ) );
				break;
			case 'manual':
				// Manual placement via shortcode or widget
				break;
		}
	}

	/**
	 * Add breadcrumbs before content.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string    $content    The post content.
	 * @return   string               The modified content.
	 */
	public function add_breadcrumbs_before_content( $content ) {
		if ( is_main_query() && in_the_loop() && is_singular() ) {
			$breadcrumbs = $this->get_breadcrumbs();
			if ( $breadcrumbs ) {
				$content = $breadcrumbs . $content;
			}
		}
		return $content;
	}

	/**
	 * Add breadcrumbs after title.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string    $title    The post title.
	 * @return   string              The modified title.
	 */
	public function add_breadcrumbs_after_title( $title ) {
		if ( is_main_query() && in_the_loop() && is_singular() ) {
			$breadcrumbs = $this->get_breadcrumbs();
			if ( $breadcrumbs ) {
				$title = $title . $breadcrumbs;
			}
		}
		return $title;
	}

	/**
	 * Get the breadcrumbs HTML.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    array     $args    Optional. Arguments for breadcrumb display.
	 * @return   string             The breadcrumbs HTML.
	 */
	public function get_breadcrumbs( $args = array() ) {
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Default arguments
		$defaults = array(
			'separator' => isset( $settings['seo_breadcrumb_separator'] ) ? $settings['seo_breadcrumb_separator'] : ' &raquo; ',
			'home_text' => isset( $settings['seo_breadcrumb_home_text'] ) ? $settings['seo_breadcrumb_home_text'] : __( 'Home', 'seo-simple' ),
			'home_url' => home_url( '/' ),
			'before' => '<nav class="seo-breadcrumbs" aria-label="breadcrumb"><ol class="breadcrumb">',
			'after' => '</ol></nav>',
			'before_item' => '<li class="breadcrumb-item">',
			'after_item' => '</li>',
			'before_current' => '<li class="breadcrumb-item active" aria-current="page">',
			'after_current' => '</li>',
			'show_on_front' => true,
			'show_on_home' => false,
			'show_current' => true,
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		// Don't show on homepage if disabled
		if ( is_front_page() && ! $args['show_on_home'] ) {
			return '';
		}
		
		// Don't show on front page if disabled
		if ( is_front_page() && ! $args['show_on_front'] ) {
			return '';
		}
		
		$breadcrumbs = array();
		
		// Add home breadcrumb
		if ( ! is_front_page() ) {
			$breadcrumbs[] = array(
				'url' => $args['home_url'],
				'text' => $args['home_text'],
			);
		}
		
		// Add breadcrumbs based on current page
		if ( is_home() ) {
			// Blog page
			$blog_page = get_option( 'page_for_posts' );
			if ( $blog_page ) {
				$breadcrumbs[] = array(
					'url' => '',
					'text' => get_the_title( $blog_page ),
				);
			}
		} elseif ( is_singular() ) {
			// Single post or page
			$post_type = get_post_type();
			
			// Add post type archive if it exists
			if ( $post_type !== 'page' && $post_type !== 'post' ) {
				$post_type_obj = get_post_type_object( $post_type );
				if ( $post_type_obj && $post_type_obj->has_archive ) {
					$breadcrumbs[] = array(
						'url' => get_post_type_archive_link( $post_type ),
						'text' => $post_type_obj->labels->name,
					);
				}
			}
			
			// Add categories for posts
			if ( $post_type === 'post' ) {
				$categories = get_the_category();
				if ( $categories && ! is_wp_error( $categories ) ) {
					$category = $categories[0];
					$breadcrumbs = array_merge( $breadcrumbs, $this->get_category_breadcrumbs( $category ) );
				}
			}
			
			// Add taxonomies for custom post types
			if ( $post_type !== 'page' && $post_type !== 'post' ) {
				$taxonomies = get_object_taxonomies( $post_type, 'objects' );
				foreach ( $taxonomies as $taxonomy ) {
					if ( $taxonomy->hierarchical && $taxonomy->public ) {
						$terms = get_the_terms( get_the_ID(), $taxonomy->name );
						if ( $terms && ! is_wp_error( $terms ) ) {
							$term = $terms[0];
							$breadcrumbs = array_merge( $breadcrumbs, $this->get_term_breadcrumbs( $term ) );
							break; // Only use the first hierarchical taxonomy
						}
					}
				}
			}
			
			// Add current post
			if ( $args['show_current'] ) {
				$breadcrumbs[] = array(
					'url' => '',
					'text' => get_the_title(),
				);
			}
		} elseif ( is_category() ) {
			// Category archive
			$category = get_queried_object();
			$breadcrumbs = array_merge( $breadcrumbs, $this->get_category_breadcrumbs( $category ) );
		} elseif ( is_tag() ) {
			// Tag archive
			$tag = get_queried_object();
			$breadcrumbs[] = array(
				'url' => '',
				'text' => __( 'Tag: ', 'seo-simple' ) . $tag->name,
			);
		} elseif ( is_tax() ) {
			// Custom taxonomy archive
			$term = get_queried_object();
			$breadcrumbs = array_merge( $breadcrumbs, $this->get_term_breadcrumbs( $term ) );
		} elseif ( is_author() ) {
			// Author archive
			$author = get_queried_object();
			$breadcrumbs[] = array(
				'url' => '',
				'text' => __( 'Author: ', 'seo-simple' ) . $author->display_name,
			);
		} elseif ( is_date() ) {
			// Date archive
			if ( is_day() ) {
				$breadcrumbs[] = array(
					'url' => get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) ),
					'text' => get_the_date( 'F Y' ),
				);
				$breadcrumbs[] = array(
					'url' => '',
					'text' => get_the_date( 'd' ),
				);
			} elseif ( is_month() ) {
				$breadcrumbs[] = array(
					'url' => '',
					'text' => get_the_date( 'F Y' ),
				);
			} elseif ( is_year() ) {
				$breadcrumbs[] = array(
					'url' => '',
					'text' => get_the_date( 'Y' ),
				);
			}
		} elseif ( is_search() ) {
			// Search results
			$breadcrumbs[] = array(
				'url' => '',
				'text' => __( 'Search Results for: ', 'seo-simple' ) . get_search_query(),
			);
		} elseif ( is_404() ) {
			// 404 page
			$breadcrumbs[] = array(
				'url' => '',
				'text' => __( '404 Not Found', 'seo-simple' ),
			);
		}
		
		// Build HTML
		$html = $args['before'];
		$count = count( $breadcrumbs );
		
		foreach ( $breadcrumbs as $index => $breadcrumb ) {
			$is_last = ( $index === $count - 1 );
			
			if ( $is_last ) {
				$html .= $args['before_current'] . esc_html( $breadcrumb['text'] ) . $args['after_current'];
			} else {
				$html .= $args['before_item'] . '<a href="' . esc_url( $breadcrumb['url'] ) . '">' . esc_html( $breadcrumb['text'] ) . '</a>' . $args['after_item'];
			}
		}
		
		$html .= $args['after'];
		
		// Store breadcrumbs for schema
		$this->current_breadcrumbs = $breadcrumbs;
		
		return apply_filters( 'seo_simple_breadcrumbs_html', $html, $breadcrumbs, $args );
	}

	/**
	 * Get category breadcrumbs.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    WP_Term    $category    The category term.
	 * @return   array                   The category breadcrumbs.
	 */
	private function get_category_breadcrumbs( $category ) {
		$breadcrumbs = array();
		
		// Get parent categories
		$parent_id = $category->parent;
		$parents = array();
		
		while ( $parent_id ) {
			$parent = get_term( $parent_id, 'category' );
			if ( is_wp_error( $parent ) ) {
				break;
			}
			$parents[] = $parent;
			$parent_id = $parent->parent;
		}
		
		// Add parents in reverse order
		$parents = array_reverse( $parents );
		foreach ( $parents as $parent ) {
			$breadcrumbs[] = array(
				'url' => get_category_link( $parent->term_id ),
				'text' => $parent->name,
			);
		}
		
		// Add current category
		$breadcrumbs[] = array(
			'url' => get_category_link( $category->term_id ),
			'text' => $category->name,
		);
		
		return $breadcrumbs;
	}

	/**
	 * Get term breadcrumbs.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    WP_Term    $term    The term.
	 * @return   array               The term breadcrumbs.
	 */
	private function get_term_breadcrumbs( $term ) {
		$breadcrumbs = array();
		
		// Get parent terms
		$parent_id = $term->parent;
		$parents = array();
		
		while ( $parent_id ) {
			$parent = get_term( $parent_id, $term->taxonomy );
			if ( is_wp_error( $parent ) ) {
				break;
			}
			$parents[] = $parent;
			$parent_id = $parent->parent;
		}
		
		// Add parents in reverse order
		$parents = array_reverse( $parents );
		foreach ( $parents as $parent ) {
			$breadcrumbs[] = array(
				'url' => get_term_link( $parent ),
				'text' => $parent->name,
			);
		}
		
		// Add current term
		$breadcrumbs[] = array(
			'url' => get_term_link( $term ),
			'text' => $term->name,
		);
		
		return $breadcrumbs;
	}

	/**
	 * Output breadcrumb schema markup.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function output_breadcrumb_schema() {
		if ( empty( $this->current_breadcrumbs ) ) {
			return;
		}
		
		$schema = $this->get_breadcrumb_schema( $this->current_breadcrumbs );
		if ( $schema ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>';
		}
	}

	/**
	 * Get breadcrumb schema data.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    array     $breadcrumbs    The breadcrumb data.
	 * @return   array                     The schema data.
	 */
	public function get_breadcrumb_schema( $breadcrumbs ) {
		if ( empty( $breadcrumbs ) ) {
			return false;
		}
		
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'BreadcrumbList',
			'itemListElement' => array(),
		);
		
		foreach ( $breadcrumbs as $index => $breadcrumb ) {
			$item = array(
				'@type' => 'ListItem',
				'position' => $index + 1,
				'name' => $breadcrumb['text'],
			);
			
			if ( ! empty( $breadcrumb['url'] ) ) {
				$item['item'] = $breadcrumb['url'];
			}
			
			$schema['itemListElement'][] = $item;
		}
		
		return apply_filters( 'seo_simple_breadcrumb_schema', $schema, $breadcrumbs );
	}

	/**
	 * Add breadcrumb schema to schema data filter.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    array     $schema_data    The current schema data.
	 * @param    int       $post_id        The post ID.
	 * @return   array                     The modified schema data.
	 */
	public function add_breadcrumb_schema( $schema_data, $post_id ) {
		if ( empty( $this->current_breadcrumbs ) ) {
			return $schema_data;
		}
		
		$breadcrumb_schema = $this->get_breadcrumb_schema( $this->current_breadcrumbs );
		if ( $breadcrumb_schema ) {
			$schema_data[] = $breadcrumb_schema;
		}
		
		return $schema_data;
	}

	/**
	 * Breadcrumb shortcode.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    array     $atts    Shortcode attributes.
	 * @return   string             The shortcode output.
	 */
	public function breadcrumb_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'separator' => '',
			'home_text' => '',
			'before' => '',
			'after' => '',
		), $atts, 'seo_breadcrumbs' );
		
		$args = array();
		if ( ! empty( $atts['separator'] ) ) {
			$args['separator'] = $atts['separator'];
		}
		if ( ! empty( $atts['home_text'] ) ) {
			$args['home_text'] = $atts['home_text'];
		}
		if ( ! empty( $atts['before'] ) ) {
			$args['before'] = $atts['before'];
		}
		if ( ! empty( $atts['after'] ) ) {
			$args['after'] = $atts['after'];
		}
		
		return $this->get_breadcrumbs( $args );
	}

	/**
	 * Register widgets for breadcrumbs.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function register_widgets() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-seo-simple-breadcrumbs-widget.php';
		register_widget( 'Seo_Simple_Breadcrumbs_Widget' );
	}
}