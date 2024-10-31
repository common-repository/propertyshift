<?php
/*-----------------------------------------------------------------------------------*/
/*  Global Template Loader
/*  Used for core plugin and add-ons
/*-----------------------------------------------------------------------------------*/
function propertyshift_template_loader($template, $template_args = array(), $wrapper = true, $plugin_path = null) {
	$theme_file = locate_template(array( 'propertyshift/' . $template));

	if($wrapper == true) { echo '<div class="propertyshift">'; }
	if(empty($theme_file)) {
		if(empty($plugin_path)) { $plugin_path = plugin_dir_path( __FILE__ ); }
		include( $plugin_path . $template);
	} else {
		include(get_parent_theme_file_path('/propertyshift/'.$template));
	}
	if($wrapper == true) { echo '</div>'; }
}

/*-----------------------------------------------------------------------------------*/
/*  Global Single Template Loader
/*  Used for core plugin and add-ons
/*-----------------------------------------------------------------------------------*/
function propertyshift_template_loader_single($template, $post_type, $plugin_path = null) {

	$theme_file = locate_template(array( 'propertyshift/' . $template));

	if(is_singular($post_type)) {
		if(empty($theme_file)) {
			echo '<div class="propertyshift">'; 
			if(empty($plugin_path)) { $plugin_path = plugin_dir_path( __FILE__ ); }
	    	include( $plugin_path . $template);
	    	echo '</div>';
	    } else {
	    	include(get_parent_theme_file_path('/propertyshift/'.$template));
	    }
	}
}


/*-----------------------------------------------------------------------------------*/
/*  Property Single Template
/*-----------------------------------------------------------------------------------*/
function propertyshift_template_property_single( $content ) {
	ob_start();
	propertyshift_template_loader_single('loop_property_single.php', 'ps-property');
    $content = $content.ob_get_clean();
    return $content;
}
add_filter( 'the_content', 'propertyshift_template_property_single', 20 );

?>