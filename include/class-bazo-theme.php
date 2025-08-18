<?php
/**
 * Bazo Theme Setup Class.
 *
 * @package Bazo
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class Bazo_Theme
 */
class Bazo_Theme {

	/**
	 * Constructor. Hooks all theme setup functions.
	 */
	public function __construct() {
		add_action( 'after_setup_theme', [ $this, 'bazo_register_menus' ] );
		add_action( 'init', [ $this, 'register_all_blocks' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest_fields' ] ); // Corrected
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'enqueue_block_assets', [ $this, 'load_editor_assets' ] );
	}
	
	public function register_all_blocks() {
		$blocks_dir = get_template_directory() . '/build/';
		foreach ( glob( $blocks_dir . '*', GLOB_ONLYDIR ) as $block_path ) :
			$block_json = $block_path . '/block.json';
			if ( file_exists( $block_json ) ) :
				register_block_type( $block_path );
			endif;
		endforeach;
	}

	// Register navigation menus
	public function bazo_register_menus() {
		register_nav_menus( array(
			'primary'   => __( 'Primary Menu', 'bazo' ),
			'footer'    => __( 'Footer Menu', 'bazo' ),
		));
	}

    public function register_rest_fields() {
        register_rest_field('product', 'featured_media_url', [
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

		register_rest_field('product', 'event_date', [
			'get_callback' => function ($post_arr) {
				return get_field('event_date', $post_arr['id']);
			},
			'schema' => null,
		]);

		register_rest_field('product', 'event_time', [
			'get_callback' => function ($post_arr) {
				return get_field('event_time', $post_arr['id']);
			},
			'schema' => null,
		]);

		register_rest_field('product', 'taxonomy_terms', [
			'get_callback' => function ($post_arr) {
				$terms = get_the_terms($post_arr['id'], 'product_cat');
				if (is_wp_error($terms) || !$terms) return [];
				return array_map(function ($term) {
					return [
						'id' => $term->term_id,
						'name' => $term->name,
						'slug' => $term->slug,
					];
				}, $terms);
			},
			'schema' => null,
		]);

    }

	public function enqueue_scripts() {
		$theme_version 			= wp_get_theme()->get( 'Version' );
		wp_enqueue_style( 'main', get_theme_file_uri( '/assets/css/main.css' ), array(), $theme_version, 'all' );
	}

	public function load_editor_assets() {
		if ( is_admin() ) :
			$theme_version 			= wp_get_theme()->get( 'Version' );
			wp_enqueue_style( 'main', get_theme_file_uri( '/assets/css/main.css' ), array(), $theme_version, 'all' );
		endif;
	}

}

// Instantiate the theme class.
new Bazo_Theme();
