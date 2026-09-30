<?php
/**
 * Elementor widget: FAQ.
 *
 * The same accordion as the product one, filled from questions written into the
 * widget, with optional FAQ structured data.
 *
 * @package KM_Catalog_Builder
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;

class KMCB_Faq_Widget extends Widget_Base {

	public function get_name() {
		return 'kmcb_faq';
	}

	public function get_title() {
		return __( 'FAQ', 'km-catalog-builder' );
	}

	public function get_icon() {
		return 'eicon-help-o';
	}

	public function get_categories() {
		return array( 'km-catalog' );
	}

	public function get_keywords() {
		return array( 'faq', 'questions', 'accordion', 'toggle', 'answers' );
	}

	public function get_style_depends() {
		return array( 'kmcb-grid' );
	}

	public function get_script_depends() {
		return array( 'kmcb-grid' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_faq',
			array( 'label' => __( 'Questions', 'km-catalog-builder' ) )
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'question',
			array(
				'label'       => __( 'Question', 'km-catalog-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'How much should my cat eat per day?', 'km-catalog-builder' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'answer',
			array(
				'label'   => __( 'Answer', 'km-catalog-builder' ),
				'type'    => Controls_Manager::WYSIWYG,
				'default' => __( 'The recommended daily amount depends on your cat’s weight and the specific product.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Questions', 'km-catalog-builder' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ question }}}',
				'default'     => array(
					array(
						'question' => __( 'How much should my cat eat per day?', 'km-catalog-builder' ),
						'answer'   => __( 'The recommended daily amount depends on your cat’s weight and the specific product.', 'km-catalog-builder' ),
					),
				),
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'cards',
				'options' => array(
					'cards'  => __( 'Separate cards', 'km-catalog-builder' ),
					'joined' => __( 'One joined block', 'km-catalog-builder' ),
				),
			)
		);

		$this->add_control(
			'icon',
			array(
				'label'   => __( 'Toggle', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'circle',
				'options' => array(
					'circle' => __( 'Chevron in a circle', 'km-catalog-builder' ),
					'plain'  => __( 'Plain chevron', 'km-catalog-builder' ),
				),
			)
		);

		$this->add_control(
			'open_first',
			array(
				'label'   => __( 'Open the first question', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);

		$this->add_control(
			'one_at_a_time',
			array(
				'label'   => __( 'Only one open at a time', 'km-catalog-builder' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'schema',
			array(
				'label'       => __( 'Add FAQ structured data', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => __( 'Helps search engines show these questions. Use it on one FAQ block per page only.', 'km-catalog-builder' ),
			)
		);

		$this->end_controls_section();

		/* ------------------------------------------------------------- Style */

		$this->start_controls_section(
			'style_faq',
			array(
				'label' => __( 'Style', 'km-catalog-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'faq_align',
			array(
				'label'       => __( 'Question alignment', 'km-catalog-builder' ),
				'type'        => Controls_Manager::CHOOSE,
				'options'     => array(
					'start'  => array(
						'title' => __( 'Start of the line', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Centre', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-center',
					),
					'end'    => array(
						'title' => __( 'End of the line', 'km-catalog-builder' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'     => 'start',
				'selectors'   => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-align: {{VALUE}};' ),
				'description' => __( 'Start and end follow the language: on an Arabic page start is the right.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'faq_bg',
			array(
				'label'     => __( 'Card background', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'faq_line',
			array(
				'label'     => __( 'Card border', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ECECEC',
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-line: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'faq_radius',
			array(
				'label'       => __( 'Card corner radius', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'     => array( 'size' => 12 ),
				'selectors'   => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-radius: {{SIZE}}px;' ),
				'description' => __( 'Each question. The whole block has its own radius below.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'faq_gap',
			array(
				'label'     => __( 'Gap between cards', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'   => array( 'size' => 14 ),
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-gap: {{SIZE}}px;' ),
				'condition' => array( 'layout' => 'cards' ),
			)
		);

		$this->add_responsive_control(
			'faq_head_padding',
			array(
				'label'      => __( 'Question padding', 'km-catalog-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'top'      => 20,
					'right'    => 24,
					'bottom'   => 20,
					'left'     => 24,
					'unit'     => 'px',
					'isLinked' => false,
				),
				'selectors'  => array(
					'{{WRAPPER}} .kmcb-acc-head' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'faq_question_typography',
				'label'    => __( 'Question', 'km-catalog-builder' ),
				'selector' => '{{WRAPPER}} .kmcb-acc-title',
			)
		);

		$this->add_control(
			'faq_question_color',
			array(
				'label'     => __( 'Question colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-title: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'faq_answer_typography',
				'label'    => __( 'Answer', 'km-catalog-builder' ),
				'selector' => '{{WRAPPER}} .kmcb-acc-content',
			)
		);

		$this->add_control(
			'faq_answer_color',
			array(
				'label'     => __( 'Answer colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#5B6B52',
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-body: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'faq_outer_heading',
			array(
				'label'     => __( 'Whole widget', 'km-catalog-builder' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'faq_outer_radius',
			array(
				'label'       => __( 'Corner radius', 'km-catalog-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'   => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-outer-radius: {{SIZE}}px;' ),
				'description' => __( 'Rounds the outside of the whole block. Leave empty to follow the card radius.', 'km-catalog-builder' ),
			)
		);

		$this->add_control(
			'faq_outer_bg',
			array(
				'label'     => __( 'Background', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-outer-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'faq_outer_line',
			array(
				'label'     => __( 'Border colour', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-outer-line: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'faq_outer_bw',
			array(
				'label'     => __( 'Border width', 'km-catalog-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 10 ) ),
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-outer-bw: {{SIZE}}px;' ),
			)
		);

		$this->add_responsive_control(
			'faq_outer_pad',
			array(
				'label'      => __( 'Padding', 'km-catalog-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-outer-pad: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'faq_open_heading',
			array(
				'label'     => __( 'Open question', 'km-catalog-builder' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'faq_open_bg',
			array(
				'label'     => __( 'Question background', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#E2F4FD',
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-open-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'faq_open_body_bg',
			array(
				'label'     => __( 'Answer background', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-open-body-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'faq_open_border',
			array(
				'label'     => __( 'Card outline', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-open-border: {{VALUE}};' ),
				'condition' => array( 'layout' => 'cards' ),
			)
		);

		$this->add_control(
			'faq_icon_bg',
			array(
				'label'     => __( 'Toggle circle', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2C2C2C',
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-icon-bg: {{VALUE}};' ),
				'condition' => array( 'icon' => 'circle' ),
			)
		);

		$this->add_control(
			'faq_icon_color',
			array(
				'label'     => __( 'Toggle chevron', 'km-catalog-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( '{{WRAPPER}} .kmcb-accordion' => '--kmcb-acc-icon-color: {{VALUE}};' ),
				'condition' => array( 'icon' => 'circle' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['items'] ) || ! is_array( $settings['items'] ) ) {
			return;
		}

		$panels = array();

		foreach ( $settings['items'] as $item ) {
			$question = isset( $item['question'] ) ? $item['question'] : '';

			if ( '' === trim( $question ) ) {
				continue;
			}

			$panels[] = array(
				'title'   => $question,
				'content' => isset( $item['answer'] ) ? $item['answer'] : '',
			);
		}

		if ( empty( $panels ) ) {
			return;
		}

		$config = array(
			'widget_id'     => $this->get_id(),
			'source'        => 'custom',
			'panels'        => $panels,
			'layout'        => ( isset( $settings['layout'] ) && 'joined' === $settings['layout'] ) ? 'joined' : 'cards',
			'icon'          => ( isset( $settings['icon'] ) && 'plain' === $settings['icon'] ) ? 'plain' : 'circle',
			'open_first'    => 'yes' === ( isset( $settings['open_first'] ) ? $settings['open_first'] : '' ),
			'one_at_a_time' => 'yes' === ( isset( $settings['one_at_a_time'] ) ? $settings['one_at_a_time'] : '' ),
			// Unused by the custom source, but the renderer reads them.
			'heading_tag'   => 'h2',
			'skip'          => array(),
			'rename'        => array(),
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the template.
		echo KMCB_Render::accordion( $config, null );

		if ( 'yes' === ( isset( $settings['schema'] ) ? $settings['schema'] : '' ) ) {
			$this->render_schema( $panels );
		}
	}

	/**
	 * FAQPage structured data for the questions on show.
	 */
	private function render_schema( array $panels ) {
		$entities = array();

		foreach ( $panels as $panel ) {
			$answer = trim( wp_strip_all_tags( (string) $panel['content'] ) );

			if ( '' === $answer ) {
				continue;
			}

			$entities[] = array(
				'@type'          => 'Question',
				'name'           => wp_strip_all_tags( $panel['title'] ),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $answer,
				),
			);
		}

		if ( empty( $entities ) ) {
			return;
		}

		$schema = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $entities,
		);

		printf(
			'<script type="application/ld+json">%s</script>',
			wp_json_encode( $schema ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON encoded.
		);
	}
}
