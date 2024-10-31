<?php
// Exit if accessed directly
if (!defined( 'ABSPATH')) { exit; }

/**
 *	PropertyShift_Properties class
 *
 */
class PropertyShift_Properties {

	/************************************************************************/
	// Initialize
	/************************************************************************/

	public function __construct() {
		// Load admin object & settings
		$this->admin_obj = new PropertyShift_Admin();
        $this->global_settings = $this->admin_obj->load_settings();;
	}

	/**
	 *	Init
	 */
	public function init() {
		add_action('init', array( $this, 'rewrite_rules' ));
		add_action( 'ns_basics_page_settings_init_filter', array( $this, 'add_page_settings' ));
		$this->add_image_sizes();
		add_action( 'init', array( $this, 'add_custom_post_type' ));
		add_action( 'init', array( $this, 'property_type_init' ));
		add_action( 'init', array( $this, 'property_status_init' ));
		add_action( 'init', array( $this, 'property_city_init' ));
		add_action( 'init', array( $this, 'property_state_init' ));
		add_action( 'init', array( $this, 'property_neighborhood_init' ));
		add_action( 'init', array( $this, 'property_amenities_init' ));
		add_filter( 'manage_edit-ps-property_columns', array( $this, 'add_properties_columns' ));
		add_action( 'manage_ps-property_posts_custom_column', array( $this, 'manage_properties_columns' ), 10, 2 );
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box'));
		add_action( 'save_post', array( $this, 'save_meta_box'));
		add_filter( 'ns_basics_page_settings_post_types', array( $this, 'add_page_settings_meta_box'), 10, 3 );
		add_action( 'widgets_init', array( $this, 'properties_sidebar_init'));

		//admin property filter
		add_action('restrict_manage_posts', array($this, 'add_admin_properties_filter'));
		add_filter( 'parse_query', array($this, 'admin_process_filter'));

		//add property type tax fields
		add_action('property_type_edit_form_fields', array( $this, 'add_tax_fields'), 10, 2);
		add_action('edited_property_type', array( $this, 'save_tax_fields'), 10, 2);
		add_action('property_type_add_form_fields', array( $this, 'add_tax_fields'), 10, 2 );  
		add_action('created_property_type', array( $this, 'save_tax_fields'), 10, 2);

		//add property status tax fields
		add_action('property_status_edit_form_fields', array( $this, 'add_tax_fields'), 10, 2);
		add_action('edited_property_status', array( $this, 'save_tax_fields'), 10, 2);
		add_action('property_status_add_form_fields', array( $this, 'add_tax_fields'), 10, 2 );  
		add_action('created_property_status', array( $this, 'save_tax_fields'), 10, 2);
		add_action( 'property_status_edit_form_fields', array( $this, 'add_tax_price_range_field'), 10, 2);
		add_action('property_status_add_form_fields', array( $this, 'add_tax_price_range_field'), 10, 2 );

		//add property city tax fields
		add_action('property_city_edit_form_fields', array( $this, 'add_tax_fields'), 10, 2);
		add_action('edited_property_city', array( $this, 'save_tax_fields'), 10, 2);
		add_action('property_city_add_form_fields', array( $this, 'add_tax_fields'), 10, 2 );  
		add_action('created_property_city', array( $this, 'save_tax_fields'), 10, 2);

		//add property state tax fields
		add_action('property_state_edit_form_fields', array( $this, 'add_tax_fields'), 10, 2);
		add_action('edited_property_state', array( $this, 'save_tax_fields'), 10, 2);
		add_action('property_state_add_form_fields', array( $this, 'add_tax_fields'), 10, 2 );  
		add_action('created_property_state', array( $this, 'save_tax_fields'), 10, 2);

		//add property neighborhood tax fields
		add_action('property_neighborhood_edit_form_fields', array( $this, 'add_tax_fields'), 10, 2);
		add_action('edited_property_neighborhood', array( $this, 'save_tax_fields'), 10, 2);
		add_action('property_neighborhood_add_form_fields', array( $this, 'add_tax_fields'), 10, 2 );  
		add_action('created_property_neighborhood', array( $this, 'save_tax_fields'), 10, 2);

		//front-end template hooks
		add_action('propertyshift_property_actions', array($this, 'add_property_share'));
		add_action('propertyshift_property_actions', array($this, 'add_property_favoriting'));
		add_action('ns_core_before_sidebar', array($this, 'add_property_detail_sidebar_template'));
	}

	/**
	 *	Add Image Sizes
	 */
	public function add_image_sizes() {
		add_image_size( 'property-thumbnail', 800, 600, array( 'center', 'center' ) );
	}

	/**
	 *	Rewrite Rules
	 */
	public function rewrite_rules() {
		add_rewrite_rule('^properties/page/([0-9]+)','index.php?pagename=properties&paged=$matches[1]', 'top');
	}

	/************************************************************************/
	// Properties Custom Post Type
	/************************************************************************/

	/**
	 *	Add custom post type
	 */
	public function add_custom_post_type() {
		$properties_slug = $this->global_settings['ps_property_detail_slug'];
	    register_post_type( 'ps-property',
	        array(
	            'labels' => array(
	                'name' => __( 'Properties', 'propertyshift' ),
	                'singular_name' => __( 'Property', 'propertyshift' ),
	                'add_new_item' => __( 'Add New Property', 'propertyshift' ),
	                'search_items' => __( 'Search Properties', 'propertyshift' ),
	                'edit_item' => __( 'Edit Property', 'propertyshift' ),
	            ),
	        'public' => true,
	        'capability_type' => 'ps-property',
	        'capabilities' => array(
			    'edit_post'          => 'edit_ps-property',
			    'read_post'          => 'read_ps-property',
			    'read_posts'         => 'read_ps-propertys',
			    'delete_post'        => 'delete_ps-property',
			    'delete_posts'       => 'delete_ps-propertys',
			    'edit_posts'         => 'edit_ps-propertys',
			    'edit_others_posts'  => 'edit_others_ps-propertys',
			    'publish_posts'      => 'publish_ps-propertys',
			    'read_private_posts' => 'read_private_ps-propertys',
			    'create_posts'       => 'create_ps-propertys',
			  ),
	        'show_in_menu' => true,
	        'menu_position' => 26,
	        'menu_icon' => 'dashicons-admin-home',
	        'has_archive' => false,
	        'supports' => array('title', 'editor', 'revisions', 'thumbnail', 'page_attributes'),
	        'rewrite' => array('slug' => $properties_slug),
	        )
	    );
	}

	/**
	 *	Register meta box
	 */
	public function register_meta_box() {
		add_meta_box( 'property-details-meta-box', 'Property Details', array($this, 'output_meta_box'), 'ps-property', 'normal', 'high' );
	}

	/**
	 *	Load property settings
	 *
	 * @param int $post_id
	 */
	public function load_property_settings($post_id, $return_defaults = false) {

		global $post;

		//populate countries
		$countries = array("Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");
		$countries_select_options = array();
		$countries_select_options[__( 'Selet a country', 'propertyshift' )] = '';
		foreach($countries as $country) { $countries_select_options[$country] = $country; }

		//populate agent select
		$agent_obj = new PropertyShift_Agents();
		$agent_select_options = $agent_obj->get_agents();

        // settings
		$property_settings_init = array(
			'id' => array(
				'group' => 'general',
				'title' => esc_html__('Property Code', 'propertyshift'),
				'description' => esc_html__('An optional string to used to identify properties', 'propertyshift'),
				'name' => 'ps_property_code',
				'type' => 'text',
				'value' => $post_id,
				'order' => 0,
			),
			'featured' => array(
				'group' => 'general',
				'title' => esc_html__('Featured Property', 'propertyshift'),
				'name' => 'ps_property_featured',
				'type' => 'checkbox',
				'value' => 'false',
				'order' => 1,
			),
			'price' => array(
				'group' => 'general',
				'title' => esc_html__('Price', 'propertyshift'),
				'name' => 'ps_property_price',
				'description' => esc_html__('Use only numbers. Do not include commas or dollar sign (ex.- 250000)', 'propertyshift'),
				'type' => 'number',
				'min' => 0,
				'order' => 2,
			),
			'price_postfix' => array(
				'group' => 'general',
				'title' => esc_html__('Price Postfix', 'propertyshift'),
				'name' => 'ps_property_price_postfix',
				'description' => esc_html__('Provide the text displayed after the price (ex.- Per Month)', 'propertyshift'),
				'type' => 'text',
				'order' => 3,
			),
			'beds' => array(
				'group' => 'general',
				'title' => esc_html__('Bedrooms', 'propertyshift'),
				'name' => 'ps_property_bedrooms',
				'description' => esc_html__('Provide the number of bedrooms', 'propertyshift'),
				'type' => 'number',
				'min' => 0,
				'order' => 4,
			),
			'baths' => array(
				'group' => 'general',
				'title' => esc_html__('Bathrooms', 'propertyshift'),
				'name' => 'ps_property_bathrooms',
				'description' => esc_html__('Provide the number of bathrooms', 'propertyshift'),
				'type' => 'number',
				'min' => 0,
				'step' => 0.5,
				'order' => 5,
			),
			'garages' => array(
				'group' => 'general',
				'title' => esc_html__('Garages', 'propertyshift'),
				'name' => 'ps_property_garages',
				'description' => esc_html__('Provide the number of garages', 'propertyshift'),
				'type' => 'number',
				'min' => 0,
				'order' => 6,
			),
			'area' => array(
				'group' => 'general',
				'title' => esc_html__('Area', 'propertyshift'),
				'name' => 'ps_property_area',
				'description' => esc_html__('Provide the area. Use only numbers and decimals, do not include commas.', 'propertyshift'),
				'type' => 'number',
				'min' => 0,
				'step' => 0.01,
				'order' => 7,
			),
			'area_postfix' => array(
				'group' => 'general',
				'title' => esc_html__('Area Postfix', 'propertyshift'),
				'name' => 'ps_property_area_postfix',
				'description' => esc_html__('Provide the text to display directly after the area (ex. - Sq Ft)', 'propertyshift'),
				'type' => 'text',
				'value' => 'Sq Ft',
				'order' => 8,
			),
			'street_address' => array(
				'group' => 'location',
				'title' => esc_html__('Street Address', 'propertyshift'),
				'name' => 'ps_property_address',
				'placeholder' => 'Ex. 123 Smith Drive',
				'description' => __('Provide <strong>only</strong> the street address. Use the categories to the right to select city & state', 'propertyshift'),
				'type' => 'text',
				'order' => 9,
			),
			'postal_code' => array(
				'group' => 'location',
				'title' => esc_html__('Postal Code', 'propertyshift'),
				'name' => 'ps_property_postal_code',
				'description' => esc_html__('Provide the postal code for the property', 'propertyshift'),
				'type' => 'text',
				'order' => 10,
			),
			'country' => array(
				'group' => 'location',
				'title' => esc_html__('Country', 'propertyshift'),
				'name' => 'ps_property_country',
				'description' => esc_html__('Provide the country for the property', 'propertyshift'),
				'type' => 'select',
				'options' => $countries_select_options,
				'order' => 11,
			),
			'latitude' => array(
				'group' => 'location',
				'title' => esc_html__('Latitude', 'propertyshift'),
				'name' => 'ps_property_latitude',
				'description' => sprintf( 
				    __( 'Used only for add-ons, such as <a href="%s" target="_blank">Advanced Maps</a>', 'propertyshift' ), 
				    esc_url( NS_BASICS_SHOP_URL.'plugins/propertyshift/advanced-maps/' ) 
				),
				'type' => 'text',
				'order' => 12,
			),
			'longitude' => array(
				'group' => 'location',
				'title' => esc_html__('Longitude', 'propertyshift'),
				'name' => 'ps_property_longitude',
				'description' => sprintf( 
				    __( 'Used only for add-ons, such as <a href="%s" target="_blank">Advanced Maps</a>', 'propertyshift' ), 
				    esc_url( NS_BASICS_SHOP_URL.'plugins/propertyshift/advanced-maps/' ) 
				),
				'type' => 'text',
				'order' => 13,	
			),
			'description' => array(
				'group' => 'description',
				'name' => 'ps_property_description',
				'type' => 'editor',
				'order' => 14,
				'class' => 'full-width no-padding',
				'esc' => false,
			),
			'gallery' => array(
				'group' => 'gallery',
				'name' => 'ps_additional_img',
				'type' => 'gallery',
				'serialized' => true,
				'order' => 15,
				'class' => 'full-width no-padding',
			),
			'floor_plans' => array(
				'group' => 'floor_plans',
				'name' => 'ps_property_floor_plans',
				'type' => 'floor_plans',
				'serialized' => true,
				'order' => 16,
				'class' => 'full-width no-padding',
			),
			'video_url' => array(
				'group' => 'video',
				'title' => esc_html__('Video URL', 'propertyshift'),
				'name' => 'ps_property_video_url',
				'type' => 'text',
				'order' => 17,
			),
			'video_cover' => array(
				'group' => 'video',
				'title' => esc_html__('Video Cover Image', 'propertyshift'),
				'name' => 'ps_property_video_img',
				'type' => 'image_upload',
				'display_img' => true,
				'order' => 18,
			),
			'agent' => array(
				'group' => 'owner_info',
				'title' => esc_html__('Select an Agent', 'propertyshift'),
				'name' => 'post_author_override', //overrides the author
				'description' => '<a href="users.php?role=ps_agent">Manage Agents</a>',
				'type' => 'select',
				'options' => $agent_select_options,
				'value' => $post->post_author,
				'order' => 19,
			),
			'agent_display' => array(
				'group' => 'owner_info',
				'title' => esc_html__('Display Agent Info on Listing', 'propertyshift'),
				'description' => esc_html__('If checked, the agents info will be publicly displayed on the listing', 'propertyshift'),
				'name' => 'ps_property_agent_display',
				'type' => 'checkbox',
				'value' => true,
				'order' => 20,
			),
		);
		$property_settings_init = apply_filters('propertyshift_property_settings_init_filter', $property_settings_init, $post_id);
		uasort($property_settings_init, 'ns_basics_sort_by_order');

		// Return default settings
		if($return_defaults == true) {
			
			return $property_settings_init;
		
		// Return saved settings
		} else {
			$property_settings = $this->admin_obj->get_meta_box_values($post_id, $property_settings_init);
			return $property_settings;
		}
	}

	/**
	 *	Output meta box interface
	 */
	public function output_meta_box($post) {

		$property_settings = $this->load_property_settings($post->ID); 
		wp_nonce_field( 'ps_property_details_meta_box_nonce', 'ps_property_details_meta_box_nonce' ); ?>
		
		<div class="ns-tabs meta-box-form meta-box-form-property-details">
			<ul class="ns-tabs-nav">
	            <li><a href="#general" title="<?php esc_html_e('General Info', 'propertyshift'); ?>"><i class="fa fa-home"></i> <span class="tab-text"><?php echo esc_html_e('General Info', 'propertyshift'); ?></span></a></li>
	            <li><a href="#location" title="<?php esc_html_e('Location', 'propertyshift'); ?>"><i class="fa fa-map"></i> <span class="tab-text"><?php echo esc_html_e('Location', 'propertyshift'); ?></span></a></li>
	            <li><a href="#description" title="<?php esc_html_e('Description', 'propertyshift'); ?>"><i class="fa fa-pencil-alt"></i> <span class="tab-text"><?php echo esc_html_e('Description', 'propertyshift'); ?></span></a></li>
	            <li><a href="#gallery" title="<?php esc_html_e('Gallery', 'propertyshift'); ?>"><i class="fa fa-image"></i> <span class="tab-text"><?php echo esc_html_e('Gallery', 'propertyshift'); ?></span></a></li>
	            <li><a href="#floor-plans" title="<?php esc_html_e('Floor Plans', 'propertyshift'); ?>"><i class="fa fa-th-large"></i> <span class="tab-text"><?php echo esc_html_e('Floor Plans', 'propertyshift'); ?></span></a></li>
	            <li><a href="#video" title="<?php esc_html_e('Video', 'propertyshift'); ?>"><i class="fa fa-video"></i> <span class="tab-text"><?php echo esc_html_e('Video', 'propertyshift'); ?></span></a></li>
	            <li><a href="#agent" title="<?php esc_html_e('Contacts', 'propertyshift'); ?>"><i class="fa fa-user"></i> <span class="tab-text"><?php echo esc_html_e('Contacts', 'propertyshift'); ?></span></a></li>
	            <?php do_action('propertyshift_after_property_tabs'); ?>
	        </ul>

	        <div class="ns-tabs-content">
        	<div class="tab-loader"><img src="<?php echo esc_url(home_url('/')); ?>wp-admin/images/spinner.gif" alt="" /> <?php echo esc_html_e('Loading...', 'propertyshift'); ?></div>

        	<!--*************************************************-->
	        <!-- GENERAL INFO -->
	        <!--*************************************************-->
	        <div id="general" class="tab-content">
	            <h3><?php echo esc_html_e('General Info', 'propertyshift'); ?></h3>
	            <?php
	            foreach($property_settings as $setting) {
	            	if($setting['group'] == 'general') {
            			$this->admin_obj->build_admin_field($setting);
            		}
	            } ?>
	        </div>

	        <!--*************************************************-->
	        <!-- LOCATION -->
	        <!--*************************************************-->
	        <div id="location" class="tab-content">
	            <h3><?php echo esc_html_e('Location', 'propertyshift'); ?></h3>
	            <?php
	            foreach($property_settings as $setting) {
	            	if($setting['group'] == 'location') {
            			$this->admin_obj->build_admin_field($setting);
            		}
	            } ?>
	        </div>

	        <!--*************************************************-->
	        <!-- DESCRIPTION -->
	        <!--*************************************************-->
	        <div id="description" class="tab-content">
	            <h3><?php echo esc_html_e('Description', 'propertyshift'); ?></h3>
	            <?php
	            foreach($property_settings as $setting) {
	            	if($setting['group'] == 'description') {
            			$this->admin_obj->build_admin_field($setting);
            		}
	            } ?>
	        </div>

	        <!--*************************************************-->
	        <!-- GALLERY -->
	        <!--*************************************************-->
	        <div id="gallery" class="tab-content">
	            <h3><?php echo esc_html_e('Gallery', 'propertyshift'); ?></h3>
	            <?php
	            foreach($property_settings as $setting) {
	            	if($setting['group'] == 'gallery') {
            			$this->admin_obj->build_admin_field($setting);
            		}
	            } ?>
	        </div>

	        <!--*************************************************-->
	        <!-- FLOOR PLANS -->
	        <!--*************************************************-->
	        <div id="floor-plans" class="tab-content">
	            <h3><?php echo esc_html_e('Floor Plans', 'propertyshift'); ?></h3>
	            <?php
	            foreach($property_settings as $setting) {
	            	if($setting['group'] == 'floor_plans') {
            			$this->admin_obj->build_admin_field($setting);
            		}
	            } ?>
	        </div>
	        
	        <!--*************************************************-->
	        <!-- VIDEO -->
	        <!--*************************************************-->
	        <div id="video" class="tab-content">
	            <h3><?php echo esc_html_e('Video', 'propertyshift'); ?></h3>
	            <?php
	            foreach($property_settings as $setting) {
	            	if($setting['group'] == 'video') {
            			$this->admin_obj->build_admin_field($setting);
            		}
	            } ?>
	        </div>

	        <!--*************************************************-->
	        <!-- CONTACT INFO -->
	        <!--*************************************************-->
	        <div id="agent" class="tab-content">
	            <h3><?php echo esc_html_e('Primary Agent', 'propertyshift'); ?></h3>
	            <?php
	            foreach($property_settings as $setting) {
	            	if($setting['group'] == 'owner_info') {
            			$this->admin_obj->build_admin_field($setting);
            		}
	            } ?>
	        </div>

	        <?php do_action('propertyshift_after_property_tab_content', $property_settings); ?>

        	</div><!-- end ns-tabs-content -->
        	<div class="clear"></div>

		</div><!-- end ns-tabs -->

	<?php }

	/**
	 * Save Meta Box
	 */
	public function save_meta_box($post_id) {
		// Bail if we're doing an auto save
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

        // if our nonce isn't there, or we can't verify it, bail
        if( !isset( $_POST['ps_property_details_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['ps_property_details_meta_box_nonce'], 'ps_property_details_meta_box_nonce' ) ) return;

        // if our current user can't edit this post, bail
        if( !current_user_can( 'edit_post', $post_id ) ) return;

        // allow certain attributes
        $allowed = array('a' => array('href' => array()));

        // Load property settings and save
        $property_settings = $this->load_property_settings($post_id);
        $this->admin_obj->save_meta_box($post_id, $property_settings, $allowed);
	}

	/************************************************************************/
	// Admin Properties Filter
	/************************************************************************/
	public function add_admin_properties_filter($post_type) {
		if($post_type == 'ps-property') { ?>
			<select name="ps_beds">
				<option value=""><?php _e( 'Beds', 'propertyshift' ) ?></option>
				<?php for ($x = 1; $x <= 10; $x++) { echo '<option value="'.$x.'">'.$x.'</option>'; } ?>
			</select>
			<select name="ps_baths">
				<option value=""><?php _e( 'Baths', 'propertyshift' ) ?></option>
				<?php for ($x = 1; $x <= 10; $x++) { echo '<option value="'.$x.'">'.$x.'</option>'; } ?>
			</select>
			<select name="ps_agent">
				<option value=""><?php _e( 'Assigned Agent', 'propertyshift' ) ?></option>
				<?php
				$agent_obj = new PropertyShift_Agents();
				$agent_select_options = $agent_obj->get_agents();
				foreach($agent_select_options as $key=>$value) { echo '<option value="'.$value.'">'.$key.'</option>'; } ?>
			</select>
		<?php }
	}

	public function admin_process_filter($query) {
	    global $pagenow;
	    if(is_admin() && $pagenow=='edit.php') {
	    	if(isset($_GET['ps_beds']) && $_GET['ps_beds'] != '') {
		        $query->query_vars['meta_key'] = 'ps_property_bedrooms';
		        $query->query_vars['meta_value'] = esc_attr($_GET['ps_beds']);
		    }
		    if(isset($_GET['ps_baths']) && $_GET['ps_baths'] != '') {
		        $query->query_vars['meta_key'] = 'ps_property_bathrooms';
		        $query->query_vars['meta_value'] = esc_attr($_GET['ps_baths']);
		    }
		    if(isset($_GET['ps_agent']) && $_GET['ps_agent'] != '') {
		        $query->query_vars['meta_key'] = 'post_author_override';
		        $query->query_vars['meta_value'] = esc_attr($_GET['ps_agent']);
		    }
	    }
	}

	/************************************************************************/
	// Property Taxonomies
	/************************************************************************/

	public function create_tax_labels($tax = '', $tax_plural = '') {
		$labels = array();
		if(!empty($tax) && !empty($tax_plural)) {
			$labels = array(
		    'name'                          => $tax,
		    'singular_name'                 => $tax,
		    'search_items'                  => __( 'Search', 'propertyshift' ).' '.$tax_plural,
		    'popular_items'                 => __( 'Popular', 'propertyshift' ).' '.$tax_plural,
		    'all_items'                     => __( 'All', 'propertyshift' ).' '.$tax_plural,
		    'parent_item'                   => __( 'Parent', 'propertyshift' ).' '.$tax,
		    'edit_item'                     => __( 'Edit', 'propertyshift' ).' '.$tax,
		    'update_item'                   => __( 'Update', 'propertyshift' ).' '.$tax,
		    'add_new_item'                  => __( 'Add New', 'propertyshift' ).' '.$tax,
		    'new_item_name'                 => __( 'New', 'propertyshift' ).' '.$tax,
		    'separate_items_with_commas'    => sprintf(__( 'Separate %s with commas', 'propertyshift' ), $tax_plural),
		    'add_or_remove_items'           => __( 'Add or remove', 'propertyshift' ).' '.$tax_plural,
		    'choose_from_most_used'         => __( 'Choose from most used', 'propertyshift' ).' '.$tax_plural,
		    );
		}
		return $labels;
	}

	/**
	 *	Register property type taxonomy
	 */
	public function property_type_init() {
		$property_type_tax_slug = $this->global_settings['ps_property_type_tax_slug'];
	    $labels = $this->create_tax_labels(__( 'Property Type', 'propertyshift' ), __( 'Property Types', 'propertyshift' ));
	    
	    register_taxonomy(
	        'property_type',
	        'ps-property',
	        array(
	            'label'         => __( 'Property Types', 'propertyshift' ),
	            'labels'        => $labels,
	            'hierarchical'  => true,
	            'rewrite' => array( 'slug' => $property_type_tax_slug ),
	            'capabilities' => array(
	            	'manage_terms' => 'manage_property_type',
    				'edit_terms' => 'edit_property_type',
    				'delete_terms' => 'delete_property_type',
	            	'assign_terms' => 'assign_property_type',
	            ),
	        )
	    );
	}

	/**
	 *	Register property status taxonomy
	 */
	public function property_status_init() {
		$property_status_tax_slug = $this->global_settings['ps_property_status_tax_slug'];
	    $labels = $this->create_tax_labels(__( 'Property Status', 'propertyshift' ), __( 'Property Statuses', 'propertyshift' ));
	    
	    register_taxonomy(
	        'property_status',
	        'ps-property',
	        array(
	            'label'         => __( 'Property Status', 'propertyshift' ),
	            'labels'        => $labels,
	            'hierarchical'  => true,
	            'rewrite' => array( 'slug' => $property_status_tax_slug ),
	            'capabilities' => array(
	            	'manage_terms' => 'manage_property_status',
    				'edit_terms' => 'edit_property_status',
    				'delete_terms' => 'delete_property_status',
	            	'assign_terms' => 'assign_property_status',
	            ),
	        )
	    );
	}

	/**
	 *	Register property city taxonomy
	 */
	public function property_city_init() {
		$property_city_tax_slug = $this->global_settings['ps_property_city_tax_slug'];
	    $labels = $this->create_tax_labels(__( 'City', 'propertyshift' ), __( 'Cities', 'propertyshift' ));
	    
	    register_taxonomy(
	        'property_city',
	        'ps-property',
	        array(
	            'label'         => __( 'City', 'propertyshift' ),
	            'labels'        => $labels,
	            'hierarchical'  => true,
	            'rewrite' => array( 'slug' => $property_city_tax_slug ),
	            'capabilities' => array(
	            	'manage_terms' => 'manage_property_city',
    				'edit_terms' => 'edit_property_city',
    				'delete_terms' => 'delete_property_city',
	            	'assign_terms' => 'assign_property_city',
	            ),
	        )
	    );
	}

	/**
	 *	Register property state taxonomy
	 */
	public function property_state_init() {
		$property_state_tax_slug = $this->global_settings['ps_property_state_tax_slug'];
	    $labels = $this->create_tax_labels(__( 'State', 'propertyshift' ), __( 'States', 'propertyshift' ));
	    
	    register_taxonomy(
	        'property_state',
	        'ps-property',
	        array(
	            'label'         => __( 'State', 'propertyshift' ),
	            'labels'        => $labels,
	            'hierarchical'  => true,
	            'rewrite' => array( 'slug' => $property_state_tax_slug ),
	            'capabilities' => array(
	            	'manage_terms' => 'manage_property_state',
    				'edit_terms' => 'edit_property_state',
    				'delete_terms' => 'delete_property_state',
	            	'assign_terms' => 'assign_property_state',
	            ),
	        )
	    );
	}

	/**
	 *	Register property neighborhood taxonomy
	 */
	public function property_neighborhood_init() {
		$property_neighborhood_tax_slug = $this->global_settings['ps_property_neighborhood_tax_slug'];
	    $labels = $this->create_tax_labels(__( 'Neighborhood', 'propertyshift' ), __( 'Neighborhoods', 'propertyshift' ));
	    
	    register_taxonomy(
	        'property_neighborhood',
	        'ps-property',
	        array(
	            'label'         => __( 'Neighborhood', 'propertyshift' ),
	            'labels'        => $labels,
	            'hierarchical'  => true,
	            'rewrite' => array( 'slug' => $property_neighborhood_tax_slug ),
	            'capabilities' => array(
	            	'manage_terms' => 'manage_property_neighborhood',
    				'edit_terms' => 'edit_property_neighborhood',
    				'delete_terms' => 'delete_property_neighborhood',
	            	'assign_terms' => 'assign_property_neighborhood',
	            ),
	        )
	    );
	}

	/**
	 *	Register property amenities taxonomy
	 */
	public function property_amenities_init() {
		$property_amenities_tax_slug = $this->global_settings['ps_property_amenities_tax_slug'];
	    $labels = $this->create_tax_labels(__( 'Amenity', 'propertyshift' ), __( 'Amenities', 'propertyshift' ));
	    
	    register_taxonomy(
	        'property_amenities',
	        'ps-property',
	        array(
	            'label'         => __( 'Amenities', 'propertyshift' ),
	            'labels'        => $labels,
	            'hierarchical'  => true,
	            'rewrite' => array( 'slug' => $property_amenities_tax_slug ),
	            'capabilities' => array(
	            	'manage_terms' => 'manage_property_amenities',
    				'edit_terms' => 'edit_property_amenities',
    				'delete_terms' => 'delete_property_amenities',
	            	'assign_terms' => 'assign_property_amenities',
	            ),
	        )
	    );
	}

	/************************************************************************/
	// Add Columns to Properties Post Type
	/************************************************************************/

	/**
	 *	Add properties columns
	 *
	 * @param array $columns
	 *
	 */
	public function add_properties_columns($columns) {
		$columns = array(
	        'cb' => '<input type="checkbox" />',
	        'title' => __( 'Property', 'propertyshift' ),
	        'thumbnail' => __('Image', 'propertyshift'),
	        'location' => __( 'Location', 'propertyshift' ),
	        'type' => __( 'Type', 'propertyshift' ),
	        'status' => __( 'Status', 'propertyshift' ),
	        'price'  => __( 'Price','propertyshift' ),
	        'agent' => __('Assigned Agent', 'propertyshift'),
	        'date' => __( 'Date', 'propertyshift' )
	    );
	    return $columns;
	}

	/**
	 *	Manage properties columns
	 *
	 * @param string $column
	 * @param int $post_id 
	 */
	public function manage_properties_columns($column, $post_id) {
		global $post;
		$property_settings = $this->load_property_settings($post_id); 

	    switch( $column ) {

	        case 'thumbnail' :
	            if(has_post_thumbnail()) { echo the_post_thumbnail('thumbnail'); } else { echo '--'; }
	            break;

	        case 'price' :
	            $price = $property_settings['price']['value'];
	            if(!empty($price)) { $price = $this->get_formatted_price($price); }
	            if(empty($price)) { echo '--'; } else { echo $price; }
	            break;

	        case 'location' :

	        	$address = $this->get_full_address($post_id, $exclude = array('Postal Code', 'Country'), $return = 'array');
	          	foreach($address as $key=>$value) { echo $key.': '.$value.'<br/>'; }
	            break;

	        case 'type' :

	        	$property_type = $this->get_tax($post_id, 'property_type');
	            if(empty( $property_type)) { echo '--'; } else { echo $property_type; }
	            break;

	        case 'status' :

	        	$property_status = $this->get_tax($post_id, 'property_status');
	            if(empty($property_status)) { echo '--'; } else { echo $property_status; }
	            break;

	        case 'agent' :

	        	$agent_id = get_the_author_meta('ID');
	            if(!empty($agent_id)) { 
	            	$agent = get_userdata($agent_id); ?>
	            	<a href="<?php echo get_edit_user_link($agent_id); ?>"><?php echo $agent->display_name; ?></a>
	            <?php } else {
	            	echo '--';
	            }
	            break;

	        default :
	            break;
	    }
	}

	/************************************************************************/
	// Customize Property Taxonomies Admin Page
	/************************************************************************/

	/**
	 *	Add taxonomy fields
	 *
	 * @param string $tag
	 */
	public function add_tax_fields($tag) {
		if(is_object($tag)) { $t_id = $tag->term_id; } else { $t_id = ''; }
	    $term_meta = get_option( "taxonomy_$t_id");
	    ?>
	    <tr class="form-field">
	        <th scope="row" valign="top"><label for="cat_Image_url"><?php esc_html_e('Category Image Url', 'propertyshift'); ?></label></th>
	        <td>
	            <div class="admin-module admin-module-tax-field admin-module-tax-img no-border">
	                <input type="text" class="property-tax-img" name="term_meta[img]" id="term_meta[img]" size="3" style="width:60%;" value="<?php echo $term_meta['img'] ? $term_meta['img'] : ''; ?>">
	                <input class="button admin-button ns_upload_image_button" type="button" value="<?php esc_html_e('Upload Image', 'propertyshift'); ?>" />
	                <span class="button button-secondary remove"><?php esc_html_e('Remove', 'propertyshift'); ?></span><br/>
	                <p class="description"><?php esc_html_e('Image for Term, use full url', 'propertyshift'); ?></p>
	            </div>
	        </td>
	    </tr>
	<?php }

	/**
	 *	Add taxonomy price range field
	 *
	 * @param string $tag
	 */
	public function add_tax_price_range_field($tag) {
		if(is_object($tag)) { $t_id = $tag->term_id; } else { $t_id = ''; }
	    $term_meta = get_option( "taxonomy_$t_id");
	    ?>
	    <tr class="form-field">
	        <th scope="row" valign="top">
	            <strong><?php esc_html_e('Price Range Settings', 'propertyshift'); ?></strong>
	            <p class="admin-module-note"><?php esc_html_e('Settings here will override the defaults configured in the plugin settings.', 'propertyshift'); ?></p>
	        </th>
	        <td>
	            <div class="admin-module admin-module-tax-field tax-price-range-field no-border">
	                <label for="price_range_min"><?php esc_html_e('Minimum', 'propertyshift'); ?></label>
	                <input type="number" class="property-tax-price-range-min" name="term_meta[price_range_min]" id="term_meta[price_range_min]" size="3" value="<?php echo $term_meta['price_range_min'] ? $term_meta['price_range_min'] : ''; ?>">
	            </div>
	            <div class="admin-module admin-module-tax-field tax-price-range-field no-border">
	                <label for="price_range_max"><?php esc_html_e('Maximum', 'propertyshift'); ?></label>
	                <input type="number" class="property-tax-price-range-max" name="term_meta[price_range_max]" id="term_meta[price_range_max]" size="3" value="<?php echo $term_meta['price_range_max'] ? $term_meta['price_range_max'] : ''; ?>">
	            </div>
	            <div class="admin-module admin-module-tax-field tax-price-range-field no-border">
	                <label for="price_range_min_start"><?php esc_html_e('Minimum Start', 'propertyshift'); ?></label>
	                <input type="number" class="property-tax-price-range-min-start" name="term_meta[price_range_min_start]" id="term_meta[price_range_min_start]" size="3" value="<?php echo $term_meta['price_range_min_start'] ? $term_meta['price_range_min_start'] : ''; ?>">
	            </div>
	            <div class="admin-module admin-module-tax-field tax-price-range-field no-border">
	                <label for="price_range_max_start"><?php esc_html_e('Maximum Start', 'propertyshift'); ?></label>
	                <input type="number" class="property-tax-price-range-max-start" name="term_meta[price_range_max_start]" id="term_meta[price_range_max_start]" size="3" value="<?php echo $term_meta['price_range_max_start'] ? $term_meta['price_range_max_start'] : ''; ?>">
	            </div>
	        </td>
	    </tr>
	<?php }

	/**
	 *	Save taxonomy fields
	 *
	 * @param int $term_id
	 */
	public function save_tax_fields($term_id) {
		if ( isset( $_POST['term_meta'] ) ) {
	        $t_id = $term_id;
	        $term_meta = get_option( "taxonomy_$t_id");
	        $cat_keys = array_keys($_POST['term_meta']);
	            foreach ($cat_keys as $key){
	            if (isset($_POST['term_meta'][$key])){
	                $term_meta[$key] = $_POST['term_meta'][$key];
	            }
	        }
	        //save the option array
	        update_option( "taxonomy_$t_id", $term_meta );
	    }
	}

	/************************************************************************/
	// Property Utilities
	/************************************************************************/

	/**
	 *	Count properties
	 *
	 * @param string $type
	 * @param int $user_id 
	 */
	public function count_properties($type, $user_id = null) {
		$args_total_properties = array(
            'post_type' => 'ps-property',
            'showposts' => -1,
            'author' => $user_id,
            'post_status' => $type 
        );

        $meta_posts = get_posts( $args_total_properties );
        $meta_post_count = count( $meta_posts );
        unset( $meta_posts);
        return $meta_post_count;
	}

	/**
	 *	Get formatted price
	 *
	 * @param string $price
	 */
	public function get_formatted_price($price) {

	    $currency_symbol = $this->global_settings['ps_currency_symbol'];
	    $currency_symbol_position = $this->global_settings['ps_currency_symbol_position'];
	    $currency_thousand = $this->global_settings['ps_thousand_separator'];
	    $currency_decimal = $this->global_settings['ps_decimal_separator'];
	    $currency_decimal_num =  $this->global_settings['ps_num_decimal'];

	    if(!empty($price)) { $price = number_format($price, $currency_decimal_num, $currency_decimal, $currency_thousand); }
	    if($currency_symbol_position == 'before') { $price = $currency_symbol.$price; } else { $price = $price.$currency_symbol; }

	    return $price;
	}

	/**
	 *	Get formatted area
	 *
	 * @param string $area
	 */
	public function get_formatted_area($area) {
		
	    $decimal_num_area = $this->global_settings['ps_num_decimal_area'];
	    $decimal_area = $this->global_settings['ps_decimal_separator_area'];
	    $thousand_area =  $this->global_settings['ps_thousand_separator_area'];

    	if(!empty($area)) { $area = number_format($area, $decimal_num_area, $decimal_area, $thousand_area); }
    	return $area;
	}

	/**
	 *	Get property taxonomy
	 *
	 * @param int $post_id
	 * @param string $tax
	 * @param string $array
	 */
	public function get_tax($post_id, $tax, $array = null, $hide_empty = true) {
		$output = '';

	    if($hide_empty == false) {
	        $tax_terms =  get_terms(['taxonomy' => $tax, 'hide_empty' => false,]);
	    } else {
	        $tax_terms = get_the_terms( $post_id, $tax);
	    }

	    if($tax_terms && ! is_wp_error($tax_terms)) : 
	        
	        //populate term links
	        $term_links = array();
	        foreach ($tax_terms as $term) {
	            if($array == 'true') {
	                $term_links[] = $term->slug;
	            } else {
	                $term_links[] = '<a href="'. esc_attr(get_term_link($term->slug, $tax)) .'">'.$term->name.'</a>' ;
	            }
	        }

	        //determine output
	        if($array == 'true') { $output = $term_links;  } else { $output = join( ", ", $term_links); }
	    
	    endif;
	    return $output;
	}

	/**
	 *	Retrieves the FULL address
	 *
	 * @param int $post_id
	 *
	 */
	public function get_full_address($post_id, $exclude = array(), $return = 'string') {
	    $property_settings = $this->load_property_settings($post_id);
	   	$property_address = array();
	    if(!in_array('Address', $exclude)) { $property_address['Address'] = $property_settings['street_address']['value']; }
	    if(!in_array('Neighborhood', $exclude)) { $property_address['Neighborhood'] = $this->get_tax($post_id, 'property_neighborhood'); }
	    if(!in_array('City', $exclude)) { $property_address['City'] = $this->get_tax($post_id, 'property_city'); }
	    if(!in_array('State', $exclude)) { $property_address['State'] = $this->get_tax($post_id, 'property_state'); }
	    if(!in_array('Country', $exclude)) { $property_address['Country'] = $property_settings['country']['value']; }
	    if(!in_array('Postal Code', $exclude)) { $property_address['Postal Code'] = $property_settings['postal_code']['value']; }

	    $property_address = apply_filters('propertyshift_full_address', $property_address);
	    $property_address = array_filter($property_address);

	    if($return == 'string') {
	    	$property_address = implode(', ',$property_address);
	    	return $property_address;
	    } else {
	    	return $property_address;
	    }
	}

	/**
	 *	Get property amenities
	 *
	 * @param int $post_id
	 * @param boolean $hide_empty
	 * @param boolean $array
	 */
	public function get_tax_amenities($post_id, $hide_empty = true, $array = null) {
		$property_amenities = '';
	    $property_amenities_links = array();

	    if($hide_empty == false) {
	        $property_amenities_terms =  get_terms(['taxonomy' => 'property_amenities', 'hide_empty' => false,]);
	    } else {
	        $property_amenities_terms = get_the_terms( $post_id, 'property_amenities' );
	    }

	    if ( $property_amenities_terms && ! is_wp_error( $property_amenities_terms) ) : 
	        foreach ( $property_amenities_terms as $property_amenity_term ) {
	            if($array == 'true') {
	                $property_amenities_links[] = $property_amenity_term->slug;
	            } else {
	                if(has_term($property_amenity_term->slug, 'property_amenities', $post_id)) { $icon = '<i class="fa fa-check icon"></i>'; } else { $icon = '<i class="fa fa-times icon"></i>'; }
	                $property_amenities_links[] = '<li><a href="'. esc_attr(get_term_link($property_amenity_term->slug, 'property_amenities')) .'">'.$icon.'<span>'.$property_amenity_term->name.'</span></a></li>' ;
	            }
	        } 
	    endif;

	    if($array == 'true') { 
	        $property_amenities = $property_amenities_links;
	    } else { 
	        $property_amenities = join( '', $property_amenities_links ); 
	        if(!empty($property_amenities)) { $property_amenities = '<ul class="amenities-list clean-list">'.$property_amenities.'</ul>'; }
	    }

	    return $property_amenities;
	}

	/**
	 *	Get property walkscore
	 *
	 * @param int $post_id
	 *
	 */
	public function get_walkscore($lat, $lon, $address) {
		$address = urlencode($address);
	    $url = "http://api.walkscore.com/score?format=json&address=$address";
	    $url .= "&lat=$lat&lon=$lon&wsapikey=f6c3f50b09a7ce69d6d276015e57e996";
	    $request = wp_remote_get($url);
	    $str = wp_remote_retrieve_body($request);
	    return $str;
	}


	/************************************************************************/
	// Property Page Settings Methods
	/************************************************************************/
	
	/**
	 *	Add page settings meta box
	 *
	 * @param array $post_types
	 */
	public function add_page_settings_meta_box($post_types) {
		$post_types[] = 'ps-property';
    	return $post_types;
	}

	/**
	 *	Add page settings
	 *
	 * @param array $page_settings_init
	 */
	public function add_page_settings($page_settings_init) {

		// Add filter banner options
		$page_settings_init['property_filter_override'] = array(
			'group' => 'banner',
			'title' => esc_html__('Use Custom Property Filter Settings', 'propertyshift'),
			'name' => 'ns_banner_property_filter_override',
			'description' => esc_html__('The global property filter settings can be configured in PropertyShift > Settings', 'propertyshift'),
			'value' => 'false',
			'type' => 'switch',
			'order' => 14,
			'children' => array(
				'property_filter_display' => array(
					'title' => esc_html__('Display Property Filter', 'propertyshift'),
					'name' => 'ns_banner_property_filter_display',
					'type' => 'checkbox',
					'value' => 'true',
				),
				'property_filter_id' => array(
					'title' => esc_html__('Select a Filter', 'propertyshift'),
					'name' => 'ns_banner_property_filter_id',
					'type' => 'select',
					'options' => PropertyShift_Filters::get_filter_ids(),
				),
			),
		);

		// Set default page layout
		if(isset($_GET['post_type']) && $_GET['post_type'] == 'ps-property') { $page_settings_init['page_layout']['value'] = 'right sidebar'; }
			
		// Set default page sidebar
		if(isset($_GET['post_type']) && $_GET['post_type'] == 'ps-property') { $page_settings_init['page_layout_widget_area']['value'] = 'properties_sidebar'; }

		return $page_settings_init;
	}

	/************************************************************************/
	// Property Detail Methods
	/************************************************************************/

	/**
	 *	Load property detail items
	 */
	public static function load_property_detail_items() {
		$property_detail_items_init = array(
	        0 => array(
	            'name' => esc_html__('Overview', 'propertyshift'),
	            'label' => esc_html__('Overview', 'propertyshift'),
	            'slug' => 'overview',
	            'active' => 'true',
	            'sidebar' => 'false',
	        ),
	        1 => array(
	            'name' => esc_html__('Description', 'propertyshift'),
	            'label' => esc_html__('Description', 'propertyshift'),
	            'slug' => 'description',
	            'active' => 'true',
	            'sidebar' => 'false',
	        ),
	        2 => array(
	            'name' => esc_html__('Address', 'propertyshift'),
	            'label' => esc_html__('Address', 'propertyshift'),
	            'slug' => 'address',
	            'active' => 'true',
	            'sidebar' => 'false',
	        ),
	        3 => array(
	            'name' => esc_html__('Gallery', 'propertyshift'),
	            'label' => esc_html__('Gallery', 'propertyshift'),
	            'slug' => 'gallery',
	            'active' => 'true',
	            'sidebar' => 'false',
	        ),
	        4 => array(
	            'name' => esc_html__('Property Details', 'propertyshift'),
	            'label' => esc_html__('Property Details', 'propertyshift'),
	            'slug' => 'property_details',
	            'active' => 'true',
	            'sidebar' => 'false',
	        ),
	        5 => array(
	            'name' => esc_html__('Video', 'propertyshift'),
	            'label' => esc_html__('Video', 'propertyshift'),
	            'slug' => 'video',
	            'active' => 'true',
	            'sidebar' => 'false',
	        ),
	        6 => array(
	            'name' => esc_html__('Amenities', 'propertyshift'),
	            'label' => esc_html__('Amenities', 'propertyshift'),
	            'slug' => 'amenities',
	            'active' => 'true',
	            'sidebar' => 'false',
	        ),
	        7 => array(
	            'name' => esc_html__('Floor Plans', 'propertyshift'),
	            'label' => esc_html__('Floor Plans', 'propertyshift'),
	            'slug' => 'floor_plans',
	            'active' => 'true',
	            'sidebar' => 'false',
	        ),
	        8 => array(
	            'name' => esc_html__('Walk Score', 'propertyshift'),
	            'label' => esc_html__('Walk Score', 'propertyshift'),
	            'slug' => 'walk_score',
	            'active' => 'true',
	            'sidebar' => 'false',
	        ),
	        9 => array(
	            'name' => esc_html__('Agent Info', 'propertyshift'),
	            'label' => 'Agent Information',
	            'slug' => 'agent_info',
	            'active' => 'true',
	            'sidebar' => 'false',
	        ),
	        10 => array(
	            'name' => esc_html__('Related Properties', 'propertyshift'),
	            'label' => 'Related Properties',
	            'slug' => 'related',
	            'active' => 'true',
	            'sidebar' => 'false',
	        ),
	    );

		$property_detail_items_init = apply_filters( 'propertyshift_property_detail_items_init_filter', $property_detail_items_init);
	    return $property_detail_items_init;
	}

	/************************************************************************/
	// Front-end Template Hooks
	/************************************************************************/

	/**
	 *	Add property sharing
	 */
	public function add_property_share() {
		$property_listing_display_share = esc_attr(get_option('ps_property_listing_display_share', 'true'));
		if(class_exists('NS_Basics_Post_Sharing') && $property_listing_display_share == 'true') {
			$post_share_obj = new NS_Basics_Post_Sharing();
			echo $post_share_obj->build_post_sharing_links();
		}
	}

	/**
	 *	Add property favoriting
	 */
	public function add_property_favoriting() {
		$property_listing_display_favorite = esc_attr(get_option('ps_property_listing_display_favorite', 'true'));
		if(class_exists('NS_Basics_Post_Likes') && $property_listing_display_favorite == 'true') {
			$post_likes_obj = new NS_Basics_Post_Likes();
			global $post;
			echo $post_likes_obj->get_post_likes_button($post->ID);
		}
	}

	/**
	 *	Add property detail sidebar template
	 */
	public function add_property_detail_sidebar_template() {
		if(is_singular('ps-property') && function_exists('propertyshift_template_loader')) {
			propertyshift_template_loader('loop_property_single.php', ['location' => 'sidebar']); 
		}
	}


	/************************************************************************/
	// Register Widget Areas
	/************************************************************************/

	/**
	 *	Register properties sidebar
	 */
	public static function properties_sidebar_init() {
		register_sidebar( array(
	        'name' => esc_html__( 'Properties Sidebar', 'propertyshift' ),
	        'id' => 'properties_sidebar',
	        'before_widget' => '<div class="widget widget-sidebar widget-sidebar-properties %2$s">',
	        'after_widget' => '</div>',
	        'before_title' => '<h4 class="widget-header">',
	        'after_title' => '</h4>',
	    ));
	}

}
?>