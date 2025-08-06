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
}

add_action( 'wp_enqueue_scripts', 'bazo_styles' );

function register_all_blocks() {
	$blocks_dir = get_template_directory() . '/build/';
	foreach ( glob( $blocks_dir . '*', GLOB_ONLYDIR ) as $block_path ) :
		$block_json = $block_path . '/block.json';
		if ( file_exists( $block_json ) ) :
			register_block_type( $block_path );
		endif;
	endforeach;
}
add_action('init', 'register_all_blocks');

// Register navigation menus
function bazo_register_menus() {
    register_nav_menus( array(
        'primary'   => __( 'Primary Menu', 'bazo' ),
        'footer'    => __( 'Footer Menu', 'bazo' ),
    ));
}
add_action( 'after_setup_theme', 'bazo_register_menus' );

add_action('rest_api_init', function () {
    register_rest_field('post', 'featured_media_url', [
        'get_callback' => function ($post_arr) {
            $img_id = $post_arr['featured_media'];
            if ($img_id) {
                $img = wp_get_attachment_image_src($img_id, 'large');
                return $img ? $img[0] : '';
            }
            return '';
        },
        'schema' => null,
    ]);
    register_rest_field('post', 'event_date', [
        'get_callback' => function ($post_arr) {
            return get_field('event_date', $post_arr['id']);
        },
        'schema' => null,
    ]);
});
