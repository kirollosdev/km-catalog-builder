<?php
/**
 * Elementor widget: Marquee.
 *
 * A band of repeating logos or text that scrolls sideways.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class KMCB_Marquee_Widget extends Widget_Base {

	public function get_name() {
		return 'kmcb_marquee';
	}

	public function get_title() {
		return __( 'Marquee', 'km-catalog-builder' );
	}

	public function get_icon() {
		return 'eicon-slider-push';
	}

	public function get_categories() {
		return array( 'km-catalog' );
	}

	public function get_keywords() {
		return array( 'marquee', 'ticker', 'scroll', 'logos', 'band', 'strip' );
	}

	public function get_style_depends() {
		return array( 'kmcb-grid' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_marquee',
			array( 'label' => __( 'Marquee', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'type',
			array(
				'label'   => __( 'Repeat', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'image',
				'options' => array(
					'image' => __( 'An image', 'km-catalog-builder' ),
					'text'  => __( 'Text', 'km-catalog-builder' ),
				),
			)
		);

		$this->add_control(
			'image',
			array(
				'label'     => __( 'Image', 'km-catalog-builder' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array( 'type' => 'image' ),
			)
		);

		$this->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'km-catalog',
				'label_block' => true,
				'condition'   => array( 'type' => 'text' ),
			)
		);

		$this->add_control(
			'repeat',
			array(
				'label'       => __( 'How many across', 'km-catalog-builder' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 8,
				'min'         => 2,
				'max'         => 40,
				'description' => __( 'Enough to fill the width. The set is duplicated automatically so the loop has no seam.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'speed',
			array(
				'label'       => __( 'Seconds per loop', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array( 'px' => array( 'min' => 4, 'max' => 120 ) ),
				'default'     => array( 'size' => 30 ),
				'selectors'   => array( '{{WRAPPER}} .kmcb-marquee' => '--kmcb-mq-duration: {{SIZE}}s;' ),
				'description' => __( 'Higher is slower.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'direction',
			array(
				'label'   => __( 'Direction', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'left',
				'options' => array(
					'left'  => __( 'Right to left', 'km-catalog-builder' ),
					'right' => __( 'Left to right', 'km-catalog-builder' ),
				),
			)
		);

		$this->add_control(
			'pause_on_hover',
			array(
				'label'   => __( 'Pause on hover', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);

		$this->end_controls_section();

		/* ------------------------------------------------------------- Style */

		$this->start_controls_section(
			'style_marquee',
			array(
				'label' => __( 'Style', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'band_bg',
			array(
				'label'     => __( 'Band background', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-marquee' => '--kmcb-mq-bg: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'band_padding',
			array(
				'label'      => __( 'Band padding', 'km-catalog-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'top'      => 14,
					'right'    => 0,
					'bottom'   => 14,
					'left'     => 0,
					'unit'     => 'px',
					'isLinked' => false,
				),
				'selectors'  => array(
					'{{WRAPPER}} .kmcb-marquee' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'item_gap',
			array(
				'label'     => __( 'Gap between items', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 200 ) ),
				'default'   => array( 'size' => 60 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-marquee' => '--kmcb-mq-gap: {{SIZE}}px;' ),
			)
		);

		$this->add_responsive_control(
			'image_height',
			array(
				'label'     => __( 'Image height', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 10, 'max' => 160 ) ),
				'default'   => array( 'size' => 34 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-marquee' => '--kmcb-mq-img-h: {{SIZE}}px;' ),
				'condition' => array( 'type' => 'image' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'marquee_typography',
				'selector'  => '{{WRAPPER}} .kmcb-marquee-item',
				'condition' => array( 'type' => 'text' ),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'Text colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-marquee' => '--kmcb-mq-color: {{VALUE}};' ),
				'condition' => array( 'type' => 'text' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * One repetition of the marquee content.
	 */
	private function item_markup( $settings ) {
		if ( 'text' === $settings['type'] ) {
			return '<span class="kmcb-marquee-item">' . esc_html( $settings['text'] ) . '</span>';
		}

		if ( empty( $settings['image']['url'] ) ) {
			return '';
		}

		// Empty alt: the same image repeated a dozen times is decoration, and
		// the whole band is hidden from assistive tech below.
		return '<span class="kmcb-marquee-item"><img src="' . esc_url( $settings['image']['url'] ) . '" alt="" /></span>';
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$settings['type'] = ( isset( $settings['type'] ) && 'text' === $settings['type'] ) ? 'text' : 'image';
		$settings['text'] = isset( $settings['text'] ) ? $settings['text'] : '';

		$item = $this->item_markup( $settings );

		if ( '' === $item ) {
			return;
		}

		$repeat = isset( $settings['repeat'] ) ? max( 2, min( 40, (int) $settings['repeat'] ) ) : 8;

		$classes = array( 'kmcb-marquee' );

		if ( 'right' === ( isset( $settings['direction'] ) ? $settings['direction'] : 'left' ) ) {
			$classes[] = 'kmcb-marquee--reverse';
		}

		if ( 'yes' === ( isset( $settings['pause_on_hover'] ) ? $settings['pause_on_hover'] : '' ) ) {
			$classes[] = 'kmcb-marquee--pause';
		}

		// The set is rendered twice and the track shifts by half its width, so
		// the loop repeats with no visible jump.
		$set = str_repeat( $item, $repeat );

		printf(
			'<div class="%1$s" aria-hidden="true"><div class="kmcb-marquee-track">%2$s%2$s</div></div>',
			esc_attr( implode( ' ', $classes ) ),
			$set // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in item_markup().
		);
	}
}
