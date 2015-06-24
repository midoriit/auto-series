<?php
/*
	Plugin Name: Automatic Series Organizer
	Plugin URI: http://midoriit.com/works/auto-series.html
	Description: Automaticallly organize pagination and widgets from series of posts.
	Version: 0.3
	Author: Midori IT Office, LLC
	Author URI: http://midoriit.com/
	License: GPLv2 or later
	Text Domain: auto-series
	Domain Path: /languages/
*/

/*
 * Localization
*/
add_action( 'plugins_loaded', 'auto_series_loaded' );
function auto_series_loaded() {
	$ret = load_plugin_textdomain( 'auto-series', false,
		basename( dirname(__FILE__) ).'/languages/' );
}

/*
 * List of Series Widget
*/
add_action( 'widgets_init', function() {
	register_widget("Series_Widget");
});
class Series_Widget extends WP_Widget {

	// Constructor
	function __construct() {
		parent::__construct(
			'Series_Widget',
			__('Series List', 'auto-series'),
			array( 'description' => __( 'Show List of Series', 'auto-series' ), )
		);
	}

	// Widget option form
	function form($instance) {
		$title = esc_attr($instance['title']);
		echo '<p><label for="'.$this->get_field_id('title').'">';
		echo __( 'Title:', 'auto-series' );
		echo '<input class="widefat" id="'.$this->get_field_id('title').'" name="'. $this->get_field_name('title').'" type="text" value="'.$title.'" />';
		echo '</label></p>';
	}

	// Updaate widget option
	function update($new_instance, $old_instance) {
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	// Output list of series
	function widget($args, $instance) {

		extract( $args );
		echo $before_widget;
		if( $instance['title'] ) {
			echo $before_title.$instance['title'].$after_title;
		} else {
			echo $before_title.__( 'Series List', 'auto-series' ).$after_title;
		}

		$args = array(
			'meta_key'=> 'series_id',
			'orderby' => 'date',
			'order' => 'ASC',
			'nopaging' => true );

		$series = array();

		$posts = new WP_Query( $args );
		if( $posts->found_posts > 1) {
			echo '<ul>';
			while( $posts->have_posts() ) {
				$posts->the_post();
				$series_id = get_post_meta(get_the_ID(), 'series_id', true);
				if( !in_array( $series_id, $series ) ) {
					echo '<li><a href="'.get_permalink().'">'.$series_id.'</a></li>';
					array_push($series, $series_id);
				}
			}
			echo '</ul>';
		}
		wp_reset_postdata();
		echo $after_widget;
	}
}

/*
 * CSS for Pagination
 */
add_action( 'wp_print_styles', 'pagination_css' );
function pagination_css() {
	$css_file = plugins_url( 'auto-series.css', __FILE__ );
	wp_enqueue_style( 'auto-series', $css_file );
}

/*
 * Show Pagination
 */
function series_pagination() {

	global $post;
	$current = $post->ID;
	$meta_value = get_post_meta($current, 'series_id', true);

	if( $meta_value == '' ) {
		return;
	}

	$args = array(
		'meta_key'=> 'series_id',
		'meta_value' => $meta_value,
		'orderby' => 'date',
		'order' => 'ASC',
		'nopaging' => true );

	$pagination='';
	$series = new WP_Query( $args );
	if( $series->found_posts > 1) {
		$num = 1;
		$pagination = '<div class="series_pagination"><span>'.$meta_value.'</span> <span>';
		while( $series->have_posts() ) {
			$series->the_post();
			if( $current == get_the_ID() ) {
				if( $num > 9 ) {
					$pagination = $pagination.'<a class="current_post wide">'.$num.'</a>';
				} else {
					$pagination = $pagination.'<a class="current_post">'.$num.'</a>';
				}
			} else {
				$permalink = esc_url( apply_filters( 'the_permalink', get_permalink() ) );
				if( $num > 9 ) {
					$pagination = $pagination.'<a class="wide" href="'.$permalink.'" title="'.get_the_title().'">'.$num.'</a>';
				} else {
					$pagination = $pagination.'<a href="'.$permalink.'" title="'.get_the_title().'">'.$num.'</a>';
				}
			}
			$num++;
		}
		$pagination = $pagination.'</span></div>';
	}
	wp_reset_postdata();

	echo $pagination;
}
?>
