<?php
/**
 * Plugin Name:       TS Woo Category Hover Showcase
 * Plugin URI:        https://example.com/
 * Description:       An Elementor widget to display WooCommerce child categories on the left and switch product grids on hover on the right.
 * Version:           1.0.0
 * Author:            TS Developer
 * Text Domain:       ts-woo-hover-showcase
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'TS_SHOWCASE_VERSION', '1.0.0' );
define( 'TS_SHOWCASE_PATH', plugin_dir_path( __FILE__ ) );
define( 'TS_SHOWCASE_URL', plugin_dir_url( __FILE__ ) );

// Load the core plugin execution class.
require_once TS_SHOWCASE_PATH . 'includes/class-ts-plugin.php';

function ts_woo_category_hover_showcase_init() {
    TS_Plugin::get_instance();
}
add_action( 'plugins_loaded', 'ts_woo_category_hover_showcase_init' );