<?php
/**
 * Elementor widget: Segmented Switcher.
 *
 * The pill with two or more segments, e.g. CAT FOOD / DOG FOOD, where each
 * segment links to a different page.
 *
 * Deliberately static: which segment reads as selected is set in the widget,
 * not worked out from the current URL. The pages are duplicates of each other,
 * so each copy simply marks its own segment.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;

class KMCB_Switcher_Widget extends Widget_Base {

	public function get_name() {
		return 'kmcb_switcher';
	}

	public function get_title() {
		return __( 'Segmented Switcher', 'km-catalog-builder' );
	}

	public function get_icon() {
		return 'eicon-toggle';
	}

	public function get_categories() {
		return array( 'km-catalog' );
	}

	public function get_keywords() {
		return array( 'switcher', 'toggle', 'segmented', 'tabs', 'pill', 'nav' );
	}

	public function get_style_depends() {
		return array( 'kmcb-grid' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_switcher',
			array( 'label' => __( 'Segments', 'km-catalog-builder' ) )
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Segment', 'km-catalog-builder' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label'       => __( 'Link', 'km-catalog-builder' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://example.com/cat-food/',
			)
		);

		$repeater->add_control(
			'force_active',
			array(
				'label'       => __( 'Selected', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => '',
				'description' => __( 'The filled segment. Turn it on for the page this switcher sits on.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Segments', 'km-catalog-builder' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ text }}}',
				'default'     => array(
					array(
						'text'         => __( 'Cat Food', 'km-catalog-builder' ),
						'force_active' => 'yes',
					),
					array( 'text' => __( 'Dog Food', 'km-catalog-builder' ) ),
				),
			)
		);

		$this->add_responsive_control(
			'switcher_width',
			array(
				'label'      => __( 'Width', 'km-catalog-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array( 'px' => array( 'min' => 200, 'max' => 900 ) ),
				'default'    => array(
					'unit' => 'px',
					'size' => 640,
				),
				'selectors'  => array( '{{WRAPPER}} .kmcb-switch' => 'width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'switcher_align',
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
				'selectors' => array( '{{WRAPPER}} .kmcb-switch-wrap' => 'justify-content: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		/* ------------------------------------------------------------- Style */

		$this->start_controls_section(
			'style_switcher',
			array(
				'label' => __( 'Style', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'switch_typography',
				'selector' => '{{WRAPPER}} .kmcb-switch-item',
			)
		);

		$this->add_control(
			'switch_bg',
			array(
				'label'     => __( 'Track background', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-switch' => '--kmcb-switch-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'switch_border',
			array(
				'label'     => __( 'Track border', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#E6E6E6',
				'selectors' => array( '{{WRAPPER}} .kmcb-switch' => '--kmcb-switch-border: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'switch_color',
			array(
				'label'     => __( 'Text', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-switch' => '--kmcb-switch-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'switch_active_bg',
			array(
				'label'     => __( 'Selected background', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-switch' => '--kmcb-switch-active-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'switch_active_color',
			array(
				'label'     => __( 'Selected text', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-switch' => '--kmcb-switch-active-color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'switch_height',
			array(
				'label'     => __( 'Height', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 32, 'max' => 90 ) ),
				'default'   => array( 'size' => 48 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-switch' => '--kmcb-switch-h: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'switch_radius',
			array(
				'label'     => __( 'Corner radius', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'   => array( 'size' => 30 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-switch' => '--kmcb-switch-radius: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['items'] ) || ! is_array( $settings['items'] ) ) {
			return;
		}

		$items = array();

		foreach ( $settings['items'] as $item ) {
			$text = isset( $item['text'] ) ? $item['text'] : '';

			if ( '' === trim( $text ) ) {
				continue;
			}

			$url = isset( $item['link']['url'] ) ? $item['link']['url'] : '';

			$items[] = array(
				'text'     => $text,
				'url'      => $url,
				'active'   => 'yes' === ( isset( $item['force_active'] ) ? $item['force_active'] : '' ),
				'external' => ! empty( $item['link']['is_external'] ),
			);
		}

		if ( empty( $items ) ) {
			return;
		}

		echo '<div class="kmcb-switch-wrap"><div class="kmcb-switch" role="group">';

		foreach ( $items as $item ) {
			$classes = 'kmcb-switch-item' . ( $item['active'] ? ' is-active' : '' );

			if ( $item['url'] ) {
				printf(
					'<a class="%1$s" href="%2$s"%3$s%4$s>%5$s</a>',
					esc_attr( $classes ),
					esc_url( $item['url'] ),
					$item['external'] ? ' target="_blank" rel="noopener"' : '',
					$item['active'] ? ' aria-current="page"' : '',
					esc_html( $item['text'] )
				);
			} else {
				printf( '<span class="%1$s">%2$s</span>', esc_attr( $classes ), esc_html( $item['text'] ) );
			}
		}

		echo '</div></div>';
	}
}
