<?php
/**
 * Product grid + pagination.
 *
 * @var array    $config
 * @var array    $state
 * @var WP_Query $query
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="kmcb-grid kmcb-grid--<?php echo esc_attr( $state['view'] ); ?>" data-kmcb-grid>
	<?php
	if ( $query->have_posts() ) {
		echo KMCB_Render::cards( $config, $state, $query ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>
</div>

<?php if ( ! $query->have_posts() ) : ?>
	<div class="kmcb-empty">
		<p class="kmcb-empty-title"><?php echo esc_html( $config['empty_title'] ? $config['empty_title'] : __( 'No products match your filters.', 'km-catalog-builder' ) ); ?></p>
		<a class="kmcb-empty-reset" href="<?php echo esc_url( KMCB_Render::base_url() ); ?>" data-kmcb-clear><?php echo esc_html( $config['empty_reset'] ? $config['empty_reset'] : __( 'Clear all filters', 'km-catalog-builder' ) ); ?></a>
		<?php echo KMCB_Render::diagnostics( $config, $state, $query ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
<?php endif; ?>

<?php
$kmcb_max = (int) $query->max_num_pages;

if ( 'none' !== $config['pagination'] && $kmcb_max > 1 ) :

	if ( 'load_more' === $config['pagination'] ) :
		$kmcb_has_more = $state['paged'] < $kmcb_max;
		?>
		<div class="kmcb-pagination kmcb-pagination--more">
			<button type="button"
				class="kmcb-load-more"
				data-kmcb-load-more
				data-paged="<?php echo esc_attr( $state['paged'] + 1 ); ?>"
				<?php disabled( ! $kmcb_has_more ); ?>
				<?php echo $kmcb_has_more ? '' : 'hidden'; ?>>
				<?php echo esc_html( $config['load_more_text'] ? $config['load_more_text'] : __( 'Load more', 'km-catalog-builder' ) ); ?>
			</button>
		</div>
		<?php
	else :
		?>
		<nav class="kmcb-pagination kmcb-pagination--numbers" aria-label="<?php esc_attr_e( 'Products pagination', 'km-catalog-builder' ); ?>">
			<?php
			for ( $kmcb_i = 1; $kmcb_i <= $kmcb_max; $kmcb_i++ ) :
				$kmcb_state          = $state;
				$kmcb_state['paged'] = $kmcb_i;
				?>
				<a class="kmcb-page<?php echo (int) $state['paged'] === $kmcb_i ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( KMCB_Render::url_for_state( $config, $kmcb_state ) ); ?>"
					data-kmcb-page="<?php echo esc_attr( $kmcb_i ); ?>"><?php echo esc_html( $kmcb_i ); ?></a>
			<?php endfor; ?>
		</nav>
		<?php
	endif;

endif;
