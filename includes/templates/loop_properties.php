<?php
    //GET GLOBAL SETTINGS
    global $post;
    $properties_page = get_option('ps_properties_page');
    $properties_tax_layout = get_option('ps_properties_default_layout', 'grid');
    $num_properties_per_page = esc_attr(get_option('ps_num_properties_per_page', 12));
    $page_template = get_post_meta($post->ID, '_wp_page_template', true);
    $property_listing_header_display = esc_attr(get_option('ps_property_listing_header_display', 'true'));

    //GET CUSTOM ARGS
    if(isset($template_args)) {
        $custom_args = isset($template_args['custom_args']) ? $template_args['custom_args'] : null;
        $custom_show_filter = isset($template_args['custom_show_filter']) ? $template_args['custom_show_filter'] : null;
        $custom_layout = isset($template_args['custom_layout']) ? $template_args['custom_layout'] : null;
        $custom_pagination = isset($template_args['custom_pagination']) ? $template_args['custom_pagination'] : null;
        $custom_cols = isset($template_args['custom_cols']) ? $template_args['custom_cols'] : null;
        $no_post_message = isset($template_args['no_post_message']) ? $template_args['no_post_message'] : null;
    }
	
    //PAGE SETTINGS
    if(is_tax()) { 
        if(!empty($properties_page)) {
            $properties_page_id = url_to_postid( $properties_page ); 
            $values = get_post_custom( $properties_page_id ); 
            $page_layout = isset( $values['ns_basics_page_layout'] ) ? esc_attr( $values['ns_basics_page_layout'][0] ) : 'full';
        } else {
            $page_layout = 'full';
        }
    } else { 
        $values = get_post_custom( $post->ID ); 
        $page_layout = isset( $values['ns_basics_page_layout'] ) ? esc_attr( $values['ns_basics_page_layout'][0] ) : 'full';
    }
	
	//GENERATE COLUMN LAYOUT
    $property_col_num = 2;
    if(isset($custom_cols)) { $property_col_num = $custom_cols; } else if($page_layout == 'full') { $property_col_num = 3; }
    $property_col_class = propertyshift_col_class($property_col_num);

    //GET PROPERTY LAYOUT
    if(isset($custom_layout)) {
        if(isset($_GET['custom_layout'])) {
            $property_layout = sanitize_text_field($_GET['custom_layout']); 
        } else if($custom_layout == 'row') { 
            $property_layout = 'row';  
        } else if($custom_layout == 'tile') {
            $property_layout = 'tile';
        } else {
            $property_layout = 'grid'; 
        }
    } else if(isset($_GET['property_layout'])) {
        $property_layout = sanitize_text_field($_GET['property_layout']); 
    } else if($page_template == 'template_properties_row.php') {
        $property_layout = 'row';
    } else if($page_template == 'template_properties_map.php' || is_tax()) {
        $property_layout = $properties_tax_layout; 
    } else {
        $property_layout = 'grid'; 
    }

    /***************************************************************************/
    /** SET QUERY ARGS **/
    /***************************************************************************/

    //SET PAGED VARIABLE
    if(is_front_page()) {  
        $paged = (get_query_var('page')) ? get_query_var('page') : 1;
    } else {  
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    }

    //DETERMINE HOW POSTS ARE SORTED
    $meta_key = '';
    $order = 'DESC';
    $order_by = get_option('ps_property_listing_default_sortby', 'date_desc');
    if(isset($_GET['sort_by'])) { $order_by = sanitize_text_field($_GET['sort_by']); }

    if ($order_by == 'date_desc') {
        $order = 'DESC';
    } else if($order_by == 'date_asc') {
        $order = 'ASC';
    } else if($order_by == 'price_asc') {
        $order = 'ASC';
        $order_by = 'meta_value_num';
        $meta_key = 'ps_property_price';
    } else if($order_by == 'price_desc') {
        $order = 'DESC';
        $order_by = 'meta_value_num';
        $meta_key = 'ps_property_price';
    }

    //SET TAXONOMIES
    if(empty($property_neighborhood)) { if(!empty($_GET['propertyNeighborhood'])) { $property_neighborhood = sanitize_text_field($_GET['propertyNeighborhood']); } else { $property_neighborhood = ''; } }
    if(empty($property_city)) { if(!empty($_GET['propertyCity'])) { $property_city = sanitize_text_field($_GET['propertyCity']); } else { $property_city = ''; } }
    if(empty($property_state)) { if(!empty($_GET['propertyState'])) { $property_state = sanitize_text_field($_GET['propertyState']); } else { $property_state = ''; } }
    if(empty($property_status)) { if(!empty($_GET['propertyStatus'])) { $property_status = sanitize_text_field($_GET['propertyStatus']); } else { $property_status = ''; } }
    if(empty($property_type)) { if(!empty($_GET['propertyType'])) { $property_type = sanitize_text_field($_GET['propertyType']); } else { $property_type = ''; } }

    //SET META QUERY
    $meta_query = array();

    //FILTER FEATURED PROPERTIES
    if (isset($_GET['featured'])) {
        $meta_query[] = array(
            'key' => 'ps_property_featured',
            'value'   => 'true'
        );
    }

    //ADVANCED META QUERY
    if(isset($_GET['advancedSearch'])) {

        if(isset($_GET['priceMin'])) { $priceMin = preg_replace("/[^0-9]/","", $_GET['priceMin']); } else { $priceMin = null; }
        if(isset($_GET['priceMax'])) { $priceMax = preg_replace("/[^0-9]/","", $_GET['priceMax']); } else { $priceMax = null; }

        $areaCompare = '';
        if(empty($_GET['areaMin'])) { $areaMin = 0; } else { $areaMin = preg_replace("/[^0-9]/","", $_GET['areaMin']); }

        if(empty($_GET['areaMax'])) {
            $areaValue = $areaMin;
            $areaCompare = '>=';
        } else {
            $areaMax = preg_replace("/[^0-9]/","", $_GET['areaMax']);
            $areaCompare = 'BETWEEN';
            $areaValue = array( $areaMin, $areaMax );
        }

        if(isset($_GET['priceMin']) && isset($_GET['priceMax'])) {
            $meta_query[] = array(
                'key' => 'ps_property_price',
                'value'   => array( $priceMin, $priceMax ),
                'type'    => 'numeric',
                'compare' => 'BETWEEN',
            );
        }

        if(!empty($_GET['beds'])) {
            $meta_query[] = array(
                'key'     => 'ps_property_bedrooms',
                'value'   => sanitize_text_field($_GET['beds']),
            );
        }

        if (!empty($_GET['baths'])) {
            $numBaths = intval(sanitize_text_field($_GET['baths']));
            $numBathsDemical = $numBaths + 0.5;
            $meta_query[] = array(
                'key' => 'ps_property_bathrooms',
                'compare' => 'IN',
                'value'   => array($_GET['baths'], $numBathsDemical)
            );
        }

        $meta_query[] = array(
            'key' => 'ps_property_area',
            'value'   => $areaValue,
            'type'    => 'numeric',
            'compare' => $areaCompare,
        );
    }

	$property_listing_args = array(
        'post_type' => 'ps-property',
        'posts_per_page' => $num_properties_per_page,
        'property_neighborhood' => $property_neighborhood,
        'property_city' => $property_city,
        'property_state' => $property_state,
        'property_status' => $property_status,
        'property_type' => $property_type,
        'order' => $order,
        'orderby' => $order_by,
        'meta_key' => $meta_key,
        'paged' => $paged,
        'meta_query' => $meta_query,
    );

    //OVERWRITE QUERY WITH CUSTOM ARGS
    if(isset($custom_args) && !isset($_GET['advancedSearch'])) {
        $property_listing_args = propertyshift_overwrite_query_args($property_listing_args, $custom_args);
    }

    $property_listing_args = apply_filters('propertyshift_pre_get_properties', $property_listing_args);
	$property_listing_query = new WP_Query( $property_listing_args );
?>

<?php 
if($property_listing_header_display == 'true') { 
    if(isset($custom_show_filter) && $custom_show_filter != 'true') {
	   //do nothing
    } else {
        propertyshift_template_loader('property-listing-header.php', ['query' => $property_listing_query], false); 
    }
}
?>

<div class="ps-listing ps-property-listing">
<?php
if ( $property_listing_query->have_posts() ) : while ( $property_listing_query->have_posts() ) : $property_listing_query->the_post(); ?>

    <?php if ($property_layout == 'row' || $property_layout == 'grid') { ?>

        <?php if ($property_layout == 'row') { ?>
            <div class="col-lg-12"><?php propertyshift_template_loader('loop_property_grid.php', null, false); ?></div>
        <?php } else { ?>
            <div class="<?php echo esc_attr($property_col_class); ?>"><?php propertyshift_template_loader('loop_property_grid.php', null, false); ?></div>
        <?php } ?>

    <?php } else if($property_layout == 'tile') {
        propertyshift_template_loader('loop_property_grid.php', null, false);
    } ?>

<?php endwhile; ?>
    <div class="clear"></div>
	</div><!-- end row -->
	
	<?php 
	wp_reset_postdata();
    $big = 999999999; // need an unlikely integer
    if(is_front_page()) { $current_page = get_query_var('page'); } else { $current_page = get_query_var('paged'); }

    $args = array(
        'base'         => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
        'format'       => '/page/%#%',
        'total'        => $property_listing_query->max_num_pages,
        'current'      => max( 1, $current_page ),
        'show_all'     => False,
        'end_size'     => 1,
        'mid_size'     => 2,
        'prev_next'    => True,
        'prev_text'    => esc_html__('&raquo; Previous', 'propertyshift'),
        'next_text'    => esc_html__('Next &raquo;', 'propertyshift'),
        'type'         => 'plain',
        'add_args'     => False,
        'add_fragment' => '',
        'before_page_number' => '',
        'after_page_number' => ''
    ); ?>
	

    <?php 
    //DETERMINE IF PAGINATION IS NEEDED
    if(isset($custom_pagination)) { 
        if ($custom_pagination === false || $custom_pagination === 'false') { $custom_pagination = false; } else { $custom_pagination = true; }
        $show_pagination = $custom_pagination; 
    } else { 
        $show_pagination = true; 
    } 
    
    if($show_pagination === true) {  ?>
	   <div class="page-list"><?php echo paginate_links( $args ); ?></div>
    <?php } ?>
	
<?php else: ?>
	<div class="ps-no-posts">
        <p>
            <?php 
            if(isset($no_post_message)) { echo wp_kses_post($no_post_message); } else { esc_html_e('Sorry, no properties were found.', 'propertyshift'); }
            if(is_user_logged_in() && current_user_can('administrator')) { 
                $new_property_url = esc_url(home_url('/')).'wp-admin/post-new.php?post_type=ps-property';
                printf(__('<em><b><a href="%s" target="_blank"> Click here</a> to add a new property.</b></em>', 'propertyshift'), $new_property_url );  
            } ?>
        </p>
    </div>
    <div class="clear"></div>
	</div><!-- end row -->
<?php endif; ?>