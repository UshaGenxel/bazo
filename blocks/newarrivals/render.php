<?php
/**
 * Server-side rendering for the newarrivals block.
 */

// Get block attributes.
$posts_to_show = $attributes['postsToShow'] ?? 6;
$post_type = $attributes['postType'] ?? 'post';

// Arguments for the WP_Query to get the newest posts/products.
$args = [
    'post_type'      => $post_type,
    'posts_per_page' => $posts_to_show,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
];

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
                $event_date = get_field('event_date', get_the_ID());
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
                                <p class="bazo-date"><?php echo esc_html(date('d.m.Y', strtotime($event_date))); ?></p>
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