<?php
/**
 * Server-side rendering for the event-grid block.
 * This file is responsible for the initial rendering for better SEO.
 */

$is_logged_in = is_user_logged_in();
$posts_to_show = $attributes['postsToShow'] ?? 9;
$selected_categories = $attributes['selectedCategories'] ?? []; // This now contains term_ids (numbers)
$show_load_more_button = $attributes['showLoadMoreButton'] ?? true;
$show_loader = $attributes['showLoader'] ?? true;
$post_type = $attributes['postType'] ?? 'post';
$taxonomy = $attributes['taxonomy'] ?? 'category';
$show_ads = $attributes['showAds'] ?? true;
$ad_positions = $attributes['adPositions'] ?? [];
$paged = 1;

// Arguments for the initial WP_Query
$args = [
    'post_type' => $post_type,
    'posts_per_page' => $posts_to_show,
    'paged' => $paged,
    'post_status' => 'publish',
];

if (!empty($selected_categories)) {
    $args['tax_query'] = [
        [
            'taxonomy' => $taxonomy,
            'field'    => 'term_id', // IMPORTANT: Use 'term_id' for filtering by ID
            'terms'    => $selected_categories,
        ],
    ];
}

$query = new WP_Query($args);

// Get only the selected categories to render the filter buttons
$filter_categories = [];
if (!empty($selected_categories)) {
    $filter_categories = get_terms([
        'taxonomy' => $taxonomy,
        'include' => $selected_categories,
        'hide_empty' => false,
    ]);
} else {
    // If no categories selected, show all categories
    $filter_categories = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ]);
}

ob_start();
?>
<div <?php echo get_block_wrapper_attributes([
    'data-posts-to-show' => esc_attr($posts_to_show),
    'data-post-type' => esc_attr($post_type),
    'data-taxonomy' => esc_attr($taxonomy),
    'data-selected-categories' => json_encode($selected_categories),
    'data-show-load-more' => json_encode($show_load_more_button),
    'data-show-loader' => json_encode($show_loader),
    'data-show-ads' => json_encode($show_ads),
    'data-ad-positions' => json_encode($ad_positions),
    'data-placeholder-url' => esc_attr(get_template_directory_uri() . '/assets/images/placeholder.png')
]); ?>>
    <div class="bazo-event-filters-wrapper">
        <button class="bazo-event-filter-icon-button">
            <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="1.35" y="1.35" width="41.2971" height="41.2971" rx="20.6485" fill="white" stroke="#8D8D8E" stroke-width="1.3"/>
                <g clip-path="url(#clip0_4_2588)">
                <path d="M20.7516 35C20.3526 35 19.9434 34.8775 19.6058 34.6426C19.0534 34.2647 18.7158 33.6418 18.7158 32.9677V25.4412C18.7158 24.7774 18.5214 24.1442 18.1429 23.6029L10.2349 12.114C9.9485 11.6953 9.9178 11.1642 10.1531 10.7149C10.3986 10.2757 10.8794 10.0306 11.3603 10L32.6493 10.1328C33.1505 10.1328 33.6007 10.4085 33.836 10.8476C34.0713 11.2868 34.0406 11.8178 33.7644 12.2263L26.0713 23.7868C25.8155 24.1748 25.6825 24.6242 25.6723 25.0837L25.6314 31.8546C25.6314 32.692 25.1199 33.4273 24.3424 33.7337L21.4779 34.8468C21.2426 34.9387 20.9869 34.9898 20.7413 34.9898L20.7516 35ZM11.6979 11.5319L19.4114 22.7349C19.9638 23.5315 20.2503 24.471 20.2503 25.4412V32.9677C20.2503 33.2026 20.4037 33.3354 20.4651 33.3762C20.5265 33.4171 20.7106 33.509 20.9255 33.4273L23.7797 32.3141C23.9741 32.2426 24.0968 32.0588 24.0968 31.8546L24.1378 25.0837C24.1378 24.3178 24.373 23.5825 24.7925 22.9493L32.3014 11.6646L11.6979 11.5319Z" fill="#8D8D8E"/>
                </g>
                <defs>
                <clipPath id="clip0_4_2588">
                <rect width="24" height="25" fill="white" transform="translate(10 10)"/>
                </clipPath>
                </defs>
            </svg>
        </button>
        <div class="bazo-event-filters">
            
            <div class="bazo-event-filters-category">
            <div class="bazo-event-filters-header">
                <button class="bazo-event-filter-close">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
                <button class="bazo-event-filter-button <?php echo empty($selected_categories) ? 'active' : ''; ?>" data-term="all">All</button>
                <?php foreach ($filter_categories as $category) : ?>
                    <button
                        class="bazo-event-filter-button <?php echo in_array($category->term_id, $selected_categories) ? 'active' : ''; ?>"
                        data-term="<?php echo esc_attr($category->term_id); ?>">
                        <?php echo esc_html($category->name); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        
    </div>
    
    <?php if ($show_loader) : ?>
    <div class="bazo-event-loader" style="display: none;">
        <div class="dot-spinner">
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="bazo-event-grid-container">
        <div class="bazo-event-grid">
        <?php
        if ($query->have_posts()) {
            $post_counter = 0;
            while ($query->have_posts()) {
                $query->the_post();
                $post_counter++;

                // Check if we need to insert an ad at this position
                if ($show_ads && !empty($ad_positions)) {
                    foreach ($ad_positions as $ad) {
                        if ($ad['position'] === $post_counter) {
                            // Insert ad block
                            $ad_span_class = 'bazo-ad-span-' . $ad['span'];
                            ?>
                            <div class="bazo-ad-block <?php echo esc_attr($ad_span_class); ?>">
                                <div class="bazo-ad-content">
                                    <span class="bazo-ad-text"><?php echo esc_html($ad['text']); ?></span>
                                </div>
                            </div>
                            <?php
                        }
                    }
                }

                // Assuming is an ACF field
                $event_date = get_field('event_date', get_the_ID());
                $event_time = get_field('event_time', get_the_ID());
                
                ?>
                <div class="bazo-event-card">
                    <a href="<?php the_permalink(); ?>">
                        <div class="bazo-event-card-image">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('large'); ?>
                            <?php else : ?>
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.png" alt="Event placeholder" />
                            <?php endif; ?>
                        </div>
                        <div class="bazo-event-card-content">
                            <p class="bazo-event-card-category">
                                <?php
                                $categories = get_the_terms(get_the_ID(), $taxonomy);
                                if ($categories && !is_wp_error($categories)) {
                                    foreach ($categories as $category) {
                                        echo '<span class="bazo-event-category">' . esc_html($category->name) . '</span> ';
                                    }
                                }
                                ?>
                            </p>
                            <h3><?php the_title(); ?></h3>
                                <?php if ($event_date) : ?>
                                <p class="bazo-event-card-date"><?php echo esc_html($event_date); ?></p>
                                <?php endif; ?>
                            
                                <?php if ($event_time) : ?>
                                <p class="bazo-event-card-date"><?php echo esc_html($event_time); ?></p>
                                <?php endif; ?>
                                <samp class="bazo-event-card-short-description"><?php
                                    $excerpt = get_the_excerpt();
                                    $trimmed = wp_trim_words($excerpt, 30, '...');
                                    echo esc_html($trimmed);
                                    ?>
                                </samp>
                        </div>
                    </a>
                    <div class="wishlist-wrap">
                        <?php if ( $is_logged_in ) : 
                            echo do_shortcode( '[ti_wishlists_addtowishlist product_id="' . get_the_ID() . '"]' );
                        else :
                            ?>
                            <div class="bazo-event-card-wishlist-button tinv-wraper woocommerce tinv-wishlist tinvwl-shortcode-add-to-cart tinvwl-the_content" data-tinvwl_product_id="<?php echo get_the_ID(); ?>">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/wishlist.svg" alt="Wishlist icon" />
                            </div>
                        <?php
                        endif;
                        ?>
                    </div>
                </div>
                <?php
            }
            wp_reset_postdata();
        } else {
            echo '<p>' . __('No events found.', 'bazo') . '</p>';
        }
        ?>
        </div>
    </div>
    <?php if ($show_load_more_button && $query->max_num_pages > 1) : ?>
        <div class="bazo-event-load-more" data-max-pages="<?php echo esc_attr($query->max_num_pages); ?>">
            <button class="bazo-load-more-button">load more...</button>
        </div>
    <?php endif; ?>
</div>
<?php
echo ob_get_clean();
?>