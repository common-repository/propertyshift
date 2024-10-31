<?php
//Global settings
$admin_obj = new PropertyShift_Admin();
$agents_obj = new PropertyShift_Agents();
$icon_set = esc_attr(get_option('ns_core_icon_set', 'fa'));
if(function_exists('ns_core_load_theme_options')) { $icon_set = ns_core_load_theme_options('ns_core_icon_set'); }
$num_properties_per_page = $admin_obj->load_settings(false, 'ps_num_properties_per_page');
$agent_detail_items = $admin_obj->load_settings(false, 'ps_agent_detail_items', false);

//Get template location
$template_location = isset($template_args['location']) ? $template_args['location'] : ''; 
if($template_location == 'sidebar') { 
    $template_location_sidebar = 'true'; 
} else { 
    $template_location_sidebar = 'false';
}

//Get agent
$agent = '';
$agent_slug = $admin_obj->load_settings(false, 'ps_agent_detail_slug');
$user_slug = get_query_var($agent_slug);
if(!empty($user_slug)) {
    $agent = get_user_by( 'slug', $user_slug);
} else if(is_user_logged_in()) {
    $agent = wp_get_current_user();
}

if(!empty($agent)) {
    
    //Get agent details
    $agent_settings = $agents_obj->load_agent_settings($agent->ID);
    $agent_display_name = isset($agent_settings['display_name']['value']) ? $agent_settings['display_name']['value'] : '';
    $agent_avatar_url = isset($agent_settings['avatar_url']['value']) ? $agent_settings['avatar_url']['value'] : '';
    $agent_email = isset($agent_settings['email']['value']) ? $agent_settings['email']['value'] : '';
    $agent_title = isset($agent_settings['job_title']['value']) ? $agent_settings['job_title']['value'] : '';
    $agent_mobile_phone = isset($agent_settings['mobile_phone']['value']) ? $agent_settings['mobile_phone']['value'] : '';
    $agent_office_phone = isset($agent_settings['office_phone']['value']) ? $agent_settings['office_phone']['value'] : '';
    $agent_website = isset($agent_settings['website']['value']) ? $agent_settings['website']['value'] : '';
    $agent_description = isset($agent_settings['description']['value']) ? $agent_settings['description']['value'] : '';
    $agent_fb = isset($agent_settings['facebook']['value']) ? $agent_settings['facebook']['value'] : '';
    $agent_twitter = isset($agent_settings['twitter']['value']) ? $agent_settings['twitter']['value'] : '';
    $agent_google = isset($agent_settings['google']['value']) ? $agent_settings['google']['value'] : '';
    $agent_linkedin = isset($agent_settings['linkedin']['value']) ? $agent_settings['linkedin']['value'] : '';
    $agent_youtube = isset($agent_settings['youtube']['value']) ? $agent_settings['youtube']['value'] : '';
    $agent_instagram = isset($agent_settings['instagram']['value']) ? $agent_settings['instagram']['value'] : '';
    $agent_form_source = isset($agent_settings['contact_form_source']['value']) ? $agent_settings['contact_form_source']['value'] : '';
    $agent_form_id = isset($agent_settings['contact_form_7_id']['value']) ? $agent_settings['contact_form_7_id']['value'] : '';

    //Get agent properties
    $agent_properties = $agents_obj->get_agent_properties($agent->ID, $num_properties_per_page);
    $agent_properties_count = $agent_properties['count']; ?>

    <div class="ps-agent ps-agent-single ps-agent-<?php echo $agent->ID; ?>">
	<?php if (!empty($agent_detail_items)) { 
		foreach($agent_detail_items as $value) { ?>

				<?php
                    if(isset($value['name'])) { $name = $value['name']; }
                    if(isset($value['label'])) { $label = $value['label']; }
                    if(isset($value['slug'])) { $slug = $value['slug']; }
                    if(isset($value['active']) && $value['active'] == 'true') { $active = 'true'; } else { $active = 'false'; }
                    if(isset($value['sidebar']) && $value['sidebar'] == 'true') { $sidebar = 'true'; } else { $sidebar = 'false'; }
                ?>

                <?php if($active == 'true' && ($sidebar == $template_location_sidebar)) { ?>
					
                	<?php if($slug == 'overview') { ?>
                    <!--******************************************************-->
                    <!-- OVERVIEW -->
                    <!--******************************************************-->
                	<div class="agent-single-item ps-single-item widget agent-<?php echo esc_attr($slug); ?>">

                        <div class="agent-img">
                            <?php if(!empty($agent_avatar_url)) {  ?>
                                <img src="<?php echo $agent_avatar_url; ?>" alt="<?php echo get_the_title(); ?>" />  
                            <?php } else { ?>
                                <img src="<?php echo PROPERTYSHIFT_DIR.'/images/agent-img-default.gif'; ?>" alt="" />
                            <?php } ?>
                        </div>

                        <?php if(isset($agent_properties_count) && $agent_properties_count > 0) { ?>
                            <div class="button alt agent-tag agent-assigned"><?php echo esc_attr($agent_properties_count); ?> <?php if($agent_properties_count <= 1) { esc_html_e('Assigned Property', 'propertyshift'); } else { esc_html_e('Assigned Properties', 'propertyshift'); } ?></div>
                        <?php } ?>

                        <div class="agent-content">
                            <h2><?php echo $agent_display_name; ?></h2>
                            <div class="agent-details">
        	                	<?php if(!empty($agent_title)) { ?><p><span><?php echo esc_attr($agent_title); ?></span><?php echo ns_core_get_icon($icon_set, 'tag'); ?><?php esc_html_e('Title', 'propertyshift'); ?>:</p><?php } ?>
        	                	<?php if(!empty($agent_email)) { ?><p><span><?php echo esc_attr($agent_email); ?></span><?php echo ns_core_get_icon($icon_set, 'envelope', 'envelope', 'mail'); ?><?php esc_html_e('Email', 'propertyshift'); ?>:</p><?php } ?>
        	                	<?php if(!empty($agent_mobile_phone)) { ?><p><span><?php echo esc_attr($agent_mobile_phone); ?></span><?php echo ns_core_get_icon($icon_set, 'phone', 'telephone'); ?><?php esc_html_e('Mobile', 'propertyshift'); ?>:</p><?php } ?>
        	                	<?php if(!empty($agent_office_phone)) { ?><p><span><?php echo esc_attr($agent_office_phone); ?></span><?php echo ns_core_get_icon($icon_set, 'building', 'apartment', 'briefcase'); ?><?php esc_html_e('Office', 'propertyshift'); ?>:</p><?php } ?>
                                <?php if(!empty($agent_website)) { ?><p><span><?php echo '<a href="'.esc_attr($agent_website).'" target="_blank">'.esc_attr($agent_website).'</a>'; ?></span><?php echo ns_core_get_icon($icon_set, 'globe'); ?><?php esc_html_e('Website', 'propertyshift'); ?>:</p><?php } ?>
                                <?php do_action('propertyshift_after_agent_details', $agent->ID); ?>
                            </div>
                            <?php if(in_array('agent_detail_item_contact', $agent_detail_items)) { ?> 
                                <div class="button button-icon agent-message right"><?php echo ns_core_get_icon($icon_set, 'envelope'); ?><?php esc_html_e('Message Agent', 'propertyshift'); ?></div>
                            <?php } ?>
                            <?php if(!empty($agent_fb) || !empty($agent_twitter) || !empty($agent_google) || !empty($agent_linkedin) || !empty($agent_youtube) || !empty($agent_instagram)) { ?>
                            <div class="center">
                                <ul class="social-icons circle clean-list">
                                    <?php if(!empty($agent_fb)) { ?><li class="agent-footer-item"><a href="<?php echo esc_url($agent_fb); ?>" target="_blank"><i class="fab fa-facebook"></i></a></li><?php } ?>
                                    <?php if(!empty($agent_twitter)) { ?><li class="agent-footer-item"><a href="<?php echo esc_url($agent_twitter); ?>" target="_blank"><i class="fab fa-twitter"></i></a></li><?php } ?>
                                    <?php if(!empty($agent_google)) { ?><li class="agent-footer-item"><a href="<?php echo esc_url($agent_google); ?>" target="_blank"><i class="fab fa-google-plus"></i></a></li><?php } ?>
                                    <?php if(!empty($agent_linkedin)) { ?><li class="agent-footer-item"><a href="<?php echo esc_url($agent_linkedin); ?>" target="_blank"><i class="fab fa-linkedin"></i></a></li><?php } ?>
                                    <?php if(!empty($agent_youtube)) { ?><li class="agent-footer-item"><a href="<?php echo esc_url($agent_youtube); ?>" target="_blank"><i class="fab fa-youtube"></i></a></li><?php } ?>
                                    <?php if(!empty($agent_instagram)) { ?><li class="agent-footer-item"><a href="<?php echo esc_url($agent_instagram); ?>" target="_blank"><i class="fab fa-instagram"></i></a></li><?php } ?>
                                </ul>
                            </div>
                            <?php } ?>
                        </div>

                        <div class="clear"></div>
	                </div>
                	<?php } ?>

                	<?php if($slug == 'description' && !empty($agent_description)) { ?>
                    <!--******************************************************-->
                    <!-- DESCRIPTION -->
                    <!--******************************************************-->
                		<div class="agent-single-item ps-single-item content widget agent-<?php echo esc_attr($slug); ?>">
                			<?php if(!empty($label)) { ?>
                                <div class="module-header module-header-left">
                                    <h4><?php echo esc_attr($label); ?></h4>
                                    <div class="widget-divider"><div class="bar"></div></div>
                                </div>
                            <?php } ?>
                			<?php echo $agent_description; ?>
                		</div>
                	<?php } ?>

                	<?php if($slug == 'contact' && $agents_obj->is_agent($agent->ID)) { ?>
                    <!--******************************************************-->
                    <!-- CONTACT -->
                    <!--******************************************************-->
                        <a class="anchor" name="anchor-agent-contact"></a>
                		<div class="agent-single-item ps-single-item widget agent-<?php echo esc_attr($slug); ?>">
                			<?php if(!empty($label)) { ?>
                                <div class="module-header module-header-left">
                                    <h4><?php echo esc_attr($label); ?></h4>
                                    <div class="widget-divider"><div class="bar"></div></div>
                                </div>
                            <?php }
                            $agents_obj->get_contact_form($agent->ID); ?>
                		</div>
                	<?php } ?>

                	<?php if($slug == 'properties' && $agents_obj->is_agent($agent->ID)) { ?>
                    <!--******************************************************-->
                    <!-- AGENT PROPERTIES -->
                    <!--******************************************************-->
                        <a class="anchor" name="anchor-agent-properties"></a>
                		<div class="agent-single-item ps-single-item widget agent-<?php echo esc_attr($slug); ?>">
                		    <?php if(!empty($label)) { ?>
                                <div class="module-header module-header-left">
                                    <h4><?php echo esc_attr($label); ?></h4>
                                    <div class="widget-divider"><div class="bar"></div></div>
                                </div>
                            <?php } ?>
                	        <?php 
                                //Set template args
                                $template_args_properties = array();
                                $template_args_properties['custom_args'] = $agent_properties['args'];
                                $template_args_properties['custom_show_filter'] = false;
                                $template_args_properties['custom_layout'] = 'grid';
                                $template_args_properties['custom_pagination'] = true;
                                if($template_location_sidebar == 'true') { $template_args_properties['custom_cols'] = 1; }
                                $template_args_properties['no_post_message'] = esc_html__( 'Sorry, no properties were found.', 'propertyshift' );
                                
                                //Load template
                                propertyshift_template_loader('loop_properties.php', $template_args_properties);
                            ?>
                        </div>
                	<?php } ?>

                <?php } ?>

        <?php } //end foreach ?>
	<?php } ?>
    </div> <!-- end ps-agent -->

<?php } ?>