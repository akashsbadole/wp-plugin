<?php

/**
 * Handles bulk SEO editing admin functionality.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Handles bulk SEO editing admin functionality.
 *
 * This class handles the admin interface for bulk SEO editing features.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Bulk_Editor_Admin {

	/**
	 * The meta handler instance.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	protected $seo_meta;

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
	 * Register hooks for bulk editing admin.
	 *
	 * @since    1.0.0
	 */
	public function register_hooks() {
		// Add admin menu
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		
		// Enqueue admin scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		// Handle bulk action notices
		add_action( 'admin_notices', array( $this, 'show_bulk_action_notices' ) );
		
		// Add inline editing functionality
		add_action( 'admin_init', array( $this, 'add_inline_editing' ) );
		
		// Add export/import functionality
		add_action( 'admin_init', array( $this, 'add_export_import' ) );
	}

	/**
	 * Add admin menu for bulk editing.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'seo-simple-settings',
			__( 'Bulk Editor', 'seo-simple' ),
			__( 'Bulk Editor', 'seo-simple' ),
			'manage_options',
			'seo-simple-bulk-editor',
			array( $this, 'render_bulk_editor_page' )
		);
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @since    1.0.0
	 * @param    string    $hook    The current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		// Only enqueue on our pages
		if (
			$hook === 'seo-simple_page_seo-simple-bulk-editor' ||
			$hook === 'edit.php' ||
			$hook === 'edit-post.php' ||
			$hook === 'edit-page.php' ||
			( $hook === 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'product' )
		) {
			wp_enqueue_script(
				'seo-simple-bulk-editor',
				plugin_dir_url( __FILE__ ) . 'js/seo-simple-bulk-editor.js',
				array( 'jquery', 'wp-util' ),
				SEO_SIMPLE_VERSION,
				true
			);
			
			// Enqueue export/import script specifically on the bulk editor page
			if ( $hook === 'seo-simple_page_seo-simple-bulk-editor' ) {
				wp_enqueue_script(
					'seo-simple-export-import',
					plugin_dir_url( __FILE__ ) . 'js/seo-simple-export-import.js',
					array( 'jquery', 'seo-simple-bulk-editor' ),
					SEO_SIMPLE_VERSION,
					true
				);
			}
			
			wp_localize_script(
				'seo-simple-bulk-editor',
				'seo_simple_bulk_editor',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce_bulk_edit' => wp_create_nonce( 'seo_simple_bulk_edit' ),
					'nonce_bulk_analyze' => wp_create_nonce( 'seo_simple_bulk_analyze' ),
					'nonce_bulk_optimize' => wp_create_nonce( 'seo_simple_bulk_optimize' ),
					'nonce_inline_edit' => wp_create_nonce( 'seo_simple_inline_edit' ),
					'nonce_export' => wp_create_nonce( 'seo_simple_export_seo' ),
					'nonce_import' => wp_create_nonce( 'seo_simple_import_seo' ),
					'text_confirm_bulk' => __( 'Are you sure you want to perform this action on the selected items?', 'seo-simple' ),
					'text_analyzing' => __( 'Analyzing...', 'seo-simple' ),
					'text_optimizing' => __( 'Optimizing...', 'seo-simple' ),
					'text_updating' => __( 'Updating...', 'seo-simple' ),
				)
			);
			
			wp_enqueue_style(
				'seo-simple-bulk-editor',
				plugin_dir_url( __FILE__ ) . 'css/seo-simple-bulk-editor.css',
				array(),
				SEO_SIMPLE_VERSION
			);
		}
	}

	/**
	 * Show bulk action notices.
	 *
	 * @since    1.0.0
	 */
	public function show_bulk_action_notices() {
		// Check if our bulk action was performed
		if ( isset( $_GET['seo_simple_bulk_action'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'seo_simple_bulk_action' ) ) {
			$action = sanitize_text_field( $_GET['seo_simple_bulk_action'] );
			$processed = isset( $_GET['processed'] ) ? intval( $_GET['processed'] ) : 0;
			$errors = isset( $_GET['errors'] ) ? intval( $_GET['errors'] ) : 0;
			
			$action_names = array(
				'seo_simple_analyze_seo' => __( 'SEO Analysis', 'seo-simple' ),
				'seo_simple_optimize_seo' => __( 'SEO Optimization', 'seo-simple' ),
				'seo_simple_reset_seo' => __( 'SEO Reset', 'seo-simple' ),
				'seo_simple_generate_schema' => __( 'Schema Generation', 'seo-simple' ),
			);
			
			$action_name = isset( $action_names[$action] ) ? $action_names[$action] : $action;
			
			if ( $processed > 0 ) {
				$message = sprintf(
					_n(
						'%s completed for %d item.',
						'%s completed for %d items.',
						$processed,
						'seo-simple'
					),
					$action_name,
					$processed
				);
				
				if ( $errors > 0 ) {
					$message .= ' ' . sprintf(
						_n(
							'%d error occurred.',
							'%d errors occurred.',
							$errors,
							'seo-simple'
						),
						$errors
					);
					
					echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
				} else {
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
				}
			} elseif ( $errors > 0 ) {
				echo '<div class="notice notice-error is-dismissible"><p>' . 
					sprintf(
						_n(
							'%d error occurred. No items were processed.',
							'%d errors occurred. No items were processed.',
							$errors,
							'seo-simple'
						),
						$errors
					) . 
				'</p></div>';
			}
		}
	}

	/**
	 * Add inline editing functionality.
	 *
	 * @since    1.0.0
	 */
	public function add_inline_editing() {
		// Add inline editing scripts to post list pages
		add_action( 'admin_footer-edit.php', array( $this, 'add_inline_editing_script' ) );
		add_action( 'admin_footer-edit.php', array( $this, 'add_inline_editing_style' ) );
	}

	/**
	 * Add inline editing script.
	 *
	 * @since    1.0.0
	 */
	public function add_inline_editing_script() {
		$screen = get_current_screen();
		
		if ( ! $screen || ! in_array( $screen->base, array( 'edit-post', 'edit-page', 'edit-product' ) ) ) {
			return;
		}
		
		?>
		<script>
		jQuery(document).ready(function($) {
			// Make SEO columns editable
			$('.column-seo_title, .column-seo_description, .column-seo_focus_keyword').on('click', function() {
				var $cell = $(this);
				var $row = $cell.closest('tr');
				var postId = $row.find('input[name="post[]"]').val();
				var field = $cell.data('column') || $cell.attr('class').match(/column-(\w+)/)[1];
				var currentValue = $cell.text().trim();
				
				// Skip if already editing or missing value
				if ($cell.find('input, textarea').length || currentValue === '<?php echo esc_js( __( 'Default', 'seo-simple' ) ); ?>' || currentValue === '<?php echo esc_js( __( 'Not set', 'seo-simple' ) ); ?>') {
					return;
				}
				
				// Create input field
				var $input;
				if (field === 'seo_description') {
					$input = $('<textarea></textarea>').val(currentValue);
				} else {
					$input = $('<input type="text">').val(currentValue);
				}
				
				// Replace cell content with input
				$cell.empty().append($input);
				$input.focus();
				
				// Handle save
				function saveInlineEdit() {
					var newValue = $input.val().trim();
					
					if (newValue !== currentValue) {
						// Show loading state
						$cell.html('<span class="spinner is-active"></span>');
						
						// Save via AJAX
						$.ajax({
							url: ajaxurl,
							type: 'POST',
							data: {
								action: 'seo_simple_inline_edit',
								nonce: seo_simple_bulk_editor.nonce_inline_edit,
								post_id: postId,
								field: field,
								value: newValue
							},
							success: function(response) {
								if (response.success) {
									$cell.text(newValue);
								} else {
									$cell.text(currentValue);
									alert(response.data.message || '<?php echo esc_js( __( 'Failed to update field.', 'seo-simple' ) ); ?>');
								}
							},
							error: function() {
								$cell.text(currentValue);
								alert('<?php echo esc_js( __( 'Request failed.', 'seo-simple' ) ); ?>');
							}
						});
					} else {
						$cell.text(currentValue);
					}
				}
				
				// Handle cancel
				function cancelInlineEdit() {
					$cell.text(currentValue);
				}
				
				// Save on blur or Enter key
				$input.on('blur', saveInlineEdit);
				$input.on('keydown', function(e) {
					if (e.which === 13) { // Enter
						e.preventDefault();
						saveInlineEdit();
					} else if (e.which === 27) { // Escape
						e.preventDefault();
						cancelInlineEdit();
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Add inline editing style.
	 *
	 * @since    1.0.0
	 */
	public function add_inline_editing_style() {
		$screen = get_current_screen();
		
		if ( ! $screen || ! in_array( $screen->base, array( 'edit-post', 'edit-page', 'edit-product' ) ) ) {
			return;
		}
		
		?>
		<style>
		.column-seo_title, .column-seo_description, .column-seo_focus_keyword {
			cursor: pointer;
			position: relative;
		}
		
		.column-seo_title:hover, .column-seo_description:hover, .column-seo_focus_keyword:hover {
			background-color: #f9f9f9;
		}
		
		.column-seo_title:after, .column-seo_description:after, .column-seo_focus_keyword:after {
			content: "\f464";
			font-family: dashicons;
			position: absolute;
			right: 5px;
			top: 50%;
			transform: translateY(-50%);
			opacity: 0.3;
			font-size: 16px;
		}
		
		.column-seo_title input, .column-seo_description textarea, .column-seo_focus_keyword input {
			width: 100%;
			padding: 5px;
			border: 1px solid #ddd;
			box-sizing: border-box;
		}
		
		.seo-simple-missing {
			color: #999;
			font-style: italic;
		}
		
		.seo-simple-score {
			display: inline-block;
			width: 40px;
			height: 20px;
			line-height: 20px;
			text-align: center;
			border-radius: 10px;
			font-size: 11px;
			font-weight: bold;
			color: #fff;
		}
		
		.seo-simple-score-green {
			background-color: #46b450;
		}
		
		.seo-simple-score-orange {
			background-color: #ffb900;
		}
		
		.seo-simple-score-red {
			background-color: #dc3232;
		}
		</style>
		<?php
	}

	/**
	 * Add export/import functionality.
	 *
	 * @since    1.0.0
	 */
	public function add_export_import() {
		// Add export/import buttons to post list pages
		add_action( 'manage_posts_extra_tablenav', array( $this, 'add_export_import_buttons' ) );
		add_action( 'manage_pages_extra_tablenav', array( $this, 'add_export_import_buttons' ) );
	}

	/**
	 * Add export/import buttons.
	 *
	 * @since    1.0.0
	 * @param    string    $which    The position of the table navigation.
	 */
	public function add_export_import_buttons( $which ) {
		if ( $which !== 'top' ) {
			return;
		}
		
		$screen = get_current_screen();
		
		if ( ! $screen ) {
			return;
		}
		
		$post_type = $screen->post_type;
		?>
		<div class="alignleft actions seo-simple-export-import">
			<button type="button" id="seo-simple-export-seo" class="button" data-post-type="<?php echo esc_attr( $post_type ); ?>">
				<?php _e( 'Export SEO', 'seo-simple' ); ?>
			</button>
			
			<button type="button" id="seo-simple-import-seo" class="button">
				<?php _e( 'Import SEO', 'seo-simple' ); ?>
			</button>
			
			<input type="file" id="seo-simple-import-file" accept=".csv,.json" style="display: none;" />
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			// Export SEO data
			$('#seo-simple-export-seo').on('click', function() {
				var postType = $(this).data('post-type');
				var format = confirm('<?php echo esc_js( __( 'Export as JSON? Click OK for JSON or Cancel for CSV.', 'seo-simple' ) ); ?>') ? 'json' : 'csv';
				
				// Show loading state
				$(this).prop('disabled', true).text('<?php echo esc_js( __( 'Exporting...', 'seo-simple' ) ); ?>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'seo_simple_export_seo',
						nonce: seo_simple_bulk_editor.nonce_export,
						post_type: postType,
						format: format
					},
					success: function(response) {
						if (response.success) {
							// Download the file
							window.location.href = response.data.file_url;
						} else {
							alert(response.data.message || '<?php echo esc_js( __( 'Export failed.', 'seo-simple' ) ); ?>');
						}
					},
					error: function() {
						alert('<?php echo esc_js( __( 'Request failed.', 'seo-simple' ) ); ?>');
					},
					complete: function() {
						// Reset button state
						$('#seo-simple-export-seo').prop('disabled', false).text('<?php echo esc_js( __( 'Export SEO', 'seo-simple' ) ); ?>');
					}
				});
			});
			
			// Import SEO data
			$('#seo-simple-import-seo').on('click', function() {
				$('#seo-simple-import-file').click();
			});
			
			$('#seo-simple-import-file').on('change', function() {
				var file = this.files[0];
				
				if (!file) {
					return;
				}
				
				// Check file type
				if (!file.name.match(/\.(csv|json)$/i)) {
					alert('<?php echo esc_js( __( 'Please select a CSV or JSON file.', 'seo-simple' ) ); ?>');
					return;
				}
				
				// Confirm import
				if (!confirm('<?php echo esc_js( __( 'This will overwrite existing SEO data. Are you sure?', 'seo-simple' ) ); ?>')) {
					return;
				}
				
				// Create form data
				var formData = new FormData();
				formData.append('action', 'seo_simple_import_seo');
				formData.append('nonce', seo_simple_bulk_editor.nonce_import);
				formData.append('import_file', file);
				
				// Show loading state
				$('#seo-simple-import-seo').prop('disabled', true).text('<?php echo esc_js( __( 'Importing...', 'seo-simple' ) ); ?>');
				
				// Send request
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function(response) {
						if (response.success) {
							alert(response.data.message);
							// Reload page to show changes
							location.reload();
						} else {
							alert(response.data.message || '<?php echo esc_js( __( 'Import failed.', 'seo-simple' ) ); ?>');
						}
					},
					error: function() {
						alert('<?php echo esc_js( __( 'Request failed.', 'seo-simple' ) ); ?>');
					},
					complete: function() {
						// Reset button state
						$('#seo-simple-import-seo').prop('disabled', false).text('<?php echo esc_js( __( 'Import SEO', 'seo-simple' ) ); ?>');
						// Clear file input
						$('#seo-simple-import-file').val('');
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Render the bulk editor page.
	 *
	 * @since    1.0.0
	 */
	public function render_bulk_editor_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<div class="seo-simple-bulk-editor-container">
				<div class="seo-simple-bulk-editor-tabs">
					<ul class="seo-simple-tab-nav">
						<li class="active"><a href="#tab-bulk-edit" data-tab="bulk-edit"><?php _e( 'Bulk Edit', 'seo-simple' ); ?></a></li>
						<li><a href="#tab-bulk-analyze" data-tab="bulk-analyze"><?php _e( 'Bulk Analyze', 'seo-simple' ); ?></a></li>
						<li><a href="#tab-bulk-optimize" data-tab="bulk-optimize"><?php _e( 'Bulk Optimize', 'seo-simple' ); ?></a></li>
						<li><a href="#tab-export-import" data-tab="export-import"><?php _e( 'Export/Import', 'seo-simple' ); ?></a></li>
					</ul>
					
					<div class="seo-simple-tab-content">
						<!-- Bulk Edit Tab -->
						<div id="tab-bulk-edit" class="seo-simple-tab-pane active">
							<?php include plugin_dir_path( __FILE__ ) . 'partials/bulk-editor-page.php'; ?>
						</div>
						
						<!-- Bulk Analyze Tab -->
						<div id="tab-bulk-analyze" class="seo-simple-tab-pane">
							<?php include plugin_dir_path( __FILE__ ) . 'partials/bulk-analyze-page.php'; ?>
						</div>
						
						<!-- Bulk Optimize Tab -->
						<div id="tab-bulk-optimize" class="seo-simple-tab-pane">
							<?php include plugin_dir_path( __FILE__ ) . 'partials/bulk-optimize-page.php'; ?>
						</div>
						
						<!-- Export/Import Tab -->
						<div id="tab-export-import" class="seo-simple-tab-pane">
							<?php include plugin_dir_path( __FILE__ ) . 'partials/export-import-page.php'; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<style>
		.seo-simple-bulk-editor-container {
			margin-top: 20px;
		}
		
		.seo-simple-bulk-editor-tabs {
			background: #fff;
			border: 1px solid #ccd0d4;
			box-shadow: 0 1px 1px rgba(0,0,0,.04);
		}
		
		.seo-simple-tab-nav {
			margin: 0;
			padding: 0;
			list-style: none;
			border-bottom: 1px solid #ccd0d4;
			background: #f8f9f9;
		}
		
		.seo-simple-tab-nav li {
			display: inline-block;
			margin: 0;
			padding: 0;
		}
		
		.seo-simple-tab-nav li a {
			display: block;
			padding: 10px 15px;
			text-decoration: none;
			color: #555;
			border-right: 1px solid #ddd;
		}
		
		.seo-simple-tab-nav li a:hover {
			background: #f1f1f1;
		}
		
		.seo-simple-tab-nav li.active a {
			background: #fff;
			color: #23282d;
			border-bottom: 1px solid #fff;
			margin-bottom: -1px;
		}
		
		.seo-simple-tab-content {
			padding: 20px;
		}
		
		.seo-simple-tab-pane {
			display: none;
		}
		
		.seo-simple-tab-pane.active {
			display: block;
		}
		</style>
		
		<script>
		jQuery(document).ready(function($) {
			// Tab switching functionality
			$('.seo-simple-tab-nav a').on('click', function(e) {
				e.preventDefault();
				
				var $tabLink = $(this);
				var tabName = $tabLink.data('tab');
				
				// Update nav
				$('.seo-simple-tab-nav li').removeClass('active');
				$tabLink.parent().addClass('active');
				
				// Update content
				$('.seo-simple-tab-pane').removeClass('active');
				$('#tab-' + tabName).addClass('active');
			});
		});
		</script>
		<?php
	}
}