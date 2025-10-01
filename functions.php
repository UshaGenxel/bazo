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
require_once "include/class-bazo-common.php";

// Change the button URL
add_filter( 'woocommerce_return_to_shop_redirect', function () {
    return home_url();
});












// a code delete karvano che get_google_places_textsearch and google_places
function get_google_places_textsearch($query) {
    $api_key = 'AIzaSyAYQ-yVy65uSRag9I0vYU3_J0UA5xjNnsI'; // Replace with your key

    // Build URL
    $url = add_query_arg( array(
        'query' => $query,
        'key'   => $api_key,
    ), 'https://maps.googleapis.com/maps/api/place/textsearch/json' );

    // Fetch data
    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        return 'Error fetching data';
    }

	echo "<pre>";
	print_r($response);
	echo "</pre>";

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!$data || $data['status'] !== 'OK') {
        return 'Invalid API response';
    }

    $output = '<div class="google-places-list">';
    foreach ($data['results'] as $place) {
        $name    = $place['name'];
        $address = isset($place['formatted_address']) ? $place['formatted_address'] : '';
        $status  = isset($place['opening_hours']['open_now']) ? 
                      ($place['opening_hours']['open_now'] ? 'Open Now' : 'Closed') 
                      : 'Hours not available';

        $output .= "<div class='google-place'>
                        <h3>{$name}</h3>
                        <p><strong>Address:</strong> {$address}</p>
                        <p><strong>Status:</strong> {$status}</p>
                    </div>";
    }
    $output .= '</div>';

    return $output;
}

// Shortcode
add_shortcode('google_places', function($atts) {
    $atts = shortcode_atts(['q' => 'coffee shops Munich'], $atts, 'google_places');
    return get_google_places_textsearch($atts['q']);
});


function get_google_places_data($query = 'specialty coffee Munich') {
    $api_key = 'AIzaSyAYQ-yVy65uSRag9I0vYU3_J0UA5xjNnsI'; // Replace with your key
    $url = add_query_arg( array(
        'query' => $query,
        'key'   => $api_key,
    ), 'https://maps.googleapis.com/maps/api/place/textsearch/json' );

    $response = wp_remote_get( $url );

   
    if ( is_wp_error( $response ) ) {
        return []; // return empty if request fails
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    echo"<pre>";
    print_r($body);
    echo"</pre>";
    $results = [];

    if ( ! empty( $data['results'] ) ) {
        foreach ( $data['results'] as $place ) {
            $results[] = [
                'name'      => $place['name'] ?? '',
                'address'   => $place['formatted_address'] ?? '',
                'open_now'  => $place['opening_hours']['open_now'] ?? 'unknown',
                'place_id'  => $place['place_id'] ?? '',
            ];
        }
    }

    return $results;
}


function get_place_details($place_id) {
    $api_key = 'AIzaSyAYQ-yVy65uSRag9I0vYU3_J0UA5xjNnsI';
    $url = add_query_arg( array(
        'place_id' => $place_id,
        'fields'   => 'name,formatted_address,opening_hours',
        'key'      => $api_key,
    ), 'https://maps.googleapis.com/maps/api/place/details/json' );

    $response = wp_remote_get( $url );

    if ( is_wp_error( $response ) ) {
        return [];
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    return $data['result'] ?? [];
}

function cafes_shortcode() {
    $cafes = get_google_places_data('specialty coffee Munich Glockenbachviertel');

    if ( empty( $cafes ) ) {
        return '<p>No cafés found.</p>';
    }

    $output = '<ul class="cafes-list">';
    foreach ( $cafes as $cafe ) {
        $details = get_place_details($cafe['place_id']);
        $hours   = $details['opening_hours']['weekday_text'] ?? [];

        $output .= '<li>';
        $output .= '<strong>' . esc_html( $cafe['name'] ) . '</strong><br>';
        $output .= esc_html( $cafe['address'] ) . '<br>';
        if ( ! empty( $hours ) ) {
            $output .= implode('<br>', array_map('esc_html', $hours));
        }
        $output .= '</li>';
    }
    $output .= '</ul>';

    return $output;
}
add_shortcode('cafes', 'cafes_shortcode');

