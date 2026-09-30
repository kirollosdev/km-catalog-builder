<?php
/**
 * A constant floating effect, added to Elementor's containers and widgets.
 *
 * The one carried over from the portfolio builder: an element that drifts
 * gently up and down forever, for a shape, a badge or a product image.
 *
 * Like the wave edge, this is a class and a few custom properties rather than
 * anything rendered, so the editor shows exactly what the live site does.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

class KMCB_Float {

	/**
	 * Elements already given the section, so it is added once each.
	 *
	 * @var array
	 */
	private static $done = array();

	public static function init() {
		add_action( 'elementor/element/after_section_end', array( __CLASS__, 'add_controls' ), 10, 2 );
	}

	/**
	 * @param \Elementor\Controls_Stack $element    The element being built.
	 * @param string                    $section_id The section that just closed.
	 */
	public static function add_controls( $element, $section_id ) {
		if ( ! is_object( $element ) || ! method_exists( $element, 'get_name' ) ) {
			return;
		}

		// Section ids move between Elementor versions, so rather than name one,
		// take the first section that closes and add ours after it.
		$key = spl_object_hash( $element );

		if ( isset( self::$done[ $key ] ) ) {
			return;
		}

		self::$done[ $key ] = true;

		unset( $section_id );

		$element->start_controls_section(
			'kmcb_float_section',
			array(
				'label' => __( 'Floating', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			)
		);

		$element->add_control(
			'kmcb_float',
			array(
				'label'        => __( 'Float', 'km-catalog-builder' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => '',
				'options'      => array(
					''        => __( 'None', 'km-catalog-builder' ),
					'updown'  => __( 'Up and down', 'km-catalog-builder' ),
					'sideway' => __( 'Side to side', 'km-catalog-builder' ),
				),
				'prefix_class' => 'kmcb-float-',
				'description'  => __( 'Runs continuously. It stops for visitors who ask their system to reduce motion.', 'km-catalog-builder' ),
			)
		);

		$element->add_responsive_control(
			'kmcb_float_distance',
			array(
				'label'      => __( 'Distance', 'km-catalog-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 2, 'max' => 80 ) ),
				'default'    => array(
					'unit' => 'px',
					'size' => 14,
				),
				'selectors'  => array( '{{WRAPPER}}' => '--kmcb-float-d: {{SIZE}}px;' ),
				'condition'  => array( 'kmcb_float!' => '' ),
			)
		);

		$element->add_control(
			'kmcb_float_duration',
			array(
				'label'       => __( 'One trip takes', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 's' ),
				'range'       => array( 's' => array( 'min' => 0.5, 'max' => 20, 'step' => 0.1 ) ),
				'default'     => array(
					'unit' => 's',
					'size' => 3,
				),
				'selectors'   => array( '{{WRAPPER}}' => '--kmcb-float-t: {{SIZE}}s;' ),
				'condition'   => array( 'kmcb_float!' => '' ),
				'description' => __( 'Slower reads calmer. Around 3 to 6 seconds suits most things.', 'km-catalog-builder' ),
			)
		);

		$element->add_control(
			'kmcb_float_delay',
			array(
				'label'       => __( 'Start after', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 's' ),
				'range'       => array( 's' => array( 'min' => 0, 'max' => 10, 'step' => 0.1 ) ),
				'default'     => array(
					'unit' => 's',
					'size' => 0,
				),
				'selectors'   => array( '{{WRAPPER}}' => '--kmcb-float-delay: {{SIZE}}s;' ),
				'condition'   => array( 'kmcb_float!' => '' ),
				'description' => __( 'Give each of several floating things a different delay so they do not move as one.', 'km-catalog-builder' ),
			)
		);

		$element->end_controls_section();
	}
}
