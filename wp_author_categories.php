<?php
/*
Plugin Name: Author Categories
Plugin URI: http://www.makesites.cc/projects/wp_author_categories
Description: A plugin that wraps around the default 'wp_list_categories' and presents an author's menu, when in the author pages. 
Version: 1.0
Author: MAKE SITES
Author URI: http://www.makesites.cc/
*/

/*  Copyright 2009 MAKE SITES  (email : makis@makesites.cc)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/**
 *  Notice: As the plugins full name is "WP Author Categories", 
 *  the initials "wpac" will be used as the prefix for all custom functions. 
 */


/**
 *  Main function - launched from a template file, in place of wp_list_categories()
 */
function wp_author_categories( $args = '' ){
	global $author;

	// have a general fallback for non author pages, just in case this condition is not already present in the 'sidebar.php'
	if( $author ){ 
		wpac_list_categories($args);
	} else {
		wp_list_categories($args);
	}
}


/**
 *  Display or retrieve the HTML list of categories.
 *
 *  This is a modification of the wp_list_categories() function, 
 *  available in the 'category-teplate.php' 
 */
function wpac_list_categories( $args = '' ) {
	global $wpac_show_count;

	$defaults = array(
		'show_option_all' => '', 'orderby' => 'name',
		'order' => 'ASC', 'show_last_update' => 0,
		'style' => 'list', 'show_count' => 0,
		'hide_empty' => 1, 'use_desc_for_title' => 1,
		'child_of' => 0, 'feed' => '', 'feed_type' => '',
		'feed_image' => '', 'exclude' => '', 'exclude_tree' => '', 'current_category' => 0,
		'hierarchical' => true, 'title_li' => __( 'Categories' ),
		'echo' => 1, 'depth' => 0
	);

	$r = wp_parse_args( $args, $defaults );

	if ( !isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
		$r['pad_counts'] = true;
	}

	if ( isset( $r['show_date'] ) ) {
		$r['include_last_update_time'] = $r['show_date'];
	}

	if ( true == $r['hierarchical'] ) {
		$r['exclude_tree'] = $r['exclude'];
		$r['exclude'] = '';
	}

	// disable the post count calculation temporarily since worpress only stores the overall post count. 
	$wpac_show_count = $r['show_count'];
	$r['show_count'] = false;

	
	extract( $r );

	$categories = get_categories( $r );

	// create the menu the same way as usual, with no author information injected
	$output = '';
	if ( $title_li && 'list' == $style )
			$output = '<li class="categories">' . $r['title_li'] . '<ul>';

	if ( empty( $categories ) ) {
		if ( 'list' == $style )
			$output .= '<li>' . __( "No categories" ) . '</li>';
		else
			$output .= __( "No categories" );
	} else {
		global $wp_query;

		if( !empty( $show_option_all ) )
			if ( 'list' == $style )
				$output .= '<li><a href="' .  get_bloginfo( 'url' )  . '">' . $show_option_all . '</a></li>';
			else
				$output .= '<a href="' .  get_bloginfo( 'url' )  . '">' . $show_option_all . '</a>';

		if ( empty( $r['current_category'] ) && is_category() )
			$r['current_category'] = $wp_query->get_queried_object_id();

		if ( $hierarchical )
			$depth = $r['depth'];
		else
			$depth = -1; // Flat.

		$output .= walk_category_tree( $categories, $depth, $r );
	}
	
	if ( $title_li && 'list' == $style )
		$output .= '</ul></li>';


	// parse through the created markup and update with author links and postcount
	$output = wpac_cleanRawMenu($output);
	$output = wpac_updateLinks($output);
	if( $wpac_show_count ) {
		$output = wpac_addPostCount($output);
	}
	
	// finally output the autho's menu
	if ( $echo )
		echo $output;
	else
		return $output;

}


/**
 *  This is a precaution to make the parsing smoother
 */
function wpac_cleanRawMenu( $output ){
	// clean the break-lines before the category item ends so we can make a regular expression later on
	$output = str_replace("\n</li>", "</li>", $output);
	return $output;
}


/**
 *  Update the menu with author links.
 */
function wpac_updateLinks( $output ){
	global $author, $wp_rewrite;

	/**
	// check if we need to add SEO friendly URLs - disabled for this version
	$permalink = $wp_rewrite->permalink_structure;
	if( empty( $permalink ) ){
		$author_path = "&author=$author";
	} else if( substr($permalink, -1) == "/" ) {
		$author_path = "author/$author";
	}else {
		$author_path = "/author/$author";
	}
	*/
	$author_path = "&author=$author";

	// write the regular expression to find all links in our menu
	$regexp = "(([a-zA-Z]+://)([a-zA-Z0-9?&%.;:/=+_-]*))"; 
	$output = preg_replace("#$regexp#i", "$1$author_path", $output);
	
	return $output;
 
}


/**
 *  Add the post count for each category.
 */
function wpac_addPostCount( $output ){

	// write the regular expression to find the id for every category based on it's class name "cat-item-?"
	$regexp = "<li class=\"(.*) cat-item-([0-9]{1,3})(.*)\">(.*)</li>"; 
	$output = preg_replace_callback("#$regexp#i", "wpac_findPostCount", $output);
	return $output;

}


/**
 *  Find the post count for each category.
 */
function wpac_findPostCount( $x ){
	global $wpdb, $author;
	
	$category = $x[2];

	// find the author's post count in the category
	$sql = "SELECT COUNT(*) FROM $wpdb->term_relationships AS t INNER JOIN $wpdb->posts as tt ON t.object_id=tt.ID WHERE t.term_taxonomy_id=$category AND tt.post_author=$author AND tt.post_type='post'";
	$post_count = $wpdb->get_var($sql);

	if( $post_count > 0 ){
		// re-create the link we processed earlier
		$link = "<li class=\"$x[1] cat-item-$x[2]$x[3]\">$x[4] (" . $post_count . ")</li>";
	} else {
		// don't display empty categories
		$link = "";

	}
	
	return $link;

}


?>