<?php
/**
 * Title: Footer
 * Slug: bazo/hidden-footer
 * Inserter: no
 *
 * @package bazo
 * @since 1.0.0
 */
?>

<!-- Outer Footer Wrapper -->
<!-- wp:group {"style":{"spacing":{"blockGap":"0px"},"backgroundColor":"white","textColor":"black"},"layout":{"type":"constrained","wideSize":"1280px","contentSize":"800px"}} -->
<div class="wp-block-group has-black-color has-white-background-color has-text-color has-background">

    <!-- Newsletter Signup Section -->
    <!-- wp:group {"layout":{"type":"constrained","justifyContent":"center"},"style":{"spacing":{"padding":{"top":"50px","bottom":"50px"}}}} -->
    <div class="wp-block-group" style="padding-top:50px;padding-bottom:50px">
        <!-- wp:paragraph {"align":"center","fontSize":"medium"} -->
        <p class="has-text-align-center has-medium-font-size"><strong><?php echo esc_html__( 'stay up to date!', 'bazo' ); ?></strong></p>
        <!-- /wp:paragraph -->

        <!-- Newsletter Form -->
        <!-- wp:group {"layout":{"type":"flex","justifyContent":"center","flexWrap":"nowrap"},"style":{"spacing":{"blockGap":"0"}}} -->
        <div class="wp-block-group">
            <!-- wp:html -->
            <input type="email" placeholder="<?php esc_attr_e( 'your e-mail address…', 'bazo' ); ?>" style="padding: 10px 20px; border: 1px solid #ccc; border-radius: 999px 0 0 999px; outline: none; background: white;" />
            <button style="padding: 10px 20px; border: 1px solid #ccc; border-left: none; border-radius: 0 999px 999px 0; background: white; cursor: pointer;"><?php esc_html_e( 'join', 'bazo' ); ?></button>
            <!-- /wp:html -->
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:group -->

    <!-- Bottom Footer Bar -->
    <!-- wp:group {"style":{"border":{"top":{"color":"#000000","width":"1px"}}, "spacing":{"padding":{"top":"20px","bottom":"20px"}}},"layout":{"type":"flex","justifyContent":"space-between","flexWrap":"wrap","verticalAlignment":"center"}} -->
    <div class="wp-block-group" style="border-top:1px solid #000000; padding-top:20px; padding-bottom:20px">

        <!-- Left: Logo -->
        <!-- wp:group {"layout":{"type":"flex","verticalAlignment":"center","justifyContent":"flex-start","flexWrap":"nowrap","blockGap":"20px"}} -->
        <div class="wp-block-group">
            <!-- wp:image {"width":30,"height":30,"style":{"border":{"radius":"999px"}}} -->
            <figure class="wp-block-image" style="border-radius:999px">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo-icon.png" alt="Bazo Logo" width="30" height="30" />
            </figure>
            <!-- /wp:image -->
        </div>

        <!-- Center: Social Icons and Copyright -->
        <!-- wp:group {"layout":{"type":"flex","orientation":"vertical","justifyContent":"center","alignItems":"center","blockGap":"10px"}} -->
        <div class="wp-block-group">
            <!-- wp:social-links {"iconColor":"black","iconColorValue":"#000000","className":"is-style-logos-only"} -->
            <ul class="wp-block-social-links is-style-logos-only has-icon-color">
                <!-- wp:social-link {"service":"instagram"} /-->
                <!-- wp:social-link {"service":"google"} /-->
                <!-- wp:social-link {"service":"facebook"} /-->
            </ul>
            <!-- /wp:social-links -->

            <!-- wp:paragraph {"fontSize":"small"} -->
            <p class="has-small-font-size"><?php echo esc_html__( '© BAZO ' . date('Y') . '. powered by <a href="https://spiderwares.com/">spiderwares</a>', 'bazo' ); ?></p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:group -->

        <!-- Right: Footer Menu -->
        <!-- wp:navigation {"layout":{"type":"flex","flexWrap":"wrap"},"style":{"typography":{"fontSize":"12px"}}} -->
        <!-- wp:navigation-link {"label":"legal notice","url":"#"} /-->
        <!-- wp:navigation-link {"label":"privacy policy","url":"#"} /-->
        <!-- wp:navigation-link {"label":"terms","url":"#"} /-->
        <!-- /wp:navigation -->
    </div>
    <!-- /wp:group -->

</div>
<!-- /wp:group -->
