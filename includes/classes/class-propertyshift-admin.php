<?php
// Exit if accessed directly
if (!defined( 'ABSPATH')) { exit; }

/**
 *	PropertyShift_Admin class
 *
 *  Outputs admin pages and provides the core methods for building admin interfaces.
 */
class PropertyShift_Admin extends NS_Basics_Admin {

	/************************************************************************/
	// Initialize
	/************************************************************************/

	/**
	 *	Init
	 */
	public function init() {
		add_action('admin_menu', array( $this, 'admin_menu' ));
		add_action('admin_init', array( $this, 'register_settings' ));
		add_filter('ns_basics_admin_field_types', array( $this, 'add_field_types' ));
	}

	/**
	 *	Add admin menu
	 */
	public function admin_menu() {
		add_menu_page('PropertyShift', 'PropertyShift', 'administrator', 'propertyshift-settings', array( $this, 'settings_page' ), PROPERTYSHIFT_DIR.'/images/icon.png', 25);
	    add_submenu_page('propertyshift-settings', 'Settings', 'Settings', 'administrator', 'propertyshift-settings');
		add_submenu_page('propertyshift-settings', 'Properties', 'Properties', 'administrator', 'edit.php?post_type=ps-property');
		add_submenu_page('propertyshift-settings', 'Property Filters', 'Property Filters', 'administrator', 'edit.php?post_type=ps-property-filter');
	    add_submenu_page('propertyshift-settings', 'Agents', 'Agents', 'administrator', 'users.php?role=ps_agent');
	    add_submenu_page('propertyshift-settings', 'Add-Ons', 'Add-Ons', 'administrator', 'propertyshift-add-ons', array( $this, 'add_ons_page' ));
	    add_submenu_page('propertyshift-settings', 'License Keys', 'License Keys', 'administrator', 'propertyshift-license-keys', array( $this, 'license_keys_page' ));
	    add_submenu_page('propertyshift-settings', 'Help', 'Help', 'administrator', 'propertyshift-help', array( $this, 'help_page' ));
	}

	/**
	 *	Register Settings
	 */
	public function register_settings() {
		$return_defaults = true;
		$settings = $this->load_settings($return_defaults);
	    foreach($settings as $key=>$field) { 
	    	if(!empty($field['args'])) { $args = $field['args']; } else { $args = null; }
	    	register_setting( 'propertyshift-settings-group', $key, $args); 
	    } 
	    do_action( 'propertyshift_register_settings');
	}
	

	/**
	 * Load settings
	 *
	 * @param boolean $return_defaults
	 *
	 */
	public function load_settings($return_defaults = false, $single_setting = null, $single_esc = true) {

		$settings_init = array(
			'ps_property_detail_slug' => array('value' => 'properties', 'esc' => true, 'args' => array('sanitize_callback' => 'sanitize_title')),
			'ps_property_type_tax_slug' => array('value' => 'property-type', 'esc' => true, 'args' => array('sanitize_callback' => 'sanitize_title')),
			'ps_property_status_tax_slug' => array('value' => 'property-status', 'esc' => true, 'args' => array('sanitize_callback' => 'sanitize_title')),
			'ps_property_neighborhood_tax_slug' => array('value' => 'neighborhood', 'esc' => true, 'args' => array('sanitize_callback' => 'sanitize_title')),
			'ps_property_city_tax_slug' => array('value' => 'city', 'esc' => true, 'args' => array('sanitize_callback' => 'sanitize_title')),
			'ps_property_state_tax_slug' => array('value' => 'state', 'esc' => true, 'args' => array('sanitize_callback' => 'sanitize_title')),
			'ps_property_amenities_tax_slug' => array('value' => 'property-amenity', 'esc' => true, 'args' => array('sanitize_callback' => 'sanitize_title')),
			'ps_property_filter_display' => array('value' => 'false'),
			'ps_property_filter_id' => array('value' => ''),
			'ps_properties_page' => array('value' => ''),
			'ps_num_properties_per_page' => array('value' => 12),
			'ps_properties_default_layout' => array('value' => 'grid'),
			'ps_property_listing_header_display' => array('value' => 'true'),
			'ps_property_listing_default_sortby' => array('value' => 'date_desc'),
			'ps_property_listing_crop' => array('value' => 'true'),
			'ps_property_listing_display_time' => array('value' => 'true'),
			'ps_property_listing_display_favorite' => array('value' => 'true'),
			'ps_property_listing_display_share' => array('value' => 'true'),
			'ps_property_detail_default_layout' => array('value' => 'right sidebar'),
			'ps_property_detail_id' => array('value' => 'false'),
			'ps_property_detail_items' => array('value' => PropertyShift_Properties::load_property_detail_items(), 'esc' => false),
			'ps_property_detail_amenities_hide_empty' => array('value' => 'false'),
			'ps_property_detail_agent_contact_form' => array('value' => 'false'),
			'ps_agent_detail_slug' => array('value' => 'agents'),
			'ps_num_agents_per_page' => array('value' => 12),
			'ps_agent_listing_crop' => array('value' => 'true'),
			'ps_agent_detail_items' => array('value' => PropertyShift_Agents::load_agent_detail_items(), 'esc' => false),
			'ps_agent_form_message_placeholder' => array('value' => esc_html__('I am interested in this property and would like to know more.', 'propertyshift')),
			'ps_agent_form_success' => array('value' => esc_html__('Thanks! Your email has been delivered!', 'propertyshift')),
			'ps_agent_form_submit_text' => array('value' => esc_html__('Contact Agent', 'propertyshift')),
			'ps_members_profile_page' => array('value' => ''),
			'ps_members_auto_agent_profile' => array('value' => 'false'),
			'ps_members_submit_property_approval' => array('value' => 'true'),
			'ps_members_add_types' => array('value' => 'true'),
			'ps_members_add_status' => array('value' => 'true'),
			'ps_members_add_neighborhood' => array('value' => 'true'),
			'ps_members_add_city' => array('value' => 'true'),
			'ps_members_add_state' => array('value' => 'true'),
			'ps_members_add_amenities' => array('value' => 'true'),
			'ps_currency_symbol' => array('value' => '$'),
			'ps_currency_symbol_position' => array('value' => 'before'),
			'ps_thousand_separator' => array('value' => ','),
			'ps_decimal_separator' => array('value' => '.'),
			'ps_num_decimal' => array('value' => 0),
			'ps_default_area_postfix' => array('value' => 'Sq Ft'),
			'ps_thousand_separator_area' => array('value' => ','),
			'ps_decimal_separator_area' => array('value' => '.'),
			'ps_num_decimal_area' => array('value' => 0),
		);
		$settings_init = apply_filters('propertyshift_settings_init_filter', $settings_init);
		$settings = $this->get_settings($settings_init, $return_defaults, $single_setting, $single_esc);
		if($single_setting == null) { $settings = apply_filters( 'propertyshift_settings_saved_filter', $settings); }
		return $settings;
		
	}

	/************************************************************************/
	// Output Pages
	/************************************************************************/

	/**
	 *	Settings page
	 */
	public function settings_page() {
	    
	    $content_nav = array(
	        array('name' => esc_html__('Properties', 'propertyshift'), 'link' => '#properties', 'icon' => 'fa-home', 'order' => 1),
	        array('name' => esc_html__('Agents & Users', 'propertyshift'), 'link' => '#agents', 'icon' => 'fa-user-tie', 'order' => 2),
	        array('name' => esc_html__('Currency & Numbers', 'propertyshift'), 'link' => '#currency', 'icon' => 'fa-money-bill-alt', 'order' => 4),
	    );
	    $content_nav = apply_filters( 'propertyshift_setting_tabs_filter', $content_nav);
	    usort($content_nav, function ($a, $b) {return ($a["order"]-$b["order"]); });
	    
	    //add alerts
	    $alerts = array();
	    if(!current_theme_supports('propertyshift')) {
	        $current_theme = wp_get_theme();
	        $incompatible_theme_alert = $this->admin_alert('info', esc_html__('The active theme ('.$current_theme->name.') does not declare support for PropertyShift.', 'propertyshift'), $action = '#', $action_text = esc_html__('Get a compatible theme', 'propertyshift'), true); 
	        $alerts[] = $incompatible_theme_alert; 
	    }

	    $properties_page = esc_attr(get_option('ps_properties_page'));
	    if(empty($properties_page)) {
	        $properties_page_alert = $this->admin_alert('warning', esc_html__('You have not set your properties listing page. Go to Properties > Property Listing Options, to set this field.', 'propertyshift'), $action = null, $action_text = null, true);
	        $alerts[] = $properties_page_alert; 
	    }

	    $args = array(
			'page_name' => 'PropertyShift',
			'settings_group' => 'propertyshift-settings-group',
			'pages' => $this->get_admin_pages(),
			'display_actions' => 'true',
			'content' => $this->settings_page_content(),
			'content_class' => null,
			'content_nav'=> $content_nav,
			'alerts' => $alerts,
			'ajax' => true,
			'icon' => PROPERTYSHIFT_DIR.'/images/icon-real-estate.svg',
		);
	    echo $this->build_admin_page($args);
	}

	/**
	 *	Settings page content
	 */
	public function settings_page_content() {
		ob_start(); 

		$settings = $this->load_settings();
		?>

		<div id="properties" class="tab-content">
	        <h2><?php echo esc_html_e('Properties Settings', 'propertyshift'); ?></h2>

	        <div class="ns-accordion" data-name="property-url">
	            <div class="ns-accordion-header"><i class="fa fa-chevron-right"></i> <?php echo esc_html_e('Property URL Options', 'propertyshift'); ?></div>
	            <div class="ns-accordion-content">

	            	<p class="admin-module-note"><?php esc_html_e('After changing slugs, make sure you re-save your permalinks in Settings > Permalinks.', 'propertyshift'); ?></p>
                	<br/>

                	<?php
                	$property_slug_field = array(
                		'title' => esc_html__('Properties Slug', 'propertyshift'),
                		'name' => 'ps_property_detail_slug',
                		'description' => esc_html__('Default: properties', 'propertyshift'),
                		'value' => $settings['ps_property_detail_slug'],
                		'type' => 'text',
                	);
                	$this->build_admin_field($property_slug_field);

                	$property_type_tax_slug_field = array(
                		'title' => esc_html__('Property Type Taxonomy Slug', 'propertyshift'),
                		'name' => 'ps_property_type_tax_slug',
                		'description' => esc_html__('Default: property-type', 'propertyshift'),
                		'value' => $settings['ps_property_type_tax_slug'],
                		'type' => 'text',
                	);
                	$this->build_admin_field($property_type_tax_slug_field);

                	$property_status_tax_slug_field = array(
                		'title' => esc_html__('Property Status Taxonomy Slug', 'propertyshift'),
                		'name' => 'ps_property_status_tax_slug',
                		'description' => esc_html__('Default: property-status', 'propertyshift'),
                		'value' => $settings['ps_property_status_tax_slug'],
                		'type' => 'text',
                	);
                	$this->build_admin_field($property_status_tax_slug_field);

                	$property_neighborhood_tax_slug_field = array(
                		'title' => esc_html__('Property Neighborhood Taxonomy Slug', 'propertyshift'),
                		'name' => 'ps_property_neighborhood_tax_slug',
                		'description' => esc_html__('Default: neighborhood', 'propertyshift'),
                		'value' => $settings['ps_property_neighborhood_tax_slug'],
                		'type' => 'text',
                	);
                	$this->build_admin_field($property_neighborhood_tax_slug_field);

                	$property_city_tax_slug_field = array(
                		'title' => esc_html__('Property City Taxonomy Slug', 'propertyshift'),
                		'name' => 'ps_property_city_tax_slug',
                		'description' => esc_html__('Default: city', 'propertyshift'),
                		'value' => $settings['ps_property_city_tax_slug'],
                		'type' => 'text',
                	);
                	$this->build_admin_field($property_city_tax_slug_field);

                	$property_state_tax_slug_field = array(
                		'title' => esc_html__('Property State Taxonomy Slug', 'propertyshift'),
                		'name' => 'ps_property_state_tax_slug',
                		'description' => esc_html__('Default: state', 'propertyshift'),
                		'value' => $settings['ps_property_state_tax_slug'],
                		'type' => 'text',
                	);
                	$this->build_admin_field($property_state_tax_slug_field);

                	$property_amenities_tax_slug_field = array(
                		'title' => esc_html__('Property Amenities Taxonomy Slug', 'propertyshift'),
                		'name' => 'ps_property_amenities_tax_slug',
                		'description' => esc_html__('Default: property-amenity', 'propertyshift'),
                		'value' => $settings['ps_property_amenities_tax_slug'],
                		'type' => 'text',
                	);
                	$this->build_admin_field($property_amenities_tax_slug_field);
                	?>
	            </div>
	        </div>

	        <div class="ns-accordion" data-name="property-filter">
	            <div class="ns-accordion-header"><i class="fa fa-chevron-right"></i> <?php echo esc_html_e('Property Filter Options', 'propertyshift'); ?></div>
	            <div class="ns-accordion-content">
	            	<?php
                	$display_property_filter_field = array(
                		'title' => esc_html__('Display Property Filter in Page Banners', 'propertyshift'),
                		'name' => 'ps_property_filter_display',
                		'value' => $settings['ps_property_filter_display'],
                		'type' => 'switch',
                	);
                	$this->build_admin_field($display_property_filter_field);

                	$default_property_filter_field = array(
                		'title' => esc_html__('Default Banner Filter', 'propertyshift'),
                		'name' => 'ps_property_filter_id',
                		'description' => esc_html__('This can be overriden on individual pages from the page settings meta box.', 'propertyshift'),
                		'value' => $settings['ps_property_filter_id'],
                		'type' => 'select',
                		'options' => PropertyShift_Filters::get_filter_ids(),
                	);
                	$this->build_admin_field($default_property_filter_field);
                	?>
	            </div>
	        </div>

	        <div class="ns-accordion" data-name="property-listing">
	            <div class="ns-accordion-header"><i class="fa fa-chevron-right"></i> <?php echo esc_html_e('Property Listing Options', 'propertyshift'); ?></div>
	            <div class="ns-accordion-content">

	            	<?php
	            	$page_options = array('Select a page' => '');
	            	$pages = get_pages();
	            	foreach ( $pages as $page ) { $page_options[esc_attr($page->post_title)] = get_page_link( $page->ID ); }
	            	$property_listing_page_field = array(
                		'title' => esc_html__('Select Your Property Listings Page', 'propertyshift'),
                		'name' => 'ps_properties_page',
                		'value' => $settings['ps_properties_page'],
                		'type' => 'select',
                		'options' => $page_options,
                	);
                	$this->build_admin_field($property_listing_page_field);

                	$num_properties_per_page_field = array(
                		'title' => esc_html__('Number of Properties Per Page', 'propertyshift'),
                		'name' => 'ps_num_properties_per_page',
                		'value' => $settings['ps_num_properties_per_page'],
                		'type' => 'number',
                	);
                	$this->build_admin_field($num_properties_per_page_field);

                	$properties_tax_layout_field = array(
                		'title' => esc_html__('Properties Taxonomy Layout', 'propertyshift'),
                		'name' => 'ps_properties_default_layout',
                		'value' => $settings['ps_properties_default_layout'],
                		'type' => 'radio_image',
                		'options' => array(
                			esc_html__('Grid', 'propertyshift') => array('value' => 'grid'), 
							esc_html__('Row', 'propertyshift') => array('value' => 'row'),
                		),
                	);
                	$this->build_admin_field($properties_tax_layout_field);

                	$display_listing_header_field = array(
                		'title' => esc_html__('Display Listing Header?', 'propertyshift'),
                		'name' => 'ps_property_listing_header_display',
                		'description' => esc_html__('Toggle on/off the filter options that display directly above property listings.', 'propertyshift'),
                		'value' => $settings['ps_property_listing_header_display'],
                		'type' => 'switch',
                	);
                	$this->build_admin_field($display_listing_header_field);

                	$default_sort_by_field = array(
                		'title' => esc_html__('Default Sort By', 'propertyshift'),
                		'name' => 'ps_property_listing_default_sortby',
                		'description' => esc_html__('Choose the default sorting for property listings.', 'propertyshift'),
                		'value' => $settings['ps_property_listing_default_sortby'],
                		'type' => 'select',
                		'options' => array(
                			esc_html__('New to Old', 'propertyshift') => 'date_desc',
                			esc_html__('Old to New', 'propertyshift') => 'date_asc',
                			esc_html__('Price (High to Low)', 'propertyshift') => 'price_desc',
                			esc_html__('Price (Low to High)', 'propertyshift') => 'price_asc',
                		),
                	);
                	$this->build_admin_field($default_sort_by_field);

                	$property_img_size = propertyshift_get_image_size('property-thumbnail');
                	$property_listing_crop_description = '';
					if(!empty($property_img_size)) { $property_listing_crop_description = esc_html__('If active, property listing thumbnails will be cropped to: ', 'propertyshift').$property_img_size['width'].' x '.$property_img_size['height'].' pixels'; }
                	$property_listing_crop_field = array(
                		'title' => esc_html__('Hard crop property listing featured images?', 'propertyshift'),
                		'name' => 'ps_property_listing_crop',
                		'description' => $property_listing_crop_description,
                		'value' => $settings['ps_property_listing_crop'],
                		'type' => 'switch',
                	);
                	$this->build_admin_field($property_listing_crop_field);

                	$time_stamp_field = array(
                		'title' => esc_html__('Display Time Stamp?', 'propertyshift'),
                		'name' => 'ps_property_listing_display_time',
                		'value' => $settings['ps_property_listing_display_time'],
                		'type' => 'switch',
                	);
                	$this->build_admin_field($time_stamp_field);

                	$listing_display_favorite_field = array(
                		'title' => esc_html__('Allow users to favorite properties?', 'propertyshift'),
                		'name' => 'ps_property_listing_display_favorite',
                		'value' => $settings['ps_property_listing_display_favorite'],
                		'type' => 'switch',
                	);
                	$this->build_admin_field($listing_display_favorite_field);

                	$listing_display_share_field = array(
                		'title' => esc_html__('Allow users to share properties?', 'propertyshift'),
                		'name' => 'ps_property_listing_display_share',
                		'value' => $settings['ps_property_listing_display_share'],
                		'type' => 'switch',
                	);
                	$this->build_admin_field($listing_display_share_field);
                	?>
	            </div>
	        </div>

	        <div class="ns-accordion" data-name="property-detail">
	            <div class="ns-accordion-header"><i class="fa fa-chevron-right"></i> <?php echo esc_html_e('Property Detail Options', 'propertyshift'); ?></div>
	            <div class="ns-accordion-content">

	            	<?php
	            	$property_detail_default_layout_field = array(
                		'title' => esc_html__('Select the default page layout for property detail pages', 'propertyshift'),
                		'name' => 'ps_property_detail_default_layout',
                		'value' => $settings['ps_property_detail_default_layout'],
                		'type' => 'radio_image',
                		'options' => array(
                			esc_html__('Full Width', 'propertyshift') => array('value' => 'full', 'icon' => NS_BASICS_PLUGIN_DIR.'/images/full-width-icon.png'), 
							esc_html__('Right Sidebar', 'propertyshift') => array('value' => 'right sidebar', 'icon' => NS_BASICS_PLUGIN_DIR.'/images/right-sidebar-icon.png'),
							esc_html__('Left Sidebar', 'propertyshift') => array('value' => 'left sidebar', 'icon' => NS_BASICS_PLUGIN_DIR.'/images/left-sidebar-icon.png'),
                		),
                	);
                	$this->build_admin_field($property_detail_default_layout_field);
                	
                	$property_detail_id_field = array(
                		'title' => esc_html__('Show Property Code on Front-End', 'propertyshift'),
                		'name' => 'ps_property_detail_id',
                		'value' => $settings['ps_property_detail_id'],
                		'type' => 'switch',
                	);
                	$this->build_admin_field($property_detail_id_field);

                	$property_detail_items_field = array(
                		'title' => esc_html__('Property Detail Layout', 'propertyshift'),
                		'name' => 'ps_property_detail_items',
                		'description' => esc_html__('Drag & drop the sections to rearrange their order', 'propertyshift'),
                		'value' => $settings['ps_property_detail_items'],
                		'type' => 'sortable',
                		'display_sidebar' => true,
                		'children' => array(
                			'hide_empty_amenities' => array(
                				'title' => esc_html__('Hide empty amenities?', 'propertyshift'),
	                			'name' => 'ps_property_detail_amenities_hide_empty',
	                			'value' => $settings['ps_property_detail_amenities_hide_empty'],
	                			'type' => 'checkbox',
	                			'parent_val' => 'amenities',
                			),
                			'agent_contact_form' => array(
                				'title' => esc_html__('Display agent contact form underneath agent information?', 'propertyshift'),
	                			'name' => 'ps_property_detail_agent_contact_form',
	                			'description' => esc_html__('Configure the agent contact form options in the Agent Settings tab.', 'propertyshift'),
	                			'value' => $settings['ps_property_detail_agent_contact_form'],
	                			'type' => 'checkbox',
	                			'parent_val' => 'agent_info',
                			),
                		),
                	);
                	$this->build_admin_field($property_detail_items_field);
	            	?>
	            </div>
	        </div>

	        <!-- Hook in for Add-Ons -->
        	<?php do_action( 'propertyshift_after_property_settings'); ?>

	    </div><!-- end property settings -->

	    <div id="agents" class="tab-content">
	        <h2><?php echo esc_html_e('Agent & User Settings', 'propertyshift'); ?></h2>

	        <div class="ns-accordion" data-name="agent-listing">
	            <div class="ns-accordion-header"><i class="fa fa-chevron-right"></i> <?php echo esc_html_e('Agent Listing Options', 'propertyshift'); ?></div>
	            <div class="ns-accordion-content">

	            	<?php
                	$agents_num_field = array(
                		'title' => esc_html__('Number of Agents Per Page', 'propertyshift'),
                		'name' => 'ps_num_agents_per_page',
                		'value' => $settings['ps_num_agents_per_page'],
                		'type' => 'number',
                	);
                	$this->build_admin_field($agents_num_field);

                	$agent_listing_crop_field = array(
                		'title' => esc_html__('Hard crop agent listing featured images?', 'propertyshift'),
                		'name' => 'ps_agent_listing_crop',
                		'description' => esc_html__('If active, agent listing thumbnails will be cropped to 800 x 600 pixels.', 'propertyshift'),
                		'value' => $settings['ps_agent_listing_crop'],
                		'type' => 'switch',
                	);
                	$this->build_admin_field($agent_listing_crop_field);
	            	?>
	            </div>
	        </div>

	        <div class="ns-accordion" data-name="agent-detail">
	            <div class="ns-accordion-header"><i class="fa fa-chevron-right"></i> <?php echo esc_html_e('Agent Profile Options', 'propertyshift'); ?></div>
	            <div class="ns-accordion-content">

	            	<?php
	            	$agent_profile_slug_field = array(
                		'title' => esc_html__('Agent Profile Slug', 'propertyshift'),
                		'name' => 'ps_agent_detail_slug',
                		'description' => esc_html__('After changing the slug, make sure you re-save your permalinks in Settings > Permalinks. The default slug is agents.', 'propertyshift'),
                		'value' => $settings['ps_agent_detail_slug'],
                		'type' => 'text',
                	);
                	$this->build_admin_field($agent_profile_slug_field);

                	$page_options = array('Select a page' => '');
			        $page_options_ids = array('Select a page' => '');
			        $pages = get_pages();
			        foreach ( $pages as $page ) { 
			        	$page_options[esc_attr($page->post_title)] = get_page_link( $page->ID ); 
			        	$page_options_ids[esc_attr($page->post_title)] = $page->ID; 
			        }
			        
			        $agent_profile_page_field = array(
		                'title' => esc_html__('Select Agent Profile Page', 'propertyshift'),
		                'name' => 'ps_members_profile_page',
		                'description' => esc_html__('Create a page and add the [ps_agent_profile] shortcode.', 'propertyshift'),
		                'value' => $settings['ps_members_profile_page'],
		                'type' => 'select',
		                'options' => $page_options_ids,
		            );
		            $this->build_admin_field($agent_profile_page_field);

	            	$agent_detail_items_field = array(
                		'title' => esc_html__('Agent Profile Layout', 'propertyshift'),
                		'name' => 'ps_agent_detail_items',
                		'description' => esc_html__('Drag & drop the sections to rearrange their order', 'propertyshift'),
                		'value' => $settings['ps_agent_detail_items'],
                		'type' => 'sortable',
                		'display_sidebar' => true, 
                		'children' => array(
                			'form_message_placeholder' => array(
                				'title' => esc_html__('Message Placeholder on Property Pages', 'propertyshift'),
	                			'name' => 'ps_agent_form_message_placeholder',
	                			'value' => $settings['ps_agent_form_message_placeholder'],
	                			'type' => 'text',
	                			'parent_val' => 'contact',
                			),
                			'form_success' => array(
                				'title' => esc_html__('Success Message', 'propertyshift'),
	                			'name' => 'ps_agent_form_success',
	                			'value' => $settings['ps_agent_form_success'],
	                			'type' => 'text',
	                			'parent_val' => 'contact',
                			),
                			'form_submit_text' => array(
                				'title' => esc_html__('Submit Button Text', 'propertyshift'),
	                			'name' => 'ps_agent_form_submit_text',
	                			'value' => $settings['ps_agent_form_submit_text'],
	                			'type' => 'text',
	                			'parent_val' => 'contact',
                			),
                		),
                	);
                	$this->build_admin_field($agent_detail_items_field);
                	?>

	            </div>
	        </div>

	        <div class="ns-accordion" data-name="agent-capabilities">
	            <div class="ns-accordion-header"><i class="fa fa-chevron-right"></i> <?php echo esc_html_e('Agent Capabilities', 'propertyshift'); ?></div>
	            <div class="ns-accordion-content">

	            	<?php
		            $auto_agent_profile = array(
		                'title' => esc_html__('Automatically show agents in listing on registration', 'propertyshift'),
		                'name' => 'ps_members_auto_agent_profile',
		                'description' => esc_html__('When users register as an agent role, their profile will automatically appear in agent listings.', 'propertyshift'),
		                'value' => $settings['ps_members_auto_agent_profile'],
		                'type' => 'switch',
		            );
		            $this->build_admin_field($auto_agent_profile);

		            $submit_property_approval = array(
		                'title' => esc_html__('Agent property submissions must be approved before being published', 'propertyshift'),
		                'name' => 'ps_members_submit_property_approval',
		                'value' => $settings['ps_members_submit_property_approval'],
		                'type' => 'switch',
		            );
		            $this->build_admin_field($submit_property_approval);

		            $submit_add_types = array(
		                'title' => esc_html__('Agents can manage property types', 'propertyshift'),
		                'name' => 'ps_members_add_types',
		                'value' => $settings['ps_members_add_types'],
		                'type' => 'switch',
		            );
		            $this->build_admin_field($submit_add_types);

		            $submit_add_status = array(
		                'title' => esc_html__('Agents can manage property statuses', 'propertyshift'),
		                'name' => 'ps_members_add_status',
		                'value' => $settings['ps_members_add_status'],
		                'type' => 'switch',
		            );
		            $this->build_admin_field($submit_add_status);

		            $submit_add_neighborhood = array(
		                'title' => esc_html__('Agents can manage property neighborhoods', 'propertyshift'),
		                'name' => 'ps_members_add_neighborhood',
		                'value' => $settings['ps_members_add_neighborhood'],
		                'type' => 'switch',
		            );
		            $this->build_admin_field($submit_add_neighborhood);

		            $submit_add_cities = array(
		                'title' => esc_html__('Agents can manage property cities', 'propertyshift'),
		                'name' => 'ps_members_add_city',
		                'value' => $settings['ps_members_add_city'],
		                'type' => 'switch',
		            );
		            $this->build_admin_field($submit_add_cities);

		            $submit_add_states = array(
		                'title' => esc_html__('Agents can manage property states', 'propertyshift'),
		                'name' => 'ps_members_add_state',
		                'value' => $settings['ps_members_add_state'],
		                'type' => 'switch',
		            );
		            $this->build_admin_field($submit_add_states);

		            $submit_add_amenities = array(
		                'title' => esc_html__('Agent can manage property amenities', 'propertyshift'),
		                'name' => 'ps_members_add_amenities',
		                'value' => $settings['ps_members_add_amenities'],
		                'type' => 'switch',
		            );
		            $this->build_admin_field($submit_add_amenities);

			        do_action( 'propertyshift_after_agent_capabilities_settings'); ?>

	            </div>
	        </div>

        	<?php do_action( 'propertyshift_after_agent_settings'); ?>

	    </div><!-- end agent settings -->

	    <div id="currency" class="tab-content">
	        <h2><?php echo esc_html_e('Currency & Numbers', 'propertyshift'); ?></h2>

	        <?php
	        $currency_symbol_field = array(
                'title' => esc_html__('Currency Symbol', 'propertyshift'),
                'name' => 'ps_currency_symbol',
                'value' => $settings['ps_currency_symbol'],
                'type' => 'text',
            );
            $this->build_admin_field($currency_symbol_field);

            $currency_symbol_position_field = array(
                'title' => esc_html__('Currency Symbol Position', 'propertyshift'),
                'name' => 'ps_currency_symbol_position',
                'value' => $settings['ps_currency_symbol_position'],
                'type' => 'radio_image',
                'options' => array(esc_html__('Display before price', 'propertyshift') => array('value' => 'before'), esc_html__('Display after price', 'propertyshift') => array('value' => 'after')),
            );
            $this->build_admin_field($currency_symbol_position_field);

            $currency_thousand_field = array(
                'title' => esc_html__('Thousand Separator', 'propertyshift'),
                'name' => 'ps_thousand_separator',
                'value' => $settings['ps_thousand_separator'],
                'type' => 'text',
            );
            $this->build_admin_field($currency_thousand_field);

            $currency_decimal_field = array(
                'title' => esc_html__('Decimal Separator', 'propertyshift'),
                'name' => 'ps_decimal_separator',
                'value' => $settings['ps_decimal_separator'],
                'type' => 'text',
            );
            $this->build_admin_field($currency_decimal_field);

            $currency_decimal_num_field = array(
                'title' => esc_html__('Number of Decimals', 'propertyshift'),
                'name' => 'ps_num_decimal',
                'value' => $settings['ps_num_decimal'],
                'type' => 'number',
                'min' => 0,
                'max' => 5,
            );
            $this->build_admin_field($currency_decimal_num_field);

            echo '<br/><br/><h2>'.esc_html__('Area Formatting', 'propertyshift').'</h2>';
            $area_postfix_field = array(
                'title' => esc_html__('Deafult Area Postfix', 'propertyshift'),
                'name' => 'ps_default_area_postfix',
                'value' => $settings['ps_default_area_postfix'],
                'type' => 'text',
            );
            $this->build_admin_field($area_postfix_field);

            $area_thousand_field = array(
                'title' => esc_html__('Area Thousand Separator', 'propertyshift'),
                'name' => 'ps_thousand_separator_area',
                'value' => $settings['ps_thousand_separator_area'],
                'type' => 'text',
            );
            $this->build_admin_field($area_thousand_field);

            $area_decimal_field = array(
                'title' => esc_html__('Area Decimal Separator', 'propertyshift'),
                'name' => 'ps_decimal_separator_area',
                'value' => $settings['ps_decimal_separator_area'],
                'type' => 'text',
            );
            $this->build_admin_field($area_decimal_field);

            $area_decimal_num_field = array(
                'title' => esc_html__('Area Number of Decimals', 'propertyshift'),
                'name' => 'ps_num_decimal_area',
                'value' => $settings['ps_num_decimal_area'],
                'type' => 'number',
                'min' => 0,
                'max' => 5,
            );
            $this->build_admin_field($area_decimal_num_field);

	        do_action( 'propertyshift_after_currency_settings'); ?>

	    </div><!-- end currency settings -->

	    <?php do_action( 'propertyshift_after_settings'); ?>

		<?php $output = ob_get_clean();
    	return $output;
	}

	/**
	 *	Add-Ons page
	 */
	public function add_ons_page() {
		$args = array(
			'page_name' => 'PropertyShift',
			'pages' => $this->get_admin_pages(),
			'content' => $this->add_ons_page_content(),
			'content_class' => 'ns-modules',
			'icon' => PROPERTYSHIFT_DIR.'/images/icon-real-estate.svg',
		);
	    echo $this->build_admin_page($args);
	}

	public function add_ons_page_content() {
		ob_start();

		$raw_addons = wp_remote_get(
	        constant('NS_BASICS_SHOP_URL').'/plugins/propertyshift/add-ons/',
	        array('timeout'     => 10, 'redirection' => 5, 'sslverify'   => false)
	    );

	    if(!is_wp_error($raw_addons)) {
	        echo '<div class="ns-module-group">';
	        $raw_addons = wp_remote_retrieve_body($raw_addons);
	        $dom = new DOMDocument();
	        libxml_use_internal_errors(true);
	        $dom->loadHTML( $raw_addons );

	        $finder = new DomXPath($dom);
	        $classname = "ns-product-grid";
	        $nodes = $finder->query("//*[contains(@class, '$classname')]");
	 
	        function DOMinnerHTML(DOMNode $element) { 
	            $innerHTML = ""; 
	            $children  = $element->childNodes;
	            $anchors = $element->getElementsByTagName('a');
	            foreach($anchors as $anchor) { $anchor->setAttribute('target','_blank'); }
	            foreach ($children as $child) { $innerHTML .= $element->ownerDocument->saveHTML($child); }
	            return $innerHTML; 
	        } 

	        foreach($nodes as $node) { echo '<div class="admin-module add-on">'.DOMinnerHTML($node).'</div>'; }
	        echo '</div>';
	    } else { 
	    	esc_html_e('There was an issue connecting to the add-ons store. Get more information about our add-ons', 'propertyshift'); 
	    	echo ' <a href="'.NS_BASICS_SHOP_URL.'" target="_blank">here</a>.';
	    }

		$output = ob_get_clean();
    	return $output;
	}

	/**
	 *	License Keys page
	 */
	public function license_keys_page() {
		$args = array(
			'page_name' => 'PropertyShift',
			'settings_group' => 'propertyshift-license-keys-group',
			'pages' => $this->get_admin_pages(),
			'content' => $this->license_keys_page_content(),
			'display_actions' => 'true',
			'ajax' => false,
			'icon' => PROPERTYSHIFT_DIR.'/images/icon-real-estate.svg',
		);
	    echo $this->build_admin_page($args);
	}

	public function license_keys_page_content() {
		ob_start(); ?>

	    <div class="admin-module-note">
	        <?php esc_html_e('All premium add-ons require a valid license key for updates and support.', 'propertyshift'); ?><br/>
	        <?php esc_html_e('Your licenses keys can be found in your account on the Nightshift Products website.', 'propertyshift'); ?>
	    </div><br/>
	    
	    <?php do_action( 'propertyshift_register_license_keys'); ?>

	    <?php $output = ob_get_clean();
	    return $output;
	}

	/**
	 *	Help page
	 */
	public function help_page() {
		$args = array(
			'page_name' => 'PropertyShift',
			'pages' => $this->get_admin_pages(),
			'content' => $this->resources_page_content(),
			'display_actions' => 'false',
			'icon' => PROPERTYSHIFT_DIR.'/images/icon-real-estate.svg',
		);
	    echo $this->build_admin_page($args);
	}

	/**
	 *	Get admin pages
	 */
	public function get_admin_pages() {
		$pages = array();
	    $pages[] = array('slug' => 'propertyshift-settings', 'name' => esc_html__('Settings', 'propertyshift'));
	    $pages[] = array('slug' => 'propertyshift-add-ons', 'name' => esc_html__('Add-Ons', 'propertyshift'));
	    $pages[] = array('slug' => 'propertyshift-license-keys', 'name' => esc_html__('License Keys', 'propertyshift'));
	    $pages[] = array('slug' => 'propertyshift-help', 'name' => esc_html__('Help', 'propertyshift'));
	    return $pages;
	}

	/************************************************************************/
	// Add Field Types
	/************************************************************************/
	
	/**
	 *	Add field types
	 */
	public function add_field_types($field_types) {
		$field_types['floor_plans'] = array($this, 'build_admin_field_floor_plans');
		return $field_types;
	}

	/**
	 *	Build floor plans admin field
	 */
	public function build_admin_field_floor_plans($field) { ?>

		<div class="repeater-container floor-plans">
			<div class="repeater-items">
				<?php 
				$floor_plans = $field['value']; 
				if(!empty($floor_plans) && !empty($floor_plans[0])) { 
	                $count = 0;                     
	                foreach ($floor_plans as $floor_plan) { ?>
	                	<div class="ns-accordion">
                            <div class="ns-accordion-header"><i class="fa fa-chevron-right"></i> <span class="repeater-title-mirror floor-plan-title-mirror"><?php echo $floor_plan['title']; ?></span> <span class="action delete delete-floor-plan"><i class="fa fa-trash"></i> Delete</span></div>
                            <div class="ns-accordion-content floor-plan-item"> 
                                <div class="floor-plan-left"> 
                                    <label><?php esc_html_e('Title:', 'propertyshift'); ?> </label> <input class="repeater-title floor-plan-title" type="text" name="<?php echo $field['name']; ?>[<?php echo $count; ?>][title]" placeholder="New Floor Plan" value="<?php echo $floor_plan['title']; ?>" /><br/>
                                    <label><?php esc_html_e('Size:', 'propertyshift'); ?> </label> <input type="text" name="<?php echo $field['name']; ?>[<?php echo $count; ?>][size]" value="<?php echo $floor_plan['size']; ?>" /><br/>
                                    <label><?php esc_html_e('Rooms:', 'propertyshift'); ?> </label> <input type="number" name="<?php echo $field['name']; ?>[<?php echo $count; ?>][rooms]" value="<?php echo $floor_plan['rooms']; ?>" /><br/>
                                    <label><?php esc_html_e('Bathrooms:', 'propertyshift'); ?> </label> <input type="number" name="<?php echo $field['name']; ?>[<?php echo $count; ?>][baths]" value="<?php echo $floor_plan['baths']; ?>" /><br/>
                                </div>
                                <div class="floor-plan-right">
                                    <label><?php esc_html_e('Description:', 'propertyshift'); ?></label>
                                    <textarea name="<?php echo $field['name']; ?>[<?php echo $count; ?>][description]"><?php echo $floor_plan['description']; ?></textarea>
                                    <div class="floor-plan-img">
                                        <label><?php esc_html_e('Image:', 'propertyshift'); ?> </label> 
                                        <input type="text" name="<?php echo $field['name']; ?>[<?php echo $count; ?>][img]" value="<?php echo $floor_plan['img']; ?>" />
                                        <input id="_btn" class="ns_upload_image_button" type="button" value="<?php esc_html_e('Upload Image', 'propertyshift'); ?>" />
                                        <span class="button-secondary remove"><?php esc_html_e('Remove', 'propertyshift'); ?></span>
                                    </div>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div> 
	                	<?php $count++; 
	                }
				} ?>
			</div>

			<?php if(empty($floor_plans) && empty($floor_plans[0])) { echo '<p class="admin-module-note no-floor-plan">'.esc_html__('No floor plans were found.', 'propertyshift').'</p>'; } ?>
	        <span class="admin-button add-repeater"><i class="fa fa-plus"></i> <?php esc_html_e('Create New Floor Plan', 'propertyshift'); ?></span>
	    </div>
	<?php }


}

?>