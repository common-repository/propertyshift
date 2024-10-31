<?php

	//Get global settings
    global $post;
    $postID = get_the_id();
    $icon_set = esc_attr(get_option('ns_core_icon_set', 'fa'));
    if(function_exists('ns_core_load_theme_options')) { $icon_set = ns_core_load_theme_options('ns_core_icon_set'); }
    
    $admin_obj = new PropertyShift_Admin();
    $global_settings = $admin_obj->load_settings(false, null);
    $properties_page = $global_settings['ps_properties_page'];
    $property_listing_display_time = $global_settings['ps_property_listing_display_time'];
    $property_detail_items = $global_settings['ps_property_detail_items'];
    $property_detail_amenities_hide_empty = $global_settings['ps_property_detail_amenities_hide_empty'];
    $property_detail_id = $global_settings['ps_property_detail_id'];
    $property_detail_agent_contact_form = $global_settings['ps_property_detail_agent_contact_form'];

    //Get template location
    $template_location = isset($template_args['location']) ? $template_args['location'] : ''; 
    if($template_location == 'sidebar') { 
        $template_location_sidebar = 'true'; 
    } else { 
        $template_location_sidebar = 'false';
    }

	//Get property details
    $property_obj = new PropertyShift_Properties();
    $property_settings = $property_obj->load_property_settings($post->ID);
    $code = $property_settings['id']['value'];
    $featured = $property_settings['featured']['value'];
    $address = $property_obj->get_full_address($post->ID, array('Postal Code', 'Country', 'Neighborhood'));
    $address_array = $property_obj->get_full_address($post->ID, array(), 'array');
    $price = $property_settings['price']['value'];
    $price_postfix = $property_settings['price_postfix']['value'];
    $area = $property_settings['area']['value'];
    if(!empty($area)) { $area = $property_obj->get_formatted_area($area); }
    $area_postfix = $property_settings['area_postfix']['value'];
    $bedrooms = $property_settings['beds']['value'];
    $bathrooms = $property_settings['baths']['value'];
    $garages = $property_settings['garages']['value'];
    $description = $property_settings['description']['value'];
    $additional_images = $property_settings['gallery']['value'];
    $floor_plans = $property_settings['floor_plans']['value'];
    $latitude = $property_settings['latitude']['value'];
    $longitude = $property_settings['longitude']['value'];
    $video_url = $property_settings['video_url']['value'];
    $video_img = $property_settings['video_cover']['value'];

    $property_type = $property_obj->get_tax($postID, 'property_type');
    $property_status = $property_obj->get_tax($postID, 'property_status');
    $property_city = $property_obj->get_tax($postID, 'property_city');
    $property_amenities = $property_obj->get_tax_amenities($postID, $property_detail_amenities_hide_empty, null);

    //Get agent details
    $agent_id = get_the_author_meta('ID');
    $agent_display = $property_settings['agent_display']['value'];
?>	

	<div class="property-single">

        <?php do_action('propertyshift_before_property_detail', $property_settings); ?>
	
		<?php if(!empty($property_detail_items)) {
            foreach($property_detail_items as $value) { ?>
                <?php
                    if(isset($value['name'])) { $name = $value['name']; }
                    if(isset($value['label'])) { $label = $value['label']; }
                    if(isset($value['slug'])) { $slug = $value['slug']; }
                    if(isset($value['active']) && $value['active'] == 'true') { $active = 'true'; } else { $active = 'false'; }
                    if(isset($value['sidebar']) && $value['sidebar'] == 'true') { $sidebar = 'true'; } else { $sidebar = 'false'; }
                    if(isset($value['add_on'])) { $add_on = $value['add_on']; } else { $add_on = ''; }
                ?>

                <?php if($active == 'true' && ($sidebar == $template_location_sidebar)) { ?>
                	
                    <?php if($slug == 'overview') { ?>
                        <!--******************************************************-->
                        <!-- PROPERTY OVERVIEW -->
                        <!--******************************************************-->
                        <div class="property-single-item ps-single-item widget property-<?php echo esc_attr($slug); ?>">
                            
                            <div class="property-title">
                                <?php if(!empty($price)) { ?>
                                    <div class="property-price-single right">
                                        <?php echo $property_obj->get_formatted_price($price); ?>
                                        <?php if(!empty($price_postfix)) { ?><span class="price-postfix"><?php echo esc_attr($price_postfix); ?></span><?php } ?>
                                    </div>
                                <?php } ?>
                                            
                                <?php if(!empty($address)) { echo '<p class="property-address">'.ns_core_get_icon($icon_set, 'map-marker', 'map-marker', 'location').$address.'</p>'; } ?>
                                <div class="clear"></div>
                            </div>

                            <div class="property-title-below">
                                <div class="left">
                                    <?php if($featured == 'true') { ?><a href="<?php if(!empty($properties_page)) { echo esc_url($properties_page).'/?featured=true'; } ?>" class="property-tag button alt featured"><?php esc_html_e('Featured', 'propertyshift'); ?></a><?php } ?>
                                    <?php if(!empty($property_status)) { ?>
                                        <div class="property-tag button status"><?php echo wp_kses_post($property_status); ?></div>
                                    <?php } ?>
                                    <?php if($property_detail_id == 'true' && !empty($code)) { ?><div class="property-id"><?php esc_html_e('Property Code', 'propertyshift'); ?>: <?php echo $code; ?></div><?php } ?>
                                    <?php if(!empty($property_type)) { ?><div class="property-type"><?php esc_html_e('Property Type:', 'propertyshift'); ?> <?php echo wp_kses_post($property_type); ?></div><?php } ?>
                                </div>
                                <div class="right property-actions">
                                    <?php if($property_listing_display_time == 'true') {
                                        $toggle = ns_core_get_icon($icon_set, 'clock', 'clock3', 'clock');
                                        $content = human_time_diff( get_the_time('U'), current_time('timestamp') ) . esc_html__(' ago', 'propertyshift'); 
                                        echo ns_basics_tooltip($toggle, $content); 
                                    }
                                    do_action('propertyshift_property_actions'); ?>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <div class="clear"></div>

                            <?php if(!empty($bedrooms) || !empty($bathrooms) || !empty($area)) { ?>
                            <table class="property-details-single">
                                <tr>
                                    <?php if(!empty($bedrooms)) { ?><td><?php echo ns_core_get_icon($icon_set, 'bed', 'bed', 'n/a'); ?> <span><?php echo esc_attr($bedrooms); ?></span> <?php esc_html_e('Beds', 'propertyshift'); ?></td><?php } ?>
                                    <?php if(!empty($bathrooms)) { ?><td><?php echo ns_core_get_icon($icon_set, 'tint', 'bathtub', 'n/a'); ?> <span><?php echo esc_attr($bathrooms); ?></span> <?php esc_html_e('Baths', 'propertyshift'); ?></td><?php } ?>
                                    <?php if(!empty($area)) { ?><td><?php echo ns_core_get_icon($icon_set, 'expand'); ?> <span><?php echo esc_attr($area); ?></span> <?php echo esc_attr($area_postfix); ?></td><?php } ?>
                                </tr>
                            </table>
                            <?php } ?>

                        </div>
                    <?php } ?>

                    <?php if($slug == 'description' && (!empty($description)) ) { ?>
                    <!--******************************************************-->
                    <!-- DESCRIPTION -->
                    <!--******************************************************-->
                        <div class="property-single-item ps-single-item content widget property-<?php echo esc_attr($slug); ?>">
                            <?php if(!empty($label)) { ?>
                                <div class="module-header module-header-left">
                                    <h4><?php echo esc_attr($label); ?></h4>
                                    <div class="widget-divider"><div class="bar"></div></div>
                                </div>
                            <?php } ?>
                            <?php echo $description; ?>
                        </div>
                    <?php } ?>

                    <?php if($slug == 'address' && !empty($address_array)) { ?>
                    <!--******************************************************-->
                    <!-- ADDRESS -->
                    <!--******************************************************-->
                        <div class="property-single-item ps-single-item widget property-<?php echo esc_attr($slug); ?>">
                            <?php if(!empty($label)) { ?>
                                <div class="module-header module-header-left">
                                    <h4><?php echo esc_attr($label); ?></h4>
                                    <div class="widget-divider"><div class="bar"></div></div>
                                </div>
                            <?php } ?>
                            <div class="property-details-full">
                                <?php
                                foreach($address_array as $key=>$value) { ?>
                                    <div class="property-detail-item"><?php echo $key.': '; ?><span><?php echo $value; ?></span></div>
                                <?php }
                                do_action('propertyshift_property_address_widget', $postID); ?>
                                <div class="clear"></div>
                            </div>
                        </div>
                    <?php } ?>

                	<?php if($slug == 'gallery' && !empty($additional_images[0])) { ?>
                    <!--******************************************************-->
                    <!-- PROPERTY GALLERY -->
                    <!--******************************************************-->
						<div class="property-single-item ps-single-item widget property-<?php echo esc_attr($slug); ?>">
                            <?php if(!empty($label)) { ?>
                                <div class="module-header module-header-left">
                                    <h4><?php echo esc_attr($label); ?></h4>
                                    <div class="widget-divider"><div class="bar"></div></div>
                                </div>
                            <?php } ?>

						    <div class="gallery-images">
                                <?php
                                    foreach ($additional_images as $additional_image) {
                                        $image_id = ns_basics_get_image_id($additional_image);
                                        $image_thumb = wp_get_attachment_image_src($image_id, 'property-thumbnail');
                                        if(!empty($image_thumb) && !empty($image_thumb[0])) {
                                            echo '<a href="'.$additional_image.'" target="_blank"><img src="'.$image_thumb[0].'" alt="" /></a>';
                                        } else {
                                            echo '<a href="'.$additional_image.'" target="_blank"><img src="'.$additional_image.'" alt="" /></a>';
                                        }
                                    } ?>
                                <div class="clear"></div>
                            </div>

						</div>
                	<?php } ?>

                    <?php if($slug == 'property_details') { ?>
                        <!--******************************************************-->
                        <!-- PROPERTY DETAILS -->
                        <!--******************************************************-->
                        <div class="property-single-item ps-single-item widget property-<?php echo esc_attr($slug); ?>">
                            <?php if(!empty($label)) { ?>
                                <div class="module-header module-header-left">
                                    <h4><?php echo esc_attr($label); ?></h4>
                                    <div class="widget-divider"><div class="bar"></div></div>
                                </div>
                            <?php } ?>

                            <div class="property-details-full">
                                <?php if($property_detail_id == 'true' && !empty($code)) { ?><div class="property-detail-item"><?php esc_html_e('Property Code', 'propertyshift'); ?>:<span><?php echo $code; ?></span></div><?php } ?>
                                <?php if(!empty($bedrooms)) { ?><div class="property-detail-item"><?php esc_html_e('Beds', 'propertyshift'); ?>:<span><?php echo esc_attr($bedrooms); ?></span></div><?php } ?>
                                <?php if(!empty($bathrooms)) { ?><div class="property-detail-item"><?php esc_html_e('Baths', 'propertyshift'); ?>:<span><?php echo esc_attr($bathrooms); ?></span></div><?php } ?>
                                <?php if(!empty($area)) { ?><div class="property-detail-item"><?php esc_html_e('Area', 'propertyshift'); ?>:<span><?php echo esc_attr($area); ?> <?php echo esc_attr($area_postfix); ?></span></div><?php } ?>
                                <?php if(!empty($garages)) { ?><div class="property-detail-item"><?php esc_html_e('Garages', 'propertyshift'); ?>:<span><?php echo esc_attr($garages); ?></span></div><?php } ?>
                                <?php if(!empty($property_status)) { ?><div class="property-detail-item"><?php esc_html_e('Status', 'propertyshift'); ?>:<span><?php echo wp_kses_post($property_status); ?></span></div><?php } ?>
                                <?php if(!empty($property_type)) { ?><div class="property-detail-item"><?php esc_html_e('Type', 'propertyshift'); ?>:<span><?php echo wp_kses_post($property_type); ?></span></div><?php } ?>
                                <div class="property-detail-item publish-date"><?php esc_html_e('Posted On', 'propertyshift'); ?>:<span><?php echo get_the_date(); ?></span></div>
                                <?php do_action('propertyshift_property_details_widget', $postID); ?>
                                <div class="clear"></div>
                            </div>

                        </div>
                    <?php } ?>

                	<?php if($slug == 'amenities' && !empty($property_amenities)) { ?>
                    <!--******************************************************-->
                    <!-- AMENITIES -->
                    <!--******************************************************-->
						<div class="property-single-item ps-single-item widget property-<?php echo esc_attr($slug); ?>">
					        <?php if(!empty($label)) { ?>
                                <div class="module-header module-header-left">
                                    <h4><?php echo esc_attr($label); ?></h4>
                                    <div class="widget-divider"><div class="bar"></div></div>
                                </div>
                            <?php } ?>
                            <?php echo $property_amenities; ?>
						</div>
                	<?php } ?>

                    <?php if($slug == 'floor_plans' && !empty($floor_plans[0])) { ?>
                    <!--******************************************************-->
                    <!-- FLOOR PLANS -->
                    <!--******************************************************-->
                        <div class="property-single-item ps-single-item widget property-<?php echo esc_attr($slug); ?>">
                            <?php if(!empty($label)) { ?>
                                <div class="module-header module-header-left">
                                    <h4><?php echo esc_attr($label); ?></h4>
                                    <div class="widget-divider"><div class="bar"></div></div>
                                </div>
                            <?php } ?>

                            <div class="accordion" class="accordion-floor-plans">
                                <?php 
                                    if(!empty($floor_plans)) {   
                                        foreach ($floor_plans as $floor_plan) { ?>
                                            <h4 class="accordion-tab"><?php echo esc_html_e($floor_plan['title'], 'propertyshift'); ?></h4>
                                            <div class="floor-plan-item"> 
                                                <table>
                                                    <tr>
                                                        <td><strong><?php esc_html_e('Size', 'propertyshift'); ?></strong></td>
                                                        <td><strong><?php esc_html_e('Rooms', 'propertyshift'); ?></strong></td>
                                                        <td><strong><?php esc_html_e('Bathrooms', 'propertyshift'); ?></strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?php if(!empty($floor_plan['size'])) { echo esc_attr($floor_plan['size']); } else { echo '--'; } ?></td>
                                                        <td><?php if(!empty($floor_plan['rooms'])) { echo esc_attr($floor_plan['rooms']); } else { echo '--'; } ?></td>
                                                        <td><?php if(!empty($floor_plan['baths'])) { echo esc_attr($floor_plan['baths']); } else { echo '--'; } ?></td>
                                                    </tr>
                                                </table>
                                                <?php if(!empty($floor_plan['description'])) { echo '<p>'.esc_html__($floor_plan['description'], 'propertyshift').'</p>'; } ?>
                                                <?php if(!empty($floor_plan['img'])) { echo '<img class="floor-plan-img" src="'.$floor_plan['img'].'" alt="" />'; } ?>
                                            </div> 
                                        <?php }
                                    } 
                                 ?>
                            </div>

                        </div>
                    <?php } ?>
					
					<?php if($slug == 'walk_score' && (!empty($latitude) && !empty($longitude))) { ?>
                    <!--******************************************************-->
                    <!-- WALK SCORE -->
                    <!--******************************************************-->
						<div class="property-single-item ps-single-item widget property-<?php echo esc_attr($slug); ?>">
						
							<?php 
                            $json = $property_obj->get_walkscore($latitude,$longitude,$address);
							$walkScoreData = json_decode($json, true);
							?>

                            <?php if(!empty($label)) { ?>
                                <div class="module-header module-header-left">
                                    <h4>
                                        <span class="right">
                                            <img src="<?php echo esc_url($walkScoreData['logo_url']); ?>" alt="" />
                                            <a target="_blank" href="<?php echo esc_url($walkScoreData['help_link']); ?>"><img src="<?php echo esc_url($walkScoreData['more_info_icon']); ?>" alt="" /></a>
                                        </span>
                                        <?php echo esc_attr($label); ?>
                                    </h4>
                                    <div class="widget-divider"><div class="bar"></div></div>
                                </div>
                            <?php } ?>
							
							<div class="walk-score">
								<h2><?php echo esc_attr($walkScoreData['walkscore']); ?><span>/100</span></h2>
								<p><?php echo esc_attr($walkScoreData['description']); ?></p>
                                <a href="<?php echo esc_url($walkScoreData['ws_link']); ?>" target="_blank" class="button"><?php esc_html_e('View More Details', 'propertyshift'); ?></a>
							</div>
						</div>
					<?php } ?>

                	<?php if($slug == 'video' && !empty($video_url)) { ?>
                    <!--******************************************************-->
                    <!-- VIDEO -->
                    <!--******************************************************-->
						<div class="property-single-item ps-single-item widget property-<?php echo esc_attr($slug); ?>">
					        <?php if(!empty($label)) { ?>
                                <div class="module-header module-header-left">
                                    <h4><?php echo esc_attr($label); ?></h4>
                                    <div class="widget-divider"><div class="bar"></div></div>
                                </div>
                            <?php } ?>
							<a href="<?php echo esc_url($video_url); ?>" data-fancybox class="video-cover">
								<div class="video-cover-content"><i class="fa fa-play icon"></i></div>
								<?php if(!empty($video_img)) { ?>
									<img src="<?php echo esc_url($video_img); ?>" alt="" />
								<?php } else { ?>
                                    <img src="<?php echo PROPERTYSHIFT_DIR.'/images/property-img-default.gif'; ?>" alt="" />
								<?php } ?>
							</a>
						</div>
                	<?php } ?>

                    <?php if($slug == 'agent_info' && !empty($agent_id) && $agent_display == 'true') { ?>
                    <!--******************************************************-->
                    <!-- OWNER INFO -->
                    <!--******************************************************-->
						<div class="property-single-item ps-single-item widget property-<?php echo esc_attr($slug); ?>">
                            
                            <?php if(!empty($label)) { ?>
                                <div class="module-header module-header-left">
                                    <h4><?php echo esc_attr($label); ?></h4>
                                    <div class="widget-divider"><div class="bar"></div></div>
                                </div>
                            <?php } ?>

                            <?php 
                            $agents_obj = new PropertyShift_Agents();
                            $agent_settings = $agents_obj->load_agent_settings($agent_id);
                            ?>

                            <div class="agent-details">
                                <?php
                                if(!empty($agent_settings['avatar_url_thumb']['value'])) { echo '<img src="'.$agent_settings['avatar_url_thumb']['value'].'" alt="" />'; }
                                echo '<div class="agent-display-name"><strong>'.$agent_settings['display_name']['value'].'</strong></div>';
                                echo '<div class="agent-email">Email: '.$agent_settings['email']['value'].'</div>';
                                if(!empty($agent_settings['office_phone']['value'])) { echo '<div class="agent-office-phone">Office: '.$agent_settings['office_phone']['value'].'</div>'; }
                                if(!empty($agent_settings['mobile_phone']['value'])) { echo '<div class="agent-mobile-phone">Mobile: '.$agent_settings['mobile_phone']['value'].'</div>'; }
                                echo '<a href="'.get_author_posts_url($agent_id).'" class="button">'.__("View Listings", "propertyshift").'</a>';
                                do_action('propertyshift_after_agent_details', $agent_id); ?>
                            </div>

                            <?php
                            if($property_detail_agent_contact_form == 'true') {
                                $agents_obj->get_contact_form($agent_id);
                            } ?>

						</div>
                    <?php } ?>
					
					<?php if($slug == 'related') { ?>
                    <!--******************************************************-->
                    <!-- RELATED PROPERTIES -->
                    <!--******************************************************-->
						<div class="property-single-item ps-single-item widget property-<?php echo esc_attr($slug); ?>">
                            <?php if(!empty($label)) { ?>
                                <div class="module-header module-header-left">
                                    <h4><?php echo esc_attr($label); ?></h4>
                                    <div class="widget-divider"><div class="bar"></div></div>
                                </div>
                            <?php } ?>
                            <?php 
                                $args_related_properties = array(
                                    'post_type' => 'ps-property',
                                    'showposts' => 2,
                                    'tax_query' => array(
                                        'relation' => 'OR',
                                        array(
                                            'taxonomy' => 'property_status',
                                            'field' => 'slug',
                                            'terms' => $property_status
                                        ),
                                        array(
                                            'taxonomy' => 'property_type',
                                            'field' => 'slug',
                                            'terms' => $property_type
                                        ),
                                        array(
                                            'taxonomy' => 'property_city',
                                            'field' => 'slug',
                                            'terms' => $property_city
                                        ),
                                    ),
                                    'orderby' => 'rand',
                                    'post__not_in' => array( $postID )
                                );

                                //Set template args
                                $template_args_related_properties = array();
                                $template_args_related_properties['custom_args'] = $args_related_properties;
                                $template_args_related_properties['custom_show_filter'] = false;
                                $template_args_related_properties['custom_layout'] = 'grid';
                                $template_args_related_properties['custom_pagination'] = false;
                                if($template_location_sidebar == 'true') { $template_args_related_properties['custom_cols'] = 1; }
                                $template_args_related_properties['no_post_message'] = esc_html__( 'Sorry, no related properties were found.', 'propertyshift' );
                                
                                //Load template
                                propertyshift_template_loader('loop_properties.php', $template_args_related_properties);
                            ?>
						</div>
					<?php } ?>

                    <?php if(!empty($add_on)) { ?>
                        <!--******************************************************-->
                        <!-- ADD-ONS -->
                        <!--******************************************************-->
                        <?php do_action('propertyshift_property_detail_items', $property_settings, $value); ?>
                    <?php } ?>

                <?php } ?>
            <?php } //end foreach ?>
        <?php } //end if ?>

        <?php do_action('propertyshift_after_property_detail', $property_settings); ?>

	</div><!-- end property single -->