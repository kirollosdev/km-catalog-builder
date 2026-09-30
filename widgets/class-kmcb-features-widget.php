<?php
/**
 * Elementor widget: Feature List.
 *
 * Icon, heading and a line of copy, repeated. For benefit lists such as
 * "Digestive Support" / "Expertly Developed".
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Typography;

class KMCB_Features_Widget extends Widget_Base {

	public function get_name() {
		return 'kmcb_features';
	}

	public function get_title() {
		return __( 'Feature List', 'km-catalog-builder' );
	}

	public function get_icon() {
		return 'eicon-bullet-list';
	}

	public function get_categories() {
		return array( 'km-catalog' );
	}

	public function get_keywords() {
		return array( 'features', 'benefits', 'icon', 'list', 'usp' );
	}

	public function get_style_depends() {
		return array( 'kmcb-grid' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_features',
			array( 'label' => __( 'Features', 'km-catalog-builder' ) )
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'icon',
			array(
				'label'       => __( 'Icon', 'km-catalog-builder' ),
				'type'        => Controls_Manager::ICONS,
				'description' => __( 'Upload an SVG here to use your own line icons.', 'km-catalog-builder' ),
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Digestive Support', 'km-catalog-builder' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'km-catalog-builder' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 3,
				'default' => __( 'With rice, MOS & beta-glucan for a happy gut.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Items', 'km-catalog-builder' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array(
						'title' => __( 'Digestive Support', 'km-catalog-builder' ),
						'text'  => __( 'With rice, MOS & beta-glucan for a happy gut.', 'km-catalog-builder' ),
					),
					array(
						'title' => __( 'Expertly Developed', 'km-catalog-builder' ),
						'text'  => __( 'Created by pet nutrition experts.', 'km-catalog-builder' ),
					),
				),
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'     => __( 'Columns', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '1',
				'options'   => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'selectors' => array( '{{WRAPPER}} .kmcb-features' => '--kmcb-feat-cols: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'icon_position',
			array(
				'label'   => __( 'Icon position', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'left',
				'options' => array(
					'left' => __( 'Beside the text', 'km-catalog-builder' ),
					'top'  => __( 'Above the text', 'km-catalog-builder' ),
				),
			)
		);

		$this->end_controls_section();

		/* ------------------------------------------------------------- Style */

		$this->start_controls_section(
			'style_features',
			array(
				'label' => __( 'Style', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'     => __( 'Icon size', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 16, 'max' => 120 ) ),
				'default'   => array( 'size' => 44 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-features' => '--kmcb-feat-icon: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array(
					'{{WRAPPER}} .kmcb-feature-icon'          => 'color: {{VALUE}};',
					'{{WRAPPER}} .kmcb-feature-icon svg'      => 'fill: {{VALUE}};',
					'{{WRAPPER}} .kmcb-feature-icon svg path' => 'stroke: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_gap',
			array(
				'label'     => __( 'Gap beside the icon', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'   => array( 'size' => 18 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-features' => '--kmcb-feat-gap: {{SIZE}}px;' ),
			)
		);

		$this->add_responsive_control(
			'row_gap',
			array(
				'label'     => __( 'Gap between items', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'default'   => array( 'size' => 34 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-features' => '--kmcb-feat-row-gap: {{SIZE}}px;' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'feat_title_typography',
				'label'     => __( 'Title', 'km-catalog-builder' ),
				'selector'  => '{{WRAPPER}} .kmcb-feature-title',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Title colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-feature-title' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'feat_text_typography',
				'label'    => __( 'Text', 'km-catalog-builder' ),
				'selector' => '{{WRAPPER}} .kmcb-feature-text',
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'Text colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#5B6B52',
				'selectors' => array( '{{WRAPPER}} .kmcb-feature-text' => 'color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['items'] ) || ! is_array( $settings['items'] ) ) {
			return;
		}

		$position = ( isset( $settings['icon_position'] ) && 'top' === $settings['icon_position'] ) ? 'top' : 'left';

		printf( '<div class="kmcb-features kmcb-features--icon-%s">', esc_attr( $position ) );

		foreach ( $settings['items'] as $item ) {
			$title = isset( $item['title'] ) ? $item['title'] : '';
			$text  = isset( $item['text'] ) ? $item['text'] : '';

			if ( '' === trim( $title ) && '' === trim( $text ) ) {
				continue;
			}

			echo '<div class="kmcb-feature">';

			if ( ! empty( $item['icon']['value'] ) ) {
				echo '<span class="kmcb-feature-icon">';
				Icons_Manager::render_icon( $item['icon'], array( 'aria-hidden' => 'true' ) );
				echo '</span>';
			}

			echo '<div class="kmcb-feature-body">';

			if ( '' !== trim( $title ) ) {
				echo '<h3 class="kmcb-feature-title">' . esc_html( $title ) . '</h3>';
			}

			if ( '' !== trim( $text ) ) {
				echo '<p class="kmcb-feature-text">' . esc_html( $text ) . '</p>';
			}

			echo '</div></div>';
		}

		echo '</div>';
	}
}
