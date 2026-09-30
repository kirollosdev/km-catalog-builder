<?php
/**
 * Elementor widget: Shape Image.
 *
 * Masks any image into a shape and puts an offset colour shape behind it.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class KMCB_Shape_Image_Widget extends Widget_Base {

	public function get_name() {
		return 'kmcb_shape_image';
	}

	public function get_title() {
		return __( 'Shape Image', 'km-catalog-builder' );
	}

	public function get_icon() {
		return 'eicon-image-rollover';
	}

	public function get_categories() {
		return array( 'km-catalog' );
	}

	public function get_keywords() {
		return array( 'image', 'mask', 'shape', 'blob', 'paw', 'photo' );
	}

	public function get_style_depends() {
		return array( 'kmcb-grid' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_shape',
			array( 'label' => __( 'Image', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'image',
			array(
				'label'   => __( 'Image', 'km-catalog-builder' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array( 'url' => \Elementor\Utils::get_placeholder_image_src() ),
			)
		);

		$this->add_control(
			'shape',
			array(
				'label'   => __( 'Shape', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'paw',
				'options' => array(
					'paw'      => __( 'Paw', 'km-catalog-builder' ),
					'blob'     => __( 'Blob', 'km-catalog-builder' ),
					'flower'   => __( 'Flower', 'km-catalog-builder' ),
					'circle'   => __( 'Circle', 'km-catalog-builder' ),
					'squircle' => __( 'Rounded square', 'km-catalog-builder' ),
					'custom'   => __( 'My own SVG', 'km-catalog-builder' ),
				),
			)
		);

		$this->add_control(
			'custom_shape',
			array(
				'label'       => __( 'SVG shape', 'km-catalog-builder' ),
				'type'        => Controls_Manager::MEDIA,
				'media_types' => array( 'svg' ),
				'description' => __( 'A solid black silhouette works best: the filled area is what shows through.', 'km-catalog-builder' ),
				'condition'   => array( 'shape' => 'custom' ),
			)
		);

		$this->add_control(
			'ratio',
			array(
				'label'     => __( 'Height', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( '%' => array( 'min' => 40, 'max' => 200 ) ),
				'default'   => array(
					'unit' => '%',
					'size' => 100,
				),
				'selectors' => array( '{{WRAPPER}} .kmcb-shape' => '--kmcb-shape-ratio: calc({{SIZE}} / 100);' ),
			)
		);

		$this->add_responsive_control(
			'max_width',
			array(
				'label'      => __( 'Maximum width', 'km-catalog-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array( 'px' => array( 'min' => 80, 'max' => 900 ) ),
				'selectors'  => array( '{{WRAPPER}} .kmcb-shape' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'rotation',
			array(
				'label'     => __( 'Tilt', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => -30, 'max' => 30 ) ),
				'default'   => array( 'size' => 0 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-shape' => '--kmcb-shape-rot: {{SIZE}}deg;' ),
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'     => __( 'Alignment', 'km-catalog-builder' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Left', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'     => array(
						'title' => __( 'Center', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'Right', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'center',
				'selectors' => array( '{{WRAPPER}} .kmcb-shape-wrap' => 'justify-content: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_backdrop',
			array( 'label' => __( 'Shape behind', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'show_backdrop',
			array(
				'label'   => __( 'Show it', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'backdrop_color',
			array(
				'label'     => __( 'Colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#5B2E2E',
				'selectors' => array( '{{WRAPPER}} .kmcb-shape' => '--kmcb-shape-bg: {{VALUE}};' ),
				'condition' => array( 'show_backdrop' => 'yes' ),
			)
		);

		$this->add_control(
			'offset_x',
			array(
				'label'     => __( 'Offset sideways', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => -80, 'max' => 80 ) ),
				'default'   => array( 'size' => 12 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-shape' => '--kmcb-shape-x: {{SIZE}}px;' ),
				'condition' => array( 'show_backdrop' => 'yes' ),
			)
		);

		$this->add_control(
			'offset_y',
			array(
				'label'     => __( 'Offset down', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => -80, 'max' => 80 ) ),
				'default'   => array( 'size' => 12 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-shape' => '--kmcb-shape-y: {{SIZE}}px;' ),
				'condition' => array( 'show_backdrop' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$url = isset( $settings['image']['url'] ) ? $settings['image']['url'] : '';

		if ( ! $url ) {
			return;
		}

		$shape = isset( $settings['shape'] ) ? $settings['shape'] : 'paw';
		$shape = in_array( $shape, array( 'paw', 'blob', 'flower', 'circle', 'squircle', 'custom' ), true ) ? $shape : 'paw';

		$style = '';

		if ( 'custom' === $shape ) {
			$svg = isset( $settings['custom_shape']['url'] ) ? $settings['custom_shape']['url'] : '';

			if ( ! $svg ) {
				// Nothing to mask with; fall back to a shape that always works.
				$shape = 'circle';
			} else {
				$style = 'style="--kmcb-shape-mask: url(\'' . esc_url( $svg ) . '\');"';
			}
		}

		$alt = '';

		if ( ! empty( $settings['image']['id'] ) ) {
			$alt = (string) get_post_meta( (int) $settings['image']['id'], '_wp_attachment_image_alt', true );
		}

		$backdrop = 'yes' === ( isset( $settings['show_backdrop'] ) ? $settings['show_backdrop'] : '' );

		printf(
			'<div class="kmcb-shape-wrap"><div class="kmcb-shape kmcb-shape--%1$s" %2$s>%3$s<img class="kmcb-shape-img" src="%4$s" alt="%5$s" /></div></div>',
			esc_attr( $shape ),
			$style, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_url above.
			$backdrop ? '<span class="kmcb-shape-back" aria-hidden="true"></span>' : '',
			esc_url( $url ),
			esc_attr( $alt )
		);
	}
}
