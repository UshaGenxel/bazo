<?php
/**
 * Server-side rendering for the event-grid block.
 * This file is responsible for the initial rendering for better SEO.
 */

$posts_to_show = $attributes['postsToShow'] ?? 9;
$selected_categories = $attributes['selectedCategories'] ?? []; // This now contains term_ids (numbers)
$show_load_more_button = $attributes['showLoadMoreButton'] ?? true;
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

// Get all categories to render the filter buttons
$all_categories = get_terms([
    'taxonomy' => $taxonomy,
    'hide_empty' => false,
]);

ob_start();
?>
<div <?php echo get_block_wrapper_attributes([
    'data-posts-to-show' => esc_attr($posts_to_show),
    'data-post-type' => esc_attr($post_type),
    'data-taxonomy' => esc_attr($taxonomy),
    'data-selected-categories' => json_encode($selected_categories),
    'data-show-load-more' => json_encode($show_load_more_button)
]); ?>>
    <div class="bazo-event-filters-wrapper">
        <button class="bazo-event-filter-icon-button">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-filter">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
        </button>
        <div class="bazo-event-filters">
            <button class="bazo-event-filter-button <?php echo empty($selected_categories) ? 'active' : ''; ?>" data-term="all">All</button>
            <?php foreach ($all_categories as $category) : ?>
                <button
                    class="bazo-event-filter-button <?php echo in_array($category->term_id, $selected_categories) ? 'active' : ''; ?>"
                    data-term="<?php echo esc_attr($category->term_id); ?>">
                    <?php echo esc_html($category->name); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="bazo-event-grid-container">
        <div class="bazo-event-grid">
        <?php
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                // Assuming 'event_date' is an ACF field
                $event_date = get_field('event_date', get_the_ID());
                ?>
                <div class="bazo-event-card">
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="bazo-event-card-image">
                                <?php the_post_thumbnail('large'); ?>
                            </div>
                        <?php endif; ?>
                        <div class="bazo-event-card-content">
                            <h3><?php the_title(); ?></h3>
                            <?php if ($event_date) : ?>
                                <p class="bazo-event-card-date"><?php echo esc_html($event_date); ?></p>
                            <?php endif; ?>
                            </div>
                    </a>
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