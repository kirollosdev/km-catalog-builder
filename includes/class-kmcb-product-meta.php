<?php
/**
 * Card colour controls: one per product, one per taxonomy term.
 *
 * Lets the pastel card backgrounds from the design be managed by the shop
 * owner instead of being hard-coded.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

class KMCB_Product_Meta {

	const PRODUCT_META   = '_kmcb_card_bg';
	const PRODUCT_LINK   = '_kmcb_find_url';
	const PRODUCT_META_2 = '_kmcb_card_bg_2';
	const SECTION_INFO        = '_kmcb_section_info';
	const SECTION_INGREDIENTS = '_kmcb_section_ingredients';
	const SECTION_FEEDING     = '_kmcb_section_feeding';
	const PRODUCT_NOTE        = '_kmcb_product_note';

	const TERM_META      = 'kmcb_card_bg';
	const TERM_META_2    = 'kmcb_card_bg_2';

	public static function init() {
		// Product field. This lives in its own tab rather than the General one:
		// WooCommerce hides the General tab for variable and grouped products,
		// so a field placed there is invisible for half the catalogue.
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'product_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'product_panel' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'details_panel' ) );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_product_field' ) );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_details' ) );

		// Term fields for product categories and every product attribute.
		add_action( 'admin_init', array( __CLASS__, 'register_term_fields' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	public static function enqueue( $hook ) {
		$screens = array( 'post.php', 'post-new.php', 'edit-tags.php', 'term.php' );

		if ( ! in_array( $hook, $screens, true ) ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'kmcb-admin', KMCB_URL . 'assets/js/kmcb-admin.js', array( 'jquery', 'wp-color-picker' ), KMCB_VERSION, true );
	}

	/**
	 * Add a "Grid Card" tab to the Product data panel.
	 *
	 * No show_if_* classes, so it is present for every product type.
	 */
	public static function product_tab( $tabs ) {
		$tabs['kmcb_details'] = array(
			'label'    => __( 'Product Details', 'km-catalog-builder' ),
			'target'   => 'kmcb_details_product_data',
			'class'    => array(),
			'priority' => 64,
		);

		$tabs['kmcb_card'] = array(
			'label'    => __( 'Grid Card', 'km-catalog-builder' ),
			'target'   => 'kmcb_card_product_data',
			'class'    => array(),
			'priority' => 65,
		);

		return $tabs;
	}

	public static function product_panel() {
		global $post;

		$from = get_post_meta( $post->ID, self::PRODUCT_META, true );
		$to   = get_post_meta( $post->ID, self::PRODUCT_META_2, true );
		?>
		<div id="kmcb_card_product_data" class="panel woocommerce_options_panel hidden">
			<div class="options_group">
				<p class="form-field">
					<label for="<?php echo esc_attr( self::PRODUCT_META ); ?>"><?php esc_html_e( 'Card background', 'km-catalog-builder' ); ?></label>
					<input type="text"
						class="kmcb-color-field"
						name="<?php echo esc_attr( self::PRODUCT_META ); ?>"
						id="<?php echo esc_attr( self::PRODUCT_META ); ?>"
						value="<?php echo esc_attr( $from ); ?>" />
				</p>
				<p class="form-field">
					<label for="<?php echo esc_attr( self::PRODUCT_META_2 ); ?>"><?php esc_html_e( 'Second colour', 'km-catalog-builder' ); ?></label>
					<input type="text"
						class="kmcb-color-field"
						name="<?php echo esc_attr( self::PRODUCT_META_2 ); ?>"
						id="<?php echo esc_attr( self::PRODUCT_META_2 ); ?>"
						value="<?php echo esc_attr( $to ); ?>" />
				</p>
				<p class="form-field">
					<label for="<?php echo esc_attr( self::PRODUCT_LINK ); ?>"><?php esc_html_e( 'Where to find this product', 'km-catalog-builder' ); ?></label>
					<input type="url"
						class="short"
						name="<?php echo esc_attr( self::PRODUCT_LINK ); ?>"
						id="<?php echo esc_attr( self::PRODUCT_LINK ); ?>"
						placeholder="https://"
						value="<?php echo esc_attr( get_post_meta( $post->ID, self::PRODUCT_LINK, true ) ); ?>" />
					<span class="description"><?php esc_html_e( 'Optional. Overrides the link on the Product Button widget for this product only.', 'km-catalog-builder' ); ?></span>
				</p>
				<p class="form-field">
					<span class="description">
						<?php esc_html_e( 'The colours shown behind this product image in the product grid. Set both and the card blends from the first into the second; set only the first for a flat colour.', 'km-catalog-builder' ); ?>
						<br />
						<?php esc_html_e( 'Whatever you choose here always wins, whatever the sorting or filtering. Leave both empty and the grid falls back to the colours set on the product category, then to the widget palette.', 'km-catalog-builder' ); ?>
					</span>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * The sections shown in the Product Accordion widget, as meta key => label.
	 */
	public static function detail_sections() {
		return array(
			self::SECTION_INFO        => __( 'Product Information', 'km-catalog-builder' ),
			self::SECTION_INGREDIENTS => __( 'Ingredients / Composition', 'km-catalog-builder' ),
			self::SECTION_FEEDING     => __( 'Feeding Guide', 'km-catalog-builder' ),
		);
	}

	/**
	 * A rich text editor per section, so the accordion content is edited here
	 * rather than buried in the description.
	 */
	public static function details_panel() {
		global $post;
		?>
		<div id="kmcb_details_product_data" class="panel woocommerce_options_panel hidden">
			<div class="options_group kmcb-details">
				<p class="kmcb-details-intro">
					<?php esc_html_e( 'These three sections fill the Product Accordion widget on the single product page. Leave one empty and its panel is not shown.', 'km-catalog-builder' ); ?>
				</p>

				<?php foreach ( self::detail_sections() as $kmcb_key => $kmcb_label ) : ?>
					<div class="kmcb-details-field">
						<label for="<?php echo esc_attr( $kmcb_key ); ?>"><strong><?php echo esc_html( $kmcb_label ); ?></strong></label>
						<?php
						wp_editor(
							get_post_meta( $post->ID, $kmcb_key, true ),
							$kmcb_key,
							array(
								'textarea_name' => $kmcb_key,
								'textarea_rows' => 8,
								'media_buttons' => false,
								'teeny'         => true,
								'quicktags'     => true,
							)
						);
						?>
					</div>
				<?php endforeach; ?>

				<div class="kmcb-details-field">
					<label for="<?php echo esc_attr( self::PRODUCT_NOTE ); ?>"><strong><?php esc_html_e( 'Note under the accordion', 'km-catalog-builder' ); ?></strong></label>
					<p class="description"><?php esc_html_e( 'Shown under the whole accordion, not inside a panel. Used for the lines the pack prints under the feeding table, e.g. "For Animal Use."', 'km-catalog-builder' ); ?></p>
					<?php
					wp_editor(
						get_post_meta( $post->ID, self::PRODUCT_NOTE, true ),
						self::PRODUCT_NOTE,
						array(
							'textarea_name' => self::PRODUCT_NOTE,
							'textarea_rows' => 4,
							'media_buttons' => false,
							'teeny'         => true,
							'quicktags'     => true,
						)
					);
					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * @param WC_Product $product
	 */
	public static function save_details( $product ) {
		foreach ( array_merge( array_keys( self::detail_sections() ), array( self::PRODUCT_NOTE ) ) as $key ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies before this hook.
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$value = wp_kses_post( wp_unslash( $_POST[ $key ] ) );

			$product->update_meta_data( $key, $value );
		}
	}

	/**
	 * @param WC_Product $product
	 */
	public static function save_product_field( $product ) {
		foreach ( array( self::PRODUCT_META, self::PRODUCT_META_2 ) as $key ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies before this hook.
			$value = isset( $_POST[ $key ] ) ? sanitize_hex_color( wp_unslash( $_POST[ $key ] ) ) : '';

			$product->update_meta_data( $key, $value ? $value : '' );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies before this hook.
		$link = isset( $_POST[ self::PRODUCT_LINK ] ) ? esc_url_raw( wp_unslash( $_POST[ self::PRODUCT_LINK ] ) ) : '';

		$product->update_meta_data( self::PRODUCT_LINK, $link );
	}

	public static function register_term_fields() {
		foreach ( self::color_taxonomies() as $taxonomy ) {
			add_action( $taxonomy . '_add_form_fields', array( __CLASS__, 'term_add_field' ) );
			add_action( $taxonomy . '_edit_form_fields', array( __CLASS__, 'term_edit_field' ) );
			add_action( 'created_' . $taxonomy, array( __CLASS__, 'save_term_field' ) );
			add_action( 'edited_' . $taxonomy, array( __CLASS__, 'save_term_field' ) );
		}
	}

	private static function color_taxonomies() {
		$taxonomies = array( 'product_cat' );

		if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
			foreach ( wc_get_attribute_taxonomies() as $attribute ) {
				$taxonomies[] = wc_attribute_taxonomy_name( $attribute->attribute_name );
			}
		}

		return $taxonomies;
	}

	public static function term_add_field() {
		?>
		<div class="form-field">
			<label for="<?php echo esc_attr( self::TERM_META ); ?>"><?php esc_html_e( 'Grid card background', 'km-catalog-builder' ); ?></label>
			<input type="text" class="kmcb-color-field" name="<?php echo esc_attr( self::TERM_META ); ?>" id="<?php echo esc_attr( self::TERM_META ); ?>" value="" />
			<p><?php esc_html_e( 'Default card background for products in this term.', 'km-catalog-builder' ); ?></p>
		</div>
		<div class="form-field">
			<label for="<?php echo esc_attr( self::TERM_META_2 ); ?>"><?php esc_html_e( 'Second colour', 'km-catalog-builder' ); ?></label>
			<input type="text" class="kmcb-color-field" name="<?php echo esc_attr( self::TERM_META_2 ); ?>" id="<?php echo esc_attr( self::TERM_META_2 ); ?>" value="" />
			<p><?php esc_html_e( 'Set this too and the card blends from the first colour into the second.', 'km-catalog-builder' ); ?></p>
		</div>
		<?php
	}

	public static function term_edit_field( $term ) {
		$from = get_term_meta( $term->term_id, self::TERM_META, true );
		$to   = get_term_meta( $term->term_id, self::TERM_META_2, true );
		?>
		<tr class="form-field">
			<th scope="row"><label for="<?php echo esc_attr( self::TERM_META ); ?>"><?php esc_html_e( 'Grid card background', 'km-catalog-builder' ); ?></label></th>
			<td>
				<input type="text" class="kmcb-color-field" name="<?php echo esc_attr( self::TERM_META ); ?>" id="<?php echo esc_attr( self::TERM_META ); ?>" value="<?php echo esc_attr( $from ); ?>" />
				<p class="description"><?php esc_html_e( 'Default card background for products in this term.', 'km-catalog-builder' ); ?></p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="<?php echo esc_attr( self::TERM_META_2 ); ?>"><?php esc_html_e( 'Second colour', 'km-catalog-builder' ); ?></label></th>
			<td>
				<input type="text" class="kmcb-color-field" name="<?php echo esc_attr( self::TERM_META_2 ); ?>" id="<?php echo esc_attr( self::TERM_META_2 ); ?>" value="<?php echo esc_attr( $to ); ?>" />
				<p class="description"><?php esc_html_e( 'Set this too and the card blends from the first colour into the second.', 'km-catalog-builder' ); ?></p>
			</td>
		</tr>
		<?php
	}

	public static function save_term_field( $term_id ) {
		if ( ! current_user_can( 'manage_product_terms' ) && ! current_user_can( 'manage_categories' ) ) {
			return;
		}

		foreach ( array( self::TERM_META, self::TERM_META_2 ) as $key ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- core verifies the term form nonce.
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$value = sanitize_hex_color( wp_unslash( $_POST[ $key ] ) );

			if ( $value ) {
				update_term_meta( $term_id, $key, $value );
			} else {
				delete_term_meta( $term_id, $key );
			}
		}
	}
}
