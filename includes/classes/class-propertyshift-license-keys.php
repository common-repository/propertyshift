<?php
// Exit if accessed directly
if (!defined( 'ABSPATH')) { exit; }

/**
 *	PropertyShift_License_Keys class
 *
 */
class PropertyShift_License_Keys {

	/**
	 *	Init
	 */
	public function init() {
		add_action('admin_init', array($this, 'activate_license'));
		add_action('admin_init', array($this, 'deactivate_license'));
		add_action('admin_notices', array($this, 'admin_notices'));
	}

	/**
	 * Retrieve License Key
	 */
	public function get_license($item_id) {
		$license = array();
	    $license['key_name'] = 'ps_'.$item_id.'_license_key';
	    $license['key'] = trim(get_option('ps_'.$item_id.'_license_key'));
	    $license['status_name'] = 'ps_'.$item_id.'_license_status';
	    $license['status'] = get_option('ps_'.$item_id.'_license_status');
	    return $license;
	}

	/**
	 * Activate License Key
	 */
	public function activate_license() {
		if(isset($_POST['propertyshift_activate_license']) && !empty($_POST['propertyshift_activate_license'])) {

	        $item_id = sanitize_text_field($_POST['propertyshift_activate_license']);
	        $license = $this->get_license($item_id);

	        $api_params = array(
	            'edd_action' => 'activate_license',
	            'license'    => $license['key'],
	            'item_id'  => $item_id, // the name of our product in EDD
	            'url'      => home_url()
	        );

	        $response = wp_remote_post( NS_BASICS_SHOP_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

	        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
	            if ( is_wp_error( $response ) ) {
	                $message = $response->get_error_message();
	            } else {
	                $message = __( 'An error occurred, please try again.', 'propertyshift' );
	            }
	        } else {

	            $license_data = json_decode( wp_remote_retrieve_body( $response ) );

	            if(false === $license_data->success ) {
	                switch( $license_data->error ) {
	                    case 'expired' :
	                        $message = sprintf(
	                             __( 'Your license key expired on %s.' ),
	                            date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
	                        );
	                        break;
	                    case 'disabled' :
	                    case 'revoked' :
	                        $message = __( 'Your license key has been disabled.', 'propertyshift' );
	                        break;
	                    case 'missing' :
	                        $message = __( 'Invalid license.', 'propertyshift' );
	                        break;
	                    case 'invalid' :
	                    case 'site_inactive' :
	                        $message = __( 'Your license is not active for this URL.', 'propertyshift' );
	                        break;
	                    case 'item_name_mismatch' :
	                        $message = sprintf( __( 'This appears to be an invalid license key for %s.' ), 'NS Open Houses' );
	                        break;
	                    case 'no_activations_left':
	                        $message = __( 'Your license key has reached its activation limit.', 'propertyshift' );
	                        break;
	                    default :
	                        $message = __( 'An error occurred, please try again.', 'propertyshift' );
	                        break;
	                }
	            }
	        }

	        if(!empty($message)) {
	            $base_url = admin_url( 'admin.php?page=' . PROPERTYSHIFT_LICENSE_PAGE );
	            $redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );
	            wp_redirect( $redirect );
	            exit();
	        }

	        update_option($license['status_name'], $license_data->license);
	        wp_redirect( admin_url( 'admin.php?page=' . PROPERTYSHIFT_LICENSE_PAGE ) );
	        exit();
	    }
	}

	/**
	 * Deactivate License Key
	 */
	public function deactivate_license() {
		if(isset($_POST['propertyshift_deactivate_license']) && !empty($_POST['propertyshift_deactivate_license'])) {

	        $item_id = sanitize_text_field($_POST['propertyshift_deactivate_license']);
	        $license = $this->get_license($item_id);

	        $api_params = array(
	            'edd_action' => 'deactivate_license',
	            'license'    => $license['key'],
	            'item_id'  => $item_id, // the name of our product in EDD
	            'url'      => home_url()
	        );

	        $response = wp_remote_post( NS_BASICS_SHOP_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

	        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

	            if ( is_wp_error( $response ) ) {
	                $message = $response->get_error_message();
	            } else {
	                $message = __( 'An error occurred, please try again.' );
	            }

	            $base_url = admin_url( 'admin.php?page=' . PROPERTYSHIFT_LICENSE_PAGE );
	            $redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );
	            wp_redirect( $redirect );
	            exit();
	        }

	        delete_option($license['status_name']);
	        wp_redirect( admin_url( 'admin.php?page=' . PROPERTYSHIFT_LICENSE_PAGE) );
	        exit();
	    }
	}

	/**
	 * Catch activation errors and display
	 */
	public function admin_notices() {
		if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

	        switch( $_GET['sl_activation'] ) {
	            case 'false':
	                $message = urldecode( $_GET['message'] ); ?>
	                <div class="error"><p><?php echo $message; ?></p></div>
	                <?php
	                break;
	            case 'true':
	            default:
	                // Custom message here on successful activation
	                break;
	        }
	    }
	}

	/**
	 * Build License Key Form
	 */
	public function build_license_key_form($item_name, $item_id) {

		$license = $this->get_license($item_id);
	    $license_key = $license['key'];
	    $license_status = $license['status'];
	    settings_fields('propertyshift-licenses-group'); 
	    ?>

	    <div class="ns-accordion ns-license-key">
	        <div class="ns-accordion-header">
	            <i class="fa fa-chevron-right"></i> 
	            <?php echo $item_name; ?> <?php esc_html_e('License', 'propertyshift'); ?>
	            <?php if($license_status !== false && $license_status == 'valid' ) { echo '<div class="button admin-button green">'.esc_html__('Active', 'propertyshift').'</div>';  }?>
	        </div>
	        <div class="ns-accordion-content">

	            <table class="admin-module">
	                <tr>
	                    <td class="admin-module-label">
	                        <label><?php echo esc_html_e('License Key', 'propertyshift'); ?></label>
	                        <span class="admin-module-note"><?php esc_html_e('Enter your license key here.', 'propertyshift'); ?></span>
	                    </td>
	                    <td class="admin-module-field">
	                        <?php if($license_status == false) { ?>
	                            <input class="license-key-input" name="<?php echo $license['key_name'] ?>" type="text" value="<?php esc_attr_e( $license_key ); ?>" />
	                        <?php } else { ?>
	                            <input value="<?php esc_attr_e( $license_key ); ?>" disabled />
	                            <input type="hidden" name="<?php echo $license['key_name'] ?>" value="<?php esc_attr_e( $license_key ); ?>" /> 
	                            <span class="admin-module-note"><?php echo esc_html_e('Deactivate to modify license key', 'propertyshift'); ?></span>
	                        <?php } ?>
	                    </td>
	                </tr>
	            </table>

	            <?php if( false !== $license_key && !empty($license_key) ) { ?>
	            <table class="admin-module">
	                <tr>
	                    <td class="admin-module-label"><label><?php echo esc_html_e('License Actions', 'propertyshift'); ?></label></td>
	                    <td class="admin-module-field">
	                        <?php if( $license_status !== false && $license_status == 'valid' ) { ?>
	                            <?php wp_nonce_field( 'ns_nonce', 'ns_nonce' ); ?>
	                            <button style="width:150px;" type="submit" class="button-secondary activate-license-button" name="propertyshift_deactivate_license" value="<?php echo $item_id; ?>"><?php echo esc_html_e('Deactivate License', 'propertyshift'); ?></button>
	                        <?php } else {
	                            wp_nonce_field( 'ns_nonce', 'ns_nonce' ); ?>
	                            <button style="width:150px;" type="submit" class="button-secondary activate-license-button" name="propertyshift_activate_license" value="<?php echo $item_id; ?>"><?php echo esc_html_e('Activate License', 'propertyshift'); ?></button>
	                        <?php } ?>
	                    </td>
	                </tr>
	            </table>
	            <?php } ?>

	        </div>
	    </div>

	<?php }

}
?>