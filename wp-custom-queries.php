<?php
/*
Plugin Name: WP Custom Queries
Plugin URI: http://www.callum-macdonald.com/code/wp-custom-queries/
Description: Allows users to set normally private query vars through GET, POST or cookies. For exapmle, exclude posts from a specific category.
Version: 0.1
Author: Callum Macdonald
Author URI: http://www.callum-macdonald.com/
*/

// Test code to set example cookie
//if ($_GET['set_cookie'] == 'yes')
//	setcookie('tag__not_in', '50');

function wpcq_init() {
	
	// Method to quickly switch between $_GET / $_POST / $_REQUEST
	$wpcq_global = array_merge($_REQUEST, $_COOKIE);
	/**
	 * NOTE: If using $_GET then the contents will be slashed, while if using
	 * $_REQUEST the contents will not be slashed. Using $_REQUEST avoids adding
	 * double slashes.
	 */
	
	// If wpcq is set, overwrite the values in $wpcq_global
	if (!empty($_REQUEST['wpcq']))
		parse_str($_REQUEST['wpcq'], $wpcq_global);
	
	/**
	 * @todo This could be stored in wp_options so users can choose which options are allowed / disallowed.
	 */
	$opened_args = $all_args = array('category__in', 'category__not_in', 'category__and', 'tag__in', 'tag__not_in', 'tag__and', 'tag_slug__in', 'tag_slug__not_in', 'tag_slug__and');
	
	// Set the id / slug escape functions here so they can be easily changed
	$escape_ids = 'intval';
	$escape_slugs = 'addslashes_gpc';
	
	// Map each variable name to the type of escape function
	$escape_function = array('category__in' => $escape_ids, 'category__not_in' => $escape_ids, 'category__and' => $escape_ids, 'tag__in' => $escape_ids, 'tag__not_in' => $escape_ids, 'tag__and' => $escape_ids, 'tag_slug__in' => $escape_slugs, 'tag_slug__not_in' => $escape_slugs, 'tag_slug__and' => $escape_slugs);
	
	foreach ($opened_args as $opened_arg) {
		
		switch ($opened_arg) {
			
			/**
			 * There is no tag_slug__not_in, so let's add our very own version.
			 * We take the values and map them to tag ids then add them to tag__not_in.
			 * @todo We could also add category_slug__in/not_in/and options.
			 */
			case 'tag_slug__not_in':
				
				global $tag__not_in;
				
				$tag_slug__not_in = array_map($escape_function['tag_slug__not_in'], explode(',', $wpcq_global['tag_slug__not_in']));
				
				foreach ($tag_slug__not_in as $tag_slug) {
					
					if ($tag = get_term_by('slug', $tag_slug, 'post_tag'))
						$tag__not_in[] = $tag->term_id;
					
				}
				
				break;
			
			default:
				
				if (!empty($wpcq_global[$opened_arg])) {
					// Make the variable global so it is pickecd up during $wp->parse_request()
					global $$opened_arg;
					// Get the data from the request, explode it by , into an array, the escape each value
					$$opened_arg = array_map($escape_function[$opened_arg], explode(',', $wpcq_global[$opened_arg]));
				}
				
				break;
			
		}
		
	}
	
//	header('Content-type: text/plain;'); var_dump($_GET, $_REQUEST,/*$tag,*/ $wpcq_global['tag_slug__not_in'], $tag_slug__not_in, $tag__not_in); exit();
	
}
add_action('init', 'wpcq_init');

?>
