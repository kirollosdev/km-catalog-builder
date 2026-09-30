<?php
/**
 * Facet sidebar.
 *
 * @var array $config
 * @var array $state
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

$kmcb_p      = KMCB_Render::PREFIX;
$kmcb_groups = KMCB_Config::get_filter_groups( $config );
$kmcb_active = KMCB_Render::has_active_filters( $state );
?>
<div class="kmcb-filters">

	<div class="kmcb-filters-head">
		<span class="kmcb-filters-title"><?php echo esc_html( $config['sidebar_title'] ? $config['sidebar_title'] : __( 'Filters', 'km-catalog-builder' ) ); ?></span>
		<a class="kmcb-clear<?php echo $kmcb_active ? '' : ' is-disabled'; ?>"
			href="<?php echo esc_url( KMCB_Render::base_url() ); ?>"
			data-kmcb-clear><?php echo esc_html( $config['clear_label'] ? $config['clear_label'] : __( 'Clear All', 'km-catalog-builder' ) ); ?></a>
	</div>

	<?php
	foreach ( $kmcb_groups as $kmcb_group ) :
		$kmcb_terms = KMCB_Query::get_group_terms( $kmcb_group, $config );

		if ( empty( $kmcb_terms ) ) {
			continue;
		}

		$kmcb_tax_obj  = get_taxonomy( $kmcb_group['taxonomy'] );
		$kmcb_label    = $kmcb_group['label'] ? $kmcb_group['label'] : ( $kmcb_tax_obj ? $kmcb_tax_obj->labels->singular_name : $kmcb_group['taxonomy'] );
		$kmcb_selected = isset( $state['f'][ $kmcb_group['taxonomy'] ] ) ? $state['f'][ $kmcb_group['taxonomy'] ] : array();
		$kmcb_use_counts = $config['filter_counts'] || $config['hide_empty_terms'];
		$kmcb_counts     = $kmcb_use_counts ? KMCB_Query::term_counts( $config, $state, $kmcb_group['taxonomy'] ) : array();
		$kmcb_input    = 'single' === $kmcb_group['type'] ? 'radio' : 'checkbox';
		$kmcb_open     = ! empty( $kmcb_group['open'] ) || ! empty( $kmcb_selected );
		?>
		<div class="kmcb-group<?php echo $kmcb_open ? ' is-open' : ''; ?>" data-kmcb-group="<?php echo esc_attr( $kmcb_group['taxonomy'] ); ?>">

			<?php if ( ! empty( $kmcb_group['collapsible'] ) ) : ?>
				<button type="button" class="kmcb-group-head" data-kmcb-group-toggle aria-expanded="<?php echo $kmcb_open ? 'true' : 'false'; ?>">
					<span><?php echo esc_html( $kmcb_label ); ?></span>
					<span class="kmcb-toggle" aria-hidden="true"></span>
				</button>
			<?php else : ?>
				<div class="kmcb-group-head kmcb-group-head--static"><span><?php echo esc_html( $kmcb_label ); ?></span></div>
			<?php endif; ?>

			<div class="kmcb-group-body">
				<?php
				foreach ( $kmcb_terms as $kmcb_term ) :
					$kmcb_is_on  = in_array( $kmcb_term->slug, $kmcb_selected, true );
					$kmcb_count  = isset( $kmcb_counts[ $kmcb_term->slug ] ) ? (int) $kmcb_counts[ $kmcb_term->slug ] : 0;
					$kmcb_is_out = ( $kmcb_use_counts && 0 === $kmcb_count && ! $kmcb_is_on );

					if ( $kmcb_is_out && $config['hide_empty_terms'] ) {
						continue;
					}

					$kmcb_url = KMCB_Render::url_for_state(
						$config,
						KMCB_Render::toggled( $state, $kmcb_group['taxonomy'], $kmcb_term->slug, 'single' === $kmcb_group['type'] )
					);
					?>
					<label class="kmcb-opt<?php echo $kmcb_is_on ? ' is-checked' : ''; ?><?php echo $kmcb_is_out ? ' is-empty' : ''; ?>">
						<input type="<?php echo esc_attr( $kmcb_input ); ?>"
							class="kmcb-opt-input"
							name="<?php echo esc_attr( $kmcb_p . 'f[' . $kmcb_group['taxonomy'] . ']' . ( 'checkbox' === $kmcb_input ? '[]' : '' ) ); ?>"
							value="<?php echo esc_attr( $kmcb_term->slug ); ?>"
							data-kmcb-option
							data-kmcb-url="<?php echo esc_url( $kmcb_url ); ?>"
							<?php checked( $kmcb_is_on ); ?>
							<?php disabled( $kmcb_is_out ); ?> />

						<span class="kmcb-opt-text">
							<span class="kmcb-opt-name"><?php echo esc_html( $kmcb_term->name ); ?></span>
							<?php if ( $kmcb_term->description ) : ?>
								<span class="kmcb-opt-sub"><?php echo esc_html( wp_strip_all_tags( $kmcb_term->description ) ); ?></span>
							<?php endif; ?>
						</span>

						<?php if ( $config['filter_counts'] ) : ?>
							<span class="kmcb-opt-count"><?php echo esc_html( $kmcb_count ); ?></span>
						<?php endif; ?>

						<span class="kmcb-opt-mark" aria-hidden="true">
							<svg viewBox="0 0 14 14" focusable="false"><path d="M3.5 7.2 6 9.6l4.5-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
