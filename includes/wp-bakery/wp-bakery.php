<?php
/*****************************************************************/
/** This file maps shortcodes to WPBakery Elements
/** Shortcodes are located in /includes/shortcodes.php
/*****************************************************************/ 

add_action('vc_before_init', 'propertyshift_vc_map');
function propertyshift_vc_map() {

	/** GET GLOBAL SETTINGS **/
	$num_properties_per_page = esc_attr(get_option('ps_num_properties_per_page', 12));
	$num_agents_per_page = esc_attr(get_option('ps_num_agents_per_page', 12));

	/** LIST PROPERTIES **/
	vc_map(array(
		'name' => esc_html__( 'List Properties', 'propertyshift' ),
		'base' => 'ps_list_properties',
		'description' => esc_html__( 'Display your property listings', 'propertyshift' ),
		'icon' => PROPERTYSHIFT_DIR.'/images/icon-real-estate.svg',
		'class' => '',
		'category' => 'PropertyShift',
		'params' => array(
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Number of Properties', 'propertyshift' ),
				'param_name' => 'show_posts',
				'value' => $num_properties_per_page,
			),
			array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Show Header', 'propertyshift' ),
				'param_name' => 'show_header',
				'value' => array('Yes' => 'true', 'No' => 'false'),
				'std' => 'false',
			),
			array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Show Pagination', 'propertyshift' ),
				'param_name' => 'show_pagination',
				'value' => array('Yes' => 'true', 'No' => 'false'),
				'std' => 'true',
			),
			array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Layout', 'propertyshift' ),
				'param_name' => 'layout',
				'value' => array('Grid' => 'grid', 'Row' => 'row'),
				'std' => 'false',
			),
			array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Columns', 'propertyshift' ),
				'param_name' => 'cols',
				'value' => array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'),
				'std' => '3',
			),
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Property Status', 'propertyshift' ),
				'param_name' => 'property_status',
				'description' => esc_html__( 'Enter the property status slug', 'propertyshift' ),
			),
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Property Type', 'propertyshift' ),
				'param_name' => 'property_type',
				'description' => esc_html__( 'Enter the property type slug', 'propertyshift' ),
			),
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Property Neighborhood', 'propertyshift' ),
				'param_name' => 'property_neighborhood',
				'description' => esc_html__( 'Enter the property neighborhood slug', 'propertyshift' ),
			),
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Property City', 'propertyshift' ),
				'param_name' => 'property_city',
				'description' => esc_html__( 'Enter the property city slug', 'propertyshift' ),
			),
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Property State', 'propertyshift' ),
				'param_name' => 'property_state',
				'description' => esc_html__( 'Enter the property state slug', 'propertyshift' ),
			),
			array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Filter By', 'propertyshift' ),
				'param_name' => 'featured',
				'value' => array('Most Recent' => 'false', 'Featured' => 'true'),
				'std' => 'false',
			),
		),
	));

	/** LIST PROPERTY TAXONOMY **/
	vc_map(array(
			'name' => esc_html__( 'List Property Taxonomy', 'propertyshift' ),
			'base' => 'ps_list_property_tax',
			'description' => esc_html__( 'Display property taxonomy terms', 'propertyshift' ),
			'icon' => PROPERTYSHIFT_DIR.'/images/icon-real-estate.svg',
			'class' => '',
			'category' => 'PropertyShift',
			'params' => array(
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Taxonomy', 'propertyshift' ),
					'param_name' => 'tax',
					'value' => array(
						esc_html__( 'Property Type', 'propertyshift' ) => 'property_type', 
						esc_html__( 'Property Status', 'propertyshift' ) => 'property_status', 
						esc_html__( 'Property Neighborhood', 'propertyshift' ) => 'property_neighborhood', 
						esc_html__( 'Property City', 'propertyshift' ) => 'property_city', 
						esc_html__( 'Property State', 'propertyshift' ) => 'property_state', 
					),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Terms', 'propertyshift' ),
					'param_name' => 'terms',
					'description' => esc_html__( 'Comma separated list of term slugs. If left empty, all terms will display.', 'propertyshift' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Layout', 'propertyshift' ),
					'param_name' => 'layout',
					'value' => array(
						esc_html__( 'Grid', 'propertyshift' ) => 'grid', 
						esc_html__( 'Carousel', 'propertyshift' ) => 'carousel', 
					),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Number of Terms', 'propertyshift' ),
					'param_name' => 'show_posts',
					'value' => 5,
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Order By', 'propertyshift' ),
					'param_name' => 'orderby',
					'value' => array(
						esc_html__( 'Count', 'propertyshift' ) => 'count', 
						esc_html__( 'Name', 'propertyshift' ) => 'name', 
					),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Order Direction', 'propertyshift' ),
					'param_name' => 'order',
					'value' => array(
						esc_html__( 'Descending', 'propertyshift' ) => 'DESC', 
						esc_html__( 'Ascending', 'propertyshift' ) => 'ASC', 
					),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Hide Empty Terms', 'propertyshift' ),
					'param_name' => 'hide_empty',
					'value' => array(
						esc_html__( 'True', 'propertyshift' ) => 'true', 
						esc_html__( 'False', 'propertyshift' ) => 'false', 
					),
				),
			),
	));

	/** PROPERTY FILTER **/
	vc_map(array(
		'name' => esc_html__( 'Property Filter', 'propertyshift' ),
		'base' => 'ps_property_filter',
		'description' => esc_html__( 'Display a property search filter', 'propertyshift' ),
		'icon' => PROPERTYSHIFT_DIR.'/images/icon-real-estate.svg',
		'class' => '',
		'category' => 'PropertyShift',
		'params' => array(
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Filter ID', 'propertyshift' ),
				'param_name' => 'id',
				'description' => __( 'Filters can be created and edited <a href="/wp-admin/edit.php?post_type=ps-property-filter" target="_blank">here.</a>', 'propertyshift' ),
			),
		),
	));

	/** LIST AGENTS **/
	vc_map(array(
		'name' => esc_html__( 'List Agents', 'propertyshift' ),
		'base' => 'ps_list_agents',
		'description' => esc_html__( 'Display a list of agents', 'propertyshift' ),
		'icon' => PROPERTYSHIFT_DIR.'/images/icon-real-estate.svg',
		'class' => '',
		'category' => 'PropertyShift',
		'params' => array(
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Number of Agents', 'propertyshift' ),
				'param_name' => 'show_posts',
				'value' => $num_agents_per_page,
			),
			array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Show Pagination', 'propertyshift' ),
				'param_name' => 'show_pagination',
				'value' => array('Yes' => 'true', 'No' => 'false'),
				'std' => 'false',
			),
			array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Columns', 'propertyshift' ),
				'param_name' => 'cols',
				'value' => array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'),
				'std' => '3',
			),
		),
	));

}
?>