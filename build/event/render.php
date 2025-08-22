<?php
/**
 * Server-side rendering for the event-grid block.
 * This file is responsible for the initial rendering for better SEO.
 */

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
            <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_4_2590)">
                <path d="M10.7516 25C10.3526 25 9.94338 24.8775 9.60579 24.6426C9.05336 24.2647 8.71576 23.6418 8.71576 22.9677V15.4412C8.71576 14.7774 8.52139 14.1442 8.14287 13.6029L0.234941 2.11397C-0.0515045 1.69526 -0.0821951 1.16422 0.153099 0.714869C0.398623 0.275735 0.879442 0.0306373 1.36026 0L22.6493 0.132761C23.1505 0.132761 23.6007 0.408497 23.836 0.847631C24.0713 1.28676 24.0406 1.81781 23.7644 2.22631L16.0713 13.7868C15.8155 14.1748 15.6825 14.6242 15.6723 15.0837L15.6314 21.8546C15.6314 22.692 15.1199 23.4273 14.3424 23.7337L11.4779 24.8468C11.2426 24.9387 10.9869 24.9898 10.7413 24.9898L10.7516 25ZM1.69786 1.53186L9.41141 12.7349C9.96384 13.5315 10.2503 14.471 10.2503 15.4412V22.9677C10.2503 23.2026 10.4037 23.3354 10.4651 23.3762C10.5265 23.4171 10.7106 23.509 10.9255 23.4273L13.7797 22.3141C13.9741 22.2426 14.0968 22.0588 14.1378 15.0837C14.1378 14.3178 14.373 13.5825 14.7925 12.9493L22.3014 1.66462L1.69786 1.53186Z" fill="#8D8D8E"/>
                </g>
                <defs>
                <clipPath id="clip0_4_2590">
                <rect width="24" height="25" fill="white"/>
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
                                $trimmed = wp_trim_words($excerpt, 10, '...');
                                echo esc_html($trimmed);
                                ?>
                            </samp>
                        </div>
                    </a>
                    <div class="wishlist-wrap">
                        <?php
                            echo do_shortcode( '[ti_wishlists_addtowishlist product_id="' . get_the_ID() . '"]' );
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