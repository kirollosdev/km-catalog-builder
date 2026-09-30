<?php
/**
 * Elementor widget: Related Products ("You May Also Like").
 *
 * Renders the same card as the product grid, so a product looks identical
 * wherever it appears.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;

class KMCB_Related_Widget extends Widget_Base {

	public function get_name() {
		return 'kmcb_related_products';
	}

	public function get_title() {
		return __( 'You May Also Like', 'km-catalog-builder' );
	}

	public function get_icon() {
		return 'eicon-products';
	}

	public function get_categories() {
		return array( 'km-catalog' );
	}

	public function get_keywords() {
		return array( 'related', 'products', 'upsell', 'cross-sell', 'you may also like', 'woocommerce' );
	}

	public function get_style_depends() {
		return array( 'kmcb-grid' );
	}

	public function get_script_depends() {
		return array( 'kmcb-grid' );
	}

	private function taxonomy_options() {
		return KMCB_Plugin::get_filterable_taxonomies();
	}

	protected function register_controls() {

		/* ----------------------------------------------------------- Content */

		$this->start_controls_section(
			'section_related',
			array( 'label' => __( 'Products', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'heading',
			array(
				'label'       => __( 'Heading', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'You May Also Like', 'km-catalog-builder' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Show', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'related',
				'options' => array(
					'related'     => __( 'Related products', 'km-catalog-builder' ),
					'category'    => __( 'Others in the same category', 'km-catalog-builder' ),
					'upsells'     => __( 'Upsells', 'km-catalog-builder' ),
					'cross_sells' => __( 'Cross-sells', 'km-catalog-builder' ),
				),
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'   => __( 'How many', 'km-catalog-builder' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 3,
				'min'     => 1,
				'max'     => 24,
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns', 'km-catalog-builder' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				),
				'selectors'      => array( '{{WRAPPER}} .kmcb-related' => '--kmcb-cols: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'mobile_layout',
			array(
				'label'       => __( 'On phones', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'swipe',
				'options'     => array(
					'swipe' => __( 'Swipe sideways', 'km-catalog-builder' ),
					'grid'  => __( 'Stack them', 'km-catalog-builder' ),
				),
				'description' => __( 'Swiping keeps the next card peeking in from the edge, so it reads as a row.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'mobile_card_width',
			array(
				'label'       => __( 'Card width on phones', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( '%' ),
				'range'       => array( '%' => array( 'min' => 50, 'max' => 100 ) ),
				'default'     => array(
					'unit' => '%',
					'size' => 78,
				),
				'selectors'   => array( '{{WRAPPER}} .kmcb-related' => '--kmcb-peek: {{SIZE}}%;' ),
				'description' => __( 'Under 100% leaves room for the next card to show.', 'km-catalog-builder' ),
				'condition'   => array( 'mobile_layout' => 'swipe' ),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Order', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'rand',
				'options' => array(
					'rand'       => __( 'Random', 'km-catalog-builder' ),
					'date'       => __( 'Newest', 'km-catalog-builder' ),
					'title'      => __( 'Alphabetical', 'km-catalog-builder' ),
					'menu_order' => __( 'Catalogue order', 'km-catalog-builder' ),
				),
			)
		);

		$this->end_controls_section();

		/* -------------------------------------------------------------- Card */

		$this->start_controls_section(
			'section_related_card',
			array( 'label' => __( 'Card', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'badge_tax',
			array(
				'label'   => __( 'Badge taxonomy', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array( '' => __( 'None', 'km-catalog-builder' ) ) + $this->taxonomy_options(),
				'default' => '',
			)
		);

		$this->add_control(
			'eyebrow_tax',
			array(
				'label'   => __( 'Small label taxonomy', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array( '' => __( 'None', 'km-catalog-builder' ) ) + $this->taxonomy_options(),
				'default' => 'product_cat',
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => __( 'Link text', 'km-catalog-builder' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'View Product', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'card_arrow_dir',
			array(
				'label'       => __( 'Arrow points', 'km-catalog-builder' ),
				'type'        => Controls_Manager::CHOOSE,
				'default'     => 'right',
				'options'     => array(
					'left'  => array(
						'title' => __( 'Left', 'km-catalog-builder' ),
						'icon'  => 'eicon-arrow-left',
					),
					'right' => array(
						'title' => __( 'Right', 'km-catalog-builder' ),
						'icon'  => 'eicon-arrow-right',
					),
				),
				'toggle'      => false,
				'description' => __( 'A left arrow also moves to the other side of the text, so it still sits at the end of it in Arabic.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'show_price',
			array(
				'label'   => __( 'Show price', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);

		$this->add_control(
			'card_bg_style',
			array(
				'label'   => __( 'Second colour style', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'wave',
				'options' => array(
					'wave'  => __( 'Curved wave', 'km-catalog-builder' ),
					'blend' => __( 'Gradient blend', 'km-catalog-builder' ),
					'flat'  => __( 'Ignore it', 'km-catalog-builder' ),
				),
			)
		);

		$palette = new Repeater();

		$palette->add_control(
			'color',
			array(
				'label'   => __( 'Colour', 'km-catalog-builder' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '#F3F1EC',
			)
		);

		$palette->add_control(
			'color2',
			array(
				'label'   => __( 'Second colour', 'km-catalog-builder' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '',
			)
		);

		$this->add_control(
			'palette',
			array(
				'label'       => __( 'Fallback palette', 'km-catalog-builder' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $palette->get_controls(),
				'title_field' => '{{{ color }}}',
				'default'     => array(
					array(
						'color'  => '#FBE3C7',
						'color2' => '#F6D3AE',
					),
					array(
						'color'  => '#D6EAF6',
						'color2' => '#BEDCF0',
					),
					array(
						'color'  => '#F7DCE8',
						'color2' => '#EFC7DA',
					),
				),
				'description' => __( 'Only for products with no colour of their own.', 'km-catalog-builder' ),
			)
		);

		$this->end_controls_section();

		/* ------------------------------------------------------------- Style */

		$this->start_controls_section(
			'style_related',
			array(
				'label' => __( 'Heading', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'related_heading_typography',
				'selector'       => '{{WRAPPER}} .kmcb-related-heading',
				'fields_options' => array(
					'typography'  => array( 'default' => 'yes' ),
					'font_size'   => array(
						'default' => array(
							'unit' => 'px',
							'size' => 30,
						),
					),
					'font_weight' => array( 'default' => '900' ),
					'line_height' => array(
						'default' => array(
							'unit' => 'em',
							'size' => 1.2,
						),
					),
				),
			)
		);

		$this->add_control(
			'related_heading_color',
			array(
				'label'     => __( 'Colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-related-heading' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'related_heading_gap',
			array(
				'label'     => __( 'Space below', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'default'   => array( 'size' => 28 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-related-heading' => 'margin-bottom: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_related_grid',
			array(
				'label' => __( 'Grid', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'related_gap_x',
			array(
				'label'     => __( 'Column gap', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'   => array( 'size' => 26 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-related' => '--kmcb-gap-x: {{SIZE}}px;' ),
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'related_gap_y',
			array(
				'label'     => __( 'Row gap', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'default'   => array( 'size' => 30 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-related' => '--kmcb-gap-y: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'related_card_radius',
			array(
				'label'     => __( 'Image box radius', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 48 ) ),
				'default'   => array( 'size' => 14 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-related' => '--kmcb-card-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'related_card_ratio',
			array(
				'label'     => __( 'Image box height', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( '%' => array( 'min' => 50, 'max' => 160 ) ),
				'default'   => array(
					'unit' => '%',
					'size' => 73,
				),
				'selectors' => array( '{{WRAPPER}} .kmcb-related' => '--kmcb-card-ratio: calc({{SIZE}} / 100);' ),
			)
		);

		$this->add_control(
			'related_text_scale',
			array(
				'label'       => __( 'Text size', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array( '%' => array( 'min' => 80, 'max' => 160 ) ),
				'default'     => array(
					'unit' => '%',
					'size' => 100,
				),
				'selectors'   => array( '{{WRAPPER}} .kmcb-related' => '--kmcb-fs: calc({{SIZE}} / 100);' ),
				'description' => __( 'Scales anything without a typography setting of its own.', 'km-catalog-builder' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_related_text',
			array(
				'label' => __( 'Card text', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'related_title_heading',
			array(
				'label' => __( 'Product name', 'km-catalog-builder' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'related_title_typography',
				'selector'       => '{{WRAPPER}} .kmcb-title, {{WRAPPER}} .kmcb-title a',
				'fields_options' => array(
					'typography'  => array( 'default' => 'yes' ),
					'font_size'   => array(
						'default' => array(
							'unit' => 'px',
							'size' => 18,
						),
					),
					'font_weight' => array( 'default' => '900' ),
				),
			)
		);

		$this->add_control(
			'related_title_color',
			array(
				'label'     => __( 'Colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array(
					'{{WRAPPER}} .kmcb-title'   => 'color: {{VALUE}};',
					'{{WRAPPER}} .kmcb-title a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'related_title_hover_color',
			array(
				'label'     => __( 'Colour on hover', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-title a:hover' => 'color: {{VALUE}}; opacity: 1;' ),
			)
		);

		$this->add_responsive_control(
			'related_title_gap',
			array(
				'label'     => __( 'Space below', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors' => array( '{{WRAPPER}} .kmcb-title' => 'margin-bottom: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'related_eyebrow_heading',
			array(
				'label'     => __( 'Small label', 'km-catalog-builder' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'related_eyebrow_typography',
				'selector'       => '{{WRAPPER}} .kmcb-eyebrow',
				'fields_options' => array(
					'typography'     => array( 'default' => 'yes' ),
					'font_size'      => array(
						'default' => array(
							'unit' => 'px',
							'size' => 11,
						),
					),
					'font_weight'    => array( 'default' => '600' ),
					'text_transform' => array( 'default' => 'uppercase' ),
					'letter_spacing' => array(
						'default' => array(
							'unit' => 'px',
							'size' => 1,
						),
					),
				),
			)
		);

		$this->add_control(
			'related_eyebrow_color',
			array(
				'label'     => __( 'Colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#8C9A86',
				'selectors' => array( '{{WRAPPER}} .kmcb-eyebrow' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'related_eyebrow_gap',
			array(
				'label'     => __( 'Space below', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors' => array( '{{WRAPPER}} .kmcb-eyebrow' => 'margin-bottom: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'related_link_heading',
			array(
				'label'     => __( 'Link', 'km-catalog-builder' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'related_link_typography',
				'selector'       => '{{WRAPPER}} .kmcb-more',
				'fields_options' => array(
					'typography'  => array( 'default' => 'yes' ),
					'font_size'   => array(
						'default' => array(
							'unit' => 'px',
							'size' => 13,
						),
					),
					'font_weight' => array( 'default' => '600' ),
				),
			)
		);

		$this->add_control(
			'related_link_color',
			array(
				'label'     => __( 'Colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-more' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'related_price_heading',
			array(
				'label'     => __( 'Price', 'km-catalog-builder' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'show_price' => 'yes' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'related_price_typography',
				'selector'  => '{{WRAPPER}} .kmcb-price',
				'condition' => array( 'show_price' => 'yes' ),
			)
		);

		$this->add_control(
			'related_price_color',
			array(
				'label'     => __( 'Colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-price' => 'color: {{VALUE}};' ),
				'condition' => array( 'show_price' => 'yes' ),
			)
		);

		$this->add_control(
			'related_badge_heading',
			array(
				'label'     => __( 'Badge', 'km-catalog-builder' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'related_badge_typography',
				'selector' => '{{WRAPPER}} .kmcb-badge',
			)
		);

		$this->add_control(
			'related_badge_bg',
			array(
				'label'     => __( 'Background', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-related' => '--kmcb-badge-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'related_badge_color',
			array(
				'label'     => __( 'Text', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-related' => '--kmcb-badge-color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'related_body_padding',
			array(
				'label'      => __( 'Text padding', 'km-catalog-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'separator'  => 'before',
				'selectors'  => array(
					'{{WRAPPER}} .kmcb-card-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'related_text_align',
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
				'selectors' => array( '{{WRAPPER}} .kmcb-card-body' => 'text-align: {{VALUE}};' ),
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

	/**
	 * The product ids to show, by source.
	 *
	 * @return int[]
	 */
	private function related_ids( $product, $source, $limit ) {
		$id = $product->get_id();

		switch ( $source ) {
			case 'upsells':
				return array_map( 'intval', $product->get_upsell_ids() );

			case 'cross_sells':
				return array_map( 'intval', $product->get_cross_sell_ids() );

			case 'category':
				$terms = get_the_terms( $id, 'product_cat' );

				if ( is_wp_error( $terms ) || empty( $terms ) ) {
					return array();
				}

				return get_posts(
					array(
						'post_type'      => 'product',
						'post_status'    => 'publish',
						'posts_per_page' => $limit,
						'fields'         => 'ids',
						'post__not_in'   => array( $id ),
						'orderby'        => 'rand',
						'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
							array(
								'taxonomy' => 'product_cat',
								'field'    => 'term_id',
								'terms'    => wp_list_pluck( $terms, 'term_id' ),
							),
						),
					)
				);

			default:
				return function_exists( 'wc_get_related_products' )
					? array_map( 'intval', wc_get_related_products( $id, $limit ) )
					: array();
		}
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
		$limit    = isset( $settings['limit'] ) ? max( 1, (int) $settings['limit'] ) : 3;
		$source   = isset( $settings['source'] ) ? $settings['source'] : 'related';

		$ids = $this->related_ids( $product, $source, $limit );
		$ids = array_slice( array_filter( array_unique( $ids ) ), 0, $limit );

		if ( empty( $ids ) ) {
			return;
		}

		$orderby = isset( $settings['orderby'] ) ? $settings['orderby'] : 'rand';

		$query = new WP_Query(
			array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'post__in'            => $ids,
				'posts_per_page'      => $limit,
				'orderby'             => 'menu_order' === $orderby ? 'menu_order title' : $orderby,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);

		$palette = array();
		if ( ! empty( $settings['palette'] ) && is_array( $settings['palette'] ) ) {
			foreach ( $settings['palette'] as $item ) {
				if ( ! empty( $item['color'] ) ) {
					$palette[] = array(
						'from' => $item['color'],
						'to'   => isset( $item['color2'] ) ? $item['color2'] : '',
					);
				}
			}
		}

		$config = array(
			'heading'        => isset( $settings['heading'] ) ? $settings['heading'] : '',
			'columns'        => isset( $settings['columns'] ) ? (int) $settings['columns'] : 3,
			'per_page'       => $limit,
			'badge_tax'      => isset( $settings['badge_tax'] ) ? $settings['badge_tax'] : '',
			'eyebrow_tax'    => isset( $settings['eyebrow_tax'] ) ? $settings['eyebrow_tax'] : 'product_cat',
			'button_text'    => isset( $settings['button_text'] ) ? $settings['button_text'] : '',
			'card_arrow_dir' => isset( $settings['card_arrow_dir'] ) ? $settings['card_arrow_dir'] : 'right',
			'show_price'     => 'yes' === ( isset( $settings['show_price'] ) ? $settings['show_price'] : '' ),
			'image_size'     => 'woocommerce_thumbnail',
			'card_bg_style'  => isset( $settings['card_bg_style'] ) ? $settings['card_bg_style'] : 'wave',
			'palette'        => $palette,
			'gradient_angle' => 160,
			'mobile_layout'  => ( isset( $settings['mobile_layout'] ) && 'grid' === $settings['mobile_layout'] ) ? 'grid' : 'swipe',
		);

		$state = array(
			'paged' => 1,
			'view'  => 'grid',
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the templates.
		echo KMCB_Render::template(
			'related',
			array(
				'config' => $config,
				'state'  => $state,
				'query'  => $query,
			)
		);

		wp_reset_postdata();
	}
}
