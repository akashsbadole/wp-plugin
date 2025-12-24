<?php
/**
 * Direct SEO Output Class
 * 
 * This class provides a direct method to output SEO tags without relying on
 * complex caching or settings systems. It's a fallback mechanism to ensure
 * SEO tags are always visible in the source code.
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class Seo_Simple_Direct_Output {
    
    /**
     * Initialize the class and set up WordPress hooks
     */
    public function __construct() {
        // Register hooks with very high priority to ensure they run first
        add_action('wp_head', array($this, 'direct_meta_tags'), 1);
        add_action('wp_head', array($this, 'direct_open_graph_tags'), 2);
        add_action('wp_head', array($this, 'direct_twitter_card_tags'), 3);
        add_action('wp_head', array($this, 'direct_schema'), 4);
    }
    
    /**
     * Get the SEO settings
     */
    private function get_settings() {
        // Try to get settings from the new settings system first
        $settings = get_option('seo_simple_settings', array());
        
        // If settings are empty, try to get them using Seo_Simple_Meta
        if (empty($settings) && class_exists('Seo_Simple_Meta')) {
            $meta = new Seo_Simple_Meta();
            $settings = $meta->get_settings_from_db();
        }
        
        // If settings are still empty, use defaults
        if (empty($settings)) {
            $settings = array(
                'seo_title_separator' => '-',
                'seo_title_format' => '%post_title% | %site_name%',
                'seo_description_template' => '',
                'seo_home_title' => get_bloginfo('name') . ' | ' . get_bloginfo('description'),
                'seo_home_description' => get_bloginfo('description'),
                'seo_title_length' => 60,
                'seo_description_length' => 160,
                'seo_enable_posts' => 1,
                'seo_enable_pages' => 1,
                'seo_enable_categories' => 1,
                'seo_enable_tags' => 1,
                'seo_enable_og' => 1,
                'seo_enable_twitter' => 1,
                'seo_enable_json_ld' => 1,
            );
        }
        
        return $settings;
    }
    
    /**
     * Directly output basic meta tags using the settings system
     */
    public function direct_meta_tags() {
        global $post;
        
        // Get settings to check if SEO is enabled
        $settings = $this->get_settings();
        
        // Don't output if SEO is disabled for this post type
        if (is_singular() && isset($post->post_type)) {
            $enable_key = 'seo_enable_' . $post->post_type;
            if (isset($settings[$enable_key]) && !$settings[$enable_key]) {
                return;
            }
        }
        
        // Always output title tag using settings
        $title = $this->get_direct_title($post);
        if (!empty($title)) {
            echo '<title>' . esc_html($title) . '</title>' . "\n";
        }
        
        // Always output description meta tag using settings
        $description = $this->get_direct_description($post);
        if (!empty($description)) {
            echo '<meta name="description" content="' . esc_attr($description) . '" />' . "\n";
        }
        
        // Always output robots meta tag
        $robots = $this->get_direct_robots($post);
        echo '<meta name="robots" content="' . esc_attr($robots) . '" />' . "\n";
        
        // Always output canonical URL
        $canonical = $this->get_direct_canonical($post);
        if (!empty($canonical)) {
            echo '<link rel="canonical" href="' . esc_url($canonical) . '" />' . "\n";
        }
    }
    
    /**
     * Directly output Open Graph tags using settings
     */
    public function direct_open_graph_tags() {
        global $post;
        
        // Check if OG is enabled in settings
        $settings = $this->get_settings();
        if (!isset($settings['seo_enable_og']) || !$settings['seo_enable_og']) {
            return;
        }
        
        // Always output basic OG tags
        echo '<meta property="og:locale" content="' . esc_attr(get_locale()) . '" />' . "\n";
        echo '<meta property="og:type" content="' . esc_attr($this->get_direct_og_type($post)) . '" />' . "\n";
        
        // Title
        $title = $this->get_direct_title($post);
        if (!empty($title)) {
            echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
        }
        
        // Description
        $description = $this->get_direct_description($post);
        if (!empty($description)) {
            echo '<meta property="og:description" content="' . esc_attr($description) . '" />' . "\n";
        }
        
        // URL
        $url = $this->get_direct_url($post);
        if (!empty($url)) {
            echo '<meta property="og:url" content="' . esc_url($url) . '" />' . "\n";
        }
        
        // Site Name
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";
        
        // Image
        $image = $this->get_direct_image($post);
        if (!empty($image)) {
            echo '<meta property="og:image" content="' . esc_url($image) . '" />' . "\n";
            echo '<meta property="og:image:width" content="1200" />' . "\n";
            echo '<meta property="og:image:height" content="630" />' . "\n";
            echo '<meta property="og:image:type" content="image/jpeg" />' . "\n";
        }
    }
    
    /**
     * Directly output Twitter Card tags using settings
     */
    public function direct_twitter_card_tags() {
        global $post;
        
        // Check if Twitter Cards are enabled in settings
        $settings = $this->get_settings();
        if (!isset($settings['seo_enable_twitter']) || !$settings['seo_enable_twitter']) {
            return;
        }
        
        // Always output Twitter Card type
        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        
        // Title
        $title = $this->get_direct_title($post);
        if (!empty($title)) {
            echo '<meta name="twitter:title" content="' . esc_attr($title) . '" />' . "\n";
        }
        
        // Description
        $description = $this->get_direct_description($post);
        if (!empty($description)) {
            echo '<meta name="twitter:description" content="' . esc_attr($description) . '" />' . "\n";
        }
        
        // Image
        $image = $this->get_direct_image($post);
        if (!empty($image)) {
            echo '<meta name="twitter:image" content="' . esc_url($image) . '" />' . "\n";
        }
    }
    
    /**
     * Directly output JSON-LD schema using settings
     */
    public function direct_schema() {
        global $post;
        
        // Check if schema is enabled in settings
        $settings = $this->get_settings();
        if (!isset($settings['seo_enable_json_ld']) || !$settings['seo_enable_json_ld']) {
            return;
        }
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => $this->get_direct_schema_type($post),
            'name' => $this->get_direct_title($post),
            'description' => $this->get_direct_description($post),
            'url' => $this->get_direct_url($post),
        );
        
        // Add image if available
        $image = $this->get_direct_image($post);
        if (!empty($image)) {
            $schema['image'] = $image;
        }
        
        // Add publisher info
        $schema['publisher'] = array(
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
        );
        
        // Output JSON-LD
        echo '<script type="application/ld+json">' . "\n";
        echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
        echo '</script>' . "\n";
    }
    
    /**
     * Get title using settings templates
     */
    private function get_direct_title($post) {
        $settings = $this->get_settings();
        
        if (is_home() || is_front_page()) {
            // Use homepage title from settings if available
            if (!empty($settings['seo_home_title'])) {
                return $settings['seo_home_title'];
            }
            return get_bloginfo('name') . ' | ' . get_bloginfo('description');
        }
        
        if (is_singular() && $post) {
            // Try custom title first
            $custom_title = get_post_meta($post->ID, '_seo_simple_title', true);
            if (!empty($custom_title)) {
                return $custom_title;
            }
            
            // Get content type specific template
            $content_type = $post->post_type;
            $title_format = isset($settings['seo_' . $content_type . '_title_format']) ?
                $settings['seo_' . $content_type . '_title_format'] :
                (isset($settings['seo_title_format']) ? $settings['seo_title_format'] : '%post_title% | %site_name%');
            
            $separator = isset($settings['seo_title_separator']) ? $settings['seo_title_separator'] : '-';
            
            // Prepare replacements
            $replacements = array(
                '%post_title%' => get_the_title($post),
                '%site_name%' => get_bloginfo('name'),
                '%separator%' => $separator,
            );
            
            // Add category if it's a post
            if ($content_type === 'post') {
                $categories = get_the_category($post->ID);
                if (!empty($categories)) {
                    $replacements['%category%'] = $categories[0]->name;
                }
            }
            
            // Add author if it's a post
            if ($content_type === 'post') {
                $author_id = $post->post_author;
                $author = get_the_author_meta('display_name', $author_id);
                $replacements['%author%'] = $author;
            }
            
            // Replace placeholders
            $title = str_replace(array_keys($replacements), array_values($replacements), $title_format);
            
            // Apply length limit
            $max_length = isset($settings['seo_title_length']) ? intval($settings['seo_title_length']) : 60;
            if (is_string($title) && strlen($title) > $max_length) {
                $title = substr($title, 0, $max_length - 3) . '...';
            }
            
            return $title;
        }
        
        // Default for other pages
        return wp_title('|', false, 'right');
    }
    
    /**
     * Get description using settings templates
     */
    private function get_direct_description($post) {
        $settings = $this->get_settings();
        
        if (is_home() || is_front_page()) {
            // Use homepage description from settings if available
            if (!empty($settings['seo_home_description'])) {
                return $settings['seo_home_description'];
            }
            return get_bloginfo('description');
        }
        
        if (is_singular() && $post) {
            // Try custom description first
            $custom_desc = get_post_meta($post->ID, '_seo_simple_description', true);
            if (!empty($custom_desc)) {
                return $custom_desc;
            }
            
            // Get content type specific template
            $content_type = $post->post_type;
            $description_template = isset($settings['seo_' . $content_type . '_description_format']) ?
                $settings['seo_' . $content_type . '_description_format'] :
                (isset($settings['seo_description_template']) ? $settings['seo_description_template'] : '');
            
            // If no template is set, use default behavior
            if (empty($description_template)) {
                $description = !empty($post->post_excerpt) ? $post->post_excerpt : $post->post_content;
                $description = strip_tags($description);
                $description = strip_shortcodes($description);
                $description = wp_trim_words($description, 30, '...');
            } else {
                // Process template
                $content = !empty($post->post_excerpt) ? $post->post_excerpt : $post->post_content;
                $content = strip_tags($content);
                $content = strip_shortcodes($content);
                
                $replacements = array(
                    '%post_content%' => $content,
                    '%post_excerpt%' => !empty($post->post_excerpt) ? $post->post_excerpt : $content,
                    '%post_title%' => get_the_title($post),
                );
                
                $description = str_replace(array_keys($replacements), array_values($replacements), $description_template);
            }
            
            // Apply length limit
            $max_length = isset($settings['seo_description_length']) ? intval($settings['seo_description_length']) : 160;
            if (is_string($description) && strlen($description) > $max_length) {
                $description = substr($description, 0, $max_length - 3) . '...';
            }
            
            return $description;
        }
        
        // Default description
        return get_bloginfo('description');
    }
    
    /**
     * Get robots meta tag directly
     */
    private function get_direct_robots($post) {
        if (is_single() && $post) {
            $noindex = get_post_meta($post->ID, '_seo_simple_noindex', true);
            $nofollow = get_post_meta($post->ID, '_seo_simple_nofollow', true);
            
            $robots = array();
            if ($noindex) {
                $robots[] = 'noindex';
            } else {
                $robots[] = 'index';
            }
            
            if ($nofollow) {
                $robots[] = 'nofollow';
            } else {
                $robots[] = 'follow';
            }
            
            return implode(', ', $robots);
        }
        
        return 'index, follow';
    }
    
    /**
     * Get canonical URL directly
     */
    private function get_direct_canonical($post) {
        if (is_single() && $post) {
            // Try custom canonical first
            $custom_canonical = get_post_meta($post->ID, '_seo_simple_canonical', true);
            if (!empty($custom_canonical)) {
                return $custom_canonical;
            }
        }
        
        // Fall back to current URL
        return get_permalink($post);
    }
    
    /**
     * Get OG type directly
     */
    private function get_direct_og_type($post) {
        if (is_single()) {
            return 'article';
        }
        
        return 'website';
    }
    
    /**
     * Get URL directly
     */
    private function get_direct_url($post) {
        return get_permalink($post);
    }
    
    /**
     * Get image directly
     */
    private function get_direct_image($post) {
        if (is_single() && $post) {
            // Try custom social image first
            $custom_image = get_post_meta($post->ID, '_seo_simple_social_image', true);
            if (!empty($custom_image)) {
                return $custom_image;
            }
            
            // Try featured image
            if (has_post_thumbnail($post->ID)) {
                $thumb_id = get_post_thumbnail_id($post->ID);
                $thumb_url = wp_get_attachment_image_src($thumb_id, 'large');
                if ($thumb_url) {
                    return $thumb_url[0];
                }
            }
        }
        
        // Fall back to site logo or default image
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $image = wp_get_attachment_image_src($custom_logo_id, 'full');
            if ($image) {
                return $image[0];
            }
        }
        
        // Return a default placeholder if nothing else works
        return '';
    }
    
    /**
     * Get schema type directly
     */
    private function get_direct_schema_type($post) {
        if (is_single()) {
            return 'Article';
        }
        
        return 'WebSite';
    }
}