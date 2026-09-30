<?php
/**
 * Elementor widget: Wave Edge.
 *
 * Drop it anywhere inside a container and it draws a scalloped edge on that
 * container's outer edge: green bumps rising into whatever sits above, which
 * is the footer treatment.
 *
 * Unlike the Shape Divider widget, this one positions itself against the
 * container rather than sitting in the flow, so it clears the container's
 * padding and does not care where in the container it is dropped.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class KMCB_Wave_Widget extends Widget_Base {

	public function get_name() {
		return 'kmcb_wave_edge';
	}

	public function get_title() {
		return __( 'Wave Edge', 'km-catalog-builder' );
	}

	public function get_icon() {
		return 'eicon-wave';
	}

	public function get_categories() {
		return array( 'km-catalog' );
	}

	public function get_keywords() {
		return array( 'wave', 'scallop', 'edge', 'divider', 'footer', 'shape', 'container' );
	}

	public function get_style_depends() {
		return array( 'kmcb-edges' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_wave',
			array( 'label' => __( 'Wave Edge', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'place',
			array(
				'label'       => __( 'Where', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'above',
				'options'     => array(
					'above' => __( 'Above this container', 'km-catalog-builder' ),
					'below' => __( 'Below this container', 'km-catalog-builder' ),
					'here'  => __( 'Right here, in the flow', 'km-catalog-builder' ),
				),
				'description' => __( 'Above is the footer look: the bumps rise into the section overhead.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'shape',
			array(
				'label'   => __( 'Shape', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'wave',
				'options' => array(
					'wave'    => __( 'Smooth wave', 'km-catalog-builder' ),
					'scallop' => __( 'Round bumps', 'km-catalog-builder' ),
				),
			)
		);

		$this->add_control(
			'color',
			array(
				'label'       => __( 'Colour', 'km-catalog-builder' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '#2C2C2C',
				'selectors'   => array( '{{WRAPPER}} .kmcb-wave' => '--kmcb-wave-color: {{VALUE}};' ),
				'description' => __( 'Match the container this sits in, so the edge reads as part of it.', 'km-catalog-builder' ),
			)
		);

		$this->add_responsive_control(
			'height',
			array(
				'label'     => __( 'Height', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 8, 'max' => 160 ) ),
				'default'   => array( 'size' => 45 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-wave' => '--kmcb-wave-h: {{SIZE}}px;' ),
			)
		);

		$this->add_responsive_control(
			'tile',
			array(
				'label'       => __( 'Bump width', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array( 'px' => array( 'min' => 20, 'max' => 320 ) ),
				'default'     => array( 'size' => 115 ),
				'selectors'   => array( '{{WRAPPER}} .kmcb-wave' => '--kmcb-wave-tile: {{SIZE}}px;' ),
				'description' => __( 'Smaller means more bumps across the width.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'full_width',
			array(
				'label'       => __( 'Stretch past the container', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => '',
				'description' => __( 'Turn on when the container is boxed but the edge should run the full width of the screen.', 'km-catalog-builder' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$place = isset( $settings['place'] ) ? $settings['place'] : 'above';
		$place = in_array( $place, array( 'above', 'below', 'here' ), true ) ? $place : 'above';

		$shape = ( isset( $settings['shape'] ) && 'scallop' === $settings['shape'] ) ? 'scallop' : 'wave';

		// Bumps point away from the container, so the shape flips with the side.
		$direction = ( 'below' === $place ) ? 'down' : 'up';

		$classes = array(
			'kmcb-wave',
			'kmcb-wave--' . $shape . '-' . $direction,
			'kmcb-wave--' . $place,
		);

		if ( 'yes' === ( isset( $settings['full_width'] ) ? $settings['full_width'] : '' ) ) {
			$classes[] = 'kmcb-wave--full';
		}

		printf( '<div class="%s" aria-hidden="true"></div>', esc_attr( implode( ' ', $classes ) ) );
	}
}
