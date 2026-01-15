<?php
/*
Plugin Name: Winter Snow Effect
Description: Automatically adds a falling snow effect to your website only during winter months (December, January, February).
Version: 1.1
Author: Sarangan Thillaiampalam
Author URI: https://sarangan.dk
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check if the current month is a winter month (Dec, Jan, Feb).
 *
 * @return bool True if winter, false otherwise.
 */
function wse_is_winter() {
	$current_month = (int) gmdate( 'n' );
	// 12 = December, 1 = January, 2 = February
	return in_array( $current_month, array( 12, 1, 2 ), true );
}

/**
 * Enqueue scripts and styles only if it is winter.
 */
function wse_enqueue_scripts() {
	if ( wse_is_winter() ) {
		wp_enqueue_style( 'wse-snow-style', plugin_dir_url( __FILE__ ) . 'assets/css/snow.css', array(), '1.1' );
		wp_enqueue_script( 'wse-snow-script', plugin_dir_url( __FILE__ ) . 'assets/js/snow.js', array(), '1.1', true );
	}
}
add_action( 'wp_enqueue_scripts', 'wse_enqueue_scripts' );
