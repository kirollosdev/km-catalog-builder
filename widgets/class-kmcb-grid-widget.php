<?php
/**
 * Elementor widget: Products Grid.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

class KMCB_Grid_Widget extends Widget_Base {

	public function get_name() {
		return 'kmcb_product_grid';
	}

	public function get_title() {
		return __( 'Product Grid + Filters', 'km-catalog-builder' );
	}

	public function get_icon() {
		return 'eicon-products';
	}

	public function get_categories() {
		return array( 'km-catalog' );
	}

	public function get_keywords() {
		return array( 'woocommerce', 'products', 'filter', 'facet', 'shop', 'grid', 'catalog' );
	}

	public function get_style_depends() {
		return array( 'kmcb-grid' );
	}

	public function get_script_depends() {
		return array( 'kmcb-grid' );
	}

	/**
	 * Product categories for the source picker.
	 */
	private function category_options() {
		$options = array();
		$terms   = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name;
			}
		}

		return $options;
	}

	private function taxonomy_options() {
		return KMCB_Plugin::get_filterable_taxonomies();
	}

	private function image_size_options() {
		$sizes   = get_intermediate_image_sizes();
		$options = array( 'full' => __( 'Full', 'km-catalog-builder' ) );

		foreach ( $sizes as $size ) {
			$options[ $size ] = ucwords( str_replace( array( '_', '-' ), ' ', $size ) );
		}

		return $options;
	}

	protected function register_controls() {
		$this->register_query_controls();
		$this->register_layout_controls();
		$this->register_toolbar_controls();
		$this->register_filter_controls();
		$this->register_card_controls();

		$this->register_layout_style();
		$this->register_toolbar_style();
		$this->register_sidebar_style();
		$this->register_card_style();
		$this->register_pagination_style();
	}

	/* ---------------------------------------------------------------------
	 * Content
	 * ------------------------------------------------------------------ */

	private function register_query_controls() {
		$this->start_controls_section(
			'section_query',
			array( 'label' => __( 'Products', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'source_cats',
			array(
				'label'       => __( 'Limit to categories', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $this->category_options(),
				'label_block' => true,
				'description' => __( 'Leave empty to show every product in the catalog.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'per_page',
			array(
				'label'   => __( 'Products per page', 'km-catalog-builder' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 12,
				'min'     => 1,
				'max'     => 100,
			)
		);

		$sort_options = array();
		foreach ( KMCB_Query::sort_options() as $key => $option ) {
			$sort_options[ $key ] = $option['label'];
		}

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Default sorting', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'featured',
				'options' => $sort_options,
			)
		);

		$this->add_control(
			'hide_out_of_stock',
			array(
				'label'   => __( 'Hide out of stock', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);

		$this->add_control(
			'pagination',
			array(
				'label'   => __( 'Pagination', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'load_more',
				'options' => array(
					'load_more' => __( 'Load more button', 'km-catalog-builder' ),
					'numbers'   => __( 'Page numbers', 'km-catalog-builder' ),
					'none'      => __( 'None', 'km-catalog-builder' ),
				),
			)
		);

		$this->add_control(
			'load_more_text',
			array(
				'label'     => __( 'Load more text', 'km-catalog-builder' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Load more', 'km-catalog-builder' ),
				'condition' => array( 'pagination' => 'load_more' ),
			)
		);

		$this->add_control(
			'url_sync',
			array(
				'label'       => __( 'Sync filters with the URL', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => __( 'Keeps filters shareable and restores them on reload. Turn off if you use more than one grid on a page.', 'km-catalog-builder' ),
			)
		);

		$this->end_controls_section();
	}

	private function register_layout_controls() {
		$this->start_controls_section(
			'section_layout',
			array( 'label' => __( 'Layout', 'km-catalog-builder' ) )
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns', 'km-catalog-builder' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				),
				'selectors'      => array(
					'{{WRAPPER}} .kmcb' => '--kmcb-cols: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'show_sidebar',
			array(
				'label'   => __( 'Show filter sidebar', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_responsive_control(
			'sidebar_width',
			array(
				'label'      => __( 'Sidebar width', 'km-catalog-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 180,
						'max' => 420,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 270,
				),
				'selectors'  => array(
					'{{WRAPPER}} .kmcb' => '--kmcb-sidebar-w: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array( 'show_sidebar' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'sidebar_gap',
			array(
				'label'     => __( 'Sidebar gap', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 120 ) ),
				'default'   => array( 'size' => 32 ),
				'selectors' => array(
					'{{WRAPPER}} .kmcb' => '--kmcb-sidebar-gap: {{SIZE}}px;',
				),
				'condition' => array( 'show_sidebar' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'column_gap',
			array(
				'label'     => __( 'Column gap', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'   => array( 'size' => 20 ),
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-gap-x: {{SIZE}}px;' ),
			)
		);

		$this->add_responsive_control(
			'row_gap',
			array(
				'label'     => __( 'Row gap', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'default'   => array( 'size' => 26 ),
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-gap-y: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();
	}

	private function register_toolbar_controls() {
		$this->start_controls_section(
			'section_toolbar',
			array( 'label' => __( 'Toolbar', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'tabs_enabled',
			array(
				'label'   => __( 'Show category tabs', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'tabs_tax',
			array(
				'label'     => __( 'Tabs taxonomy', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'product_cat',
				'options'   => $this->taxonomy_options(),
				'condition' => array( 'tabs_enabled' => 'yes' ),
			)
		);

		$this->add_control(
			'tabs_terms',
			array(
				'label'       => __( 'Tabs (term slugs)', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => 'dry-food, wet-food',
				'description' => __( 'Comma-separated slugs, in the order you want them. Leave empty to list every term.', 'km-catalog-builder' ),
				'condition'   => array( 'tabs_enabled' => 'yes' ),
			)
		);

		$this->add_control(
			'tabs_all_label',
			array(
				'label'     => __( '"All" tab label', 'km-catalog-builder' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'All Products', 'km-catalog-builder' ),
				'condition' => array( 'tabs_enabled' => 'yes' ),
			)
		);

		$this->add_control(
			'show_search',
			array(
				'label'     => __( 'Show search', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'search_placeholder',
			array(
				'label'     => __( 'Search placeholder', 'km-catalog-builder' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Search Products', 'km-catalog-builder' ),
				'condition' => array( 'show_search' => 'yes' ),
			)
		);

		$this->add_control(
			'search_position',
			array(
				'label'     => __( 'Search position', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'start',
				'options'   => array(
					'start'  => __( 'Left', 'km-catalog-builder' ),
					'center' => __( 'Centre', 'km-catalog-builder' ),
					'end'    => __( 'Right', 'km-catalog-builder' ),
				),
				'condition' => array( 'show_search' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'search_width',
			array(
				'label'      => __( 'Search width', 'km-catalog-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 180,
						'max' => 900,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 520,
				),
				'selectors'  => array(
					'{{WRAPPER}} .kmcb' => '--kmcb-search-w: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array( 'show_search' => 'yes' ),
			)
		);

		$this->add_control(
			'show_count',
			array(
				'label'     => __( 'Show result count', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'count_single',
			array(
				'label'     => __( 'Singular noun', 'km-catalog-builder' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Product', 'km-catalog-builder' ),
				'condition' => array( 'show_count' => 'yes' ),
			)
		);

		$this->add_control(
			'count_plural',
			array(
				'label'     => __( 'Plural noun', 'km-catalog-builder' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Products', 'km-catalog-builder' ),
				'condition' => array( 'show_count' => 'yes' ),
			)
		);

		$this->add_control(
			'show_sort',
			array(
				'label'     => __( 'Show sorting', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'show_view_toggle',
			array(
				'label'   => __( 'Show grid / list switch', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'default_view',
			array(
				'label'   => __( 'Default view', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => array(
					'grid' => __( 'Grid', 'km-catalog-builder' ),
					'list' => __( 'List', 'km-catalog-builder' ),
				),
			)
		);

		$this->end_controls_section();

		$this->register_label_controls();
	}

	/**
	 * Every word the grid says, in one place.
	 *
	 * A second language is a second copy of the page rather than a second
	 * build, so the wording lives on the widget and not in a translation file.
	 */
	private function register_label_controls() {

		$this->start_controls_section(
			'section_labels',
			array(
				'label' => __( 'Labels', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'labels_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'Leave a field empty to keep the English wording. The product count nouns, the search placeholder, the sidebar title, the card button and the load more text are with their own settings above.', 'km-catalog-builder' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->add_control(
			'filters_btn_label',
			array(
				'label'       => __( 'Filters button, on phones', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Filters', 'km-catalog-builder' ),
				'label_block' => true,
				'condition'   => array( 'show_sidebar' => 'yes' ),
			)
		);

		$this->add_control(
			'sort_label',
			array(
				'label'       => __( 'Sort label', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Sort By:', 'km-catalog-builder' ),
				'label_block' => true,
				'condition'   => array( 'show_sort' => 'yes' ),
			)
		);

		$this->add_control(
			'sort_options_heading',
			array(
				'label'     => __( 'Sort options', 'km-catalog-builder' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'show_sort' => 'yes' ),
			)
		);

		foreach ( KMCB_Query::sort_options() as $key => $option ) {
			$this->add_control(
				'sort_label_' . str_replace( '-', '_', $key ),
				array(
					'label'       => $option['label'],
					'type'        => Controls_Manager::TEXT,
					'placeholder' => $option['label'],
					'label_block' => true,
					'condition'   => array( 'show_sort' => 'yes' ),
				)
			);
		}

		$this->add_control(
			'empty_heading',
			array(
				'label'     => __( 'When nothing matches', 'km-catalog-builder' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'empty_title',
			array(
				'label'       => __( 'Message', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'No products match your filters.', 'km-catalog-builder' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'empty_reset',
			array(
				'label'       => __( 'Reset link', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Clear all filters', 'km-catalog-builder' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'apply_label',
			array(
				'label'       => __( 'Apply button, without JavaScript', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Apply filters', 'km-catalog-builder' ),
				'label_block' => true,
				'separator'   => 'before',
				'description' => __( 'Only seen by visitors with JavaScript off, and by search engines.', 'km-catalog-builder' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Collect the per-option sort wording.
	 *
	 * The controls are flat, one per sort key, because a repeater would let
	 * the same option be given two labels. They are gathered back into a map
	 * keyed by sort key here.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	private function sort_labels( array $settings ) {
		$labels = array();

		foreach ( array_keys( KMCB_Query::sort_options() ) as $key ) {
			$control = 'sort_label_' . str_replace( '-', '_', $key );

			if ( ! empty( $settings[ $control ] ) ) {
				$labels[ $key ] = (string) $settings[ $control ];
			}
		}

		return $labels;
	}

	private function register_filter_controls() {
		$this->start_controls_section(
			'section_filters',
			array(
				'label'     => __( 'Filters', 'km-catalog-builder' ),
				'condition' => array( 'show_sidebar' => 'yes' ),
			)
		);

		$this->add_control(
			'sidebar_title',
			array(
				'label'   => __( 'Sidebar title', 'km-catalog-builder' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Filters', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'clear_label',
			array(
				'label'   => __( '"Clear" label', 'km-catalog-builder' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Clear All', 'km-catalog-builder' ),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'taxonomy',
			array(
				'label'   => __( 'Taxonomy', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->taxonomy_options(),
				'default' => 'product_cat',
			)
		);

		$repeater->add_control(
			'label',
			array(
				'label'       => __( 'Heading', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Leave empty to use the taxonomy name', 'km-catalog-builder' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'type',
			array(
				'label'   => __( 'Selection', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'multi',
				'options' => array(
					'multi'  => __( 'Multiple (checkboxes)', 'km-catalog-builder' ),
					'single' => __( 'One only (radio)', 'km-catalog-builder' ),
				),
			)
		);

		$repeater->add_control(
			'include',
			array(
				'label'       => __( 'Only these terms', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => 'adult, kitten, sterilised',
				'description' => __( 'Comma-separated slugs, in display order. Empty = all terms.', 'km-catalog-builder' ),
			)
		);

		$repeater->add_control(
			'exclude',
			array(
				'label'       => __( 'Exclude terms', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'collapsible',
			array(
				'label'   => __( 'Collapsible', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$repeater->add_control(
			'open',
			array(
				'label'   => __( 'Open by default', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'filters',
			array(
				'label'       => __( 'Filter groups', 'km-catalog-builder' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ label || taxonomy }}}',
				'default'     => array(),
				'description' => __( 'Leave empty and the grid will build one group per product attribute automatically.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'filter_counts',
			array(
				'label'   => __( 'Show product counts', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);

		$this->add_control(
			'hide_empty_terms',
			array(
				'label'       => __( 'Hide options with no results', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => '',
				'description' => __( 'Off: unavailable options stay visible but greyed out.', 'km-catalog-builder' ),
			)
		);

		$this->end_controls_section();
	}

	private function register_card_controls() {
		$this->start_controls_section(
			'section_card',
			array( 'label' => __( 'Card', 'km-catalog-builder' ) )
		);

		$this->add_control(
			'badge_tax',
			array(
				'label'       => __( 'Badge taxonomy', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array( '' => __( 'None', 'km-catalog-builder' ) ) + $this->taxonomy_options(),
				'default'     => '',
				'description' => __( 'The pill on the top-right of the image, e.g. Life Stage.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'eyebrow_tax',
			array(
				'label'       => __( 'Small label taxonomy', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array( '' => __( 'None', 'km-catalog-builder' ) ) + $this->taxonomy_options(),
				'default'     => 'product_cat',
				'description' => __( 'The uppercase line above the product name.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => __( 'Link text', 'km-catalog-builder' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'View Product', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'card_arrow_dir',
			array(
				'label'       => __( 'Arrow points', 'km-catalog-builder' ),
				'type'        => Controls_Manager::CHOOSE,
				'default'     => 'right',
				'options'     => array(
					'left'  => array(
						'title' => __( 'Left', 'km-catalog-builder' ),
						'icon'  => 'eicon-arrow-left',
					),
					'right' => array(
						'title' => __( 'Right', 'km-catalog-builder' ),
						'icon'  => 'eicon-arrow-right',
					),
				),
				'toggle'      => false,
				'description' => __( 'A left arrow also moves to the other side of the text, so it still sits at the end of it in Arabic.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'show_price',
			array(
				'label'   => __( 'Show price', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);

		$this->add_control(
			'image_size',
			array(
				'label'   => __( 'Image size', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'woocommerce_thumbnail',
				'options' => $this->image_size_options(),
			)
		);

		$this->add_control(
			'card_bg_style',
			array(
				'label'       => __( 'Second colour style', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'wave',
				'options'     => array(
					'wave'  => __( 'Curved wave', 'km-catalog-builder' ),
					'blend' => __( 'Gradient blend', 'km-catalog-builder' ),
					'flat'  => __( 'Ignore it', 'km-catalog-builder' ),
				),
				'description' => __( 'How a card uses its second colour. The wave sits along the bottom and rises to fill the card on hover.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'wave_height',
			array(
				'label'     => __( 'Wave height', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( '%' => array( 'min' => 0, 'max' => 100 ) ),
				'default'   => array(
					'unit' => '%',
					'size' => 34,
				),
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-wave-h: {{SIZE}}%;' ),
				'condition' => array( 'card_bg_style' => 'wave' ),
			)
		);

		$this->add_control(
			'wave_curve',
			array(
				'label'       => __( 'Wave curve', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array( '%' => array( 'min' => 0, 'max' => 80 ) ),
				'default'     => array(
					'unit' => '%',
					'size' => 26,
				),
				'selectors'   => array( '{{WRAPPER}} .kmcb' => '--kmcb-wave-curve: {{SIZE}}%;' ),
				'description' => __( '0 is a straight line across.', 'km-catalog-builder' ),
				'condition'   => array( 'card_bg_style' => 'wave' ),
			)
		);

		$this->add_control(
			'gradient_angle',
			array(
				'label'       => __( 'Blend angle', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array( 'px' => array( 'min' => 0, 'max' => 360 ) ),
				'default'     => array( 'size' => 160 ),
				'selectors'   => array( '{{WRAPPER}} .kmcb' => '--kmcb-blend-angle: {{SIZE}}deg;' ),
				'description' => __( '180° runs top to bottom.', 'km-catalog-builder' ),
				'condition'   => array( 'card_bg_style' => 'blend' ),
			)
		);

		$this->add_control(
			'image_zoom',
			array(
				'label'       => __( 'Image zoom on hover', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array( '%' => array( 'min' => 100, 'max' => 130 ) ),
				'default'     => array(
					'unit' => '%',
					'size' => 106,
				),
				'selectors'   => array( '{{WRAPPER}} .kmcb' => '--kmcb-img-zoom: calc({{SIZE}} / 100);' ),
				'description' => __( '100% turns the zoom off.', 'km-catalog-builder' ),
			)
		);

		$palette = new Repeater();

		$palette->add_control(
			'color',
			array(
				'label'   => __( 'Colour', 'km-catalog-builder' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '#F3F1EC',
			)
		);

		$palette->add_control(
			'color2',
			array(
				'label'       => __( 'Second colour', 'km-catalog-builder' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '',
				'description' => __( 'Optional. Leave empty for a flat colour.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'palette',
			array(
				'label'       => __( 'Card background palette', 'km-catalog-builder' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $palette->get_controls(),
				'title_field' => '{{{ color }}}',
				'default'     => array(
					array(
						'color'  => '#FBE3C7',
						'color2' => '#F6D3AE',
					),
					array(
						'color'  => '#D6EAF6',
						'color2' => '#BEDCF0',
					),
					array(
						'color'  => '#F7DCE8',
						'color2' => '#EFC7DA',
					),
					array(
						'color'  => '#DCEDE1',
						'color2' => '#C4E1CD',
					),
					array(
						'color'  => '#FBE9D2',
						'color2' => '#F3D8B8',
					),
					array(
						'color'  => '#DDE9F7',
						'color2' => '#C6D9EE',
					),
				),
				'description' => __( 'Used only for products with no colour of their own. Each product keeps the same palette entry regardless of sorting or filtering.', 'km-catalog-builder' ),
			)
		);

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Style
	 * ------------------------------------------------------------------ */

	private function register_layout_style() {
		$this->start_controls_section(
			'style_general',
			array(
				'label' => __( 'General', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'accent_color',
			array(
				'label'     => __( 'Accent colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-accent: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'ink_color',
			array(
				'label'     => __( 'Text colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-ink: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'muted_color',
			array(
				'label'     => __( 'Muted text colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#9AA2A6',
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-muted: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'text_scale',
			array(
				'label'       => __( 'Text size', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array( '%' => array( 'min' => 80, 'max' => 160 ) ),
				'default'     => array(
					'unit' => '%',
					'size' => 100,
				),
				'selectors'   => array( '{{WRAPPER}} .kmcb' => '--kmcb-fs: calc({{SIZE}} / 100);' ),
				'description' => __( 'Scales every text size in the grid at once. Individual typography controls still override it.', 'km-catalog-builder' ),
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'line_color',
			array(
				'label'     => __( 'Divider colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#EAEAEA',
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-line: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	private function register_toolbar_style() {
		$this->start_controls_section(
			'style_toolbar',
			array(
				'label' => __( 'Toolbar', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'tab_typography',
				'selector' => '{{WRAPPER}} .kmcb-tab',
			)
		);

		$this->add_control(
			'tab_color',
			array(
				'label'     => __( 'Tab colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-tab' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'tab_active_color',
			array(
				'label'     => __( 'Active tab colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .kmcb-tab.is-active' => 'color: {{VALUE}}; border-bottom-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'meta_typography',
				'label'     => __( 'Count & sort typography', 'km-catalog-builder' ),
				'selector'  => '{{WRAPPER}} .kmcb-count, {{WRAPPER}} .kmcb-sort',
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'search_typography',
				'label'     => __( 'Search typography', 'km-catalog-builder' ),
				'selector'  => '{{WRAPPER}} .kmcb-search-input, {{WRAPPER}} .kmcb-search-input::placeholder',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'search_color',
			array(
				'label'     => __( 'Search text colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-search-input' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'search_placeholder_color',
			array(
				'label'     => __( 'Search placeholder colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-search-input::placeholder' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'toolbar_border',
			array(
				'label'     => __( 'Bottom border colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-toolbar' => 'border-bottom-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	private function register_sidebar_style() {
		$this->start_controls_section(
			'style_sidebar',
			array(
				'label'     => __( 'Sidebar', 'km-catalog-builder' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_sidebar' => 'yes' ),
			)
		);

		$this->add_control(
			'panel_bg',
			array(
				'label'     => __( 'Background', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F7F5F0',
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-panel-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'option_line_color',
			array(
				'label'     => __( 'Row divider', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#E4E0D6',
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-opt-line: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'panel_border',
				'selector' => '{{WRAPPER}} .kmcb-filters',
			)
		);

		$this->add_control(
			'panel_radius',
			array(
				'label'      => __( 'Border radius', 'km-catalog-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'size' => 14 ),
				'selectors'  => array( '{{WRAPPER}} .kmcb' => '--kmcb-panel-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_responsive_control(
			'panel_padding',
			array(
				'label'      => __( 'Padding', 'km-catalog-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .kmcb-filters' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'panel_shadow',
				'selector' => '{{WRAPPER}} .kmcb-filters',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'filters_title_typography',
				'label'     => __( '"Filters" heading', 'km-catalog-builder' ),
				'selector'  => '{{WRAPPER}} .kmcb-filters-title',
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'clear_typography',
				'label'    => __( '"Clear All" link', 'km-catalog-builder' ),
				'selector' => '{{WRAPPER}} .kmcb-clear',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'group_typography',
				'label'    => __( 'Group heading', 'km-catalog-builder' ),
				'selector' => '{{WRAPPER}} .kmcb-group-head',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'option_typography',
				'label'    => __( 'Option label', 'km-catalog-builder' ),
				'selector' => '{{WRAPPER}} .kmcb-opt-name',
			)
		);

		$this->add_control(
			'filter_colors_heading',
			array(
				'label'     => __( 'Colours', 'km-catalog-builder' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'filters_title_color',
			array(
				'label'     => __( '"Filters" heading', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-filters-title: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'clear_color',
			array(
				'label'     => __( '"Clear All" link', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-clear-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'group_color',
			array(
				'label'     => __( 'Group heading', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-group-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'option_color',
			array(
				'label'     => __( 'Option label', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-option-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'option_sub_color',
			array(
				'label'     => __( 'Option description', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-option-sub-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'check_border_color',
			array(
				'label'     => __( 'Circle outline', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-check-border: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'check_bg_color',
			array(
				'label'     => __( 'Circle when selected', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-check-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'check_tick_color',
			array(
				'label'     => __( 'Tick', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-check-tick: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	private function register_card_style() {
		$this->start_controls_section(
			'style_card',
			array(
				'label' => __( 'Card', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'card_radius',
			array(
				'label'     => __( 'Image box radius', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 48 ) ),
				'default'   => array( 'size' => 16 ),
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-card-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'card_ratio',
			array(
				'label'     => __( 'Image box ratio', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( '%' => array( 'min' => 50, 'max' => 160, 'step' => 1 ) ),
				'default'   => array(
					'unit' => '%',
					'size' => 100,
				),
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-card-ratio: calc({{SIZE}} / 100);' ),
			)
		);

		$this->add_responsive_control(
			'card_media_padding',
			array(
				'label'      => __( 'Image padding', 'km-catalog-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .kmcb-card-media' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'badge_heading',
			array(
				'label'     => __( 'Badge', 'km-catalog-builder' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'badge_bg',
			array(
				'label'     => __( 'Background', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-badge-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'badge_color',
			array(
				'label'     => __( 'Text', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb' => '--kmcb-badge-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'badge_typography',
				'selector' => '{{WRAPPER}} .kmcb-badge',
			)
		);

		$this->add_control(
			'text_heading',
			array(
				'label'     => __( 'Text', 'km-catalog-builder' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'eyebrow_typography',
				'label'    => __( 'Small label', 'km-catalog-builder' ),
				'selector' => '{{WRAPPER}} .kmcb-eyebrow',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => __( 'Product name', 'km-catalog-builder' ),
				'selector' => '{{WRAPPER}} .kmcb-title',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Product name colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-title a' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'more_typography',
				'label'    => __( 'Link', 'km-catalog-builder' ),
				'selector' => '{{WRAPPER}} .kmcb-more',
			)
		);

		$this->add_control(
			'more_color',
			array(
				'label'     => __( 'Link colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-more' => 'color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	private function register_pagination_style() {
		$this->start_controls_section(
			'style_pagination',
			array(
				'label' => __( 'Load More & Pages', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'load_more_typography',
				'label'    => __( 'Load more typography', 'km-catalog-builder' ),
				'selector' => '{{WRAPPER}} .kmcb-load-more',
			)
		);

		$this->start_controls_tabs( 'load_more_states' );

		foreach ( array(
			'normal' => array( __( 'Normal', 'km-catalog-builder' ), '{{WRAPPER}} .kmcb-load-more' ),
			'hover'  => array( __( 'Hover', 'km-catalog-builder' ), '{{WRAPPER}} .kmcb-load-more:hover' ),
		) as $state => $info ) {
			$this->start_controls_tab( 'load_more_' . $state, array( 'label' => $info[0] ) );

			$this->add_control(
				'load_more_' . $state . '_color',
				array(
					'label'     => __( 'Text colour', 'km-catalog-builder' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $info[1] => 'color: {{VALUE}};' ),
				)
			);

			$this->add_control(
				'load_more_' . $state . '_bg',
				array(
					'label'     => __( 'Background', 'km-catalog-builder' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $info[1] => 'background-color: {{VALUE}};' ),
				)
			);

			$this->add_control(
				'load_more_' . $state . '_border',
				array(
					'label'     => __( 'Border colour', 'km-catalog-builder' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $info[1] => 'border-color: {{VALUE}};' ),
				)
			);

			$this->end_controls_tab();
		}

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'page_typography',
				'label'     => __( 'Page numbers typography', 'km-catalog-builder' ),
				'selector'  => '{{WRAPPER}} .kmcb-page',
				'separator' => 'before',
			)
		);

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Render
	 * ------------------------------------------------------------------ */

	/**
	 * Turn Elementor settings into the plain config array the rest of the
	 * plugin works with.
	 */
	private function build_config( array $settings ) {
		$palette = array();
		if ( ! empty( $settings['palette'] ) && is_array( $settings['palette'] ) ) {
			foreach ( $settings['palette'] as $item ) {
				if ( ! empty( $item['color'] ) ) {
					$palette[] = array(
						'from' => $item['color'],
						'to'   => isset( $item['color2'] ) ? $item['color2'] : '',
					);
				}
			}
		}

		$filters = array();
		if ( ! empty( $settings['filters'] ) && is_array( $settings['filters'] ) ) {
			foreach ( $settings['filters'] as $item ) {
				$filters[] = array(
					'taxonomy'    => isset( $item['taxonomy'] ) ? $item['taxonomy'] : '',
					'label'       => isset( $item['label'] ) ? $item['label'] : '',
					'type'        => isset( $item['type'] ) ? $item['type'] : 'multi',
					'include'     => $this->split_slugs( isset( $item['include'] ) ? $item['include'] : '' ),
					'exclude'     => $this->split_slugs( isset( $item['exclude'] ) ? $item['exclude'] : '' ),
					'collapsible' => 'yes' === ( isset( $item['collapsible'] ) ? $item['collapsible'] : 'yes' ),
					'open'        => 'yes' === ( isset( $item['open'] ) ? $item['open'] : 'yes' ),
				);
			}
		}

		$columns = isset( $settings['columns'] ) ? (int) $settings['columns'] : 3;

		return array(
			'source_cats'        => isset( $settings['source_cats'] ) ? (array) $settings['source_cats'] : array(),
			'per_page'           => isset( $settings['per_page'] ) ? (int) $settings['per_page'] : 12,
			'columns'            => $columns,
			'orderby'            => isset( $settings['orderby'] ) ? $settings['orderby'] : 'featured',
			'hide_out_of_stock'  => 'yes' === ( isset( $settings['hide_out_of_stock'] ) ? $settings['hide_out_of_stock'] : '' ),
			'tabs_enabled'       => 'yes' === ( isset( $settings['tabs_enabled'] ) ? $settings['tabs_enabled'] : '' ),
			'tabs_tax'           => isset( $settings['tabs_tax'] ) ? $settings['tabs_tax'] : 'product_cat',
			'tabs_terms'         => $this->split_slugs( isset( $settings['tabs_terms'] ) ? $settings['tabs_terms'] : '' ),
			'tabs_all_label'     => isset( $settings['tabs_all_label'] ) ? $settings['tabs_all_label'] : '',
			'show_search'        => 'yes' === ( isset( $settings['show_search'] ) ? $settings['show_search'] : '' ),
			'search_placeholder' => isset( $settings['search_placeholder'] ) ? $settings['search_placeholder'] : '',
			'search_position'    => in_array( isset( $settings['search_position'] ) ? $settings['search_position'] : '', array( 'start', 'center', 'end' ), true ) ? $settings['search_position'] : 'start',
			'show_count'         => 'yes' === ( isset( $settings['show_count'] ) ? $settings['show_count'] : '' ),
			'count_single'       => isset( $settings['count_single'] ) ? $settings['count_single'] : '',
			'count_plural'       => isset( $settings['count_plural'] ) ? $settings['count_plural'] : '',
			'show_sort'          => 'yes' === ( isset( $settings['show_sort'] ) ? $settings['show_sort'] : '' ),
			'sort_label'         => isset( $settings['sort_label'] ) ? $settings['sort_label'] : '',
			'sort_labels'        => $this->sort_labels( $settings ),
			'empty_title'        => isset( $settings['empty_title'] ) ? $settings['empty_title'] : '',
			'empty_reset'        => isset( $settings['empty_reset'] ) ? $settings['empty_reset'] : '',
			'filters_btn_label'  => isset( $settings['filters_btn_label'] ) ? $settings['filters_btn_label'] : '',
			'apply_label'        => isset( $settings['apply_label'] ) ? $settings['apply_label'] : '',
			'show_view_toggle'   => 'yes' === ( isset( $settings['show_view_toggle'] ) ? $settings['show_view_toggle'] : '' ),
			'default_view'       => isset( $settings['default_view'] ) ? $settings['default_view'] : 'grid',
			'show_sidebar'       => 'yes' === ( isset( $settings['show_sidebar'] ) ? $settings['show_sidebar'] : '' ),
			'sidebar_title'      => isset( $settings['sidebar_title'] ) ? $settings['sidebar_title'] : '',
			'clear_label'        => isset( $settings['clear_label'] ) ? $settings['clear_label'] : '',
			'filters'            => $filters,
			'auto_filters'       => empty( $filters ),
			'filter_counts'      => 'yes' === ( isset( $settings['filter_counts'] ) ? $settings['filter_counts'] : '' ),
			'hide_empty_terms'   => 'yes' === ( isset( $settings['hide_empty_terms'] ) ? $settings['hide_empty_terms'] : '' ),
			'badge_tax'          => isset( $settings['badge_tax'] ) ? $settings['badge_tax'] : '',
			'eyebrow_tax'        => isset( $settings['eyebrow_tax'] ) ? $settings['eyebrow_tax'] : 'product_cat',
			'button_text'        => isset( $settings['button_text'] ) ? $settings['button_text'] : '',
			'card_arrow_dir'     => isset( $settings['card_arrow_dir'] ) ? $settings['card_arrow_dir'] : 'right',
			'show_price'         => 'yes' === ( isset( $settings['show_price'] ) ? $settings['show_price'] : '' ),
			'image_size'         => isset( $settings['image_size'] ) ? $settings['image_size'] : 'woocommerce_thumbnail',
			'palette'            => $palette,
			'gradient_angle'     => isset( $settings['gradient_angle']['size'] ) ? (int) $settings['gradient_angle']['size'] : 160,
			'card_bg_style'      => isset( $settings['card_bg_style'] ) ? $settings['card_bg_style'] : 'wave',
			'pagination'         => isset( $settings['pagination'] ) ? $settings['pagination'] : 'load_more',
			'load_more_text'     => isset( $settings['load_more_text'] ) ? $settings['load_more_text'] : '',
			'url_sync'           => 'yes' === ( isset( $settings['url_sync'] ) ? $settings['url_sync'] : '' ),
		);
	}

	private function split_slugs( $value ) {
		if ( is_array( $value ) ) {
			return array_values( array_filter( array_map( 'sanitize_title', $value ) ) );
		}

		if ( '' === trim( (string) $value ) ) {
			return array();
		}

		return array_values( array_filter( array_map( 'sanitize_title', explode( ',', (string) $value ) ) ) );
	}

	/**
	 * The document this widget lives in — that is where its config is stored.
	 */
	private function current_post_id() {
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$document = \Elementor\Plugin::$instance->documents->get_current();

			if ( $document ) {
				return $document->get_main_id();
			}
		}

		// Outside a single post the config is stored in an option instead.
		return is_singular() ? (int) get_the_ID() : 0;
	}

	protected function render() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$config              = $this->build_config( $this->get_settings_for_display() );
		$config['post_id']   = $this->current_post_id();
		$config['widget_id'] = $this->get_id();

		$config = KMCB_Config::normalize( $config );

		KMCB_Config::save( $config['post_id'], $config['widget_id'], $config );

		$state = KMCB_Render::state_from_request( $config );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside templates.
		echo KMCB_Render::widget( $config, $state );
	}
}
