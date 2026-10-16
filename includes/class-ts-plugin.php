<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TS_Plugin {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'init', array( $this, 'check_dependencies' ) );
        add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
        add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
    }

    /**
     * Verify required plugins are active (Elementor & WooCommerce).
     */
    public function check_dependencies() {
        if ( ! did_action( 'elementor/loaded' ) || ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( $this, 'admin_notice_missing_dependencies' ) );
        }
    }

    public function admin_notice_missing_dependencies() {
        echo '<div class="notice notice-error"><p>' . esc_html__( 'TS Woo Category Hover Showcase requires both WooCommerce and Elementor to be installed and active.', 'ts-woo-hover-showcase' ) . '</p></div>';
    }

    /**
     * Register Custom Elementor Category.
     */
    public function register_category( $elements_manager ) {
        $elements_manager->add_category(
            'ts-category',
            array(
                'title' => esc_html__( 'TS Custom Widgets', 'ts-woo-hover-showcase' ),
                'icon'  => 'fa fa-plug',
            )
        );
    }

    /**
     * Enqueue styles and scripts for the widget.
     */
    public function register_assets() {
        wp_register_style(
            'ts-showcase-style',
            TS_SHOWCASE_URL . 'assets/css/ts-showcase.css',
            array(),
            TS_SHOWCASE_VERSION
        );

        wp_register_script(
            'ts-showcase-script',
            TS_SHOWCASE_URL . 'assets/js/ts-showcase.js',
            array( 'jquery' ),
            TS_SHOWCASE_VERSION,
            true
        );

        wp_register_style(
            'ts-category-tabs-style',
            TS_SHOWCASE_URL . 'assets/css/ts-category-tabs.css',
            array(),
            TS_SHOWCASE_VERSION
        );

        wp_register_script(
            'ts-category-tabs-script',
            TS_SHOWCASE_URL . 'assets/js/ts-category-tabs.js',
            array( 'jquery' ),
            TS_SHOWCASE_VERSION,
            true
        );
    }

    /**
     * Register the Elementor widget.
     */
    public function register_widgets( $widgets_manager ) {
        require_once TS_SHOWCASE_PATH . 'widgets/class-ts-category-hover-widget.php';
        $widgets_manager->register( new \TS_Category_Hover_Widget() );

        require_once TS_SHOWCASE_PATH . 'widgets/class-ts-category-product-tabs-widget.php';
        $widgets_manager->register( new \TS_Category_Product_Tabs_Widget() );
    }
}