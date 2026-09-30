<?php
/**
 * Elementor widget: Product Description.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class KMCB_Description_Widget extends Widget_Base {

	public function get_name() {
		return 'kmcb_product_description';
	}

	public function get_title() {
		return __( 'Product Description', 'km-catalog-builder' );
	}

	public function get_icon() {
		return 'eicon-product-description';
	}

	public function get_categories() {
		return array( 'km-catalog' );
	}

	public function get_keywords() {
		return array( 'product', 'description', 'summary', 'excerpt', 'woocommerce' );
	}

	public function get_style_depends() {
		return array( 'kmcb-grid' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_description',
			array( 'label' => __( 'Description', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'source',
			array(
				'label'       => __( 'Show', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'short',
				'options'     => array(
					'short' => __( 'Short description', 'km-catalog-builder' ),
					'long'  => __( 'Full description', 'km-catalog-builder' ),
				),
				'description' => __( 'The short description is the one-line summary under the product name.', 'km-catalog-builder' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_description',
			array(
				'label' => __( 'Style', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'desc_typography',
				'selector' => '{{WRAPPER}} .kmcb-description',
			)
		);

		$this->add_control(
			'desc_color',
			array(
				'label'     => __( 'Text colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-description' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'desc_link_color',
			array(
				'label'     => __( 'Link colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-description a' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'desc_align',
			array(
				'label'     => __( 'Alignment', 'km-catalog-builder' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'    => array(
						'title' => __( 'Left', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'  => array(
						'title' => __( 'Center', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'   => array(
						'title' => __( 'Right', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-right',
					),
					'justify' => array(
						'title' => __( 'Justified', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-justify',
					),
				),
				'selectors' => array( '{{WRAPPER}} .kmcb-description' => 'text-align: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'desc_max_width',
			array(
				'label'      => __( 'Maximum width', 'km-catalog-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array( 'px' => array( 'min' => 200, 'max' => 1200 ) ),
				'selectors'  => array( '{{WRAPPER}} .kmcb-description' => 'max-width: {{SIZE}}{{UNIT}};' ),
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
		$source   = ( isset( $settings['source'] ) && 'long' === $settings['source'] ) ? 'long' : 'short';

		if ( 'long' === $source ) {
			$content = $product->get_description();
			/** Run the usual content filters so shortcodes and formatting work. */
			$content = apply_filters( 'the_content', $content );
		} else {
			$content = $product->get_short_description();
			$content = apply_filters( 'woocommerce_short_description', $content );
		}

		if ( '' === trim( wp_strip_all_tags( (string) $content ) ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- passed through the content filters above.
		echo '<div class="kmcb-description">' . $content . '</div>';
	}
}
