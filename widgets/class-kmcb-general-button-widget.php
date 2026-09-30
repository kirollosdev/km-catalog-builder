<?php
/**
 * Elementor widget: Button.
 *
 * The same pill as the product button, for use anywhere on the site.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-kmcb-button-base.php';

class KMCB_General_Button_Widget extends KMCB_Button_Base {

	public function get_name() {
		return 'kmcb_button';
	}

	public function get_title() {
		return __( 'Button', 'km-catalog-builder' );
	}

	public function get_icon() {
		return 'eicon-button';
	}

	public function get_keywords() {
		return array( 'button', 'cta', 'link', 'pill' );
	}
}
