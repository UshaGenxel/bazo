<?php
/**
 * Server-side rendering for the product-header block.
 * Displays product header with back button, title, share button, wishlist, and navigation controls.
 */

$show_share = $attributes['showShare'] ?? true;
$show_wishlist = $attributes['showWishlist'] ?? true;
$show_navigation = $attributes['showNavigation'] ?? true;

// Get current post data
$post_id = get_the_ID();
$post_title = get_the_title($post_id);
$product = function_exists('wc_get_product') ? wc_get_product($post_id) : null;

// Get product image if available
$product_image = '';
if ($product && $product->get_image_id()) {
    $product_image = wp_get_attachment_image_src($product->get_image_id(), 'thumbnail');
    $product_image = $product_image ? $product_image[0] : '';
}

// Get current URL for sharing
$current_url = get_permalink($post_id);

// Get adjacent products for navigation
$prev_product = null;
$next_product = null;

if (function_exists('wc_get_products')) {
    // Get all published products
    $products = wc_get_products(array(
        'status' => 'publish',
        'limit' => -1,
        'orderby' => 'date',
        'order' => 'ASC',
        'return' => 'ids'
    ));
    
    $current_index = array_search($post_id, $products);
    
    if ($current_index !== false) {
        // Get previous product
        if ($current_index > 0) {
            $prev_product_id = $products[$current_index - 1];
            $prev_product = array(
                'id' => $prev_product_id,
                'title' => get_the_title($prev_product_id),
                'url' => get_permalink($prev_product_id)
            );
        }
        
        // Get next product
        if ($current_index < count($products) - 1) {
            $next_product_id = $products[$current_index + 1];
            $next_product = array(
                'id' => $next_product_id,
                'title' => get_the_title($next_product_id),
                'url' => get_permalink($next_product_id)
            );
        }
    }
}

ob_start();
?>

<div <?php echo get_block_wrapper_attributes(); ?>>
    <div class="bazo-product-header" >
        <a href="javascript:history.back()" class="bazo-product-header-back">
            <svg style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 24 24">
                <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
            </svg>
            <span><?php echo esc_html__('Back', 'bazo'); ?></span>
        </a>
        
        <h1 class="bazo-product-header-title">
            <?php echo esc_html($post_title); ?>
        </h1>
        
        <div class="bazo-product-header-actions" >
            <?php if ($show_share) : ?>
                <button id="shareButton" class="bazo-product-header-share"  title="<?php echo esc_attr__('Share Product', 'bazo'); ?>" onclick="openShareModal()">
                    <svg style="width: 18px; height: 18px; fill: currentColor;" viewBox="0 0 24 25" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_42_1807)">
                            <path d="M17.5003 24.9997C17.1954 24.9997 16.913 24.8317 16.7662 24.5573L10.7125 13.0904L0.417955 7.3009C0.124308 7.13853 -0.0394564 6.80818 0.00572002 6.47783C0.0508965 6.14749 0.29372 5.87313 0.621249 5.78914L22.9667 0.0276655C23.249 -0.0451228 23.5483 0.0332646 23.7572 0.234832C23.9662 0.4364 24.0509 0.733153 23.9831 1.01311L18.3135 24.3726C18.2344 24.7085 17.9521 24.9549 17.6076 24.9941C17.5737 24.9941 17.5398 24.9997 17.506 24.9997H17.5003ZM3.0156 6.86977L11.746 11.7858C11.8871 11.8642 12.0001 11.9818 12.0735 12.1217L17.218 21.8586L22.0405 1.96496L3.0156 6.86977Z" fill="currentColor"/>
                            <path d="M11.3392 13.3198C11.1246 13.3198 10.9157 13.2415 10.7519 13.0791C10.43 12.7599 10.43 12.2392 10.7519 11.9145L22.5881 0.240338C22.91 -0.0788107 23.4352 -0.0788107 23.7627 0.240338C24.0846 0.559487 24.0846 1.0802 23.7627 1.40495L11.9265 13.0847C11.7627 13.2471 11.5538 13.3254 11.3392 13.3254V13.3198Z" fill="currentColor"/>
                        </g>
                        <defs>
                            <clipPath id="clip0_42_1807">
                                <rect width="24" height="25" fill="white"/>
                            </clipPath>
                        </defs>
                     </svg>
                </button>
            <?php endif; ?>
            
            <?php if ($show_wishlist) : ?>
                <div class="ti-wishlist-container" >
                    <?php echo do_shortcode( '[ti_wishlists_addtowishlist product_id="' . get_the_ID() . '"]' ); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($show_navigation) : ?>
                 <div class="bazo-product-header-navigation" >
                     <?php if ($prev_product) : ?>
                         <a href="<?php echo esc_url($prev_product['url']); ?>" 
                            title="<?php echo esc_attr__('Previous: ', 'bazo') . esc_attr($prev_product['title']); ?>"
                            onmouseover="this.style.color='#333'"
                            onmouseout="this.style.color='#666'">
                             <svg style="width: 14px; height: 14px; fill: currentColor;" viewBox="0 0 24 24">
                                 <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                             </svg>
                         </a>
                     <?php else : ?>
                         <span title="<?php echo esc_attr__('No previous product', 'bazo'); ?>">
                             <svg style="width: 14px; height: 14px; fill: currentColor;" viewBox="0 0 24 24">
                                 <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                             </svg>
                         </span>
                     <?php endif; ?>
                     
                     <?php if ($next_product) : ?>
                         <a href="<?php echo esc_url($next_product['url']); ?>" 
                            title="<?php echo esc_attr__('Next: ', 'bazo') . esc_attr($next_product['title']); ?>"
                            onmouseover="this.style.color='#333'"
                            onmouseout="this.style.color='#666'">
                             <svg style="width: 14px; height: 14px; fill: currentColor;" viewBox="0 0 24 24">
                                 <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                             </svg>
                         </a>
                     <?php else : ?>
                         <span title="<?php echo esc_attr__('No next product', 'bazo'); ?>">
                             <svg style="width: 14px; height: 14px; fill: currentColor;" viewBox="0 0 24 24">
                                 <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                             </svg>
                         </span>
                     <?php endif; ?>
                 </div>
             <?php endif; ?>
        </div>
    </div>
    
    <?php if ($show_share) : ?>
        <!-- Share Modal -->
        <div id="shareModal" class="bazo-share-modal">
            <div>
                <div>
                    <h3><?php echo esc_html__('Share Product', 'bazo'); ?></h3>
                    <button onclick="closeShareModal()" title="<?php echo esc_attr__('Close', 'bazo'); ?>">
                        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24">
                            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                        </svg>
                    </button>
                </div>
                
                <div>
                    <div>
                        <div id="productImage">
                            <?php if ($product_image) : ?>
                                <img src="<?php echo esc_url($product_image); ?>" alt="<?php echo esc_attr($post_title); ?>">
                            <?php else : ?>
                                <svg style="width: 24px; height: 24px; fill: #6c757d;" viewBox="0 0 24 24">
                                    <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4 id="productTitle"><?php echo esc_html($post_title); ?></h4>
                            <p ><?php echo esc_html__('Click to copy link', 'bazo'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div >
                    <button onclick="shareToFacebook()">
                        <svg style="width: 18px; height: 18px; fill: currentColor;" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        Facebook
                    </button>
                    
                    <button onclick="shareToTwitter()">
                        <svg style="width: 18px; height: 18px; fill: currentColor;" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                        Twitter
                    </button>
                    
                    <button onclick="shareToWhatsApp()">
                        <svg style="width: 18px; height: 18px; fill: currentColor;" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                        </svg>
                        WhatsApp
                    </button>
                    
                    <button onclick="copyToClipboard()">
                        <svg style="width: 18px; height: 18px; fill: currentColor;" viewBox="0 0 24 24">
                            <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                        </svg>
                        <?php echo esc_html__('Copy Link', 'bazo'); ?>
                    </button>
                </div>
                
                <div>
                    <button onclick="closeShareModal()"><?php echo esc_html__('Cancel', 'bazo'); ?></button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
echo ob_get_clean();
?>
