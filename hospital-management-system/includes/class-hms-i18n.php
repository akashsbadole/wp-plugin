<?php
/**
 * HMS Internationalization Class
 *
 * Defines internationalization functionality.
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @package    Hospital_Management_System
 * @subpackage Hospital_Management_System/includes
 * @author     HMS Development Team
 * @since      1.0.0
 */
class HMS_i18n {

    /**
     * Load the plugin text domain for translation.
     *
     * This function hooks into 'plugins_loaded' to load the text domain
     * after all plugins have been loaded. It looks for translation files
     * in the /languages/ directory within the plugin.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain(): void {
        // The text domain is defined as a constant in the main plugin file.
        $text_domain = HMS_TEXT_DOMAIN;

        // The relative path to the plugin's languages directory.
        // The `plugin_basename()` function gets the plugin's base name (e.g., hospital-management-system/hospital-management-system.php).
        // `dirname()` is used twice to go up two directory levels from the includes folder to the plugin root.
        $languages_rel_path = dirname(dirname(plugin_basename(__FILE__))) . '/languages/';

        // Load the text domain.
        // The third parameter is the relative path to the languages directory from the WP_PLUGIN_DIR.
        // The second parameter is deprecated and should be false.
        load_plugin_textdomain(
            $text_domain,
            false,
            $languages_rel_path
        );
    }
}
