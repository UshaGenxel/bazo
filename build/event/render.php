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
    'data-placeholder-url' => esc_attr(get_template_directory_uri() . '/assets/images/placeholder.png')
]); ?>>
    <div class="bazo-event-filters-wrapper">
        <button class="bazo-event-filter-icon-button">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-filter">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
        </button>
        <div class="bazo-event-filters">
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
            while ($query->have_posts()) {
                $query->the_post();

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