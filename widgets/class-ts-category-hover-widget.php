<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class TS_Category_Hover_Widget extends Widget_Base {

    public function get_name() {
        return 'ts_woo_category_hover_showcase';
    }

    public function get_title() {
        return esc_html__( 'TS Woo Category Hover Showcase', 'ts-woo-hover-showcase' );
    }

    public function get_icon() {
        return 'eicon-tabs';
    }

    public function get_categories() {
        return array( 'ts-category', 'general' );
    }

    public function get_style_depends() {
        return array( 'ts-showcase-style' );
    }

    public function get_script_depends() {
        return array( 'ts-showcase-script' );
    }

    /**
     * Fetch parent product categories.
     */
    private function get_parent_categories() {
        $options = array( '' => esc_html__( 'Select Parent Category', 'ts-woo-hover-showcase' ) );
        $terms   = get_terms( array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            'parent'     => 0,
        ) );

        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $options[ $term->term_id ] = $term->name;
            }
        }
        return $options;
    }

    /**
     * Fetch all subcategories for selection.
     */
    private function get_all_subcategories() {
        $options = array();
        $terms   = get_terms( array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
        ) );

        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                if ( $term->parent != 0 ) {
                    $options[ $term->term_id ] = $term->name;
                }
            }
        }
        return $options;
    }

    protected function register_controls() {

        $this->start_controls_section(
            'section_content',
            array(
                'label' => esc_html__( 'Query Configuration', 'ts-woo-hover-showcase' ),
            )
        );

        $this->add_control(
            'parent_category',
            array(
                'label'       => esc_html__( 'Parent Category', 'ts-woo-hover-showcase' ),
                'type'        => Controls_Manager::SELECT2,
                'label_block' => true,
                'options'     => $this->get_parent_categories(),
                'default'     => '',
            )
        );

        $this->add_control(
            'selected_subcategories',
            array(
                'label'       => esc_html__( 'Child Categories to Display', 'ts-woo-hover-showcase' ),
                'type'        => Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'options'     => $this->get_all_subcategories(),
                'description' => esc_html__( 'Leave empty to automatically show all child categories under the selected parent.', 'ts-woo-hover-showcase' ),
            )
        );

        $this->add_control(
            'posts_per_category',
            array(
                'label'   => esc_html__( 'Products Count per Category', 'ts-woo-hover-showcase' ),
                'type'    => Controls_Manager::NUMBER,
                'min'     => 1,
                'max'     => 12,
                'step'    => 1,
                'default' => 3,
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $parent_id = ! empty( $settings['parent_category'] ) ? intval( $settings['parent_category'] ) : 0;

        if ( empty( $parent_id ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<p>' . esc_html__( 'Please select a parent category in the widget settings.', 'ts-woo-hover-showcase' ) . '</p>';
            }
            return;
        }

        // Determine child categories to display.
        $selected_subs = ! empty( $settings['selected_subcategories'] ) ? (array) $settings['selected_subcategories'] : array();

        if ( ! empty( $selected_subs ) ) {
            $child_categories = get_terms( array(
                'taxonomy'   => 'product_cat',
                'hide_empty' => false,
                'include'    => $selected_subs,
            ) );
        } else {
            $child_categories = get_terms( array(
                'taxonomy'   => 'product_cat',
                'hide_empty' => false,
                'parent'     => $parent_id,
            ) );
        }

        if ( empty( $child_categories ) || is_wp_error( $child_categories ) ) {
            return;
        }

        $widget_id = $this->get_id();
        ?>
        <div class="ts-hover-showcase-container" id="ts-showcase-<?php echo esc_attr( $widget_id ); ?>">
            
            <!-- Left Side Categories Navigation -->
            <div class="ts-sidebar-nav">
                <ul class="ts-cat-list">
                    <?php 
                    $nav_index = 0;
                    foreach ( $child_categories as $cat ) : 
                        $is_active = ( 0 === $nav_index ) ? 'ts-active' : '';
                    ?>
                        <li class="ts-cat-item <?php echo esc_attr( $is_active ); ?>" data-target-panel="ts-panel-<?php echo esc_attr( $widget_id . '-' . $cat->term_id ); ?>">
                            <span class="ts-cat-title"><?php echo esc_html( $cat->name ); ?></span>
                            <span class="ts-cat-arrow">&#10095;</span>
                        </li>
                    <?php 
                        $nav_index++;
                    endforeach; 
                    ?>
                </ul>
            </div>

            <!-- Right Side Products Display -->
            <div class="ts-content-wrapper">
                <?php 
                $panel_index = 0;
                foreach ( $child_categories as $cat ) : 
                    $panel_active = ( 0 === $panel_index ) ? 'ts-active' : '';
                    
                    // Query products for this child category.
                    $args = array(
                        'post_type'      => 'product',
                        'post_status'    => 'publish',
                        'posts_per_page' => ! empty( $settings['posts_per_category'] ) ? intval( $settings['posts_per_category'] ) : 3,
                        'tax_query'      => array(
                            array(
                                'taxonomy' => 'product_cat',
                                'field'    => 'term_id',
                                'terms'    => $cat->term_id,
                            ),
                        ),
                    );

                    $products_query = new \WP_Query( $args );
                ?>
                    <div class="ts-product-panel <?php echo esc_attr( $panel_active ); ?>" id="ts-panel-<?php echo esc_attr( $widget_id . '-' . $cat->term_id ); ?>">
                        <?php if ( $products_query->have_posts() ) : ?>
                            <div class="ts-products-grid">
                                <?php 
                                while ( $products_query->have_posts() ) : 
                                    $products_query->the_post(); 
                                    global $product;
                                ?>
                                    <div class="ts-product-card">
                                        <div class="ts-badge-container">
                                            <span class="ts-badge ts-badge-shipping"><?php esc_html_e( 'FREE SHIPPING', 'ts-woo-hover-showcase' ); ?></span>
                                            <?php if ( $product->is_featured() ) : ?>
                                                <span class="ts-badge ts-badge-hot"><?php esc_html_e( 'Hot', 'ts-woo-hover-showcase' ); ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <a href="<?php the_permalink(); ?>" class="ts-product-thumbnail">
                                            <?php echo woocommerce_get_product_thumbnail( 'woocommerce_thumbnail' ); ?>
                                        </a>

                                        <h3 class="ts-product-title">
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h3>

                                        <div class="ts-product-excerpt">
                                            <?php echo wp_trim_words( get_the_excerpt(), 8, '...' ); ?>
                                        </div>
                                    </div>
                                <?php endwhile; wp_reset_postdata(); ?>
                            </div>
                        <?php else : ?>
                            <p class="ts-no-products"><?php esc_html_e( 'No products found in this category.', 'ts-woo-hover-showcase' ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php 
                    $panel_index++;
                endforeach; 
                ?>
            </div>

        </div>
        <?php
    }
}