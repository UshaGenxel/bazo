<?php
/**
 * The Template for displaying dialog for message added to wishlist product.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/ti-addedtowishlist-dialogbox.php.
 *
 * @version             2.5.0
 * @package           TInvWishlist\Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="tinvwl_added_to_wishlist tinv-modal tinv-modal-open">
	<div class="tinv-overlay"></div>
	<div class="tinv-table">
		<div class="tinv-cell">
			<div class="tinv-modal-inner">
				<div class="tinv-txt"><?php echo $msg; // WPCS: xss ok. ?></div>
				<div class="tinvwl-buttons-group tinv-wishlist-clear">
					<?php if ( isset( $wishlist_url ) ) : ?>
						<button class="button tinvwl_button_view tinvwl-btn-onclick"
								data-url="<?php echo esc_url( $wishlist_url ); ?>" type="button"><i
								class="ftinvwl ftinvwl-heart-o"></i><?php echo wp_kses_post( apply_filters( 'tinvwl_view_wishlist_text', tinvwl_message_placeholders( tinv_get_option( 'general', 'text_browse' ), null, $wishlist ) ) ); ?>
						</button>
					<?php endif; ?>
					<?php if ( isset( $dialog_custom_url ) && isset( $dialog_custom_html ) ) : ?>
						<button class="button tinvwl_button_view tinvwl-btn-onclick"
								data-url="<?php echo esc_url( $dialog_custom_url ); ?>"
								type="button"><?php echo $dialog_custom_html; // WPCS: xss ok. ?></button>
					<?php endif; ?>
					<button class="button tinvwl_button_close" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6L6 18"/><path d="m6 6 12 12"/></svg>
					</button>
				</div>
				<div class="tinv-wishlist-clear"></div>
			</div>
		</div>
	</div>
</div>
