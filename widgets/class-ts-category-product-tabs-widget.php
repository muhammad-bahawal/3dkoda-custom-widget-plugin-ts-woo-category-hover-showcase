<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;

class TS_Category_Product_Tabs_Widget extends Widget_Base {

    public function get_name() {
        return 'ts_category_product_tabs';
    }

    public function get_title() {
        return esc_html__( 'TS Category Product Tabs', 'ts-woo-hover-showcase' );
    }

    public function get_icon() {
        return 'eicon-tabs';
    }

    public function get_categories() {
        return array( 'ts-category', 'general' );
    }

    public function get_style_depends() {
        return array( 'ts-category-tabs-style' );
    }

    public function get_script_depends() {
        return array( 'ts-category-tabs-script' );
    }

    /**
     * Get all WooCommerce categories (parents and children) for select dropdown.
     */
    private function get_all_categories() {
        $options = array(
            '' => esc_html__( 'All Categories / Any', 'ts-woo-hover-showcase' ),
        );

        if ( ! taxonomy_exists( 'product_cat' ) ) {
            return $options;
        }

        $terms = get_terms( array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ) );

        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            $terms_by_id = array();
            foreach ( $terms as $term ) {
                $terms_by_id[ $term->term_id ] = $term;
            }

            foreach ( $terms as $term ) {
                $path = array( $term->name );
                $curr = $term;
                while ( $curr->parent && isset( $terms_by_id[ $curr->parent ] ) ) {
                    $curr = $terms_by_id[ $curr->parent ];
                    array_unshift( $path, $curr->name );
                }
                $options[ $term->term_id ] = implode( ' — ', $path ) . ' (' . $term->count . ')';
            }
        }

        return $options;
    }

    /**
     * Fetch all published Elementor Loop Templates.
     */
    private function get_loop_templates() {
        $options = array(
            '' => esc_html__( 'Default (Fallback Card)', 'ts-woo-hover-showcase' ),
        );

        $templates = get_posts( array(
            'post_type'      => 'elementor_library',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'     => '_elementor_template_type',
                    'value'   => 'loop-item',
                    'compare' => '=',
                ),
            ),
        ) );

        if ( ! empty( $templates ) && ! is_wp_error( $templates ) ) {
            foreach ( $templates as $tpl ) {
                $options[ $tpl->ID ] = $tpl->post_title;
            }
        }

        return $options;
    }

    protected function register_controls() {

        // ==========================================
        // CONTENT SECTION: TABS REPEATER
        // ==========================================
        $this->start_controls_section(
            'section_tabs',
            array(
                'label' => esc_html__( 'Tabs Configuration', 'ts-woo-hover-showcase' ),
            )
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'tab_title',
            array(
                'label'       => esc_html__( 'Tab Title', 'ts-woo-hover-showcase' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Tab Title', 'ts-woo-hover-showcase' ),
                'label_block' => true,
                'dynamic'     => array(
                    'active' => true,
                ),
            )
        );

        $repeater->add_control(
            'category_id',
            array(
                'label'       => esc_html__( 'Category Select', 'ts-woo-hover-showcase' ),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_all_categories(),
                'default'     => '',
                'label_block' => true,
                'description' => esc_html__( 'Choose a WooCommerce category for this tab. Leave empty to query all products.', 'ts-woo-hover-showcase' ),
            )
        );

        $repeater->add_control(
            'loop_template',
            array(
                'label'       => esc_html__( 'Loop Template', 'ts-woo-hover-showcase' ),
                'type'        => Controls_Manager::SELECT,
                'options'     => $this->get_loop_templates(),
                'default'     => '',
                'label_block' => true,
                'description' => esc_html__( 'Choose an Elementor Loop Template (loop-item) or use the fallback card.', 'ts-woo-hover-showcase' ),
            )
        );

        $this->add_control(
            'tabs',
            array(
                'label'       => esc_html__( 'Tabs Items', 'ts-woo-hover-showcase' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => array(
                    array(
                        'tab_title' => esc_html__( 'All printers', 'ts-woo-hover-showcase' ),
                    ),
                    array(
                        'tab_title' => esc_html__( 'Getting started', 'ts-woo-hover-showcase' ),
                    ),
                    array(
                        'tab_title' => esc_html__( 'Enclosed printers', 'ts-woo-hover-showcase' ),
                    ),
                ),
                'title_field' => '{{{ tab_title }}}',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT SECTION: LAYOUT & QUERY
        // ==========================================
        $this->start_controls_section(
            'section_layout_query',
            array(
                'label' => esc_html__( 'Layout & Query Controls', 'ts-woo-hover-showcase' ),
            )
        );

        $this->add_control(
            'products_limit',
            array(
                'label'   => esc_html__( 'Products Limit', 'ts-woo-hover-showcase' ),
                'type'    => Controls_Manager::NUMBER,
                'min'     => 1,
                'max'     => 48,
                'step'    => 1,
                'default' => 3,
            )
        );

        $this->add_responsive_control(
            'grid_columns',
            array(
                'label'          => esc_html__( 'Grid Columns', 'ts-woo-hover-showcase' ),
                'type'           => Controls_Manager::SELECT,
                'default'        => '3',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options'        => array(
                    '1' => esc_html__( '1 Column', 'ts-woo-hover-showcase' ),
                    '2' => esc_html__( '2 Columns', 'ts-woo-hover-showcase' ),
                    '3' => esc_html__( '3 Columns', 'ts-woo-hover-showcase' ),
                    '4' => esc_html__( '4 Columns', 'ts-woo-hover-showcase' ),
                    '5' => esc_html__( '5 Columns', 'ts-woo-hover-showcase' ),
                    '6' => esc_html__( '6 Columns', 'ts-woo-hover-showcase' ),
                ),
                'selectors'      => array(
                    '{{WRAPPER}} .ts-loop-products-grid, {{WRAPPER}} .ts-products-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
                ),
            )
        );

        $this->add_responsive_control(
            'grid_gap',
            array(
                'label'      => esc_html__( 'Grid Gap', 'ts-woo-hover-showcase' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array( 'px', 'em', 'rem' ),
                'range'      => array(
                    'px' => array(
                        'min'  => 0,
                        'max'  => 60,
                        'step' => 1,
                    ),
                ),
                'default'    => array(
                    'unit' => 'px',
                    'size' => 24,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .ts-loop-products-grid, {{WRAPPER}} .ts-products-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'orderby',
            array(
                'label'   => esc_html__( 'Order By', 'ts-woo-hover-showcase' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => array(
                    'date'       => esc_html__( 'Date', 'ts-woo-hover-showcase' ),
                    'title'      => esc_html__( 'Title', 'ts-woo-hover-showcase' ),
                    'price'      => esc_html__( 'Price', 'ts-woo-hover-showcase' ),
                    'popularity' => esc_html__( 'Popularity (Sales)', 'ts-woo-hover-showcase' ),
                    'rating'     => esc_html__( 'Rating', 'ts-woo-hover-showcase' ),
                    'rand'       => esc_html__( 'Random', 'ts-woo-hover-showcase' ),
                    'menu_order' => esc_html__( 'Menu Order', 'ts-woo-hover-showcase' ),
                ),
            )
        );

        $this->add_control(
            'order',
            array(
                'label'   => esc_html__( 'Order', 'ts-woo-hover-showcase' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => array(
                    'ASC'  => esc_html__( 'Ascending (ASC)', 'ts-woo-hover-showcase' ),
                    'DESC' => esc_html__( 'Descending (DESC)', 'ts-woo-hover-showcase' ),
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE SECTION: TABS NAVIGATION
        // ==========================================
        $this->start_controls_section(
            'section_style_tabs',
            array(
                'label' => esc_html__( 'Tabs Navigation', 'ts-woo-hover-showcase' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'tab_align',
            array(
                'label'     => esc_html__( 'Alignment', 'ts-woo-hover-showcase' ),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => array(
                    'flex-start' => array(
                        'title' => esc_html__( 'Left', 'ts-woo-hover-showcase' ),
                        'icon'  => 'eicon-text-align-left',
                    ),
                    'center'     => array(
                        'title' => esc_html__( 'Center', 'ts-woo-hover-showcase' ),
                        'icon'  => 'eicon-text-align-center',
                    ),
                    'flex-end'   => array(
                        'title' => esc_html__( 'Right', 'ts-woo-hover-showcase' ),
                        'icon'  => 'eicon-text-align-right',
                    ),
                ),
                'default'   => 'flex-start',
                'selectors' => array(
                    '{{WRAPPER}} .ts-product-tabs-nav' => 'justify-content: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'tab_typography',
                'label'    => esc_html__( 'Typography', 'ts-woo-hover-showcase' ),
                'selector' => '{{WRAPPER}} .ts-tab-item',
            )
        );

        $this->add_responsive_control(
            'tab_spacing',
            array(
                'label'      => esc_html__( 'Space Between Tabs', 'ts-woo-hover-showcase' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array( 'px', 'em' ),
                'range'      => array(
                    'px' => array(
                        'min'  => 8,
                        'max'  => 80,
                        'step' => 1,
                    ),
                ),
                'default'    => array(
                    'unit' => 'px',
                    'size' => 32,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .ts-product-tabs-nav' => 'gap: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->start_controls_tabs( 'tabs_color_states' );

        // Normal State
        $this->start_controls_tab(
            'tab_color_normal',
            array(
                'label' => esc_html__( 'Normal', 'ts-woo-hover-showcase' ),
            )
        );

        $this->add_control(
            'tab_text_color',
            array(
                'label'     => esc_html__( 'Text Color', 'ts-woo-hover-showcase' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#707070',
                'selectors' => array(
                    '{{WRAPPER}} .ts-tab-item' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        // Hover State
        $this->start_controls_tab(
            'tab_color_hover',
            array(
                'label' => esc_html__( 'Hover', 'ts-woo-hover-showcase' ),
            )
        );

        $this->add_control(
            'tab_text_hover_color',
            array(
                'label'     => esc_html__( 'Text Color', 'ts-woo-hover-showcase' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#111827',
                'selectors' => array(
                    '{{WRAPPER}} .ts-tab-item:hover' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        // Active State
        $this->start_controls_tab(
            'tab_color_active',
            array(
                'label' => esc_html__( 'Active', 'ts-woo-hover-showcase' ),
            )
        );

        $this->add_control(
            'tab_text_active_color',
            array(
                'label'     => esc_html__( 'Text Color', 'ts-woo-hover-showcase' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#111827',
                'selectors' => array(
                    '{{WRAPPER}} .ts-tab-item.is-active' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'tab_indicator_color',
            array(
                'label'     => esc_html__( 'Active Indicator Line Color', 'ts-woo-hover-showcase' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ff4a11',
                'selectors' => array(
                    '{{WRAPPER}} .ts-tab-item.is-active' => 'border-bottom-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'tab_indicator_height',
            array(
                'label'      => esc_html__( 'Indicator Line Thickness', 'ts-woo-hover-showcase' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array( 'px' ),
                'range'      => array(
                    'px' => array(
                        'min'  => 1,
                        'max'  => 6,
                        'step' => 1,
                    ),
                ),
                'default'    => array(
                    'unit' => 'px',
                    'size' => 2,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .ts-tab-item.is-active' => 'border-bottom-width: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'divider_heading',
            array(
                'label'     => esc_html__( 'Bottom Divider Bar', 'ts-woo-hover-showcase' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'tab_divider_color',
            array(
                'label'     => esc_html__( 'Divider Color', 'ts-woo-hover-showcase' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#e5e7eb',
                'selectors' => array(
                    '{{WRAPPER}} .ts-product-tabs-nav' => 'border-bottom-color: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'tab_padding',
            array(
                'label'      => esc_html__( 'Tab Item Padding', 'ts-woo-hover-showcase' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .ts-tab-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
                'default'    => array(
                    'top'      => 10,
                    'right'    => 4,
                    'bottom'   => 14,
                    'left'     => 4,
                    'isLinked' => false,
                ),
            )
        );

        $this->add_responsive_control(
            'tabs_nav_margin_bottom',
            array(
                'label'      => esc_html__( 'Tabs Margin Bottom', 'ts-woo-hover-showcase' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array( 'px', 'em' ),
                'range'      => array(
                    'px' => array(
                        'min'  => 0,
                        'max'  => 80,
                        'step' => 1,
                    ),
                ),
                'default'    => array(
                    'unit' => 'px',
                    'size' => 32,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .ts-product-tabs-nav-wrapper' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE SECTION: FALLBACK PRODUCT CARD
        // ==========================================
        $this->start_controls_section(
            'section_style_fallback_card',
            array(
                'label' => esc_html__( 'Product Card (Fallback Style)', 'ts-woo-hover-showcase' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'card_bg_color',
            array(
                'label'     => esc_html__( 'Card Background Color', 'ts-woo-hover-showcase' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .ts-product-fallback-card' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'      => 'card_border',
                'label'     => esc_html__( 'Card Border', 'ts-woo-hover-showcase' ),
                'selector'  => '{{WRAPPER}} .ts-product-fallback-card',
                'separator' => 'before',
            )
        );

        $this->add_responsive_control(
            'card_border_radius',
            array(
                'label'      => esc_html__( 'Border Radius', 'ts-woo-hover-showcase' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'default'    => array(
                    'top'      => 16,
                    'right'    => 16,
                    'bottom'   => 16,
                    'left'     => 16,
                    'isLinked' => true,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .ts-product-fallback-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'card_padding',
            array(
                'label'      => esc_html__( 'Card Padding', 'ts-woo-hover-showcase' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em' ),
                'default'    => array(
                    'top'      => 24,
                    'right'    => 24,
                    'bottom'   => 24,
                    'left'     => 24,
                    'isLinked' => true,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .ts-product-fallback-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'card_box_shadow',
                'label'    => esc_html__( 'Box Shadow', 'ts-woo-hover-showcase' ),
                'selector' => '{{WRAPPER}} .ts-product-fallback-card',
            )
        );

        $this->add_control(
            'card_title_color',
            array(
                'label'     => esc_html__( 'Title Color', 'ts-woo-hover-showcase' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#111827',
                'separator' => 'before',
                'selectors' => array(
                    '{{WRAPPER}} .ts-card-title, {{WRAPPER}} .ts-card-title a' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'card_title_typography',
                'label'    => esc_html__( 'Title Typography', 'ts-woo-hover-showcase' ),
                'selector' => '{{WRAPPER}} .ts-card-title',
            )
        );

        $this->add_control(
            'card_price_color',
            array(
                'label'     => esc_html__( 'Price Color', 'ts-woo-hover-showcase' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#0f172a',
                'selectors' => array(
                    '{{WRAPPER}} .ts-card-price' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'button_bg_color',
            array(
                'label'     => esc_html__( 'Action Button Background', 'ts-woo-hover-showcase' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#0f172a',
                'separator' => 'before',
                'selectors' => array(
                    '{{WRAPPER}} .ts-card-action-btn' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'button_hover_bg_color',
            array(
                'label'     => esc_html__( 'Action Button Hover Background', 'ts-woo-hover-showcase' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ff4a11',
                'selectors' => array(
                    '{{WRAPPER}} .ts-card-action-btn:hover' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render semantic fallback card matching the reference UI.
     *
     * @param \WC_Product $product
     */
    protected function render_fallback_card( $product ) {
        if ( ! $product ) {
            return;
        }

        $product_id  = $product->get_id();
        $permalink   = get_permalink( $product_id );
        $title       = $product->get_name();
        $price_html  = $product->get_price_html();
        $is_in_stock = $product->is_in_stock();

        // Retrieve product category name for eyebrow & brand line
        $categories = get_the_terms( $product_id, 'product_cat' );
        $eyebrow    = ( ! empty( $categories ) && ! is_wp_error( $categories ) ) ? $categories[0]->name : '';

        // Product short description
        $short_desc = $product->get_short_description();
        if ( empty( $short_desc ) ) {
            $short_desc = wp_trim_words( get_the_excerpt( $product_id ), 8, '...' );
        } else {
            $short_desc = wp_strip_all_tags( $short_desc );
            $short_desc = wp_trim_words( $short_desc, 8, '...' );
        }

        // Feature pills / tags
        $pills = array();
        $tags  = get_the_terms( $product_id, 'product_tag' );
        if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
            foreach ( array_slice( $tags, 0, 3 ) as $tag ) {
                $pills[] = $tag->name;
            }
        }
        if ( empty( $pills ) ) {
            $attributes = $product->get_attributes();
            if ( ! empty( $attributes ) ) {
                foreach ( $attributes as $attr ) {
                    if ( is_object( $attr ) && method_exists( $attr, 'get_options' ) ) {
                        $terms = wc_get_product_terms( $product_id, $attr->get_name(), array( 'fields' => 'names' ) );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                            $pills = array_slice( $terms, 0, 3 );
                            break;
                        }
                    }
                }
            }
        }
        ?>
        <div class="ts-product-grid-item ts-product-fallback-card">
            <div class="ts-card-top-meta">
                <?php if ( ! empty( $eyebrow ) ) : ?>
                    <span class="ts-card-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
                <?php endif; ?>
            </div>

            <a href="<?php echo esc_url( $permalink ); ?>" class="ts-card-media">
                <?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
            </a>

            <div class="ts-card-body">
                <?php if ( ! empty( $eyebrow ) ) : ?>
                    <span class="ts-card-brand"><?php echo esc_html( $eyebrow ); ?></span>
                <?php endif; ?>

                <h3 class="ts-card-title">
                    <a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
                </h3>

                <?php if ( ! empty( $short_desc ) ) : ?>
                    <p class="ts-card-excerpt"><?php echo esc_html( $short_desc ); ?></p>
                <?php endif; ?>

                <?php if ( ! empty( $pills ) ) : ?>
                    <div class="ts-card-pills">
                        <?php foreach ( $pills as $pill ) : ?>
                            <span class="ts-card-pill"><?php echo esc_html( $pill ); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="ts-card-stock">
                    <span class="ts-stock-dot <?php echo $is_in_stock ? 'in-stock' : 'out-of-stock'; ?>"></span>
                    <span class="ts-stock-text">
                        <?php echo $is_in_stock ? esc_html__( 'In Stock', 'ts-woo-hover-showcase' ) : esc_html__( 'Out of Stock', 'ts-woo-hover-showcase' ); ?>
                    </span>
                </div>
            </div>

            <div class="ts-card-footer">
                <div class="ts-card-price-wrap">
                    <span class="ts-card-price"><?php echo $price_html; ?></span>
                    <span class="ts-card-vat-note"><?php esc_html_e( 'incl. VAT / sample price', 'ts-woo-hover-showcase' ); ?></span>
                </div>

                <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" 
                   data-quantity="1" 
                   class="ts-card-action-btn <?php echo esc_attr( ( $product->is_purchasable() && $product->is_in_stock() ) ? 'add_to_cart_button ajax_add_to_cart' : '' ); ?>" 
                   data-product_id="<?php echo esc_attr( $product_id ); ?>" 
                   data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>" 
                   aria-label="<?php echo esc_attr( $product->add_to_cart_text() ); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </a>
            </div>
        </div>
        <?php
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if ( empty( $settings['tabs'] ) || ! is_array( $settings['tabs'] ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<p>' . esc_html__( 'Please add tab items to display products.', 'ts-woo-hover-showcase' ) . '</p>';
            }
            return;
        }

        $widget_id = $this->get_id();
        $limit     = ! empty( $settings['products_limit'] ) ? intval( $settings['products_limit'] ) : 3;
        $orderby   = ! empty( $settings['orderby'] ) ? sanitize_key( $settings['orderby'] ) : 'date';
        $order     = ! empty( $settings['order'] ) ? sanitize_key( $settings['order'] ) : 'DESC';

        // Enqueue Elementor Post CSS for the selected Loop Templates
        $loop_template_ids = array();
        foreach ( $settings['tabs'] as $tab ) {
            if ( ! empty( $tab['loop_template'] ) ) {
                $loop_template_ids[] = intval( $tab['loop_template'] );
            }
        }
        $loop_template_ids = array_unique( array_filter( $loop_template_ids ) );

        foreach ( $loop_template_ids as $template_id ) {
            if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
                $css_file = new \Elementor\Core\Files\CSS\Post( $template_id );
                $css_file->enqueue();
            }
        }
        ?>
        <div class="ts-product-tabs-wrapper" id="ts-product-tabs-<?php echo esc_attr( $widget_id ); ?>">

            <!-- Horizontal Tabs Navigation -->
            <div class="ts-product-tabs-nav-wrapper">
                <ul class="ts-product-tabs-nav" role="tablist">
                    <?php 
                    foreach ( $settings['tabs'] as $index => $tab ) : 
                        $tab_id    = 'ts-tab-' . $widget_id . '-' . $index;
                        $panel_id  = 'ts-panel-' . $widget_id . '-' . $index;
                        $is_active = ( 0 === $index ) ? 'is-active' : '';
                        $tab_title = ! empty( $tab['tab_title'] ) ? $tab['tab_title'] : sprintf( esc_html__( 'Tab #%d', 'ts-woo-hover-showcase' ), $index + 1 );
                    ?>
                        <li class="ts-tab-item <?php echo esc_attr( $is_active ); ?>" 
                            id="<?php echo esc_attr( $tab_id ); ?>" 
                            role="tab" 
                            aria-controls="<?php echo esc_attr( $panel_id ); ?>" 
                            aria-selected="<?php echo ( 0 === $index ) ? 'true' : 'false'; ?>" 
                            tabindex="<?php echo ( 0 === $index ) ? '0' : '-1'; ?>" 
                            data-target="#<?php echo esc_attr( $panel_id ); ?>">
                            <span class="ts-tab-title"><?php echo esc_html( $tab_title ); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Tab Content Panels -->
            <div class="ts-product-tabs-content">
                <?php 
                foreach ( $settings['tabs'] as $index => $tab ) : 
                    $tab_id    = 'ts-tab-' . $widget_id . '-' . $index;
                    $panel_id  = 'ts-panel-' . $widget_id . '-' . $index;
                    $is_active = ( 0 === $index ) ? 'is-active' : '';
                    $cat_id    = ! empty( $tab['category_id'] ) ? intval( $tab['category_id'] ) : 0;
                    $tpl_id    = ! empty( $tab['loop_template'] ) ? intval( $tab['loop_template'] ) : 0;

                    // Query products for this tab
                    $query_args = array(
                        'post_type'      => 'product',
                        'post_status'    => 'publish',
                        'posts_per_page' => $limit,
                        'orderby'        => $orderby,
                        'order'          => $order,
                    );

                    if ( ! empty( $cat_id ) ) {
                        $query_args['tax_query'] = array(
                            array(
                                'taxonomy'         => 'product_cat',
                                'field'            => 'term_id',
                                'terms'            => $cat_id,
                                'include_children' => true,
                            ),
                        );
                    }

                    if ( 'price' === $orderby ) {
                        $query_args['meta_key'] = '_price';
                        $query_args['orderby']  = 'meta_value_num';
                    } elseif ( 'popularity' === $orderby ) {
                        $query_args['meta_key'] = 'total_sales';
                        $query_args['orderby']  = 'meta_value_num';
                    }

                    $products_query = new \WP_Query( $query_args );
                ?>
                    <div class="ts-tab-panel <?php echo esc_attr( $is_active ); ?>" 
                         id="<?php echo esc_attr( $panel_id ); ?>" 
                         role="tabpanel" 
                         aria-labelledby="<?php echo esc_attr( $tab_id ); ?>">
                        
                        <?php if ( $products_query->have_posts() ) : ?>
                            <div class="ts-loop-products-grid ts-products-grid">
                                <?php 
                                while ( $products_query->have_posts() ) : 
                                    $products_query->the_post();
                                    
                                    global $post, $product;
                                    $post = get_post( get_the_ID() );
                                    setup_postdata( $post );
                                    if ( function_exists( 'wc_get_product' ) ) {
                                        $product = wc_get_product( get_the_ID() );
                                    }

                                    if ( ! empty( $tpl_id ) && class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
                                        echo '<div class="e-loop-item elementor elementor-' . esc_attr( $tpl_id ) . '">';
                                        echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $tpl_id, true );
                                        echo '</div>';
                                    } else {
                                        $this->render_fallback_card( $product );
                                    }
                                endwhile; 
                                wp_reset_postdata(); 
                                ?>
                            </div>
                        <?php else : ?>
                            <div class="ts-no-products">
                                <p><?php esc_html_e( 'No products found in this category.', 'ts-woo-hover-showcase' ); ?></p>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>

        </div>
        <?php
    }
}
