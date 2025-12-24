<?php
class CLR_Page_Builder {
    public function __construct() {
        // Elementor integration
        if (did_action('elementor/loaded')) {
            add_action('elementor/widgets/register', array($this, 'register_elementor_widget'));
        }
        
        // Divi integration
        add_action('et_builder_ready', array($this, 'register_divi_module'));
        
        // Beaver Builder integration
        add_action('fl_builder_before_render_modules', array($this, 'register_beaver_module'));
    }

    public function register_elementor_widget() {
        require_once CLR_PLUGIN_DIR . 'admin/elementor-widget.php';
        \Elementor\Plugin::instance()->widgets_manager->register(new \CLR_Elementor_Widget());
    }

    public function register_divi_module() {
        if (class_exists('ET_Builder_Module')) {
            require_once CLR_PLUGIN_DIR . 'admin/divi-module.php';
            new CLR_Divi_Module();
        }
    }

    public function register_beaver_module() {
        if (class_exists('FLBuilderModule')) {
            require_once CLR_PLUGIN_DIR . 'admin/beaver-module.php';
            FLBuilder::register_module('CLR_Beaver_Module', array());
        }
    }
}