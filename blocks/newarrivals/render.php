<?php
/**
 * Server-side rendering for the newarrivals block.
 */

// Get block attributes.
$posts_to_show = $attributes['postsToShow'] ?? 6;
$post_type = $attributes['postType'] ?? 'post';

// Get current date for filtering
$current_date = current_time('Y-m-d');

// Arguments for the WP_Query to get events with end date >= current date
$args = [
    'post_type'      => $post_type,
    'posts_per_page' => $posts_to_show,
    'post_status'    => 'publish',
    'meta_query'     => [
        [
            'key'     => 'event_end_date',
            'value'   => $current_date,
            'compare' => '>=',
            'type'    => 'DATE'
        ]
    ],
    'orderby'        => 'meta_value',
    'meta_key'       => 'event_date',
    'meta_type'      => 'DATE',
    'order'          => 'ASC',
];

// Removed eventType filtering

$query = new WP_Query($args);

ob_start();
?>
<div <?php echo get_block_wrapper_attributes(['class' => 'bazo-newarrivals']); ?>>
    <h2 class="bazo-section-title">new</h2>
    
    <div class="bazo-grid-container">
        <?php
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $event_date = function_exists('get_field') ? get_field('event_date', get_the_ID()) : '';
                $event_end_date = function_exists('get_field') ? get_field('event_end_date', get_the_ID()) : '';
        ?>
                <div class="bazo-card">
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="bazo-card-image">
                                <?php the_post_thumbnail('medium'); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="bazo-card-content">
                            <p class="bazo-category">
                                <?php
                                $terms = get_the_terms(get_the_ID(), $post_type === 'product' ? 'product_cat' : 'category');
                                if (!empty($terms) && !is_wp_error($terms)) {
                                    echo esc_html($terms[0]->name);
                                }
                                ?>
                            </p>
                            
                            <h3 class="bazo-title"><?php the_title(); ?></h3>
                            
                            <?php if ($event_date) : ?>
                                <?php if ($event_date == $event_end_date) : ?>
                                    <p class="bazo-date"><?php echo esc_html($event_date); ?></p>
                                <?php else : ?>
                                    <p class="bazo-date"><?php echo esc_html($event_date); ?> - <?php echo esc_html($event_end_date); ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
        <?php
            }
            wp_reset_postdata();
        } else {
            echo '<p class="bazo-no-items">' . __('No items found.', 'bazo') . '</p>';
        }
        ?>
    </div>
</div>
<?php
echo ob_get_clean();