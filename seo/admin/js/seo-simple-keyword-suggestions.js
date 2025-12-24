/**
 * JavaScript for SEO Simple Keyword Suggestions.
 *
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
	'use strict';

	// Initialize keyword suggestions
	var seoKeywordSuggestions = {
		init: function() {
			this.bindEvents();
			this.loadTrendingKeywords();
		},

		bindEvents: function() {
			var self = this;

			// Get suggestions button click
			$(document).on('click', '.seo-simple-get-suggestions', function(e) {
				e.preventDefault();
				self.getSuggestions($(this));
			});

			// Keyword input enter key
			$(document).on('keypress', '.seo-simple-keyword-input', function(e) {
				if (e.which === 13) {
					e.preventDefault();
					$(this).siblings('.seo-simple-get-suggestions').click();
				}
			});

			// Use keyword button click
			$(document).on('click', '.seo-simple-use-keyword', function(e) {
				e.preventDefault();
				self.useKeyword($(this));
			});

			// Clear cache button click
			$(document).on('click', '.seo-simple-clear-cache', function(e) {
				e.preventDefault();
				self.clearCache($(this));
			});

			// Refresh trending keywords button click
			$(document).on('click', '.seo-simple-refresh-trending', function(e) {
				e.preventDefault();
				self.loadTrendingKeywords($(this));
			});
		},

		getSuggestions: function($button) {
			var self = this;
			var $container = $button.closest('.seo-simple-keyword-suggestions');
			var $input = $container.find('.seo-simple-keyword-input');
			var keyword = $input.val().trim();
			var postId = $button.data('post-id') || 0;
			var limit = $button.data('limit') || 10;

			if (!keyword) {
				this.showMessage($container.find('.suggestions-container'), seo_simple_keyword_suggestions.no_suggestions, 'error');
				return;
			}

			// Show loading state
			this.showLoading($container.find('.suggestions-container'));

			// Disable button
			$button.prop('disabled', true).text(seo_simple_keyword_suggestions.getting_suggestions);

			// Make AJAX request
			$.ajax({
				url: seo_simple_keyword_suggestions.ajax_url,
				type: 'POST',
				data: {
					action: 'seo_simple_get_keyword_suggestions',
					nonce: seo_simple_keyword_suggestions.nonce,
					keyword: keyword,
					post_id: postId,
					limit: limit
				},
				success: function(response) {
					if (response.success) {
						self.displaySuggestions($container.find('.suggestions-container'), response.data.suggestions);
					} else {
						self.showMessage($container.find('.suggestions-container'), response.data.message, 'error');
					}
				},
				error: function() {
					self.showMessage($container.find('.suggestions-container'), seo_simple_keyword_suggestions.error, 'error');
				},
				complete: function() {
					// Enable button
					$button.prop('disabled', false).text('Get Suggestions');
				}
			});
		},

		displaySuggestions: function($container, suggestions) {
			var self = this;

			if (!suggestions || suggestions.length === 0) {
				this.showMessage($container, seo_simple_keyword_suggestions.no_suggestions, 'empty');
				return;
			}

			var html = '';
			$.each(suggestions, function(index, suggestion) {
				html += self.createSuggestionHTML(suggestion);
			});

			$container.html(html);
		},

		createSuggestionHTML: function(suggestion) {
			var difficultyClass = this.getDifficultyClass(suggestion.difficulty);
			var difficultyLabel = this.getDifficultyLabel(suggestion.difficulty);
			var volumeLabel = this.getVolumeLabel(suggestion.volume);
			var volumePercent = Math.min(100, (suggestion.volume / 1000) * 100);

			var html = '<div class="suggestion-item">';
			html += '<div class="suggestion-keyword">' + this.escapeHtml(suggestion.keyword) + '</div>';
			html += '<div class="suggestion-metrics">';
			html += '<div class="suggestion-metric">';
			html += '<span class="difficulty-indicator ' + difficultyClass + '"></span>';
			html += '<span class="suggestion-metric-label">' + difficultyLabel + '</span>';
			html += '</div>';
			html += '<div class="suggestion-metric">';
			html += '<div class="volume-indicator"><div class="volume-indicator-fill" style="width: ' + volumePercent + '%;"></div></div>';
			html += '<span class="suggestion-metric-label">' + volumeLabel + '</span>';
			html += '</div>';
			html += '<div class="suggestion-metric">';
			html += '<span class="suggestion-metric-label">' + suggestion.volume + '/mo</span>';
			html += '</div>';
			html += '</div>';
			html += '<div class="suggestion-actions">';
			html += '<button class="seo-simple-use-keyword" data-keyword="' + this.escapeHtml(suggestion.keyword) + '">Use Keyword</button>';
			html += '</div>';
			html += '</div>';

			return html;
		},

		getDifficultyClass: function(difficulty) {
			if (difficulty < 30) {
				return 'difficulty-easy';
			} else if (difficulty < 60) {
				return 'difficulty-medium';
			} else {
				return 'difficulty-hard';
			}
		},

		getDifficultyLabel: function(difficulty) {
			if (difficulty < 30) {
				return 'Easy';
			} else if (difficulty < 60) {
				return 'Medium';
			} else {
				return 'Hard';
			}
		},

		getVolumeLabel: function(volume) {
			if (volume < 100) {
				return 'Very Low';
			} else if (volume < 500) {
				return 'Low';
			} else if (volume < 1000) {
				return 'Medium';
			} else if (volume < 5000) {
				return 'High';
			} else {
				return 'Very High';
			}
		},

		useKeyword: function($button) {
			var keyword = $button.data('keyword');
			var $container = $button.closest('.seo-simple-keyword-suggestions');
			var $focusKeywordInput = $('#seo_simple_focus_keyword');

			if ($focusKeywordInput.length) {
				$focusKeywordInput.val(keyword).trigger('change');
				this.showNotification('Keyword "' + keyword + '" has been set as focus keyword.', 'success');
			} else {
				// Try to find the focus keyword input in the meta box
				var $metaBox = $container.closest('.seo-simple-meta-box');
				$focusKeywordInput = $metaBox.find('input[name*="focus_keyword"]');

				if ($focusKeywordInput.length) {
					$focusKeywordInput.val(keyword).trigger('change');
					this.showNotification('Keyword "' + keyword + '" has been set as focus keyword.', 'success');
				} else {
					// Copy to clipboard as fallback
					this.copyToClipboard(keyword);
					this.showNotification('Keyword "' + keyword + '" has been copied to clipboard.', 'success');
				}
			}
		},

		copyToClipboard: function(text) {
			var $temp = $('<textarea>');
			$('body').append($temp);
			$temp.val(text).select();
			document.execCommand('copy');
			$temp.remove();
		},

		clearCache: function($button) {
			var self = this;
			var $container = $button.closest('.seo-simple-keyword-suggestions');
			var keyword = $container.find('.seo-simple-keyword-input').val().trim();

			// Show loading state
			$button.prop('disabled', true).text('Clearing...');

			// Make AJAX request
			$.ajax({
				url: seo_simple_keyword_suggestions.ajax_url,
				type: 'POST',
				data: {
					action: 'seo_simple_clear_keyword_cache',
					nonce: seo_simple_keyword_suggestions.nonce,
					keyword: keyword
				},
				success: function(response) {
					if (response.success) {
						self.showNotification(response.data.message, 'success');
						// Clear suggestions container
						$container.find('.suggestions-container').empty();
					} else {
						self.showNotification(response.data.message, 'error');
					}
				},
				error: function() {
					self.showNotification(seo_simple_keyword_suggestions.error, 'error');
				},
				complete: function() {
					// Enable button
					$button.prop('disabled', false).text('Clear Cache');
				}
			});
		},

		loadTrendingKeywords: function($button) {
			var self = this;
			var $container = $button ? $button.closest('.trending-keywords') : $('.trending-keywords');

			if (!$container.length) {
				return;
			}

			// Show loading state
			if ($button) {
				$button.prop('disabled', true).text('Loading...');
			}

			// Make AJAX request
			$.ajax({
				url: seo_simple_keyword_suggestions.ajax_url,
				type: 'POST',
				data: {
					action: 'seo_simple_get_trending_keywords',
					nonce: seo_simple_keyword_suggestions.nonce,
					limit: 5
				},
				success: function(response) {
					if (response.success) {
						self.displayTrendingKeywords($container, response.data.trending);
					} else {
						self.showTrendingError($container, response.data.message);
					}
				},
				error: function() {
					self.showTrendingError($container, seo_simple_keyword_suggestions.error);
				},
				complete: function() {
					// Enable button
					if ($button) {
						$button.prop('disabled', false).text('Refresh');
					}
				}
			});
		},

		displayTrendingKeywords: function($container, trending) {
			if (!trending || trending.length === 0) {
				this.showTrendingError($container, 'No trending keywords found.');
				return;
			}

			var html = '';
			$.each(trending, function(index, keyword) {
				html += '<div class="trending-keyword-item">';
				html += '<div class="trending-keyword-title">' + this.escapeHtml(keyword.keyword) + '</div>';
				html += '<div class="trending-keyword-metrics">';
				html += '<span class="difficulty-indicator ' + this.getDifficultyClass(keyword.difficulty) + '"></span>';
				html += '<span>' + this.getDifficultyLabel(keyword.difficulty) + '</span>';
				html += ' • ';
				html += '<span>' + keyword.volume + '/mo</span>';
				html += '</div>';
				html += '</div>';
			}.bind(this));

			$container.find('.trending-keywords-list').html(html);
		},

		showTrendingError: function($container, message) {
			$container.find('.trending-keywords-list').html('<div class="error-state">' + message + '</div>');
		},

		showLoading: function($container) {
			$container.html('<div class="loading">' + seo_simple_keyword_suggestions.getting_suggestions + '</div>');
		},

		showMessage: function($container, message, type) {
			$container.html('<div class="' + type + '-state">' + message + '</div>');
		},

		showNotification: function(message, type) {
			var $notification = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
			
			// Add to top of the page
			$('.wrap h1').first().after($notification);
			
			// Auto-hide after 3 seconds
			setTimeout(function() {
				$notification.fadeOut(function() {
					$(this).remove();
				});
			}, 3000);
			
			// Handle dismiss button
			$notification.on('click', '.notice-dismiss', function() {
				$notification.fadeOut(function() {
					$(this).remove();
				});
			});
		},

		escapeHtml: function(text) {
			var map = {
				'&': '&',
				'<': '<',
				'>': '>',
				'"': '"',
				"'": '&#039;'
			};
			
			return text.replace(/[&<>"']/g, function(m) { return map[m]; });
		}
	};

	// Initialize on document ready
	seoKeywordSuggestions.init();

	// Make it available globally
	window.seoKeywordSuggestions = seoKeywordSuggestions;
});