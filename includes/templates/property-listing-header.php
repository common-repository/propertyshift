<?php
//Get global settings
global $wp;
$currentUrl = home_url( $wp->request );
$icon_set = esc_attr(get_option('ns_core_icon_set', 'fa'));
if(function_exists('ns_core_load_theme_options')) { $icon_set = ns_core_load_theme_options('ns_core_icon_set'); }
$order_by = get_option('ps_property_listing_default_sortby', 'date_desc');
if(isset($_GET['sort_by'])) { $order_by = sanitize_text_field($_GET['sort_by']); }

//Get template args
$property_listing_query = $template_args['query'];

//Get all current filters from URL
$currentFilters = array();
foreach($_GET as $key=>$value) { if(!empty($value)) { $currentFilters[$key] = $value; } }
if(!array_key_exists("advancedSearch",$currentFilters)) { $currentFilters = null; }
?>

<div class="property-listing-header">
	<span class="property-count left">
		<?php echo esc_attr($property_listing_query->found_posts); ?> <?php esc_html_e('properties found', 'propertyshift'); ?>
		<?php if(!empty($currentFilters)) { echo '<a href="'.get_the_permalink().'" class="button small outline clear-property-filters">'.ns_core_get_icon($icon_set, 'times', 'cross', 'cross').esc_html__('Clear Filters', 'propertyshift').'</a>'; } ?>
	</span>
	<form action="<?php echo $currentUrl; ?>" method="get" class="right">
		<select name="sort_by" onchange="this.form.submit();">
			<option value="date_desc" <?php if($order_by == 'date_desc') { echo 'selected'; } ?>><?php esc_html_e('New to Old', 'propertyshift'); ?></option>
			<option value="date_asc" <?php if($order_by == 'date_asc') { echo 'selected'; } ?>><?php esc_html_e('Old to New', 'propertyshift'); ?></option>
			<option value="price_desc" <?php if($order_by == 'price_desc') { echo 'selected'; } ?>><?php esc_html_e('Price (High to Low)', 'propertyshift'); ?></option>
			<option value="price_asc" <?php if($order_by == 'price_asc') { echo 'selected'; } ?>><?php esc_html_e('Price (Low to High)', 'propertyshift'); ?></option>
		</select>
		<?php
		foreach($_GET as $name => $value) {
			if($name != 'sort_by') {
				$name = htmlspecialchars($name);
				$value = htmlspecialchars($value);
				echo '<input type="hidden" name="'. $name .'" value="'. $value .'">';
			}
		}
		?>
	</form>
	<div class="clear"></div>
</div>