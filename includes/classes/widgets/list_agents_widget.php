<?php
/**
 * List Agents Widget Class
 */
class propertyshift_list_agents_widget extends WP_Widget {

    /** constructor */
    function __construct() {

        $widget_options = array(
          'classname'=>'list-agents-widget',
          'description'=> esc_html__('Display a list of agents.', 'propertyshift'),
          'panels_groups' => array('propertyshift')
        );
		parent::__construct('propertyshift_list_agents_widget', esc_html__('(PropertyShift) List Agents', 'propertyshift'), $widget_options);
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
        global $wpdb;
		global $post;

        $icon_set = get_option('ns_core_icon_set', 'fa');
        if(function_exists('ns_core_load_theme_options')) { $icon_set = ns_core_load_theme_options('ns_core_icon_set'); }

        $title = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
        $num = isset( $instance['num'] ) ? $instance['num'] : 3;
        $filter = isset( $instance['filter'] ) ? $instance['filter'] : 'user_registered';
        $order = isset( $instance['order'] ) ? $instance['order'] : 'ASC';

        echo wp_kses_post($before_widget);
                  
        if($title) {echo wp_kses_post($before_title . $title . $after_title); } 

        $template_args = array(
            'custom_args' => array('number' => $num, 'orderby' => $filter, 'order' => $order),
            'custom_pagination' => 'false',
        );
        propertyshift_template_loader('loop_agents.php', $template_args, $wrapper = true);

        echo wp_kses_post($after_widget); 

    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['num'] = strip_tags($new_instance['num']);
        $instance['filter'] = strip_tags($new_instance['filter']);
        $instance['order'] = strip_tags($new_instance['order']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {  

        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'num' => 3 ) );
        $title = esc_attr($instance['title']);
        $num = esc_attr($instance['num']);
        if(isset($instance['filter'])) { $filter = esc_attr($instance['filter']); } else { $filter = null; }
        if(isset($instance['order'])) { $order = esc_attr($instance['order']); } else { $order = null; }
        ?>

        <p>
           <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'propertyshift'); ?></label>
           <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>

        <p>
          <label for="<?php echo esc_attr($this->get_field_id('num')); ?>"><?php esc_html_e('Number of Agents:', 'propertyshift'); ?></label>
          <input class="widefat" id="<?php echo esc_attr($this->get_field_id('num')); ?>" name="<?php echo esc_attr($this->get_field_name('num')); ?>" type="number" value="<?php echo esc_attr($num); ?>" />
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('filter')); ?>"><?php esc_html_e('Sort By:', 'propertyshift'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('filter')); ?>" name="<?php echo esc_attr($this->get_field_name('filter')); ?>">
                <option value="user_registered" <?php if($filter == 'user_registered') { echo 'selected'; } ?>><?php esc_html_e('Most Recent', 'propertyshift'); ?></option>
                <option value="display_name" <?php if($filter == 'display_name') { echo 'selected'; } ?>><?php esc_html_e('Display Name', 'propertyshift'); ?></option>
            </select>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('order')); ?>"><?php esc_html_e('Order:', 'propertyshift'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('order')); ?>" name="<?php echo esc_attr($this->get_field_name('order')); ?>">
                <option value="ASC" <?php if($order == 'ASC') { echo 'selected'; } ?>><?php esc_html_e('Ascending', 'propertyshift'); ?></option>
                <option value="DESC" <?php if($order == 'DESC') { echo 'selected'; } ?>><?php esc_html_e('Descending', 'propertyshift'); ?></option>
            </select>
        </p>

        <?php
    }

}

add_action('widgets_init', 'propertyshift_list_agents_widget_init');
function propertyshift_list_agents_widget_init() { 
    return register_widget('propertyshift_list_agents_widget'); 
}

?>