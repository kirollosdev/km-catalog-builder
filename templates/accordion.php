<?php
/**
 * Product accordion.
 *
 * @var array      $config
 * @var WC_Product $product
 * @var array      $panels
 * @var string     $note
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $panels ) && '' === $note ) {
	return;
}

$kmcb_uid = 'kmcb-acc-' . $config['widget_id'];
?>
<?php
$kmcb_acc_classes = array(
	'kmcb-accordion',
	'kmcb-accordion--' . ( isset( $config['layout'] ) && 'joined' === $config['layout'] ? 'joined' : 'cards' ),
	'kmcb-accordion--icon-' . ( isset( $config['icon'] ) && 'plain' === $config['icon'] ? 'plain' : 'circle' ),
);
?>
<div class="<?php echo esc_attr( implode( ' ', $kmcb_acc_classes ) ); ?>" data-kmcb-accordion data-one="<?php echo $config['one_at_a_time'] ? '1' : '0'; ?>">
	<?php foreach ( $panels as $kmcb_i => $kmcb_panel ) : ?>
		<?php
		$kmcb_open   = ( 0 === $kmcb_i && $config['open_first'] );
		$kmcb_panel_id = $kmcb_uid . '-' . $kmcb_i;
		?>
		<div class="kmcb-acc-item<?php echo $kmcb_open ? ' is-open' : ''; ?>">
			<h3 class="kmcb-acc-heading">
				<button type="button"
					class="kmcb-acc-head"
					id="<?php echo esc_attr( $kmcb_panel_id . '-head' ); ?>"
					aria-expanded="<?php echo $kmcb_open ? 'true' : 'false'; ?>"
					aria-controls="<?php echo esc_attr( $kmcb_panel_id ); ?>"
					data-kmcb-acc-toggle>
					<span class="kmcb-acc-title"><?php echo esc_html( $kmcb_panel['title'] ); ?></span>
					<span class="kmcb-acc-icon-wrap" aria-hidden="true">
						<svg class="kmcb-acc-icon" viewBox="0 0 14 14" focusable="false"><path d="M3 5.5 7 9.5l4-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</span>
				</button>
			</h3>

			<div class="kmcb-acc-body"
				id="<?php echo esc_attr( $kmcb_panel_id ); ?>"
				role="region"
				aria-labelledby="<?php echo esc_attr( $kmcb_panel_id . '-head' ); ?>"
				<?php echo $kmcb_open ? '' : 'hidden'; ?>>
				<div class="kmcb-acc-content">
					<?php echo wp_kses_post( $kmcb_panel['content'] ); ?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>

	<?php if ( '' !== $note ) : ?>
		<div class="kmcb-acc-note"><?php echo wp_kses_post( $note ); ?></div>
	<?php endif; ?>
</div>
