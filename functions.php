<?php
/**
 * Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package bazo
 * @since 1.0.0
 */

/**
 * Enqueue the CSS files.
 *
 * @since 1.0.0
 * 
 * @return void
 */
function bazo_styles() {
	wp_enqueue_style(
		'bazo-style',
		get_stylesheet_uri(),
		[],
		wp_get_theme()->get( 'Version' )
	);

	// Enqueue Google Font - Poppins
	wp_enqueue_style( 'bazo-google-fonts', 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap', array(), null );
}

add_action( 'wp_enqueue_scripts', 'bazo_styles' );

require_once "include/class-bazo-theme.php";
require_once "include/class-bazo-myaccount-block.php";

