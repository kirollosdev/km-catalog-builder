<?php
/**
 * Elementor widget: Product Variations.
 *
 * One row of boxes per variation attribute, e.g. "2 kg" and "10 kg". Choosing
 * a box swaps the Product Gallery's main image to that variation's image.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

class KMCB_Variations_Widget extends Widget_Base {

	public function get_name() {
		return 'kmcb_product_variations';
	}

	public function get_title() {
		return __( 'Product Variations', 'km-catalog-builder' );
	}

	public function get_icon() {
		return 'eicon-product-variations';
	}

	public function get_categories() {
		return array( 'km-catalog' );
	}

	public function get_keywords() {
		return array( 'variation', 'variations', 'size', 'swatch', 'options', 'attribute', 'product', 'woocommerce' );
	}

	public function get_style_depends() {
		return array( 'kmcb-grid' );
	}

	public function get_script_depends() {
		return array( 'kmcb-grid' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_variations',
			array( 'label' => __( 'Variations', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'show_label',
			array(
				'label'   => __( 'Show the attribute name', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'label_text',
			array(
				'label'       => __( 'Name', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'e.g. Size', 'km-catalog-builder' ),
				'description' => __( 'Leave empty to use the attribute\'s own name, which follows the page language. Only applies when the product has one attribute to choose.', 'km-catalog-builder' ),
				'condition'   => array( 'show_label' => 'yes' ),
			)
		);

		$this->add_control(
			'swap_image',
			array(
				'label'       => __( 'Swap the gallery image', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => __( 'Shows the chosen variation\'s image in the Product Gallery widget on the same page.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'image_size',
			array(
				'label'     => __( 'Image size', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'large',
				'options'   => $this->image_sizes(),
				'condition' => array( 'swap_image' => 'yes' ),
				'description' => __( 'Match the Product Gallery\'s main image size.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'update_url',
			array(
				'label'       => __( 'Remember the choice in the link', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => __( 'Adds the chosen size to the address, so a shared link opens on it.', 'km-catalog-builder' ),
			)
		);

		$this->end_controls_section();

		/* ------------------------------------------------------------ Label */

		$this->start_controls_section(
			'style_label',
			array(
				'label'     => __( 'Name', 'km-catalog-builder' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_label' => 'yes' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'label_typography',
				'selector' => '{{WRAPPER}} .kmcb-var-label',
			)
		);

		$this->add_control(
			'label_color',
			array(
				'label'     => __( 'Colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-var-label' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'label_spacing',
			array(
				'label'     => __( 'Space below', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors' => array( '{{WRAPPER}} .kmcb-var-label' => 'margin-bottom: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();

		/* ------------------------------------------------------------ Boxes */

		$this->start_controls_section(
			'style_boxes',
			array(
				'label' => __( 'Boxes', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'box_typography',
				'selector' => '{{WRAPPER}} .kmcb-var-box',
			)
		);

		$this->add_responsive_control(
			'boxes_align',
			array(
				'label'     => __( 'Alignment', 'km-catalog-builder' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Start', 'km-catalog-builder' ),
						'icon'  => 'eicon-align-start-h',
					),
					'center'     => array(
						'title' => __( 'Center', 'km-catalog-builder' ),
						'icon'  => 'eicon-align-center-h',
					),
					'flex-end'   => array(
						'title' => __( 'End', 'km-catalog-builder' ),
						'icon'  => 'eicon-align-end-h',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .kmcb-var-options' => 'justify-content: {{VALUE}};',
					'{{WRAPPER}} .kmcb-var-label'   => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'box_gap',
			array(
				'label'     => __( 'Space between', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors' => array( '{{WRAPPER}} .kmcb-var-options' => 'gap: {{SIZE}}px;' ),
			)
		);

		$this->add_responsive_control(
			'box_min_width',
			array(
				'label'     => __( 'Minimum width', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 300 ) ),
				'selectors' => array( '{{WRAPPER}} .kmcb-var-box' => 'min-width: {{SIZE}}px;' ),
			)
		);

		$this->add_responsive_control(
			'box_padding',
			array(
				'label'      => __( 'Padding', 'km-catalog-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .kmcb-var-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'box_radius',
			array(
				'label'      => __( 'Corner radius', 'km-catalog-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .kmcb-var-box' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'box_border_width',
			array(
				'label'     => __( 'Border width', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 6 ) ),
				'selectors' => array( '{{WRAPPER}} .kmcb-var-box' => 'border-width: {{SIZE}}px;' ),
			)
		);

		$this->start_controls_tabs( 'box_states' );

		foreach ( array(
			'normal' => array( __( 'Normal', 'km-catalog-builder' ), '{{WRAPPER}} .kmcb-var-box' ),
			'hover'  => array( __( 'Hover', 'km-catalog-builder' ), '{{WRAPPER}} .kmcb-var-box:hover' ),
			'active' => array( __( 'Selected', 'km-catalog-builder' ), '{{WRAPPER}} .kmcb-var-box.is-active' ),
		) as $state => $info ) {
			$this->start_controls_tab( 'box_' . $state, array( 'label' => $info[0] ) );

			$this->add_control(
				'box_' . $state . '_color',
				array(
					'label'     => __( 'Text colour', 'km-catalog-builder' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $info[1] => 'color: {{VALUE}};' ),
				)
			);

			$this->add_control(
				'box_' . $state . '_bg',
				array(
					'label'     => __( 'Background', 'km-catalog-builder' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $info[1] => 'background-color: {{VALUE}};' ),
				)
			);

			$this->add_control(
				'box_' . $state . '_border',
				array(
					'label'     => __( 'Border colour', 'km-catalog-builder' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $info[1] => 'border-color: {{VALUE}};' ),
				)
			);

			$this->end_controls_tab();
		}

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function image_sizes() {
		$out = array( 'full' => __( 'Full', 'km-catalog-builder' ) );

		foreach ( get_intermediate_image_sizes() as $size ) {
			$out[ $size ] = $size;
		}

		return $out;
	}

	/**
	 * The product being viewed. In the editor, where there may be none or a
	 * simple one, the first variable product stands in so the boxes can be
	 * styled.
	 */
	private function get_product() {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( get_the_ID() ) : false;

		if ( $product && $product->is_type( 'variable' ) ) {
			return $product;
		}

		$editing = class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode();

		if ( $product && ! $editing ) {
			return $product;
		}

		$ids = wc_get_products(
			array(
				'type'   => 'variable',
				'status' => 'publish',
				'limit'  => 1,
				'return' => 'ids',
			)
		);

		return empty( $ids ) ? $product : wc_get_product( $ids[0] );
	}

	protected function render() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$product = $this->get_product();
		$editing = \Elementor\Plugin::$instance->editor->is_edit_mode();

		// A simple product has nothing to choose, so the widget stays out of the way.
		if ( ! $product || ! $product->is_type( 'variable' ) ) {
			if ( $editing ) {
				echo '<p>' . esc_html__( 'Product Variations shows on variable products. Add one to preview this widget.', 'km-catalog-builder' ) . '</p>';
			}
			return;
		}

		$settings   = $this->get_settings_for_display();
		$swap       = 'yes' === ( isset( $settings['swap_image'] ) ? $settings['swap_image'] : 'yes' );
		$image_size = ! empty( $settings['image_size'] ) ? $settings['image_size'] : 'large';

		$groups = $this->groups( $product );

		if ( empty( $groups ) ) {
			return;
		}

		$variations = $this->variations( $product, $swap ? $image_size : '' );
		$selected   = $this->selected( $product, $groups );

		$show_label = 'yes' === ( isset( $settings['show_label'] ) ? $settings['show_label'] : 'yes' );
		$label_text = ( 1 === count( $groups ) && ! empty( $settings['label_text'] ) ) ? $settings['label_text'] : '';

		printf(
			'<div class="kmcb-variations" data-kmcb-variations data-swap="%1$s" data-url="%2$s" data-variations="%3$s">',
			$swap ? '1' : '0',
			'yes' === ( isset( $settings['update_url'] ) ? $settings['update_url'] : 'yes' ) ? '1' : '0',
			esc_attr( wp_json_encode( $variations ) )
		);

		foreach ( $groups as $name => $group ) {
			$label_id = 'kmcb-var-' . $this->get_id() . '-' . sanitize_html_class( $name );

			echo '<div class="kmcb-var-group" data-attribute="' . esc_attr( $name ) . '">';

			if ( $show_label ) {
				printf(
					'<p class="kmcb-var-label" id="%1$s">%2$s</p>',
					esc_attr( $label_id ),
					esc_html( '' !== $label_text ? $label_text : $group['label'] )
				);
			}

			printf(
				'<div class="kmcb-var-options" role="radiogroup"%s>',
				$show_label ? ' aria-labelledby="' . esc_attr( $label_id ) . '"' : ' aria-label="' . esc_attr( $group['label'] ) . '"'
			);

			foreach ( $group['options'] as $slug => $option_name ) {
				$active = isset( $selected[ $name ] ) && (string) $selected[ $name ] === (string) $slug;

				printf(
					'<button type="button" class="kmcb-var-box%1$s" role="radio" aria-checked="%2$s" data-value="%3$s">%4$s</button>',
					$active ? ' is-active' : '',
					$active ? 'true' : 'false',
					esc_attr( $slug ),
					esc_html( $option_name )
				);
			}

			echo '</div></div>';
		}

		echo '</div>';
	}

	/**
	 * Each variation attribute with its options in the product's own order.
	 *
	 * @return array "attribute_pa_size" => array( label, options: slug => name )
	 */
	private function groups( $product ) {
		$groups = array();

		foreach ( $product->get_variation_attributes() as $attribute => $values ) {
			$values  = array_map( 'strval', (array) $values );
			$options = array();

			if ( taxonomy_exists( $attribute ) ) {
				// wc_get_product_terms keeps the order set on the attribute.
				foreach ( wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) ) as $term ) {
					if ( in_array( $term->slug, $values, true ) ) {
						$options[ $term->slug ] = $term->name;
					}
				}
			} else {
				foreach ( $values as $value ) {
					$options[ $value ] = $value;
				}
			}

			if ( empty( $options ) ) {
				continue;
			}

			$groups[ 'attribute_' . sanitize_title( $attribute ) ] = array(
				'label'   => wc_attribute_label( $attribute, $product ),
				'options' => $options,
			);
		}

		return $groups;
	}

	/**
	 * What the page needs to know about each variation: its attribute values
	 * and its image.
	 */
	private function variations( $product, $image_size ) {
		$out = array();

		foreach ( $product->get_children() as $child_id ) {
			$variation = wc_get_product( $child_id );

			if ( ! $variation || ! $variation->exists() || 'publish' !== $variation->get_status() ) {
				continue;
			}

			$item = array(
				'id'         => (int) $child_id,
				'attributes' => $variation->get_variation_attributes(),
			);

			$image_id = (int) $variation->get_image_id();

			if ( $image_size && $image_id ) {
				$item['image_id'] = $image_id;
				$item['image']    = (string) wp_get_attachment_image_url( $image_id, $image_size );
				$item['srcset'] = (string) wp_get_attachment_image_srcset( $image_id, $image_size );
			}

			$out[] = $item;
		}

		return $out;
	}

	/**
	 * The starting choice: the one in the link if there is one, then the
	 * product's default, then the first option.
	 */
	private function selected( $product, $groups ) {
		$defaults = $product->get_default_attributes();
		$selected = array();

		foreach ( $groups as $name => $group ) {
			$taxonomy = substr( $name, strlen( 'attribute_' ) );

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read only, to preselect a box.
			$requested = isset( $_GET[ $name ] ) ? sanitize_title( wp_unslash( $_GET[ $name ] ) ) : '';

			if ( '' !== $requested && isset( $group['options'][ $requested ] ) ) {
				$selected[ $name ] = $requested;
			} elseif ( isset( $defaults[ $taxonomy ] ) && isset( $group['options'][ $defaults[ $taxonomy ] ] ) ) {
				$selected[ $name ] = $defaults[ $taxonomy ];
			} else {
				$selected[ $name ] = (string) key( $group['options'] );
			}
		}

		return $selected;
	}
}
