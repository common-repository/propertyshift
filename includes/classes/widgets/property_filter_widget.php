<?php
/**
 * Property Filter Widget Class
 */

class propertyshift_property_filter_widget extends WP_Widget {

    /** constructor */
    function __construct() {

        $widget_options = array(
          'classname'=>'property-filter-widget',
          'description'=> esc_html__('Display property filter.', 'propertyshift'),
          'panels_groups' => array('propertyshift')
        );
		parent::__construct('propertyshift_property_filter_widget', esc_html__('(PropertyShift) Property Filter', 'propertyshift'), $widget_options);
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
        global $wpdb;

        //Get filter details
        $title = apply_filters('widget_title', $instance['title']);
        $property_filter_id = isset( $instance['property_filter_id'] ) ? strip_tags($instance['property_filter_id']) : '';
        $values = get_post_custom( $property_filter_id );
        $property_filter_layout = isset( $values['ps_property_filter_layout'] ) ? esc_attr( $values['ps_property_filter_layout'][0] ) : 'middle';      
        
        //Assign template args
        $template_args = array();
        $template_args['id'] = $property_filter_id;
        $template_args['widget_filter'] = 'true';

        echo wp_kses_post($before_widget); 
        if($title) { echo '<div class="filter-widget-title">'.$before_title . $title . $after_title.'</div>';  }   

        if($property_filter_layout == 'minimal') {
            propertyshift_template_loader('property-filter-minimal.php', $template_args);
        } else {
            propertyshift_template_loader('property-filter.php', $template_args);
        }

        echo wp_kses_post($after_widget);
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['property_filter_id'] = strip_tags($new_instance['property_filter_id']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {  

        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'filter_fields' => null) );
        $title = esc_attr($instance['title']);
        $property_filter_id = isset ( $instance['property_filter_id'] ) ? $instance['property_filter_id'] : '';
        ?>

        <p>
           <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'propertyshift'); ?></label>
           <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('property_filter_id')); ?>"><?php esc_html_e('Select a Filter', 'propertyshift'); ?></label><br/>
            <select style="width:100%" name="<?php echo esc_attr($this->get_field_name('property_filter_id')); ?>">
                <?php
                    $filter_listing_args = array(
                        'post_type' => 'ps-property-filter',
                        'posts_per_page' => -1
                        );
                    $filter_listing_query = new WP_Query( $filter_listing_args );
                ?>
                <?php if ( $filter_listing_query->have_posts() ) : while ( $filter_listing_query->have_posts() ) : $filter_listing_query->the_post(); ?>
                    <option value="<?php echo get_the_id(); ?>" <?php if($property_filter_id == get_the_id()) { echo 'selected'; } ?>><?php echo get_the_title().' (#'.get_the_id().')'; ?></option>
                    <?php wp_reset_postdata(); ?>
                <?php endwhile; ?>
                <?php else: ?>
                <?php endif; ?>
            </select>
            <span class="admin-module-note"><a href="<?php echo admin_url('edit.php?post_type=ps-property-filter'); ?>" target="_blank"><i class="fa fa-cog"></i> <?php esc_html_e('Manage property filters', 'propertyshift'); ?></a></span><br/>
        </p>

    <?php }

}

add_action('widgets_init', 'propertyshift_property_filter_widget_init');
function propertyshift_property_filter_widget_init() { 
    return register_widget('propertyshift_property_filter_widget'); 
}

?>