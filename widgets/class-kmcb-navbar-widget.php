<?php
/**
 * Elementor widget: Navbar.
 *
 * The rounded white bar: site logo, a real WordPress menu, and a language
 * button. Everything comes from the site itself, so the menu stays correct
 * without being rebuilt in the page.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;

class KMCB_Navbar_Widget extends Widget_Base {

	public function get_name() {
		return 'kmcb_navbar';
	}

	public function get_title() {
		return __( 'Navbar', 'km-catalog-builder' );
	}

	public function get_icon() {
		return 'eicon-nav-menu';
	}

	public function get_categories() {
		return array( 'km-catalog' );
	}

	public function get_keywords() {
		return array( 'nav', 'menu', 'header', 'navbar', 'logo', 'language' );
	}

	public function get_style_depends() {
		return array( 'kmcb-grid' );
	}

	public function get_script_depends() {
		return array( 'kmcb-grid' );
	}

	/**
	 * Registered menus, so the widget can name one directly.
	 */
	private function menu_options() {
		$options = array( '' => __( 'Use a menu location', 'km-catalog-builder' ) );

		foreach ( wp_get_nav_menus() as $menu ) {
			$options[ $menu->slug ] = $menu->name;
		}

		return $options;
	}

	private function location_options() {
		$options = array( '' => __( 'None', 'km-catalog-builder' ) );

		foreach ( get_registered_nav_menus() as $slug => $label ) {
			$options[ $slug ] = $label;
		}

		return $options;
	}

	protected function register_controls() {

		/* ----------------------------------------------------------- Content */

		$this->start_controls_section(
			'section_nav',
			array( 'label' => __( 'Navbar', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'logo_source',
			array(
				'label'   => __( 'Logo', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'site',
				'options' => array(
					'site'   => __( 'The site logo', 'km-catalog-builder' ),
					'custom' => __( 'An image I choose', 'km-catalog-builder' ),
					'none'   => __( 'No logo', 'km-catalog-builder' ),
				),
			)
		);

		$this->add_control(
			'logo_image',
			array(
				'label'     => __( 'Image', 'km-catalog-builder' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array( 'logo_source' => 'custom' ),
			)
		);

		$this->add_responsive_control(
			'logo_height',
			array(
				'label'     => __( 'Logo height', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 16, 'max' => 120 ) ),
				'default'   => array( 'size' => 38 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-logo-h: {{SIZE}}px;' ),
				'condition' => array( 'logo_source!' => 'none' ),
			)
		);

		$this->add_control(
			'menu_location',
			array(
				'label'       => __( 'Menu location', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $this->location_options(),
				'description' => __( 'Uses whatever menu is assigned to this location under Appearance > Menus.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'menu_slug',
			array(
				'label'       => __( 'Or a specific menu', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $this->menu_options(),
				'description' => __( 'Overrides the location above.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'show_lang',
			array(
				'label'   => __( 'Language button', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'lang_source',
			array(
				'label'       => __( 'Comes from', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'text',
				'options'     => array(
					'text' => __( 'Text and a link', 'km-catalog-builder' ),
					'menu' => __( 'A menu', 'km-catalog-builder' ),
				),
				'condition'   => array( 'show_lang' => 'yes' ),
				'description' => __( 'Pick a menu to hold a translation plugin\'s language switcher. It is rendered the same way as the main menu, so the switcher keeps working.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'lang_text',
			array(
				'label'     => __( 'Button text', 'km-catalog-builder' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'العربية',
				'condition' => array(
					'show_lang'   => 'yes',
					'lang_source' => 'text',
				),
			)
		);

		$this->add_control(
			'lang_link',
			array(
				'label'       => __( 'Button link', 'km-catalog-builder' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://example.com/ar/',
				'condition'   => array(
					'show_lang'   => 'yes',
					'lang_source' => 'text',
				),
			)
		);

		$this->add_control(
			'lang_menu_location',
			array(
				'label'       => __( 'Menu location', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $this->location_options(),
				'condition'   => array(
					'show_lang'   => 'yes',
					'lang_source' => 'menu',
				),
				'description' => __( 'Uses whatever menu is assigned to this location under Appearance > Menus.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'lang_menu_slug',
			array(
				'label'     => __( 'Or a specific menu', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => $this->menu_options(),
				'condition' => array(
					'show_lang'   => 'yes',
					'lang_source' => 'menu',
				),
			)
		);

		$this->add_control(
			'transparent_mode',
			array(
				'label'       => __( 'Float over the content', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'auto',
				'separator'   => 'before',
				'options'     => array(
					'auto'   => __( 'On pages marked for it', 'km-catalog-builder' ),
					'always' => __( 'Always', 'km-catalog-builder' ),
					'never'  => __( 'Never', 'km-catalog-builder' ),
				),
				'description' => __( 'Lifts the bar out of the flow so the section below starts at the very top and shows around it. The bar itself keeps its colour. Tick "Float the header on this page" in the Header box when editing a page.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'overlay_top',
			array(
				'label'     => __( 'Distance from the top', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 160 ) ),
				'default'   => array( 'size' => 20 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-top: {{SIZE}}px;' ),
				'condition' => array( 'transparent_mode!' => 'never' ),
			)
		);

		$this->add_control(
			'nav_z',
			array(
				'label'       => __( 'Stacking order', 'km-catalog-builder' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 20,
				'min'         => 1,
				'max'         => 9999,
				'selectors'   => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-z: {{VALUE}};' ),
				'description' => __( 'Raise it if something in the hero covers the bar.', 'km-catalog-builder' ),
				'condition'   => array( 'transparent_mode!' => 'never' ),
			)
		);

		$this->add_control(
			'clear_bar',
			array(
				'label'       => __( 'Clear the bar too', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => '',
				'description' => __( 'Off keeps the white bar and floats it over the hero. On removes the bar background as well, leaving just the logo and menu on the image.', 'km-catalog-builder' ),
				'condition'   => array( 'transparent_mode!' => 'never' ),
			)
		);

		$this->add_control(
			'logo_image_transparent',
			array(
				'label'       => __( 'Logo when the bar is clear', 'km-catalog-builder' ),
				'type'        => Controls_Manager::MEDIA,
				'description' => __( 'Optional. A light version of the logo, for dark backgrounds.', 'km-catalog-builder' ),
				'condition'   => array( 'clear_bar' => 'yes' ),
			)
		);

		$this->end_controls_section();

		/* ------------------------------------------------------- Transparent */

		$this->start_controls_section(
			'style_nav_transparent',
			array(
				'label'     => __( 'When the bar is clear', 'km-catalog-builder' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'clear_bar' => 'yes' ),
			)
		);

		$this->add_control(
			't_bg',
			array(
				'label'       => __( 'Background', 'km-catalog-builder' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => 'rgba(255,255,255,0)',
				'selectors'   => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-t-bg: {{VALUE}};' ),
				'description' => __( 'Fully clear by default. A faint tint often reads better over a busy photo.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			't_color',
			array(
				'label'     => __( 'Menu colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-t-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			't_hover',
			array(
				'label'     => __( 'Menu hover', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-t-hover: {{VALUE}};' ),
			)
		);

		$this->add_control(
			't_active',
			array(
				'label'     => __( 'Current page', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-t-active: {{VALUE}};' ),
			)
		);

		$this->add_control(
			't_lang_color',
			array(
				'label'     => __( 'Language text', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-t-lang-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			't_lang_border',
			array(
				'label'     => __( 'Language border', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-t-lang-border: {{VALUE}};' ),
			)
		);

		$this->add_control(
			't_toggle',
			array(
				'label'     => __( 'Menu button', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-t-toggle: {{VALUE}};' ),
			)
		);

		$this->add_control(
			't_shadow',
			array(
				'label'       => __( 'Keep the shadow', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => '',
				'description' => __( 'Off by default: a shadow under a clear bar reads as a mistake.', 'km-catalog-builder' ),
			)
		);

		$this->end_controls_section();

		/* --------------------------------------------------------------- Bar */

		$this->start_controls_section(
			'style_nav_bar',
			array(
				'label' => __( 'Bar', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'bar_bg',
			array(
				'label'     => __( 'Background', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'bar_radius',
			array(
				'label'     => __( 'Corner radius', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'   => array( 'size' => 44 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_responsive_control(
			'nav_outer_space',
			array(
				'label'       => __( 'Space around the bar', 'km-catalog-builder' ),
				'type'        => Controls_Manager::DIMENSIONS,
				'size_units'  => array( 'px', 'em' ),
				'default'     => array(
					'top'      => 20,
					'right'    => 16,
					'bottom'   => 20,
					'left'     => 16,
					'unit'     => 'px',
					'isLinked' => false,
				),
				'selectors'   => array(
					'{{WRAPPER}} .kmcb-nav:not(.kmcb-nav--overlay)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'description' => __( 'Only applies when the bar is in the flow. While it floats, use "Distance from the top" instead — padding here would reserve height and put a band above the hero.', 'km-catalog-builder' ),
			)
		);

		$this->add_responsive_control(
			'bar_padding',
			array(
				'label'      => __( 'Padding', 'km-catalog-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'top'      => 16,
					'right'    => 34,
					'bottom'   => 16,
					'left'     => 34,
					'unit'     => 'px',
					'isLinked' => false,
				),
				'selectors'  => array(
					'{{WRAPPER}} .kmcb-nav-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'bar_max_width',
			array(
				'label'      => __( 'Maximum width', 'km-catalog-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array( 'px' => array( 'min' => 400, 'max' => 1600 ) ),
				'default'    => array(
					'unit' => 'px',
					'size' => 1180,
				),
				'selectors'  => array( '{{WRAPPER}} .kmcb-nav-inner' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'bar_shadow',
				'selector' => '{{WRAPPER}} .kmcb-nav-inner',
				'fields_options' => array(
					'box_shadow_type' => array( 'default' => 'yes' ),
					'box_shadow'      => array(
						'default' => array(
							'horizontal' => 0,
							'vertical'   => 6,
							'blur'       => 24,
							'spread'     => 0,
							'color'      => 'rgba(0,0,0,0.06)',
						),
					),
				),
			)
		);

		$this->end_controls_section();

		/* -------------------------------------------------------------- Menu */

		$this->start_controls_section(
			'style_nav_menu',
			array(
				'label' => __( 'Menu', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'menu_typography',
				'selector'       => '{{WRAPPER}} .kmcb-nav-list a',
				'fields_options' => array(
					'typography'  => array( 'default' => 'yes' ),
					'font_size'   => array(
						'default' => array(
							'unit' => 'px',
							'size' => 15,
						),
					),
					'font_weight' => array( 'default' => '500' ),
				),
			)
		);

		$this->add_control(
			'menu_color',
			array(
				'label'     => __( 'Colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4A4A4A',
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'menu_hover_color',
			array(
				'label'     => __( 'Hover', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-hover: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'menu_active_color',
			array(
				'label'     => __( 'Current page', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-active: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'menu_active_weight',
			array(
				'label'     => __( 'Current page weight', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '700',
				'options'   => array(
					'400' => '400',
					'500' => '500',
					'600' => '600',
					'700' => '700',
					'800' => '800',
					'900' => '900',
				),
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-active-weight: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'menu_gap',
			array(
				'label'     => __( 'Gap between items', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 4, 'max' => 80 ) ),
				'default'   => array( 'size' => 30 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-gap: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();

		/* ---------------------------------------------------------- Language */

		$this->start_controls_section(
			'style_nav_lang',
			array(
				'label'     => __( 'Language button', 'km-catalog-builder' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_lang' => 'yes' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'lang_typography',
				'selector' => '{{WRAPPER}} .kmcb-nav-lang',
			)
		);

		$this->add_control(
			'lang_color',
			array(
				'label'     => __( 'Text', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-lang-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'lang_border',
			array(
				'label'     => __( 'Border', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-lang-border: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'lang_bg',
			array(
				'label'     => __( 'Background', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-lang-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'lang_hover_bg',
			array(
				'label'     => __( 'Background on hover', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-lang-hover-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'lang_hover_color',
			array(
				'label'     => __( 'Text on hover', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-lang-hover-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'lang_radius',
			array(
				'label'     => __( 'Corner radius', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'   => array( 'size' => 20 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-lang-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_responsive_control(
			'lang_padding',
			array(
				'label'      => __( 'Padding', 'km-catalog-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'top'      => 7,
					'right'    => 18,
					'bottom'   => 7,
					'left'     => 18,
					'unit'     => 'px',
					'isLinked' => false,
				),
				'selectors'  => array(
					'{{WRAPPER}} .kmcb-nav-lang' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/* ------------------------------------------------------------ Mobile */

		$this->start_controls_section(
			'style_nav_mobile',
			array(
				'label' => __( 'Mobile', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'mobile_breakpoint',
			array(
				'label'       => __( 'Collapse below', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array( 'px' => array( 'min' => 480, 'max' => 1400 ) ),
				'default'     => array(
					'unit' => 'px',
					'size' => 1024,
				),
				'selectors'   => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-break: {{SIZE}}px;' ),
				'description' => __( 'Narrower than this, the menu folds into a button.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'toggle_color',
			array(
				'label'     => __( 'Menu button', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-nav' => '--kmcb-nav-toggle: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * The logo, as markup.
	 */
	private function logo( $settings, $transparent = false ) {
		$source = isset( $settings['logo_source'] ) ? $settings['logo_source'] : 'site';

		if ( 'none' === $source ) {
			return '';
		}

		$home = home_url( '/' );

		// A light logo, when one is supplied, only for the transparent state.
		if ( $transparent && ! empty( $settings['logo_image_transparent']['url'] ) ) {
			return '<a class="kmcb-nav-logo" href="' . esc_url( $home ) . '"><img src="'
				. esc_url( $settings['logo_image_transparent']['url'] ) . '" alt="'
				. esc_attr( get_bloginfo( 'name' ) ) . '" /></a>';
		}

		if ( 'custom' === $source ) {
			$url = isset( $settings['logo_image']['url'] ) ? $settings['logo_image']['url'] : '';

			if ( ! $url ) {
				return '';
			}

			return '<a class="kmcb-nav-logo" href="' . esc_url( $home ) . '">'
				. '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" /></a>';
		}

		$logo_id = (int) get_theme_mod( 'custom_logo' );

		if ( ! $logo_id ) {
			// No logo set in the Customizer: fall back to the site name so the
			// bar never renders with a hole in it.
			return '<a class="kmcb-nav-logo kmcb-nav-logo--text" href="' . esc_url( $home ) . '">'
				. esc_html( get_bloginfo( 'name' ) ) . '</a>';
		}

		$img = wp_get_attachment_image( $logo_id, 'full', false, array( 'class' => 'kmcb-nav-logo-img' ) );

		return '<a class="kmcb-nav-logo" href="' . esc_url( $home ) . '">' . $img . '</a>';
	}

	/**
	 * A menu, from a named menu or a location. A named menu wins.
	 *
	 * @param string $slug     Menu slug, or ''.
	 * @param string $location Theme location, or ''.
	 * @param string $class    Class for the <ul>.
	 */
	private function nav_menu( $slug, $location, $class ) {
		$args = array(
			'container'   => false,
			'menu_class'  => $class,
			'depth'       => 2,
			'echo'        => false,
			'fallback_cb' => '__return_empty_string',
			'items_wrap'  => '<ul id="%1$s" class="%2$s">%3$s</ul>',
		);

		if ( ! empty( $slug ) ) {
			$args['menu'] = $slug;
		} elseif ( ! empty( $location ) ) {
			$args['theme_location'] = $location;
		} else {
			return '';
		}

		return (string) wp_nav_menu( $args );
	}

	/**
	 * The main menu, from a location or a named menu.
	 */
	private function menu( $settings ) {
		return $this->nav_menu(
			isset( $settings['menu_slug'] ) ? $settings['menu_slug'] : '',
			isset( $settings['menu_location'] ) ? $settings['menu_location'] : '',
			'kmcb-nav-list'
		);
	}

	/**
	 * The language control: either a single button, or a menu.
	 *
	 * The menu route exists so a translation plugin's own switcher can be
	 * dropped into a menu and appear here. It goes through wp_nav_menu like
	 * the main menu does, so whatever the plugin adds to that menu — its own
	 * markup, classes and hooks — arrives intact and keeps working.
	 */
	private function lang( $settings ) {
		if ( 'yes' !== ( isset( $settings['show_lang'] ) ? $settings['show_lang'] : '' ) ) {
			return '';
		}

		$source = isset( $settings['lang_source'] ) ? $settings['lang_source'] : 'text';

		if ( 'menu' === $source ) {
			$menu = $this->nav_menu(
				isset( $settings['lang_menu_slug'] ) ? $settings['lang_menu_slug'] : '',
				isset( $settings['lang_menu_location'] ) ? $settings['lang_menu_location'] : '',
				'kmcb-nav-lang-list'
			);

			return '' === $menu ? '' : '<div class="kmcb-nav-lang-menu">' . $menu . '</div>';
		}

		if ( empty( $settings['lang_text'] ) ) {
			return '';
		}

		$url      = isset( $settings['lang_link']['url'] ) ? $settings['lang_link']['url'] : '';
		$external = ! empty( $settings['lang_link']['is_external'] );

		return sprintf(
			'<a class="kmcb-nav-lang" href="%1$s"%2$s>%3$s</a>',
			$url ? esc_url( $url ) : '#',
			$external ? ' target="_blank" rel="noopener"' : '',
			esc_html( $settings['lang_text'] )
		);
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$mode = isset( $settings['transparent_mode'] ) ? $settings['transparent_mode'] : 'auto';

		if ( 'always' === $mode ) {
			$transparent = true;
		} elseif ( 'never' === $mode ) {
			$transparent = false;
		} else {
			$transparent = class_exists( 'KMCB_Page_Meta' ) && KMCB_Page_Meta::is_transparent();
		}

		$clear = $transparent && 'yes' === ( isset( $settings['clear_bar'] ) ? $settings['clear_bar'] : '' );

		$logo = $this->logo( $settings, $clear );
		$menu = $this->menu( $settings );

		$lang = $this->lang( $settings );

		if ( '' === $logo && '' === $menu && '' === $lang ) {
			return;
		}

		$id = 'kmcb-nav-' . $this->get_id();

		$classes = array( 'kmcb-nav' );

		if ( $transparent ) {
			// The bar is taken out of the flow, but Elementor's own widget
			// wrapper still occupies a row and keeps its spacing, which shows
			// as a band above the section below. Tag the wrapper so it can be
			// collapsed, rather than relying on :has() in the stylesheet.
			$this->add_render_attribute( '_wrapper', 'class', 'kmcb-nav-host' );
		}

		if ( $transparent ) {
			// Floating is the common case: the bar keeps its colour and simply
			// sits over whatever section follows.
			$classes[] = 'kmcb-nav--overlay';
		}

		if ( $clear ) {
			$classes[] = 'kmcb-nav--transparent';

			if ( 'yes' !== ( isset( $settings['t_shadow'] ) ? $settings['t_shadow'] : '' ) ) {
				$classes[] = 'kmcb-nav--flat';
			}
		}
		?>
		<nav class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-kmcb-nav>
			<div class="kmcb-nav-inner">
				<?php echo $logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in logo(). ?>

				<button type="button"
					class="kmcb-nav-toggle"
					aria-expanded="false"
					aria-controls="<?php echo esc_attr( $id ); ?>"
					aria-label="<?php esc_attr_e( 'Menu', 'km-catalog-builder' ); ?>"
					data-kmcb-nav-toggle>
					<span></span><span></span><span></span>
				</button>

				<div class="kmcb-nav-panel" id="<?php echo esc_attr( $id ); ?>">
					<?php
					echo $menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output.
					echo $lang; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in lang().
					?>
				</div>
			</div>
		</nav>
		<?php
	}
}
