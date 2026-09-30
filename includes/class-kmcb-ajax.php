<?php
/**
 * AJAX endpoint for filtering.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

class KMCB_Ajax {

	public static function init() {
		add_action( 'wp_ajax_kmcb_filter', array( __CLASS__, 'handle' ) );
		add_action( 'wp_ajax_nopriv_kmcb_filter', array( __CLASS__, 'handle' ) );
	}

	public static function handle() {
		check_ajax_referer( 'kmcb_filter', 'nonce' );

		$post_id   = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$widget_id = isset( $_POST['widget_id'] ) ? sanitize_text_field( wp_unslash( $_POST['widget_id'] ) ) : '';

		$config = KMCB_Config::get( $post_id, $widget_id );

		if ( ! $config ) {
			wp_send_json_error(
				array( 'message' => __( 'This grid needs to be re-saved in Elementor before filtering will work.', 'km-catalog-builder' ) ),
				400
			);
		}

		$config = KMCB_Config::normalize( $config );

		$raw = array();
		foreach ( array( 'f', 'tab', 's', 'orderby', 'view', 'paged', 'append' ) as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized in sanitize_state().
				$raw[ $key ] = wp_unslash( $_POST[ $key ] );
			}
		}

		$state = KMCB_Query::sanitize_state( $raw, $config );
		$query = KMCB_Query::run( $config, $state );

		$max_pages = (int) $query->max_num_pages;

		$response = array(
			// On "load more" the browser only needs the new cards; it updates the
			// button itself from `paged` / `hasMore`.
			'cards'    => $state['append'] ? KMCB_Render::cards( $config, $state, $query ) : '',
			'grid'     => $state['append'] ? '' : KMCB_Render::grid( $config, $state, $query ),
			'sidebar'  => ( $config['show_sidebar'] && ! $state['append'] ) ? KMCB_Render::sidebar( $config, $state ) : '',
			'count'    => KMCB_Render::count_label( $config, $query ),
			'total'    => (int) $query->found_posts,
			'paged'    => (int) $state['paged'],
			'maxPages' => $max_pages,
			'hasMore'  => $state['paged'] < $max_pages,
			'append'   => (bool) $state['append'],
			'view'     => $state['view'],
			'query'    => KMCB_Render::state_to_query( $config, $state ),
		);

		wp_send_json_success( $response );
	}
}
