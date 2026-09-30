<?php
/**
 * Elementor widget: Product Gallery.
 *
 * For single product templates. Shows the current product's featured image and
 * gallery on a coloured card, using the same two colours as the grid card.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

class KMCB_Gallery_Widget extends Widget_Base {

	public function get_name() {
		return 'kmcb_product_gallery';
	}

	public function get_title() {
		return __( 'Product Gallery', 'km-catalog-builder' );
	}

	public function get_icon() {
		return 'eicon-product-images';
	}

	public function get_categories() {
		return array( 'km-catalog' );
	}

	public function get_keywords() {
		return array( 'woocommerce', 'product', 'gallery', 'images', 'single', 'thumbnails' );
	}

	public function get_style_depends() {
		return array( 'kmcb-grid' );
	}

	public function get_script_depends() {
		return array( 'kmcb-grid' );
	}

	private function image_size_options() {
		$options = array( 'full' => __( 'Full', 'km-catalog-builder' ) );

		foreach ( get_intermediate_image_sizes() as $size ) {
			$options[ $size ] = ucwords( str_replace( array( '_', '-' ), ' ', $size ) );
		}

		return $options;
	}

	protected function register_controls() {

		/* ----------------------------------------------------------- Content */

		$this->start_controls_section(
			'section_gallery',
			array( 'label' => __( 'Gallery', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'main_size',
			array(
				'label'   => __( 'Main image size', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'large',
				'options' => $this->image_size_options(),
			)
		);

		$this->add_control(
			'show_thumbs',
			array(
				'label'   => __( 'Show thumbnails', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'thumbs_position',
			array(
				'label'     => __( 'Thumbnail position', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'below',
				'options'   => array(
					'below' => __( 'Below', 'km-catalog-builder' ),
					'left'  => __( 'Left', 'km-catalog-builder' ),
				),
				'condition' => array( 'show_thumbs' => 'yes' ),
			)
		);

		$this->add_control(
			'thumb_size',
			array(
				'label'     => __( 'Thumbnail size', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'woocommerce_thumbnail',
				'options'   => $this->image_size_options(),
				'condition' => array( 'show_thumbs' => 'yes' ),
			)
		);

		$this->end_controls_section();

		/* ------------------------------------------------------------ Colour */

		$this->start_controls_section(
			'section_gallery_color',
			array( 'label' => __( 'Card colour', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'use_product_colors',
			array(
				'label'       => __( 'Use the product colours', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => __( 'Takes the two colours set on the product under Product data > Grid Card, so this page matches its card in the grid.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'color_tax',
			array(
				'label'       => __( 'Inherit from', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'product_cat',
				'options'     => KMCB_Plugin::get_filterable_taxonomies(),
				'description' => __( 'Which taxonomy supplies the colour when a product has none of its own.', 'km-catalog-builder' ),
				'condition'   => array( 'use_product_colors' => 'yes' ),
			)
		);

		$this->add_control(
			'fallback_from',
			array(
				'label'       => __( 'Fallback colour', 'km-catalog-builder' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '#F3F1EC',
				'description' => __( 'Used when the product and its terms have no colour set.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'fallback_to',
			array(
				'label'   => __( 'Fallback second colour', 'km-catalog-builder' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '',
			)
		);

		$this->add_control(
			'wave',
			array(
				'label'       => __( 'Curved wave', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => __( 'Draws the second colour as a curved band along the bottom, matching the grid card.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'thumb_wave',
			array(
				'label'       => __( 'Wave on the thumbnails too', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'condition'   => array( 'wave' => 'yes' ),
				'description' => __( 'Draws the same curved band inside each thumbnail.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'gallery_wave_h',
			array(
				'label'     => __( 'Wave height', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( '%' => array( 'min' => 0, 'max' => 100 ) ),
				'default'   => array(
					'unit' => '%',
					'size' => 34,
				),
				'selectors' => array( '{{WRAPPER}} .kmcb-gallery' => '--kmcb-wave-h: {{SIZE}}%;' ),
				'condition' => array( 'wave' => 'yes' ),
			)
		);

		$this->add_control(
			'gallery_wave_curve',
			array(
				'label'     => __( 'Wave curve', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( '%' => array( 'min' => 0, 'max' => 80 ) ),
				'default'   => array(
					'unit' => '%',
					'size' => 26,
				),
				'selectors' => array( '{{WRAPPER}} .kmcb-gallery' => '--kmcb-wave-curve: {{SIZE}}%;' ),
				'condition' => array( 'wave' => 'yes' ),
			)
		);

		$this->end_controls_section();

		/* ------------------------------------------------------------- Style */

		$this->start_controls_section(
			'style_gallery',
			array(
				'label' => __( 'Main image', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'gallery_radius',
			array(
				'label'     => __( 'Corner radius', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'   => array( 'size' => 18 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-gallery' => '--kmcb-gallery-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'gallery_ratio',
			array(
				'label'       => __( 'Height', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array( '%' => array( 'min' => 50, 'max' => 180 ) ),
				'default'     => array(
					'unit' => '%',
					'size' => 100,
				),
				'selectors'   => array( '{{WRAPPER}} .kmcb-gallery' => '--kmcb-gallery-ratio: calc({{SIZE}} / 100);' ),
				'description' => __( 'As a proportion of the width. 100% is square.', 'km-catalog-builder' ),
			)
		);

		$this->add_responsive_control(
			'gallery_padding',
			array(
				'label'      => __( 'Image padding', 'km-catalog-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'default'    => array(
					'top'      => 40,
					'right'    => 40,
					'bottom'   => 40,
					'left'     => 40,
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'  => array(
					'{{WRAPPER}} .kmcb-gallery-main' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'gallery_shadow',
				'selector' => '{{WRAPPER}} .kmcb-gallery-main',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_thumbs',
			array(
				'label'     => __( 'Thumbnails', 'km-catalog-builder' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_thumbs' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'thumb_size_px',
			array(
				'label'      => __( 'Size', 'km-catalog-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 48, 'max' => 200 ) ),
				'default'    => array(
					'unit' => 'px',
					'size' => 92,
				),
				'selectors'  => array( '{{WRAPPER}} .kmcb-gallery' => '--kmcb-thumb-size: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'thumb_gap',
			array(
				'label'     => __( 'Gap', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'   => array( 'size' => 12 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-gallery' => '--kmcb-thumb-gap: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'thumb_radius',
			array(
				'label'     => __( 'Corner radius', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'   => array( 'size' => 10 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-gallery' => '--kmcb-thumb-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'thumb_active_color',
			array(
				'label'     => __( 'Selected outline', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-gallery' => '--kmcb-thumb-active: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'thumb_active_width',
			array(
				'label'     => __( 'Outline width', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 6 ) ),
				'default'   => array( 'size' => 2 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-gallery' => '--kmcb-thumb-active-w: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * The product to render.
	 *
	 * On a product page that is the current product. In the Elementor editor
	 * there is usually no product in scope, so fall back to the newest one so
	 * the widget previews with real content instead of an empty box.
	 */
	private function get_product() {
		$product = false;

		if ( function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( get_the_ID() );
		}

		if ( $product ) {
			return $product;
		}

		$ids = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'orderby'        => 'date',
				'order'          => 'DESC',
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
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p>' . esc_html__( 'Add a product to preview this widget.', 'km-catalog-builder' ) . '</p>';
			}
			return;
		}

		$settings = $this->get_settings_for_display();

		$config = array(
			'widget_id'          => $this->get_id(),
			'main_size'          => isset( $settings['main_size'] ) ? $settings['main_size'] : 'large',
			'thumb_size'         => isset( $settings['thumb_size'] ) ? $settings['thumb_size'] : 'woocommerce_thumbnail',
			'show_thumbs'        => 'yes' === ( isset( $settings['show_thumbs'] ) ? $settings['show_thumbs'] : '' ),
			'thumbs_position'    => ( isset( $settings['thumbs_position'] ) && 'left' === $settings['thumbs_position'] ) ? 'left' : 'below',
			'use_product_colors' => 'yes' === ( isset( $settings['use_product_colors'] ) ? $settings['use_product_colors'] : '' ),
			'color_tax'          => isset( $settings['color_tax'] ) ? $settings['color_tax'] : 'product_cat',
			'fallback_from'      => isset( $settings['fallback_from'] ) ? $settings['fallback_from'] : '',
			'fallback_to'        => isset( $settings['fallback_to'] ) ? $settings['fallback_to'] : '',
			'wave'               => 'yes' === ( isset( $settings['wave'] ) ? $settings['wave'] : '' ),
			'thumb_wave'         => 'yes' === ( isset( $settings['thumb_wave'] ) ? $settings['thumb_wave'] : 'yes' ),
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the template.
		echo KMCB_Render::gallery( $config, $product );
	}
}
