<?php
// Exit if accessed directly
if (!defined( 'ABSPATH')) { exit; }

/**
 *	PropertyShift_Shortcodes class
 *
 *  Registers and handles all shortcodes
 */
class PropertyShift_Shortcodes {

	/**
	 *	Constructor
	 */
	public function __construct() {
		add_filter("the_content", array( $this, 'content_filter'));
		add_shortcode('ps_list_properties', array( $this, 'add_shortcode_list_properties'));
		add_shortcode('ps_list_property_tax', array( $this, 'add_shortcode_list_property_tax'));
		add_shortcode('ps_property_filter', array( $this, 'add_shortcode_property_filter'));
		add_shortcode('ps_list_agents', array( $this, 'add_shortcode_list_agents'));
		add_shortcode('ps_agent_profile', array( $this, 'add_shortcode_agent_profile'));
	}

	/**
	 * Content filter
	 *
	 * Remove <p> and <br/> tags from shortcode content
	 */
	public function content_filter($content) {
		$block = join("|",array('ps_list_properties', 'ps_list_property_tax', 'ps_property_filter', 'ps_list_agents', 'ps_agent_profile'));
    	$rep = preg_replace("/(<p>)?\[($block)(\s[^\]]+)?\](<\/p>|<br \/>)?/","[$2$3]",$content);
    	$rep = preg_replace("/(<p>)?\[\/($block)](<\/p>|<br \/>)?/","[/$2]",$rep);
		return $rep;
	}

	/**
	 * List Properties
	 *
	 * @param array $atts
	 * @param string $content
	 */
	public function add_shortcode_list_properties($atts, $content=null) {
		$num_properties_per_page = esc_attr(get_option('ps_num_properties_per_page', 12));
	    $atts = shortcode_atts(
	        array (
	            'show_posts' => $num_properties_per_page,
	            'show_header' => false,
	            'show_pagination' => 'true',
	            'layout' => 'grid',
	            'cols' => null,
	            'property_status' => '',
	            'property_neighborhood' => '',
	            'property_city' => '',
	            'property_state' => '',
	            'property_type' => '',
	            'featured' => 'false'
	    ), $atts);

	    $meta_query_featured = array();
	    if ($atts['featured'] != 'false') {
	        $meta_query_featured[] = array(
	            'key' => 'ps_property_featured',
	            'value'   => 'true'
	        );
	    }

	    $args = array(
	        'posts_per_page' => $atts['show_posts'],
	        'property_status' => $atts['property_status'],
	        'property_neighborhood' => $atts['property_neighborhood'],
	        'property_city' => $atts['property_city'],
	        'property_state' => $atts['property_state'],
	        'property_type' => $atts['property_type'],
	        'meta_query' => $meta_query_featured,
	    );

	    ob_start();
	    if(function_exists('propertyshift_template_loader')) {

	        //Set template args
	        $template_args = array();
	        $template_args['custom_args'] = $args;
	        $template_args['custom_show_filter'] = $atts['show_header'];
	        $template_args['custom_layout'] = $atts['layout'];
	        $template_args['custom_pagination'] = $atts['show_pagination'];
	        $template_args['custom_cols'] = $atts['cols'];
	        $template_args['no_post_message'] = esc_html__( 'Sorry, no properties were found.', 'propertyshift' );

	        //Load template
	        propertyshift_template_loader('loop_properties.php', $template_args);
	    }
	    $output = ob_get_clean();

	    return $output;
	}

	/**
	 * List property taxonomy
	 *
	 * @param array $atts
	 * @param string $content
	 */
	public function add_shortcode_list_property_tax($atts, $content=null) {
		$atts = shortcode_atts(
	    array (
	        'tax' => 'property_type',
	        'terms' => '',
	        'layout' => 'grid',
	        'show_posts' => 5,
	        'orderby' => 'count',
	        'order' => 'DESC',
	        'hide_empty' => 'true',
	    ), $atts);

	    $count = 1;
	    $output = '';

	    $args = array('taxonomy' => $atts['tax'], 'orderby' => $atts['orderby'], 'order' => $atts['order']);
	    if(!empty($atts['terms'])) { $term_slugs = explode(', ', $atts['terms']); $args['slug'] = $term_slugs; }
	    if($atts['hide_empty'] == 'false') { $args['hide_empty'] = false; } else { $args['hide_empty'] = true; }

	    $property_types = get_terms($args);

	    if ( !empty( $property_types ) && !is_wp_error( $property_types ) ) { 

	        if($atts['layout'] == 'carousel') {
	            $output .= '<div class="slider-wrap slider-wrap-tax">';
	            $output .= '<div class="slider-nav slider-nav-tax"><span class="slider-prev"><i class="fa fa-angle-left"></i></span><span class="slider-next"><i class="fa fa-angle-right"></i></span></div>';
	            $output .= '<div class="slider slider-tax">';
	            foreach ( $property_types as $property_type ) { 
	                if($count <= $atts['show_posts']) {
	                    $term_data = get_option('taxonomy_'.$property_type->term_id);
	                    if (isset($term_data['img'])) { $term_img = $term_data['img']; } else { $term_img = ''; } 
	                    $output .= '<div class="slide slide-tax">';
	                    $output .= '<a href="'. esc_attr(get_term_link($property_type->slug, $atts['tax'])) .'">';
	                    if(!empty($term_img)) { $output .= '<img src="'.$term_img.'" alt="" />'; }
	                    $output .= '<h4>'.$property_type->name.'</h4>';
	                    $output .= '<span>'.$property_type->count.' '.esc_html__( 'Properties', 'propertyshift' ).'</span>';
	                    $output .= '</a>';
	                    $output .= '</div>';
	                    $count++;
	                }
	                else {
	                    break;
	                }
	            }
	            $output .= '</div>';
	            $output .= '</div>';
	        } else {
	            $output .= '<div class="row row-property-tax">';
	            foreach ( $property_types as $property_type ) { 
	               if($count <= $atts['show_posts']) {
	                    $term_data = get_option('taxonomy_'.$property_type->term_id);
	                    if (isset($term_data['img'])) { $term_img = $term_data['img']; } else { $term_img = ''; } 

	                    if($count == 1) { $output .= '<div class="col-lg-8 col-md-8 col-property-tax">'; } else { $output .= '<div class="col-lg-4 col-md-4 col-property-tax">'; }
	                    $output .= '<a href="'. esc_attr(get_term_link($property_type->slug, $atts['tax'])) .'" style="background:url('. $term_img .') no-repeat center; background-size:cover;" class="property-cat"><div class="img-overlay black"></div><h3>'. $property_type->name .'</h3><span class="button outline small">'.$property_type->count.' '. esc_html__( 'Properties', 'propertyshift' ) .'</span></a>'; 
	                    $output .= '</div>';
	                    $count++;
	                } else {
	                    break;
	                }
	            } 
	            $output .= '</div>';
	        }
	    }

	    return $output;
	}

	/**
	 * Property Filter
	 *
	 * @param array $atts
	 * @param string $content
	 */
	public function add_shortcode_property_filter($atts, $content=null) {
		$atts = shortcode_atts(array ('id' => '',), $atts);
	    ob_start();

	    $property_filter_id = $atts['id'];
	    if(empty($property_filter_id)) {
	    	return false;
	    } else {
	    	$filter_obj = new PropertyShift_Filters();
	    	$filter_settings = $filter_obj->load_filter_settings($property_filter_id);
		    $property_filter_layout = $filter_settings['layout']['value'];

		    //Set template args
		    $template_args = array();
		    $template_args['id'] = $property_filter_id;
		    $template_args['shortcode_filter'] = 'true';

		    //Load template
		    if($property_filter_layout == 'minimal') {
		        propertyshift_template_loader('property-filter-minimal.php', $template_args);
		    } else {
		        propertyshift_template_loader('property-filter.php', $template_args);
		    }

		    $output = ob_get_clean();
		    return $output;
		}
	}

	/**
	 * List Agents
	 *
	 * @param array $atts
	 * @param string $content
	 */
	public function add_shortcode_list_agents($atts, $content=null) {
		$num_agents_per_page = esc_attr(get_option('ps_num_agents_per_page', 12));
	    $atts = shortcode_atts(
	    array (
	        'number' => $num_agents_per_page,
	        'orderby' => 'display_name',
	        'order' => 'ASC',
	        'show_pagination' => true,
	        'cols' => null,
	    ), $atts);

	    $custom_args = array(
	        'number' => $atts['number'],
	        'orderby' => $atts['orderby'],
	        'order' => $atts['order'],
	    );

	    ob_start();
	    if(function_exists('propertyshift_template_loader')){ 
	        
	        //Set template args
	        $template_args = array();
	        $template_args['custom_args'] = $custom_args;
	        $template_args['custom_pagination'] = $atts['show_pagination'];
	        $template_args['custom_cols'] = $atts['cols'];
	        
	        //Load template
	        propertyshift_template_loader('loop_agents.php', $template_args);
	    }
	    $output = ob_get_clean();
	    return $output;
	}

	/**
	 *	Agent profile shortcode
	 *
	 * @param array $atts
	 * @param string $content
	 */
	public function add_shortcode_agent_profile($atts, $content=null) {
		ob_start();
	        
	    //Load template
	    propertyshift_template_loader('loop_agent_single.php');

		$output = ob_get_clean();
	    return $output;
	}

}
?>