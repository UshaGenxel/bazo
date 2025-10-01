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

        // Email verification handler
        add_action( 'init', [ $this, 'maybe_handle_email_verification' ] );
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

        $user_email = sanitize_email( $_POST['user_email'] );
        $user_password = $_POST['user_password'];

        // Validate email
        if ( empty( $user_email ) || ! is_email( $user_email ) ) {
            wp_send_json_error( __( 'Please enter a valid email address.', 'bazo' ) );
        }

        // Check if user exists
        $user = get_user_by( 'email', $user_email );
        if ( ! $user ) {
            wp_send_json_error( __( 'Email address not found. Please check your email or create an account.', 'bazo' ) );
        }

        // Block login until email is verified
        $is_verified = (int) get_user_meta( $user->ID, 'bazo_email_verified', true ) === 1;
        if ( ! $is_verified ) {
            wp_send_json_error( __( 'Please verify your email to activate your account. We have sent you a verification link.', 'bazo' ) );
        }

        // Attempt to authenticate
        $creds = array();
        $creds['user_login'] = $user_email;
        $creds['user_password'] = $user_password;
        $creds['remember'] = true;

        $authenticated_user = wp_signon( $creds, false );

        if ( is_wp_error( $authenticated_user ) ) {
            // Check for specific error types and provide clean messages
            $error_code = $authenticated_user->get_error_code();
            
            if ( $error_code === 'invalid_username' || $error_code === 'invalid_email' ) {
                wp_send_json_error( __( 'Email address not found. Please check your email or create an account.', 'bazo' ) );
            } elseif ( $error_code === 'incorrect_password' ) {
                wp_send_json_error( __( 'The password you entered is incorrect. Please try again.', 'bazo' ) );
            } else {
                wp_send_json_error( __( 'Login failed. Please check your credentials and try again.', 'bazo' ) );
            }
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
            // Create email verification token and send email
            $verification_key = wp_generate_password( 20, false );
            update_user_meta( $user_id, 'bazo_email_verification_key', $verification_key );
            update_user_meta( $user_id, 'bazo_email_verified', 0 );

            $this->send_verification_email( $user_id, $user_email, $first_name );

            // Do not auto-login; ask user to verify email
            wp_send_json_success( array( 'message' => __( 'We just emailed you. Simply click the verification link in your inbox to activate your account. If you don’t see it, check your spam folder just in case.', 'bazo' ) ) );
        }
        
        wp_die();
    }

    /**
     * Send account verification email to a new user.
     */
    private function send_verification_email( $user_id, $user_email, $first_name ) {
        $site_name = get_bloginfo( 'name' );
        $subject = __( 'Almost there – confirm your account!', 'bazo' );

        $verification_key = get_user_meta( $user_id, 'bazo_email_verification_key', true );
        $verify_url = add_query_arg(
            array(
                'bazo_verify' => 1,
                'uid' => $user_id,
                'key' => $verification_key,
            ),
            home_url( '/' )
        );

        $display_name = trim( $first_name ) !== '' ? $first_name : get_userdata( $user_id )->display_name;
        $logo_url = get_template_directory_uri() . '/assets/images/forget.png';

        // HTML email template
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Verify Your Account</title>
            <style>
                body { 
                    font-family: Arial, Helvetica, sans-serif; 
                    margin: 0; 
                    padding: 20px; 
                    background-color: #ffffff; 
                    color: #000000; 
                }
                .email-container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    text-align: center; 
                    background-color: #ffffff; 
                }
                .header { 
                    margin-bottom: 30px; 
                }
                .website-url a { 
                    display: flex;
                    justify-content: end;
                    align-items: center;
                    width: 100%;
                    font-size: 12px; 
                    color: #000000; 
                    text-decoration: none;
                }
                .website-url a:hover {
                    color: #8d8d8e;
                    text-decoration: underline;
                }
                .logo { 
                    margin: 20px 0; 
                }
                .logo img { 
                    max-width: 150px; 
                    height: auto; 
                }
                .tagline { 
                    font-size: 14px; 
                    color: #666666; 
                    margin-bottom: 30px; 
                }
                .main-heading { 
                    font-size: 24px; 
                    font-weight: bold; 
                    margin-bottom: 20px; 
                    color: #000000; 
                }
                .greeting { 
                    font-size: 16px; 
                    margin-bottom: 15px; 
                    color: #000000; 
                }
                .main-message { 
                    font-size: 16px; 
                    margin-bottom: 25px; 
                    color: #000000; 
                    line-height: 1.5; 
                }
                .cta-button { 
                    display: inline-block; 
                    background-color: #000000; 
                    color: #ffffff; 
                    padding: 15px 30px; 
                    text-decoration: none; 
                    border-radius: 50px; 
                    font-size: 16px; 
                    font-weight: bold; 
                    margin: 20px 0; 
                    border: 1px solid #000000;
                }
                .cta-button:hover {
                    background-color: #ffffff;
                    color: #000000;
                }
                .disclaimer { 
                    font-size: 14px; 
                    margin: 20px 0; 
                    color: #000000; 
                }
                .closing { 
                    font-size: 16px; 
                    margin-top: 30px; 
                    color: #000000; 
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="header">
                    <div class="website-url"><a href="'. esc_url(home_url()).'">www.bazo.studio</a></div>
                </div>
                
                <div class="logo">
                    <img src="' . esc_url( $logo_url ) . '" alt="BAZO" />
                </div>
                
                <div class="tagline">enjoy the journey</div>
                
                <div class="main-heading">Almost there – confirm your account!</div>
                
                <div class="greeting">Hi ' . esc_html( $display_name ) . ',</div>
                
                <div class="main-message">awesome, you signed up at ' . esc_html( $site_name ) . '!<br>Click the link below to activate your account:</div>
                
                <a href="' . esc_url( $verify_url ) . '" class="cta-button">confirm your account</a>
                
                <div class="disclaimer">If you didn\'t sign up, just ignore this email – no worries.</div>
                
                <div class="closing">Cheers,<br>Your ' . esc_html( $site_name ) . ' Team</div>
            </div>
        </body>
        </html>';

        // Set headers for HTML email
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <noreply@' . parse_url( home_url(), PHP_URL_HOST ) . '>'
        );

        wp_mail( $user_email, $subject, $message, $headers );
    }

    /**
     * Handle email verification link clicks.
     */
    public function maybe_handle_email_verification() {
        if ( isset( $_GET['bazo_verify'], $_GET['uid'], $_GET['key'] ) && (int) $_GET['bazo_verify'] === 1 ) {
            $user_id = absint( $_GET['uid'] );
            $key     = sanitize_text_field( wp_unslash( $_GET['key'] ) );

            if ( $user_id && $key ) {
                $saved_key = get_user_meta( $user_id, 'bazo_email_verification_key', true );
                if ( hash_equals( (string) $saved_key, (string) $key ) ) {
                    update_user_meta( $user_id, 'bazo_email_verified', 1 );
                    delete_user_meta( $user_id, 'bazo_email_verification_key' );

                    // Optionally log user in after verification
                    wp_set_current_user( $user_id );
                    wp_set_auth_cookie( $user_id, true );

                    // Redirect to home or profile page
                    wp_safe_redirect( home_url( '/' ) );
                    exit;
                }
            }

            // On failure, just redirect home silently
            wp_safe_redirect( home_url( '/' ) );
            exit;
        }
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
        
        // Generate a new random password
        $new_password = wp_generate_password( 12, false );
        
        // Update the user's password
        $update_result = wp_update_user( array(
            'ID' => $user->ID,
            'user_pass' => $new_password
        ) );
        
        if ( is_wp_error( $update_result ) ) {
            wp_send_json_error( __( 'Could not update password. Please try again.', 'bazo' ) );
        }

        // Prepare email content
        $site_name = get_bloginfo( 'name' );
        $subject = sprintf( __( 'Your new password for %s', 'bazo' ), $site_name );
        
        $message = sprintf(
            __( 'Hello %s,

Your password has been reset successfully.

Your new password is: %s

Please login with this new password and change it to something you can remember.

Best regards,
%s Team', 'bazo' ),
            $user->display_name,
            $new_password,
            $site_name
        );

        // Send the email with the new password
        if ( wp_mail( $user_email, $subject, $message ) ) {
            wp_send_json_success( array( 'message' => __( 'A new password has been sent to your email address.', 'bazo' ) ) );
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