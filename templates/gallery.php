<?php
/**
 * Single product gallery: one large image on a coloured card, thumbnails below.
 *
 * @var array      $config
 * @var WC_Product $product
 * @var int[]      $images
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

$kmcb_colors = array(
	'from' => '',
	'to'   => '',
);

if ( $config['use_product_colors'] ) {
	$kmcb_colors = KMCB_Render::product_colors( $product, $config['color_tax'] );
}

if ( '' === $kmcb_colors['from'] ) {
	$kmcb_colors = array(
		'from' => $config['fallback_from'],
		'to'   => $config['fallback_to'],
	);
}

$kmcb_from = KMCB_Render::safe_color( $kmcb_colors['from'] );
$kmcb_to   = KMCB_Render::safe_color( $kmcb_colors['to'] );

$kmcb_style = '';
if ( '' !== $kmcb_from ) {
	$kmcb_style .= '--kmcb-card-bg:' . $kmcb_from . ';';
}
if ( '' !== $kmcb_to && $kmcb_to !== $kmcb_from ) {
	$kmcb_style .= '--kmcb-card-bg-2:' . $kmcb_to . ';';
}

$kmcb_wave       = $config['wave'] && '' !== $kmcb_to && $kmcb_to !== $kmcb_from;
$kmcb_thumb_wave = $kmcb_wave && ! empty( $config['thumb_wave'] );
$kmcb_thumbs = $config['show_thumbs'] && count( $images ) > 1;

$kmcb_classes = array(
	'kmcb-gallery',
	'kmcb-gallery--thumbs-' . $config['thumbs_position'],
);

if ( ! $kmcb_thumbs ) {
	$kmcb_classes[] = 'kmcb-gallery--no-thumbs';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $kmcb_classes ) ); ?>"
	data-kmcb-gallery
	<?php echo $kmcb_style ? 'style="' . esc_attr( $kmcb_style ) . '"' : ''; ?>>

	<div class="kmcb-gallery-main<?php echo $kmcb_wave ? ' kmcb-gallery-main--wave' : ''; ?>" data-kmcb-gallery-main>
		<?php if ( $kmcb_wave ) : ?>
			<span class="kmcb-card-wave" aria-hidden="true"></span>
		<?php endif; ?>

		<?php
		if ( empty( $images ) ) {
			echo wp_kses_post( $product->get_image( $config['main_size'], array( 'class' => 'kmcb-gallery-img' ) ) );
		} else {
			echo wp_kses_post(
				wp_get_attachment_image(
					$images[0],
					$config['main_size'],
					false,
					array(
						'class' => 'kmcb-gallery-img',
						'id'    => 'kmcb-gallery-img-' . esc_attr( $config['widget_id'] ),
					)
				)
			);
		}
		?>
	</div>

	<?php if ( $kmcb_thumbs ) : ?>
		<div class="kmcb-gallery-thumbs" role="group" aria-label="<?php esc_attr_e( 'Product images', 'km-catalog-builder' ); ?>">
			<?php foreach ( $images as $kmcb_i => $kmcb_image_id ) : ?>
				<button type="button"
					class="kmcb-gallery-thumb<?php echo 0 === $kmcb_i ? ' is-active' : ''; ?>"
					data-kmcb-thumb
					data-id="<?php echo (int) $kmcb_image_id; ?>"
					data-full="<?php echo esc_url( (string) wp_get_attachment_image_url( $kmcb_image_id, $config['main_size'] ) ); ?>"
					data-srcset="<?php echo esc_attr( (string) wp_get_attachment_image_srcset( $kmcb_image_id, $config['main_size'] ) ); ?>"
					aria-pressed="<?php echo 0 === $kmcb_i ? 'true' : 'false'; ?>"
					aria-label="<?php
						/* translators: %d: image number. */
						echo esc_attr( sprintf( __( 'Show image %d', 'km-catalog-builder' ), $kmcb_i + 1 ) );
					?>">
					<?php if ( $kmcb_thumb_wave ) : ?>
						<span class="kmcb-card-wave" aria-hidden="true"></span>
					<?php endif; ?>
					<?php
					echo wp_kses_post(
						wp_get_attachment_image(
							$kmcb_image_id,
							$config['thumb_size'],
							false,
							array( 'class' => 'kmcb-gallery-thumb-img' )
						)
					);
					?>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
