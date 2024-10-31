<?php
/**
 * List Property Taxonomies Widget Class
 */
class propertyshift_list_property_categories_widget extends WP_Widget {

    /** constructor */
    function __construct() {

        $widget_options = array(
          'classname'=>'list-property-categories-widget',
          'description'=> esc_html__('Display a list of property categories.', 'propertyshift'),
          'panels_groups' => array('propertyshift')
        );
        parent::__construct('propertyshift_list_property_categories_widget', esc_html__('(PropertyShift) List Property Categories', 'propertyshift'), $widget_options);
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
        global $wpdb;
        global $post;

        $title = isset( $instance['title'] ) ? apply_filters('widget_title', $instance['title']) : '';
        $num = isset( $instance['num'] ) ? strip_tags($instance['num']) : 3;
        $layout = isset( $instance['layout'] ) ? strip_tags($instance['layout']) : 'tile';
        $category = isset( $instance['category'] ) ? strip_tags($instance['category']) : 'property_type';
        $order = isset( $instance['order'] ) ? strip_tags($instance['order']) : 'desc';
        $order_by = isset( $instance['order_by'] ) ? strip_tags($instance['order_by']) : 'count';
        if(!empty($instance['show_count'])) { $show_count = true; } else { $show_count = false; }
        ?>
            <?php echo wp_kses_post($before_widget); ?>
                <?php if ( $title )
                    echo wp_kses_post($before_title . $title . $after_title);

                    $count = 1;
                    $property_types = get_terms(array('taxonomy' => $category, 'orderby' => $order_by, 'order' => $order)); 

                    if ( !empty( $property_types ) && !is_wp_error( $property_types ) ) { 

                        if($layout == 'tile') {
                            echo '<div class="row">';
                            foreach ( $property_types as $property_type ) { 
                               if($count <= $num) {
                                    $term_data = get_option('taxonomy_'.$property_type->term_id);
                                    if (isset($term_data['img'])) { $term_img = $term_data['img']; } else { $term_img = ''; } 

                                    if($count == 1) { echo '<div class="col-lg-8 col-md-8">'; } else { echo '<div class="col-lg-4 col-md-4">'; } ?>
                                    <a href="<?php echo esc_attr(get_term_link($property_type->slug, $category)); ?>" style="background:url(<?php echo $term_img; ?>) no-repeat center; background-size:cover;" class="property-cat">
                                        <div class="img-overlay black"></div>
                                        <h3><?php echo $property_type->name; ?></h3>
                                        <?php if($show_count == true) { ?><span class="button small"><?php echo $property_type->count.' '. esc_html__( 'Properties', 'propertyshift' ); ?></span><?php } ?>
                                    </a>
                                    <?php echo '</div>';
                                    $count++;
                                } else {
                                    break;
                                }
                            } 
                            echo '</div>';
                        } else {
                            echo '<ul>';
                            foreach ( $property_types as $property_type ) {
                                if($count <= $num) {
                                    $term_data = get_option('taxonomy_'.$property_type->term_id); ?>
                                    <li>   
                                        <a href="<?php echo esc_attr(get_term_link($property_type->slug, $category)); ?>">
                                            <?php echo $property_type->name; ?> 
                                            <?php if($show_count == true) { ?><span>(<?php echo $property_type->count; ?>)</span><?php } ?>
                                        </a>
                                    </li>
                                    <?php $count++; ?>
                                <?php } else {
                                    break;
                                } 
                            }
                            echo '</ul>';
                        }
                    }

                echo wp_kses_post($after_widget); ?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['num'] = strip_tags($new_instance['num']);
        $instance['layout'] = strip_tags($new_instance['layout']);
        $instance['category'] = strip_tags($new_instance['category']);
        $instance['order'] = strip_tags($new_instance['order']);
        $instance['order_by'] = strip_tags($new_instance['order_by']);
        $instance['show_count'] = isset( $new_instance['show_count'] ) ? strip_tags($new_instance['show_count']) : '';
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {  

        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'num' => 3, 'layout' => null, 'category' => null, 'order' => 'desc', 'order_by' => 'count', 'show_count' => 'true' ) );
        $title = esc_attr($instance['title']);
        $num = esc_attr($instance['num']);
        $layout = esc_attr($instance['layout']);
        $category = esc_attr($instance['category']);
        $order = esc_attr($instance['order']);
        $order_by = esc_attr($instance['order_by']);
        $show_count = esc_attr($instance['show_count']);
        ?>

        <p>
           <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'propertyshift'); ?></label>
           <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>

        <p>
          <label for="<?php echo esc_attr($this->get_field_id('num')); ?>"><?php esc_html_e('Number of Categories:', 'propertyshift'); ?></label>
          <input class="widefat" id="<?php echo esc_attr($this->get_field_id('num')); ?>" name="<?php echo esc_attr($this->get_field_name('num')); ?>" type="number" value="<?php echo esc_attr($num); ?>" />
        </p>

        <p>
          <label for="<?php echo esc_attr($this->get_field_id('layout')); ?>"><?php esc_html_e('Listing Layout:', 'propertyshift'); ?></label>
          <select class="widefat" name="<?php echo esc_attr($this->get_field_name('layout')); ?>">
            <option value="tile" <?php if($layout == 'tile') { echo 'selected'; } ?>><?php esc_html_e('Tile', 'propertyshift'); ?></option>
            <option value="list" <?php if($layout == 'list') { echo 'selected'; } ?>><?php esc_html_e('List', 'propertyshift'); ?></option>
          </select>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('category')); ?>"><?php esc_html_e('Category:', 'propertyshift'); ?></label>
            <select class="widefat" name="<?php echo esc_attr($this->get_field_name('category')); ?>">
                <option value="property_type" <?php if($category == 'property_type') { echo 'selected'; } ?>><?php esc_html_e('Property Type', 'propertyshift'); ?></option>
                <option value="property_status" <?php if($category == 'property_status') { echo 'selected'; } ?>><?php esc_html_e('Property Status', 'propertyshift'); ?></option>
                <option value="property_neighborhood" <?php if($category == 'property_neighborhood') { echo 'selected'; } ?>><?php esc_html_e('Property Neighborhood', 'propertyshift'); ?></option>
                <option value="property_city" <?php if($category == 'property_city') { echo 'selected'; } ?>><?php esc_html_e('Property City', 'propertyshift'); ?></option>
                <option value="property_state" <?php if($category == 'property_state') { echo 'selected'; } ?>><?php esc_html_e('Property State', 'propertyshift'); ?></option>
                <option value="property_amenities" <?php if($category == 'property_amenities') { echo 'selected'; } ?>><?php esc_html_e('Property Amenities', 'propertyshift'); ?></option>
            </select>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('order')); ?>"><?php esc_html_e('Order:', 'propertyshift'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('order')); ?>" name="<?php echo esc_attr($this->get_field_name('order')); ?>">
                <option value="desc" <?php if($order == 'desc') { echo 'selected'; } ?>><?php esc_html_e('Descending', 'propertyshift'); ?></option>
                <option value="asc" <?php if($order == 'asc') { echo 'selected'; } ?>><?php esc_html_e('Ascending', 'propertyshift'); ?></option>
            </select>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('order_by')); ?>"><?php esc_html_e('Order By:', 'propertyshift'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('order_by')); ?>" name="<?php echo esc_attr($this->get_field_name('order_by')); ?>">
                <option value="count" <?php if($order_by == 'count') { echo 'selected'; } ?>><?php esc_html_e('Count', 'propertyshift'); ?></option>
                <option value="date" <?php if($order_by == 'date') { echo 'selected'; } ?>><?php esc_html_e('Date', 'propertyshift'); ?></option>
                <option value="title" <?php if($order_by == 'title') { echo 'selected'; } ?>><?php esc_html_e('Title', 'propertyshift'); ?></option>
            </select>
        </p>

        <p>
          <input id="<?php echo esc_attr($this->get_field_id('show_count')); ?>" name="<?php echo esc_attr($this->get_field_name('show_count')); ?>" type="checkbox" value="true" <?php if($show_count == 'true') { echo 'checked'; } ?> />
          <label for="<?php echo esc_attr($this->get_field_id('show_count')); ?>"><?php esc_html_e('Show Count', 'propertyshift'); ?></label>
        </p>

        <?php
    }

}

add_action('widgets_init', 'propertyshift_list_property_categories_widget_init');
function propertyshift_list_property_categories_widget_init() { 
    return register_widget('propertyshift_list_property_categories_widget'); 
}

?>