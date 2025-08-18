<?php
/**
 * Bazo Myaccount Setup Class.
 *
 * @package Bazo
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class Bazo_Myaccount_Block
 */
class Bazo_Myaccount_Block {
    /**
     * Constructor. Hooks all theme setup functions.
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'wp_ajax_nopriv_bazo_handle_login', [ $this, 'handle_login' ] );
        add_action( 'wp_ajax_nopriv_bazo_handle_register', [ $this, 'handle_register' ] );
        add_action( 'wp_ajax_nopriv_bazo_handle_forgot_password', [ $this, 'handle_forgot_password' ] );
        
        add_action('wp_ajax_bazo_profile_update', [$this, 'handle_profile_update']);
        add_action('wp_ajax_nopriv_bazo_profile_update', [$this, 'handle_nopriv_profile_update']);
    }

    /**
     * Enqueue scripts and styles for the block.
     */
    public function enqueue_assets() {
        // Enqueue the block's viewScript
        wp_enqueue_script(
            'bazo-myaccount-view-script',
            get_template_directory_uri() . '/blocks/myaccount/view.js',
            array(),
            '1.0.0',
            true
        );
        // Localize the script to expose the AJAX URL and the logout URL
        wp_localize_script(
            'bazo-myaccount-view-script',
            'bazo_myaccount_ajax',
            array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'logoutUrl' => wp_logout_url( home_url() )
            )
        );
    }

    /**
     * Handle user login via AJAX.
     */
    public function handle_login() {
        check_ajax_referer( 'bazo-login-nonce', 'security' );

        $creds = array();
        $creds['user_login'] = sanitize_user( $_POST['user_email'] );
        $creds['user_password'] = $_POST['user_password'];
        $creds['remember'] = true;

        $user = wp_signon( $creds, false );

        if ( is_wp_error( $user ) ) {
            wp_send_json_error( $user->get_error_message() );
        } else {
            wp_send_json_success( array( 'message' => __( 'Login successful! Reloading...', 'bazo' ) ) );
        }
        
        wp_die();
    }

    /**
     * Handle user registration via AJAX.
     */
    public function handle_register() {
        check_ajax_referer( 'bazo-register-nonce', 'security' );
        
        $user_email = sanitize_email( $_POST['user_email'] );
        $user_password = $_POST['user_password'];
        $first_name = sanitize_text_field( $_POST['first_name'] );
        $last_name = sanitize_text_field( $_POST['last_name'] );
        
        // Check if user already exists
        if ( username_exists( $user_email ) || email_exists( $user_email ) ) {
            wp_send_json_error( __( 'This email is already registered. Please login.', 'bazo' ) );
        }

        $userdata = array(
            'user_login' => $user_email,
            'user_pass'  => $user_password,
            'user_email' => $user_email,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'role'       => 'subscriber' // Or any other role
        );

        $user_id = wp_insert_user( $userdata );

        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( $user_id->get_error_message() );
        } else {
            // Log the user in after successful registration
            wp_set_current_user( $user_id );
            wp_set_auth_cookie( $user_id, true );
            wp_send_json_success( array( 'message' => __( 'Registration successful! Reloading...', 'bazo' ) ) );
        }
        
        wp_die();
    }

    /**
     * Handle password recovery via AJAX.
     */
    public function handle_forgot_password() {
        check_ajax_referer( 'bazo-forgot-password-nonce', 'security' );
        
        $user_email = sanitize_email( $_POST['user_email'] );
        
        if ( empty( $user_email ) || ! is_email( $user_email ) ) {
            wp_send_json_error( __( 'Please enter a valid email address.', 'bazo' ) );
        }

        if ( ! email_exists( $user_email ) ) {
            wp_send_json_error( __( 'There is no user registered with that email address.', 'bazo' ) );
        }

        $user = get_user_by( 'email', $user_email );
        $reset_key = get_password_reset_key( $user );
        
        if ( is_wp_error( $reset_key ) ) {
            wp_send_json_error( __( 'Could not generate a password reset key. Please try again.', 'bazo' ) );
        }

        $reset_link = network_site_url( "wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode( $user->user_login ), 'login' );
        
        $message = "To reset your password, please visit the following address:\n\n$reset_link\n";

        // You may want to use a more robust email sending function or a custom email template here.
        if ( wp_mail( $user_email, __( 'Password Reset Request', 'bazo' ), $message ) ) {
            wp_send_json_success( array( 'message' => __( 'A password reset link has been sent to your email.', 'bazo' ) ) );
        } else {
            wp_send_json_error( __( 'An error occurred while sending the email. Please check your mail server configuration.', 'bazo' ) );
        }
        
        wp_die();
    }

    public function handle_profile_update() {
        // Fix: Use the correct nonce name that matches render.php
        check_ajax_referer('bazo_profile_update', 'security');

        if (!is_user_logged_in()) {
            wp_send_json_error(['You must be logged in to update your profile']);
        }

        $user_id = get_current_user_id();
        $errors = [];

        // Sanitize input data
        $data = [
            'first_name' => sanitize_text_field($_POST['first_name'] ?? ''),
            'last_name' => sanitize_text_field($_POST['last_name'] ?? ''),
            'user_email' => sanitize_email($_POST['user_email'] ?? ''),
            'description' => sanitize_textarea_field($_POST['description'] ?? ''),
            'billing_country' => sanitize_text_field($_POST['billing_country'] ?? ''),
            'billing_state' => sanitize_text_field($_POST['billing_state'] ?? ''),
            'billing_phone' => sanitize_text_field($_POST['billing_phone'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? ''
        ];

        // Validate required fields
        if (empty($data['first_name'])) $errors[] = 'First name is required';
        if (empty($data['last_name'])) $errors[] = 'Last name is required';
        
        // Only require phone if WooCommerce is active
        if (class_exists('WooCommerce') && empty($data['billing_phone'])) {
            $errors[] = 'Phone number is required';
        }

        // Validate email
        if (!is_email($data['user_email'])) {
            $errors[] = 'Please enter a valid email address';
        } elseif (email_exists($data['user_email']) && $data['user_email'] !== wp_get_current_user()->user_email) {
            $errors[] = 'This email is already registered';
        }

        // Validate password
        if (!empty($data['password'])) {
            if ($data['password'] !== $data['password_confirm']) {
                $errors[] = 'Passwords do not match';
            } elseif (strlen($data['password']) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            }
        }

        if (!empty($errors)) {
            wp_send_json_error($errors);
        }

        // Prepare user data
        $userdata = [
            'ID' => $user_id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'user_email' => $data['user_email']
        ];

        // Update password if provided
        if (!empty($data['password'])) {
            $userdata['user_pass'] = $data['password'];
        }

        // Update user
        $result = wp_update_user($userdata);

        if (is_wp_error($result)) {
            wp_send_json_error([$result->get_error_message()]);
        }

        // Update user meta
        update_user_meta($user_id, 'description', $data['description']);
        
        // Handle profile image upload if provided
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_image'];
            
            // Validate file type
            $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
            if (!in_array($file['type'], $allowed_types)) {
                wp_send_json_error(['Invalid file type. Only JPEG, PNG, and GIF images are allowed']);
            }
            
            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                wp_send_json_error(['File size too large. Maximum size is 5MB']);
            }
            
            // Handle file upload
            if (!function_exists('wp_handle_upload')) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }
            
            $upload_overrides = ['test_form' => false];
            $movefile = wp_handle_upload($file, $upload_overrides);
            
            if ($movefile && !isset($movefile['error'])) {
                // Process the image and set as avatar
                $attachment_id = wp_insert_attachment([
                    'post_mime_type' => $movefile['type'],
                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($movefile['file'])),
                    'post_content' => '',
                    'post_status' => 'inherit',
                    'post_author' => $user_id
                ], $movefile['file']);
                
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attachment_data = wp_generate_attachment_metadata($attachment_id, $movefile['file']);
                wp_update_attachment_metadata($attachment_id, $attachment_data);
                
                // Update user meta with the new avatar
                update_user_meta($user_id, 'profile_image_id', $attachment_id);
            }
        }
        
        // Update WooCommerce fields only if WooCommerce is active
        if (class_exists('WooCommerce')) {
            update_user_meta($user_id, 'billing_country', $data['billing_country']);
            update_user_meta($user_id, 'billing_state', $data['billing_state']);
            update_user_meta($user_id, 'billing_phone', $data['billing_phone']);
            
            // Also update shipping fields if they match billing
            if (get_user_meta($user_id, 'ship_to_different_address', true) != '1') {
                update_user_meta($user_id, 'shipping_country', $data['billing_country']);
                update_user_meta($user_id, 'shipping_state', $data['billing_state']);
            }
        }

        // Prepare success message
        $message = 'Profile updated successfully';
        $image_url = '';
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            // Get the image URL for frontend preview update
            $image_url = wp_get_attachment_image_url($attachment_id, 'medium');
        }

        wp_send_json_success(['message' => $message, 'image_url' => $image_url]);
    }

    public function handle_nopriv_profile_update() {
        wp_send_json_error(['You must be logged in to update your profile']);
    }
}
new Bazo_Myaccount_Block();