<?php
/**
 * Admin page template for White-Label Branding settings.
 *
 * This file is included by the HMS_White_Label::render_white_label_admin_page() method.
 * The actual rendering of the settings form is handled by the WordPress Settings API
 * called within that method. This file can be used to add additional static content
 * or custom HTML above/below the settings form if needed in the future.
 *
 * @package HospitalManagementSystem
 * @subpackage Admin/Templates
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// The actual form rendering is done by do_settings_sections() and settings_fields()
// in the parent class's render method. This file is a placeholder.
//
// You could add content here, for example:
// echo '<div class="notice notice-info"><p>' . esc_html__( 'Tip: For the best results, use high-resolution images for your logo and favicon.', 'hospital-management-system' ) . '</p></div>';
// 
// Or more complex HTML structures if the Settings API is not sufficient for some parts.
?>
