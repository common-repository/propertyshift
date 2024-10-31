<?php
    //Get global settings
    $admin_obj = new PropertyShift_Admin();
    $properties_page = $admin_obj->load_settings(false, 'ps_properties_page');
    $icon_set = esc_attr(get_option('ns_core_icon_set', 'fa'));
    if(function_exists('ns_core_load_theme_options')) { $icon_set = ns_core_load_theme_options('ns_core_icon_set'); }

    //Get template args
    $property_filter_id = isset($template_args['id']) ? $template_args['id'] : null;
    $shortcode_filter = isset($template_args['shortcode_filter']) ? $template_args['shortcode_filter'] : null;
    $widget_filter = isset($template_args['widget_filter']) ? $template_args['widget_filter'] : null;

    //Get filter details
    $filter_obj = new PropertyShift_Filters();
    $filter_settings = $filter_obj->load_filter_settings($property_filter_id);
    $filter_position = $filter_settings['position']['value'];
    $filter_layout = $filter_settings['layout']['value'];
    $display_filter_tabs = $filter_settings['display_tabs']['value'];
    $filter_fields = $filter_settings['fields']['value'];
    $price_range_min = $filter_settings['fields']['children']['price_min']['value'];
    $price_range_max = $filter_settings['fields']['children']['price_max']['value'];
    $price_range_min_start = $filter_settings['fields']['children']['price_min_start']['value'];
    $price_range_max_start = $filter_settings['fields']['children']['price_max_start']['value'];
    $submit_text = $filter_settings['submit_button_text']['value'];

    //Get all current filters from URL
    $currentFilters = array();
    foreach($_GET as $key=>$value) { 
        if(!empty($value)) { $currentFilters[$key] = sanitize_text_field($value); }
    }

    //Get property status terms
    $property_statuses = get_terms('property_status'); 

    //If filter came from widget or shortcode, remove position
    if(isset($widget_filter)) { $filter_layout = ''; }
    if(isset($widget_filter) || isset($shortcode_filter)) { $filter_position = ''; }

    //Calculate filter module class
    $filter_num = 1;
    foreach($filter_fields as $field) { if($field['active'] == 'true') { $filter_num++; }}
    if($filter_num > 4) { $show_advanced = true; } else { $show_advanced = false; }

    $filter_module_class = 'filter-'.$property_filter_id.' filter-count-'.$filter_num.' ';
    if($show_advanced) { $filter_module_class .= 'show-advanced '; }
    if($filter_position == 'above') { 
        $filter_module_class .= 'filter-above-banner '; 
    } else if($filter_position == 'middle') { 
        $filter_module_class .= 'filter-inside-banner '; 
    } else if($filter_position == 'below')  {
        $filter_module_class .= 'filter-below-banner ';  
    }

    //Calculate filter item class
    $filter_class = '';
    if($filter_num == 1) {
        $filter_class = 'filter-item-1';
    } else if($filter_num == 2) {
        $filter_class = 'filter-item-2';
    } else if($filter_num == 3) {
        $filter_class = 'filter-item-3';
    } else if($filter_num == 4) {
        $filter_class = 'filter-item-4';
    } else if($filter_num == 5) {
        $filter_class = 'filter-item-4';
    } else if($filter_num == 6) {
        $filter_class = 'filter-item-4';
    } else if($filter_num == 7) {
        $filter_class = 'filter-item-4';
    } else if($filter_num >= 8) {
        $filter_class = 'filter-item-4';
    }

    //Calculate container
    $container = 'false';
    if($filter_position == 'above' || $filter_position == 'below') { $container = 'true'; }
?>

<?php if (!empty($filter_fields)) { ?>

<div class="filter filter-minimal <?php echo $filter_module_class; ?>">
    <div <?php if($container == 'true') { echo 'class="container"'; } ?>>

        <form method="get" action="<?php echo esc_url($properties_page); ?>">

            <?php  
                $count = 1;
                $label_count = 0;
                foreach($filter_fields as $value) { ?>

                    <?php
                        if(isset($value['name'])) { $name = $value['name']; }
                        if(isset($value['label'])) { $label = $value['label']; }
                        if(isset($value['placeholder'])) { $placeholder = $value['placeholder']; }
                        if(isset($value['placeholder_second'])) { $placeholder_second = $value['placeholder_second']; } else { $placeholder_second = null; }
                        if(isset($value['slug'])) { $slug = $value['slug']; }
                        if(isset($value['active']) && $value['active'] == 'true') { $active = 'true'; } else { $active = 'false'; }
                        if(isset($value['custom']) && $value['custom'] == 'true') { $custom = 'true'; } else { $custom = 'false'; }
                    ?>

                    <?php if($active == 'true') { ?>

                    <?php 
                    if($count == 4) { ?>
                        <div style="margin-right:0;" class="form-block filter-item filter-item-submit <?php if($label_count > 0) { echo 'has-label'; } ?> <?php if($show_advanced == false) { echo 'hide-advanced'; } ?> <?php echo esc_attr($filter_class); ?>">
                            <div class="advanced-options-toggle"><?php echo ns_core_get_icon($icon_set, 'cog', 'cog', 'gear'); ?><span><?php esc_html_e( 'Advanced', 'propertyshift' ); ?></span></div>
                            <input type="hidden" name="advancedSearch" value="true" />
                            <button type="submit" class="button alt"><?php echo ns_core_get_icon($icon_set, 'search', 'magnifier', 'search' ); ?></button>
                        </div>
                    <?php }

                    if($count == 4) { ?>
                        <div class="clear"></div>
                        <div class="filter-minimal-advanced show-none">
                    <?php } ?>

                    <div class="form-block filter-item <?php echo esc_attr($filter_class); ?>">

                        <?php if(!empty($label) && $custom != 'true') {
                            $label_count++;
                            echo '<label>'.esc_attr($label).'</label>'; 
                        } ?>

                        <?php if($slug == 'property_type') { ?>
                            <select name="propertyType" class="filter-input">
                                <option value=""><?php echo $placeholder; ?></option>
                                <?php
                                    $property_types = get_terms('property_type'); 
                                    if ( !empty( $property_types ) && !is_wp_error( $property_types ) ) { ?>
                                        <?php foreach ( $property_types as $property_type ) { ?>
                                            <option value="<?php echo esc_attr($property_type->slug); ?>" <?php if(isset($currentFilters['propertyType']) && $currentFilters['propertyType'] == $property_type->slug) { echo 'selected'; } ?>><?php echo esc_attr($property_type->name); ?></option>
                                        <?php } ?>
                                <?php } ?>
                            </select>
                        <?php } ?>

                        <?php if($slug == 'property_status') { ?>
                            <select name="propertyStatus" class="filter-input property-status-dropdown">
                                <option value=""><?php echo $placeholder; ?></option>
                                <?php
                                    if ( !empty( $property_statuses ) && !is_wp_error( $property_statuses ) ) { ?>
                                        <?php foreach ( $property_statuses as $property_status_select ) { ?>
                                            <option value="<?php echo esc_attr($property_status_select->slug); ?>" <?php if(isset($currentFilters['propertyStatus']) && $currentFilters['propertyStatus'] == $property_status_select->slug) { echo 'selected'; } ?>><?php echo esc_attr($property_status_select->name); ?></option>
                                        <?php } ?>
                                <?php } ?>
                            </select>
                        <?php } ?>

                        <?php if($slug == 'property_neighborhood') { ?>
                            <select name="propertyNeighborhood" class="filter-input property-neighborhood-dropdown">
                                <option value=""><?php echo $placeholder; ?></option>
                                <?php
                                    $property_neighborhoods = get_terms('property_neighborhood');
                                    if ( !empty( $property_neighborhoods ) && !is_wp_error( $property_neighborhoods ) ) { ?>
                                        <?php foreach ( $property_neighborhoods as $property_neighborhood_select ) { ?>
                                            <option value="<?php echo esc_attr($property_neighborhood_select->slug); ?>" <?php if(isset($currentFilters['propertyNeighborhood']) && $currentFilters['propertyNeighborhood'] == $property_neighborhood_select->slug) { echo 'selected'; } ?>><?php echo esc_attr($property_neighborhood_select->name); ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        <?php } ?>

                        <?php if($slug == 'property_city') { ?>
                            <select name="propertyCity" class="filter-input">
                                <option value=""><?php echo $placeholder; ?></option>
                                <?php
                                $property_cities = get_terms('property_city', array( 'hide_empty' => false, 'parent' => 0 )); 
                                if ( !empty( $property_cities ) && !is_wp_error( $property_cities ) ) { ?>
                                    <?php foreach ( $property_cities as $property_city ) { ?>
                                        <option value="<?php echo esc_attr($property_city->slug); ?>" <?php if(isset($currentFilters['propertyCity']) && $currentFilters['propertyCity'] == $property_city->slug) { echo 'selected'; } ?>><?php echo esc_attr($property_city->name); ?></option>
                                        <?php 
                                            $term_children = get_term_children($property_city->term_id, 'property_city'); 
                                            if(!empty($term_children)) {
                                                echo '<optgroup label="'.$property_city->name.'">';
                                                foreach ( $term_children as $child ) {
                                                    $term = get_term_by( 'id', $child, 'property_city' ); ?>
                                                    <option value="<?php echo $term->slug; ?>" <?php if($currentFilters['propertyCity'] == $term->slug) { echo 'selected'; } ?>><?php echo $term->name; ?></option>
                                                <?php }
                                                echo '</optgroup>';
                                            }
                                        ?>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        <?php } ?>

                        <?php if($slug == 'property_state') { ?>
                            <select name="propertyState" class="filter-input property-state-dropdown">
                                <option value=""><?php echo $placeholder; ?></option>
                                <?php
                                    $property_states = get_terms('property_state');
                                    if ( !empty( $property_states ) && !is_wp_error( $property_states ) ) { ?>
                                        <?php foreach ( $property_states as $property_state_select ) { ?>
                                            <option value="<?php echo esc_attr($property_state_select->slug); ?>" <?php if(isset($currentFilters['propertyState']) && $currentFilters['propertyState'] == $property_state_select->slug) { echo 'selected'; } ?>><?php echo esc_attr($property_state_select->name); ?></option>
                                        <?php } ?>
                                <?php } ?>
                            </select>
                        <?php } ?>

                        <?php if($slug == 'price') { ?>
                            <?php
                                if(!empty($currentFilters['priceMin'])) {
                                    $currentFilterPriceMin = preg_replace("/[^0-9]/","", $currentFilters['priceMin']);
                                    $price_range_min_start = $currentFilterPriceMin;
                                }
                                if(!empty($currentFilters['priceMax'])) {
                                    $currentFilterPriceMax = preg_replace("/[^0-9]/","", $currentFilters['priceMax']);
                                    $price_range_max_start = $currentFilterPriceMax;
                                }
                            ?>
                            <div class="price-slider-container">
                                <div class="price-slider" data-count="1" data-min="<?php echo $price_range_min; ?>" data-max="<?php echo $price_range_max; ?>" data-min-start="<?php echo $price_range_min_start; ?>" data-max-start="<?php echo $price_range_max_start; ?>" ></div>
                                <span class="price-slider-label price-min-label left"></span>
                                <span class="price-slider-label price-max-label right"></span>
                                <div class="clear"></div>
                                <input name="priceMin" type="hidden" class="price-hidden-input price-min-input" />
                                <input name="priceMax" type="hidden" class="price-hidden-input price-max-input" />
                            </div>
                        <?php } ?>

                        <?php if($slug == 'beds') { ?>
                            <select name="beds" class="filter-input">
                                <option value="" disabled selected><?php echo $placeholder; ?></option>
                                <option value=""><?php esc_html_e( 'Any', 'propertyshift' ); ?></option>
                                <option value="1" <?php if(isset($currentFilters['beds']) && $currentFilters['beds'] == '1') { echo 'selected'; } ?>>1</option>
                                <option value="2" <?php if(isset($currentFilters['beds']) && $currentFilters['beds'] == '2') { echo 'selected'; } ?>>2</option>
                                <option value="3" <?php if(isset($currentFilters['beds']) && $currentFilters['beds'] == '3') { echo 'selected'; } ?>>3</option>
                                <option value="4" <?php if(isset($currentFilters['beds']) && $currentFilters['beds'] == '4') { echo 'selected'; } ?>>4</option>
                                <option value="5" <?php if(isset($currentFilters['beds']) && $currentFilters['beds'] == '5') { echo 'selected'; } ?>>5</option>
                                <option value="6" <?php if(isset($currentFilters['beds']) && $currentFilters['beds'] == '6') { echo 'selected'; } ?>>6</option>
                                <option value="7" <?php if(isset($currentFilters['beds']) && $currentFilters['beds'] == '7') { echo 'selected'; } ?>>7</option>
                                <option value="8" <?php if(isset($currentFilters['beds']) && $currentFilters['beds'] == '8') { echo 'selected'; } ?>>8</option>
                                <option value="9" <?php if(isset($currentFilters['beds']) && $currentFilters['beds'] == '9') { echo 'selected'; } ?>>9</option>
                                <option value="10" <?php if(isset($currentFilters['beds']) && $currentFilters['beds'] == '10') { echo 'selected'; } ?>>10</option>
                            </select>
                        <?php } ?>

                        <?php if($slug == 'baths') { ?>
                            <select name="baths" class="filter-input">
                                <option value="" disabled selected><?php echo $placeholder; ?></option>
                                <option value=""><?php esc_html_e( 'Any', 'propertyshift' ); ?></option>
                                <option value="1" <?php if(isset($currentFilters['baths']) && $currentFilters['baths'] == '1') { echo 'selected'; } ?>>1</option>
                                <option value="2" <?php if(isset($currentFilters['baths']) && $currentFilters['baths'] == '2') { echo 'selected'; } ?>>2</option>
                                <option value="3" <?php if(isset($currentFilters['baths']) && $currentFilters['baths'] == '3') { echo 'selected'; } ?>>3</option>
                                <option value="4" <?php if(isset($currentFilters['baths']) && $currentFilters['baths'] == '4') { echo 'selected'; } ?>>4</option>
                                <option value="5" <?php if(isset($currentFilters['baths']) && $currentFilters['baths'] == '5') { echo 'selected'; } ?>>5</option>
                                <option value="6" <?php if(isset($currentFilters['baths']) && $currentFilters['baths'] == '6') { echo 'selected'; } ?>>6</option>
                                <option value="7" <?php if(isset($currentFilters['baths']) && $currentFilters['baths'] == '7') { echo 'selected'; } ?>>7</option>
                                <option value="8" <?php if(isset($currentFilters['baths']) && $currentFilters['baths'] == '8') { echo 'selected'; } ?>>8</option>
                                <option value="9" <?php if(isset($currentFilters['baths']) && $currentFilters['baths'] == '9') { echo 'selected'; } ?>>9</option>
                                <option value="10" <?php if(isset($currentFilters['baths']) && $currentFilters['baths'] == '10') { echo 'selected'; } ?>>10</option>
                            </select>
                        <?php } ?>

                        <?php if($slug == 'area') { ?>
                            <input type="number" name="areaMin" class="filter-input area-filter area-filter-min" placeholder="<?php echo $placeholder; ?>" value="<?php if(isset($currentFilters['areaMin'])) { echo $currentFilters['areaMin']; } ?>" />
                            <input type="number" name="areaMax" class="filter-input area-filter area-filter-max" placeholder="<?php echo $placeholder_second; ?>" value="<?php if(isset($currentFilters['areaMax'])) { echo $currentFilters['areaMax']; } ?>" />
                            <div class="clear"></div>
                        <?php } ?>

                        <?php do_action('propertyshift_after_filter_fields', $value, $filter_settings); ?>

                    </div>
                    <?php 
                    if($count == ($filter_num - 1) && $count >= 4) { echo '<div class="clear"></div></div>'; } 

                    if($filter_num <= 4 && $count >= ($filter_num - 1)) { ?>
                        <div style="margin-right:0;" class="form-block filter-item filter-item-submit <?php if($label_count > 0) { echo 'has-label'; } ?> <?php if($show_advanced == false) { echo 'hide-advanced'; } ?> <?php echo esc_attr($filter_class); ?>">
                            <div class="advanced-options-toggle"><?php echo ns_core_get_icon($icon_set, 'cog', 'cog', 'gear'); ?><span><?php esc_html_e( 'Advanced', 'propertyshift' ); ?></span></div>
                            <input type="hidden" name="advancedSearch" value="true" />
                            <button type="submit" class="button alt"><?php echo ns_core_get_icon($icon_set, 'search', 'magnifier', 'search' ); ?></button>
                        </div>
                    <?php } ?>

                    <?php $count++; ?>
                    <?php } ?>

            <?php } ?>
            <div class="clear"></div>

            <div class="filter-minimal-mobile-toggle button alt"><?php echo ns_core_get_icon($icon_set, 'cog', 'cog', 'gear'); ?><?php echo $submit_text; ?></div>
            
        </form>
    
    </div><!-- end container -->
</div><!-- end filter -->
<?php } ?>