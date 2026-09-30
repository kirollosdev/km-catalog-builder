<?php
/**
 * The KM Catalog Builder icon tab for Elementor.
 *
 * Every image dropped into assets/icons/ becomes an icon, so the set can grow
 * without touching code. The file name is the icon name: "cat-heart.webp"
 * appears in the picker as "cat heart".
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

class KMCB_Icons {

	const PREFIX = 'kmcb-icon-';
	const DIR    = 'assets/icons/';

	public static function init() {
		add_filter( 'elementor/icons_manager/additional_tabs', array( __CLASS__, 'register_tab' ) );

		// The picker iframe pulls these two over admin-ajax.
		add_action( 'wp_ajax_kmcb_icons_css', array( __CLASS__, 'serve_css' ) );
		add_action( 'wp_ajax_kmcb_icons_json', array( __CLASS__, 'serve_json' ) );

		// The front end and the editor canvas need the same rules.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'inline_css' ), 20 );
		add_action( 'elementor/editor/after_enqueue_styles', array( __CLASS__, 'editor_css' ) );
	}

	/**
	 * Icon slug => file name, for every image in the icons folder.
	 *
	 * @return array
	 */
	public static function icons() {
		static $cache = null;

		if ( null !== $cache ) {
			return $cache;
		}

		$cache = array();
		$dir   = KMCB_PATH . self::DIR;

		if ( ! is_dir( $dir ) ) {
			return $cache;
		}

		foreach ( (array) glob( $dir . '*.{webp,svg,png,jpg,jpeg}', GLOB_BRACE ) as $path ) {
			$slug = sanitize_title( pathinfo( $path, PATHINFO_FILENAME ) );

			if ( '' === $slug || isset( $cache[ $slug ] ) ) {
				continue;
			}

			$cache[ $slug ] = basename( $path );
		}

		ksort( $cache );

		return $cache;
	}

	/**
	 * Rules for the icon classes.
	 *
	 * Raster files are drawn as-is so the artwork keeps its own colours; SVGs
	 * are masked instead, so they inherit currentColor the way a font icon
	 * does.
	 */
	public static function css() {
		$icons = self::icons();

		if ( empty( $icons ) ) {
			return '';
		}

		$css = '.kmcb-icon{display:inline-block;width:1em;height:1em;vertical-align:-0.125em;'
			. 'background-repeat:no-repeat;background-position:center;background-size:contain;'
			. '-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;'
			. '-webkit-mask-position:center;mask-position:center;'
			. '-webkit-mask-size:contain;mask-size:contain;}';

		foreach ( $icons as $slug => $file ) {
			$url   = KMCB_URL . self::DIR . rawurlencode( $file );
			$class = '.' . self::PREFIX . $slug;

			if ( 'svg' === strtolower( (string) pathinfo( $file, PATHINFO_EXTENSION ) ) ) {
				$css .= $class . '{background-color:currentColor;'
					. '-webkit-mask-image:url(' . $url . ');mask-image:url(' . $url . ');}';
			} else {
				$css .= $class . '{background-image:url(' . $url . ');'
					. '-webkit-mask-image:none;mask-image:none;background-color:transparent;}';
			}
		}

		return $css;
	}

	public static function inline_css() {
		$css = self::css();

		if ( '' === $css || ! wp_style_is( 'kmcb-grid', 'registered' ) ) {
			return;
		}

		wp_add_inline_style( 'kmcb-grid', $css );
	}

	public static function editor_css() {
		$css = self::css();

		if ( '' === $css ) {
			return;
		}

		wp_register_style( 'kmcb-icons', false, array(), KMCB_VERSION );
		wp_enqueue_style( 'kmcb-icons' );
		wp_add_inline_style( 'kmcb-icons', $css );
	}

	public static function serve_css() {
		header( 'Content-Type: text/css; charset=UTF-8' );
		echo self::css(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS built from local file names.
		exit;
	}

	public static function serve_json() {
		wp_send_json( self::manifest() );
	}

	private static function manifest() {
		return array(
			'name'          => 'kmcb-icons',
			'label'         => __( 'KM Catalog Builder', 'km-catalog-builder' ),
			'prefix'        => self::PREFIX,
			'displayPrefix' => 'kmcb-icons',
			'labelIcon'     => 'kmcb-icon ' . self::PREFIX . (string) key( self::icons() ),
			'ver'           => KMCB_VERSION,
			'icons'         => array_keys( self::icons() ),
		);
	}

	/**
	 * Add the tab to Elementor's icon picker.
	 */
	public static function register_tab( $tabs ) {
		if ( empty( self::icons() ) ) {
			return $tabs;
		}

		$manifest = self::manifest();

		$tabs['kmcb-icons'] = array(
			'name'          => $manifest['name'],
			'label'         => $manifest['label'],
			'labelIcon'     => $manifest['labelIcon'],
			'prefix'        => $manifest['prefix'],
			'displayPrefix' => $manifest['displayPrefix'],
			'ver'           => $manifest['ver'],
			'url'           => admin_url( 'admin-ajax.php?action=kmcb_icons_css' ),
			'fetchJson'     => admin_url( 'admin-ajax.php?action=kmcb_icons_json' ),
			'native'        => false,
		);

		return $tabs;
	}
}
