<?php
/**
 * Uninstall Easy Social Share
 *
 * @package Easy_Social_Share
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('ess_settings');

// Delete any transients we've created
delete_transient('ess_cache');