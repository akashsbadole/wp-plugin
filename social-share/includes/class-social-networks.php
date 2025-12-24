<?php
/**
 * Social Media Configuration Class
 */
class ESS_Social_Networks {
    
    /**
     * Default Social Media
     */
    private $default_networks = array(
        'facebook' => array(
            'name' => 'Facebook',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>',
            'color' => '#3b5998',
            'share_url' => 'https://www.facebook.com/sharer/sharer.php?u={url}'
        ),
        'twitter' => array(
            'name' => 'Twitter',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>',
            'color' => '#1da1f2',
            'share_url' => 'https://twitter.com/intent/tweet?url={url}&text={title}'
        ),
        'linkedin' => array(
            'name' => 'LinkedIn',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M4.98 3.5c0 1.381-1.11 2.5-2.48 2.5s-2.48-1.119-2.48-2.5c0-1.38 1.11-2.5 2.48-2.5s2.48 1.12 2.48 2.5zm.02 4.5h-5v16h5v-16zm7.982 0h-4.968v16h4.969v-8.399c0-4.67 6.029-5.052 6.029 0v8.399h4.988v-10.131c0-7.88-8.922-7.593-11.018-3.714v-2.155z"/></svg>',
            'color' => '#0077b5',
            'share_url' => 'https://www.linkedin.com/sharing/share-offsite/?url={url}'
        ),
        'pinterest' => array(
            'name' => 'Pinterest',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>',
            'color' => '#bd081c',
            'share_url' => 'https://pinterest.com/pin/create/button/?url={url}&media={image}&description={title}'
        ),
        'email' => array(
            'name' => 'Email',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M0 3v18h24v-18h-24zm6.623 7.929l-4.623 5.712v-9.458l4.623 3.746zm-4.141-5.929h19.035l-9.517 7.713-9.518-7.713zm5.694 7.188l3.824 3.099 3.83-3.104 5.612 6.817h-18.779l5.513-6.812zm9.208-1.264l4.616-3.741v9.348l-4.616-5.607z"/></svg>',
            'color' => '#444444',
            'share_url' => 'mailto:?subject={title}&body={url}'
        )
    );
    
    /**
     * Get all networks including custom ones
     */
    public function get_networks() {
        $custom_networks = get_option('ess_custom_networks', array());
        return array_merge($this->default_networks, $custom_networks);
    }
    
    /**
     * Get network data
     */
    public function get_network_data($network_id) {
        $networks = $this->get_networks();
        return isset($networks[$network_id]) ? $networks[$network_id] : false;
    }
    
    /**
     * Add custom network
     */
    public function add_custom_network($network_id, $data) {
        $custom_networks = get_option('ess_custom_networks', array());
        
        $custom_networks[$network_id] = array(
            'name' => sanitize_text_field($data['name']),
            'icon' => wp_kses($data['icon'], array(
                'svg' => array(
                    'xmlns' => array(),
                    'width' => array(),
                    'height' => array(),
                    'viewBox' => array(),
                    'fill' => array()
                ),
                'path' => array('d' => array())
            )),
            'color' => sanitize_hex_color($data['color']),
            'share_url' => esc_url_raw($data['share_url'])
        );
        
        update_option('ess_custom_networks', $custom_networks);
    }
    
    /**
     * Remove custom network
     */
    public function remove_custom_network($network_id) {
        $custom_networks = get_option('ess_custom_networks', array());
        
        if (isset($custom_networks[$network_id])) {
            unset($custom_networks[$network_id]);
            update_option('ess_custom_networks', $custom_networks);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get share URL for a network
     */
    public function get_share_url($network_id, $url, $title = '', $image = '') {
        $network = $this->get_network_data($network_id);
        
        if (!$network) {
            return false;
        }
        
        $share_url = $network['share_url'];
        $share_url = str_replace('{url}', urlencode($url), $share_url);
        $share_url = str_replace('{title}', urlencode($title), $share_url);
        $share_url = str_replace('{image}', urlencode($image), $share_url);
        
        return $share_url;
    }
} 