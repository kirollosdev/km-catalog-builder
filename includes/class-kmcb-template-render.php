<?php
/**
 * Putting the template on the product page.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

class KMCB_Template_Render {

	public static function init() {
		if ( ! KMCB_Template_Settings::is_enabled() ) {
			return;
		}

		// Late, so a theme or another plugin choosing a product template has
		// already had its say and this is the deliberate last word.
		add_filter( 'template_include', array( __CLASS__, 'route' ), 99 );

		// Editing the template on its own, there is no product in view, so the
		// widgets would have nothing to read.
		add_action( 'wp', array( __CLASS__, 'preview_product' ), 20 );
	}

	/**
	 * The template to render, for the language being viewed.
	 *
	 * Polylang treats the templates as ordinary posts, so the Arabic layout is
	 * simply the translation of the chosen one and is picked up here without
	 * anything having to be set per language.
	 *
	 * @return int Post ID, or 0 to leave the page alone.
	 */
	public static function template_id() {
		$id = (int) get_option( KMCB_Template_Settings::OPT_TEMPLATE );

		if ( $id && function_exists( 'pll_get_post' ) ) {
			$translated = (int) pll_get_post( $id, self::language() );

			// An unfinished translation falls back to the original rather than
			// leaving the product page blank in that language.
			if ( $translated && 'publish' === get_post_status( $translated ) ) {
				$id = $translated;
			}
		}

		/**
		 * Filter the product template being used.
		 *
		 * @param int $id Post ID, or 0 for none.
		 */
		$id = (int) apply_filters( 'kmcb_template_template_id', $id );

		return self::usable( $id ) ? $id : 0;
	}

	/**
	 * Which language this product page is in.
	 *
	 * The product's own language is asked for first, and the language of the
	 * page being viewed is only the fallback. They are not always the same:
	 * where products themselves are not translated, every product is served
	 * in the site's default language whichever side of the site the visitor
	 * came from, and going by the ambient language alone then puts the same
	 * template on all of them.
	 *
	 * @return string Language slug, or '' to let Polylang decide.
	 */
	public static function language() {
		if ( is_singular( 'product' ) && function_exists( 'pll_get_post_language' ) ) {
			$lang = pll_get_post_language( get_queried_object_id() );

			if ( $lang ) {
				return (string) $lang;
			}
		}

		// A product with no language of its own has a single URL carrying no
		// language either, so Polylang answers with the site default for every
		// visitor. Where the shop has not been translated, following the
		// language the visitor last chose is the only thing left to go on.
		if ( KMCB_Template_Settings::follow_visitor() ) {
			$lang = self::visitor_language();

			if ( $lang ) {
				return $lang;
			}
		}

		if ( function_exists( 'pll_current_language' ) ) {
			$lang = pll_current_language();

			if ( $lang ) {
				return (string) $lang;
			}
		}

		return '';
	}

	/**
	 * The language the visitor last chose, as Polylang recorded it.
	 *
	 * @return string Language slug, or ''.
	 */
	public static function visitor_language() {
		$name = defined( 'PLL_COOKIE' ) ? PLL_COOKIE : 'pll_language';

		if ( empty( $_COOKIE[ $name ] ) ) {
			return '';
		}

		$lang = sanitize_key( wp_unslash( $_COOKIE[ $name ] ) );

		if ( ! $lang || ! function_exists( 'pll_languages_list' ) ) {
			return '';
		}

		// Anything not actually a language on this site is ignored rather than
		// asked for, since the cookie is visitor-supplied.
		return in_array( $lang, (array) pll_languages_list(), true ) ? $lang : '';
	}

	/**
	 * Is Polylang managing products at all?
	 *
	 * When it is not, products have no language and no per-language URL, which
	 * is the single most useful thing to know when the wrong template appears.
	 *
	 * @return bool|null Null when Polylang is not active.
	 */
	public static function products_translatable() {
		if ( ! function_exists( 'pll_is_translated_post_type' ) ) {
			return null;
		}

		return (bool) pll_is_translated_post_type( 'product' );
	}

	/**
	 * A template that was trashed, or an id left behind by something else,
	 * must not take the product page over.
	 */
	private static function usable( $id ) {
		return $id
			&& KMCB_Template_Post_Type::SLUG === get_post_type( $id )
			&& 'publish' === get_post_status( $id );
	}

	/**
	 * Hand single product pages to our own file.
	 */
	public static function route( $template ) {
		if ( ! is_singular( 'product' ) ) {
			return $template;
		}

		if ( ! self::elementor_ready() ) {
			return $template;
		}

		if ( ! self::template_id() ) {
			return $template;
		}

		/**
		 * Filter whether this product page uses the template at all, for
		 * leaving certain products on the theme's own layout.
		 *
		 * @param bool $use
		 * @param int  $product_id
		 */
		if ( ! apply_filters( 'kmcb_template_use_template', true, get_queried_object_id() ) ) {
			return $template;
		}

		return KMCB_PATH . 'templates/single-product.php';
	}

	/**
	 * Render the template's Elementor content.
	 *
	 * @return string
	 */
	public static function content( $id ) {
		if ( ! self::elementor_ready() ) {
			return '';
		}

		// get_builder_content_for_display also queues that template's own CSS,
		// which a plain post_content read would not.
		return \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $id );
	}

	private static function elementor_ready() {
		return did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' );
	}

	/**
	 * An administrator-only account of how the template was chosen.
	 *
	 * Which language a product page is in is decided by Polylang from several
	 * things at once, so when the wrong template appears the useful question
	 * is not what the settings say but what was actually resolved.
	 *
	 * @return string
	 */
	public static function diagnostics() {
		if ( ! current_user_can( 'manage_woocommerce' ) || ! KMCB_Template_Settings::show_diagnostics() ) {
			return '';
		}

		$chosen   = (int) get_option( KMCB_Template_Settings::OPT_TEMPLATE );
		$used     = self::template_id();
		$has_pll  = function_exists( 'pll_get_post' );

		$rows = array(
			__( 'Product', 'km-catalog-builder' )          => get_the_title() . ' (#' . get_queried_object_id() . ')',
			__( 'Template chosen', 'km-catalog-builder' )  => $chosen ? get_the_title( $chosen ) . ' (#' . $chosen . ')' : __( 'None', 'km-catalog-builder' ),
			__( 'Template shown', 'km-catalog-builder' )   => $used ? get_the_title( $used ) . ' (#' . $used . ')' : __( 'None, the theme is drawing this page', 'km-catalog-builder' ),
		);

		if ( $has_pll ) {
			$product_lang = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( get_queried_object_id() ) : '';
			$current_lang = function_exists( 'pll_current_language' ) ? pll_current_language() : '';

			$rows[ __( 'Language of this product', 'km-catalog-builder' ) ] = $product_lang
				? $product_lang
				: __( 'none set, so the site default is used', 'km-catalog-builder' );

			$rows[ __( 'Language being viewed', 'km-catalog-builder' ) ] = $current_lang ? $current_lang : __( 'unknown', 'km-catalog-builder' );

			$visitor = self::visitor_language();

			$rows[ __( 'Language the visitor chose', 'km-catalog-builder' ) ] = $visitor
				? $visitor . ( KMCB_Template_Settings::follow_visitor() ? '' : __( ' (not being followed, see Settings)', 'km-catalog-builder' ) )
				: __( 'not recorded yet', 'km-catalog-builder' );

			$rows[ __( 'Language used to pick', 'km-catalog-builder' ) ] = self::language();

			// The one line that explains almost every wrong template: with
			// products outside Polylang there is a single URL per product and
			// no language on it, so every visitor gets the site default.
			$translatable = self::products_translatable();

			$rows[ __( 'Products in Polylang', 'km-catalog-builder' ) ] = $translatable
				? __( 'translatable', 'km-catalog-builder' )
				: '<strong>' . esc_html__( 'not translatable.', 'km-catalog-builder' ) . '</strong> '
					. esc_html__( 'Each product has one URL with no language on it, so every visitor is served the site default and the same template appears everywhere. Switch Products on under Languages > Settings and translate the products, or tick "Follow the language the visitor chose" to at least change the layout.', 'km-catalog-builder' );

			if ( $chosen && function_exists( 'pll_get_post_translations' ) ) {
				$list = array();

				foreach ( (array) pll_get_post_translations( $chosen ) as $lang => $post_id ) {
					$list[] = $lang . ': ' . get_the_title( $post_id ) . ' (#' . $post_id . ', ' . get_post_status( $post_id ) . ')';
				}

				$rows[ __( 'Templates by language', 'km-catalog-builder' ) ] = $list
					? implode( '<br>', $list )
					: __( 'none, this template has no translations linked to it', 'km-catalog-builder' );
			}
		} else {
			$rows[ __( 'Polylang', 'km-catalog-builder' ) ] = __( 'not active, so one template serves every page', 'km-catalog-builder' );
		}

		$out = '<div style="max-width:760px;margin:30px auto;padding:16px 20px;border:1px solid #e0c86a;background:#fdf8e3;border-radius:8px;font:13px/1.6 system-ui;color:#4b3f14;" dir="ltr">';
		$out .= '<p style="margin:0 0 10px;font-weight:700">' . esc_html__( 'Product Template — only administrators can see this', 'km-catalog-builder' ) . '</p>';
		$out .= '<table style="width:100%;border-collapse:collapse">';

		foreach ( $rows as $label => $value ) {
			$out .= '<tr>';
			$out .= '<th scope="row" style="text-align:left;padding:5px 12px 5px 0;font-weight:600;vertical-align:top;white-space:nowrap">' . esc_html( $label ) . '</th>';
			$out .= '<td style="padding:5px 0;vertical-align:top">' . wp_kses( $value, array( 'br' => array(), 'strong' => array() ) ) . '</td>';
			$out .= '</tr>';
		}

		$out .= '</table>';

		// Measured on the device rather than in a desktop browser, because the
		// fault has only ever shown on a phone and only when arriving from
		// another page. Reported twice: once as the page settles and again a
		// moment later, so a late shift is visible rather than inferred.
		$out .= '<div id="kmcb-template-measure" style="margin-top:12px;font:12px/1.5 ui-monospace,Consolas,monospace;white-space:pre-wrap"></div>';
		$out .= self::measure_script();

		$out .= '<p style="margin:10px 0 0"><a href="' . esc_url( admin_url( 'admin.php?page=' . KMCB_Template_Settings::PAGE ) ) . '">' . esc_html__( 'Settings', 'km-catalog-builder' ) . '</a></p>';
		$out .= '</div>';

		return $out;
	}

	/**
	 * Measures the gallery and its ancestors on the device.
	 *
	 * A box shorter than its own content is an element drawing outside its
	 * layout box, which is what puts later content on top of it. Reported for
	 * every level, so which one is at fault is read off rather than guessed.
	 *
	 * @return string
	 */
	private static function measure_script() {
		ob_start();
		?>
<script>
( function () {
	var out = document.getElementById( 'kmcb-template-measure' );

	if ( ! out ) {
		return;
	}

	function report( when ) {
		var el = document.querySelector( '.apg-gallery' );

		if ( ! el ) {
			return when + ': no gallery on this page
';
		}

		var lines = when + ':
';
		var depth = 0;

		while ( el && depth < 5 ) {
			var s = window.getComputedStyle( el );
			var box = Math.round( el.getBoundingClientRect().height );
			var content = el.scrollHeight;

			lines += '  ' + ( depth ? 'parent ' + depth : 'gallery' )
				+ ' box ' + box + ' content ' + content
				+ ( content > box + 1 ? '  OVERFLOWING' : '' )
				+ ' pos:' + s.position + ' h:' + s.height + ' ovf:' + s.overflow + '
';

			el = el.parentElement;
			depth++;
		}

		var img = document.querySelector( '.apg-gallery-img' );

		if ( img ) {
			lines += '  main image complete:' + img.complete + ' natural:' + img.naturalWidth + 'x' + img.naturalHeight + '
';
		}

		lines += '  thumbs: ' + document.querySelectorAll( '.apg-gallery-thumb' ).length + '
';

		return lines;
	}

	function paint() {
		out.textContent = report( 'on load' ) + ( out.dataset.later || '' );
	}

	if ( 'complete' === document.readyState ) {
		paint();
	} else {
		window.addEventListener( 'load', paint );
	}

	setTimeout( function () {
		out.dataset.later = report( '2 seconds later' );
		paint();
	}, 2000 );
}() );
</script>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * While the template is open in Elementor, stand a real product in for the
	 * one that would be there on the live page, so the widgets show something
	 * true instead of empty boxes.
	 */
	public static function preview_product() {
		if ( is_admin() || ! self::elementor_ready() ) {
			return;
		}

		$preview = \Elementor\Plugin::$instance->preview;

		if ( ! $preview || ! $preview->is_preview_mode() ) {
			return;
		}

		if ( KMCB_Template_Post_Type::SLUG !== get_post_type( get_queried_object_id() ) ) {
			return;
		}

		$sample = KMCB_Template_Settings::sample_id();

		if ( ! $sample ) {
			return;
		}

		$post = get_post( $sample );

		if ( ! $post || 'product' !== $post->post_type ) {
			return;
		}

		// The widgets read the product from whatever post is current, so it is
		// the post that has to be swapped, not just the product global.
		$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- deliberate, preview only.
		setup_postdata( $post );

		if ( function_exists( 'wc_setup_product_data' ) ) {
			wc_setup_product_data( $post );
		}
	}
}
