<?php
/**
 * Related products ("You May Also Like").
 *
 * @var array    $config
 * @var array    $state
 * @var WP_Query $query
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

if ( ! $query->have_posts() ) {
	return;
}
?>
<?php
$kmcb_rel_classes = array( 'kmcb', 'kmcb-related' );

if ( ! empty( $config['mobile_layout'] ) && 'swipe' === $config['mobile_layout'] ) {
	$kmcb_rel_classes[] = 'kmcb-related--swipe';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $kmcb_rel_classes ) ); ?>" style="--kmcb-cols: <?php echo esc_attr( $config['columns'] ); ?>;">
	<?php if ( '' !== trim( (string) $config['heading'] ) ) : ?>
		<h2 class="kmcb-related-heading"><?php echo esc_html( $config['heading'] ); ?></h2>
	<?php endif; ?>

	<div class="kmcb-grid kmcb-grid--grid">
		<?php echo KMCB_Render::cards( $config, $state, $query ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</div>
