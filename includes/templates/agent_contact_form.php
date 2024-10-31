<?php
//Get agent settings
$agent_id = $template_args['id'];
$agent_obj = new PropertyShift_Agents();
$agent_settings = $agent_obj->load_agent_settings($agent_id);
$agent_email = $agent_settings['email']['value'];

//Get global settings
$admin_obj = new PropertyShift_Admin();
$site_title = get_bloginfo('name');
$agent_form_submit_text = $admin_obj->load_settings(false, 'ps_agent_form_submit_text');
$agent_form_success = $admin_obj->load_settings(false, 'ps_agent_form_success');

if(is_singular('ps-property')) {
    $agent_form_message_placeholder = $admin_obj->load_settings(false, 'ps_agent_form_message_placeholder');
} else {
    $agent_form_message_placeholder =  esc_html__( 'Message', 'propertyshift' );
}

$emailSent = false;
$nameError = '';
$emailError = '';
$commentError = '';

//If the form is submitted
if(isset($_POST['submitted'])) {
      
    // require a name from user
    if(trim($_POST['agent-contact-name']) === '') {
        $nameError =  esc_html__('Forgot your name!', 'propertyshift'); 
        $hasError = true;
    } else {
        $agent_contact_name = sanitize_text_field($_POST['agent-contact-name']);
    }
      
    // need valid email
    if(trim($_POST['agent-contact-email']) === '')  {
        $emailError = esc_html__('Forgot to enter in your e-mail address.', 'propertyshift');
        $hasError = true;
    } else if(!is_email(trim($_POST['agent-contact-email']))) {
        $emailError = 'You entered an invalid email address.';
        $hasError = true;
    } else {
        $agent_contact_email = sanitize_email($_POST['agent-contact-email']);
    }
        
    // we need at least some content
    if(trim($_POST['agent-contact-message']) === '') {
        $commentError = esc_html__('You forgot to enter a message!', 'propertyshift');
        $hasError = true;
    } else {
        if(function_exists('stripslashes')) {
          $agent_contact_message = stripslashes(sanitize_text_field($_POST['agent-contact-message']));
        } else {
          $agent_contact_message = sanitize_text_field($_POST['agent-contact-message']);
        }
    }
        
    // upon no failure errors let's email now!
    if(!isset($hasError)) {

        /*---------------------------------------------------------*/
        /* SET EMAIL ADDRESS HERE                                  */
        /*---------------------------------------------------------*/
        $emailTo = $agent_email;
        $subject = 'Submitted message from '.$agent_contact_name;
        $formUrl = esc_url_raw($_POST['current_url']);
        $body = "This message was sent from a contact from on: $formUrl \n\n Name: $agent_contact_name \n\nEmail: $agent_contact_email \n\nMessage: $agent_contact_message";
        $headers = 'From: ' .$site_title.' <'.$emailTo.'>' . "\r\n" . 'Reply-To: ' . $agent_contact_email;

        mail($emailTo, $subject, $body, $headers);
            
        // set our boolean completion value to TRUE
        $emailSent = true;
    }
}

?>

<form id="agent-contact-form" class="contact-form agent-contact-form" method="post">

    <div class="alert-box success <?php if($emailSent) { echo 'show'; } else { echo 'hide'; } ?>"><?php echo $agent_form_success; ?></div>

    <div class="contact-form-fields">
        <div>
            <?php if($nameError != '') { ?><div class="alert-box error"><?php echo $nameError;?></div> <?php } ?>
            <input type="text" name="agent-contact-name" placeholder="<?php esc_html_e( 'Name', 'propertyshift' ); ?>*" value="<?php if(isset($agent_contact_name)){ echo $agent_contact_name; } ?>" class="border requiredField" />
        </div>

        <div>
            <?php if($emailError != '') { ?><div class="alert-box error"><?php echo $emailError;?></div> <?php } ?>
            <input type="email" name="agent-contact-email" placeholder="<?php esc_html_e( 'Email', 'propertyshift' ); ?>*" value="<?php if(isset($agent_contact_email)) { echo $agent_contact_email; } ?>" class="border requiredField email" />
        </div>

        <div>
            <?php if($commentError != '') { ?><div class="alert-box error"><?php echo $commentError;?></div> <?php } ?>
            <textarea name="agent-contact-message" class="border"><?php if(isset($agent_contact_message)) { echo $agent_contact_message; } else { echo $agent_form_message_placeholder; } ?></textarea>
        </div>

        <div>
            <?php $current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>
            <input type="hidden" name="current_url" value="<?php echo $current_url; ?>" />
            <input type="hidden" name="submitted" id="submitted" value="true" />
            <input type="submit" name="submit" value="<?php echo $agent_form_submit_text; ?>" />
            <div class="form-loader"><img src="<?php echo esc_url(home_url('/')); ?>wp-admin/images/spinner.gif" alt="" /> <?php esc_html_e( 'Loading...', 'propertyshift' ); ?></div>
        </div>
    </div>
</form>
