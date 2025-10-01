<?php
/**
 * Profile Form Block Template with WooCommerce Integration
 */
defined('ABSPATH') || exit;

$user = wp_get_current_user();
$is_logged_in = is_user_logged_in();

// Check if WooCommerce is active
$woocommerce_active = class_exists('WooCommerce');

// Get countries and states
$countries = [];
$states = [];
$user_country = '';
$user_state = '';
$user_phone = '';

if ($woocommerce_active) {
    $wc_countries = new WC_Countries();
    $countries = $wc_countries->get_countries();
    $states = $wc_countries->get_states();
    
    // Get saved user data
    $user_country = get_user_meta($user->ID, 'billing_country', true);
    $user_state = get_user_meta($user->ID, 'billing_state', true);
    $user_phone = get_user_meta($user->ID, 'billing_phone', true);
} else {
    // Fallback countries if WooCommerce is not active
    $countries = [
        'US' => 'United States',
        'CA' => 'Canada',
        'GB' => 'United Kingdom',
        'AU' => 'Australia',
        'DE' => 'Germany',
        'FR' => 'France',
        'IT' => 'Italy',
        'ES' => 'Spain',
        'NL' => 'Netherlands',
        'BE' => 'Belgium'
    ];
}
?>

<div <?php echo get_block_wrapper_attributes(); ?>>
    <?php if ($is_logged_in) : ?>
        <div class="profile-picture">
            <div class="image-container">
                <?php
                // Get custom profile image
                $profile_image_id = get_user_meta($user->ID, 'profile_image_id', true);
                $profile_image_url = '';
                
                if ($profile_image_id) {
                    $profile_image_url = wp_get_attachment_image_url($profile_image_id, 'medium');
                }
                
                // Fallback to default avatar if no custom image
                if (!$profile_image_url) {
                    $profile_image_url = get_avatar_url($user->ID, ['size' => 128]);
                }
                ?>
                <img src="<?php echo esc_url($profile_image_url); ?>" 
                     alt="Profile Picture" id="profile-image-preview">
                <label for="profile-image-upload" class="edit-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/>
                        <circle cx="12" cy="13" r="3"/>
                    </svg>
                    <input type="file" id="profile-image-upload" accept="image/*" style="display: none;">
                </label>
            </div>
        </div>

        <div class="form-message-profile-image"></div>

        <form id="bazo-profile-form" class="profile-form" enctype="multipart/form-data">
            
            <div class="form-group">
                <input type="text" name="first_name" class="input-field" placeholder="First Name" 
                       value="<?php echo esc_attr($user->first_name); ?>" required>
                <div class="form-message-first_name"></div>
            </div>
            
            <div class="form-group">
                <input type="text" name="last_name" class="input-field" placeholder="Last Name" 
                       value="<?php echo esc_attr($user->last_name); ?>" required>
                <div class="form-message-last_name"></div>
            </div>
            
            <div class="form-group">
                <input type="email" name="user_email" class="input-field" placeholder="Email" 
                       value="<?php echo esc_attr($user->user_email); ?>" required>
                <div class="form-message-user_email"></div>
            </div>
            
            <div class="form-group">
                <textarea name="description" class="input-field" placeholder="About You"><?php 
                    echo esc_textarea(get_user_meta($user->ID, 'description', true)); 
                ?></textarea>
            </div>
            
            <div class="form-group">
                <input type="password" name="password" class="input-field" 
                       placeholder="New Password (leave blank to keep current)">
            </div>
            
            <div class="form-group">
                <input type="password" name="password_confirm" class="input-field" 
                       placeholder="Confirm New Password">
                <div class="form-message-password"></div>
            </div>
            
            <?php if ($woocommerce_active) : ?>
            <div class="form-group">
                <select name="billing_country" id="country-select" class="input-field" required>
                    <option value="" disabled>Country / Region</option>
                    <?php foreach ($countries as $code => $name) : ?>
                        <option value="<?php echo esc_attr($code); ?>" <?php selected($user_country, $code); ?>>
                            <?php echo esc_html($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <select name="billing_state" id="state-select" class="input-field">
                    <option value="" disabled>State / County</option>
                    <?php if ($user_country && isset($states[$user_country])) : ?>
                        <?php foreach ($states[$user_country] as $code => $name) : ?>
                            <option value="<?php echo esc_attr($code); ?>" <?php selected($user_state, $code); ?>>
                                <?php echo esc_html($name); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="form-message-billing_state"></div>
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <input type="tel" name="billing_phone" class="input-field" placeholder="Phone" 
                       value="<?php echo esc_attr($user_phone); ?>" <?php echo $woocommerce_active ? 'required' : ''; ?>>
                <div class="form-message-billing_phone"></div>
            </div>
            
            <div class="form-message-global"></div>

            <button type="submit" class="submit-button">
                <span class="button-text">Update Profile</span>
                <span class="spinner"></span>
            </button>
        </form>
    <?php else : ?>
        <div class="login-prompt">
            <p>Please <a href="<?php echo esc_url(wp_login_url()); ?>" class="bazo-event-card-wishlist-button">log in</a> to view your profile.</p>
        </div>
    <?php endif; ?>
</div>

<script>
window.bazoProfileFormData = {
    ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('bazo_profile_update'); ?>',
    states: <?php echo json_encode($states); ?>,
    savedState: '<?php echo esc_js($user_state); ?>',
    woocommerceActive: <?php echo $woocommerce_active ? 'true' : 'false'; ?>
};
</script>