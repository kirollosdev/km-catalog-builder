<?php
/**
 * The Product Template post type.
 *
 * Templates are ordinary posts on purpose. That is what lets Polylang list
 * them with its language column and its + button, so an Arabic template is
 * made the same way as an Arabic page, and each language can have a layout
 * of its own.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

class KMCB_Template_Post_Type {

	const SLUG = 'kmcb_product_tpl';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );

		// Elementor only offers "Edit with Elementor" for post types switched on
		// under Elementor > Settings. Rather than ask for that, the option is
		// answered with this post type already in it.
		add_filter( 'option_elementor_cpt_support', array( __CLASS__, 'elementor_support' ) );
		add_filter( 'default_option_elementor_cpt_support', array( __CLASS__, 'elementor_support' ) );

		// Translatable from the start, and kept out of Polylang's settings
		// screen: a product template that cannot be translated has no use here.
		add_filter( 'pll_get_post_types', array( __CLASS__, 'polylang' ), 10, 2 );

		add_filter( 'manage_' . self::SLUG . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::SLUG . '_posts_custom_column', array( __CLASS__, 'column' ), 10, 2 );
	}

	public static function register() {
		$labels = array(
			'name'               => __( 'Product Templates', 'km-catalog-builder' ),
			'singular_name'      => __( 'Product Template', 'km-catalog-builder' ),
			'add_new'            => __( 'Add New', 'km-catalog-builder' ),
			'add_new_item'       => __( 'Add New Product Template', 'km-catalog-builder' ),
			'edit_item'          => __( 'Edit Product Template', 'km-catalog-builder' ),
			'new_item'           => __( 'New Product Template', 'km-catalog-builder' ),
			'view_item'          => __( 'View Product Template', 'km-catalog-builder' ),
			'search_items'       => __( 'Search Product Templates', 'km-catalog-builder' ),
			'not_found'          => __( 'No product templates yet.', 'km-catalog-builder' ),
			'not_found_in_trash' => __( 'Nothing in the trash.', 'km-catalog-builder' ),
			'menu_name'          => __( 'Product Templates', 'km-catalog-builder' ),
		);

		register_post_type(
			self::SLUG,
			array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'exclude_from_search' => true,
				'show_ui'            => true,
				'show_in_menu'       => 'woocommerce',
				'show_in_nav_menus'  => false,
				'show_in_rest'       => true,
				'has_archive'        => false,
				'rewrite'            => false,
				'query_var'          => false,
				'hierarchical'       => false,
				'capability_type'    => 'post',
				'menu_icon'          => 'dashicons-layout',
				// The editor is what Elementor replaces itself with, and the
				// title is how the template is picked in the settings.
				'supports'           => array( 'title', 'editor', 'author', 'revisions', 'elementor' ),
			)
		);
	}

	/**
	 * @param mixed $value Stored option, whatever shape it is in.
	 * @return array
	 */
	public static function elementor_support( $value ) {
		$value = is_array( $value ) ? $value : array();

		if ( ! in_array( self::SLUG, $value, true ) ) {
			$value[] = self::SLUG;
		}

		return $value;
	}

	/**
	 * @param array $types      Post types Polylang handles.
	 * @param bool  $is_settings Whether the list is for the settings screen.
	 * @return array
	 */
	public static function polylang( $types, $is_settings ) {
		if ( $is_settings ) {
			// Always on, so it needs no checkbox.
			unset( $types[ self::SLUG ] );
		} else {
			$types[ self::SLUG ] = self::SLUG;
		}

		return $types;
	}

	/**
	 * Mark which template the product pages are actually using, so a list of
	 * several is readable.
	 */
	public static function columns( $columns ) {
		$out = array();

		foreach ( $columns as $key => $label ) {
			$out[ $key ] = $label;

			if ( 'title' === $key ) {
				$out['kmcb_template_in_use'] = __( 'In use', 'km-catalog-builder' );
			}
		}

		return $out;
	}

	public static function column( $column, $post_id ) {
		if ( 'kmcb_template_in_use' !== $column ) {
			return;
		}

		$chosen = (int) get_option( KMCB_Template_Settings::OPT_TEMPLATE );

		if ( $chosen === (int) $post_id ) {
			echo '<span aria-hidden="true">&#10003;</span> ' . esc_html__( 'Product pages', 'km-catalog-builder' );
			return;
		}

		// A translation is used automatically when its language is showing, so
		// it is in use without ever being the one chosen in the settings.
		if ( $chosen && function_exists( 'pll_get_post_translations' ) ) {
			$translations = pll_get_post_translations( $chosen );

			if ( in_array( (int) $post_id, array_map( 'intval', (array) $translations ), true ) ) {
				$lang = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $post_id, 'name' ) : '';

				printf(
					'<span aria-hidden="true">&#10003;</span> %s',
					esc_html( $lang ? sprintf( __( 'Product pages in %s', 'km-catalog-builder' ), $lang ) : __( 'Product pages', 'km-catalog-builder' ) )
				);
				return;
			}
		}

		echo '<span style="color:#a7aaad">&mdash;</span>';
	}
}
