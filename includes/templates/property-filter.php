<?php

//Get global settings
$admin_obj = new PropertyShift_Admin();
$properties_page = $admin_obj->load_settings(false, 'ps_properties_page');

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
foreach($filter_fields as $field) { if(isset($field['active']) && $field['active'] == 'true') { $filter_num++; }}

$filter_module_class = 'filter-'.$property_filter_id.' filter-count-'.$filter_num.' ';
if($filter_layout == 'boxed') { $filter_module_class .= 'filter-boxed ';  }
if($filter_layout == 'vertical') { $filter_module_class .= 'filter-boxed filter-vertical ';  }
if($filter_position == 'above') { 
	$filter_module_class .= 'filter-above-banner '; 
} else if($filter_position == 'middle') { 
	$filter_module_class .= 'filter-inside-banner '; 
} else if($filter_position == 'below')  {
	$filter_module_class .= 'filter-below-banner ';  
}
if($display_filter_tabs == 'true') { $filter_module_class .= 'filter-has-tabs'; }

//Calculate filter item class
$filter_class = '';
if($filter_layout == 'vertical') { $filter_num = 1; }

if($filter_num == 1) {
    $filter_class = 'filter-item-1';
} else if($filter_num == 2) {
    $filter_class = 'filter-item-2';
} else if($filter_num == 3) {
    $filter_class = 'filter-item-3';
} else if($filter_num == 4) {
    $filter_class = 'filter-item-4';
} else if($filter_num == 5) {
    $filter_class = 'filter-item-5';
} else if($filter_num == 6) {
    $filter_class = 'filter-item-6';
} else if($filter_num == 7) {
    $filter_class = 'filter-item-7';
} else if($filter_num >= 8) {
    $filter_class = 'filter-item-8';
}

//Calculate container
$container = 'false';
if($filter_position == 'above' || $filter_position == 'below') { $container = 'true'; }

//Output Filter
if (!empty($filter_fields)) { ?>

	<div class="filter <?php echo $filter_module_class; ?>">
	<div <?php if($container == 'true') { echo 'class="container"'; } ?>>

		<div class="tabs" id="tabs-property-filter">
			<div class="filter-header <?php if($display_filter_tabs != 'true') { echo 'show-none'; } ?>">
	            <?php
	            if ( !empty( $property_statuses ) && !is_wp_error( $property_statuses ) ){
	                echo "<ul>"; ?>
	                <li><a href="#tabs-1"><?php esc_html_e( 'All', 'propertyshift' ); ?></a></li>
	                <?php $count = 0; ?>
	                <?php foreach ( $property_statuses as $property_status ) { ?>
	                    <?php $count++; ?>
	                    <li><a href="#tabs-<?php echo esc_attr($count) + 1; ?>"><?php echo esc_attr($property_status->name); ?></a></li>
	                <?php } 
	                echo "</ul>";
	            } else {
	                echo '<ul><li><a href="#tabs-1">'. esc_html__('All', 'propertyshift') .'</a></li></ul>';
	            } ?>
	        </div><!-- end filter header -->

			<div id="tabs-1" class="ui-tabs-hide">
				<form method="get" action="<?php echo esc_url($properties_page); ?>">
					<?php 
					$label_count = 0;
					foreach($filter_fields as $value) { 
						if(isset($value['name'])) { $name = $value['name']; }
                        if(isset($value['label'])) { $label = $value['label']; }
                        if(isset($value['placeholder'])) { $placeholder = $value['placeholder']; }
                        if(isset($value['placeholder_second'])) { $placeholder_second = $value['placeholder_second']; } else { $placeholder_second = null; }
                        if(isset($value['slug'])) { $slug = $value['slug']; }
                        if(isset($value['active']) && $value['active'] == 'true') { $active = 'true'; } else { $active = 'false'; }
                        if(isset($value['custom']) && $value['custom'] == 'true') { $custom = 'true'; } else { $custom = 'false'; } 

                        if($active == 'true') { 

                        	//label count
                        	if(isset($label) && !empty($label)) { $label_count++; } ?>

                        	<div class="form-block filter-item <?php echo esc_attr($filter_class); ?>">

                        		<?php if(!empty($label) && $custom != 'true') { echo '<label>'.esc_attr($label).'</label>'; } ?>

		                        <?php if($slug == 'property_type') { ?>
		                            <select name="propertyType" class="form-dropdown">
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
		                            <select name="propertyStatus" class="form-dropdown property-status-dropdown">
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
		                            <select name="propertyNeighborhood" class="form-dropdown property-neighborhood-dropdown">
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
		                            <select name="propertyCity" class="form-dropdown">
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
		                            <select name="propertyState" class="form-dropdown property-state-dropdown">
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
			                            <input name="priceMin" type="hidden" class="price-min-input" />
			                            <input name="priceMax" type="hidden" class="price-max-input" />
			                        </div>
		                        <?php } ?>

		                        <?php if($slug == 'beds') { ?>
		                            <select name="beds" class="form-dropdown">
		                                <option value=""><?php echo $placeholder; ?></option>
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
		                            <select name="baths" class="form-dropdown">
		                                <option value=""><?php echo $placeholder; ?></option>
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
		                            <input type="number" name="areaMin" class="area-filter area-filter-min" placeholder="<?php echo $placeholder; ?>" value="<?php if(isset($currentFilters['areaMin'])) { echo $currentFilters['areaMin']; } ?>" />
		                            <input type="number" name="areaMax" class="area-filter area-filter-max" placeholder="<?php echo $placeholder_second; ?>" value="<?php if(isset($currentFilters['areaMin'])) { echo $currentFilters['areaMax']; } ?>" />
		                            <div class="clear"></div>
		                        <?php } ?>

		                        <?php do_action('propertyshift_after_filter_fields', $value, $filter_settings); ?>

                        	</div>
                        <?php }
					} ?>

					<div class="form-block filter-item filter-item-submit <?php if($label_count > 0) { echo 'has-label'; } ?> <?php echo esc_attr($filter_class); ?>">
		                <input type="hidden" name="advancedSearch" value="true" />
		                <input type="submit" class="button" value="<?php echo esc_attr($submit_text); ?>" />
		            </div>
		            <div class="clear"></div>

				</form>
			</div><!-- end tab1 -->

			<!-- start filter content -->
	        <?php $filterCount = 0; ?>
	        <?php foreach ( $property_statuses as $property_status ) { ?>
	            <?php $filterCount++ ?>
	            <div id="tabs-<?php echo esc_attr($filterCount) + 1; ?>" class="ui-tabs-hide">
	                <form method="get" action="<?php echo esc_url($properties_page); ?>">

	                <?php 
	                    foreach($filter_fields as $value) {
	                        if(isset($value['name'])) { $name = $value['name']; }
	                        if(isset($value['label'])) { $label = $value['label']; }
	                        if(isset($value['placeholder'])) { $placeholder = $value['placeholder']; }
	                        if(isset($value['slug'])) { $slug = $value['slug']; }
	                        if(isset($value['active']) && $value['active'] == 'true') { $active = 'true'; } else { $active = 'false'; }
	                        if(isset($value['custom']) && $value['custom'] == 'true') { $custom = 'true'; } else { $custom = 'false'; }

	                        if($active == 'true') { ?>
	                        <div class="form-block filter-item <?php echo esc_attr($filter_class); ?>">
	                            
	                            <?php if(!empty($label) && $custom != 'true') { echo '<label>'.esc_attr($label).'</label>'; } ?>

	                            <?php if($slug == 'property_type') { ?>
	                                <select name="propertyType" class="form-dropdown">
	                                    <option value=""><?php echo $placeholder; ?></option>
	                                    <?php
	                                        $property_types = get_terms('property_type'); 
	                                        if ( !empty( $property_types ) && !is_wp_error( $property_types ) ) { ?>
	                                            <?php foreach ( $property_types as $property_type ) { ?>
	                                                <option value="<?php echo esc_attr($property_type->slug); ?>"><?php echo esc_attr($property_type->name); ?></option>
	                                            <?php } ?>
	                                    <?php } ?>
	                                </select>
	                            <?php } ?>

	                            <?php if($slug == 'property_status') { ?>
	                                <select name="propertyStatus" class="form-dropdown property-status-dropdown">
	                                    <option value=""><?php echo $placeholder; ?></option>
	                                    <?php
	                                        if ( !empty( $property_statuses ) && !is_wp_error( $property_statuses ) ) { ?>
	                                            <?php foreach ( $property_statuses as $property_status_select ) { ?>
	                                                <option value="<?php echo esc_attr($property_status_select->slug); ?>"><?php echo esc_attr($property_status_select->name); ?></option>
	                                            <?php } ?>
	                                    <?php } ?>
	                                </select>
	                            <?php } ?>

	                            <?php if($slug == 'property_neighborhood') { ?>
		                            <select name="propertyNeighborhood" class="form-dropdown property-neighborhood-dropdown">
		                                <option value=""><?php echo $placeholder; ?></option>
		                                <?php
		                                	$property_neighborhoods = get_terms('property_neighborhood');
		                                    if ( !empty( $property_neighborhoods ) && !is_wp_error( $property_neighborhoods ) ) { ?>
		                                        <?php foreach ( $property_neighborhoods as $property_neighborhood_select ) { ?>
		                                            <option value="<?php echo esc_attr($property_neighborhood_select->slug); ?>" <?php if($currentFilters['propertyNeighborhood'] == $property_neighborhood_select->slug) { echo 'selected'; } ?>><?php echo esc_attr($property_neighborhood_select->name); ?></option>
		                                        <?php } ?>
		                                <?php } ?>
		                            </select>
		                        <?php } ?>

	                            <?php if($slug == 'property_city') { ?>
	                                <select name="propertyCity" class="form-dropdown">
	                                    <option value=""><?php echo $placeholder; ?></option>
	                                    <?php
	                                    $property_cities = get_terms('property_city', array( 'hide_empty' => false, 'parent' => 0 )); 
	                                    if ( !empty( $property_cities ) && !is_wp_error( $property_cities ) ) { ?>
	                                        <?php foreach ( $property_cities as $property_city ) { ?>
	                                            <option value="<?php echo esc_attr($property_city->slug); ?>"><?php echo esc_attr($property_city->name); ?></option>
	                                            <?php 
	                                                $term_children = get_term_children($property_city->term_id, 'property_city'); 
	                                                if(!empty($term_children)) {
	                                                    echo '<optgroup label="'.$property_city->name.'">';
	                                                    foreach ( $term_children as $child ) {
	                                                        $term = get_term_by( 'id', $child, 'property_city' );
	                                                        echo '<option value="'.$term->slug.'">'.$term->name.'</option>';
	                                                    }
	                                                    echo '</optgroup>';
	                                                }
	                                            ?>
	                                        <?php } ?>
	                                    <?php } ?>
	                                </select>
	                            <?php } ?>

	                            <?php if($slug == 'property_state') { ?>
		                            <select name="propertyState" class="form-dropdown property-state-dropdown">
		                                <option value=""><?php echo $placeholder; ?></option>
		                                <?php
		                                	$property_states = get_terms('property_state');
		                                    if ( !empty( $property_states ) && !is_wp_error( $property_states ) ) { ?>
		                                        <?php foreach ( $property_states as $property_state_select ) { ?>
		                                            <option value="<?php echo esc_attr($property_state_select->slug); ?>" <?php if($currentFilters['propertyState'] == $property_state_select->slug) { echo 'selected'; } ?>><?php echo esc_attr($property_state_select->name); ?></option>
		                                        <?php } ?>
		                                <?php } ?>
		                            </select>
		                        <?php } ?>

	                            <?php if($slug == 'price') { ?>
	                            	<?php
	                                    $term_data = get_option('taxonomy_'.$property_status->term_id);
	                                    if (isset($term_data['price_range_min'])) { $term_price_range_min = $term_data['price_range_min']; } else { $term_price_range_min = ''; } 
	                                    if (isset($term_data['price_range_max'])) { $term_price_range_max = $term_data['price_range_max']; } else { $term_price_range_max = ''; }
	                                    if (isset($term_data['price_range_min_start'])) { $term_price_range_min_start = $term_data['price_range_min_start']; } else { $term_price_range_min_start = ''; }
	                                    if (isset($term_data['price_range_max_start'])) { $term_price_range_max_start = $term_data['price_range_max_start']; } else { $term_price_range_max_start = ''; }
	                                ?>
	                                <div class="price-slider-container">
		                                <div class="price-slider" data-count="<?php echo esc_attr($filterCount) + 1; ?>" data-min="<?php echo $price_range_min; ?>" data-max="<?php echo $price_range_max; ?>" data-min-start="<?php echo $price_range_min_start; ?>" data-max-start="<?php echo $price_range_max_start; ?>" ></div>
		                                <span class="price-slider-label price-min-label  left"></span>
		                                <span class="price-slider-label price-max-label right"></span>
		                                <div class="clear"></div>
		                                <input name="priceMin" type="hidden" class="price-min-input" />
		                                <input name="priceMax" type="hidden" class="price-max-input" />
		                                <input name="termPriceMin" type="hidden" value="<?php echo $term_price_range_min; ?>" class="term-price-min" />
	                                	<input name="termPriceMax" type="hidden" value="<?php echo $term_price_range_max; ?>" class="term-price-max" />
	                                	<input name="termPriceMinStart" type="hidden" value="<?php echo $term_price_range_min_start; ?>" class="term-price-min-start" />
	                                	<input name="termPriceMaxStart" type="hidden" value="<?php echo $term_price_range_max_start; ?>" class="term-price-max-start" />
		                           	</div>
	                            <?php } ?>

	                            <?php if($slug == 'beds') { ?>
	                                <select name="beds" class="form-dropdown">
	                                    <option value=""><?php echo $placeholder; ?></option>
	                                    <option value="1">1</option>
	                                    <option value="2">2</option>
	                                    <option value="3">3</option>
	                                    <option value="4">4</option>
	                                    <option value="5">5</option>
	                                    <option value="6">6</option>
	                                    <option value="7">7</option>
	                                    <option value="8">8</option>
	                                    <option value="9">9</option>
	                                    <option value="10">10</option>
	                                </select>
	                            <?php } ?>

	                            <?php if($slug == 'baths') { ?>
	                                <select name="baths" class="form-dropdown">
	                                    <option value=""><?php echo $placeholder; ?></option>
	                                    <option value="1">1</option>
	                                    <option value="2">2</option>
	                                    <option value="3">3</option>
	                                    <option value="4">4</option>
	                                    <option value="5">5</option>
	                                    <option value="6">6</option>
	                                    <option value="7">7</option>
	                                    <option value="8">8</option>
	                                    <option value="9">9</option>
	                                    <option value="10">10</option>
	                                </select>
	                            <?php } ?>

	                            <?php if($slug == 'area') { ?>
	                                <input type="number" name="areaMin" class="area-filter area-filter-min" placeholder="<?php echo $placeholder; ?>" />
	                                <input type="number" name="areaMax" class="area-filter area-filter-max" placeholder="<?php echo $placeholder_second; ?>" />
	                                <div class="clear"></div>
	                            <?php } ?>

	                            <?php do_action('propertyshift_after_filter_fields', $value, $filter_settings); ?>
	                        </div>
	                        <?php } ?>

	                <?php } ?>

	                <div class="filter-item filter-item-submit <?php if($label_count > 0) { echo 'has-label'; } ?> <?php echo esc_attr($filter_class); ?>">
	                    <input type="hidden" name="propertyStatus" value="<?php echo esc_attr($property_status->slug); ?>" />
	                    <input type="hidden" name="advancedSearch" value="true" />
	                    <input type="submit" class="button" value="<?php echo esc_attr($submit_text); ?>" />
	                </div>
	                <div class="clear"></div>
	                
	            </form>
	            </div>
	        <?php } ?>
	        <div class="clear"></div>

		</div><!-- end tabs -->

	</div><!-- end container -->
	</div><!-- end filter -->

<?php } ?>