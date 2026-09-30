<?php
/**
 * Query building: turns a sanitized filter state into WP_Query args and runs it.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

class KMCB_Query {

	/**
	 * Sort keys we expose, mapped to WooCommerce catalog ordering values.
	 */
	public static function sort_options() {
		return array(
			'featured'   => array(
				'label' => __( 'Featured', 'km-catalog-builder' ),
				'wc'    => 'menu_order',
			),
			'popularity' => array(
				'label' => __( 'Best Selling', 'km-catalog-builder' ),
				'wc'    => 'popularity',
			),
			'rating'     => array(
				'label' => __( 'Top Rated', 'km-catalog-builder' ),
				'wc'    => 'rating',
			),
			'date'       => array(
				'label' => __( 'Newest', 'km-catalog-builder' ),
				'wc'    => 'date',
			),
			'price'      => array(
				'label' => __( 'Price: Low to High', 'km-catalog-builder' ),
				'wc'    => 'price',
			),
			'price-desc' => array(
				'label' => __( 'Price: High to Low', 'km-catalog-builder' ),
				'wc'    => 'price-desc',
			),
			'title'      => array(
				'label' => __( 'Alphabetical', 'km-catalog-builder' ),
				'wc'    => 'title',
			),
		);
	}

	/**
	 * Sort options with the widget's own wording applied.
	 *
	 * The labels above are only defaults. A grid built for another language
	 * carries its own set, so nothing here has to be translated in code.
	 *
	 * @param array $config Widget config.
	 * @return array
	 */
	public static function sort_options_for( array $config ) {
		$options = self::sort_options();
		$labels  = isset( $config['sort_labels'] ) && is_array( $config['sort_labels'] ) ? $config['sort_labels'] : array();

		foreach ( $options as $key => $option ) {
			if ( ! empty( $labels[ $key ] ) ) {
				$options[ $key ]['label'] = (string) $labels[ $key ];
			}
		}

		return $options;
	}

	/**
	 * Build a safe state array from raw request input.
	 *
	 * @param array $raw    Raw input ($_GET or $_POST slice).
	 * @param array $config Widget config.
	 */
	public static function sanitize_state( array $raw, array $config ) {
		$state = array(
			'f'       => array(),
			'tab'     => '',
			's'       => '',
			'orderby' => $config['orderby'],
			'view'    => $config['default_view'],
			'paged'   => 1,
			'append'  => false,
		);

		$allowed_tax = array();
		foreach ( KMCB_Config::get_filter_groups( $config ) as $group ) {
			$allowed_tax[ $group['taxonomy'] ] = $group;
		}

		if ( ! empty( $raw['f'] ) && is_array( $raw['f'] ) ) {
			foreach ( $raw['f'] as $taxonomy => $slugs ) {
				$taxonomy = sanitize_key( $taxonomy );

				if ( ! isset( $allowed_tax[ $taxonomy ] ) ) {
					continue;
				}

				$slugs = is_array( $slugs ) ? $slugs : explode( ',', (string) $slugs );
				$slugs = array_values( array_filter( array_map( 'sanitize_title', $slugs ) ) );

				if ( empty( $slugs ) ) {
					continue;
				}

				if ( 'single' === $allowed_tax[ $taxonomy ]['type'] ) {
					$slugs = array( $slugs[0] );
				}

				$state['f'][ $taxonomy ] = array_values( array_unique( $slugs ) );
			}
		}

		if ( ! empty( $raw['tab'] ) ) {
			$state['tab'] = sanitize_title( $raw['tab'] );
		}

		if ( isset( $raw['s'] ) ) {
			$state['s'] = sanitize_text_field( wp_unslash( $raw['s'] ) );
		}

		$sort_options = self::sort_options();
		if ( ! empty( $raw['orderby'] ) && isset( $sort_options[ $raw['orderby'] ] ) ) {
			$state['orderby'] = $raw['orderby'];
		}

		if ( ! empty( $raw['view'] ) && in_array( $raw['view'], array( 'grid', 'list' ), true ) ) {
			$state['view'] = $raw['view'];
		}

		if ( ! empty( $raw['paged'] ) ) {
			$state['paged'] = max( 1, absint( $raw['paged'] ) );
		}

		$state['append'] = ! empty( $raw['append'] );

		return $state;
	}

	/**
	 * Run the product query.
	 *
	 * $state is taken by reference so a corrected page number is visible to the
	 * caller, which renders the pagination and the URL from it.
	 *
	 * @return WP_Query
	 */
	public static function run( array $config, array &$state ) {
		$query = self::query( $config, $state );

		// A page past the end of the results renders as "no products match",
		// which is wrong and, because the page number is kept in the URL, it
		// sticks across reloads. This happens with a stale ?kmcb_paged= or when
		// a filter shrinks the result set below the current page. Fall back to
		// the first page instead of showing an empty grid.
		if ( 0 === (int) $query->post_count && $state['paged'] > 1 ) {
			$state['paged'] = 1;
			$query          = self::query( $config, $state );
		}

		return $query;
	}

	private static function query( array $config, array $state ) {
		$args = self::build_args( $config, $state );
		$args = self::apply_ordering( $state['orderby'], $args );

		$query = new WP_Query( $args );

		self::reset_ordering();

		return $query;
	}

	/**
	 * Base query args, before ordering is layered on.
	 */
	public static function build_args( array $config, array $state, $for_counts = false ) {
		$args = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'tax_query'           => self::build_tax_query( $config, $state ),
		);

		if ( '' !== $state['s'] ) {
			$args['s'] = $state['s'];
		}

		if ( $for_counts ) {
			$args['posts_per_page']         = -1;
			$args['fields']                 = 'ids';
			$args['no_found_rows']          = true;
			$args['orderby']                = 'ID';
			$args['cache_results']          = false;
			$args['update_post_meta_cache'] = false;
			$args['update_post_term_cache'] = false;
		} else {
			$args['posts_per_page'] = $config['per_page'];
			$args['paged']          = $state['paged'];
		}

		/**
		 * Filter the product query args used by the product grid.
		 *
		 * @param array $args
		 * @param array $config
		 * @param array $state
		 * @param bool  $for_counts
		 */
		return apply_filters( 'kmcb_query_args', $args, $config, $state, $for_counts );
	}

	/**
	 * @param string $skip_taxonomy Taxonomy to leave out (used when counting facets).
	 */
	private static function build_tax_query( array $config, array $state, $skip_taxonomy = '' ) {
		$tax_query = array( 'relation' => 'AND' );

		// Catalog visibility, resolved through WooCommerce's own term ids.
		//
		// This must never be built from term names or slugs: when WP_Tax_Query
		// cannot resolve a term it does not drop the clause, it injects
		// "0 = 1" and the whole query returns nothing. Looking the ids up and
		// skipping anything missing keeps the query safe on stores where these
		// terms were renamed or never created.
		$excluded = array();

		if ( function_exists( 'wc_get_product_visibility_term_ids' ) ) {
			$visibility = wc_get_product_visibility_term_ids();

			if ( ! empty( $visibility['exclude-from-catalog'] ) ) {
				$excluded[] = (int) $visibility['exclude-from-catalog'];
			}

			if ( ! empty( $config['hide_out_of_stock'] ) && ! empty( $visibility['outofstock'] ) ) {
				$excluded[] = (int) $visibility['outofstock'];
			}
		}

		if ( ! empty( $excluded ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => $excluded,
				'operator' => 'NOT IN',
			);
		}

		// Widget source categories. Empty slugs are stripped rather than passed
		// through: an unresolvable term makes WP_Tax_Query emit "0 = 1".
		$source_cats = KMCB_Config::clean_slugs( $config['source_cats'] );

		if ( ! empty( $source_cats ) ) {
			$tax_query[] = array(
				'taxonomy'         => 'product_cat',
				'field'            => 'slug',
				'terms'            => $source_cats,
				'include_children' => true,
			);
		}

		// Active tab.
		if ( ! empty( $config['tabs_enabled'] ) && ! empty( $state['tab'] ) && taxonomy_exists( $config['tabs_tax'] ) && $config['tabs_tax'] !== $skip_taxonomy ) {
			$tax_query[] = array(
				'taxonomy'         => $config['tabs_tax'],
				'field'            => 'slug',
				'terms'            => array( $state['tab'] ),
				'include_children' => true,
			);
		}

		// Sidebar facets.
		foreach ( $state['f'] as $taxonomy => $slugs ) {
			$slugs = KMCB_Config::clean_slugs( $slugs );

			if ( $taxonomy === $skip_taxonomy || empty( $slugs ) || ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			$tax_query[] = array(
				'taxonomy'         => $taxonomy,
				'field'            => 'slug',
				'terms'            => $slugs,
				'operator'         => 'IN',
				'include_children' => true,
			);
		}

		return $tax_query;
	}

	/**
	 * Hand ordering over to WooCommerce so price/popularity/rating sorting
	 * uses the product lookup tables rather than a hand-rolled meta query.
	 */
	private static function apply_ordering( $orderby, array $args ) {
		$options = self::sort_options();

		if ( ! isset( $options[ $orderby ] ) ) {
			$orderby = 'featured';
		}

		$wc_orderby = $options[ $orderby ]['wc'];
		$order      = '';

		if ( 'price-desc' === $wc_orderby ) {
			$wc_orderby = 'price';
			$order      = 'DESC';
		}

		if ( function_exists( 'WC' ) && WC()->query && method_exists( WC()->query, 'get_catalog_ordering_args' ) ) {
			$ordering_args = WC()->query->get_catalog_ordering_args( $wc_orderby, $order );

			foreach ( $ordering_args as $key => $value ) {
				if ( '' !== $value && null !== $value ) {
					$args[ $key ] = $value;
				}
			}
		} else {
			$args['orderby'] = 'menu_order title';
			$args['order']   = 'ASC';
		}

		return $args;
	}

	/**
	 * WooCommerce hooks posts_clauses filters while ordering; drop them again so
	 * later queries in the same request are unaffected.
	 */
	private static function reset_ordering() {
		if ( function_exists( 'WC' ) && WC()->query && method_exists( WC()->query, 'remove_ordering_args' ) ) {
			WC()->query->remove_ordering_args();
		}
	}

	/**
	 * Product counts per term for one facet group, honouring every *other*
	 * active filter (standard facet behaviour).
	 *
	 * @return array slug => count
	 */
	public static function term_counts( array $config, array $state, $taxonomy ) {
		static $cache = array();

		$key = md5( (string) wp_json_encode( array( $config['widget_id'], $config['source_cats'], $state['f'], $state['tab'], $state['s'], $taxonomy ) ) );

		if ( isset( $cache[ $key ] ) ) {
			return $cache[ $key ];
		}

		$args              = self::build_args( $config, $state, true );
		$args['tax_query'] = self::build_tax_query( $config, $state, $taxonomy );

		$ids = get_posts( $args );

		$counts = array();

		if ( ! empty( $ids ) && count( $ids ) <= 5000 ) {
			$terms = wp_get_object_terms( $ids, $taxonomy, array( 'fields' => 'all_with_object_id' ) );

			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$counts[ $term->slug ] = isset( $counts[ $term->slug ] ) ? $counts[ $term->slug ] + 1 : 1;
				}
			}
		}

		$cache[ $key ] = $counts;

		return $counts;
	}

	/**
	 * Terms to display for a facet group, respecting include/exclude config.
	 *
	 * @return WP_Term[]
	 */
	/**
	 * The products this widget can ever show, before the visitor filters
	 * anything. Used to scope the filter options: a cat-only grid must never
	 * offer a flavour that only dog products carry.
	 *
	 * @return int[]|null Null when the widget is not limited to any category.
	 */
	public static function scope_ids( array $config ) {
		static $cache = array();

		$cats = KMCB_Config::clean_slugs( isset( $config['source_cats'] ) ? $config['source_cats'] : array() );

		if ( empty( $cats ) ) {
			return null;
		}

		$key = md5( (string) wp_json_encode( $cats ) );

		if ( ! isset( $cache[ $key ] ) ) {
			$cache[ $key ] = get_posts(
				self::build_args(
					$config,
					array(
						'f'       => array(),
						'tab'     => '',
						's'       => '',
						'orderby' => 'featured',
						'view'    => 'grid',
						'paged'   => 1,
						'append'  => false,
					),
					true
				)
			);
		}

		return $cache[ $key ];
	}

	public static function get_group_terms( array $group, array $config = array() ) {
		// No explicit orderby: WooCommerce filters get_terms defaults so each
		// attribute uses the sort order configured under Products > Attributes.
		$args = array(
			'taxonomy'   => $group['taxonomy'],
			'hide_empty' => true,
		);

		// Limit the options to terms the in-scope products actually carry.
		if ( ! empty( $config ) ) {
			$ids = self::scope_ids( $config );

			if ( is_array( $ids ) ) {
				if ( empty( $ids ) ) {
					return array();
				}

				$args['object_ids'] = $ids;
			}
		}

		if ( ! empty( $group['include'] ) ) {
			$args['slug']    = array_map( 'sanitize_title', $group['include'] );
			$args['orderby'] = 'slug__in';
		}

		if ( ! empty( $group['exclude'] ) ) {
			$excluded = get_terms(
				array(
					'taxonomy'   => $group['taxonomy'],
					'slug'       => array_map( 'sanitize_title', $group['exclude'] ),
					'fields'     => 'ids',
					'hide_empty' => false,
				)
			);
			if ( ! is_wp_error( $excluded ) && ! empty( $excluded ) ) {
				$args['exclude'] = $excluded;
			}
		}

		$terms = get_terms( $args );

		return is_wp_error( $terms ) ? array() : $terms;
	}
}
