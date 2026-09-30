<?php
/**
 * Elementor widget: Shape Divider.
 *
 * A scalloped or wavy edge to sit at the top or bottom of a container.
 *
 * Elementor's free containers cannot be extended by a third-party widget, so
 * this is a divider you place inside a container rather than a container of
 * its own. Set it to overlap and it sits on the container's edge.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class KMCB_Divider_Widget extends Widget_Base {

	public function get_name() {
		return 'kmcb_shape_divider';
	}

	public function get_title() {
		return __( 'Shape Divider', 'km-catalog-builder' );
	}

	public function get_icon() {
		return 'eicon-divider-shape';
	}

	public function get_categories() {
		return array( 'km-catalog' );
	}

	public function get_keywords() {
		return array( 'divider', 'shape', 'scallop', 'wave', 'edge', 'separator' );
	}

	public function get_style_depends() {
		return array( 'kmcb-grid' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_divider',
			array( 'label' => __( 'Divider', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'shape',
			array(
				'label'   => __( 'Shape', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'scallop-up',
				'options' => array(
					'scallop-up'   => __( 'Scallop, bumps up', 'km-catalog-builder' ),
					'scallop-down' => __( 'Scallop, bumps down', 'km-catalog-builder' ),
					'wave'         => __( 'Wave', 'km-catalog-builder' ),
				),
			)
		);

		$this->add_control(
			'position',
			array(
				'label'       => __( 'Position', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'inline',
				'options'     => array(
					'inline' => __( 'In the flow', 'km-catalog-builder' ),
					'top'    => __( 'Overlap the top', 'km-catalog-builder' ),
					'bottom' => __( 'Overlap the bottom', 'km-catalog-builder' ),
				),
				'description' => __( 'Overlapping pulls the divider onto the edge of the container it sits in.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'color',
			array(
				'label'       => __( 'Colour', 'km-catalog-builder' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '#FFFFFF',
				'selectors'   => array( '{{WRAPPER}} .kmcb-divider' => '--kmcb-div-color: {{VALUE}};' ),
				'description' => __( 'Usually the colour of the section on the other side of the edge.', 'km-catalog-builder' ),
			)
		);

		$this->add_responsive_control(
			'height',
			array(
				'label'     => __( 'Height', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 6, 'max' => 160 ) ),
				'default'   => array( 'size' => 28 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-divider' => '--kmcb-div-h: {{SIZE}}px;' ),
			)
		);

		$this->add_responsive_control(
			'tile',
			array(
				'label'       => __( 'Bump width', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array( 'px' => array( 'min' => 10, 'max' => 300 ) ),
				'default'     => array( 'size' => 64 ),
				'selectors'   => array( '{{WRAPPER}} .kmcb-divider' => '--kmcb-div-tile: {{SIZE}}px;' ),
				'description' => __( 'How wide each scallop is. Smaller means more of them.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'flip',
			array(
				'label'   => __( 'Flip sideways', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$shape = isset( $settings['shape'] ) ? $settings['shape'] : 'scallop-up';
		$shape = in_array( $shape, array( 'scallop-up', 'scallop-down', 'wave' ), true ) ? $shape : 'scallop-up';

		$position = isset( $settings['position'] ) ? $settings['position'] : 'inline';
		$position = in_array( $position, array( 'inline', 'top', 'bottom' ), true ) ? $position : 'inline';

		$classes = array(
			'kmcb-divider',
			'kmcb-divider--' . $shape,
			'kmcb-divider--at-' . $position,
		);

		if ( 'yes' === ( isset( $settings['flip'] ) ? $settings['flip'] : '' ) ) {
			$classes[] = 'kmcb-divider--flip';
		}

		printf( '<div class="%s" aria-hidden="true"></div>', esc_attr( implode( ' ', $classes ) ) );
	}
}
