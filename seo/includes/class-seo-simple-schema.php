<?php

/**
 * Handles structured data schema generation for SEO.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles structured data schema generation for SEO.
 *
 * This class is responsible for generating various types of structured data
 * schema including Article, WebPage, Product, BreadcrumbList, and FAQ schemas.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Schema {

	/**
	 * The meta handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	private $seo_meta;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	public function __construct( $seo_meta ) {
		$this->seo_meta = $seo_meta;
	}

	/**
	 * Generate schema data based on the current page context.
	 *
	 * @since    1.0.0
	 * @return   array    The schema data.
	 */
	public function generate_schema() {
		$schema = array();
		$context = array(
			'@context' => 'https://schema.org',
			'@graph'   => array()
		);

		// Add breadcrumb schema to all pages
		$breadcrumb_schema = $this->generate_breadcrumb_schema();
		if ( ! empty( $breadcrumb_schema ) ) {
			$context['@graph'][] = $breadcrumb_schema;
		}

		// Add specific schema based on page type
		if ( is_singular() ) {
			global $post;

			// Get schema type from post meta
			$schema_type = get_post_meta( $post->ID, '_seo_simple_schema_type', true );
			$custom_schema = get_post_meta( $post->ID, '_seo_simple_custom_schema', true );

			// If custom schema is provided, use it
			if ( ! empty( $custom_schema ) ) {
				$decoded_schema = json_decode( $custom_schema, true );
				if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded_schema ) ) {
					$context['@graph'][] = $decoded_schema;
				}
			} else {
				// Generate schema based on type or post type
				switch ( $schema_type ) {
					case 'article':
						$context['@graph'][] = $this->generate_article_schema( $post->ID );
						break;
					case 'webpage':
						$context['@graph'][] = $this->generate_webpage_schema( $post->ID );
						break;
					case 'product':
						if ( $this->is_woocommerce_active() && get_post_type( $post->ID ) === 'product' ) {
							$product = wc_get_product( $post->ID );
							if ( $product ) {
								$context['@graph'][] = $this->generate_product_schema( $product );
							}
						}
						break;
					case 'faq':
						$context['@graph'][] = $this->generate_faq_schema( $post->ID );
						break;
					case 'event':
						$context['@graph'][] = $this->generate_event_schema( $post->ID );
						break;
					case 'recipe':
						$context['@graph'][] = $this->generate_recipe_schema( $post->ID );
						break;
					case 'review':
						$context['@graph'][] = $this->generate_review_schema( $post->ID );
						break;
					case 'howto':
						$context['@graph'][] = $this->generate_howto_schema( $post->ID );
						break;
					case 'jobposting':
						$context['@graph'][] = $this->generate_job_posting_schema( $post->ID );
						break;
					case 'course':
						$context['@graph'][] = $this->generate_course_schema( $post->ID );
						break;
					case 'localbusiness':
						$context['@graph'][] = $this->generate_local_business_schema( $post->ID );
						break;
					case 'service':
						$context['@graph'][] = $this->generate_service_schema( $post->ID );
						break;
					case 'video':
						$context['@graph'][] = $this->generate_video_schema( $post->ID );
						break;
					case 'software':
						$context['@graph'][] = $this->generate_software_schema( $post->ID );
						break;
					case 'book':
						$context['@graph'][] = $this->generate_book_schema( $post->ID );
						break;
					case 'music':
						$context['@graph'][] = $this->generate_music_schema( $post->ID );
						break;
					case 'organization':
						$context['@graph'][] = $this->generate_organization_schema( $post->ID );
						break;
					case 'person':
						$context['@graph'][] = $this->generate_person_schema( $post->ID );
						break;
					case 'place':
						$context['@graph'][] = $this->generate_place_schema( $post->ID );
						break;
					case 'question':
						$context['@graph'][] = $this->generate_question_schema( $post->ID );
						break;
					case 'aggregate_rating':
						$context['@graph'][] = $this->generate_aggregate_rating_schema( $post->ID );
						break;
					default:
						// Auto-detect based on post type
						if ( get_post_type( $post->ID ) === 'product' && $this->is_woocommerce_active() ) {
							$product = wc_get_product( $post->ID );
							if ( $product ) {
								$context['@graph'][] = $this->generate_product_schema( $product );
							}
						} elseif ( get_post_type( $post->ID ) === 'post' ) {
							$context['@graph'][] = $this->generate_article_schema( $post->ID );
						} else {
							$context['@graph'][] = $this->generate_webpage_schema( $post->ID );
						}
						break;
				}
			}

			// Add FAQ schema if FAQ content is detected
			$faq_schema = $this->generate_faq_schema( $post->ID );
			if ( ! empty( $faq_schema ) ) {
				$context['@graph'][] = $faq_schema;
			}
		} elseif ( is_home() || is_front_page() ) {
			$context['@graph'][] = $this->generate_webpage_schema( get_option( 'page_on_front', 0 ), true );
		}

		// If we only have one schema item, return it directly
		if ( count( $context['@graph'] ) === 1 ) {
			$schema = $context['@graph'][0];
		} elseif ( ! empty( $context['@graph'] ) ) {
			$schema = $context;
		}

		// Apply filters for developers to modify schema
		return apply_filters( 'seo_simple_generate_schema', $schema, get_queried_object_id() );
	}

	/**
	 * Generate Article schema for posts.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The Article schema data.
	 */
	public function generate_article_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		$schema = array(
			'@type'         => 'Article',
			'headline'      => get_the_title( $post ),
			'description'   => $this->seo_meta->get_description( $post_id ),
			'url'           => get_permalink( $post_id ),
			'datePublished' => date( 'c', strtotime( $post->post_date ) ),
			'dateModified'  => date( 'c', strtotime( $post->post_modified ) ),
			'author'        => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $post->post_author ),
			),
			'publisher'     => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			),
		);

		// Add publisher logo if available
		$logo = $this->get_organization_logo();
		if ( $logo ) {
			$schema['publisher']['logo'] = array(
				'@type' => 'ImageObject',
				'url'   => $logo,
			);
		}

		// Add featured image if available
		if ( has_post_thumbnail( $post_id ) ) {
			$image_id = get_post_thumbnail_id( $post_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$schema['image'] = array(
					'@type' => 'ImageObject',
					'url'   => $image_url,
				);

				// Add image dimensions if available
				$image_meta = wp_get_attachment_metadata( $image_id );
				if ( $image_meta && isset( $image_meta['width'], $image_meta['height'] ) ) {
					$schema['image']['width'] = $image_meta['width'];
					$schema['image']['height'] = $image_meta['height'];
				}
			}
		}

		// Add article type based on categories
		$categories = get_the_category( $post_id );
		if ( $categories && ! is_wp_error( $categories ) ) {
			$main_category = $categories[0];
			
			// Determine article type based on category
			if ( in_array( strtolower( $main_category->name ), array( 'news', 'blog', 'article' ) ) ) {
				$schema['@type'] = 'NewsArticle';
			} elseif ( in_array( strtolower( $main_category->name ), array( 'tech', 'technology' ) ) ) {
				$schema['@type'] = 'TechArticle';
			}
			
			// Add article section
			$schema['articleSection'] = $main_category->name;
		}

		// Add tags as keywords
		$tags = get_the_tags( $post_id );
		if ( $tags && ! is_wp_error( $tags ) ) {
			$tag_names = array();
			foreach ( $tags as $tag ) {
				$tag_names[] = $tag->name;
			}
			$schema['keywords'] = implode( ', ', $tag_names );
		}

		// Add word count
		$content = strip_tags( strip_shortcodes( $post->post_content ) );
		$schema['wordCount'] = str_word_count( $content );

		// Apply filters
		return apply_filters( 'seo_simple_article_schema', $schema, $post_id );
	}

	/**
	 * Generate WebPage schema for pages.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @param    bool    $is_home    Whether this is the homepage.
	 * @return   array               The WebPage schema data.
	 */
	public function generate_webpage_schema( $post_id, $is_home = false ) {
		$post = get_post( $post_id );
		$title = $post ? get_the_title( $post ) : get_bloginfo( 'name' );
		$description = $post ? $this->seo_meta->get_description( $post_id ) : $this->seo_meta->get_homepage_description();
		$url = $post ? get_permalink( $post_id ) : home_url( '/' );

		$schema = array(
			'@type'       => 'WebPage',
			'name'        => $title,
			'description' => $description,
			'url'         => $url,
		);

		// Add last reviewed date
		if ( $post ) {
			$schema['dateModified'] = date( 'c', strtotime( $post->post_modified ) );
		}

		// Add breadcrumb if available
		$breadcrumb_schema = $this->generate_breadcrumb_schema();
		if ( ! empty( $breadcrumb_schema ) ) {
			$schema['breadcrumb'] = array(
				'@id' => home_url( '/' ) . '#breadcrumb'
			);
		}

		// Add special webpage type for homepage
		if ( $is_home ) {
			$schema['@type'] = 'WebSite';
			$schema['name'] = get_bloginfo( 'name' );
			$schema['url'] = home_url( '/' );
			
			// Add search action
			$schema['potentialAction'] = array(
				'@type'       => 'SearchAction',
				'target'      => home_url( '/?s={search_term_string}' ),
				'query-input' => 'required name=search_term_string'
			);
		}

		// Add featured image if available
		if ( $post && has_post_thumbnail( $post_id ) ) {
			$image_id = get_post_thumbnail_id( $post_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$schema['primaryImageOfPage'] = array(
					'@type' => 'ImageObject',
					'url'   => $image_url,
				);
			}
		}

		// Apply filters
		return apply_filters( 'seo_simple_webpage_schema', $schema, $post_id, $is_home );
	}

	/**
	 * Generate Product schema for WooCommerce products.
	 *
	 * @since    1.0.0
	 * @param    WC_Product    $product    The product object.
	 * @return   array                      The Product schema data.
	 */
	public function generate_product_schema( $product ) {
		if ( ! $product ) {
			return array();
		}

		$post_id = $product->get_id();
		$title = $this->seo_meta->get_title( $post_id );
		$description = $this->seo_meta->get_description( $post_id );

		$schema = array(
			'@type'       => 'Product',
			'name'        => $title,
			'description' => $description,
			'url'         => get_permalink( $post_id ),
			'sku'         => $product->get_sku(),
		);

		// Add brand if available
		$brand = $product->get_attribute( 'brand' );
		if ( $brand ) {
			$schema['brand'] = array(
				'@type' => 'Brand',
				'name'  => $brand,
			);
		}

		// Add images
		$images = array();
		
		// Add featured image
		if ( has_post_thumbnail( $post_id ) ) {
			$image_id = get_post_thumbnail_id( $post_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$images[] = $image_url;
			}
		}

		// Add gallery images
		$attachment_ids = $product->get_gallery_image_ids();
		foreach ( $attachment_ids as $attachment_id ) {
			$image_url = wp_get_attachment_image_url( $attachment_id, 'full' );
			if ( $image_url ) {
				$images[] = $image_url;
			}
		}

		if ( ! empty( $images ) ) {
			$schema['image'] = $images;
		}

		// Add price information
		if ( $product->get_price() > 0 ) {
			$schema['offers'] = array(
				'@type'         => 'Offer',
				'price'         => $product->get_price(),
				'priceCurrency' => get_woocommerce_currency(),
				'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
				'url'           => get_permalink( $post_id ),
				'seller'        => array(
					'@type' => 'Organization',
					'name'  => get_bloginfo( 'name' ),
				),
			);

			// Add sale price if available
			if ( $product->is_on_sale() && $product->get_sale_price() > 0 ) {
				$schema['offers']['priceSpecification'] = array(
					'@type'         => 'PriceSpecification',
					'price'         => $product->get_sale_price(),
					'priceCurrency' => get_woocommerce_currency(),
				);
			}

			// Add price valid until if available
			$date_on_sale_to = $product->get_date_on_sale_to();
			if ( $date_on_sale_to ) {
				$schema['offers']['priceValidUntil'] = $date_on_sale_to->format( 'Y-m-d' );
			}
		}

		// Add ratings if available
		$average_rating = $product->get_average_rating();
		if ( $average_rating > 0 ) {
			$schema['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => $average_rating,
				'reviewCount' => $product->get_review_count(),
				'bestRating'  => '5',
				'worstRating' => '1',
			);
		}

		// Add reviews if available
		$args = array(
			'post_id' => $post_id,
			'status'  => 'approve',
			'type'    => 'review',
		);

		$comments = get_comments( $args );
		if ( ! empty( $comments ) ) {
			$schema['review'] = array();

			foreach ( $comments as $comment ) {
				$rating = get_comment_meta( $comment->comment_ID, 'rating', true );

				$review = array(
					'@type'        => 'Review',
					'author'       => array(
						'@type' => 'Person',
						'name'  => $comment->comment_author,
					),
					'datePublished' => date( 'c', strtotime( $comment->comment_date ) ),
					'reviewBody'    => $comment->comment_content,
				);

				if ( $rating > 0 ) {
					$review['reviewRating'] = array(
						'@type'       => 'Rating',
						'ratingValue' => $rating,
						'bestRating'  => '5',
						'worstRating' => '1',
					);
				}

				$schema['review'][] = $review;
			}
		}

		// Add categories
		$categories = get_the_terms( $post_id, 'product_cat' );
		if ( $categories && ! is_wp_error( $categories ) ) {
			$category_names = array();
			foreach ( $categories as $category ) {
				$category_names[] = $category->name;
			}
			$schema['category'] = $category_names;
		}

		// Add tags
		$tags = get_the_terms( $post_id, 'product_tag' );
		if ( $tags && ! is_wp_error( $tags ) ) {
			$tag_names = array();
			foreach ( $tags as $tag ) {
				$tag_names[] = $tag->name;
			}
			$schema['keywords'] = implode( ', ', $tag_names );
		}

		// Add product attributes
		$attributes = $product->get_attributes();
		if ( ! empty( $attributes ) ) {
			$schema['additionalProperty'] = array();

			foreach ( $attributes as $attribute ) {
				if ( $attribute->get_visible() ) {
					$values = $product->get_attribute( $attribute->get_name() );
					if ( $values ) {
						$schema['additionalProperty'][] = array(
							'@type' => 'PropertyValue',
							'name'  => wc_attribute_label( $attribute->get_name() ),
							'value' => $values,
						);
					}
				}
			}
		}

		// Apply filters
		return apply_filters( 'seo_simple_product_schema', $schema, $product );
	}

	/**
	 * Generate BreadcrumbList schema.
	 *
	 * @since    1.0.0
	 * @return   array    The BreadcrumbList schema data.
	 */
	public function generate_breadcrumb_schema() {
		$breadcrumbs = array();
		
		if ( is_front_page() ) {
			return array();
		}

		// Add home as first breadcrumb
		$breadcrumbs[] = array(
			'@type'    => 'ListItem',
			'position' => 1,
			'name'     => get_bloginfo( 'name' ),
			'item'     => home_url( '/' ),
		);

		// Add breadcrumbs based on current page
		if ( is_home() && ! is_front_page() ) {
			// Posts page
			$posts_page_id = get_option( 'page_for_posts' );
			if ( $posts_page_id ) {
				$breadcrumbs[] = array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => get_the_title( $posts_page_id ),
					'item'     => get_permalink( $posts_page_id ),
				);
			}
		} elseif ( is_singular() ) {
			global $post;
			
			// Add post type archive if it exists
			$post_type = get_post_type_object( $post->post_type );
			if ( $post_type && $post_type->has_archive ) {
				$breadcrumbs[] = array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => $post_type->labels->name,
					'item'     => get_post_type_archive_link( $post->post_type ),
				);
			}

			// Add categories for posts
			if ( $post->post_type === 'post' ) {
				$categories = get_the_category( $post->ID );
				if ( $categories && ! is_wp_error( $categories ) ) {
					$category = $categories[0];
					$breadcrumbs[] = array(
						'@type'    => 'ListItem',
						'position' => count( $breadcrumbs ) + 1,
						'name'     => $category->name,
						'item'     => get_category_link( $category->term_id ),
					);
				}
			}

			// Add product categories for products
			elseif ( $post->post_type === 'product' && $this->is_woocommerce_active() ) {
				$product_categories = get_the_terms( $post->ID, 'product_cat' );
				if ( $product_categories && ! is_wp_error( $product_categories ) ) {
					$category = $product_categories[0];
					$breadcrumbs[] = array(
						'@type'    => 'ListItem',
						'position' => count( $breadcrumbs ) + 1,
						'name'     => $category->name,
						'item'     => get_term_link( $category ),
					);
				}
			}

			// Add current post/page
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => count( $breadcrumbs ) + 1,
				'name'     => get_the_title( $post ),
				'item'     => get_permalink( $post->ID ),
			);
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			
			// Add taxonomy archive if it exists
			$taxonomy = get_taxonomy( $term->taxonomy );
			if ( $taxonomy && $taxonomy->show_in_nav_menus ) {
				$breadcrumbs[] = array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => $taxonomy->labels->name,
					'item'     => get_post_type_archive_link( 'post' ),
				);
			}

			// Add current term
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => count( $breadcrumbs ) + 1,
				'name'     => $term->name,
				'item'     => get_term_link( $term ),
			);
		} elseif ( is_author() ) {
			$author = get_queried_object();
			
			// Add author archive
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => __( 'Author', 'seo-simple' ),
				'item'     => home_url( '/' ),
			);

			// Add current author
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => count( $breadcrumbs ) + 1,
				'name'     => $author->display_name,
				'item'     => get_author_posts_url( $author->ID ),
			);
		} elseif ( is_search() ) {
			// Add search results
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => __( 'Search Results', 'seo-simple' ),
				'item'     => get_search_link(),
			);
		} elseif ( is_404() ) {
			// Add 404 page
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => __( 'Page Not Found', 'seo-simple' ),
				'item'     => home_url( '/' ),
			);
		}

		if ( empty( $breadcrumbs ) ) {
			return array();
		}

		return array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $breadcrumbs,
		);
	}

	/**
	 * Generate FAQ schema from post content.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The FAQ schema data.
	 */
	public function generate_faq_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Check if FAQ block is used
		if ( has_block( 'seo-simple/faq', $post ) ) {
			return $this->generate_faq_schema_from_block( $post_id );
		}

		// Check if FAQ shortcode is used
		if ( has_shortcode( $post->post_content, 'seo_simple_faq' ) ) {
			return $this->generate_faq_schema_from_shortcode( $post_id );
		}

		// Auto-detect FAQ content
		return $this->generate_faq_schema_from_content( $post );
	}

	/**
	 * Generate FAQ schema from FAQ block.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The FAQ schema data.
	 */
	private function generate_faq_schema_from_block( $post_id ) {
		$blocks = parse_blocks( get_the_content( null, false, $post_id ) );
		$faqs = array();

		foreach ( $blocks as $block ) {
			if ( $block['blockName'] === 'seo-simple/faq' ) {
				$questions = isset( $block['attrs']['questions'] ) ? $block['attrs']['questions'] : array();
				
				foreach ( $questions as $question ) {
					if ( ! empty( $question['question'] ) && ! empty( $question['answer'] ) ) {
						$faqs[] = array(
							'@type'          => 'Question',
							'name'           => $question['question'],
							'acceptedAnswer' => array(
								'@type' => 'Answer',
								'text' => $question['answer'],
							),
						);
					}
				}
			}
		}

		if ( empty( $faqs ) ) {
			return array();
		}

		return array(
			'@type'       => 'FAQPage',
			'mainEntity'  => $faqs,
		);
	}

	/**
	 * Generate FAQ schema from FAQ shortcode.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The FAQ schema data.
	 */
	private function generate_faq_schema_from_shortcode( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get shortcode content
		$pattern = get_shortcode_regex();
		preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches );

		$faqs = array();

		foreach ( $matches[0] as $shortcode ) {
			if ( strpos( $shortcode, 'seo_simple_faq' ) !== false ) {
				// Extract attributes
				$attributes = shortcode_parse_atts( $shortcode );
				
				// Get questions from attribute if available
				if ( isset( $attributes['questions'] ) ) {
					$questions = json_decode( $attributes['questions'], true );
					if ( is_array( $questions ) ) {
						foreach ( $questions as $question ) {
							if ( ! empty( $question['question'] ) && ! empty( $question['answer'] ) ) {
								$faqs[] = array(
									'@type'          => 'Question',
									'name'           => $question['question'],
									'acceptedAnswer' => array(
										'@type' => 'Answer',
										'text' => $question['answer'],
									),
								);
							}
						}
					}
				}
			}
		}

		if ( empty( $faqs ) ) {
			return array();
		}

		return array(
			'@type'       => 'FAQPage',
			'mainEntity'  => $faqs,
		);
	}

	/**
	 * Generate FAQ schema by auto-detecting FAQ content.
	 *
	 * @since    1.0.0
	 * @param    WP_Post $post       The post object.
	 * @return   array               The FAQ schema data.
	 */
	private function generate_faq_schema_from_content( $post ) {
		$content = $post->post_content;
		$faqs = array();

		// Look for common FAQ patterns
		$patterns = array(
			'/<h([1-6])[^>]*>(.*?)<\/h[1-6]>(.*?)(?=<h[1-6]|$)/s', // Headings followed by content
			'/<p><strong>(.*?)<\/strong>(.*?)<\/p>/s', // Bold text in paragraphs
		);

		foreach ( $patterns as $pattern ) {
			preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER );

			foreach ( $matches as $match ) {
				$question = strip_tags( $match[2] );
				$answer = strip_tags( $match[3] );

				// Check if it looks like a question
				if (
					! empty( $question ) && is_string( $question ) &&
					( substr( $question, -1 ) === '?' ||
					  in_array( strtolower( substr( $question, 0, 3 ) ), array( 'how', 'what', 'why', 'when', 'who', 'where' ) ) ||
					  in_array( strtolower( substr( $question, 0, 2 ) ), array( 'is', 'are', 'do', 'can' ) )
					) &&
					! empty( $answer ) && 
					strlen( $answer ) > 20 
				) {
					$faqs[] = array(
						'@type'          => 'Question',
						'name'           => $question,
						'acceptedAnswer' => array(
							'@type' => 'Answer',
							'text' => $answer,
						),
					);
				}
			}
		}

		if ( empty( $faqs ) ) {
			return array();
		}

		return array(
			'@type'       => 'FAQPage',
			'mainEntity'  => $faqs,
		);
	}

	/**
	 * Generate Event schema.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The Event schema data.
	 */
	public function generate_event_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get event data from meta fields
		$start_date = get_post_meta( $post_id, '_seo_simple_event_start_date', true );
		$end_date = get_post_meta( $post_id, '_seo_simple_event_end_date', true );
		$location = get_post_meta( $post_id, '_seo_simple_event_location', true );
		$price = get_post_meta( $post_id, '_seo_simple_event_price', true );
		$availability = get_post_meta( $post_id, '_seo_simple_event_availability', true );

		if ( empty( $start_date ) || empty( $location ) ) {
			return array();
		}

		$schema = array(
			'@type'         => 'Event',
			'name'          => get_the_title( $post ),
			'description'   => $this->seo_meta->get_description( $post_id ),
			'url'           => get_permalink( $post_id ),
			'startDate'     => date( 'c', strtotime( $start_date ) ),
			'location'      => array(
				'@type'   => 'Place',
				'name'    => $location,
				'address' => $location,
			),
		);

		if ( ! empty( $end_date ) ) {
			$schema['endDate'] = date( 'c', strtotime( $end_date ) );
		}

		if ( ! empty( $price ) ) {
			$schema['offers'] = array(
				'@type'         => 'Offer',
				'price'         => $price,
				'priceCurrency' => get_option( 'seo_simple_currency', 'USD' ),
				'availability'  => ! empty( $availability ) ? $availability : 'https://schema.org/InStock',
				'url'           => get_permalink( $post_id ),
			);
		}

		// Add featured image if available
		if ( has_post_thumbnail( $post_id ) ) {
			$image_id = get_post_thumbnail_id( $post_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$schema['image'] = array(
					'@type' => 'ImageObject',
					'url'   => $image_url,
				);
			}
		}

		// Apply filters
		return apply_filters( 'seo_simple_event_schema', $schema, $post_id );
	}

	/**
	 * Generate Recipe schema.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The Recipe schema data.
	 */
	public function generate_recipe_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get recipe data from meta fields
		$prep_time = get_post_meta( $post_id, '_seo_simple_recipe_prep_time', true );
		$cook_time = get_post_meta( $post_id, '_seo_simple_recipe_cook_time', true );
		$total_time = get_post_meta( $post_id, '_seo_simple_recipe_total_time', true );
		$yield = get_post_meta( $post_id, '_seo_simple_recipe_yield', true );
		$ingredients = get_post_meta( $post_id, '_seo_simple_recipe_ingredients', true );
		$instructions = get_post_meta( $post_id, '_seo_simple_recipe_instructions', true );
		$calories = get_post_meta( $post_id, '_seo_simple_recipe_calories', true );

		if ( empty( $ingredients ) || empty( $instructions ) ) {
			return array();
		}

		$schema = array(
			'@type'         => 'Recipe',
			'name'          => get_the_title( $post ),
			'description'   => $this->seo_meta->get_description( $post_id ),
			'url'           => get_permalink( $post_id ),
			'author'        => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $post->post_author ),
			),
		);

		if ( ! empty( $prep_time ) ) {
			$schema['prepTime'] = 'PT' . $prep_time . 'M';
		}

		if ( ! empty( $cook_time ) ) {
			$schema['cookTime'] = 'PT' . $cook_time . 'M';
		}

		if ( ! empty( $total_time ) ) {
			$schema['totalTime'] = 'PT' . $total_time . 'M';
		}

		if ( ! empty( $yield ) ) {
			$schema['recipeYield'] = $yield;
		}

		if ( ! empty( $ingredients ) ) {
			$schema['recipeIngredient'] = is_array( $ingredients ) ? $ingredients : explode( "\n", $ingredients );
		}

		if ( ! empty( $instructions ) ) {
			$recipe_instructions = array();
			
			if ( is_array( $instructions ) ) {
				foreach ( $instructions as $instruction ) {
					$recipe_instructions[] = array(
						'@type' => 'HowToStep',
						'text'  => $instruction,
					);
				}
			} else {
				$steps = explode( "\n", $instructions );
				foreach ( $steps as $step ) {
					if ( ! empty( trim( $step ) ) ) {
						$recipe_instructions[] = array(
							'@type' => 'HowToStep',
							'text'  => trim( $step ),
						);
					}
				}
			}
			
			$schema['recipeInstructions'] = $recipe_instructions;
		}

		if ( ! empty( $calories ) ) {
			$schema['nutrition'] = array(
				'@type'   => 'NutritionInformation',
				'calories' => $calories,
			);
		}

		// Add featured image if available
		if ( has_post_thumbnail( $post_id ) ) {
			$image_id = get_post_thumbnail_id( $post_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$schema['image'] = array(
					'@type' => 'ImageObject',
					'url'   => $image_url,
				);
			}
		}

		// Apply filters
		return apply_filters( 'seo_simple_recipe_schema', $schema, $post_id );
	}

	/**
	 * Generate Review schema.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The Review schema data.
	 */
	public function generate_review_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get review data from meta fields
		$item_reviewed = get_post_meta( $post_id, '_seo_simple_review_item', true );
		$rating = get_post_meta( $post_id, '_seo_simple_review_rating', true );
		$review_body = get_post_meta( $post_id, '_seo_simple_review_body', true );

		if ( empty( $item_reviewed ) || empty( $rating ) ) {
			return array();
		}

		$schema = array(
			'@type'         => 'Review',
			'itemReviewed'  => array(
				'@type' => 'Thing',
				'name'  => $item_reviewed,
			),
			'reviewRating'  => array(
				'@type'       => 'Rating',
				'ratingValue' => $rating,
				'bestRating'  => '5',
				'worstRating' => '1',
			),
			'author'        => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $post->post_author ),
			),
			'datePublished' => date( 'c', strtotime( $post->post_date ) ),
		);

		if ( ! empty( $review_body ) ) {
			$schema['reviewBody'] = $review_body;
		} else {
			$schema['reviewBody'] = $this->seo_meta->get_description( $post_id );
		}

		// Add featured image if available
		if ( has_post_thumbnail( $post_id ) ) {
			$image_id = get_post_thumbnail_id( $post_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$schema['image'] = array(
					'@type' => 'ImageObject',
					'url'   => $image_url,
				);
			}
		}

		// Apply filters
		return apply_filters( 'seo_simple_review_schema', $schema, $post_id );
	}

	/**
	 * Generate HowTo schema.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The HowTo schema data.
	 */
	public function generate_howto_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get how-to data from meta fields
		$estimated_cost = get_post_meta( $post_id, '_seo_simple_howto_cost', true );
		$supplies = get_post_meta( $post_id, '_seo_simple_howto_supplies', true );
		$tools = get_post_meta( $post_id, '_seo_simple_howto_tools', true );
		$steps = get_post_meta( $post_id, '_seo_simple_howto_steps', true );

		if ( empty( $steps ) ) {
			return array();
		}

		$schema = array(
			'@type'         => 'HowTo',
			'name'          => get_the_title( $post ),
			'description'   => $this->seo_meta->get_description( $post_id ),
			'url'           => get_permalink( $post_id ),
		);

		if ( ! empty( $estimated_cost ) ) {
			$schema['estimatedCost'] = array(
				'@type'    => 'MonetaryAmount',
				'currency' => get_option( 'seo_simple_currency', 'USD' ),
				'value'    => $estimated_cost,
			);
		}

		if ( ! empty( $supplies ) ) {
			$howto_supplies = array();
			
			if ( is_array( $supplies ) ) {
				foreach ( $supplies as $supply ) {
					$howto_supplies[] = array(
						'@type' => 'HowToSupply',
						'name'  => $supply,
					);
				}
			} else {
				$supply_items = explode( "\n", $supplies );
				foreach ( $supply_items as $item ) {
					if ( ! empty( trim( $item ) ) ) {
						$howto_supplies[] = array(
							'@type' => 'HowToSupply',
							'name'  => trim( $item ),
						);
					}
				}
			}
			
			$schema['supply'] = $howto_supplies;
		}

		if ( ! empty( $tools ) ) {
			$howto_tools = array();
			
			if ( is_array( $tools ) ) {
				foreach ( $tools as $tool ) {
					$howto_tools[] = array(
						'@type' => 'HowToTool',
						'name'  => $tool,
					);
				}
			} else {
				$tool_items = explode( "\n", $tools );
				foreach ( $tool_items as $item ) {
					if ( ! empty( trim( $item ) ) ) {
						$howto_tools[] = array(
							'@type' => 'HowToTool',
							'name'  => trim( $item ),
						);
					}
				}
			}
			
			$schema['tool'] = $howto_tools;
		}

		if ( ! empty( $steps ) ) {
			$howto_steps = array();
			
			if ( is_array( $steps ) ) {
				foreach ( $steps as $index => $step ) {
					$howto_steps[] = array(
						'@type'    => 'HowToStep',
						'position' => $index + 1,
						'name'     => $step,
						'text'     => $step,
					);
				}
			} else {
				$step_items = explode( "\n", $steps );
				foreach ( $step_items as $index => $item ) {
					if ( ! empty( trim( $item ) ) ) {
						$howto_steps[] = array(
							'@type'    => 'HowToStep',
							'position' => $index + 1,
							'name'     => trim( $item ),
							'text'     => trim( $item ),
						);
					}
				}
			}
			
			$schema['step'] = $howto_steps;
		}

		// Add featured image if available
		if ( has_post_thumbnail( $post_id ) ) {
			$image_id = get_post_thumbnail_id( $post_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$schema['image'] = array(
					'@type' => 'ImageObject',
					'url'   => $image_url,
				);
			}
		}

		// Apply filters
		return apply_filters( 'seo_simple_howto_schema', $schema, $post_id );
	}

	/**
	 * Generate JobPosting schema.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The JobPosting schema data.
	 */
	public function generate_job_posting_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get job posting data from meta fields
		$company = get_post_meta( $post_id, '_seo_simple_job_company', true );
		$location = get_post_meta( $post_id, '_seo_simple_job_location', true );
		$employment_type = get_post_meta( $post_id, '_seo_simple_job_employment_type', true );
		$salary = get_post_meta( $post_id, '_seo_simple_job_salary', true );
		$qualifications = get_post_meta( $post_id, '_seo_simple_job_qualifications', true );
		$responsibilities = get_post_meta( $post_id, '_seo_simple_job_responsibilities', true );
		$date_posted = get_post_meta( $post_id, '_seo_simple_job_date_posted', true );
		$valid_through = get_post_meta( $post_id, '_seo_simple_job_valid_through', true );

		if ( empty( $company ) || empty( $location ) ) {
			return array();
		}

		$schema = array(
			'@type'         => 'JobPosting',
			'title'         => get_the_title( $post ),
			'description'   => $this->seo_meta->get_description( $post_id ),
			'url'           => get_permalink( $post_id ),
			'hiringOrganization' => array(
				'@type' => 'Organization',
				'name'  => $company,
			),
			'jobLocation'   => array(
				'@type'     => 'Place',
				'address'   => array(
					'@type'           => 'PostalAddress',
					'addressLocality' => $location,
				),
			),
		);

		if ( ! empty( $employment_type ) ) {
			$schema['employmentType'] = $employment_type;
		}

		if ( ! empty( $salary ) ) {
			$schema['baseSalary'] = array(
				'@type'         => 'MonetaryAmount',
				'currency'      => get_option( 'seo_simple_currency', 'USD' ),
				'value'         => array(
					'@type'    => 'QuantitativeValue',
					'value'    => $salary,
					'unitText' => 'YEAR',
				),
			);
		}

		if ( ! empty( $qualifications ) ) {
			$schema['qualifications'] = $qualifications;
		}

		if ( ! empty( $responsibilities ) ) {
			$schema['responsibilities'] = $responsibilities;
		}

		if ( ! empty( $date_posted ) ) {
			$schema['datePosted'] = date( 'c', strtotime( $date_posted ) );
		} else {
			$schema['datePosted'] = date( 'c', strtotime( $post->post_date ) );
		}

		if ( ! empty( $valid_through ) ) {
			$schema['validThrough'] = date( 'c', strtotime( $valid_through ) );
		}

		// Apply filters
		return apply_filters( 'seo_simple_job_posting_schema', $schema, $post_id );
	}

	/**
	 * Generate Course schema.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The Course schema data.
	 */
	public function generate_course_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get course data from meta fields
		$provider = get_post_meta( $post_id, '_seo_simple_course_provider', true );
		$description = get_post_meta( $post_id, '_seo_simple_course_description', true );
		$duration = get_post_meta( $post_id, '_seo_simple_course_duration', true );
		$price = get_post_meta( $post_id, '_seo_simple_course_price', true );

		if ( empty( $provider ) ) {
			return array();
		}

		$schema = array(
			'@type'         => 'Course',
			'name'          => get_the_title( $post ),
			'description'   => ! empty( $description ) ? $description : $this->seo_meta->get_description( $post_id ),
			'url'           => get_permalink( $post_id ),
			'provider'      => array(
				'@type' => 'Organization',
				'name'  => $provider,
			),
		);

		if ( ! empty( $duration ) ) {
			$schema['timeRequired'] = 'PT' . $duration . 'H';
		}

		if ( ! empty( $price ) ) {
			$schema['offers'] = array(
				'@type'         => 'Offer',
				'price'         => $price,
				'priceCurrency' => get_option( 'seo_simple_currency', 'USD' ),
				'availability'  => 'https://schema.org/InStock',
				'url'           => get_permalink( $post_id ),
			);
		}

		// Add featured image if available
		if ( has_post_thumbnail( $post_id ) ) {
			$image_id = get_post_thumbnail_id( $post_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$schema['image'] = array(
					'@type' => 'ImageObject',
					'url'   => $image_url,
				);
			}
		}

		// Apply filters
		return apply_filters( 'seo_simple_course_schema', $schema, $post_id );
	}

	/**
	 * Generate LocalBusiness schema.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The LocalBusiness schema data.
	 */
	public function generate_local_business_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get local business data from meta fields
		$business_type = get_post_meta( $post_id, '_seo_simple_business_type', true );
		$address = get_post_meta( $post_id, '_seo_simple_business_address', true );
		$phone = get_post_meta( $post_id, '_seo_simple_business_phone', true );
		$email = get_post_meta( $post_id, '_seo_simple_business_email', true );
		$hours = get_post_meta( $post_id, '_seo_simple_business_hours', true );
		$price_range = get_post_meta( $post_id, '_seo_simple_business_price_range', true );

		if ( empty( $business_type ) ) {
			$business_type = 'LocalBusiness';
		}

		$schema = array(
			'@type'         => $business_type,
			'name'          => get_the_title( $post ),
			'description'   => $this->seo_meta->get_description( $post_id ),
			'url'           => get_permalink( $post_id ),
		);

		if ( ! empty( $address ) ) {
			$schema['address'] = array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => $address,
			);
		}

		if ( ! empty( $phone ) ) {
			$schema['telephone'] = $phone;
		}

		if ( ! empty( $email ) ) {
			$schema['email'] = $email;
		}

		if ( ! empty( $hours ) ) {
			$schema['openingHours'] = $hours;
		}

		if ( ! empty( $price_range ) ) {
			$schema['priceRange'] = $price_range;
		}

		// Add featured image if available
		if ( has_post_thumbnail( $post_id ) ) {
			$image_id = get_post_thumbnail_id( $post_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$schema['image'] = array(
					'@type' => 'ImageObject',
					'url'   => $image_url,
				);
			}
		}

		// Apply filters
		return apply_filters( 'seo_simple_local_business_schema', $schema, $post_id );
	}

	/**
	 * Generate Service schema.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The Service schema data.
	 */
	public function generate_service_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get service data from meta fields
		$provider = get_post_meta( $post_id, '_seo_simple_service_provider', true );
		$service_type = get_post_meta( $post_id, '_seo_simple_service_type', true );
		$area = get_post_meta( $post_id, '_seo_simple_service_area', true );

		if ( empty( $provider ) ) {
			return array();
		}

		$schema = array(
			'@type'         => 'Service',
			'name'          => get_the_title( $post ),
			'description'   => $this->seo_meta->get_description( $post_id ),
			'url'           => get_permalink( $post_id ),
			'provider'      => array(
				'@type' => 'Organization',
				'name'  => $provider,
			),
		);

		if ( ! empty( $service_type ) ) {
			$schema['serviceType'] = $service_type;
		}

		if ( ! empty( $area ) ) {
			$schema['areaServed'] = array(
				'@type' => 'Place',
				'name'  => $area,
			);
		}

		// Apply filters
		return apply_filters( 'seo_simple_service_schema', $schema, $post_id );
	}

	/**
	 * Generate Video schema.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The Video schema data.
	 */
	public function generate_video_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get video data from meta fields
		$video_url = get_post_meta( $post_id, '_seo_simple_video_url', true );
		$thumbnail_url = get_post_meta( $post_id, '_seo_simple_video_thumbnail', true );
		$duration = get_post_meta( $post_id, '_seo_simple_video_duration', true );
		$upload_date = get_post_meta( $post_id, '_seo_simple_video_upload_date', true );

		if ( empty( $video_url ) ) {
			return array();
		}

		$schema = array(
			'@type'         => 'VideoObject',
			'name'          => get_the_title( $post ),
			'description'   => $this->seo_meta->get_description( $post_id ),
			'url'           => get_permalink( $post_id ),
			'contentUrl'    => $video_url,
		);

		if ( ! empty( $thumbnail_url ) ) {
			$schema['thumbnailUrl'] = $thumbnail_url;
		} elseif ( has_post_thumbnail( $post_id ) ) {
			$image_id = get_post_thumbnail_id( $post_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$schema['thumbnailUrl'] = $image_url;
			}
		}

		if ( ! empty( $duration ) ) {
			$schema['duration'] = 'PT' . $duration . 'S';
		}

		if ( ! empty( $upload_date ) ) {
			$schema['uploadDate'] = date( 'c', strtotime( $upload_date ) );
		} else {
			$schema['uploadDate'] = date( 'c', strtotime( $post->post_date ) );
		}

		// Apply filters
		return apply_filters( 'seo_simple_video_schema', $schema, $post_id );
	}

	/**
	 * Generate Software schema.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The Software schema data.
	 */
	public function generate_software_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get software data from meta fields
		$application_category = get_post_meta( $post_id, '_seo_simple_software_category', true );
		$operating_system = get_post_meta( $post_id, '_seo_simple_software_os', true );
		$download_url = get_post_meta( $post_id, '_seo_simple_software_download', true );
		$price = get_post_meta( $post_id, '_seo_simple_software_price', true );

		$schema = array(
			'@type'         => 'SoftwareApplication',
			'name'          => get_the_title( $post ),
			'description'   => $this->seo_meta->get_description( $post_id ),
			'url'           => get_permalink( $post_id ),
		);

		if ( ! empty( $application_category ) ) {
			$schema['applicationCategory'] = $application_category;
		}

		if ( ! empty( $operating_system ) ) {
			$schema['operatingSystem'] = $operating_system;
		}

		if ( ! empty( $download_url ) ) {
			$schema['downloadUrl'] = $download_url;
		}

		if ( ! empty( $price ) ) {
			$schema['offers'] = array(
				'@type'         => 'Offer',
				'price'         => $price,
				'priceCurrency' => get_option( 'seo_simple_currency', 'USD' ),
				'availability'  => 'https://schema.org/InStock',
				'url'           => get_permalink( $post_id ),
			);
		}

		// Add featured image if available
		if ( has_post_thumbnail( $post_id ) ) {
			$image_id = get_post_thumbnail_id( $post_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$schema['image'] = array(
					'@type' => 'ImageObject',
					'url'   => $image_url,
				);
			}
		}

		// Apply filters
		return apply_filters( 'seo_simple_software_schema', $schema, $post_id );
	}

	/**
	 * Generate Book schema.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The Book schema data.
	 */
	public function generate_book_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get book data from meta fields
		$author = get_post_meta( $post_id, '_seo_simple_book_author', true );
		$isbn = get_post_meta( $post_id, '_seo_simple_book_isbn', true );
		$pages = get_post_meta( $post_id, '_seo_simple_book_pages', true );
		$genre = get_post_meta( $post_id, '_seo_simple_book_genre', true );
		$price = get_post_meta( $post_id, '_seo_simple_book_price', true );
		$language = get_post_meta( $post_id, '_seo_simple_book_language', true );

		$schema = array(
			'@type'         => 'Book',
			'name'          => get_the_title( $post ),
			'description'   => $this->seo_meta->get_description( $post_id ),
			'url'           => get_permalink( $post_id ),
		);

		if ( ! empty( $author ) ) {
			$schema['author'] = array(
				'@type' => 'Person',
				'name'  => $author,
			);
		}

		if ( ! empty( $isbn ) ) {
			$schema['isbn'] = $isbn;
		}

		if ( ! empty( $pages ) ) {
			$schema['numberOfPages'] = $pages;
		}

		if ( ! empty( $genre ) ) {
			$schema['genre'] = $genre;
		}

		if ( ! empty( $language ) ) {
			$schema['inLanguage'] = $language;
		}

		if ( ! empty( $price ) ) {
			$schema['offers'] = array(
				'@type'         => 'Offer',
				'price'         => $price,
				'priceCurrency' => get_option( 'seo_simple_currency', 'USD' ),
				'availability'  => 'https://schema.org/InStock',
				'url'           => get_permalink( $post_id ),
			);
		}

		// Add featured image if available
		if ( has_post_thumbnail( $post_id ) ) {
			$image_id = get_post_thumbnail_id( $post_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$schema['image'] = array(
					'@type' => 'ImageObject',
					'url'   => $image_url,
				);
			}
		}

		// Apply filters
		return apply_filters( 'seo_simple_book_schema', $schema, $post_id );
	}

	/**
	 * Generate Music schema.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The Music schema data.
	 */
	public function generate_music_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get music data from meta fields
		$artist = get_post_meta( $post_id, '_seo_simple_music_artist', true );
		$album = get_post_meta( $post_id, '_seo_simple_music_album', true );
		$duration = get_post_meta( $post_id, '_seo_simple_music_duration', true );
		$genre = get_post_meta( $post_id, '_seo_simple_music_genre', true );
		$music_url = get_post_meta( $post_id, '_seo_simple_music_url', true );

		if ( empty( $artist ) ) {
			return array();
		}

		$schema = array(
			'@type'         => 'MusicRecording',
			'name'          => get_the_title( $post ),
			'description'   => $this->seo_meta->get_description( $post_id ),
			'url'           => get_permalink( $post_id ),
			'byArtist'      => array(
				'@type' => 'MusicGroup',
				'name'  => $artist,
			),
		);

		if ( ! empty( $album ) ) {
			$schema['inAlbum'] = array(
				'@type' => 'MusicAlbum',
				'name'  => $album,
			);
		}

		if ( ! empty( $duration ) ) {
			$schema['duration'] = 'PT' . $duration . 'S';
		}

		if ( ! empty( $genre ) ) {
			$schema['genre'] = $genre;
		}

		if ( ! empty( $music_url ) ) {
			$schema['contentUrl'] = $music_url;
		}

		// Add featured image if available
		if ( has_post_thumbnail( $post_id ) ) {
			$image_id = get_post_thumbnail_id( $post_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$schema['image'] = array(
					'@type' => 'ImageObject',
					'url'   => $image_url,
				);
			}
		}

		// Apply filters
		return apply_filters( 'seo_simple_music_schema', $schema, $post_id );
	}

	/**
	 * Generate Organization schema.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The Organization schema data.
	 */
	public function generate_organization_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get organization data from meta fields
		$organization_type = get_post_meta( $post_id, '_seo_simple_org_type', true );
		$address = get_post_meta( $post_id, '_seo_simple_org_address', true );
		$phone = get_post_meta( $post_id, '_seo_simple_org_phone', true );
		$email = get_post_meta( $post_id, '_seo_simple_org_email', true );
		$url = get_post_meta( $post_id, '_seo_simple_org_url', true );

		if ( empty( $organization_type ) ) {
			$organization_type = 'Organization';
		}

		$schema = array(
			'@type'         => $organization_type,
			'name'          => get_the_title( $post ),
			'description'   => $this->seo_meta->get_description( $post_id ),
			'url'           => ! empty( $url ) ? $url : get_permalink( $post_id ),
		);

		if ( ! empty( $address ) ) {
			$schema['address'] = array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => $address,
			);
		}

		if ( ! empty( $phone ) ) {
			$schema['telephone'] = $phone;
		}

		if ( ! empty( $email ) ) {
			$schema['email'] = $email;
		}

		// Add featured image if available
		if ( has_post_thumbnail( $post_id ) ) {
			$image_id = get_post_thumbnail_id( $post_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$schema['logo'] = array(
					'@type' => 'ImageObject',
					'url'   => $image_url,
				);
			}
		}

		// Apply filters
		return apply_filters( 'seo_simple_organization_schema', $schema, $post_id );
	}

	/**
	 * Generate Person schema.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The Person schema data.
	 */
	public function generate_person_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get person data from meta fields
		$job_title = get_post_meta( $post_id, '_seo_simple_person_job_title', true );
		$works_for = get_post_meta( $post_id, '_seo_simple_person_works_for', true );
		$address = get_post_meta( $post_id, '_seo_simple_person_address', true );
		$email = get_post_meta( $post_id, '_seo_simple_person_email', true );
		$phone = get_post_meta( $post_id, '_seo_simple_person_phone', true );
		$url = get_post_meta( $post_id, '_seo_simple_person_url', true );

		$schema = array(
			'@type'         => 'Person',
			'name'          => get_the_title( $post ),
			'description'   => $this->seo_meta->get_description( $post_id ),
			'url'           => ! empty( $url ) ? $url : get_permalink( $post_id ),
		);

		if ( ! empty( $job_title ) ) {
			$schema['jobTitle'] = $job_title;
		}

		if ( ! empty( $works_for ) ) {
			$schema['worksFor'] = array(
				'@type' => 'Organization',
				'name'  => $works_for,
			);
		}

		if ( ! empty( $address ) ) {
			$schema['address'] = array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => $address,
			);
		}

		if ( ! empty( $email ) ) {
			$schema['email'] = $email;
		}

		if ( ! empty( $phone ) ) {
			$schema['telephone'] = $phone;
		}

		// Add featured image if available
		if ( has_post_thumbnail( $post_id ) ) {
			$image_id = get_post_thumbnail_id( $post_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$schema['image'] = array(
					'@type' => 'ImageObject',
					'url'   => $image_url,
				);
			}
		}

		// Apply filters
		return apply_filters( 'seo_simple_person_schema', $schema, $post_id );
	}

	/**
	 * Generate Place schema.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The Place schema data.
	 */
	public function generate_place_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get place data from meta fields
		$address = get_post_meta( $post_id, '_seo_simple_place_address', true );
		$phone = get_post_meta( $post_id, '_seo_simple_place_phone', true );
		$hours = get_post_meta( $post_id, '_seo_simple_place_hours', true );

		if ( empty( $address ) ) {
			return array();
		}

		$schema = array(
			'@type'         => 'Place',
			'name'          => get_the_title( $post ),
			'description'   => $this->seo_meta->get_description( $post_id ),
			'url'           => get_permalink( $post_id ),
			'address'       => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => $address,
			),
		);

		if ( ! empty( $phone ) ) {
			$schema['telephone'] = $phone;
		}

		if ( ! empty( $hours ) ) {
			$schema['openingHours'] = $hours;
		}

		// Add featured image if available
		if ( has_post_thumbnail( $post_id ) ) {
			$image_id = get_post_thumbnail_id( $post_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$schema['image'] = array(
					'@type' => 'ImageObject',
					'url'   => $image_url,
				);
			}
		}

		// Apply filters
		return apply_filters( 'seo_simple_place_schema', $schema, $post_id );
	}

	/**
	 * Generate Question schema.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The Question schema data.
	 */
	public function generate_question_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get question data from meta fields
		$answer = get_post_meta( $post_id, '_seo_simple_question_answer', true );
		$answer_author = get_post_meta( $post_id, '_seo_simple_question_answer_author', true );
		$answer_date = get_post_meta( $post_id, '_seo_simple_question_answer_date', true );
		$upvotes = get_post_meta( $post_id, '_seo_simple_question_upvotes', true );

		if ( empty( $answer ) ) {
			return array();
		}

		$schema = array(
			'@type'          => 'Question',
			'name'           => get_the_title( $post ),
			'text'           => $this->seo_meta->get_description( $post_id ),
			'url'            => get_permalink( $post_id ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $answer,
			),
		);

		if ( ! empty( $answer_author ) ) {
			$schema['acceptedAnswer']['author'] = array(
				'@type' => 'Person',
				'name'  => $answer_author,
			);
		}

		if ( ! empty( $answer_date ) ) {
			$schema['acceptedAnswer']['dateCreated'] = date( 'c', strtotime( $answer_date ) );
		}

		if ( ! empty( $upvotes ) ) {
			$schema['acceptedAnswer']['upvoteCount'] = $upvotes;
		}

		// Apply filters
		return apply_filters( 'seo_simple_question_schema', $schema, $post_id );
	}

	/**
	 * Generate AggregateRating schema.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The AggregateRating schema data.
	 */
	public function generate_aggregate_rating_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get rating data from meta fields
		$rating_value = get_post_meta( $post_id, '_seo_simple_rating_value', true );
		$review_count = get_post_meta( $post_id, '_seo_simple_review_count', true );
		$best_rating = get_post_meta( $post_id, '_seo_simple_best_rating', true );
		$worst_rating = get_post_meta( $post_id, '_seo_simple_worst_rating', true );
		$item_reviewed = get_post_meta( $post_id, '_seo_simple_item_reviewed', true );

		if ( empty( $rating_value ) || empty( $review_count ) ) {
			return array();
		}

		$schema = array(
			'@type'       => 'AggregateRating',
			'ratingValue' => $rating_value,
			'reviewCount' => $review_count,
			'bestRating'  => ! empty( $best_rating ) ? $best_rating : '5',
			'worstRating' => ! empty( $worst_rating ) ? $worst_rating : '1',
		);

		if ( ! empty( $item_reviewed ) ) {
			$schema['itemReviewed'] = array(
				'@type' => 'Thing',
				'name'  => $item_reviewed,
			);
		}

		// Apply filters
		return apply_filters( 'seo_simple_aggregate_rating_schema', $schema, $post_id );
	}

	/**
	 * Validate schema data.
	 *
	 * @since    1.0.0
	 * @param    array   $schema    The schema data to validate.
	 * @return   bool               True if valid, false otherwise.
	 */
	public function validate_schema( $schema ) {
		if ( empty( $schema ) || ! is_array( $schema ) ) {
			return false;
		}

		// Check for required context
		if ( ! isset( $schema['@context'] ) || $schema['@context'] !== 'https://schema.org' ) {
			return false;
		}

		// Check for required type
		if ( ! isset( $schema['@type'] ) ) {
			return false;
		}

		// If it's a graph, validate each item
		if ( isset( $schema['@graph'] ) && is_array( $schema['@graph'] ) ) {
			foreach ( $schema['@graph'] as $item ) {
				if ( ! $this->validate_schema( $item ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Sanitize schema data.
	 *
	 * @since    1.0.0
	 * @param    array   $schema    The schema data to sanitize.
	 * @return   array              The sanitized schema data.
	 */
	public function sanitize_schema( $schema ) {
		if ( empty( $schema ) || ! is_array( $schema ) ) {
			return array();
		}

		// Recursively sanitize the schema
		return $this->sanitize_schema_recursive( $schema );
	}

	/**
	 * Recursively sanitize schema data.
	 *
	 * @since    1.0.0
	 * @param    mixed   $data      The data to sanitize.
	 * @return   mixed              The sanitized data.
	 */
	private function sanitize_schema_recursive( $data ) {
		if ( is_array( $data ) ) {
			$sanitized = array();
			foreach ( $data as $key => $value ) {
				// Skip empty values
				if ( $value === null || $value === '' ) {
					continue;
				}

				// Sanitize keys
				$sanitized_key = sanitize_text_field( $key );
				
				// Recursively sanitize values
				$sanitized[ $sanitized_key ] = $this->sanitize_schema_recursive( $value );
			}
			return $sanitized;
		} elseif ( is_string( $data ) ) {
			return wp_kses_post( $data );
		} elseif ( is_numeric( $data ) ) {
			return $data;
		} elseif ( is_bool( $data ) ) {
			return $data;
		}

		return array();
	}

	/**
	 * Get organization logo.
	 *
	 * @since    1.0.0
	 * @return   string    The logo URL or empty string if not available.
	 */
	private function get_organization_logo() {
		// Try to get logo from customizer
		$logo_id = get_theme_mod( 'custom_logo' );
		if ( $logo_id ) {
			$logo_url = wp_get_attachment_image_url( $logo_id, 'full' );
			if ( $logo_url ) {
				return $logo_url;
			}
		}

		// Try to get logo from global settings
		$settings = $this->seo_meta->get_global_settings();
		if ( isset( $settings['seo_organization_logo'] ) && ! empty( $settings['seo_organization_logo'] ) ) {
			return $settings['seo_organization_logo'];
		}

		return '';
	}

	/**
	 * Check if WooCommerce is active.
	 *
	 * @since    1.0.0
	 * @return   bool    True if WooCommerce is active.
	 */
	private function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}
}