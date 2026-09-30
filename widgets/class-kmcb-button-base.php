<?php
/**
 * Shared implementation for the pill buttons.
 *
 * The product button and the general button look and behave identically; they
 * differ only in whether a product may override the link.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;

abstract class KMCB_Button_Base extends Widget_Base {

	const PRODUCT_META = '_kmcb_find_url';

	/**
	 * Whether the current product's own link, if it has one, wins over the
	 * link set on the widget.
	 */
	protected function uses_product_override() {
		return false;
	}

	/**
	 * Default text for the button.
	 */
	protected function default_text() {
		return __( 'Learn More', 'km-catalog-builder' );
	}

	public function get_categories() {
		return array( 'km-catalog' );
	}

	public function get_style_depends() {
		return array( 'kmcb-grid' );
	}

	/**
	 * Product categories, slug => name, for the availability control.
	 *
	 * Arabic categories are separate terms with a "-ar" slug, so they are left
	 * out of the list and matched by the English slug instead: one setting
	 * then covers a template in either language.
	 */
	protected function category_options() {
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return array();
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);

		$out = array();

		if ( is_wp_error( $terms ) ) {
			return $out;
		}

		foreach ( $terms as $term ) {
			if ( '-ar' === substr( $term->slug, -3 ) ) {
				continue;
			}

			$out[ $term->slug ] = $term->name;
		}

		return $out;
	}

	/**
	 * The category slugs a product sits in, its parents included, with the
	 * Polylang language suffix taken off so both languages match.
	 */
	protected function product_category_slugs( $product ) {
		$slugs = array();
		$terms = get_the_terms( $product->get_id(), 'product_cat' );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return $slugs;
		}

		foreach ( $terms as $term ) {
			$slugs[] = $term->slug;

			foreach ( get_ancestors( $term->term_id, 'product_cat' ) as $parent_id ) {
				$parent = get_term( $parent_id, 'product_cat' );

				if ( $parent && ! is_wp_error( $parent ) ) {
					$slugs[] = $parent->slug;
				}
			}
		}

		return array_map(
			function ( $slug ) {
				return ( '-ar' === substr( $slug, -3 ) ) ? substr( $slug, 0, -3 ) : $slug;
			},
			array_unique( $slugs )
		);
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_button',
			array( 'label' => __( 'Button', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $this->default_text(),
				'label_block' => true,
			)
		);

		$this->add_control(
			'link',
			array(
				'label'       => __( 'Link', 'km-catalog-builder' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://example.com/',
				'default'     => array( 'url' => '' ),
				'description' => $this->uses_product_override()
					? __( 'A product can override this under Product data > Grid Card.', 'km-catalog-builder' )
					: '',
			)
		);

		$this->add_control(
			'show_arrow',
			array(
				'label'   => __( 'Arrow', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);

		$this->add_control(
			'arrow_dir',
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
				'condition'   => array( 'show_arrow' => 'yes' ),
				'description' => __( 'A left arrow also moves to the other side of the text, which is what Arabic wants.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'full_width',
			array(
				'label'   => __( 'Full width', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => $this->uses_product_override() ? 'yes' : '',
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
				'default'   => 'flex-start',
				'selectors' => array( '{{WRAPPER}} .kmcb-btn-wrap' => 'justify-content: {{VALUE}};' ),
				'condition' => array( 'full_width!' => 'yes' ),
			)
		);

		$this->end_controls_section();

		if ( $this->uses_product_override() ) {
			$this->register_availability_controls();
		}

		/* ------------------------------------------------------------- Style */

		$this->start_controls_section(
			'style_button',
			array(
				'label' => __( 'Style', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'btn_bg',
			array(
				'label'     => __( 'Background', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-btn' => '--kmcb-btn-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'btn_bg_hover',
			array(
				'label'     => __( 'Background on hover', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#111111',
				'selectors' => array( '{{WRAPPER}} .kmcb-btn' => '--kmcb-btn-bg-hover: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'btn_color',
			array(
				'label'     => __( 'Text', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-btn' => '--kmcb-btn-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'btn_color_hover',
			array(
				'label'     => __( 'Text on hover', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-btn' => '--kmcb-btn-color-hover: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'btn_typography',
				'selector' => '{{WRAPPER}} .kmcb-btn',
			)
		);

		$this->add_control(
			'btn_border_color',
			array(
				'label'     => __( 'Border colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-btn' => '--kmcb-btn-border: {{VALUE}}; border-style: solid;' ),
			)
		);

		$this->add_control(
			'btn_border_width',
			array(
				'label'     => __( 'Border width', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 8 ) ),
				'selectors' => array( '{{WRAPPER}} .kmcb-btn' => 'border-width: {{SIZE}}px; border-style: solid; border-color: var(--kmcb-btn-border, transparent);' ),
			)
		);

		$this->add_control(
			'btn_arrow_gap',
			array(
				'label'     => __( 'Arrow spacing', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 30 ) ),
				'default'   => array( 'size' => 10 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-btn' => 'gap: {{SIZE}}px;' ),
				'condition' => array( 'show_arrow' => 'yes' ),
			)
		);

		$this->add_control(
			'btn_radius',
			array(
				'label'     => __( 'Corner radius', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'   => array( 'size' => 30 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-btn' => 'border-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_responsive_control(
			'btn_padding',
			array(
				'label'      => __( 'Padding', 'km-catalog-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'top'      => 18,
					'right'    => 32,
					'bottom'   => 18,
					'left'     => 32,
					'unit'     => 'px',
					'isLinked' => false,
				),
				'selectors'  => array(
					'{{WRAPPER}} .kmcb-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'btn_shadow',
				'selector' => '{{WRAPPER}} .kmcb-btn',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Which products the link is for, and what the rest show instead.
	 */
	protected function register_availability_controls() {
		$this->start_controls_section(
			'section_availability',
			array( 'label' => __( 'Which products', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'link_cats',
			array(
				'label'       => __( 'Link these categories only', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $this->category_options(),
				'label_block' => true,
				'description' => __( 'Every other product shows the waiting text instead of the link. Leave this empty and every product gets the link. A category covers the products in it and in everything under it, in both languages.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'soon_text',
			array(
				'label'       => __( 'Text for the others', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Coming Soon', 'km-catalog-builder' ),
				'label_block' => true,
				'condition'   => array( 'link_cats!' => array() ),
			)
		);

		$this->add_control(
			'soon_action',
			array(
				'label'     => __( 'And show it as', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'button',
				'options'   => array(
					'button' => __( 'A button that does nothing', 'km-catalog-builder' ),
					'hide'   => __( 'Nothing at all', 'km-catalog-builder' ),
				),
				'condition' => array( 'link_cats!' => array() ),
			)
		);

		$this->add_control(
			'soon_bg',
			array(
				'label'     => __( 'Its background', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-btn--soon' => '--kmcb-btn-bg: {{VALUE}};' ),
				'condition' => array(
					'link_cats!'  => array(),
					'soon_action' => 'button',
				),
			)
		);

		$this->add_control(
			'soon_color',
			array(
				'label'     => __( 'Its text colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-btn--soon' => '--kmcb-btn-color: {{VALUE}};' ),
				'condition' => array(
					'link_cats!'  => array(),
					'soon_action' => 'button',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Is this product one the link is for?
	 */
	protected function product_is_linked( $settings, $product ) {
		$wanted = isset( $settings['link_cats'] ) ? array_filter( (array) $settings['link_cats'] ) : array();

		if ( empty( $wanted ) || ! $product ) {
			return true;
		}

		$wanted = array_map(
			function ( $slug ) {
				return ( '-ar' === substr( $slug, -3 ) ) ? substr( $slug, 0, -3 ) : $slug;
			},
			$wanted
		);

		return (bool) array_intersect( $wanted, $this->product_category_slugs( $product ) );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$text = isset( $settings['text'] ) ? $settings['text'] : '';

		if ( '' === trim( $text ) ) {
			return;
		}

		$url  = isset( $settings['link']['url'] ) ? $settings['link']['url'] : '';
		$soon = false;

		if ( $this->uses_product_override() && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( get_the_ID() );

			if ( $product ) {
				$override = get_post_meta( $product->get_id(), self::PRODUCT_META, true );

				if ( $override ) {
					$url = $override;
				}

				// A product outside the categories the link is for waits its
				// turn: no link, and the text says so.
				if ( ! $this->product_is_linked( $settings, $product ) ) {
					$soon = true;
					$url  = '';

					if ( 'hide' === ( isset( $settings['soon_action'] ) ? $settings['soon_action'] : 'button' ) ) {
						return;
					}

					$text = isset( $settings['soon_text'] ) ? $settings['soon_text'] : __( 'Coming Soon', 'km-catalog-builder' );

					if ( '' === trim( $text ) ) {
						return;
					}
				}
			}
		}

		$classes = 'kmcb-btn';

		if ( $soon ) {
			$classes .= ' kmcb-btn--soon';
		}

		if ( 'yes' === ( isset( $settings['full_width'] ) ? $settings['full_width'] : '' ) ) {
			$classes .= ' kmcb-btn--full';
		}

		$arrow = '';
		$label = '<span>' . esc_html( $text ) . '</span>';
		$inner = $label;

		if ( ! $soon && 'yes' === ( isset( $settings['show_arrow'] ) ? $settings['show_arrow'] : '' ) ) {
			// One arrow, mirrored in CSS rather than a second path to keep in
			// step, and moved to the other side of the label so it leads the
			// way it points.
			$left = 'left' === ( isset( $settings['arrow_dir'] ) ? $settings['arrow_dir'] : 'right' );

			$arrow = '<svg class="kmcb-btn-arrow' . ( $left ? ' kmcb-btn-arrow--left' : '' ) . '" viewBox="0 0 14 10" aria-hidden="true" focusable="false">'
				. '<path d="M1 5h11M8.5 1.5 12 5l-3.5 3.5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';

			$inner = $left ? $arrow . $label : $label . $arrow;
		}

		echo '<div class="kmcb-btn-wrap">';

		if ( $url ) {
			$rel      = array();
			$external = ! empty( $settings['link']['is_external'] );

			if ( ! empty( $settings['link']['nofollow'] ) ) {
				$rel[] = 'nofollow';
			}

			if ( $external ) {
				$rel[] = 'noopener';
			}

			printf(
				'<a class="%1$s" href="%2$s"%3$s%4$s>%5$s</a>',
				esc_attr( $classes ),
				esc_url( $url ),
				$external ? ' target="_blank"' : '',
				$rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '',
				$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- label escaped above, arrow is static markup.
			);
		} else {
			printf(
				'<span class="%1$s">%2$s</span>',
				esc_attr( $classes ),
				$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- label escaped above, arrow is static markup.
			);
		}

		echo '</div>';
	}
}
