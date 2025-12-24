<?php
/**
 * Custom Code Management for Custom Login Redirect
 *
 * This class handles the output of custom code in the header and footer
 * of the website, such as Google Analytics tracking codes.
 */

class CLR_Custom_Code {
    public function __construct() {
        // Add actions to output custom code
        add_action('wp_head', array($this, 'output_header_code'), 999);
        add_action('wp_footer', array($this, 'output_footer_code'), 999);
    }
    
    /**
     * Output custom code in the header
     */
    public function output_header_code() {
        $header_code = get_option('clr_header_code', '');
        
        if (!empty($header_code)) {
            echo '<!-- Custom Login Redirect Header Code -->' . "\n";
            echo $header_code . "\n";
            echo '<!-- End Custom Login Redirect Header Code -->' . "\n";
        }
    }
    
    /**
     * Output custom code in the footer
     */
    public function output_footer_code() {
        $footer_code = get_option('clr_footer_code', '');
        
        if (!empty($footer_code)) {
            echo '<!-- Custom Login Redirect Footer Code -->' . "\n";
            echo $footer_code . "\n";
            echo '<!-- End Custom Login Redirect Footer Code -->' . "\n";
        }
    }
}