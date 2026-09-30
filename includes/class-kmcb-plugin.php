<?php
/**
 * Plugin bootstrap: dependency checks, asset registration, Elementor integration.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

final class KMCB_Plugin {

	const MIN_ELEMENTOR_VERSION = '3.5.0';

	/**
	 * @var KMCB_Plugin|null
	 */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'before_woocommerce_init', array( $this, 'declare_wc_compat' ) );
	}

	/**
	 * WooCommerce High-Performance Order Storage compatibility.
	 */
	public function declare_wc_compat() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', KMCB_FILE, true );
		}
	}

	public function init() {
		if ( ! $this->requirements_met() ) {
			return;
		}

		KMCB_Ajax::init();

		// The product template module: the page a product is drawn on, which
		// the widgets then fill. It stands aside where Elementor Pro's Theme
		// Builder is doing that job.
		KMCB_Template_Post_Type::init();
		KMCB_Template_Settings::init();
		KMCB_Template_Render::init();
		KMCB_Product_Meta::init();
		KMCB_Icons::init();
		KMCB_Page_Meta::init();
		KMCB_Shapes::init();
		KMCB_Container::init();
		KMCB_Float::init();

		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register_assets' ) );
		add_action( 'elementor/frontend/after_register_styles', array( $this, 'register_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	/**
	 * Bail (with an admin notice) when Elementor or WooCommerce is missing.
	 */
	private function requirements_met() {
		$missing = array();

		if ( ! did_action( 'elementor/loaded' ) ) {
			$missing[] = 'Elementor';
		} elseif ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, self::MIN_ELEMENTOR_VERSION, '<' ) ) {
			$missing[] = 'Elementor ' . self::MIN_ELEMENTOR_VERSION . '+';
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			$missing[] = 'WooCommerce';
		}

		if ( empty( $missing ) ) {
			return true;
		}

		add_action(
			'admin_notices',
			function () use ( $missing ) {
				if ( ! current_user_can( 'activate_plugins' ) ) {
					return;
				}
				printf(
					'<div class="notice notice-warning"><p><strong>%s</strong> %s <strong>%s</strong>.</p></div>',
					esc_html__( 'KM Catalog Builder', 'km-catalog-builder' ),
					esc_html__( 'is inactive because it requires:', 'km-catalog-builder' ),
					esc_html( implode( ', ', $missing ) )
				);
			}
		);

		return false;
	}

	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'km-catalog',
			array(
				'title' => __( 'KM Catalog Builder', 'km-catalog-builder' ),
				'icon'  => 'eicon-star',
			)
		);
	}

	/**
	 * Every widget this plugin ships, as file => class.
	 *
	 * @return array
	 */
	public static function widgets() {
		/**
		 * Filter the widgets registered by the catalog builder.
		 *
		 * @param array $widgets file name (in widgets/) => class name.
		 */
		return apply_filters(
			'kmcb_widgets',
			array(
				'class-kmcb-grid-widget.php'      => 'KMCB_Grid_Widget',
				'class-kmcb-gallery-widget.php'   => 'KMCB_Gallery_Widget',
				'class-kmcb-accordion-widget.php' => 'KMCB_Accordion_Widget',
				'class-kmcb-button-widget.php'    => 'KMCB_Button_Widget',
				'class-kmcb-description-widget.php' => 'KMCB_Description_Widget',
				'class-kmcb-variations-widget.php'  => 'KMCB_Variations_Widget',
				'class-kmcb-related-widget.php'     => 'KMCB_Related_Widget',
				'class-kmcb-terms-widget.php'       => 'KMCB_Terms_Widget',
				'class-kmcb-general-button-widget.php' => 'KMCB_General_Button_Widget',
				'class-kmcb-switcher-widget.php'    => 'KMCB_Switcher_Widget',
				'class-kmcb-features-widget.php'    => 'KMCB_Features_Widget',
				'class-kmcb-marquee-widget.php'     => 'KMCB_Marquee_Widget',
				'class-kmcb-shape-image-widget.php' => 'KMCB_Shape_Image_Widget',
				'class-kmcb-divider-widget.php'     => 'KMCB_Divider_Widget',
				'class-kmcb-wave-widget.php'        => 'KMCB_Wave_Widget',
				'class-kmcb-faq-widget.php'         => 'KMCB_Faq_Widget',
				'class-kmcb-navbar-widget.php'      => 'KMCB_Navbar_Widget',
			)
		);
	}

	public function register_widgets( $widgets_manager ) {
		foreach ( self::widgets() as $file => $class ) {
			$path = KMCB_PATH . 'widgets/' . $file;

			if ( ! file_exists( $path ) ) {
				continue;
			}

			require_once $path;

			if ( class_exists( $class ) ) {
				$widgets_manager->register( new $class() );
			}
		}
	}

	/**
	 * Register (not enqueue) assets; the widget enqueues them on render.
	 */
	public function register_assets() {
		// The edge classes go on plain containers that hold no widget of ours,
		// so this one cannot wait to be pulled in as a widget dependency.
		if ( ! wp_style_is( 'kmcb-edges', 'enqueued' ) ) {
			wp_register_style( 'kmcb-edges', KMCB_URL . 'assets/css/kmcb-edges.css', array(), KMCB_VERSION );
			wp_enqueue_style( 'kmcb-edges' );
		}

		if ( wp_style_is( 'kmcb-grid', 'registered' ) ) {
			return;
		}

		wp_register_style( 'kmcb-grid', KMCB_URL . 'assets/css/kmcb.css', array(), KMCB_VERSION );

		// Noto Sans Arabic, for the note under the product accordion. Loaded
		// only where that widget is on the page, through its style depends.
		wp_register_style(
			'kmcb-noto-arabic',
			'https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;700&display=swap',
			array(),
			null
		);
		wp_register_script( 'kmcb-grid', KMCB_URL . 'assets/js/kmcb.js', array(), KMCB_VERSION, true );

		wp_localize_script(
			'kmcb-grid',
			'KMCB_DATA',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'kmcb_filter' ),
				'i18n'    => array(
					'loading'  => __( 'Loading…', 'km-catalog-builder' ),
					'noResult' => __( 'No products match your filters.', 'km-catalog-builder' ),
				),
			)
		);
	}

	/**
	 * Product taxonomies that make sense as filters (categories, tags, attributes).
	 *
	 * @return array taxonomy slug => label
	 */
	public static function get_filterable_taxonomies() {
		$out        = array();
		$taxonomies = get_object_taxonomies( 'product', 'objects' );

		foreach ( $taxonomies as $taxonomy ) {
			if ( in_array( $taxonomy->name, array( 'product_type', 'product_visibility', 'product_shipping_class' ), true ) ) {
				continue;
			}
			if ( ! $taxonomy->public && 0 !== strpos( $taxonomy->name, 'pa_' ) ) {
				continue;
			}
			$out[ $taxonomy->name ] = $taxonomy->labels->singular_name . ' (' . $taxonomy->name . ')';
		}

		return $out;
	}
}
