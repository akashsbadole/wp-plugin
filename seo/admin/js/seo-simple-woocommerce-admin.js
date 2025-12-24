/**
 * WooCommerce SEO admin JavaScript.
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/js
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Initialize WooCommerce SEO admin
		initWooCommerceSEO();
	});

	/**
	 * Initialize WooCommerce SEO functionality.
	 */
	function initWooCommerceSEO() {
		// Add bulk actions to product list
		addBulkActions();
		
		// Add quick action buttons to product list
		addQuickActions();
		
		// Initialize product analysis
		initProductAnalysis();
		
		// Add SEO score indicators
		addScoreIndicators();
	}

	/**
	 * Add bulk actions to product list.
	 */
	function addBulkActions() {
		// Add bulk action dropdown option
		$('#bulk-action-selector-top').append('<option value="seo_simple_bulk_optimize">' + seo_simple_woocommerce.text_bulk_optimize + '</option>');
		$('#bulk-action-selector-bottom').append('<option value="seo_simple_bulk_optimize">' + seo_simple_woocommerce.text_bulk_optimize + '</option>');
		
		// Handle bulk action submission
		$('#doaction, #doaction2').on('click', function(e) {
			var action = $(this).prev('select').val();
			
			if (action === 'seo_simple_bulk_optimize') {
				e.preventDefault();
				
				var productIds = [];
				$('input[name="post[]"]:checked').each(function() {
					productIds.push($(this).val());
				});
				
				if (productIds.length === 0) {
					alert(seo_simple_woocommerce.text_no_products);
					return;
				}
				
				if (!confirm(seo_simple_woocommerce.text_confirm_bulk)) {
					return;
				}
				
				// Show loading indicator
				$('#posts-filter').append('<div id="seo-simple-bulk-loading" class="seo-simple-bulk-loading"><div class="spinner is-active"></div> ' + seo_simple_woocommerce.text_optimizing + '</div>');
				
				// Perform bulk optimization
				$.ajax({
					url: seo_simple_woocommerce.ajax_url,
					type: 'POST',
					data: {
						action: 'seo_simple_woocommerce_bulk_optimize',
						nonce: seo_simple_woocommerce.nonce_bulk,
						product_ids: productIds
					},
					success: function(response) {
						if (response.success) {
							// Show success message
							$('#seo-simple-bulk-loading').html('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
							
							// Reload page after a delay
							setTimeout(function() {
								window.location.reload();
							}, 2000);
						} else {
							// Show error message
							$('#seo-simple-bulk-loading').html('<div class="notice notice-error is-dismissible"><p>' + (response.data.message || seo_simple_woocommerce.text_error) + '</p></div>');
						}
					},
					error: function() {
						// Show error message
						$('#seo-simple-bulk-loading').html('<div class="notice notice-error is-dismissible"><p>' + seo_simple_woocommerce.text_error + '</p></div>');
					}
				});
			}
		});
	}

	/**
	 * Add quick action buttons to product list.
	 */
	function addQuickActions() {
		// Add quick analyze button to each product row
		$('tr.type-product').each(function() {
			var productId = $(this).find('input[name="post[]"]').val();
			var titleCell = $(this).find('.title.column-title');
			
			if (titleCell.length && productId) {
				var analyzeButton = $('<button type="button" class="button button-small seo-simple-quick-analyze" data-product-id="' + productId + '">' + seo_simple_woocommerce.text_analyze + '</button>');
				titleCell.find('.row-actions').append(' | <span class="analyze">' + analyzeButton.prop('outerHTML') + '</span>');
			}
		});
		
		// Handle quick analyze button click
		$(document).on('click', '.seo-simple-quick-analyze', function(e) {
			e.preventDefault();
			
			var button = $(this);
			var productId = button.data('product-id');
			
			if (!productId) {
				return;
			}
			
			// Show loading state
			button.prop('disabled', true).text(seo_simple_woocommerce.text_analyzing);
			
			// Analyze product
			$.ajax({
				url: seo_simple_woocommerce.ajax_url,
				type: 'POST',
				data: {
					action: 'seo_simple_woocommerce_analyze_product',
					nonce: seo_simple_woocommerce.nonce_analyze,
					product_id: productId
				},
				success: function(response) {
					if (response.success) {
						// Show analysis results in a modal
						showAnalysisModal(response.data);
					} else {
						// Show error message
						alert(response.data.message || seo_simple_woocommerce.text_error);
					}
				},
				error: function() {
					// Show error message
					alert(seo_simple_woocommerce.text_error);
				},
				complete: function() {
					// Reset button state
					button.prop('disabled', false).text(seo_simple_woocommerce.text_analyze);
				}
			});
		});
	}

	/**
	 * Initialize product analysis.
	 */
	function initProductAnalysis() {
		// Add analysis button to product edit page
		if ($('#seo-simple-woocommerce-product').length) {
			// Add analyze button to the meta box
			$('#seo-simple-woocommerce-product').append('<div class="seo-simple-analyze-container"><button type="button" id="seo-simple-analyze-product" class="button button-secondary">' + seo_simple_woocommerce.text_analyze_seo + '</button><div id="seo-simple-analysis-results" style="display:none;"></div></div>');
			
			// Handle analyze button click
			$('#seo-simple-analyze-product').on('click', function() {
				var button = $(this);
				var resultsContainer = $('#seo-simple-analysis-results');
				var productId = $('#post_ID').val();
				
				if (!productId) {
					return;
				}
				
				// Show loading state
				button.prop('disabled', true).text(seo_simple_woocommerce.text_analyzing);
				resultsContainer.hide().html('<div class="spinner is-active"></div> ' + seo_simple_woocommerce.text_analyzing).slideDown();
				
				// Analyze product
				$.ajax({
					url: seo_simple_woocommerce.ajax_url,
					type: 'POST',
					data: {
						action: 'seo_simple_woocommerce_analyze_product',
						nonce: seo_simple_woocommerce.nonce_analyze,
						product_id: productId
					},
					success: function(response) {
						if (response.success) {
							// Display analysis results
							displayAnalysisResults(response.data, resultsContainer);
						} else {
							// Show error message
							resultsContainer.html('<div class="notice notice-error is-dismissible"><p>' + (response.data.message || seo_simple_woocommerce.text_error) + '</p></div>');
						}
					},
					error: function() {
						// Show error message
						resultsContainer.html('<div class="notice notice-error is-dismissible"><p>' + seo_simple_woocommerce.text_error + '</p></div>');
					},
					complete: function() {
						// Reset button state
						button.prop('disabled', false).text(seo_simple_woocommerce.text_analyze_seo);
					}
				});
			});
		}
	}

	/**
	 * Add SEO score indicators.
	 */
	function addScoreIndicators() {
		// Add color coding to SEO score columns
		$('.column-seo_score .seo-simple-score').each(function() {
			var score = parseInt($(this).text());
			
			if (score >= 80) {
				$(this).addClass('seo-simple-score-good');
			} else if (score >= 50) {
				$(this).addClass('seo-simple-score-warning');
			} else {
				$(this).addClass('seo-simple-score-bad');
			}
		});
	}

	/**
	 * Show analysis modal.
	 *
	 * @param {Object} data The analysis data.
	 */
	function showAnalysisModal(data) {
		// Create modal HTML
		var modalHtml = '<div id="seo-simple-analysis-modal" class="seo-simple-modal">' +
			'<div class="seo-simple-modal-content">' +
				'<div class="seo-simple-modal-header">' +
					'<h3>' + seo_simple_woocommerce.text_seo_analysis + '</h3>' +
					'<button type="button" class="seo-simple-modal-close">&times;</button>' +
				'</div>' +
				'<div class="seo-simple-modal-body">' +
					getAnalysisResultsHtml(data) +
				'</div>' +
				'<div class="seo-simple-modal-footer">' +
					'<button type="button" class="seo-simple-modal-close button">' + seo_simple_woocommerce.text_close + '</button>' +
				'</div>' +
			'</div>' +
			'<div class="seo-simple-modal-backdrop"></div>' +
		'</div>';
		
		// Add modal to page
		$('body').append(modalHtml);
		
		// Handle modal close
		$(document).on('click', '.seo-simple-modal-close, .seo-simple-modal-backdrop', function() {
			$('#seo-simple-analysis-modal').remove();
		});
	}

	/**
	 * Display analysis results.
	 *
	 * @param {Object} data The analysis data.
	 * @param {jQuery} container The container element.
	 */
	function displayAnalysisResults(data, container) {
		container.html(getAnalysisResultsHtml(data));
	}

	/**
	 * Get analysis results HTML.
	 *
	 * @param {Object} data The analysis data.
	 * @return {string} The HTML.
	 */
	function getAnalysisResultsHtml(data) {
		var html = '';
		
		// Overall score
		if (data.score) {
			var scoreClass = 'seo-simple-score-' + (data.score.value >= 80 ? 'good' : (data.score.value >= 50 ? 'warning' : 'bad'));
			html += '<div class="seo-simple-score-display">' +
				'<div class="seo-simple-score-circle ' + scoreClass + '">' + data.score.value + '</div>' +
				'<div class="seo-simple-score-details">' +
					'<div class="seo-simple-score-grade">' + data.score.grade + '</div>' +
					'<div class="seo-simple-score-text">' + seo_simple_woocommerce.text_seo_score + '</div>' +
				'</div>' +
			'</div>';
		}
		
		// Analysis results
		html += '<div class="seo-simple-analysis-results">';
		
		for (var key in data) {
			if (key !== 'score') {
				var item = data[key];
				var statusClass = 'seo-simple-status-' + item.status;
				
				html += '<div class="seo-simple-analysis-item ' + statusClass + '">' +
					'<div class="seo-simple-analysis-status">' + getStatusIcon(item.status) + '</div>' +
					'<div class="seo-simple-analysis-message">' + item.message + '</div>' +
				'</div>';
			}
		}
		
		html += '</div>';
		
		return html;
	}

	/**
	 * Get status icon HTML.
	 *
	 * @param {string} status The status.
	 * @return {string} The icon HTML.
	 */
	function getStatusIcon(status) {
		switch (status) {
			case 'good':
				return '<span class="dashicons dashicons-yes-alt"></span>';
			case 'warning':
				return '<span class="dashicons dashicons-warning"></span>';
			case 'error':
				return '<span class="dashicons dashicons-no-alt"></span>';
			default:
				return '<span class="dashicons dashicons-info"></span>';
		}
	}

})(jQuery);