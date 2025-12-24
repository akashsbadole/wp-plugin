<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		// Textdomain is now loaded in seo-simple.php on init action
		// This method is kept for compatibility but no longer loads the textdomain
		error_log('[SEO Simple I18n] load_plugin_textdomain called - textdomain is handled by main plugin file');
	}

	/**
	 * Custom translate function that uses the translation holder
	 *
	 * @since    1.0.0
	 * @param    string    $text     Text to translate.
	 * @param    string    $domain   Text domain.
	 * @return   string              Translated text.
	 */
	public static function translate( $text, $domain = 'seo-simple' ) {
		if (class_exists('Seo_Simple_Translation_Holder') && Seo_Simple_Translation_Holder::is_loaded()) {
			return __($text, $domain);
		}
		
		// Use early translation function if available
		if (function_exists('seo_simple_early_translate')) {
			return seo_simple_early_translate($text, $domain);
		}
		
		// Fallback to untranslated text
		return $text;
	}



}
