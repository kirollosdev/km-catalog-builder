<?php
/**
 * Widget wrapper.
 *
 * @var array    $config
 * @var array    $state
 * @var WP_Query $query
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

$kmcb_classes = array(
	'kmcb',
	'kmcb--view-' . $state['view'],
	$config['show_sidebar'] ? 'kmcb--has-sidebar' : 'kmcb--no-sidebar',
);
?>
<div class="<?php echo esc_attr( implode( ' ', $kmcb_classes ) ); ?>"
	data-kmcb
	data-post="<?php echo esc_attr( $config['post_id'] ); ?>"
	data-widget="<?php echo esc_attr( $config['widget_id'] ); ?>"
	data-view="<?php echo esc_attr( $state['view'] ); ?>"
	data-sync="<?php echo $config['url_sync'] ? '1' : '0'; ?>"
	data-pagination="<?php echo esc_attr( $config['pagination'] ); ?>">

	<form class="kmcb-form" method="get" action="<?php echo esc_url( KMCB_Render::base_url() ); ?>" data-kmcb-form>

		<?php echo KMCB_Render::toolbar( $config, $state, $query ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<div class="kmcb-body">

			<?php if ( $config['show_sidebar'] ) : ?>
				<aside class="kmcb-sidebar" data-kmcb-sidebar>
					<?php echo KMCB_Render::sidebar( $config, $state ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</aside>
			<?php endif; ?>

			<div class="kmcb-content" data-kmcb-content aria-live="polite" aria-busy="false">
				<?php echo KMCB_Render::grid( $config, $state, $query ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

		</div>

		<noscript>
			<p><button type="submit" class="kmcb-apply"><?php echo esc_html( $config['apply_label'] ? $config['apply_label'] : __( 'Apply filters', 'km-catalog-builder' ) ); ?></button></p>
		</noscript>
	</form>
</div>
