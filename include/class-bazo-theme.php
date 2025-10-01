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
		// Ensure Theme File Editor support in admin when allowed
		add_action( 'admin_menu', [ $this, 'ensure_theme_file_editor_menu' ], 11 );
		add_action( 'admin_notices', [ $this, 'maybe_show_file_editor_disabled_notice' ] );
		add_filter( 'wp_theme_editor_filetypes', [ $this, 'extend_theme_editor_filetypes' ] );

		add_filter( 'gettext', [ $this, 'translate_wishlist_text' ], 10, 3 );

		add_filter('nav_menu_css_class', [ $this, 'bazo_add_nav_menu_active_classes' ], 10, 3);
		add_filter('render_block', [ $this, 'bazo_navigation_block_active_classes' ], 10, 2);
		
		// Add custom ordering for events in REST API
		add_filter( 'rest_product_query', [ $this, 'custom_event_ordering' ], 10, 2 );
	}

	/**
	 * Ensure the Appearance > Theme File Editor menu is available when permitted.
	 */
	public function ensure_theme_file_editor_menu() {
		if ( is_multisite() ) {
			return;
		}
		if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) {
			return;
		}
		if ( ! current_user_can( 'edit_themes' ) ) {
			return;
		}

		global $submenu;
		$has_theme_editor = false;
		if ( isset( $submenu['themes.php'] ) ) {
			foreach ( $submenu['themes.php'] as $item ) {
				if ( isset( $item[2] ) && $item[2] === 'theme-editor.php' ) {
					$has_theme_editor = true;
					break;
				}
			}
		}

		if ( ! $has_theme_editor ) {
			add_submenu_page(
				'themes.php',
				__( 'Theme File Editor', 'bazo' ),
				__( 'Theme File Editor', 'bazo' ),
				'edit_themes',
				'theme-editor.php'
			);
		}
	}

	/**
	 * Show an admin notice if file editing is disabled globally.
	 */
	public function maybe_show_file_editor_disabled_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'Theme File Editor is disabled by DISALLOW_FILE_EDIT in wp-config.php. Set it to false or remove it to enable editing.', 'bazo' ) . '</p></div>';
		}
	}

	/**
	 * Allow additional file types to be edited in the Theme File Editor.
	 *
	 * @param array $types Existing allowed extensions.
	 * @return array Modified list of allowed extensions.
	 */
	public function extend_theme_editor_filetypes( $types ) {
		$additional = array( 'js', 'json', 'scss', 'sass', 'md', 'txt', 'svg' );
		return array_values( array_unique( array_merge( (array) $types, $additional ) ) );
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

		register_rest_field('product', 'event_end_date', [
			'get_callback' => function ($post_arr) {
				return get_field('event_end_date', $post_arr['id']);
			},
			'schema' => null,
		]);

		register_rest_field('product', 'event_end_time', [
			'get_callback' => function ($post_arr) {
				return get_field('event_end_time', $post_arr['id']);
			},
			'schema' => null,
		]);

		// Expose location address for local type rendering in AJAX
		register_rest_field('product', 'location_address', [
			'get_callback' => function ($post_arr) {
				return get_field('location_address', $post_arr['id']);
			},
			'schema' => null,
		]);

		// Expose direction URL for local type
		register_rest_field('product', 'direction', [
			'get_callback' => function ($post_arr) {
				return get_field('direction', $post_arr['id']);
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

		register_rest_field( 'product', 'wishlist_html', array(
			'get_callback' => function( $object ) {
				if ( ! function_exists( 'do_shortcode' ) ) {
					return '';
				}
				
				if ( wp_get_current_user() ) : 
					return do_shortcode( '[ti_wishlists_addtowishlist product_id="' . $object['id'] . '"]' );
				else :
					return '<div class="bazo-event-card-wishlist-button tinv-wraper woocommerce tinv-wishlist tinvwl-shortcode-add-to-cart tinvwl-the_content" data-tinvwl_product_id="' . $object['id'] . '">
						<img src="'. get_template_directory_uri().'/assets/images/wishlist.svg" alt="Wishlist icon" />
					</div>';
				endif;
			},
			'schema' => array(
				'description' => __( 'TI Wishlist button HTML', 'bazo' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
		));

    }

	/**
	 * Custom ordering for events in REST API
	 * Orders by: 1) Event date (nearest first), 2) Event time, 3) Title (alphabetical)
	 * Also filters out past events
	 */
	public function custom_event_ordering( $args, $request ) {
		// Build meta query based on event_type and date
		$current_date = date('Y-m-d');
		$event_type = $request->get_param('event_type');
		$meta_query = [];
		
		// Apply date filter only for real events
		if ( $event_type === 'event' ) {
			$meta_query[] = [
				'key' => 'event_end_date',
				'value' => $current_date,
				'compare' => '>=',
				'type' => 'DATE',
			];
		}
		
		// Apply ACF event_types radio if provided
		if ( $event_type === 'event' || $event_type === 'local' ) {
			$meta_query[] = [
				'key' => 'event_types',
				'value' => $event_type,
				'compare' => '=',
			];
		}
		
		if ( ! empty( $meta_query ) ) {
			$args['meta_query'] = $meta_query;
		}
		
		// Use simple ordering by event date
		$args['orderby'] = 'meta_value';
		$args['meta_key'] = 'event_date';
		$args['meta_type'] = 'DATE';
		$args['order'] = 'ASC';
		
		return $args;
	}

	public function enqueue_scripts() {
		$theme_version 			= time(); //wp_get_theme()->get( 'Version' );
		wp_enqueue_style( 'main', get_theme_file_uri( '/assets/css/main.css' ), array(), $theme_version, 'all' );
		wp_enqueue_script( 'main', get_theme_file_uri( '/assets/js/main.js' ), array(), $theme_version, true );

		// wp_enqueue_script( 'google-map', 'https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY', [], null, true );
    	//wp_enqueue_script( 'acf-map-init', get_theme_file_uri( '/assets/js/acf-map.js'), ['google-map','jquery'], null, true );
	}

	public function load_editor_assets() {
		if ( is_admin() ) :
			$theme_version 			= time(); //wp_get_theme()->get( 'Version' );
			wp_enqueue_style( 'main', get_theme_file_uri( '/assets/css/main.css' ), array(), $theme_version, 'all' );
		endif;
	}

	
	
	/**
     * Translate wishlist text from "Product Name" to "Event Name"
     */
    public function translate_wishlist_text( $translated, $text, $domain ) {
        
		if ( $domain === 'ti-woocommerce-wishlist' ) {

			if ( $text === 'Product Name' ) {
				$translated = 'Event Name';
			}

			// Empty wishlist message
			if ( $text === 'Your Wishlist is currently empty.' ) {
				return 'Your saved events list is currently empty.';
			}
	
			// Return to shop button
			if ( $text === 'Return To Shop' ) {
				return 'Return To Events';
			}
	
			if ( $text === '%s has been removed from the wishlist.' ) {
				$translated = '%s has been removed from your saved events.';
			}
	
			if ( $text === '%s has not been removed from the wishlist.' ) {
				$translated = '%s could not be removed from your saved events.';
			}
		}
	
        return $translated;
    }


	/**
	 * Add active classes to navigation menu items
	 *
	 * @since 1.0.0
	 * 
	 * @return void
	 */
	public function bazo_add_nav_menu_active_classes($classes, $item, $args) {
		// Check if this is the primary navigation
		if ($args->theme_location === 'primary' || $args->menu_class === 'wp-block-navigation__container') {
			// Check if current page matches menu item
			if (is_page() && $item->object_id == get_queried_object_id()) {
				$classes[] = 'current_page_item';
			}
			
			// Check if current post type matches menu item
			if (is_singular() && $item->object_id == get_queried_object_id()) {
				$classes[] = 'current-menu-item';
			}
			
			// Check if current archive matches menu item
			if (is_archive() && $item->object_id == get_queried_object_id()) {
				$classes[] = 'current-menu-item';
			}
			
			// Check if current post type archive matches menu item
			if (is_post_type_archive() && $item->object_id == get_queried_object_id()) {
				$classes[] = 'current-menu-item';
			}
			
			// Check if current category/tag matches menu item
			if (is_category() || is_tag() || is_tax()) {
				$current_term = get_queried_object();
				if ($item->object_id == $current_term->term_id) {
					$classes[] = 'current-menu-item';
				}
			}
			
			// Check if current search results
			if (is_search() && $item->url && strpos($item->url, 'search') !== false) {
				$classes[] = 'current-menu-item';
			}
			
			// Check if current 404 page
			if (is_404() && $item->url && strpos($item->url, '404') !== false) {
				$classes[] = 'current-menu-item';
			}
		}
		
		return $classes;
	}

	/**
	 * Add active classes to navigation block menu items
	 *
	 * @since 1.0.0
	 * 
	 * @return void
	 */
	public function bazo_navigation_block_active_classes($block_content, $block) {
		if ($block['blockName'] === 'core/navigation') {
			// Add active class to current page menu item
			if (is_page()) {
				$current_page_id = get_queried_object_id();
				$block_content = str_replace(
					'wp-block-navigation-item__content',
					'wp-block-navigation-item__content current_page_item',
					$block_content
				);
			}
			
			// Add active class to current post menu item
			if (is_single()) {
				$current_post_id = get_queried_object_id();
				$block_content = str_replace(
					'wp-block-navigation-item__content',
					'wp-block-navigation-item__content current-menu-item',
					$block_content
				);
			}
			
			// Add active class to current archive menu item
			if (is_archive()) {
				$block_content = str_replace(
					'wp-block-navigation-item__content',
					'wp-block-navigation-item__content current-menu-item',
					$block_content
				);
			}
		}
		
		return $block_content;
	}
	
}

// Instantiate the theme class.
new Bazo_Theme();
