<?php
/**
 * Markup rendering for the grid, toolbar, facet sidebar and cards.
 *
 * Every template can be overridden from a theme by copying the file into
 * `your-theme/km-catalog-builder/<file>.php`.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

class KMCB_Render {

	/**
	 * URL query prefix, so several widgets can share a page without clashing
	 * with other query vars.
	 */
	const PREFIX = 'kmcb_';

	/**
	 * Load a template, allowing theme overrides.
	 *
	 * @param string $name Template file name without extension.
	 * @param array  $vars Variables exposed to the template.
	 */
	public static function template( $name, array $vars = array() ) {
		$override = locate_template( array( 'km-catalog-builder/' . $name . '.php' ) );
		$file     = $override ? $override : KMCB_PATH . 'templates/' . $name . '.php';

		/** Filter the template path used for a given part. */
		$file = apply_filters( 'kmcb_template', $file, $name, $vars );

		if ( ! file_exists( $file ) ) {
			return '';
		}

		ob_start();
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- template scope.
		extract( $vars, EXTR_SKIP );
		include $file;
		return ob_get_clean();
	}

	/**
	 * Read the filter state from the URL (first widget on the page only).
	 */
	public static function state_from_request( array $config ) {
		static $claimed = false;

		if ( empty( $config['url_sync'] ) || $claimed ) {
			return KMCB_Query::sanitize_state( array(), $config );
		}

		$claimed = true;

		$raw = array();
		foreach ( array( 'f', 'tab', 's', 'orderby', 'view', 'paged' ) as $key ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display state.
			if ( isset( $_GET[ self::PREFIX . $key ] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput -- sanitized in sanitize_state().
				$raw[ $key ] = wp_unslash( $_GET[ self::PREFIX . $key ] );
			}
		}

		return KMCB_Query::sanitize_state( $raw, $config );
	}

	/**
	 * Build the query string that represents a state (for history.pushState and
	 * for no-JS form submissions).
	 */
	public static function state_to_query( array $config, array $state ) {
		$params = array();

		foreach ( $state['f'] as $taxonomy => $slugs ) {
			if ( ! empty( $slugs ) ) {
				$params[ self::PREFIX . 'f' ][ $taxonomy ] = $slugs;
			}
		}

		if ( ! empty( $state['tab'] ) ) {
			$params[ self::PREFIX . 'tab' ] = $state['tab'];
		}
		if ( '' !== $state['s'] ) {
			$params[ self::PREFIX . 's' ] = $state['s'];
		}
		if ( $state['orderby'] !== $config['orderby'] ) {
			$params[ self::PREFIX . 'orderby' ] = $state['orderby'];
		}
		if ( $state['view'] !== $config['default_view'] ) {
			$params[ self::PREFIX . 'view' ] = $state['view'];
		}
		// Only numbered pagination belongs in the URL. With a load-more button,
		// reloading page 3 would show that slice alone instead of everything
		// loaded so far.
		if ( $state['paged'] > 1 && 'numbers' === $config['pagination'] ) {
			$params[ self::PREFIX . 'paged' ] = $state['paged'];
		}

		return http_build_query( $params );
	}

	/**
	 * Current page URL with every one of our own query vars stripped.
	 */
	public static function base_url() {
		$url = home_url( add_query_arg( array() ) );

		return remove_query_arg(
			array(
				self::PREFIX . 'f',
				self::PREFIX . 'tab',
				self::PREFIX . 's',
				self::PREFIX . 'orderby',
				self::PREFIX . 'view',
				self::PREFIX . 'paged',
			),
			$url
		);
	}

	/**
	 * A full URL representing a state — used for no-JS navigation and as the
	 * href behind tabs and pagination links.
	 */
	public static function url_for_state( array $config, array $state ) {
		$query = self::state_to_query( $config, $state );
		$base  = self::base_url();

		if ( '' === $query ) {
			return $base;
		}

		return $base . ( false === strpos( $base, '?' ) ? '?' : '&' ) . $query;
	}

	/**
	 * Clone a state with one key replaced.
	 */
	public static function with( array $state, $key, $value ) {
		$state[ $key ]   = $value;
		$state['paged']  = 1;
		$state['append'] = false;

		return $state;
	}

	/**
	 * Toggle one term inside a facet group and return the resulting state.
	 */
	public static function toggled( array $state, $taxonomy, $slug, $single = false ) {
		$current = isset( $state['f'][ $taxonomy ] ) ? $state['f'][ $taxonomy ] : array();

		if ( in_array( $slug, $current, true ) ) {
			$current = array_values( array_diff( $current, array( $slug ) ) );
		} elseif ( $single ) {
			$current = array( $slug );
		} else {
			$current[] = $slug;
		}

		if ( empty( $current ) ) {
			unset( $state['f'][ $taxonomy ] );
		} else {
			$state['f'][ $taxonomy ] = $current;
		}

		$state['paged'] = 1;

		return $state;
	}

	/**
	 * Whether any user-controlled filter is active.
	 */
	public static function has_active_filters( array $state ) {
		return ! empty( $state['f'] ) || ! empty( $state['tab'] ) || '' !== $state['s'];
	}

	/**
	 * The whole widget.
	 */
	public static function widget( array $config, array &$state ) {
		$query = KMCB_Query::run( $config, $state );

		return self::template(
			'wrapper',
			array(
				'config' => $config,
				'state'  => $state,
				'query'  => $query,
			)
		);
	}

	public static function toolbar( array $config, array $state, WP_Query $query ) {
		return self::template(
			'toolbar',
			array(
				'config' => $config,
				'state'  => $state,
				'query'  => $query,
			)
		);
	}

	public static function sidebar( array $config, array $state ) {
		return self::template(
			'sidebar',
			array(
				'config' => $config,
				'state'  => $state,
			)
		);
	}

	public static function grid( array $config, array $state, WP_Query $query ) {
		return self::template(
			'grid',
			array(
				'config' => $config,
				'state'  => $state,
				'query'  => $query,
			)
		);
	}

	public static function cards( array $config, array $state, WP_Query $query ) {
		$html  = '';
		$index = ( max( 1, $state['paged'] ) - 1 ) * $config['per_page'];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$product = wc_get_product( get_the_ID() );

				if ( ! $product ) {
					continue;
				}

				$html .= self::template(
					'card',
					array(
						'config'  => $config,
						'state'   => $state,
						'product' => $product,
						'index'   => $index,
					)
				);

				$index++;
			}
		}

		wp_reset_postdata();

		return $html;
	}

	/**
	 * Result count string, e.g. "7 Products".
	 */
	public static function count_label( array $config, WP_Query $query ) {
		$total  = (int) $query->found_posts;
		$single = $config['count_single'] ? $config['count_single'] : __( 'Product', 'km-catalog-builder' );
		$plural = $config['count_plural'] ? $config['count_plural'] : __( 'Products', 'km-catalog-builder' );

		return $total . ' ' . ( 1 === $total ? $single : $plural );
	}

	/**
	 * Whether the why-is-this-empty panel is shown at all.
	 *
	 * Off. It was written for building the shop, and on a live one the people
	 * running it are administrators too, so they would be the ones reading it.
	 * Add ?kmcb_debug=1 to the address to see it for one page load, or turn it
	 * on for good with the kmcb_show_diagnostics filter.
	 */
	public static function show_diagnostics() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reading a switch, changing nothing.
		if ( isset( $_GET['kmcb_debug'] ) ) {
			return '0' !== (string) $_GET['kmcb_debug']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Filter whether the administrator-only grid panel is rendered.
		 *
		 * @param bool $show Off by default.
		 */
		return (bool) apply_filters( 'kmcb_show_diagnostics', false );
	}

	/**
	 * Why-is-this-empty panel, for administrators, when the grid returns
	 * nothing and the panel is switched on. Never rendered for visitors.
	 */
	public static function diagnostics( array $config, array $state, WP_Query $query ) {
		if ( ! current_user_can( 'manage_options' ) || ! self::show_diagnostics() ) {
			return '';
		}

		$counts    = wp_count_posts( 'product' );
		$published = isset( $counts->publish ) ? (int) $counts->publish : 0;

		// The same query with every clause of ours removed, to isolate the cause.
		$bare = new WP_Query(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		$rows = array(
			__( 'Published products in WooCommerce', 'km-catalog-builder' ) => $published,
			__( 'Found by a plain product query', 'km-catalog-builder' )    => (int) $bare->found_posts,
			__( 'Found by this widget', 'km-catalog-builder' )              => (int) $query->found_posts,
			__( 'Limited to categories', 'km-catalog-builder' )             => $config['source_cats'] ? implode( ', ', $config['source_cats'] ) : __( 'no', 'km-catalog-builder' ),
			__( 'Active tab', 'km-catalog-builder' )                        => $state['tab'] ? $state['tab'] : __( 'none', 'km-catalog-builder' ),
			__( 'Active filters', 'km-catalog-builder' )                    => $state['f'] ? wp_json_encode( $state['f'] ) : __( 'none', 'km-catalog-builder' ),
			__( 'Search term', 'km-catalog-builder' )                       => '' !== $state['s'] ? $state['s'] : __( 'none', 'km-catalog-builder' ),
			__( 'Sorting', 'km-catalog-builder' )                           => $state['orderby'],
			__( 'Page', 'km-catalog-builder' )                              => $state['paged'] . ' (' . $config['per_page'] . ' ' . __( 'per page', 'km-catalog-builder' ) . ')',
		);

		// WP_Tax_Query rewrites a clause it cannot resolve into "0 = 1", which
		// silently empties the query. Call that out rather than leaving it to be
		// spotted in the SQL.
		if ( false !== strpos( (string) $query->request, '0 = 1' ) ) {
			$rows[ __( 'Problem', 'km-catalog-builder' ) ] = __( 'A filter clause referenced a term that does not exist, so WordPress neutralised the query (0 = 1).', 'km-catalog-builder' );
		}

		wp_reset_postdata();

		$html  = '<div class="kmcb-debug"><p class="kmcb-debug-title">' . esc_html__( 'Products Grid — only administrators can see this', 'km-catalog-builder' ) . '</p><table>';

		foreach ( $rows as $label => $value ) {
			$html .= '<tr><th>' . esc_html( $label ) . '</th><td>' . esc_html( (string) $value ) . '</td></tr>';
		}

		$html .= '</table>';
		$html .= '<p class="kmcb-debug-title">' . esc_html__( 'Query', 'km-catalog-builder' ) . '</p>';
		$html .= '<pre>' . esc_html( (string) $query->request ) . '</pre>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Inline custom properties carrying a card's colours.
	 *
	 * The colours are handed to CSS as variables rather than a finished
	 * background value, because the wave style needs them as two separate
	 * layers rather than one gradient.
	 *
	 * @return string e.g. "--kmcb-card-bg:#FBE3C7;--kmcb-card-bg-2:#F6D3AE;"
	 */
	public static function card_style_attr( array $config, $product ) {
		$colors = self::card_colors( $config, $product );

		$from = self::safe_color( $colors['from'] );
		$to   = self::safe_color( $colors['to'] );

		$style = '';

		if ( '' !== $from ) {
			$style .= '--kmcb-card-bg:' . $from . ';';
		}

		if ( '' !== $to && $to !== $from ) {
			$style .= '--kmcb-card-bg-2:' . $to . ';';
		}

		return $style;
	}

	/**
	 * Does this card have a distinct second colour to render?
	 */
	public static function card_has_two_colors( array $config, $product ) {
		$colors = self::card_colors( $config, $product );

		$from = self::safe_color( $colors['from'] );
		$to   = self::safe_color( $colors['to'] );

		return '' !== $from && '' !== $to && $to !== $from;
	}

	/**
	 * Classes for the card's image box.
	 */
	public static function card_media_class( array $config, $product ) {
		$classes = array( 'kmcb-card-media' );

		if ( self::card_has_two_colors( $config, $product ) ) {
			$classes[] = 'kmcb-card-media--' . $config['card_bg_style'];
		}

		return implode( ' ', $classes );
	}

	/**
	 * Resolve a card's two colours.
	 *
	 * Precedence: the colours set on the product, then those set on its term,
	 * then the widget palette. The product always wins, so a colour chosen on
	 * the product page is never overridden.
	 *
	 * @return array{from:string,to:string}
	 */
	public static function card_colors( array $config, $product ) {
		$own = self::product_colors( $product, ! empty( $config['eyebrow_tax'] ) ? $config['eyebrow_tax'] : 'product_cat' );

		if ( '' !== $own['from'] ) {
			return $own;
		}

		$palette = self::palette_pairs( $config );

		if ( ! empty( $palette ) ) {
			// Keyed on the product id, not the grid position: keying on position
			// meant a product changed colour whenever the sorting, filtering or
			// page changed, which reads as random.
			return $palette[ $product_id % count( $palette ) ];
		}

		return array(
			'from' => '',
			'to'   => '',
		);
	}

	/**
	 * The two colours belonging to a product itself: set on the product, else
	 * inherited from one of its terms. No palette fallback — callers decide
	 * what to do when a product has no colours of its own.
	 *
	 * @return array{from:string,to:string}
	 */
	public static function product_colors( $product, $taxonomy = 'product_cat' ) {
		$product_id = $product->get_id();

		$from = (string) get_post_meta( $product_id, KMCB_Product_Meta::PRODUCT_META, true );

		if ( '' !== $from ) {
			return array(
				'from' => $from,
				'to'   => (string) get_post_meta( $product_id, KMCB_Product_Meta::PRODUCT_META_2, true ),
			);
		}

		$terms = get_the_terms( $product_id, $taxonomy ? $taxonomy : 'product_cat' );

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$term_from = (string) get_term_meta( $term->term_id, KMCB_Product_Meta::TERM_META, true );

				if ( '' !== $term_from ) {
					return array(
						'from' => $term_from,
						'to'   => (string) get_term_meta( $term->term_id, KMCB_Product_Meta::TERM_META_2, true ),
					);
				}
			}
		}

		return array(
			'from' => '',
			'to'   => '',
		);
	}

	/**
	 * Featured image first, then the gallery, de-duplicated.
	 *
	 * @return int[] Attachment ids.
	 */
	public static function product_images( $product ) {
		$ids = array();

		$featured = (int) $product->get_image_id();

		if ( $featured ) {
			$ids[] = $featured;
		}

		foreach ( (array) $product->get_gallery_image_ids() as $gallery_id ) {
			$gallery_id = (int) $gallery_id;

			if ( $gallery_id && ! in_array( $gallery_id, $ids, true ) ) {
				$ids[] = $gallery_id;
			}
		}

		return $ids;
	}

	/**
	 * Split a block of HTML into panels on its headings.
	 *
	 * WooCommerce stores the whole product description as one blob, so the
	 * accordion reads it back by cutting it at each heading. Content before the
	 * first heading is dropped: it is the wrapper markup, not a section.
	 *
	 * @param string $html Description HTML.
	 * @param string $tag  Heading tag to split on, e.g. 'h2'.
	 * @return array<int, array{title:string,content:string}>
	 */
	public static function split_sections( $html, $tag = 'h2' ) {
		$tag = preg_match( '/^h[1-6]$/i', $tag ) ? strtolower( $tag ) : 'h2';

		$parts = preg_split(
			'#<' . $tag . '\b[^>]*>(.*?)</' . $tag . '>#is',
			(string) $html,
			-1,
			PREG_SPLIT_DELIM_CAPTURE
		);

		if ( ! is_array( $parts ) || count( $parts ) < 3 ) {
			return array();
		}

		$panels = array();

		for ( $i = 1; $i < count( $parts ); $i += 2 ) {
			$title = trim( wp_strip_all_tags( $parts[ $i ] ) );
			$body  = isset( $parts[ $i + 1 ] ) ? trim( $parts[ $i + 1 ] ) : '';

			if ( '' === $title && '' === $body ) {
				continue;
			}

			$panels[] = array(
				'title' => $title,
				// Cutting mid-document leaves the wrapper's closing tag on the
				// last section; balance each panel so it cannot break the page.
				'content' => force_balance_tags( $body ),
			);
		}

		return $panels;
	}

	/**
	 * Panels for the accordion widget, after skipping and renaming.
	 */
	public static function accordion_panels( array $config, $product = null ) {
		if ( 'custom' === $config['source'] ) {
			return $config['panels'];
		}

		// The three fields edited on the product screen. A section with no
		// content simply has no panel.
		if ( 'fields' === $config['source'] ) {
			$titles = array(
				KMCB_Product_Meta::SECTION_INFO        => $config['title_info'],
				KMCB_Product_Meta::SECTION_INGREDIENTS => $config['title_ingredients'],
				KMCB_Product_Meta::SECTION_FEEDING     => $config['title_feeding'],
			);

			$out = array();

			foreach ( $titles as $meta_key => $title ) {
				$content = (string) get_post_meta( $product->get_id(), $meta_key, true );

				if ( '' === trim( wp_strip_all_tags( $content ) ) ) {
					continue;
				}

				$out[] = array(
					'title'   => $title,
					'content' => wpautop( $content ),
				);
			}

			return $out;
		}

		$panels = self::split_sections( $product->get_description(), $config['heading_tag'] );

		$skip   = array_filter( array_map( 'trim', (array) $config['skip'] ) );
		$rename = (array) $config['rename'];

		$out = array();

		foreach ( $panels as $panel ) {
			$skip_this = false;

			foreach ( $skip as $needle ) {
				if ( 0 === strcasecmp( $needle, $panel['title'] ) ) {
					$skip_this = true;
					break;
				}
			}

			if ( $skip_this ) {
				continue;
			}

			foreach ( $rename as $from => $to ) {
				if ( 0 === strcasecmp( $from, $panel['title'] ) && '' !== $to ) {
					$panel['title'] = $to;
					break;
				}
			}

			$out[] = $panel;
		}

		return $out;
	}

	public static function accordion( array $config, $product = null ) {
		return self::template(
			'accordion',
			array(
				'config'  => $config,
				'product' => $product,
				'panels'  => self::accordion_panels( $config, $product ),
				'note'    => self::accordion_note( $config, $product ),
			)
		);
	}

	/**
	 * The line printed under the whole accordion, from the product's own
	 * "Note under the accordion" field. Empty for a product without one.
	 */
	public static function accordion_note( array $config, $product = null ) {
		if ( empty( $config['show_note'] ) || ! $product ) {
			return '';
		}

		$note = (string) get_post_meta( $product->get_id(), KMCB_Product_Meta::PRODUCT_NOTE, true );

		return ( '' === trim( wp_strip_all_tags( $note ) ) ) ? '' : $note;
	}

	/**
	 * Render the single-product gallery.
	 */
	public static function gallery( array $config, $product ) {
		return self::template(
			'gallery',
			array(
				'config'  => $config,
				'product' => $product,
				'images'  => self::product_images( $product ),
			)
		);
	}

	/**
	 * Normalise the widget palette into from/to pairs, tolerating the flat list
	 * of single colours saved by earlier versions.
	 *
	 * @return array<int, array{from:string,to:string}>
	 */
	public static function palette_pairs( array $config ) {
		$pairs = array();

		foreach ( (array) $config['palette'] as $entry ) {
			if ( is_array( $entry ) ) {
				$from = isset( $entry['from'] ) ? (string) $entry['from'] : '';
				$to   = isset( $entry['to'] ) ? (string) $entry['to'] : '';
			} else {
				$from = (string) $entry;
				$to   = '';
			}

			if ( '' !== $from ) {
				$pairs[] = array(
					'from' => $from,
					'to'   => $to,
				);
			}
		}

		return $pairs;
	}

	/**
	 * Colours end up in an inline `style` attribute, so allow only the
	 * characters a colour value can legitimately contain.
	 */
	public static function safe_color( $value ) {
		$value = preg_replace( '/[^a-zA-Z0-9#(),.%\s\/-]/', '', (string) $value );

		return trim( $value );
	}

	/**
	 * Build the line of taxonomy terms shown above a product title,
	 * e.g. "Dry Food - Adult Cat".
	 *
	 * @param array      $config  Rows, separator, term limit, linking.
	 * @param WC_Product $product
	 * @return string HTML, already escaped.
	 */
	public static function term_line( array $config, $product ) {
		$product_id = $product->get_id();
		$parts      = array();

		foreach ( $config['rows'] as $row ) {
			$taxonomy = isset( $row['taxonomy'] ) ? $row['taxonomy'] : '';

			if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			$terms = get_the_terms( $product_id, $taxonomy );

			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				continue;
			}

			// Prefer the most specific term, so a child category wins over its
			// parent when only one is shown.
			usort(
				$terms,
				function ( $a, $b ) {
					return $b->parent <=> $a->parent;
				}
			);

			if ( 'first' === $config['terms'] ) {
				$terms = array_slice( $terms, 0, 1 );
			}

			$names = array();

			foreach ( $terms as $term ) {
				$name = esc_html( $term->name );

				if ( $config['link'] ) {
					$link = get_term_link( $term );

					if ( ! is_wp_error( $link ) ) {
						$name = '<a href="' . esc_url( $link ) . '">' . $name . '</a>';
					}
				}

				$names[] = $name;
			}

			$prefix = isset( $row['prefix'] ) ? esc_html( $row['prefix'] ) : '';
			$suffix = isset( $row['suffix'] ) ? esc_html( $row['suffix'] ) : '';

			$parts[] = $prefix . implode( ', ', $names ) . $suffix;
		}

		if ( empty( $parts ) ) {
			return '';
		}

		return implode( esc_html( $config['separator'] ), $parts );
	}

	/**
	 * First term name of a taxonomy for a product (badge / eyebrow text).
	 */
	public static function first_term_name( $product_id, $taxonomy ) {
		if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
			return '';
		}

		$terms = get_the_terms( $product_id, $taxonomy );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return '';
		}

		// Prefer the deepest term so "Dry Cat Food" wins over a parent "Cat Food".
		usort(
			$terms,
			function ( $a, $b ) {
				return $b->parent <=> $a->parent;
			}
		);

		return $terms[0]->name;
	}
}
