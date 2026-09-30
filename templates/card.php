<?php
/**
 * Single product card.
 *
 * @var array      $config
 * @var array      $state
 * @var WC_Product $product
 * @var int        $index
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

$kmcb_id      = $product->get_id();
$kmcb_style   = KMCB_Render::card_style_attr( $config, $product );
$kmcb_wave    = 'wave' === $config['card_bg_style'] && KMCB_Render::card_has_two_colors( $config, $product );
$kmcb_badge   = KMCB_Render::first_term_name( $kmcb_id, $config['badge_tax'] );
$kmcb_eyebrow = KMCB_Render::first_term_name( $kmcb_id, $config['eyebrow_tax'] );
$kmcb_image   = $product->get_image( $config['image_size'], array( 'class' => 'kmcb-card-img', 'loading' => 'lazy' ) );
$kmcb_link    = get_permalink( $kmcb_id );
$kmcb_button  = $config['button_text'] ? $config['button_text'] : __( 'View Product', 'km-catalog-builder' );
?>
<article class="kmcb-card" data-kmcb-card>

	<a class="<?php echo esc_attr( KMCB_Render::card_media_class( $config, $product ) ); ?>"
		href="<?php echo esc_url( $kmcb_link ); ?>" tabindex="-1" aria-hidden="true"
		<?php echo $kmcb_style ? 'style="' . esc_attr( $kmcb_style ) . '"' : ''; ?>>

		<?php if ( $kmcb_wave ) : ?>
			<span class="kmcb-card-wave" aria-hidden="true"></span>
		<?php endif; ?>

		<?php echo wp_kses_post( $kmcb_image ); ?>

		<?php if ( $kmcb_badge ) : ?>
			<span class="kmcb-badge"><?php echo esc_html( $kmcb_badge ); ?></span>
		<?php endif; ?>
	</a>

	<div class="kmcb-card-body">
		<?php if ( $kmcb_eyebrow ) : ?>
			<p class="kmcb-eyebrow"><?php echo esc_html( $kmcb_eyebrow ); ?></p>
		<?php endif; ?>

		<h3 class="kmcb-title">
			<a href="<?php echo esc_url( $kmcb_link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
		</h3>

		<?php if ( $config['show_price'] && $product->get_price_html() ) : ?>
			<div class="kmcb-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
		<?php endif; ?>

		<?php
		// A left arrow leads the text rather than following it, so it still
		// reads as pointing onwards on the Arabic side of the shop.
		$kmcb_arrow_left = isset( $config['card_arrow_dir'] ) && 'left' === $config['card_arrow_dir'];
		$kmcb_arrow      = '<svg viewBox="0 0 14 10" aria-hidden="true" focusable="false"><path d="M1 5h11M8.5 1.5 12 5l-3.5 3.5" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		$kmcb_label      = '<span>' . esc_html( $kmcb_button ) . '</span>';
		?>
		<a class="kmcb-more<?php echo $kmcb_arrow_left ? ' kmcb-more--left' : ''; ?>" href="<?php echo esc_url( $kmcb_link ); ?>">
			<?php
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- label escaped above, arrow is static markup.
			echo $kmcb_arrow_left ? $kmcb_arrow . $kmcb_label : $kmcb_label . $kmcb_arrow;
			// phpcs:enable
			?>
		</a>
	</div>
</article>
