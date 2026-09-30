<?php
/**
 * Elementor widget: Product Accordion.
 *
 * Panels come from the three section fields edited on the product screen, or
 * from the product description split at its headings, or from panels written
 * into the widget itself.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

class KMCB_Accordion_Widget extends Widget_Base {

	public function get_name() {
		return 'kmcb_product_accordion';
	}

	public function get_title() {
		return __( 'Product Accordion', 'km-catalog-builder' );
	}

	public function get_icon() {
		return 'eicon-accordion';
	}

	public function get_categories() {
		return array( 'km-catalog' );
	}

	public function get_keywords() {
		return array( 'accordion', 'product', 'tabs', 'description', 'toggle', 'woocommerce' );
	}

	public function get_style_depends() {
		return array( 'kmcb-grid', 'kmcb-noto-arabic' );
	}

	public function get_script_depends() {
		return array( 'kmcb-grid' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_accordion',
			array( 'label' => __( 'Panels', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'source',
			array(
				'label'       => __( 'Content from', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'fields',
				'options'     => array(
					'fields'      => __( 'The product section fields', 'km-catalog-builder' ),
					'description' => __( 'The product description', 'km-catalog-builder' ),
					'custom'      => __( 'Panels I write here', 'km-catalog-builder' ),
				),
				'description' => __( 'The section fields are edited per product under Product data > Product Details. Reading the description instead turns each of its headings into a panel.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'title_info',
			array(
				'label'       => __( 'First panel title', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Product Information', 'km-catalog-builder' ),
				'label_block' => true,
				'condition'   => array( 'source' => 'fields' ),
			)
		);

		$this->add_control(
			'title_ingredients',
			array(
				'label'       => __( 'Second panel title', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Ingredients / Composition', 'km-catalog-builder' ),
				'label_block' => true,
				'condition'   => array( 'source' => 'fields' ),
			)
		);

		$this->add_control(
			'title_feeding',
			array(
				'label'       => __( 'Third panel title', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Feeding Guide', 'km-catalog-builder' ),
				'label_block' => true,
				'condition'   => array( 'source' => 'fields' ),
			)
		);

		$this->add_control(
			'show_note',
			array(
				'label'       => __( 'Note under the accordion', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => __( 'Prints the product\'s own note under the whole accordion, edited under Product data > Product Details. A product without one shows nothing.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'heading_tag',
			array(
				'label'     => __( 'Split on', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h2',
				'options'   => array(
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
				),
				'condition' => array( 'source' => 'description' ),
			)
		);

		$this->add_control(
			'skip',
			array(
				'label'       => __( 'Leave these out', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => __( 'Product Description', 'km-catalog-builder' ),
				'description' => __( 'Headings to skip, comma separated. The summary usually appears above the accordion already.', 'km-catalog-builder' ),
				'condition'   => array( 'source' => 'description' ),
			)
		);

		$rename = new Repeater();

		$rename->add_control(
			'from',
			array(
				'label'       => __( 'Heading in the product', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$rename->add_control(
			'to',
			array(
				'label'       => __( 'Show it as', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$this->add_control(
			'rename',
			array(
				'label'       => __( 'Rename headings', 'km-catalog-builder' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rename->get_controls(),
				'title_field' => '{{{ from }}} → {{{ to }}}',
				'default'     => array(
					array(
						'from' => 'Composition / Ingredients',
						'to'   => 'Ingredients / Composition',
					),
					array(
						'from' => 'Feeding Guidelines',
						'to'   => 'Feeding Guide',
					),
				),
				'condition'   => array( 'source' => 'description' ),
			)
		);

		$panels = new Repeater();

		$panels->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$panels->add_control(
			'content',
			array(
				'label' => __( 'Content', 'km-catalog-builder' ),
				'type'  => Controls_Manager::WYSIWYG,
			)
		);

		$this->add_control(
			'panels',
			array(
				'label'       => __( 'Panels', 'km-catalog-builder' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $panels->get_controls(),
				'title_field' => '{{{ title }}}',
				'condition'   => array( 'source' => 'custom' ),
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'cards',
				'options' => array(
					'cards'  => __( 'Separate cards', 'km-catalog-builder' ),
					'joined' => __( 'One joined block', 'km-catalog-builder' ),
				),
			)
		);

		$this->add_control(
			'icon',
			array(
				'label'   => __( 'Toggle', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'circle',
				'options' => array(
					'circle' => __( 'Chevron in a circle', 'km-catalog-builder' ),
					'plain'  => __( 'Plain chevron', 'km-catalog-builder' ),
				),
			)
		);

		$this->add_control(
			'open_first',
			array(
				'label'   => __( 'Open the first panel', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);

		$this->add_control(
			'one_at_a_time',
			array(
				'label'   => __( 'Only one open at a time', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->end_controls_section();

		/* ------------------------------------------------------------- Style */

		$this->start_controls_section(
			'style_accordion',
			array(
				'label' => __( 'Accordion', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'acc_align',
			array(
				'label'       => __( 'Heading alignment', 'km-catalog-builder' ),
				'type'        => Controls_Manager::CHOOSE,
				'options'     => array(
					'start'  => array(
						'title' => __( 'Start of the line', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Centre', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-center',
					),
					'end'    => array(
						'title' => __( 'End of the line', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'     => 'start',
				'selectors'   => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-align: {{VALUE}};' ),
				'description' => __( 'Start and end follow the language: on an Arabic page start is the right.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'note_heading',
			array(
				'label'     => __( 'Note under the accordion', 'km-catalog-builder' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'show_note' => 'yes' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'note_typography',
				'selector'  => '{{WRAPPER}} .kmcb-acc-note',
				'condition' => array( 'show_note' => 'yes' ),
			)
		);

		$this->add_control(
			'note_color',
			array(
				'label'     => __( 'Note colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-acc-note' => 'color: {{VALUE}};' ),
				'condition' => array( 'show_note' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'note_align',
			array(
				'label'     => __( 'Note alignment', 'km-catalog-builder' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'start'  => array(
						'title' => __( 'Start of the line', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Centre', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-center',
					),
					'end'    => array(
						'title' => __( 'End of the line', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'start',
				'selectors' => array( '{{WRAPPER}} .kmcb-acc-note' => 'text-align: {{VALUE}};' ),
				'condition' => array( 'show_note' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'note_gap',
			array(
				'label'     => __( 'Space above the note', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors' => array( '{{WRAPPER}} .kmcb-acc-note' => 'margin-top: {{SIZE}}px;' ),
				'condition' => array( 'show_note' => 'yes' ),
			)
		);

		$this->add_control(
			'acc_bg',
			array(
				'label'     => __( 'Background', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'acc_border_color',
			array(
				'label'     => __( 'Border', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#E6E6E6',
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-line: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'acc_head_bg',
			array(
				'label'       => __( 'Title background', 'km-catalog-builder' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '',
				'selectors'   => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-head-bg: {{VALUE}};' ),
				'description' => __( 'Behind every closed panel header. Leave empty to use the accordion background.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'acc_gap',
			array(
				'label'     => __( 'Gap between cards', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'   => array( 'size' => 14 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-gap: {{SIZE}}px;' ),
				'condition' => array( 'layout' => 'cards' ),
			)
		);

		$this->add_control(
			'acc_radius',
			array(
				'label'       => __( 'Card corner radius', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'     => array( 'size' => 12 ),
				'selectors'   => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-radius: {{SIZE}}px;' ),
				'description' => __( 'Each panel. The whole block has its own radius below.', 'km-catalog-builder' ),
			)
		);

		$this->add_responsive_control(
			'acc_head_padding',
			array(
				'label'      => __( 'Header padding', 'km-catalog-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'top'      => 18,
					'right'    => 20,
					'bottom'   => 18,
					'left'     => 20,
					'unit'     => 'px',
					'isLinked' => false,
				),
				'selectors'  => array(
					'{{WRAPPER}} .kmcb-acc-head' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'acc_title_typography',
				'label'    => __( 'Title', 'km-catalog-builder' ),
				'selector' => '{{WRAPPER}} .kmcb-acc-title',
			)
		);

		$this->add_control(
			'acc_title_color',
			array(
				'label'     => __( 'Title colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-title: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'acc_outer_heading',
			array(
				'label'     => __( 'Whole widget', 'km-catalog-builder' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'acc_outer_radius',
			array(
				'label'       => __( 'Corner radius', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'   => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-outer-radius: {{SIZE}}px;' ),
				'description' => __( 'Rounds the outside of the whole block. Leave empty to follow the card radius.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'acc_outer_bg',
			array(
				'label'     => __( 'Background', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-outer-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'acc_outer_line',
			array(
				'label'     => __( 'Border colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-outer-line: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'acc_outer_bw',
			array(
				'label'     => __( 'Border width', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 10 ) ),
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-outer-bw: {{SIZE}}px;' ),
			)
		);

		$this->add_responsive_control(
			'acc_outer_pad',
			array(
				'label'      => __( 'Padding', 'km-catalog-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-outer-pad: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'acc_open_heading',
			array(
				'label'     => __( 'Open panel', 'km-catalog-builder' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'acc_open_bg',
			array(
				'label'       => __( 'Title background', 'km-catalog-builder' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '#E2F4FD',
				'selectors'   => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-open-bg: {{VALUE}};' ),
				'description' => __( 'Behind the header of whichever panel is open.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'acc_open_body_bg',
			array(
				'label'       => __( 'Body background', 'km-catalog-builder' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '#FFFFFF',
				'selectors'   => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-open-body-bg: {{VALUE}};' ),
				'description' => __( 'The panel body behind the answer.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'acc_open_border',
			array(
				'label'     => __( 'Card outline', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-open-border: {{VALUE}};' ),
				'condition' => array( 'layout' => 'cards' ),
			)
		);

		$this->add_control(
			'acc_icon_bg',
			array(
				'label'     => __( 'Toggle circle', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-icon-bg: {{VALUE}};' ),
				'condition' => array( 'icon' => 'circle' ),
			)
		);

		$this->add_control(
			'acc_icon_color',
			array(
				'label'     => __( 'Toggle chevron', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-icon-color: {{VALUE}};' ),
				'condition' => array( 'icon' => 'circle' ),
			)
		);

		$this->add_control(
			'acc_open_title_color',
			array(
				'label'     => __( 'Title colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-open-title: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'acc_body_typography',
				'label'     => __( 'Body', 'km-catalog-builder' ),
				'selector'  => '{{WRAPPER}} .kmcb-acc-content',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'acc_body_color',
			array(
				'label'     => __( 'Body colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-body: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	private function get_product() {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( get_the_ID() ) : false;

		if ( $product ) {
			return $product;
		}

		$ids = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		return empty( $ids ) ? false : wc_get_product( $ids[0] );
	}

	protected function render() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$product = $this->get_product();

		if ( ! $product ) {
			return;
		}

		$settings = $this->get_settings_for_display();

		$rename = array();
		if ( ! empty( $settings['rename'] ) && is_array( $settings['rename'] ) ) {
			foreach ( $settings['rename'] as $row ) {
				if ( ! empty( $row['from'] ) ) {
					$rename[ $row['from'] ] = isset( $row['to'] ) ? $row['to'] : '';
				}
			}
		}

		$panels = array();
		if ( ! empty( $settings['panels'] ) && is_array( $settings['panels'] ) ) {
			foreach ( $settings['panels'] as $row ) {
				$panels[] = array(
					'title'   => isset( $row['title'] ) ? $row['title'] : '',
					'content' => isset( $row['content'] ) ? $row['content'] : '',
				);
			}
		}

		$config = array(
			'widget_id'     => $this->get_id(),
			'source'        => in_array( isset( $settings['source'] ) ? $settings['source'] : '', array( 'fields', 'description', 'custom' ), true ) ? $settings['source'] : 'fields',
			'title_info'        => isset( $settings['title_info'] ) ? $settings['title_info'] : __( 'Product Information', 'km-catalog-builder' ),
			'title_ingredients' => isset( $settings['title_ingredients'] ) ? $settings['title_ingredients'] : __( 'Ingredients / Composition', 'km-catalog-builder' ),
			'title_feeding'     => isset( $settings['title_feeding'] ) ? $settings['title_feeding'] : __( 'Feeding Guide', 'km-catalog-builder' ),
			'heading_tag'   => isset( $settings['heading_tag'] ) ? $settings['heading_tag'] : 'h2',
			'skip'          => isset( $settings['skip'] ) ? explode( ',', (string) $settings['skip'] ) : array(),
			'rename'        => $rename,
			'panels'        => $panels,
			'layout'        => ( isset( $settings['layout'] ) && 'joined' === $settings['layout'] ) ? 'joined' : 'cards',
			'icon'          => ( isset( $settings['icon'] ) && 'plain' === $settings['icon'] ) ? 'plain' : 'circle',
			'show_note'     => 'yes' === ( isset( $settings['show_note'] ) ? $settings['show_note'] : 'yes' ),
			'open_first'    => 'yes' === ( isset( $settings['open_first'] ) ? $settings['open_first'] : '' ),
			'one_at_a_time' => 'yes' === ( isset( $settings['one_at_a_time'] ) ? $settings['one_at_a_time'] : '' ),
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the template.
		echo KMCB_Render::accordion( $config, $product );
	}
}
