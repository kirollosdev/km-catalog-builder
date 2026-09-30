<?php
/**
 * Plugin Name:       KM Catalog Builder
 * Plugin URI:        https://github.com/kirollosdev/km-catalog-builder
 * Description:       Elementor widgets for a catalogue or shop: a filterable WooCommerce product grid with its own sidebar, a single-product gallery, variation boxes, a product accordion, taxonomy line, related products, buttons, segmented switcher, feature list, marquee, FAQ, navbar, shape-masked images and container edges.
 * Version:           1.1.0
 * Author:            Kirollos Magdy
 * Author URI:        https://kirollosmagdy.com
 * License:           Proprietary, all rights reserved
 * License URI:       https://github.com/kirollosdev/km-catalog-builder/blob/main/LICENSE
 * Text Domain:       km-catalog-builder
 * Requires PHP:      7.4
 * Requires at least: 5.9
 * Elementor tested up to: 3.25
 * WC requires at least: 6.0
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

define( 'KMCB_VERSION', '1.1.0' );
define( 'KMCB_FILE', __FILE__ );
define( 'KMCB_PATH', plugin_dir_path( __FILE__ ) );
define( 'KMCB_URL', plugin_dir_url( __FILE__ ) );

require_once KMCB_PATH . 'includes/class-kmcb-plugin.php';
require_once KMCB_PATH . 'includes/class-kmcb-template-post-type.php';
require_once KMCB_PATH . 'includes/class-kmcb-template-settings.php';
require_once KMCB_PATH . 'includes/class-kmcb-template-render.php';
require_once KMCB_PATH . 'includes/class-kmcb-config.php';
require_once KMCB_PATH . 'includes/class-kmcb-query.php';
require_once KMCB_PATH . 'includes/class-kmcb-render.php';
require_once KMCB_PATH . 'includes/class-kmcb-ajax.php';
require_once KMCB_PATH . 'includes/class-kmcb-product-meta.php';
require_once KMCB_PATH . 'includes/class-kmcb-icons.php';
require_once KMCB_PATH . 'includes/class-kmcb-page-meta.php';
require_once KMCB_PATH . 'includes/class-kmcb-shapes.php';
require_once KMCB_PATH . 'includes/class-kmcb-container.php';
require_once KMCB_PATH . 'includes/class-kmcb-float.php';

KMCB_Plugin::instance();
