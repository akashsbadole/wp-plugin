<?php
/**
 * Test script to verify the Seo_Simple_Roles::get_custom_roles() fix
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    // If not in WordPress context, simulate basic WordPress functions for testing
    if ( ! function_exists( '__' ) ) {
        function __( $text, $domain = 'default' ) {
            return $text;
        }
    }
    
    if ( ! function_exists( 'add_action' ) ) {
        function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
            return true;
        }
    }
    
    if ( ! function_exists( 'register_activation_hook' ) ) {
        function register_activation_hook( $file, $function ) {
            return true;
        }
    }
    
    if ( ! function_exists( 'register_deactivation_hook' ) ) {
        function register_deactivation_hook( $file, $function ) {
            return true;
        }
    }
    
    if ( ! function_exists( 'get_option' ) ) {
        function get_option( $option, $default = false ) {
            return $default;
        }
    }
    
    if ( ! function_exists( 'update_option' ) ) {
        function update_option( $option, $value ) {
            return true;
        }
    }
    
    if ( ! function_exists( 'add_role' ) ) {
        function add_role( $role, $display_name, $capabilities = array() ) {
            return (object) array( 'name' => $display_name );
        }
    }
    
    if ( ! function_exists( 'get_role' ) ) {
        function get_role( $role ) {
            return (object) array( 'name' => $role );
        }
    }
    
    if ( ! function_exists( 'remove_role' ) ) {
        function remove_role( $role ) {
            return true;
        }
    }
    
    if ( ! function_exists( 'get_users' ) ) {
        function get_users( $args = array() ) {
            return array();
        }
    }
    
    if ( ! function_exists( 'current_user_can' ) ) {
        function current_user_can( $capability ) {
            return true;
        }
    }
    
    if ( ! function_exists( 'wp_verify_nonce' ) ) {
        function wp_verify_nonce( $nonce, $action = -1 ) {
            return true;
        }
    }
    
    if ( ! function_exists( 'wp_create_nonce' ) ) {
        function wp_create_nonce( $action = -1 ) {
            return 'test_nonce';
        }
    }
    
    if ( ! function_exists( 'sanitize_text_field' ) ) {
        function sanitize_text_field( $str ) {
            return trim( strip_tags( $str ) );
        }
    }
    
    if ( ! function_exists( 'wp_send_json_success' ) ) {
        function wp_send_json_success( $data = null ) {
            echo json_encode( array( 'success' => true, 'data' => $data ) );
        }
    }
    
    if ( ! function_exists( 'wp_send_json_error' ) ) {
        function wp_send_json_error( $data = null ) {
            echo json_encode( array( 'success' => false, 'data' => $data ) );
        }
    }
    
    if ( ! function_exists( 'check_admin_referer' ) ) {
        function check_admin_referer( $action = -1, $query_arg = '_wpnonce' ) {
            return true;
        }
    }
    
    if ( ! function_exists( 'wp_safe_redirect' ) ) {
        function wp_safe_redirect( $location, $status = 302 ) {
            return true;
        }
    }
    
    if ( ! function_exists( 'wp_get_referer' ) ) {
        function wp_get_referer() {
            return '';
        }
    }
    
    if ( ! function_exists( 'add_settings_error' ) ) {
        function add_settings_error( $setting, $code, $message, $type = 'error' ) {
            return true;
        }
    }
    
    if ( ! function_exists( 'set_transient' ) ) {
        function set_transient( $transient, $value, $expiration = 0 ) {
            return true;
        }
    }
    
    if ( ! function_exists( 'get_settings_errors' ) ) {
        function get_settings_errors( $setting = false, $sanitize = false ) {
            return array();
        }
    }
    
    if ( ! function_exists( 'add_submenu_page' ) ) {
        function add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
            return true;
        }
    }
    
    if ( ! function_exists( 'plugin_dir_path' ) ) {
        function plugin_dir_path( $file ) {
            return dirname( $file ) . '/';
        }
    }
    
    if ( ! function_exists( 'plugin_dir_url' ) ) {
        function plugin_dir_url( $file ) {
            return 'http://example.com/';
        }
    }
    
    if ( ! function_exists( 'admin_url' ) ) {
        function admin_url( $path ) {
            return 'http://example.com/wp-admin/' . $path;
        }
    }
    
    if ( ! function_exists( 'wp_enqueue_script' ) ) {
        function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
            return true;
        }
    }
    
    if ( ! function_exists( 'wp_enqueue_style' ) ) {
        function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
            return true;
        }
    }
    
    if ( ! function_exists( 'wp_localize_script' ) ) {
        function wp_localize_script( $handle, $object_name, $l10n ) {
            return true;
        }
    }
    
    if ( ! function_exists( 'register_setting' ) ) {
        function register_setting( $option_group, $option_name, $args = array() ) {
            return true;
        }
    }
    
    if ( ! function_exists( 'settings_errors' ) ) {
        function settings_errors( $setting = '' ) {
            return '';
        }
    }
    
    if ( ! function_exists( 'submit_button' ) ) {
        function submit_button( $text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = null ) {
            echo '<button type="submit">' . ( $text ? $text : 'Submit' ) . '</button>';
        }
    }
    
    if ( ! function_exists( 'checked' ) ) {
        function checked( $checked, $current = true, $echo = true ) {
            return (bool) $checked;
        }
    }
    
    if ( ! function_exists( 'esc_attr' ) ) {
        function esc_attr( $text ) {
            return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
        }
    }
    
    if ( ! function_exists( 'esc_html' ) ) {
        function esc_html( $text ) {
            return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
        }
    }
    
    // Mock global wp_roles
    if ( ! isset( $GLOBALS['wp_roles'] ) ) {
        $GLOBALS['wp_roles'] = (object) array(
            'roles' => array(
                'administrator' => array( 'name' => 'Administrator' ),
                'editor' => array( 'name' => 'Editor' ),
                'author' => array( 'name' => 'Author' ),
                'contributor' => array( 'name' => 'Contributor' ),
                'subscriber' => array( 'name' => 'Subscriber' ),
                'seo_manager' => array( 'name' => 'SEO Manager' ),
            )
        );
    }
    
    // Define constant
    if ( ! defined( 'SEO_SIMPLE_PLUGIN_FILE' ) ) {
        define( 'SEO_SIMPLE_PLUGIN_FILE', __FILE__ );
    }
}

// Include the roles class
require_once 'includes/class-seo-simple-roles.php';

// Test the class
echo "Testing Seo_Simple_Roles class...\n\n";

try {
    $roles = new Seo_Simple_Roles();
    
    // Test get_custom_roles method
    echo "Testing get_custom_roles() method:\n";
    $custom_roles = $roles->get_custom_roles();
    echo "Result: " . print_r( $custom_roles, true ) . "\n";
    
    // Test get_role_statistics method
    echo "\nTesting get_role_statistics() method:\n";
    $statistics = $roles->get_role_statistics();
    echo "Result: " . print_r( $statistics, true ) . "\n";
    
    // Test other methods to ensure they still work
    echo "\nTesting get_wordpress_roles() method:\n";
    $wp_roles = $roles->get_wordpress_roles();
    echo "Result: " . print_r( $wp_roles, true ) . "\n";
    
    echo "\nTesting get_seo_capabilities() method:\n";
    $capabilities = $roles->get_seo_capabilities();
    echo "Result: " . print_r( $capabilities, true ) . "\n";
    
    echo "\n✅ All tests passed! The Seo_Simple_Roles::get_custom_roles() method is now available.\n";
    
} catch ( Exception $e ) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} catch ( Error $e ) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
}
