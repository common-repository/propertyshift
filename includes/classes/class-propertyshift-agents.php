<?php
// Exit if accessed directly
if (!defined( 'ABSPATH')) { exit; }

/**
 *	PropertyShift_Agents class
 *
 */
class PropertyShift_Agents {

	/************************************************************************/
	// Initialize
	/************************************************************************/

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

		//basic setup
		$this->add_image_sizes();
		add_action('init', array( $this, 'rewrite_rules' ));
		add_action('admin_init', array( $this, 'add_agent_role' ));
	
		//add & save user fields
		add_action( 'show_user_profile', array($this, 'create_agent_user_fields'));
        add_action( 'edit_user_profile', array($this, 'create_agent_user_fields'));
        add_action( 'personal_options_update', array($this, 'save_agent_user_fields'));
        add_action( 'edit_user_profile_update', array($this, 'save_agent_user_fields'));
        add_action( 'ns_basics_edit_profile_fields', array($this, 'create_agent_user_fields'));
        add_action( 'ns_basics_edit_profile_save', array($this, 'save_agent_user_fields'));
        add_action( 'user_register', array($this, 'on_agent_register'));

        //manage user columns
		add_filter( 'manage_users_columns', array($this, 'new_modify_user_table'));
		add_filter( 'manage_users_custom_column', array($this, 'new_modify_user_table_row'), 10, 3 );

        //front-end agent profiles
		add_filter( 'query_vars', array($this, 'agent_query_vars'));
		add_action('init', array($this, 'agent_rewrite_rule'));
		add_filter( 'request', array($this, 'agent_profile_template_redirect'));
		add_filter( 'author_link', array( $this, 'change_author_link'), 10, 2 );
		add_filter( 'document_title_parts', array($this, 'change_agent_doc_title'), 10, 2 );
		add_filter('the_title', array($this, 'change_agent_title'), 10, 2);

        //front-end template hooks
        add_action('ns_basics_dashboard_stats', array($this, 'add_dashboard_stats'));
		add_action('ns_basics_after_dashboard', array($this, 'add_dashboard_widgets'));
		add_action('ns_core_before_sidebar', array($this, 'add_agent_detail_sidebar_template'));
	}

	/************************************************************************/
	// Basic Setup
	/************************************************************************/

	/**
	 *	Add Image Sizes
	 */
	public function add_image_sizes() {
		add_image_size( 'agent-thumbnail', 800, 600, array( 'center', 'center' ) );
	}

	/**
	 *	Rewrite Rules
	 */
	public function rewrite_rules() {
		add_rewrite_rule('^agents/page/([0-9]+)','index.php?pagename=agents&paged=$matches[1]', 'top');
	}

	/**
	 *	Add Agent Role
	 */
	public function add_agent_role() {
		global $wp_roles;
		remove_role('ps_agent');
    	$author_role = $wp_roles->get_role('subscriber');
		add_role('ps_agent', 'Agent', $author_role->capabilities);

		$role = $wp_roles->get_role('ps_agent');               
	    $role->add_cap( 'edit_ps-property');
	    $role->add_cap( 'read_ps-property');
	    $role->add_cap( 'read_ps-propertys');
	    $role->add_cap( 'delete_ps-property');
	    $role->add_cap( 'delete_ps-propertys');
	    $role->add_cap( 'edit_ps-propertys');
	    $role->add_cap( 'read_private_ps-propertys');
	    $role->add_cap( 'create_ps-propertys');

	    $role->add_cap( 'assign_property_type');
	    $role->add_cap( 'assign_property_status');
	    $role->add_cap( 'assign_property_neighborhood');
	    $role->add_cap( 'assign_property_city');
	    $role->add_cap( 'assign_property_state');
	    $role->add_cap( 'assign_property_amenities');

	    // Allow agents to manage property types
	    $agent_add_types = $this->global_settings['ps_members_add_types'];
	    if($agent_add_types == 'true') {
		    $role->add_cap( 'manage_property_type');
		    $role->add_cap( 'edit_property_type');
		    $role->add_cap( 'delete_property_type');
		}

		// Allow agents to manage property statuses
	    $agent_add_status = $this->global_settings['ps_members_add_status'];
	    if($agent_add_status == 'true') {
		    $role->add_cap( 'manage_property_status');
		    $role->add_cap( 'edit_property_status');
		    $role->add_cap( 'delete_property_status');
		}

		// Allow agents to manage property neighborhoods
	    $agent_add_neighborhood = $this->global_settings['ps_members_add_neighborhood'];
	    if($agent_add_neighborhood == 'true') {
	    	$role->add_cap( 'manage_property_neighborhood');
	    	$role->add_cap( 'edit_property_neighborhood');
	    	$role->add_cap( 'delete_property_neighborhood');
	    }

	    // Allow agents to manage property cities
	    $agent_add_city = $this->global_settings['ps_members_add_city'];
	    if($agent_add_city == 'true') {
	    	$role->add_cap( 'manage_property_city');
	    	$role->add_cap( 'edit_property_city');
	    	$role->add_cap( 'delete_property_city');
	    }

	    // Allow agents to manage property states
	    $agent_add_state = $this->global_settings['ps_members_add_state'];
	    if($agent_add_state == 'true') {
	    	$role->add_cap( 'manage_property_state');
	    	$role->add_cap( 'edit_property_state');
	    	$role->add_cap( 'delete_property_state');
	    }

	    // Allow agents to manage property amenities
	    $agent_add_amenities = $this->global_settings['ps_members_add_amenities'];
	    if($agent_add_amenities == 'true') {
	    	$role->add_cap( 'manage_property_amenities');
	    	$role->add_cap( 'edit_property_amenities');
	    	$role->add_cap( 'delete_property_amenities');
	    }

	    // Allow agents to publish properties
	    $agent_property_approval = $this->global_settings['ps_members_submit_property_approval'];
	    if($agent_property_approval != 'true') {
	    	$role->add_cap( 'publish_ps-propertys');
	    }
	    
	}

	/************************************************************************/
	// Load agent settings
	/************************************************************************/

	/**
	 *	Load agent settings
	 *
	 * @param int $user_id
	 */
	public function load_agent_settings($user_id) {

		$agent_settings = array();
		$user_data = get_userdata($user_id);
		        
		$agent_settings['avatar'] = array('title' => 'Avatar ID', 'value' => get_user_meta($user_id, 'avatar', true)); 
		if(!empty($agent_settings['avatar']['value'])) { 
			$agent_listing_crop = $this->global_settings['ps_agent_listing_crop'];
			if($agent_listing_crop == 'true') { $avatar_size = 'agent-thumbnail'; } else { $avatar_size = 'full';  }
			$agent_settings['avatar_url'] = array('title' => 'Avatar URL', 'value' => wp_get_attachment_image_url($agent_settings['avatar']['value'], $avatar_size)); 
			$agent_settings['avatar_url_thumb'] = array('title' => 'Avatar Thumbnail URL', 'value' => wp_get_attachment_image_url($agent_settings['avatar']['value'], 'thumbnail'));
		}
		        
		$agent_settings['username'] = array('title' => 'Username', 'value' => $user_data->user_login);
		$agent_settings['display_name'] = array('title' => 'Display Name', 'value' => $user_data->display_name);
		$agent_settings['edit_profile_url'] = array('title' => 'Edit Profile URL', 'value' => get_edit_user_link($user_id));
		$agent_settings['email'] = array('title' => 'Email', 'value' => $user_data->user_email);
		$agent_settings['first_name'] = array('title' => 'First Name', 'value' => $user_data->first_name);
		$agent_settings['last_name'] = array('title' => 'Last Name', 'value' => $user_data->last_name);
		$agent_settings['website'] = array('title' => 'Website', 'value' => $user_data->user_url);
		$agent_settings['show_in_listings'] = array('title' => 'Show In Listings', 'value' => get_user_meta($user_id, 'ps_agent_show_in_listings', true));
		$agent_settings['job_title'] = array('title' => 'Job Title', 'value' => get_user_meta($user_id, 'ps_agent_job_title', true));
		$agent_settings['mobile_phone'] = array('title' => 'Mobile Phone', 'value' => get_user_meta($user_id, 'ps_agent_mobile_phone', true));
		$agent_settings['office_phone'] = array('title' => 'Office Phone', 'value' => get_user_meta($user_id, 'ps_agent_office_phone', true));
		$agent_settings['description'] = array('title' => 'Description', 'value' => $user_data->description);
		$agent_settings['facebook'] = array('title' => 'Facebook', 'value' => get_user_meta($user_id, 'ps_agent_facebook', true));
		$agent_settings['twitter'] = array('title' => 'Twitter', 'value' => get_user_meta($user_id, 'ps_agent_twitter', true));
		$agent_settings['google'] = array('title' => 'Google Plus', 'value' => get_user_meta($user_id, 'ps_agent_google', true));
		$agent_settings['linkedin'] = array('title' => 'Linkedin', 'value' => get_user_meta($user_id, 'ps_agent_linkedin', true));
		$agent_settings['youtube'] = array('title' => 'Youtube', 'value' => get_user_meta($user_id, 'ps_agent_youtube', true));
		$agent_settings['instagram'] = array('title' => 'Instagram', 'value' => get_user_meta($user_id, 'ps_agent_instagram', true));
		$agent_settings['contact_form_source'] = array('title' => 'Contact Form Source', 'value' => get_user_meta($user_id, 'ps_agent_contact', true));
		$agent_settings['contact_form_7_id'] = array('title' => 'Contact Form 7 ID', 'value' => get_user_meta($user_id, 'ps_agent_contact_form_7', true));

		$agent_settings = apply_filters( 'propertyshift_agent_settings_filter', $agent_settings, $user_id);

		return $agent_settings;
	}

	/************************************************************************/
	// Agent User Fields
	/************************************************************************/

	/**
     *  Create Agent User Fields
     */
    public function create_agent_user_fields($user) { 
    	if($this->is_agent($user->ID)) { ?>
    	<div class="form-section">
	        <h3><?php _e("Agent Information", "propertyshift"); ?></h3>

	        <?php
	    	if(current_user_can('administrator')) { ?>
	    		<table class="form-table">
		        <tr>
		            <th><label><?php esc_html_e('Agent Actions', 'propertyshift'); ?></label></th>
		            <td>
		            	<a href="<?php echo admin_url().'edit.php?post_type=ps-property&author='.$user->ID; ?>" class="button">
		            		<?php esc_html_e('Manage Properties', 'propertyshift'); ?>
							<?php 
							$agent_properties = $this->get_agent_properties($user->ID, null, false, array('publish', 'pending'));
							echo '('.$agent_properties['count'].')';
							?>	
		            	</a>
		            	<a target="_blank" href="<?php echo get_author_posts_url($user->ID); ?>" class="button"><?php esc_html_e('View Profile', 'propertyshift'); ?>	</a>
		            </td>
		        </tr>
		        </table>
	    	
		        <table class="form-table">
		        <tr>
		            <th><label><?php esc_html_e('Show in Agent Listings', 'propertyshift'); ?></label></th>
		            <td>
		            	<input type="radio" name="ps_agent_show_in_listings" checked <?php if (get_the_author_meta( 'ps_agent_show_in_listings', $user->ID) == 'true' ) { ?>checked="checked"<?php }?> value="true" />Yes<br/>
		            	<input type="radio" name="ps_agent_show_in_listings" <?php if (get_the_author_meta( 'ps_agent_show_in_listings', $user->ID) == 'false' ) { ?>checked="checked"<?php }?> value="false" />No<br/>
		            </td>
		        </tr>
		        </table>

	        <?php } ?>

	        <table class="form-table">
	        <tr>
	            <th><label><?php esc_html_e('Job Title', 'propertyshift'); ?></label></th>
	            <td>
	                <input type="text" name="ps_agent_job_title" value="<?php echo esc_attr( get_the_author_meta( 'ps_agent_job_title', $user->ID ) ); ?>" class="regular-text" /><br/>
	                <span class="description"><?php esc_html_e("Provide the agents job title. For example: Broker", 'propertyshift'); ?></span>
	            </td>
	        </tr>
	        </table>

	        <table class="form-table">
	        <tr>
	            <th><label><?php esc_html_e('Mobile Phone', 'propertyshift'); ?></label></th>
	            <td>
	                <input type="text" name="ps_agent_mobile_phone" value="<?php echo esc_attr( get_the_author_meta( 'ps_agent_mobile_phone', $user->ID ) ); ?>" class="regular-text" /><br/>
	                <span class="description"><?php esc_html_e("Provide the agents mobile phone number.", 'propertyshift'); ?></span>
	            </td>
	        </tr>
	        </table>

	        <table class="form-table">
	        <tr>
	            <th><label><?php esc_html_e('Office Phone', 'propertyshift'); ?></label></th>
	            <td>
	                <input type="text" name="ps_agent_office_phone" value="<?php echo esc_attr( get_the_author_meta( 'ps_agent_office_phone', $user->ID ) ); ?>" class="regular-text" /><br/>
	                <span class="description"><?php esc_html_e("Provide the agents office phone number.", 'propertyshift'); ?></span>
	            </td>
	        </tr>
	        </table>

	        <table class="form-table">
	        <tr>
	            <th><label><?php esc_html_e('Facebook', 'propertyshift'); ?></label></th>
	            <td>
	                <input type="text" name="ps_agent_facebook" value="<?php echo esc_attr( get_the_author_meta( 'ps_agent_facebook', $user->ID ) ); ?>" class="regular-text" /><br/>
	                <span class="description"><?php esc_html_e("Provide the agents Facebook profile URL.", 'propertyshift'); ?></span>
	            </td>
	        </tr>
	        </table>

	        <table class="form-table">
	        <tr>
	            <th><label><?php esc_html_e('Twitter', 'propertyshift'); ?></label></th>
	            <td>
	                <input type="text" name="ps_agent_twitter" value="<?php echo esc_attr( get_the_author_meta( 'ps_agent_twitter', $user->ID ) ); ?>" class="regular-text" /><br/>
	                <span class="description"><?php esc_html_e("Provide the agents Twitter profile URL.", 'propertyshift'); ?></span>
	            </td>
	        </tr>
	        </table>

	        <table class="form-table">
	        <tr>
	            <th><label><?php esc_html_e('Linkedin', 'propertyshift'); ?></label></th>
	            <td>
	                <input type="text" name="ps_agent_linkedin" value="<?php echo esc_attr( get_the_author_meta( 'ps_agent_linkedin', $user->ID ) ); ?>" class="regular-text" /><br/>
	                <span class="description"><?php esc_html_e("Provide the agents Linkedin profile URL.", 'propertyshift'); ?></span>
	            </td>
	        </tr>
	        </table>

	        <table class="form-table">
	        <tr>
	            <th><label><?php esc_html_e('Google Plus', 'propertyshift'); ?></label></th>
	            <td>
	                <input type="text" name="ps_agent_google" value="<?php echo esc_attr( get_the_author_meta( 'ps_agent_google', $user->ID ) ); ?>" class="regular-text" /><br/>
	                <span class="description"><?php esc_html_e("Provide the agents Google Plus profile URL.", 'propertyshift'); ?></span>
	            </td>
	        </tr>
	        </table>

	        <table class="form-table">
	        <tr>
	            <th><label><?php esc_html_e('Youtube', 'propertyshift'); ?></label></th>
	            <td>
	                <input type="text" name="ps_agent_youtube" value="<?php echo esc_attr( get_the_author_meta( 'ps_agent_youtube', $user->ID ) ); ?>" class="regular-text" /><br/>
	                <span class="description"><?php esc_html_e("Provide the agents Youtube profile URL.", 'propertyshift'); ?></span>
	            </td>
	        </tr>
	        </table>

	        <table class="form-table">
	        <tr>
	            <th><label><?php esc_html_e('Instagram', 'propertyshift'); ?></label></th>
	            <td>
	                <input type="text" name="ps_agent_instagram" value="<?php echo esc_attr( get_the_author_meta( 'ps_agent_instagram', $user->ID ) ); ?>" class="regular-text" /><br/>
	                <span class="description"><?php esc_html_e("Provide the agents Instagram profile URL.", 'propertyshift'); ?></span>
	            </td>
	        </tr>
	        </table>

	        <?php if(is_admin()) { ?>
	        <table class="form-table">
	        <tr>
	            <th><label><?php esc_html_e('Agent Contact Form', 'propertyshift'); ?></label></th>
	            <td>
	            	<input type="radio" name="ps_agent_contact" checked <?php if (get_the_author_meta( 'ps_agent_contact', $user->ID) == 'default' ) { ?>checked="checked"<?php }?> value="default" />Default Contact Form<br/>
	            	<input type="radio" name="ps_agent_contact" <?php if (get_the_author_meta( 'ps_agent_contact', $user->ID) == 'contact-form-7' ) { ?>checked="checked"<?php }?> value="contact-form-7" />Contact Form 7<br/>
	            	<input type="radio" name="ps_agent_contact" <?php if (get_the_author_meta( 'ps_agent_contact', $user->ID) == 'none' ) { ?>checked="checked"<?php }?> value="none" />None
	            </td>
	        </tr>
	        </table>
	        <table class="form-table">
	        <tr>
	            <th><label><?php esc_html_e('Contact Form 7 ID', 'propertyshift'); ?></label></th>
	            <td>
	                <input type="text" name="ps_agent_contact_form_7" value="<?php echo esc_attr( get_the_author_meta( 'ps_agent_contact_form_7', $user->ID ) ); ?>" class="regular-text" /><br/>
	                <span class="description"><?php esc_html_e("Provide the Contact Form 7 ID.", 'propertyshift'); ?></span>
	            </td>
	        </tr>
	        </table>
    		<?php }

    		do_action('propertyshift_after_agent_fields', $user); ?>

    	</div>
    	<?php }
    }

    /**
     *  Save Agent User Fields
     */
    public function save_agent_user_fields($user_id) {
        if(!current_user_can( 'edit_user', $user_id )) { return false; }
        if(isset($_POST['ps_agent_show_in_listings'])) {update_user_meta( $user_id, 'ps_agent_show_in_listings', sanitize_text_field($_POST['ps_agent_show_in_listings']) ); }
        if(isset($_POST['ps_agent_job_title'])) {update_user_meta( $user_id, 'ps_agent_job_title', sanitize_text_field($_POST['ps_agent_job_title']) ); }
        if(isset($_POST['ps_agent_mobile_phone'])) {update_user_meta( $user_id, 'ps_agent_mobile_phone', sanitize_text_field($_POST['ps_agent_mobile_phone']) ); }
        if(isset($_POST['ps_agent_office_phone'])) {update_user_meta( $user_id, 'ps_agent_office_phone', sanitize_text_field($_POST['ps_agent_office_phone']) ); }
        if(isset($_POST['ps_agent_facebook'])) {update_user_meta( $user_id, 'ps_agent_facebook', esc_url_raw($_POST['ps_agent_facebook']) ); }
        if(isset($_POST['ps_agent_twitter'])) {update_user_meta( $user_id, 'ps_agent_twitter', esc_url_raw($_POST['ps_agent_twitter']) ); }
        if(isset($_POST['ps_agent_linkedin'])) {update_user_meta( $user_id, 'ps_agent_linkedin', esc_url_raw($_POST['ps_agent_linkedin']) ); }
        if(isset($_POST['ps_agent_google'])) {update_user_meta( $user_id, 'ps_agent_google', esc_url_raw($_POST['ps_agent_google']) ); }
        if(isset($_POST['ps_agent_youtube'])) {update_user_meta( $user_id, 'ps_agent_youtube', esc_url_raw($_POST['ps_agent_youtube']) ); }
        if(isset($_POST['ps_agent_instagram'])) {update_user_meta( $user_id, 'ps_agent_instagram', esc_url_raw($_POST['ps_agent_instagram']) ); }
        if(isset($_POST['ps_agent_contact'])) {update_user_meta( $user_id, 'ps_agent_contact', sanitize_text_field($_POST['ps_agent_contact']) ); }
    	if(isset($_POST['ps_agent_contact_form_7'])) {update_user_meta( $user_id, 'ps_agent_contact_form_7', sanitize_text_field($_POST['ps_agent_contact_form_7']) ); }
    	do_action('propertyshift_save_agent_fields', $user_id); 
    }

    /**
     *  On agent register
     */
    public function on_agent_register($user_id) {
    	if($this->global_settings['ps_members_auto_agent_profile'] == 'true') {
    		update_user_meta( $user_id, 'ps_agent_show_in_listings', 'true');
    	} else {
    		update_user_meta( $user_id, 'ps_agent_show_in_listings', 'false');
    	}
    }

    /************************************************************************/
	// Manage User Columns
	/************************************************************************/
    public function new_modify_user_table( $column ) {
	    $column['properties'] = esc_html__('Properties', 'propertyshift');
	    return $column;
	}
	
	public function new_modify_user_table_row( $val, $column_name, $user_id ) {
	    switch ($column_name) {
	        case 'properties' :
	        	$agent_properties = $this->get_agent_properties($user_id, null, false, array('publish', 'pending'));
	        	if($agent_properties['count'] > 0) {
	        		$return_link = '<a href="'.admin_url().'edit.php?post_type=ps-property&author='.$user_id.'">'.$agent_properties['count'].'</a>';
	        	} else {
	        		$return_link = '0';
	        	}
	        	
	            return $return_link;
	        default:
	    }
	    return $val;
	}

	/************************************************************************/
	// Agent Utilities
	/************************************************************************/

    /**
     *  Get agents
     *
     */
    public function get_agents($empty_default = false) {
    	$agents = array();
    	if($empty_default == true) { $agents['Select an agent...'] = ''; }
    	$user_agents = get_users(array('role__in' => array('ps_agent', 'administrator')));
    	foreach($user_agents as $user) {
			$agents[$user->display_name.' ('.$user->user_login.')'] = $user->ID;
		}
		return $agents;
    }

    /**
     *  Check if user is an agent
     *
     */
    public function is_agent($user_id) {
    	$user_meta = get_userdata($user_id);
    	$user_roles = $user_meta->roles; 
    	if(!empty($user_roles) && (in_array("ps_agent", $user_roles) || in_array("administrator", $user_roles))) {
    		return true;
    	} else {
    		return false;
    	}
    }

	/**
	 *	Get agent properties
	 *
	 * @param int $user_id
	 * @param int $posts_per_page
	 * @param boolean $pagination
	 */
	public function get_agent_properties($user_id, $posts_per_page = null, $pagination = false, $post_status = array('publish')) {
		$agent_properties = array(); 

	    $args = array('post_type' => 'ps-property');
	    if(is_array($user_id)) { $args['author__in'] = $user_id; } else { $args['author'] = $user_id; }

	    if(!empty($posts_per_page)) { $args['posts_per_page'] = $posts_per_page; }
	    if($pagination == true) { 
	        $paged = isset($_GET['paged']) ? $_GET['paged'] : 1;
	        $args['paged'] = $paged; 
	    }

	    $args['post_status'] = $post_status;

	    $agent_properties['args'] = $args;
	    $agent_properties['properties'] = new WP_Query($args);
	    $agent_properties['count'] =  $agent_properties['properties']->found_posts;
	    return $agent_properties;
	}

	/**
	 *	Load agent contact form
	 */
	public function get_contact_form($agent_id) {
		$agent_settings = $this->load_agent_settings($agent_id);
		if($agent_settings['contact_form_source']['value'] == 'contact-form-7') {
            $agent_form_id = $agent_settings['contact_form_7_id']['value'];
            $agent_form_title = get_the_title($agent_form_id);
            echo do_shortcode('[contact-form-7 id="'.esc_attr($agent_form_id).'" title="'.$agent_form_title.'"]');
        } else if($agent_settings['contact_form_source']['value'] != 'none') {
        	$template_args = array('id' => $agent_id);
            propertyshift_template_loader('agent_contact_form.php', $template_args);
        } 
	}

	/**
	 *	Load agent detail items
	 */
	public static function load_agent_detail_items() {
		$agent_detail_items_init = array(
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
	            'name' => esc_html__('Contact', 'propertyshift'),
	            'label' => esc_html__('Contact', 'propertyshift'),
	            'slug' => 'contact',
	            'active' => 'true',
	            'sidebar' => 'false',
	        ),
	        3 => array(
	            'name' => esc_html__('Properties', 'propertyshift'),
	            'label' => esc_html__('Properties', 'propertyshift'),
	            'slug' => 'properties',
	            'active' => 'true',
	            'sidebar' => 'false',
	        ),
	    );

		$agent_detail_items_init = apply_filters( 'propertyshift_agent_detail_items_init_filter', $agent_detail_items_init);
	    return $agent_detail_items_init;
	}

	/************************************************************************/
	// Front-End Agent Profiles
	/************************************************************************/

	/**
	 *	Add query var
	 */
	public function agent_query_vars( $vars ) {
	    $vars[] = $this->global_settings['ps_agent_detail_slug'];
	    return $vars;
	}
	
	/**
	 *	Add rewrite rule
	 */
	public function agent_rewrite_rule() {
		$agent_slug = $this->global_settings['ps_agent_detail_slug'];
	    add_rewrite_tag( '%'.$agent_slug.'%', '([^&]+)' );
	    add_rewrite_rule(
	        '^'.$agent_slug.'/([^/]*)/?',
	        'index.php?'.$agent_slug.'=$matches[1]',
	        'top'
	    );
	}
	
	/**
	 *	Redirect to agent profile template
	 */
	public function agent_profile_template_redirect($query_vars) {
		$agent_slug = $this->global_settings['ps_agent_detail_slug'];
		$agent_profile_page = $this->global_settings['ps_members_profile_page'];

		//Redirect to agent page (fallback to author archive if agent page isn't set)
		if(isset($query_vars[$agent_slug])) {

			$agent = get_user_by('slug', $query_vars[$agent_slug]);

			if(!empty($agent_profile_page) && $this->is_agent($agent->ID)) {
				$query_vars['page_id'] = $agent_profile_page;
			} else {
				$query_vars['author'] = $agent->ID; 
			}
		}
	    
    	return $query_vars;
	}

	/**
	 *	Modify author link
	 */
	function change_author_link($link, $author_id) {
		$agent_slug = $this->global_settings['ps_agent_detail_slug'];
		if($this->is_agent($author_id)) {
			$link = str_replace( 'author', $agent_slug, $link );
		}
	    return $link;
	}

	/**
	 *	Modify agent page doc title
	 */
	public function change_agent_doc_title($title_parts_array) {
		$agent_slug = $this->global_settings['ps_agent_detail_slug'];
		if(get_query_var($agent_slug)) {
			$user = get_user_by('slug', get_query_var($agent_slug));
			$title_parts_array['title'] = $user->display_name;
		}
	    
	    return $title_parts_array;
	}

	/**
	 *	Modify agent title
	 */
	public function change_agent_title($title, $id) {
		$agent_slug = $this->global_settings['ps_agent_detail_slug'];
		$post = get_post( $id );
		if ($post instanceof WP_Post && $post->post_type == 'page') {
			if(get_query_var($agent_slug) && in_the_loop()) { 
				$user = get_user_by('slug', get_query_var($agent_slug));
				$title = $user->display_name; 
			}
		}
	    return $title;
	}

	/************************************************************************/
	// Front-end Template Hooks
	/************************************************************************/

	/**
	 *	Add dashboard stats
	 */
	public function add_dashboard_stats() { 
		
		$current_user = wp_get_current_user();

		//Get post likes
		$post_likes_obj = new NS_Basics_Post_Likes();
		$saved_posts = $post_likes_obj->show_user_likes_count($current_user); 

		//Get properties
		$pending_properties = $this->get_agent_properties($current_user->ID, null, false, array('pending'));
		$published_properties = $this->get_agent_properties($current_user->ID, null, false, array('publish')); ?>
		
		<div class="user-dashboard-widget stat">
			<span><?php echo $pending_properties['count']; ?></span>
			<p><?php esc_html_e( 'Pending Properties', 'propertyshift' ) ?></p>
		</div>
		<div class="user-dashboard-widget stat">
			<span><?php echo $published_properties['count']; ?></span>
			<p><?php esc_html_e( 'Published Properties', 'propertyshift' ) ?></p>
		</div>
		<div class="user-dashboard-widget stat">
			<span><?php echo $saved_posts; ?></span>
			<p><?php esc_html_e( 'Saved Posts', 'propertyshift' ) ?></p>
		</div>
	<?php }

	/**
	 *	Add dashboard widgets
	 */
	public function add_dashboard_widgets() { 
		$members_my_properties_page = $this->global_settings['ps_members_my_properties_page']; ?>
		<div class="user-dashboard-widget">
			<h4><?php esc_html_e( 'Your Recent Properties', 'propertyshift' ) ?></h4>
			<?php echo do_shortcode('[ps_my_properties show_posts=3 show_pagination="false"]'); ?>
			<?php if(!empty($members_my_properties_page)) { ?><a href="<?php echo $members_my_properties_page; ?>" class="button small">View All Properties</a><?php } ?>
		</div>
	<?php }

	/**
	 *	Add agent detail sidebar template
	 */
	public function add_agent_detail_sidebar_template() {
		$agent_slug = $this->global_settings['ps_agent_detail_slug'];
		if(get_query_var($agent_slug) && function_exists('propertyshift_template_loader')) {
			propertyshift_template_loader('loop_agent_single.php', ['location' => 'sidebar']); 
		}
	}

} ?>