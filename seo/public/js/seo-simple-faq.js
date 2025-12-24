/**
 * FAQ JavaScript for SEO Simple Plugin
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/public/js
 */

(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(function() {
		// FAQ toggle functionality
		$(document).on('click', '[data-toggle="faq"]', function(e) {
			e.preventDefault();
			
			var $this = $(this);
			var targetId = $this.data('target');
			var $target = $('#' + targetId);
			var $faqItem = $this.closest('.seo-simple-faq-item');
			
			// Toggle FAQ item
			if ($faqItem.hasClass('active')) {
				$faqItem.removeClass('active');
				$target.slideUp(300);
			} else {
				// Close other FAQ items if accordion mode is enabled
				if ($('.seo-simple-faq-container').data('accordion') === 'true') {
					$('.seo-simple-faq-item.active').removeClass('active');
					$('.seo-simple-faq-answer').slideUp(300);
				}
				
				$faqItem.addClass('active');
				$target.slideDown(300);
			}
		});
		
		// FAQ expand/collapse all functionality
		$('.seo-simple-faq-expand-all').on('click', function(e) {
			e.preventDefault();
			
			$('.seo-simple-faq-item').addClass('active');
			$('.seo-simple-faq-answer').slideDown(300);
		});
		
		$('.seo-simple-faq-collapse-all').on('click', function(e) {
			e.preventDefault();
			
			$('.seo-simple-faq-item').removeClass('active');
			$('.seo-simple-faq-answer').slideUp(300);
		});
		
		// FAQ search functionality
		$('.seo-simple-faq-search').on('input', function() {
			var searchTerm = $(this).val().toLowerCase();
			
			$('.seo-simple-faq-item').each(function() {
				var $faqItem = $(this);
				var question = $faqItem.find('.seo-simple-faq-question h4').text().toLowerCase();
				var answer = $faqItem.find('.seo-simple-faq-answer-content').text().toLowerCase();
				
				if (question.indexOf(searchTerm) !== -1 || answer.indexOf(searchTerm) !== -1) {
					$faqItem.show();
				} else {
					$faqItem.hide();
				}
			});
			
			// Show message if no results found
			if ($('.seo-simple-faq-item:visible').length === 0) {
				if (!$('.seo-simple-faq-no-results').length) {
					$('.seo-simple-faq-container').append('<div class="seo-simple-faq-no-results">No FAQs found matching your search.</div>');
				}
			} else {
				$('.seo-simple-faq-no-results').remove();
			}
		});
		
		// FAQ category filter functionality
		$('.seo-simple-faq-category-filter').on('change', function() {
			var category = $(this).val();
			
			if (category === 'all') {
				$('.seo-simple-faq-item').show();
			} else {
				$('.seo-simple-faq-item').each(function() {
					var $faqItem = $(this);
					
					if ($faqItem.hasClass('seo-simple-faq-category-' + category)) {
						$faqItem.show();
					} else {
						$faqItem.hide();
					}
				});
			}
		});
		
		// Smooth scroll to FAQ when clicking on FAQ link
		$('a[href*="#seo-simple-faq-"]').on('click', function(e) {
			var targetId = $(this).attr('href').substring(1);
			var $target = $('#' + targetId);
			
			if ($target.length) {
				e.preventDefault();
				
				// Expand FAQ item if it's collapsed
				var $faqItem = $target.closest('.seo-simple-faq-item');
				if (!$faqItem.hasClass('active')) {
					$faqItem.find('.seo-simple-faq-question').trigger('click');
				}
				
				// Scroll to FAQ item
				$('html, body').animate({
					scrollTop: $target.offset().top - 50
				}, 500);
			}
		});
		
		// Add schema.org microdata attributes for better SEO
		if (typeof seo_simple_faq_schema !== 'undefined' && seo_simple_faq_schema === true) {
			$('.seo-simple-faq-item').each(function() {
				var $faqItem = $(this);
				var $question = $faqItem.find('.seo-simple-faq-question h4');
				var $answer = $faqItem.find('.seo-simple-faq-answer-content');
				
				// Add microdata attributes
				$faqItem.attr('itemscope', '');
				$faqItem.attr('itemtype', 'https://schema.org/Question');
				
				$question.attr('itemprop', 'name');
				$answer.closest('.seo-simple-faq-answer').attr('itemprop', 'acceptedAnswer');
				$answer.attr('itemprop', 'text');
			});
		}
		
		// Track FAQ interactions if analytics is enabled
		if (typeof seo_simple_faq_analytics !== 'undefined' && seo_simple_faq_analytics === true && typeof gtag !== 'undefined') {
			$(document).on('click', '[data-toggle="faq"]', function() {
				var question = $(this).find('h4').text();
				gtag('event', 'faq_click', {
					'faq_question': question
				});
			});
		}
		
		// Auto-expand first FAQ item if enabled
		if ($('.seo-simple-faq-container').data('auto-expand') === 'true') {
			var $firstFaq = $('.seo-simple-faq-item').first();
			if ($firstFaq.length) {
				$firstFaq.addClass('active');
				$firstFaq.find('.seo-simple-faq-answer').show();
			}
		}
	});

})( jQuery );