<?php
/**
 * Which template product pages use, and which product stands in while editing.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

class KMCB_Template_Settings {

	const OPT_TEMPLATE = 'kmcb_template';
	const OPT_SAMPLE   = 'kmcb_template_sample';
	const OPT_FOLLOW   = 'kmcb_template_follow_visitor';
	const OPT_DIAGNOSTICS = 'kmcb_template_diagnostics';
	const OPT_ENABLED     = 'kmcb_template_enabled';
	const PAGE         = 'kmcb-product-template';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	public static function menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Product Template Settings', 'km-catalog-builder' ),
			__( 'Product Template', 'km-catalog-builder' ),
			'manage_woocommerce',
			self::PAGE,
			array( __CLASS__, 'page' )
		);
	}

	public static function register() {
		register_setting( self::PAGE, self::OPT_TEMPLATE, array( 'sanitize_callback' => 'absint' ) );
		register_setting( self::PAGE, self::OPT_SAMPLE, array( 'sanitize_callback' => 'absint' ) );
		register_setting( self::PAGE, self::OPT_FOLLOW, array( 'sanitize_callback' => 'absint' ) );
		register_setting( self::PAGE, self::OPT_DIAGNOSTICS, array( 'sanitize_callback' => 'absint' ) );
		register_setting( self::PAGE, self::OPT_ENABLED, array( 'sanitize_callback' => 'absint' ) );
	}

	public static function sample_id() {
		return (int) get_option( self::OPT_SAMPLE );
	}

	/**
	 * Is Elementor Pro's Theme Builder here?
	 *
	 * It builds product pages too, and two things claiming the same page is a
	 * fight nobody wins, so this module stands aside for it.
	 */
	public static function theme_builder_active() {
		return did_action( 'elementor_pro/init' ) || class_exists( '\\ElementorPro\\Modules\\ThemeBuilder\\Module' );
	}

	/**
	 * Whether this module draws product pages at all.
	 *
	 * On by default on a site with plain Elementor, which is the whole reason
	 * it exists. Off by default where Elementor Pro is active, and either way
	 * the checkbox on the settings screen has the final say.
	 */
	public static function is_enabled() {
		$stored = get_option( self::OPT_ENABLED, null );

		if ( null !== $stored && '' !== $stored ) {
			return (bool) $stored;
		}

		return ! self::theme_builder_active();
	}

	public static function follow_visitor() {
		return (bool) get_option( self::OPT_FOLLOW );
	}

	/**
	 * Whether the panel explaining the template choice is shown.
	 *
	 * Off, so a live shop shows nothing to the staff who run it. An
	 * administrator can still see it on any product page by adding
	 * ?kmcb_template_debug=1 to the address, without turning anything on.
	 */
	public static function show_diagnostics() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reading a switch, changing nothing.
		if ( isset( $_GET['kmcb_template_debug'] ) && current_user_can( 'manage_woocommerce' ) ) {
			return '0' !== (string) $_GET['kmcb_template_debug']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return (bool) get_option( self::OPT_DIAGNOSTICS );
	}

	/**
	 * Templates in the site's main language only.
	 *
	 * The other languages are translations of these, picked up on their own
	 * when that language is being viewed, so listing them here would only
	 * invite choosing the wrong one.
	 */
	private static function templates() {
		$args = array(
			'post_type'        => KMCB_Template_Post_Type::SLUG,
			'post_status'      => 'publish',
			'numberposts'      => 100,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'suppress_filters' => false,
		);

		if ( function_exists( 'pll_default_language' ) ) {
			$args['lang'] = pll_default_language();
		}

		return get_posts( $args );
	}

	private static function products() {
		return get_posts(
			array(
				'post_type'   => 'product',
				'post_status' => 'publish',
				'numberposts' => 100,
				'orderby'     => 'title',
				'order'       => 'ASC',
			)
		);
	}

	public static function page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$templates = self::templates();
		$chosen    = (int) get_option( self::OPT_TEMPLATE );
		$sample    = self::sample_id();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Product Template', 'km-catalog-builder' ); ?></h1>

			<?php if ( empty( $templates ) ) : ?>
				<div class="notice notice-info inline">
					<p>
						<?php esc_html_e( 'No product templates yet.', 'km-catalog-builder' ); ?>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . KMCB_Template_Post_Type::SLUG ) ); ?>">
							<?php esc_html_e( 'Create one', 'km-catalog-builder' ); ?>
						</a>
						<?php esc_html_e( 'and build it with Elementor, then come back here to switch it on.', 'km-catalog-builder' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<form method="post" action="options.php">
				<?php settings_fields( self::PAGE ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="kmcb_template_template"><?php esc_html_e( 'Use this template', 'km-catalog-builder' ); ?></label>
						</th>
						<td>
							<select name="<?php echo esc_attr( self::OPT_TEMPLATE ); ?>" id="kmcb_template_template">
								<option value="0"><?php esc_html_e( 'None, leave the theme in charge', 'km-catalog-builder' ); ?></option>
								<?php foreach ( $templates as $template ) : ?>
									<option value="<?php echo esc_attr( $template->ID ); ?>" <?php selected( $chosen, $template->ID ); ?>>
										<?php echo esc_html( get_the_title( $template ) ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description">
								<?php esc_html_e( 'Every product page is built from this template.', 'km-catalog-builder' ); ?>
								<?php if ( function_exists( 'pll_get_post' ) ) : ?>
									<br>
									<?php esc_html_e( 'Translations are picked up on their own: translate this template with the + button in the Product Templates list, and each language gets its own layout. A language with no translation, or an unpublished one, falls back to this.', 'km-catalog-builder' ); ?>
								<?php endif; ?>
							</p>
						</td>
					</tr>

					<?php if ( function_exists( 'pll_get_post' ) ) : ?>
						<tr>
							<th scope="row"><?php esc_html_e( 'Untranslated products', 'km-catalog-builder' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( self::OPT_FOLLOW ); ?>" value="1" <?php checked( self::follow_visitor() ); ?> />
									<?php esc_html_e( 'Follow the language the visitor chose', 'km-catalog-builder' ); ?>
								</label>
								<p class="description">
									<?php esc_html_e( 'Only used for a product that has no language of its own. Such a product has one URL carrying no language either, so every visitor is served the site default and the same template appears on both sides of the shop. This falls back to the language the visitor last picked instead.', 'km-catalog-builder' ); ?>
									<br>
									<strong><?php esc_html_e( 'It changes the layout only.', 'km-catalog-builder' ); ?></strong>
									<?php esc_html_e( 'There is still one product, so its name and description stay in whichever language they were written. For a genuinely Arabic product page, switch Products on under Languages > Settings and translate the products themselves.', 'km-catalog-builder' ); ?>
								</p>
							</td>
						</tr>
					<?php endif; ?>

					<tr>
						<th scope="row"><?php esc_html_e( 'Product pages', 'km-catalog-builder' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( self::OPT_ENABLED ); ?>" value="1" <?php checked( self::is_enabled() ); ?> />
								<?php esc_html_e( 'Build product pages from a template here', 'km-catalog-builder' ); ?>
							</label>
							<p class="description">
								<?php if ( self::theme_builder_active() ) : ?>
									<?php esc_html_e( 'Elementor Pro is active, and its Theme Builder builds product pages as well. Leave this off and use one of them, not both: two templates claiming the same page fight over it.', 'km-catalog-builder' ); ?>
								<?php else : ?>
									<?php esc_html_e( 'Turn this off to hand product pages back to your theme.', 'km-catalog-builder' ); ?>
								<?php endif; ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Diagnostics', 'km-catalog-builder' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( self::OPT_DIAGNOSTICS ); ?>" value="1" <?php checked( (bool) get_option( self::OPT_DIAGNOSTICS ) ); ?> />
								<?php esc_html_e( 'Show the panel explaining how the template was chosen', 'km-catalog-builder' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Only administrators ever saw it, but on a live shop that includes the people running it, so it is off. Leave it off and add ?kmcb_template_debug=1 to a product page address when you need to look.', 'km-catalog-builder' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="kmcb_template_sample"><?php esc_html_e( 'Preview with', 'km-catalog-builder' ); ?></label>
						</th>
						<td>
							<select name="<?php echo esc_attr( self::OPT_SAMPLE ); ?>" id="kmcb_template_sample">
								<option value="0"><?php esc_html_e( 'The newest product', 'km-catalog-builder' ); ?></option>
								<?php foreach ( self::products() as $product ) : ?>
									<option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $sample, $product->ID ); ?>>
										<?php echo esc_html( get_the_title( $product ) ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description">
								<?php esc_html_e( 'Editing a template there is no product in view, so this one stands in and the widgets show real content. It changes nothing on the live site.', 'km-catalog-builder' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
