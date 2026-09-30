<?php
/**
 * Per-page header settings.
 *
 * A single navbar is usually shared across the whole site through a header
 * template, so "transparent on some pages" has to be decided by the page, not
 * by the widget. This adds that switch to the editor.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

class KMCB_Page_Meta {

	const TRANSPARENT = '_kmcb_nav_transparent';

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_box' ) );
		add_action( 'save_post', array( __CLASS__, 'save' ), 10, 2 );
	}

	/**
	 * Post types that can have a header.
	 */
	private static function post_types() {
		$types = get_post_types(
			array(
				'public'  => true,
				'show_ui' => true,
			),
			'names'
		);

		unset( $types['attachment'] );

		/** Filter the post types offered the transparent header switch. */
		return apply_filters( 'kmcb_transparent_header_post_types', array_values( $types ) );
	}

	public static function add_box() {
		add_meta_box(
			'kmcb_header',
			__( 'Header', 'km-catalog-builder' ),
			array( __CLASS__, 'render_box' ),
			self::post_types(),
			'side',
			'default'
		);
	}

	public static function render_box( $post ) {
		wp_nonce_field( 'kmcb_header_meta', 'kmcb_header_nonce' );

		$on = (bool) get_post_meta( $post->ID, self::TRANSPARENT, true );
		?>
		<p>
			<label>
				<input type="checkbox" name="<?php echo esc_attr( self::TRANSPARENT ); ?>" value="1" <?php checked( $on ); ?> />
				<?php esc_html_e( 'Float the header on this page', 'km-catalog-builder' ); ?>
			</label>
		</p>
		<p class="description">
			<?php esc_html_e( 'The Navbar lifts out of the flow here, so the section below starts at the top of the page and shows around the bar.', 'km-catalog-builder' ); ?>
		</p>
		<?php
	}

	public static function save( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Only act on a real submission of our own box, so quick edits and
		// programmatic saves never clear the value.
		if ( ! isset( $_POST['kmcb_header_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['kmcb_header_nonce'] ) ), 'kmcb_header_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! in_array( $post->post_type, self::post_types(), true ) ) {
			return;
		}

		if ( empty( $_POST[ self::TRANSPARENT ] ) ) {
			delete_post_meta( $post_id, self::TRANSPARENT );
		} else {
			update_post_meta( $post_id, self::TRANSPARENT, 1 );
		}
	}

	/**
	 * Should the header be transparent on whatever is being viewed?
	 */
	public static function is_transparent() {
		$id = 0;

		if ( is_singular() ) {
			$id = get_queried_object_id();
		} elseif ( is_home() ) {
			$id = (int) get_option( 'page_for_posts' );
		}

		$transparent = $id ? (bool) get_post_meta( $id, self::TRANSPARENT, true ) : false;

		/**
		 * Filter whether the current view uses a transparent header.
		 *
		 * @param bool $transparent
		 * @param int  $id
		 */
		return (bool) apply_filters( 'kmcb_header_is_transparent', $transparent, $id );
	}
}
