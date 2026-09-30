<?php
/**
 * Custom shapes for Elementor's own container Shape Divider.
 *
 * Elementor lets a plugin add entries to the Shape Divider dropdown through
 * elementor/shapes/additional_shapes, and reads the SVG from a 'path' key, so
 * the shapes can live inside this plugin instead of Elementor's assets folder.
 *
 * Each shape ships a matching -negative.svg, which is what Elementor's
 * "Invert" switch loads.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

class KMCB_Shapes {

	public static function init() {
		add_filter( 'elementor/shapes/additional_shapes', array( __CLASS__, 'register' ) );
	}

	/**
	 * @param array $shapes Shapes already registered.
	 * @return array
	 */
	public static function register( $shapes ) {
		if ( ! is_array( $shapes ) ) {
			$shapes = array();
		}

		foreach ( self::shapes() as $name => $title ) {
			$shapes[ $name ] = array(
				'title'        => $title,
				'path'         => KMCB_PATH . 'assets/shapes/' . $name . '.svg',
				'image'        => KMCB_URL . 'assets/shapes/' . $name . '.svg',
				'has_negative' => true,
				'has_flip'     => true,
			);
		}

		return $shapes;
	}

	private static function shapes() {
		return array(
			'kmcb-scallop' => __( 'Scallop', 'km-catalog-builder' ),
			'kmcb-wave'    => __( 'Wave', 'km-catalog-builder' ),
		);
	}
}
