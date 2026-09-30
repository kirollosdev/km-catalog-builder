<?php
/**
 * Toolbar: category tabs, search, result count, sort, view switch.
 *
 * Laid out as three columns so the search sits dead centre whether or not
 * there are tabs on the left.
 *
 * @var array    $config
 * @var array    $state
 * @var WP_Query $query
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

$kmcb_p = KMCB_Render::PREFIX;

$kmcb_tab_terms = array();

if ( $config['tabs_enabled'] && taxonomy_exists( $config['tabs_tax'] ) ) {
	$kmcb_tab_terms = KMCB_Query::get_group_terms(
		array(
			'taxonomy' => $config['tabs_tax'],
			'include'  => $config['tabs_terms'],
			'exclude'  => array(),
		),
		$config
	);
}
?>
<div class="kmcb-toolbar kmcb-toolbar--search-<?php echo esc_attr( $config['search_position'] ); ?><?php echo $kmcb_tab_terms ? '' : ' kmcb-toolbar--no-tabs'; ?>" data-kmcb-toolbar>

	<input type="hidden" name="<?php echo esc_attr( $kmcb_p . 'tab' ); ?>" value="<?php echo esc_attr( $state['tab'] ); ?>" data-kmcb-input="tab" />
	<input type="hidden" name="<?php echo esc_attr( $kmcb_p . 'view' ); ?>" value="<?php echo esc_attr( $state['view'] ); ?>" data-kmcb-input="view" />

	<div class="kmcb-toolbar-start">
		<?php if ( $kmcb_tab_terms ) : ?>
			<nav class="kmcb-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Product categories', 'km-catalog-builder' ); ?>">
				<?php
				$kmcb_all_label = $config['tabs_all_label'] ? $config['tabs_all_label'] : __( 'All', 'km-catalog-builder' );
				$kmcb_tabs      = array( '' => $kmcb_all_label );

				foreach ( $kmcb_tab_terms as $kmcb_term ) {
					$kmcb_tabs[ $kmcb_term->slug ] = $kmcb_term->name;
				}

				foreach ( $kmcb_tabs as $kmcb_slug => $kmcb_label ) :
					$kmcb_active = $state['tab'] === $kmcb_slug;
					?>
					<a class="kmcb-tab<?php echo $kmcb_active ? ' is-active' : ''; ?>"
						href="<?php echo esc_url( KMCB_Render::url_for_state( $config, KMCB_Render::with( $state, 'tab', $kmcb_slug ) ) ); ?>"
						data-kmcb-tab="<?php echo esc_attr( $kmcb_slug ); ?>"
						role="tab"
						aria-selected="<?php echo $kmcb_active ? 'true' : 'false'; ?>"><?php echo esc_html( $kmcb_label ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</div>

	<?php if ( $config['show_search'] ) : ?>
		<div class="kmcb-search">
			<svg class="kmcb-search-icon" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><circle cx="7" cy="7" r="5" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M10.8 10.8 14 14" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
			<label class="screen-reader-text" for="kmcb-search-<?php echo esc_attr( $config['widget_id'] ); ?>"><?php esc_html_e( 'Search products', 'km-catalog-builder' ); ?></label>
			<input type="search"
				id="kmcb-search-<?php echo esc_attr( $config['widget_id'] ); ?>"
				class="kmcb-search-input"
				name="<?php echo esc_attr( $kmcb_p . 's' ); ?>"
				value="<?php echo esc_attr( $state['s'] ); ?>"
				placeholder="<?php echo esc_attr( $config['search_placeholder'] ? $config['search_placeholder'] : __( 'Search products', 'km-catalog-builder' ) ); ?>"
				data-kmcb-search
				autocomplete="off" />
		</div>
	<?php else : ?>
		<div class="kmcb-search kmcb-search--empty"></div>
	<?php endif; ?>

	<div class="kmcb-toolbar-end">

		<?php if ( $config['show_sidebar'] ) : ?>
			<button type="button" class="kmcb-mobile-filters" data-kmcb-mobile-toggle aria-expanded="false">
				<svg viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M2 4h12M4 8h8M6.5 12h3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
				<?php echo esc_html( $config['filters_btn_label'] ? $config['filters_btn_label'] : __( 'Filters', 'km-catalog-builder' ) ); ?>
			</button>
		<?php endif; ?>

		<?php if ( $config['show_count'] ) : ?>
			<span class="kmcb-count" data-kmcb-count><?php echo esc_html( KMCB_Render::count_label( $config, $query ) ); ?></span>
		<?php endif; ?>

		<?php if ( $config['show_count'] && $config['show_sort'] ) : ?>
			<span class="kmcb-toolbar-rule" aria-hidden="true"></span>
		<?php endif; ?>

		<?php if ( $config['show_sort'] ) : ?>
			<div class="kmcb-sort">
				<label class="kmcb-sort-label" for="kmcb-sort-<?php echo esc_attr( $config['widget_id'] ); ?>"><?php echo esc_html( $config['sort_label'] ? $config['sort_label'] : __( 'Sort By:', 'km-catalog-builder' ) ); ?></label>
				<select id="kmcb-sort-<?php echo esc_attr( $config['widget_id'] ); ?>" class="kmcb-sort-select" name="<?php echo esc_attr( $kmcb_p . 'orderby' ); ?>" data-kmcb-sort>
					<?php foreach ( KMCB_Query::sort_options_for( $config ) as $kmcb_key => $kmcb_option ) : ?>
						<option value="<?php echo esc_attr( $kmcb_key ); ?>" <?php selected( $state['orderby'], $kmcb_key ); ?>><?php echo esc_html( $kmcb_option['label'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		<?php endif; ?>

		<?php if ( $config['show_view_toggle'] ) : ?>
			<div class="kmcb-views" role="group" aria-label="<?php esc_attr_e( 'Layout', 'km-catalog-builder' ); ?>">
				<button type="button" class="kmcb-view<?php echo 'grid' === $state['view'] ? ' is-active' : ''; ?>" data-kmcb-view="grid" aria-label="<?php esc_attr_e( 'Grid view', 'km-catalog-builder' ); ?>" aria-pressed="<?php echo 'grid' === $state['view'] ? 'true' : 'false'; ?>">
					<svg viewBox="0 0 16 16" aria-hidden="true" focusable="false"><rect x="2" y="2" width="5" height="5" rx="1"/><rect x="9" y="2" width="5" height="5" rx="1"/><rect x="2" y="9" width="5" height="5" rx="1"/><rect x="9" y="9" width="5" height="5" rx="1"/></svg>
				</button>
				<button type="button" class="kmcb-view<?php echo 'list' === $state['view'] ? ' is-active' : ''; ?>" data-kmcb-view="list" aria-label="<?php esc_attr_e( 'List view', 'km-catalog-builder' ); ?>" aria-pressed="<?php echo 'list' === $state['view'] ? 'true' : 'false'; ?>">
					<svg viewBox="0 0 16 16" aria-hidden="true" focusable="false"><rect x="2" y="3" width="12" height="2" rx="1"/><rect x="2" y="7" width="12" height="2" rx="1"/><rect x="2" y="11" width="12" height="2" rx="1"/></svg>
				</button>
			</div>
		<?php endif; ?>

	</div>
</div>
