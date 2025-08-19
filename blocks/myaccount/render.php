<?php
/**
 * Dynamic block rendering logic for the My Account block with backend logic.
 *
 * This block will render different content based on the user's login status.
 *
 * @package bazo
 */

$is_logged_in = is_user_logged_in();

// Define nonce values for security
$login_nonce = wp_create_nonce( 'bazo-login-nonce' );
$register_nonce = wp_create_nonce( 'bazo-register-nonce' );
$forgot_password_nonce = wp_create_nonce( 'bazo-forgot-password-nonce' );

// Define attribute variables
$yourprofileurl = $attributes['yourprofileurl'] ?? 'my_account';
$savedeventsurl = $attributes['savedeventsurl'] ?? 'save_event';

?>
<div <?php echo get_block_wrapper_attributes(); ?>>
    <?php if ( $is_logged_in ) : ?>
        <button id="open-my-account-modal-btn" class="my-account-button">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_2004_200)">
                    <path d="M21.2448 0C21.6958 0.116886 22.2052 0.108537 22.6811 0.16698C30.9734 1.2607 37.8127 7.62263 39.5496 15.7963L39.9922 18.827C39.9588 19.6034 40.034 20.3966 39.9922 21.173C38.9985 38.1215 18.2553 46.2868 6.02144 34.331C-6.36269 22.225 1.57885 1.06032 18.7396 0H21.2365H21.2448ZM18.8816 1.26905C5.0277 2.0956 -3.22282 17.3074 3.75839 29.3968C10.8315 41.6364 28.5434 41.8785 35.9588 29.8393C43.9004 16.9317 33.9296 0.384053 18.8816 1.26905Z" fill="#8D8D8E"/>
                    <path d="M19.0392 20.0209C23.1144 19.7287 27.4567 21.3818 29.5027 25.0553C30.3962 26.65 31.749 30.9914 28.8597 31.2503H11.1227C8.85131 30.9079 9.25214 28.5285 9.71978 26.8754C10.8805 22.7593 14.9223 20.3131 19.0392 20.0209ZM19.1144 21.2732C15.3816 21.5487 11.7156 23.7779 10.8221 27.5934C10.6968 28.1361 10.3127 29.7558 10.9974 29.9645H28.9682C29.653 29.7474 29.2772 28.1361 29.1436 27.5934C28.0997 23.1183 23.3983 20.956 19.106 21.2732H19.1144Z" fill="#8D8D8E"/>
                    <path d="M19.4319 8.77479C26.2211 8.08183 26.9142 18.2509 20.4256 18.7518C13.9371 19.2528 13.0854 9.42601 19.4319 8.77479ZM19.5071 10.0188C14.3213 10.645 15.8494 18.8353 21.052 17.3409C25.411 16.0885 24.0833 9.46776 19.5071 10.0188Z" fill="#8D8D8E"/>
                </g>
                <defs>
                <clipPath id="clip0_2004_200">
                <rect width="40" height="40" fill="white"/>
                </clipPath>
                </defs>
            </svg>
        </button>
    <?php else : ?>
        <button id="open-login-modal" class="login-button">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_2004_200)">
                    <path d="M21.2448 0C21.6958 0.116886 22.2052 0.108537 22.6811 0.16698C30.9734 1.2607 37.8127 7.62263 39.5496 15.7963L39.9922 18.827C39.9588 19.6034 40.034 20.3966 39.9922 21.173C38.9985 38.1215 18.2553 46.2868 6.02144 34.331C-6.36269 22.225 1.57885 1.06032 18.7396 0H21.2365H21.2448ZM18.8816 1.26905C5.0277 2.0956 -3.22282 17.3074 3.75839 29.3968C10.8315 41.6364 28.5434 41.8785 35.9588 29.8393C43.9004 16.9317 33.9296 0.384053 18.8816 1.26905Z" fill="#8D8D8E"/>
                    <path d="M19.0392 20.0209C23.1144 19.7287 27.4567 21.3818 29.5027 25.0553C30.3962 26.65 31.749 30.9914 28.8597 31.2503H11.1227C8.85131 30.9079 9.25214 28.5285 9.71978 26.8754C10.8805 22.7593 14.9223 20.3131 19.0392 20.0209ZM19.1144 21.2732C15.3816 21.5487 11.7156 23.7779 10.8221 27.5934C10.6968 28.1361 10.3127 29.7558 10.9974 29.9645H28.9682C29.653 29.7474 29.2772 28.1361 29.1436 27.5934C28.0997 23.1183 23.3983 20.956 19.106 21.2732H19.1144Z" fill="#8D8D8E"/>
                    <path d="M19.4319 8.77479C26.2211 8.08183 26.9142 18.2509 20.4256 18.7518C13.9371 19.2528 13.0854 9.42601 19.4319 8.77479ZM19.5071 10.0188C14.3213 10.645 15.8494 18.8353 21.052 17.3409C25.411 16.0885 24.0833 9.46776 19.5071 10.0188Z" fill="#8D8D8E"/>
                </g>
                <defs>
                <clipPath id="clip0_2004_200">
                <rect width="40" height="40" fill="white"/>
                </clipPath>
                </defs>
            </svg>
        </button>
    <?php endif; ?>

    <div id="auth-modal" class="modal-container auth-modal-container">
        <div class="modal-content">
            <button class="close-modal">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6L6 18"/><path d="m6 6 12 12"/></svg>
            </button>
            
            <div id="login-form-wrapper" class="form-wrapper">
                <div class="text-center">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/login.png" alt="BAZO Logo" class="mx-auto mb-4 rounded-full">
                    <p><?php echo esc_html__( 'enjoy the journey', 'bazo' ); ?></p>
                    <h2 class="form-title"><?php echo esc_html__( 'Login', 'bazo' ); ?></h2>
                    <div id="login-message" class="form-message"></div>
                </div>
                
                <form id="login-form">
                    <input type="email" name="user_email" placeholder="<?php echo esc_attr__( 'E-mail', 'bazo' ); ?>" class="input-field" required>
                    <input type="password" name="user_password" placeholder="<?php echo esc_attr__( 'Password', 'bazo' ); ?>" class="input-field" required>
                    <input type="hidden" name="action" value="bazo_handle_login">
                    <input type="hidden" name="security" value="<?php echo esc_attr( $login_nonce ); ?>">
                    <button type="submit" class="submit-button black-button"><?php echo esc_html__( 'Login', 'bazo' ); ?></button>
                </form>
                
                <div class="text-center text-sm">
                    <a href="#" id="show-forgot-password" class="forgot-password-link"><?php echo esc_html__( 'Forgot password?', 'bazo' ); ?></a>
                </div>
                
                <div class="text-center bazo_sign_up">
                    <?php echo esc_html__( "Don't have an account?", 'bazo' ); ?> <a href="#" id="show-signup" class="toggle-link"><?php echo esc_html__( 'Sign up', 'bazo' ); ?></a>
                </div>
            </div>
            
            <div id="signup-form-wrapper" class="form-wrapper hidden">
                <div class="text-center">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/signup.png" alt="BAZO Logo" class="mx-auto mb-4 rounded-full">
                    <h2 class="form-title"><?php echo esc_html__( 'Create an account', 'bazo' ); ?></h2>
                    <div id="signup-message" class="form-message"></div>
                </div>
                
                <form id="signup-form">
                    <input type="text" name="first_name" placeholder="<?php echo esc_attr__( 'First Name', 'bazo' ); ?>" class="input-field" required>
                    <input type="text" name="last_name" placeholder="<?php echo esc_attr__( 'Last Name', 'bazo' ); ?>" class="input-field" required>
                    <input type="email" name="user_email" placeholder="<?php echo esc_attr__( 'Email', 'bazo' ); ?>" class="input-field" required>
                    <input type="password" name="user_password" placeholder="<?php echo esc_attr__( 'Password', 'bazo' ); ?>" class="input-field" required>
                    <input type="password" name="confirm_password" placeholder="<?php echo esc_attr__( 'Confirm Password', 'bazo' ); ?>" class="input-field" required>
                    <input type="hidden" name="action" value="bazo_handle_register">
                    <input type="hidden" name="security" value="<?php echo esc_attr( $register_nonce ); ?>">
                    <label class="terms-conditions-label">
                        <input type="checkbox" name="terms" class="terms-conditions-checkbox" required>
                        <span><?php echo esc_html__( 'I agree to the general terms & conditions.', 'bazo' ); ?></span>
                    </label>
                    <button type="submit" class="submit-button black-button"><?php echo esc_html__( 'Signup', 'bazo' ); ?></button>
                </form>
                
                <div class="text-center bazo_sign_up">
                    <?php echo esc_html__( 'Already have an account?', 'bazo' ); ?> <a href="#" id="show-login" class="toggle-link"><?php echo esc_html__( 'Login', 'bazo' ); ?></a>
                </div>
            </div>
        </div>
    </div>

    <div id="forgot-password-modal" class="modal-container forgot-password-modal">
        <div class="modal-content">
            <button class="close-modal">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6L6 18"/><path d="m6 6 12 12"/></svg>
            </button>
            <div id="forgot-password-wrapper" class="form-wrapper">
                <div class="text-center">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/forget.png" alt="BAZO Logo" class="mx-auto mb-4">
                    <h2 class="form-title"><?php echo esc_html__( 'Forgot Password?', 'bazo' ); ?></h2>
                    <p class="bazo_forgot_text mt-2"><?php echo esc_html__( 'Enter your email to receive a reset link.', 'bazo' ); ?></p>
                    <div id="forgot-password-message" class="form-message"></div>
                </div>
                <form id="forgot-password-form">
                    <input type="email" name="user_email" placeholder="<?php echo esc_attr__( 'E-mail', 'bazo' ); ?>" class="input-field" required>
                    <input type="hidden" name="action" value="bazo_handle_forgot_password">
                    <input type="hidden" name="security" value="<?php echo esc_attr( $forgot_password_nonce ); ?>">
                    <button type="submit" class="submit-button black-button"><?php echo esc_html__( 'Send Reset Link', 'bazo' ); ?></button>
                </form>
                <div class="text-center bazo_sign_up">
                    <a href="#" id="forgot-to-login" class="toggle-link"><?php echo esc_html__( 'Back to login', 'bazo' ); ?></a>
                </div>
            </div>
        </div>
    </div>

    <div id="my-account-modal" class="modal-container my-account-modal-container">
        <div class="modal-content">
            <button class="close-modal">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6L6 18"/><path d="m6 6 12 12"/></svg>
            </button>
            <div class="my-account-menu">
                <ul>
                    <li>
                        <a href="<?php echo esc_url( get_home_url( null, $yourprofileurl ) ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span><?php echo esc_html__( 'your profile', 'bazo' ); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url( get_home_url( null, $savedeventsurl ) ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/>
                        </svg>
                        <span><?php echo esc_html__( 'saved events', 'bazo' ); ?></span>
                        </a>
                    </li>
                    <li class="logout-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v10"/><path d="M18.4 8.6a8 8 0 1 1-12.8 0"/>
                        </svg>
                        <span><?php echo esc_html__( 'log out', 'bazo' ); ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>