<?php
/**
 * Elementor widget: Product Button.
 *
 * The call to action under the product details, e.g. "Where to Find This
 * Product". A product may point it somewhere of its own.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-kmcb-button-base.php';

class KMCB_Button_Widget extends KMCB_Button_Base {

	public function get_name() {
		return 'kmcb_product_button';
	}

	public function get_title() {
		return __( 'Product Button', 'km-catalog-builder' );
	}

	public function get_icon() {
		return 'eicon-button';
	}

	public function get_keywords() {
		return array( 'button', 'cta', 'product', 'where to buy', 'stockist' );
	}

	protected function uses_product_override() {
		return true;
	}

	protected function default_text() {
		return __( 'Where to Buy', 'km-catalog-builder' );
	}
}
