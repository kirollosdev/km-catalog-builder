<?php
/**
 * The wave edge, added to Elementor's own Container.
 *
 * Elementor's Container cannot be subclassed into a second container element:
 * nesting, drag and drop and the editor's own handling are tied to the
 * "container" element type. So rather than clone it, this adds a Wave Edge
 * section to the container everyone already uses.
 *
 * The controls write CSS custom properties and a class onto the container, and
 * assets/css/kmcb-edges.css draws the edge from those. Nothing is rendered, so
 * it works the same in the editor and on the live site.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

class KMCB_Container {

	/**
	 * Containers already given the section, so it is added once each.
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

		if ( 'container' !== $element->get_name() ) {
			return;
		}

		// Section ids move between Elementor versions, so rather than name one,
		// take the first section that closes and add ours after it. The section
		// declares its own tab, so it lands under Style wherever it goes in.
		$key = spl_object_hash( $element );

		if ( isset( self::$done[ $key ] ) ) {
			return;
		}

		self::$done[ $key ] = true;

		unset( $section_id );

		$element->start_controls_section(
			'kmcb_wave_section',
			array(
				'label' => __( 'Wave Edge', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$element->add_control(
			'kmcb_wave',
			array(
				'label'        => __( 'Wave edge', 'km-catalog-builder' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => '',
				'options'      => array(
					''       => __( 'None', 'km-catalog-builder' ),
					'top'    => __( 'On the top edge', 'km-catalog-builder' ),
					'bottom' => __( 'On the bottom edge', 'km-catalog-builder' ),
					'both'   => __( 'Both edges', 'km-catalog-builder' ),
				),
				'prefix_class' => 'kmcb-wave-',
				'description'  => __( 'The edge is drawn outside the container, so it rises into the section next to it.', 'km-catalog-builder' ),
			)
		);

		$element->add_control(
			'kmcb_wave_shape',
			array(
				'label'        => __( 'Shape', 'km-catalog-builder' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'wave',
				'options'      => array(
					'wave'  => __( 'Smooth wave', 'km-catalog-builder' ),
					'round' => __( 'Round bumps', 'km-catalog-builder' ),
				),
				'prefix_class' => 'kmcb-waveshape-',
				'condition'    => array( 'kmcb_wave!' => '' ),
			)
		);

		$element->add_control(
			'kmcb_wave_color',
			array(
				'label'       => __( 'Colour', 'km-catalog-builder' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '#2C2C2C',
				'selectors'   => array( '{{WRAPPER}}' => '--kmcb-wave-color: {{VALUE}};' ),
				'condition'   => array( 'kmcb_wave!' => '' ),
				'description' => __( 'Normally the container\'s own background, so the edge reads as part of it.', 'km-catalog-builder' ),
			)
		);

		$element->add_responsive_control(
			'kmcb_wave_height',
			array(
				'label'      => __( 'Height', 'km-catalog-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 8, 'max' => 160 ) ),
				'default'    => array(
					'unit' => 'px',
					'size' => 45,
				),
				'selectors'  => array( '{{WRAPPER}}' => '--kmcb-wave-h: {{SIZE}}px;' ),
				'condition'  => array( 'kmcb_wave!' => '' ),
			)
		);

		$element->add_responsive_control(
			'kmcb_wave_tile',
			array(
				'label'       => __( 'Wave width', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array( 'px' => array( 'min' => 20, 'max' => 320 ) ),
				'default'     => array(
					'unit' => 'px',
					'size' => 115,
				),
				'selectors'   => array( '{{WRAPPER}}' => '--kmcb-wave-tile: {{SIZE}}px;' ),
				'condition'   => array( 'kmcb_wave!' => '' ),
				'description' => __( 'How wide one wave is. Smaller means more of them.', 'km-catalog-builder' ),
			)
		);

		$element->add_control(
			'kmcb_wave_inside',
			array(
				'label'        => __( 'Draw inside the container', 'km-catalog-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'prefix_class' => 'kmcb-waveinside-',
				'condition'    => array( 'kmcb_wave!' => '' ),
				'description'  => __( 'Carves the container\'s own edge instead of growing out of it. Set the colour to the section next door: a hero whose bottom edge is cut by a white wave.', 'km-catalog-builder' ),
			)
		);

		$element->add_control(
			'kmcb_wave_full',
			array(
				'label'        => __( 'Stretch past the container', 'km-catalog-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'full',
				'default'      => '',
				'prefix_class' => 'kmcb-wave-',
				'condition'    => array( 'kmcb_wave!' => '' ),
				'description'  => __( 'For a boxed container whose edge should still run the whole width of the screen.', 'km-catalog-builder' ),
			)
		);

		$element->end_controls_section();
	}
}
