<?php
// Exit if accessed directly
if (!defined( 'ABSPATH')) { exit; }

/**
 *	PropertyShift_Filters class
 *
 */
class PropertyShift_Filters {

	/**
	 *	Constructor
	 */
	public function __construct() {
		// Load admin object & settings
		$this->admin_obj = new PropertyShift_Admin();
        $this->global_settings = $this->admin_obj->load_settings();
	}

	/**
	 *	Init
	 */
	public function init() {
		add_action( 'init', array($this, 'add_custom_post_type'));
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box'));
		add_action( 'save_post', array( $this, 'save_meta_box'));
		add_filter( 'manage_edit-ps-property-filter_columns', array($this, 'edit_property_filter_columns'));
		add_action( 'manage_ps-property-filter_posts_custom_column',  array($this, 'manage_property_filter_columns'), 10, 2 );
		add_action( 'template_redirect', array( $this, 'page_filter_template_direct'));
	}

	/************************************************************************/
	// Filters Custom Post Type
	/************************************************************************/

	/**
	 *	Add custom post type
	 */
	public function add_custom_post_type() {
		register_post_type( 'ps-property-filter',
	        array(
	            'labels' => array(
	                'name' => __( 'Property Filters', 'propertyshift' ),
	                'singular_name' => __( 'Property Filter', 'propertyshift' ),
	                'add_new_item' => __( 'Add New Property Filter', 'propertyshift' ),
	                'search_items' => __( 'Search Property Filters', 'propertyshift' ),
	                'edit_item' => __( 'Edit Property Filter', 'propertyshift' ),
	            ),
	        'public' => false,
	        'capability_type' => 'ps-property-filter',
			'publicly_queryable' => true,
			'show_in_menu' => false,
			'show_ui' => true,
	        'show_in_nav_menus' => false,
	        'menu_icon' => 'dashicons-filter',
	        'has_archive' => false,
	        'supports' => array('title', 'revisions', 'page_attributes'),
	        )
	    );
	}

	/**
	 *	Load filter settings
	 *
	 * @param int $post_id
	 */
	public function load_filter_settings($post_id, $return_defaults = false) {
		$filter_settings_init = array(
			'shortcode' => array(
				'title' => esc_html__('Shortcode', 'propertyshift'),
				'description' => esc_html__('Copy/paste it into your post, page, or text widget content.', 'propertyshift'),
				'type' => 'text',
				'value' => '[ps_property_filter id="'.$post_id.'"]',
				'order' => 1,
				'disabled' => true,
			),
			'position' => array(
				'title' => esc_html__('Page Banner Position', 'propertyshift'),
				'name' => 'ps_property_filter_position',
				'description' => esc_html__('Choose the where the filter will display relative to page banners.', 'propertyshift'),
				'type' => 'select',
				'options' => array(
					esc_html__('Above Banner', 'propertyshift') => 'above',
					esc_html__('Inside Banner', 'propertyshift') => 'middle',
					esc_html__('Below Banner', 'propertyshift') => 'below',
				),
				'order' => 2,
			),
			'layout' => array(
				'title' => esc_html__('Filter Layout', 'propertyshift'),
				'name' => 'ps_property_filter_layout',
				'description' => esc_html__('Choose a layout to for the filter.', 'propertyshift'),
				'type' => 'select',
				'options' => array(
					esc_html__('Full Width', 'propertyshift') => 'full',
					esc_html__('Minimal', 'propertyshift') => 'minimal',
					esc_html__('Boxed', 'propertyshift') => 'boxed',
					esc_html__('Vertical', 'propertyshift') => 'vertical',
				),
				'order' => 3,
			),
			'display_tabs' => array(
				'title' => esc_html__('Display Filter Tabs', 'propertyshift'),
				'name' => 'ps_property_filter_display_tabs',
				'description' => esc_html__('Will display tabs to switch between available property statuses.', 'propertyshift'),
				'type' => 'checkbox',
				'order' => 4,
			),
			'fields' => array(
				'title' => esc_html__('Filter Fields', 'propertyshift'),
				'name' => 'ps_property_filter_items',
				'description' => esc_html__('Drag and drop to rearrange order.', 'propertyshift'),
				'type' => 'sortable',
				'value' => $this->load_filter_fields(),
				'order' => 5,
				'serialized' => true,
				'children' => array(
                	'price_min' => array(
                		'title' => esc_html__('Price Range Minimum', 'propertyshift'),
	                	'name' => 'ps_property_filter_price_min',
	                	'value' => 0,
	                	'type' => 'number',
	                	'parent_val' => 'price',
                	),
                	'price_max' => array(
                		'title' => esc_html__('Price Range Maximum', 'propertyshift'),
	                	'name' => 'ps_property_filter_price_max',
	                	'value' => 1000000,
	                	'type' => 'number',
	                	'parent_val' => 'price',
                	),
                	'price_min_start' => array(
                		'title' => esc_html__('Price Range Minimum Start', 'propertyshift'),
	                	'name' => 'ps_property_filter_price_min_start',
	                	'value' => 200000,
	                	'type' => 'number',
	                	'parent_val' => 'price',
                	),
                	'price_max_start' => array(
                		'title' => esc_html__('Price Range Maximum Start', 'propertyshift'),
	                	'name' => 'ps_property_filter_price_max_start',
	                	'value' => 600000,
	                	'type' => 'number',
	                	'parent_val' => 'price',
                	),
                ),
			),
			'submit_button_text' => array(
				'title' => esc_html__('Submit Button Text', 'propertyshift'),
				'name' => 'ps_property_filter_submit_text',
				'type' => 'text',
				'value' => esc_html__('Find Properties', 'propertyshift'),
				'order' => 6,
			),
		);
		$filter_settings_init = apply_filters( 'propertyshift_filter_settings_init_filter', $filter_settings_init, $post_id);
		uasort($filter_settings_init, 'ns_basics_sort_by_order');

		// Return default settings
		if($return_defaults == true) {
			
			return $filter_settings_init;
		
		// Return saved settings
		} else {
			$filter_settings = $this->admin_obj->get_meta_box_values($post_id, $filter_settings_init);
			$filter_settings = apply_filters( 'propertyshift_filter_settings_saved_filter', $filter_settings, $post_id);
			return $filter_settings;
		}
	}

	/**
	 *	Register meta box
	 */
	public function register_meta_box() {
		add_meta_box( 'property-filter-details-meta-box', 'Filter Details', array($this, 'output_meta_box'), 'ps-property-filter', 'normal', 'high' );
	}

	/**
	 *	Output meta box interface
	 */
	public function output_meta_box($post) {

		$filter_settings = $this->load_filter_settings($post->ID); 
		wp_nonce_field( 'ps_property_filter_details_meta_box_nonce', 'ps_property_filter_details_meta_box_nonce' );

	    foreach($filter_settings as $setting) {
        	$this->admin_obj->build_admin_field($setting);
	    }
	}

	/**
	 * Save Meta Box
	 */
	public function save_meta_box($post_id) {
		// Bail if we're doing an auto save
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

        // if our nonce isn't there, or we can't verify it, bail
        if( !isset( $_POST['ps_property_filter_details_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['ps_property_filter_details_meta_box_nonce'], 'ps_property_filter_details_meta_box_nonce' ) ) return;

        // if our current user can't edit this post, bail
        if( !current_user_can( 'edit_post', $post_id ) ) return;

        // allow certain attributes
        $allowed = array('a' => array('href' => array()));

        // Load settings and save
        $filter_settings = $this->load_filter_settings($post_id);
        $this->admin_obj->save_meta_box($post_id, $filter_settings, $allowed);
	}

	/************************************************************************/
	// Filter Utilities
	/************************************************************************/

	/**
	 *	Load filter fields
	 */
	public static function load_filter_fields() {
		$filter_fields_init = array(
	        0 => array(
	            'name' => esc_html__('Property Type', 'propertyshift'),
	            'label' => esc_html__('Property Type', 'propertyshift'),
	            'placeholder' => esc_html__('Any', 'propertyshift'),
	            'slug' => 'property_type',
	            'active' => 'true',
	        ),
	        1 => array(
	            'name' => esc_html__('Property Status', 'propertyshift'),
	            'label' => esc_html__('Property Status', 'propertyshift'),
	            'placeholder' => esc_html__('Any', 'propertyshift'),
	            'slug' => 'property_status',
	            'active' => 'true',
	        ),
	        2 => array(
	            'name' => esc_html__('Property City', 'propertyshift'),
	            'label' => esc_html__('Property City', 'propertyshift'),
	            'placeholder' => esc_html__('Any', 'propertyshift'),
	            'slug' => 'property_city',
	            'active' => 'true',
	        ),
	        3 => array(
	            'name' => esc_html__('Price Range', 'propertyshift'),
	            'label' => esc_html__('Price Range', 'propertyshift'),
	            'slug' => 'price',
	            'active' => 'true',
	        ),
	        4 => array(
	            'name' => esc_html__('Bedrooms', 'propertyshift'),
	            'label' => esc_html__('Bedrooms', 'propertyshift'),
	            'placeholder' => esc_html__('Any', 'propertyshift'),
	            'slug' => 'beds',
	            'active' => 'true',
	        ),
	        5 => array(
	            'name' => esc_html__('Bathrooms', 'propertyshift'),
	            'label' => esc_html__('Bathrooms', 'propertyshift'),
	            'placeholder' => esc_html__('Any', 'propertyshift'),
	            'slug' => 'baths',
	            'active' => 'true',
	        ),
	        6 => array(
	            'name' => esc_html__('Area', 'propertyshift'),
	            'label' => esc_html__('Area', 'propertyshift'),
	            'placeholder' => esc_html__('Min', 'propertyshift'),
	            'placeholder_second' => esc_html__('Max', 'propertyshift'),
	            'slug' => 'area',
	            'active' => 'true',
	        ),
	        7 => array(
	            'name' => esc_html__('Property Neighborhood', 'propertyshift'),
	            'label' => esc_html__('Property Neighborhood', 'propertyshift'),
	            'placeholder' => esc_html__('Any', 'propertyshift'),
	            'slug' => 'property_neighborhood',
	            'active' => 'false',
	        ),
	        8 => array(
	            'name' => esc_html__('Property State', 'propertyshift'),
	            'label' => esc_html__('Property State', 'propertyshift'),
	            'placeholder' => esc_html__('Any', 'propertyshift'),
	            'slug' => 'property_state',
	            'active' => 'false',
	        ),
	    );

		$filter_fields_init = apply_filters( 'propertyshift_filter_fields_init_filter', $filter_fields_init);
	    return $filter_fields_init;
	}

	/**
	 *	Get all filter ids
	 */
	public static function get_filter_ids() {
		$filters = get_posts(array('post_type' => 'ps-property-filter', 'posts_per_page' => -1));
		$filter_ids = array();
		foreach($filters as $filter) {
			$filter_ids[$filter->post_title] = $filter->ID;
		}	
		return $filter_ids;
	}

	/**
	 *	Get filter position hook name
	 */
	public function get_filter_position_hook($position) {
		if($position == 'above') { 
            $hook = 'ns_core_before_page_banner'; 
        } else if($position == 'middle') {
            $hook = 'ns_core_after_subheader_title'; 
        } else { 
            $hook = 'ns_core_after_page_banner'; 
        }
        return $hook;
	}


	/************************************************************************/
	// Add Columns
	/************************************************************************/

	/**
	 *	Edit Columns
	 */
	public function edit_property_filter_columns($columns) {
		$columns = array(
	        'cb' => '<input type="checkbox" />',
	        'title' => __( 'Property', 'propertyshift' ),
	        'shortcode' => __( 'Shortcode', 'propertyshift' ),
	        'date' => __( 'Date', 'propertyshift' )
	    );
	    return $columns;
	}

	/**
	 *	Manage Columns
	 */
	public function manage_property_filter_columns($column, $post_id) {
		global $post;

	    switch( $column ) {
	        case 'shortcode' :
	            echo '<pre>[ps_property_filter id="'.$post_id.'"]</pre>';
	            break;
	        default :
	            break;
	    }
	}

	/************************************************************************/
	// Front-end template hooks
	/************************************************************************/

	/**
	 *	Output page banner filter
	 */
	public function page_filter_template_direct() {

		//Global settings
		$property_filter_display = $this->global_settings['ps_property_filter_display'];
		$property_filter_id = $this->global_settings['ps_property_filter_id'];

		// Get page setings
		$page_obj = new NS_Basics_Page_Settings();
		global $post;
    	if(function_exists('ns_core_get_page_id')) { $page_id = ns_core_get_page_id(); } else { $page_id = $post->ID; }
		$page_settings = $page_obj->load_page_settings($page_id);
		$banner_property_filter_override = $page_settings['property_filter_override']['value'];
		if(isset($banner_property_filter_override) && !empty($banner_property_filter_override)) {
	        $property_filter_display = $page_settings['property_filter_override']['children']['property_filter_display']['value'];
	        $property_filter_id = $page_settings['property_filter_override']['children']['property_filter_id']['value'];
	    }

	    if(!empty($property_filter_id) && $property_filter_display == 'true') {

	    	//Get filter settings
	    	$filter_settings = $this->load_filter_settings($property_filter_id);
	    	$property_filter_position = $filter_settings['position']['value'];
			$property_filter_hook = $this->get_filter_position_hook($property_filter_position);
			$property_filter_layout = $filter_settings['layout']['value'];

			//If filter position above, change to classic header
	        if($property_filter_position == 'above') {
	        	function propertyshift_property_filter_change_header($theme_options_init) {
	                if($theme_options_init['ns_core_header_style'] == 'transparent') { $theme_options_init['ns_core_header_style'] = 'classic'; }
	                return $theme_options_init;
	            }
	            add_filter( 'ns_core_theme_options_filter', 'propertyshift_property_filter_change_header' );
	            add_filter( 'ns_core_theme_options_saved_filter', 'propertyshift_property_filter_change_header' );
	        }

			//Output template based on the hook
			add_action($property_filter_hook, function() use ($property_filter_id, $property_filter_layout) {
				$template_args = array();
	            $template_args['id'] = $property_filter_id;
				if($property_filter_layout == 'minimal') {
                	propertyshift_template_loader('property-filter-minimal.php', $template_args);
	            } else {
	                propertyshift_template_loader('property-filter.php', $template_args);
	            }
			});
		}

	}

} ?>