<?php
/**
 * Widget configuration storage.
 *
 * AJAX requests must never trust settings coming from the browser, so the
 * resolved widget settings are persisted to post meta on render and read back
 * server-side using only the post ID + widget ID from the request.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

class KMCB_Config {

	const META_PREFIX = '_kmcb_cfg_';

	/**
	 * Persist the resolved config for a widget instance (only when it changed).
	 */
	public static function save( $post_id, $widget_id, array $config ) {
		$post_id   = absint( $post_id );
		$widget_id = self::sanitize_widget_id( $widget_id );

		if ( ! $widget_id ) {
			return;
		}

		$key = self::META_PREFIX . $widget_id;

		// Widgets rendered outside a single post (archives, theme parts) fall
		// back to an option so filtering still works there.
		if ( ! $post_id ) {
			$existing = get_option( $key );

			if ( is_array( $existing ) && $existing === $config ) {
				return;
			}

			update_option( $key, $config, false );
			return;
		}

		$existing = get_post_meta( $post_id, $key, true );

		if ( is_array( $existing ) && $existing === $config ) {
			return;
		}

		update_post_meta( $post_id, $key, $config );
	}

	/**
	 * @return array|false
	 */
	public static function get( $post_id, $widget_id ) {
		$post_id   = absint( $post_id );
		$widget_id = self::sanitize_widget_id( $widget_id );

		if ( ! $widget_id ) {
			return false;
		}

		$key = self::META_PREFIX . $widget_id;

		$config = $post_id ? get_post_meta( $post_id, $key, true ) : get_option( $key );

		return is_array( $config ) ? $config : false;
	}

	public static function sanitize_widget_id( $widget_id ) {
		return preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $widget_id );
	}

	/**
	 * Normalise a list of term slugs.
	 *
	 * Elementor hands back array( '' ) from an empty SELECT2, and a stray empty
	 * slug is not harmless: WP_Tax_Query cannot resolve it, so instead of
	 * skipping the clause it emits "0 = 1" and the query returns nothing.
	 * Empty values are dropped here so a clause is never built from them.
	 *
	 * @param array|string $value Array of slugs, or a comma-separated string.
	 * @return string[]
	 */
	public static function clean_slugs( $value ) {
		if ( ! is_array( $value ) ) {
			$value = ( '' === trim( (string) $value ) ) ? array() : explode( ',', (string) $value );
		}

		$slugs = array_map( 'sanitize_title', $value );

		$slugs = array_filter(
			$slugs,
			static function ( $slug ) {
				return '' !== $slug;
			}
		);

		return array_values( array_unique( $slugs ) );
	}

	/**
	 * Defaults for every key the renderer/query relies on.
	 */
	public static function defaults() {
		return array(
			'post_id'            => 0,
			'widget_id'          => '',
			'source_cats'        => array(),
			'per_page'           => 12,
			'columns'            => 3,
			'orderby'            => 'featured',
			'hide_out_of_stock'  => false,
			'tabs_enabled'       => true,
			'tabs_tax'           => 'product_cat',
			'tabs_terms'         => array(),
			'tabs_all_label'     => '',
			'show_search'        => true,
			'search_placeholder' => '',
			'search_position'    => 'start',
			'show_count'         => true,
			'count_single'       => '',
			'count_plural'       => '',
			'show_sort'          => true,
			'sort_label'         => '',
			'sort_labels'        => array(),
			'empty_title'        => '',
			'empty_reset'        => '',
			'filters_btn_label'  => '',
			'apply_label'        => '',
			'show_view_toggle'   => true,
			'default_view'       => 'grid',
			'show_sidebar'       => true,
			'sidebar_title'      => '',
			'clear_label'        => '',
			'filters'            => array(),
			'auto_filters'       => true,
			'filter_counts'      => false,
			'hide_empty_terms'   => false,
			'badge_tax'          => '',
			'eyebrow_tax'        => 'product_cat',
			'button_text'        => '',
			'card_arrow_dir'     => 'right',
			'show_price'         => false,
			'image_size'         => 'woocommerce_thumbnail',
			'palette'            => array(),
			'gradient_angle'     => 160,
			'card_bg_style'      => 'wave',
			'pagination'         => 'load_more',
			'load_more_text'     => '',
			'url_sync'           => true,
		);
	}

	/**
	 * Merge with defaults and coerce types.
	 */
	public static function normalize( array $config ) {
		$config = wp_parse_args( $config, self::defaults() );

		$config['post_id']   = absint( $config['post_id'] );
		$config['widget_id'] = self::sanitize_widget_id( $config['widget_id'] );
		$config['per_page']  = max( 1, absint( $config['per_page'] ) );
		$config['columns']   = min( 6, max( 1, absint( $config['columns'] ) ) );

		foreach ( array( 'sort_label', 'empty_title', 'empty_reset', 'filters_btn_label', 'apply_label' ) as $key ) {
			$config[ $key ] = is_scalar( $config[ $key ] ) ? (string) $config[ $key ] : '';
		}

		// Sort labels are keyed by sort key, and a key we do not offer would
		// never be shown, so unknown ones are dropped rather than carried.
		if ( ! is_array( $config['sort_labels'] ) ) {
			$config['sort_labels'] = array();
		}

		$config['sort_labels'] = array_intersect_key(
			array_map(
				static function ( $label ) {
					return is_scalar( $label ) ? (string) $label : '';
				},
				$config['sort_labels']
			),
			KMCB_Query::sort_options()
		);

		foreach ( array( 'source_cats', 'tabs_terms', 'palette', 'filters' ) as $key ) {
			if ( ! is_array( $config[ $key ] ) ) {
				$config[ $key ] = array();
			}
		}

		$config['source_cats'] = self::clean_slugs( $config['source_cats'] );
		$config['tabs_terms']  = self::clean_slugs( $config['tabs_terms'] );

		foreach ( array( 'hide_out_of_stock', 'tabs_enabled', 'show_search', 'show_count', 'show_sort', 'show_view_toggle', 'show_sidebar', 'auto_filters', 'filter_counts', 'hide_empty_terms', 'show_price', 'url_sync' ) as $key ) {
			$config[ $key ] = (bool) $config[ $key ];
		}

		$config['gradient_angle'] = max( 0, min( 360, (int) $config['gradient_angle'] ) );

		$config['card_bg_style'] = in_array( $config['card_bg_style'], array( 'wave', 'blend', 'flat' ), true )
			? $config['card_bg_style']
			: 'wave';

		$config['card_arrow_dir'] = ( 'left' === $config['card_arrow_dir'] ) ? 'left' : 'right';

		$config['search_position'] = in_array( $config['search_position'], array( 'start', 'center', 'end' ), true ) ? $config['search_position'] : 'start';

		$config['default_view'] = 'list' === $config['default_view'] ? 'list' : 'grid';
		$config['pagination']   = in_array( $config['pagination'], array( 'load_more', 'numbers', 'none' ), true ) ? $config['pagination'] : 'load_more';

		$config['filters'] = self::normalize_filters( $config['filters'] );

		return $config;
	}

	private static function normalize_filters( array $filters ) {
		$out = array();

		foreach ( $filters as $filter ) {
			if ( empty( $filter['taxonomy'] ) || ! taxonomy_exists( $filter['taxonomy'] ) ) {
				continue;
			}

			$out[] = array(
				'taxonomy'    => $filter['taxonomy'],
				'label'       => isset( $filter['label'] ) ? (string) $filter['label'] : '',
				'type'        => ( isset( $filter['type'] ) && 'single' === $filter['type'] ) ? 'single' : 'multi',
				'include'     => isset( $filter['include'] ) ? self::clean_slugs( $filter['include'] ) : array(),
				'exclude'     => isset( $filter['exclude'] ) ? self::clean_slugs( $filter['exclude'] ) : array(),
				'collapsible' => ! isset( $filter['collapsible'] ) || (bool) $filter['collapsible'],
				'open'        => ! isset( $filter['open'] ) || (bool) $filter['open'],
			);
		}

		return $out;
	}

	/**
	 * Filter groups to render — the configured ones, or one per product
	 * attribute when none were configured. A category group can still be added
	 * by hand in the Filters repeater.
	 */
	public static function get_filter_groups( array $config ) {
		if ( ! empty( $config['filters'] ) ) {
			return $config['filters'];
		}

		if ( ! $config['auto_filters'] ) {
			return array();
		}

		// Whatever the tabs already filter by does not need a sidebar group too.
		$skip = ( ! empty( $config['tabs_enabled'] ) && ! empty( $config['tabs_tax'] ) ) ? $config['tabs_tax'] : '';

		$groups = array();

		foreach ( self::auto_taxonomies() as $taxonomy ) {
			if ( $taxonomy === $skip ) {
				continue;
			}

			$groups[] = array(
				'taxonomy'    => $taxonomy,
				'label'       => '',
				'type'        => 'multi',
				'include'     => array(),
				'exclude'     => array(),
				'collapsible' => true,
				'open'        => true,
			);
		}

		return $groups;
	}

	/**
	 * The taxonomies the sidebar builds itself from: product attributes only.
	 *
	 * Categories are deliberately left out. They are the page's own scope (a
	 * cat page, a dog page) or the tabs, so repeating them as a filter offers
	 * the visitor a way to contradict the page they are on.
	 */
	private static function auto_taxonomies() {
		static $cache = null;

		if ( null !== $cache ) {
			return $cache;
		}

		$out = array();

		if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
			foreach ( wc_get_attribute_taxonomies() as $attribute ) {
				$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );

				if ( taxonomy_exists( $taxonomy ) && self::taxonomy_in_use( $taxonomy ) ) {
					$out[] = $taxonomy;
				}
			}
		}

		/**
		 * Filter the taxonomies used to auto-build the filter sidebar.
		 *
		 * @param string[] $out
		 */
		$cache = apply_filters( 'kmcb_auto_filter_taxonomies', $out );

		return $cache;
	}

	/**
	 * Does this taxonomy have at least one term attached to a product?
	 */
	private static function taxonomy_in_use( $taxonomy ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
				'number'     => 1,
				'fields'     => 'ids',
			)
		);

		return ! is_wp_error( $terms ) && ! empty( $terms );
	}
}
