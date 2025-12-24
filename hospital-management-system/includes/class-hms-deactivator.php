<?php
// includes/class-hms-deactivator.php

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 * Deactivation is typically for temporary cleanup, not data removal.
 * Data removal is handled by the uninstall.php script.
 */
class HMS_Deactivator {

    /**
     * Fired when the plugin is deactivated.
     *
     * Handles tasks that need to be performed when the plugin is turned off.
     * This includes removing custom roles and flushing rewrite rules.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Remove custom roles created by the plugin.
        // This ensures that if the plugin is reactivated, roles are created fresh.
        self::remove_roles();

        // Flush rewrite rules to remove any CPT or taxonomy rewrite rules
        // added by the plugin. This prevents 404 errors on the front end
        // after deactivation.
        flush_rewrite_rules();
        
        // Note: Deactivation does not delete plugin data (tables, options, posts).
        // Data removal is handled by the uninstall.php script upon plugin deletion.
    }

    /**
     * Remove custom user roles created by the plugin.
     *
     * This ensures that any custom roles added during activation are
     * cleanly removed when the plugin is deactivated.
     */
    private static function remove_roles() {
        // List of custom roles actually created by the HMS_Activator.
        // This list should be kept in sync with the roles created in HMS_Activator::create_roles().
        $hms_roles_to_remove = array(
            'hms_patient', // This is the only custom role created by the activator.
        );

        foreach ($hms_roles_to_remove as $role) {
            if (get_role($role)) {
                remove_role($role);
            }
        }
    }
}
