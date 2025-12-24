<?php
// Simple script to run the HMS migration
require_once('wp-config.php');

// Include the migration class
require_once('includes/class-hms-migration.php');

// Run the migration
echo "Running HMS migration...\n";
HMS_Migration::run_migrations();
echo "Migration completed.\n";
?>