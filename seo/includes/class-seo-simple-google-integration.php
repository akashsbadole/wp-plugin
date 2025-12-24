<?php

/**
 * Handles Google Analytics, AdSense & Search Console integration.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles Google Analytics, AdSense & Search Console integration.
 *
 * This class is responsible for integrating with Google services including
 * Analytics, AdSense, and Search Console to provide SEO insights.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Google_Integration {

	/**
	 * The meta handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	private $seo_meta;

	/**
	 * The security handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Security    $security    The security handler instance.
	 */
	private $security;

	/**
	 * The cache handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Cache_Manager    $cache_manager    The cache handler instance.
	 */
	private $cache_manager;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Meta               $seo_meta       The SEO meta handler instance.
	 * @param    Seo_Simple_Security           $security       The security handler instance.
	 * @param    Seo_Simple_Cache_Manager      $cache_manager  The cache handler instance.
	 */
	public function __construct( $seo_meta, $security = null, $cache_manager = null ) {
		$this->seo_meta = $seo_meta;
		$this->security = $security;
		$this->cache_manager = $cache_manager;
	}

	/**
	 * Register hooks for Google integration.
	 *
	 * @since    1.0.0
	 */
	public function register_hooks() {
		// Add tracking codes to frontend
		add_action( 'wp_head', array( $this, 'output_google_analytics' ), 1 );
		add_action( 'wp_head', array( $this, 'output_google_adsense' ), 2 );
		add_action( 'wp_head', array( $this, 'output_google_site_verification' ), 3 );
		
		// Add Google Analytics to WooCommerce thank you page
		add_action( 'woocommerce_thankyou', array( $this, 'track_ecommerce_conversion' ) );
		
		// Add Google Analytics to EDD thank you page
		add_action( 'edd_payment_receipt_after', array( $this, 'track_edd_conversion' ) );
		
		// Add AJAX handlers
		add_action( 'wp_ajax_seo_simple_google_analytics_data', array( $this, 'ajax_get_analytics_data' ) );
		add_action( 'wp_ajax_seo_simple_google_search_console_data', array( $this, 'ajax_get_search_console_data' ) );
		add_action( 'wp_ajax_seo_simple_google_save_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_seo_simple_google_authenticate', array( $this, 'ajax_authenticate' ) );
		add_action( 'wp_ajax_seo_simple_google_revoke_access', array( $this, 'ajax_revoke_access' ) );
		
		// Add cron job for fetching data
		add_action( 'seo_simple_google_fetch_data', array( $this, 'fetch_google_data' ) );
		
		// Add dashboard widget
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
	}

	/**
	 * Output Google Analytics tracking code.
	 *
	 * @since    1.0.0
	 */
	public function output_google_analytics() {
		$settings = $this->seo_meta->get_global_settings();
		
		if ( empty( $settings['google_analytics_enabled'] ) || empty( $settings['google_analytics_id'] ) ) {
			return;
		}
		
		$tracking_id = $settings['google_analytics_id'];
		$anonymize_ip = isset( $settings['google_analytics_anonymize_ip'] ) ? $settings['google_analytics_anonymize_ip'] : false;
		$track_outbound = isset( $settings['google_analytics_track_outbound'] ) ? $settings['google_analytics_track_outbound'] : false;
		
		// Check if user should be excluded
		if ( $this->should_exclude_user() ) {
			return;
		}
		
		// Output Universal Analytics or Global Site Tag based on settings
		if ( isset( $settings['google_analytics_version'] ) && $settings['google_analytics_version'] === 'ga4' ) {
			$this->output_ga4_tracking_code( $tracking_id, $anonymize_ip, $track_outbound );
		} else {
			$this->output_universal_analytics_tracking_code( $tracking_id, $anonymize_ip, $track_outbound );
		}
	}

	/**
	 * Output Google Analytics 4 (GA4) tracking code.
	 *
	 * @since    1.0.0
	 * @param    string    $tracking_id     The GA tracking ID.
	 * @param    bool      $anonymize_ip    Whether to anonymize IP.
	 * @param    bool      $track_outbound  Whether to track outbound links.
	 */
	private function output_ga4_tracking_code( $tracking_id, $anonymize_ip, $track_outbound ) {
		?>
		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js( $tracking_id ); ?>"></script>
		<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		
		gtag('config', '<?php echo esc_js( $tracking_id ); ?>', {
			<?php if ( $anonymize_ip ) : ?>
			anonymize_ip: true,
			<?php endif; ?>
			<?php if ( is_singular() ) : ?>
			page_title: '<?php echo esc_js( wp_title( '|', false, 'right' ) ); ?>',
			page_location: window.location.href,
			<?php endif; ?>
		});
		</script>
		<?php
		
		if ( $track_outbound ) {
			$this->output_ga4_outbound_tracking();
		}
	}

	/**
	 * Output Universal Analytics tracking code.
	 *
	 * @since    1.0.0
	 * @param    string    $tracking_id     The GA tracking ID.
	 * @param    bool      $anonymize_ip    Whether to anonymize IP.
	 * @param    bool      $track_outbound  Whether to track outbound links.
	 */
	private function output_universal_analytics_tracking_code( $tracking_id, $anonymize_ip, $track_outbound ) {
		?>
		<!-- Google Analytics -->
		<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
		
		ga('create', '<?php echo esc_js( $tracking_id ); ?>', 'auto');
		<?php if ( $anonymize_ip ) : ?>
		ga('set', 'anonymizeIp', true);
		<?php endif; ?>
		<?php if ( is_singular() ) : ?>
		ga('set', 'title', '<?php echo esc_js( wp_title( '|', false, 'right' ) ); ?>');
		<?php endif; ?>
		ga('send', 'pageview');
		</script>
		<?php
		
		if ( $track_outbound ) {
			$this->output_universal_analytics_outbound_tracking();
		}
	}

	/**
	 * Output GA4 outbound link tracking.
	 *
	 * @since    1.0.0
	 */
	private function output_ga4_outbound_tracking() {
		?>
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			var links = document.querySelectorAll('a[href^="http"]:not([href*="' + window.location.hostname + '"])');
			
			links.forEach(function(link) {
				link.addEventListener('click', function(e) {
					var url = this.href;
					
					gtag('event', 'click', {
						event_category: 'outbound',
						event_label: url,
						transport_type: 'beacon'
					});
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Output Universal Analytics outbound link tracking.
	 *
	 * @since    1.0.0
	 */
	private function output_universal_analytics_outbound_tracking() {
		?>
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			var links = document.querySelectorAll('a[href^="http"]:not([href*="' + window.location.hostname + '"])');
			
			links.forEach(function(link) {
				link.addEventListener('click', function(e) {
					var url = this.href;
					
					ga('send', 'event', 'outbound', 'click', url, {
						'transport': 'beacon'
					});
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Output Google AdSense code.
	 *
	 * @since    1.0.0
	 */
	public function output_google_adsense() {
		$settings = $this->seo_meta->get_global_settings();
		
		if ( empty( $settings['google_adsense_enabled'] ) || empty( $settings['google_adsense_id'] ) ) {
			return;
		}
		
		$adsense_id = $settings['google_adsense_id'];
		
		// Check if user should be excluded
		if ( $this->should_exclude_user() ) {
			return;
		}
		
		?>
		<!-- Google AdSense -->
		<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?php echo esc_attr( $adsense_id ); ?>"
		     crossorigin="anonymous"></script>
		<?php
	}

	/**
	 * Output Google site verification code.
	 *
	 * @since    1.0.0
	 */
	public function output_google_site_verification() {
		$settings = $this->seo_meta->get_global_settings();
		
		if ( empty( $settings['google_site_verification'] ) ) {
			return;
		}
		
		$verification_code = $settings['google_site_verification'];
		
		?>
		<!-- Google Site Verification -->
		<meta name="google-site-verification" content="<?php echo esc_attr( $verification_code ); ?>" />
		<?php
	}

	/**
	 * Track e-commerce conversion in Google Analytics.
	 *
	 * @since    1.0.0
	 * @param    int       $order_id    The order ID.
	 */
	public function track_ecommerce_conversion( $order_id ) {
		$settings = $this->seo_meta->get_global_settings();
		
		if ( empty( $settings['google_analytics_enabled'] ) || empty( $settings['google_analytics_ecommerce'] ) ) {
			return;
		}
		
		$order = wc_get_order( $order_id );
		
		if ( ! $order ) {
			return;
		}
		
		$tracking_id = $settings['google_analytics_id'];
		$currency = $order->get_currency();
		$total = $order->get_total();
		
		// Get order items
		$items = array();
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			$items[] = array(
				'item_id' => $product->get_sku() ? $product->get_sku() : $product->get_id(),
				'item_name' => $item->get_name(),
				'category' => $this->get_product_category( $product ),
				'quantity' => $item->get_quantity(),
				'price' => $item->get_total(),
			);
		}
		
		// Output conversion tracking
		if ( isset( $settings['google_analytics_version'] ) && $settings['google_analytics_version'] === 'ga4' ) {
			$this->output_ga4_ecommerce_tracking( $tracking_id, $order_id, $currency, $total, $items );
		} else {
			$this->output_universal_analytics_ecommerce_tracking( $tracking_id, $order_id, $currency, $total, $items );
		}
	}

	/**
	 * Output GA4 e-commerce tracking.
	 *
	 * @since    1.0.0
	 * @param    string    $tracking_id    The GA tracking ID.
	 * @param    int       $order_id       The order ID.
	 * @param    string    $currency       The currency code.
	 * @param    float     $total          The order total.
	 * @param    array     $items          The order items.
	 */
	private function output_ga4_ecommerce_tracking( $tracking_id, $order_id, $currency, $total, $items ) {
		?>
		<!-- Google Analytics E-commerce Tracking -->
		<script>
		gtag('event', 'purchase', {
			transaction_id: '<?php echo esc_js( $order_id ); ?>',
			value: <?php echo esc_js( $total ); ?>,
			currency: '<?php echo esc_js( $currency ); ?>',
			items: <?php echo json_encode( $items ); ?>
		});
		</script>
		<?php
	}

	/**
	 * Output Universal Analytics e-commerce tracking.
	 *
	 * @since    1.0.0
	 * @param    string    $tracking_id    The GA tracking ID.
	 * @param    int       $order_id       The order ID.
	 * @param    string    $currency       The currency code.
	 * @param    float     $total          The order total.
	 * @param    array     $items          The order items.
	 */
	private function output_universal_analytics_ecommerce_tracking( $tracking_id, $order_id, $currency, $total, $items ) {
		?>
		<!-- Google Analytics E-commerce Tracking -->
		<script>
		ga('require', 'ecommerce');
		
		ga('ecommerce:addTransaction', {
			'id': '<?php echo esc_js( $order_id ); ?>',
			'revenue': '<?php echo esc_js( $total ); ?>',
			'currency': '<?php echo esc_js( $currency ); ?>'
		});
		
		<?php foreach ( $items as $item ) : ?>
		ga('ecommerce:addItem', {
			'id': '<?php echo esc_js( $order_id ); ?>',
			'name': '<?php echo esc_js( $item['item_name'] ); ?>',
			'sku': '<?php echo esc_js( $item['item_id'] ); ?>',
			'category': '<?php echo esc_js( $item['category'] ); ?>',
			'price': '<?php echo esc_js( $item['price'] ); ?>',
			'quantity': '<?php echo esc_js( $item['quantity'] ); ?>'
		});
		<?php endforeach; ?>
		
		ga('ecommerce:send');
		</script>
		<?php
	}

	/**
	 * Track Easy Digital Downloads conversion.
	 *
	 * @since    1.0.0
	 * @param    int       $payment_id    The payment ID.
	 */
	public function track_edd_conversion( $payment_id ) {
		$settings = $this->seo_meta->get_global_settings();
		
		if ( empty( $settings['google_analytics_enabled'] ) || empty( $settings['google_analytics_ecommerce'] ) ) {
			return;
		}
		
		$payment = edd_get_payment( $payment_id );
		
		if ( ! $payment ) {
			return;
		}
		
		$tracking_id = $settings['google_analytics_id'];
		$currency = edd_get_currency();
		$total = edd_get_payment_amount( $payment_id );
		
		// Get payment downloads
		$items = array();
		$downloads = edd_get_payment_meta_downloads( $payment_id );
		
		if ( $downloads ) {
			foreach ( $downloads as $download ) {
				$download_id = isset( $download['id'] ) ? $download['id'] : 0;
				$download_obj = get_post( $download_id );
				
				if ( $download_obj ) {
					$items[] = array(
						'item_id' => $download_id,
						'item_name' => $download_obj->post_title,
						'category' => 'Digital Download',
						'quantity' => isset( $download['quantity'] ) ? $download['quantity'] : 1,
						'price' => isset( $download['price'] ) ? $download['price'] : 0,
					);
				}
			}
		}
		
		// Output conversion tracking
		if ( isset( $settings['google_analytics_version'] ) && $settings['google_analytics_version'] === 'ga4' ) {
			$this->output_ga4_ecommerce_tracking( $tracking_id, $payment_id, $currency, $total, $items );
		} else {
			$this->output_universal_analytics_ecommerce_tracking( $tracking_id, $payment_id, $currency, $total, $items );
		}
	}

	/**
	 * Get product category for tracking.
	 *
	 * @since    1.0.0
	 * @param    WC_Product    $product    The product object.
	 * @return   string                    The product category.
	 */
	private function get_product_category( $product ) {
		$terms = get_the_terms( $product->get_id(), 'product_cat' );
		
		if ( $terms && ! is_wp_error( $terms ) ) {
			return $terms[0]->name;
		}
		
		return 'Product';
	}

	/**
	 * Check if current user should be excluded from tracking.
	 *
	 * @since    1.0.0
	 * @return   bool    True if user should be excluded.
	 */
	private function should_exclude_user() {
		$settings = $this->seo_meta->get_global_settings();
		
		if ( empty( $settings['google_analytics_exclude_roles'] ) ) {
			return false;
		}
		
		if ( ! is_user_logged_in() ) {
			return false;
		}
		
		$user = wp_get_current_user();
		$excluded_roles = $settings['google_analytics_exclude_roles'];
		
		return (bool) array_intersect( $user->roles, $excluded_roles );
	}

	/**
	 * AJAX handler for getting Analytics data.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_analytics_data() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_google_analytics_data' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to access this data.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : date( 'Y-m-d', strtotime( '-30 days' ) );
		$end_date = isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : date( 'Y-m-d' );
		$metrics = isset( $_POST['metrics'] ) ? array_map( 'sanitize_text_field', $_POST['metrics'] ) : array( 'sessions', 'users', 'pageviews' );
		$dimensions = isset( $_POST['dimensions'] ) ? array_map( 'sanitize_text_field', $_POST['dimensions'] ) : array();
		
		// Get data from cache or API
		$cache_key = 'google_analytics_' . md5( $start_date . $end_date . implode( ',', $metrics ) . implode( ',', $dimensions ) );
		
		if ( $this->cache_manager ) {
			$cached_data = $this->cache_manager->get( $cache_key );
			
			if ( $cached_data ) {
				wp_send_json_success( $cached_data );
			}
		}
		
		// Get data from API
		$data = $this->get_analytics_data( $start_date, $end_date, $metrics, $dimensions );
		
		if ( $data ) {
			// Cache the data
			if ( $this->cache_manager ) {
				$this->cache_manager->set( $cache_key, $data, 3600 ); // Cache for 1 hour
			}
			
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to retrieve Analytics data.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for getting Search Console data.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_search_console_data() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_google_search_console_data' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to access this data.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : date( 'Y-m-d', strtotime( '-30 days' ) );
		$end_date = isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : date( 'Y-m-d' );
		$dimensions = isset( $_POST['dimensions'] ) ? array_map( 'sanitize_text_field', $_POST['dimensions'] ) : array( 'page', 'query' );
		
		// Get data from cache or API
		$cache_key = 'google_search_console_' . md5( $start_date . $end_date . implode( ',', $dimensions ) );
		
		if ( $this->cache_manager ) {
			$cached_data = $this->cache_manager->get( $cache_key );
			
			if ( $cached_data ) {
				wp_send_json_success( $cached_data );
			}
		}
		
		// Get data from API
		$data = $this->get_search_console_data( $start_date, $end_date, $dimensions );
		
		if ( $data ) {
			// Cache the data
			if ( $this->cache_manager ) {
				$this->cache_manager->set( $cache_key, $data, 3600 ); // Cache for 1 hour
			}
			
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to retrieve Search Console data.', 'seo-simple' ) ) );
		}
	}

	/**
	 * Get Analytics data from API.
	 *
	 * @since    1.0.0
	 * @param    string    $start_date    The start date.
	 * @param    string    $end_date      The end date.
	 * @param    array     $metrics       The metrics to retrieve.
	 * @param    array     $dimensions    The dimensions to retrieve.
	 * @return   array|false              The data or false on failure.
	 */
	private function get_analytics_data( $start_date, $end_date, $metrics, $dimensions ) {
		$settings = $this->seo_meta->get_global_settings();
		
		if ( empty( $settings['google_analytics_enabled'] ) || empty( $settings['google_analytics_id'] ) ) {
			return false;
		}
		
		// This is a placeholder implementation
		// In a real implementation, you would use the Google Analytics API
		// For now, we'll return mock data
		
		return array(
			'total_sessions' => rand( 1000, 10000 ),
			'total_users' => rand( 800, 8000 ),
			'total_pageviews' => rand( 5000, 50000 ),
			'bounce_rate' => rand( 30, 70 ),
			'avg_session_duration' => rand( 60, 300 ),
			'top_pages' => array(
				array(
					'page' => '/',
					'pageviews' => rand( 1000, 5000 ),
					'sessions' => rand( 500, 2000 ),
				),
				array(
					'page' => '/about',
					'pageviews' => rand( 500, 2000 ),
					'sessions' => rand( 200, 1000 ),
				),
			),
		);
	}

	/**
	 * Get Search Console data from API.
	 *
	 * @since    1.0.0
	 * @param    string    $start_date    The start date.
	 * @param    string    $end_date      The end date.
	 * @param    array     $dimensions    The dimensions to retrieve.
	 * @return   array|false              The data or false on failure.
	 */
	private function get_search_console_data( $start_date, $end_date, $dimensions ) {
		$settings = $this->seo_meta->get_global_settings();
		
		if ( empty( $settings['google_search_console_enabled'] ) ) {
			return false;
		}
		
		// This is a placeholder implementation
		// In a real implementation, you would use the Google Search Console API
		// For now, we'll return mock data
		
		return array(
			'total_clicks' => rand( 100, 1000 ),
			'total_impressions' => rand( 1000, 10000 ),
			'avg_ctr' => rand( 1, 10 ),
			'avg_position' => rand( 1, 50 ),
			'top_queries' => array(
				array(
					'query' => 'example keyword',
					'clicks' => rand( 10, 100 ),
					'impressions' => rand( 100, 1000 ),
					'ctr' => rand( 1, 10 ),
					'position' => rand( 1, 20 ),
				),
				array(
					'query' => 'another keyword',
					'clicks' => rand( 10, 100 ),
					'impressions' => rand( 100, 1000 ),
					'ctr' => rand( 1, 10 ),
					'position' => rand( 1, 20 ),
				),
			),
			'top_pages' => array(
				array(
					'page' => '/',
					'clicks' => rand( 10, 100 ),
					'impressions' => rand( 100, 1000 ),
					'ctr' => rand( 1, 10 ),
					'position' => rand( 1, 20 ),
				),
				array(
					'page' => '/about',
					'clicks' => rand( 10, 100 ),
					'impressions' => rand( 100, 1000 ),
					'ctr' => rand( 1, 10 ),
					'position' => rand( 1, 20 ),
				),
			),
		);
	}

	/**
	 * Add dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function add_dashboard_widget() {
		wp_add_dashboard_widget(
			'seo-simple-google-analytics',
			__( 'Google Analytics', 'seo-simple' ),
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function render_dashboard_widget() {
		$settings = $this->seo_meta->get_global_settings();
		
		if ( empty( $settings['google_analytics_enabled'] ) || empty( $settings['google_analytics_id'] ) ) {
			echo '<p>' . __( 'Google Analytics is not configured. Please configure it in the SEO settings.', 'seo-simple' ) . '</p>';
			return;
		}
		
		// Get cached data
		$cache_key = 'google_analytics_dashboard_' . date( 'Y-m-d' );
		
		if ( $this->cache_manager ) {
			$cached_data = $this->cache_manager->get( $cache_key );
			
			if ( $cached_data ) {
				$this->display_dashboard_data( $cached_data );
				return;
			}
		}
		
		// Get fresh data
		$data = $this->get_analytics_data( date( 'Y-m-d', strtotime( '-30 days' ) ), date( 'Y-m-d' ), array( 'sessions', 'users', 'pageviews' ), array() );
		
		if ( $data ) {
			// Cache the data
			if ( $this->cache_manager ) {
				$this->cache_manager->set( $cache_key, $data, 3600 ); // Cache for 1 hour
			}
			
			$this->display_dashboard_data( $data );
		} else {
			echo '<p>' . __( 'Unable to retrieve Google Analytics data.', 'seo-simple' ) . '</p>';
		}
	}

	/**
	 * Display dashboard data.
	 *
	 * @since    1.0.0
	 * @param    array     $data    The data to display.
	 */
	private function display_dashboard_data( $data ) {
		?>
		<div class="seo-simple-google-stats">
			<div class="seo-simple-stat">
				<div class="seo-simple-stat-number"><?php echo number_format_i18n( $data['total_sessions'] ); ?></div>
				<div class="seo-simple-stat-label"><?php _e( 'Sessions', 'seo-simple' ); ?></div>
			</div>
			<div class="seo-simple-stat">
				<div class="seo-simple-stat-number"><?php echo number_format_i18n( $data['total_users'] ); ?></div>
				<div class="seo-simple-stat-label"><?php _e( 'Users', 'seo-simple' ); ?></div>
			</div>
			<div class="seo-simple-stat">
				<div class="seo-simple-stat-number"><?php echo number_format_i18n( $data['total_pageviews'] ); ?></div>
				<div class="seo-simple-stat-label"><?php _e( 'Pageviews', 'seo-simple' ); ?></div>
			</div>
		</div>
		
		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=seo-simple-settings&tab=google' ) ); ?>">
				<?php _e( 'View detailed analytics', 'seo-simple' ); ?>
			</a>
		</p>
		
		<style>
		.seo-simple-google-stats {
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
		.seo-simple-stat-label {
			font-size: 0.8em;
			color: #666;
		}
		</style>
		<?php
	}

	/**
	 * Fetch Google data via cron.
	 *
	 * @since    1.0.0
	 */
	public function fetch_google_data() {
		$settings = $this->seo_meta->get_global_settings();
		
		if ( empty( $settings['google_analytics_enabled'] ) || empty( $settings['google_analytics_id'] ) ) {
			return;
		}
		
		// Fetch Analytics data
		$analytics_data = $this->get_analytics_data(
			date( 'Y-m-d', strtotime( '-30 days' ) ),
			date( 'Y-m-d' ),
			array( 'sessions', 'users', 'pageviews' ),
			array()
		);
		
		if ( $analytics_data && $this->cache_manager ) {
			$cache_key = 'google_analytics_dashboard_' . date( 'Y-m-d' );
			$this->cache_manager->set( $cache_key, $analytics_data, 3600 );
		}
		
		// Fetch Search Console data
		if ( ! empty( $settings['google_search_console_enabled'] ) ) {
			$search_console_data = $this->get_search_console_data(
				date( 'Y-m-d', strtotime( '-30 days' ) ),
				date( 'Y-m-d' ),
				array( 'page', 'query' )
			);
			
			if ( $search_console_data && $this->cache_manager ) {
				$cache_key = 'google_search_console_dashboard_' . date( 'Y-m-d' );
				$this->cache_manager->set( $cache_key, $search_console_data, 3600 );
			}
		}
	}
}