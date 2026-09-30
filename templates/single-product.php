<?php
/**
 * The single product page, built from the chosen template.
 *
 * The loop is run rather than the product simply being fetched, because the
 * widgets on the template read whichever post is current. Running it is what
 * makes the page they are on the product they describe.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

$kmcb_template_template = KMCB_Template_Render::template_id();

get_header( 'shop' );

?>
<div class="woocommerce kmcb-template-product">
	<?php
	while ( have_posts() ) :
		the_post();

		global $product;

		$product = wc_get_product( get_the_ID() );

		/**
		 * WooCommerce's own hook. Notices ride on this one, so an add to cart
		 * message still appears above the page.
		 */
		do_action( 'woocommerce_before_single_product' );

		echo KMCB_Template_Render::content( $kmcb_template_template ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor output.

		do_action( 'woocommerce_after_single_product' );

		echo KMCB_Template_Render::diagnostics(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in diagnostics().
	endwhile;
	?>
</div>
<?php

get_footer( 'shop' );
