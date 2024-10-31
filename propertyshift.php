<?php
/**
* Plugin Name: PropertyShift
* Plugin URI: https://products.nightshiftcreative.co/plugins/propertyshift/
* Description: Robust real estate listing system for agents and agencies of any size. 
* Version: 1.0.0
* Author: Nightshift Creative
* Author URI: https://products.nightshiftcreative.co/
* Text Domain: propertyshift
**/

// Exit if accessed directly
if (!defined( 'ABSPATH')) { exit; }

class PropertyShift {

	/**
	 * Constructor - intialize the plugin
	 */
	public function __construct() {
		
		//Init
		$this->load_plugin_textdomain();
		$this->define_constants();

		// Require NS Basics
		require_once( plugin_dir_path( __FILE__ ) . '/includes/classes/class-tgm-plugin-activation.php');
		add_action( 'tgmpa_register', array( $this, 'require_plugins' ) );
		
		// Load Assets and Includes
		if($this->is_plugin_active('ns-basics/ns-basics.php')) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
			$this->includes();
		}
	}

	/**
	 * Load the textdomain for translation
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'propertyshift', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Get latest github release
	 */
	public function get_latest_github_release($repo_name) {
		$release_tag = '1.0.0';
		$request = wp_remote_get('https://api.github.com/repos/NightshiftCreative/'.$repo_name.'/releases/latest');
		if(is_wp_error($request)) { return false; }
		$body = wp_remote_retrieve_body($request);
		$data = json_decode($body);
		if(!empty($data) && !empty($data->tag_name)) { $release_tag = $data->tag_name; }
		return $release_tag;
	}

	/**
	 * Define constants
	 */
	public function define_constants() {
		$ns_basics_latest_release = $this->get_latest_github_release('NS-Basics');
		if(!defined('NS_BASICS_URL')) { define('NS_BASICS_URL', 'https://nightshiftcreative.co/'); }
		if(!defined('NS_BASICS_SHOP_URL')) { define('NS_BASICS_SHOP_URL', 'https://products.nightshiftcreative.co/'); }
		if(!defined('NS_BASICS_GITHUB')) { define('NS_BASICS_GITHUB', '/NightShiftCreative/NS-Basics/archive/'.$ns_basics_latest_release.'.zip'); }
		if(!defined('PROPERTYSHIFT_GITHUB')) { define('PROPERTYSHIFT_GITHUB', '/NightShiftCreative/PropertyShift/'); } 
		if(!defined('PROPERTYSHIFT_LICENSE_PAGE')) { define('PROPERTYSHIFT_LICENSE_PAGE', 'propertyshift-license-keys' ); }
		if(!defined('PROPERTYSHIFT_DIR')) { define('PROPERTYSHIFT_DIR', plugins_url('', __FILE__)); }
	}

	/**
	 * Require Plugins
	 */
	public function require_plugins() {
		$plugins = array(
	        array(
				'name'         => 'Nightshift Basics', // The plugin name.
				'slug'         => 'ns-basics', // The plugin slug (typically the folder name).
				'source'       => 'https://github.com'.constant('NS_BASICS_GITHUB'), // The plugin source.
				'required'     => true, // If false, the plugin is only 'recommended' instead of required.
				'version'	   => '1.0.0',
				'force_activation'   => false,
				'force_deactivation' => false,
				'external_url' => constant('NS_BASICS_SHOP_URL'),
			),
	    );

	    $config = array(
	        'id'           => 'propertyshift',       // Unique ID for hashing notices for multiple instances of TGMPA.
	        'default_path' => '',                      // Default absolute path to bundled plugins.
	        'menu'         => 'tgmpa-install-plugins', // Menu slug.
	        'has_notices'  => true,                    // Show admin notices or not.
	        'dismissable'  => false,                    // If false, a user cannot dismiss the nag message.
	        'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
	        'is_automatic' => true,                   // Automatically activate plugins after installation or not.
	        'message'      => '',                      // Message to output right before the plugins table.
	    );

	    tgmpa( $plugins, $config );
	}

	/**
	 * Check if plugin is activated
	 *
	 * @param string $plugin
	 */
	public function is_plugin_active($plugin) {
		return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
	}

	/**
	 * Load admin scripts and styles
	 */
	public function admin_scripts() {
		if (is_admin()) {

			wp_enqueue_script('propertyshift-admin-js', plugins_url('/js/propertyshift-admin.js', __FILE__), array('jquery', 'jquery-ui-core', 'jquery-ui-tabs', 'media-upload', 'thickbox'), '', true);
			wp_enqueue_style('propertyshift-admin-css', plugins_url('/css/propertyshift-admin.css',  __FILE__), array(), '1.0', 'all');

			/* localize scripts */
	        $translation_array = array(
	            'admin_url' => esc_url(get_admin_url()),
	            'delete_text' => __( 'Delete', 'propertyshift' ),
	            'remove_text' => __( 'Remove', 'propertyshift' ),
	            'edit_text' => __( 'Edit', 'propertyshift' ),
	            'upload_img' => __( 'Upload Image', 'propertyshift' ),
	            'floor_plan_title' => __( 'Title:', 'propertyshift' ),
	            'floor_plan_size' => __( 'Size:', 'propertyshift' ),
	            'floor_plan_rooms' => __( 'Bedrooms:', 'propertyshift' ),
	            'floor_plan_bathrooms' => __( 'Bathrooms:', 'propertyshift' ),
	            'floor_plan_img' => __( 'Image:', 'propertyshift' ),
	            'floor_plan_description' => __( 'Description:', 'propertyshift' ),
	            'new_floor_plan' => __( 'New Floor Plan', 'propertyshift' ),
	        );
	        wp_localize_script( 'propertyshift-admin-js', 'propertyshift_local_script', $translation_array );
		}
	}

	/**
	 * Load front end scripts and styles
	 */
	public function frontend_scripts() {
		if (!is_admin()) {
	    	
	    	wp_enqueue_script('nouislider', plugins_url('/assets/noUiSlider/nouislider.min.js', __FILE__), array('jquery'), '', true);
	        wp_enqueue_style('nouislider', plugins_url('/assets/noUiSlider/nouislider.min.css',  __FILE__), array(), '1.0', 'all');
	        wp_enqueue_script('wnumb', plugins_url('/assets/noUiSlider/wNumb.js', __FILE__), array('jquery'), '', true);
	        wp_enqueue_style('propertyshift', plugins_url('/css/propertyshift.css',  __FILE__), array(), '1.0', 'all');
	    	wp_enqueue_script('propertyshift', plugins_url('/js/propertyshift.js', __FILE__), array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), '', true);

	    	/* localize scripts */
	        $translation_array = array(
	            'admin_url' => esc_url(get_admin_url()),
	            'delete_text' => __( 'Delete', 'propertyshift' ),
	            'purchase_price' => __( 'Purchase Price', 'propertyshift' ),
	            'down_payment' => __( 'Down Payment', 'propertyshift' ),
	            'percent' => __( 'Percent', 'propertyshift' ),
	            'fixed' => __( 'Fixed', 'propertyshift' ),
	            'rate' => __( 'Rate', 'propertyshift' ),
	            'term' => __( 'Term', 'propertyshift' ),
	            'years' => __( 'Years', 'propertyshift' ),
	            'months' => __( 'Months', 'propertyshift' ),
	            'calculate' => __( 'Calculate', 'propertyshift' ),
	            'monthly_payment' => __( 'Your monthly payment:', 'propertyshift' ),
	            'required_field' => __( 'This field is required', 'propertyshift' ),
	        );
	        wp_localize_script( 'propertyshift', 'propertyshift_local_script', $translation_array );
	    
	        /* dynamic scripts */
        	include( plugin_dir_path( __FILE__ ) . '/js/dynamic_scripts.php');
	    }
	}

	/**
	 * Load Includes
	 */
	public function includes() {

		/************************************************************************/
		// Include functions
		/************************************************************************/
		include( plugin_dir_path( __FILE__ ) . 'includes/global-functions.php');
		include( plugin_dir_path( __FILE__ ) . 'includes/templates/templates.php');
		if($this->is_plugin_active('js_composer/js_composer.php')) { 
			include( plugin_dir_path( __FILE__ ) . 'includes/wp-bakery/wp-bakery.php');
		}

		/************************************************************************/
		// Include classes
		/************************************************************************/

		include( plugin_dir_path( __FILE__ ) . 'includes/classes/class-propertyshift-admin.php');
		include( plugin_dir_path( __FILE__ ) . 'includes/classes/class-propertyshift-properties.php');
		include( plugin_dir_path( __FILE__ ) . 'includes/classes/class-propertyshift-agents.php');
		include( plugin_dir_path( __FILE__ ) . 'includes/classes/class-propertyshift-filters.php');
		include( plugin_dir_path( __FILE__ ) . 'includes/classes/class-propertyshift-license-keys.php');
		include( plugin_dir_path( __FILE__ ) . 'includes/classes/class-propertyshift-shortcodes.php');
		include( plugin_dir_path( __FILE__ ) . 'includes/classes/widgets/list_agents_widget.php');
		include( plugin_dir_path( __FILE__ ) . 'includes/classes/widgets/list_properties_widget.php');
		include( plugin_dir_path( __FILE__ ) . 'includes/classes/widgets/list_property_categories_widget.php');
		include( plugin_dir_path( __FILE__ ) . 'includes/classes/widgets/mortgage_widget.php');
		include( plugin_dir_path( __FILE__ ) . 'includes/classes/widgets/property_filter_widget.php');

		// Setup the admin
		if(is_admin()) { 
			$this->admin = new PropertyShift_Admin(); 
			$this->admin->init();
		}

		// Load properties class
		$this->properties = new PropertyShift_Properties();
		$this->properties->init();

		// Load agents class
		$this->agents = new PropertyShift_Agents();
		$this->agents->init();

		// Load filters class
		$this->filters = new PropertyShift_Filters();
		$this->filters->init();

		// Load license keys class
		$this->license_keys = new PropertyShift_License_Keys();
		$this->license_keys->init();

		// Load shortcodes class
		$this->shortcodes = new PropertyShift_Shortcodes();

		//Action to let add-ons know that core classes are ready
		do_action( 'propertyshift_loaded', plugin_dir_path( __FILE__ ) );

	}

}


/**
 *  Load the main class
 */
function propertyshift() {
	global $propertyshift;
	if(!isset($propertyshift)) { $propertyshift = new PropertyShift(); }
	return $propertyshift;
}
propertyshift();
?>