<?php
/**
 * Elementor widget: Product Taxonomy Line.
 *
 * The small line above a product title, e.g. "Dry Food - Adult Cat", built from
 * whichever taxonomies you choose.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;

class KMCB_Terms_Widget extends Widget_Base {

	public function get_name() {
		return 'kmcb_product_terms';
	}

	public function get_title() {
		return __( 'Product Taxonomy Line', 'km-catalog-builder' );
	}

	public function get_icon() {
		return 'eicon-tags';
	}

	public function get_categories() {
		return array( 'km-catalog' );
	}

	public function get_keywords() {
		return array( 'category', 'taxonomy', 'terms', 'eyebrow', 'life stage', 'attribute', 'product' );
	}

	public function get_style_depends() {
		return array( 'kmcb-grid' );
	}

	/**
	 * Sensible starting pair: product type then life stage, matching the design.
	 * Falls back to the product category when those attributes do not exist.
	 */
	private function default_rows() {
		$taxonomies = KMCB_Plugin::get_filterable_taxonomies();

		$rows = array();

		foreach ( array( 'pa_product-type', 'pa_life-stage' ) as $taxonomy ) {
			if ( isset( $taxonomies[ $taxonomy ] ) ) {
				$rows[] = array( 'taxonomy' => $taxonomy );
			}
		}

		if ( empty( $rows ) ) {
			$rows[] = array( 'taxonomy' => 'product_cat' );
		}

		return $rows;
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_terms',
			array( 'label' => __( 'Taxonomies', 'km-catalog-builder' ) )
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'taxonomy',
			array(
				'label'   => __( 'Taxonomy', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'options' => KMCB_Plugin::get_filterable_taxonomies(),
				'default' => 'product_cat',
			)
		);

		$repeater->add_control(
			'prefix',
			array(
				'label'       => __( 'Before', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'e.g. "for "', 'km-catalog-builder' ),
			)
		);

		$repeater->add_control(
			'suffix',
			array(
				'label'       => __( 'After', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'e.g. " Cat"', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'rows',
			array(
				'label'       => __( 'Show these, in order', 'km-catalog-builder' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ taxonomy }}}',
				'default'     => $this->default_rows(),
				'description' => __( 'Each row adds one taxonomy to the line. A product with no term in a taxonomy simply skips it.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'separator',
			array(
				'label'   => __( 'Separator', 'km-catalog-builder' ),
				'type'    => Controls_Manager::TEXT,
				'default' => ' - ',
			)
		);

		$this->add_control(
			'terms',
			array(
				'label'   => __( 'When a product has several terms', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'first',
				'options' => array(
					'first' => __( 'Show the most specific one', 'km-catalog-builder' ),
					'all'   => __( 'Show them all', 'km-catalog-builder' ),
				),
			)
		);

		$this->add_control(
			'link',
			array(
				'label'   => __( 'Link the terms', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);

		$this->add_control(
			'tag',
			array(
				'label'   => __( 'HTML tag', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'p',
				'options' => array(
					'p'    => 'p',
					'div'  => 'div',
					'span' => 'span',
					'h2'   => 'h2',
					'h3'   => 'h3',
					'h4'   => 'h4',
				),
			)
		);

		$this->end_controls_section();

		/* ------------------------------------------------------------- Style */

		$this->start_controls_section(
			'style_terms',
			array(
				'label' => __( 'Style', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'terms_typography',
				'selector' => '{{WRAPPER}} .kmcb-term-line',
			)
		);

		$this->add_control(
			'terms_color',
			array(
				'label'     => __( 'Colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array(
					'{{WRAPPER}} .kmcb-term-line'   => 'color: {{VALUE}};',
					'{{WRAPPER}} .kmcb-term-line a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'terms_align',
			array(
				'label'     => __( 'Alignment', 'km-catalog-builder' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors' => array( '{{WRAPPER}} .kmcb-term-line' => 'text-align: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'terms_spacing',
			array(
				'label'     => __( 'Space below', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors' => array( '{{WRAPPER}} .kmcb-term-line' => 'margin-bottom: {{SIZE}}px;' ),
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

		$rows = array();
		if ( ! empty( $settings['rows'] ) && is_array( $settings['rows'] ) ) {
			foreach ( $settings['rows'] as $row ) {
				$rows[] = array(
					'taxonomy' => isset( $row['taxonomy'] ) ? $row['taxonomy'] : '',
					'prefix'   => isset( $row['prefix'] ) ? $row['prefix'] : '',
					'suffix'   => isset( $row['suffix'] ) ? $row['suffix'] : '',
				);
			}
		}

		$config = array(
			'rows'      => $rows,
			'separator' => isset( $settings['separator'] ) ? $settings['separator'] : ' - ',
			'terms'     => ( isset( $settings['terms'] ) && 'all' === $settings['terms'] ) ? 'all' : 'first',
			'link'      => 'yes' === ( isset( $settings['link'] ) ? $settings['link'] : '' ),
		);

		$line = KMCB_Render::term_line( $config, $product );

		if ( '' === $line ) {
			return;
		}

		$tag = isset( $settings['tag'] ) ? $settings['tag'] : 'p';
		$tag = in_array( $tag, array( 'p', 'div', 'span', 'h2', 'h3', 'h4' ), true ) ? $tag : 'p';

		printf(
			'<%1$s class="kmcb-term-line">%2$s</%1$s>',
			esc_attr( $tag ),
			$line // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in term_line().
		);
	}
}
