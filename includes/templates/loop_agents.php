<?php
    //GET GLOBAL SETTINGS
    $num_agents_per_page = esc_attr(get_option('ps_num_agents_per_page', 12));

    //GET CUSTOM ARGS
    if(isset($template_args)) {
        $custom_args = isset($template_args['custom_args']) ? $template_args['custom_args'] : null;
        $custom_pagination = isset($template_args['custom_pagination']) ? $template_args['custom_pagination'] : null;
        $custom_cols = isset($template_args['custom_cols']) ? $template_args['custom_cols'] : null;
        $no_post_message = isset($template_args['no_post_message']) ? $template_args['no_post_message'] : null;
    }

    //GENERATE COLUMN LAYOUT
    $agent_col_num = 3;
    if(isset($custom_cols)) { $agent_col_num = $custom_cols; }
    $agent_col_class = propertyshift_col_class($agent_col_num);

    //SET PAGED VARIABLE
    if(is_front_page()) {  
        $paged = (get_query_var('page')) ? get_query_var('page') : 1;
    } else {  
        $paged = get_query_var('paged') ? (int) get_query_var('paged') : 1;
    }

    //SET QUERY ARGS
    $agent_listing_args = array(
        'role__in' => array('ps_agent', 'administrator'),
        'number' => $num_agents_per_page,
        'paged' => $paged,
        'meta_key' => 'ps_agent_show_in_listings',
        'meta_value' => 'true',
    );

    //OVERWRITE QUERY WITH CUSTOM ARGS
    if(isset($custom_args)) { $agent_listing_args = propertyshift_overwrite_query_args($agent_listing_args, $custom_args); }

    //FILTER AND SET QUERY
    $agent_listing_args = apply_filters('propertyshift_pre_get_agents', $agent_listing_args);
    $agents_query = new WP_User_Query($agent_listing_args);
    $agents = $agents_query->get_results();
?>

<div class="ps-listing ps-agent-listing">
    <?php 
    if(!empty($agents)) {
        foreach($agents as $agent) { ?>
            <div class="<?php echo esc_attr($agent_col_class); ?>">
                <?php 
                    $template_args = array();
                    $template_args['id'] = $agent->ID;
                    propertyshift_template_loader('loop_agent.php', $template_args, false); 
                ?>
            </div>
        <?php }
    } else { 
        echo '<div class="ps-no-posts">';
        if(isset($no_post_message)) { echo wp_kses_post($no_post_message); } else { esc_html_e('Sorry, no agents were found.', 'propertyshift'); } 
        echo '</div>';
    } ?>
    <div class="clear"></div>

    <?php
    $total_users = $agents_query->get_total();
    $num_pages = ceil($total_users / $agent_listing_args['number']);
    $big = 999999999;

    $pagination_args = array(
        'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
        'format' => '/page/%#%',
        'total' => $num_pages,
        'current' => $paged,
        'prev_text'    => esc_html__('&raquo; Previous', 'propertyshift'),
        'next_text'    => esc_html__('Next &raquo;', 'propertyshift'),
        'end_size' => 1,
        'mid_size' => 5,
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
    <div class="page-list page-list-agents">
        <?php echo paginate_links($pagination_args); ?> 
    </div>
    <?php } ?>

</div><!-- end agent listings -->