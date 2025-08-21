<?php
/**
 * Server-side rendering for the review-section block.
 * Displays review options with thumbs up/down, neutral face, and comment functionality.
 */

$show_review_options = $attributes['showReviewOptions'] ?? true;

// Map coordinates (you can make these dynamic via block attributes)
$map_data = [
    'lat' => 48.1326213,
    'lng' => 11.57301,
    'address' => 'Goldener Reiter, Theklastraße, Munich-Altstadt-Lehel, Germany',
    'name' => 'Goldener Reiter'
];

ob_start();
?>

<div <?php echo get_block_wrapper_attributes(); ?>>
	<div class="bazo-review-section">
		<div class="bazo-review-header">
			<div class="bazo-review-title-container">
				<h3 class="bazo-review-title">review 
					<svg class="bazo-review-info-icon" width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M7.5 1.25C4.0625 1.25 1.25 4.0625 1.25 7.5C1.25 10.9375 4.0625 13.75 7.5 13.75C10.9375 13.75 13.75 10.9375 13.75 7.5C13.75 4.0625 10.9375 1.25 7.5 1.25Z" stroke="#8D8D8E" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M7.5 10L7.5 6.875" stroke="#8D8D8E" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M7.50391 5L7.49829 5" stroke="#8D8D8E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</h3>
				<div class="bazo-review-tooltip" role="tooltip" aria-hidden="true">
					<div class="bazo-review-tooltip-item">👍 would definitely visit again</div>
					<div class="bazo-review-tooltip-item">😐 visit was fine</div>
					<div class="bazo-review-tooltip-item">👎 would definitely not visit again</div>
					<div class="bazo-review-tooltip-item">💬 add some note</div>
				</div>
			</div>
		</div>

		<div class="bazo-review-actions">
			<?php
			// Get current product if in WooCommerce context
			$product_id = get_the_ID();
			$product = wc_get_product($product_id);
			
			// Dynamic review actions based on product data
			$review_actions = [
				'thumbs-up' => [
					'title' => $product ? sprintf(__('Would definitely buy %s again', 'bazo'), $product->get_name()) : __('Would definitely visit again', 'bazo'),
					'class' => 'bazo-review-thumbs-up',
					'icon' => '👍'
				],
				'neutral' => [
					'title' => $product ? sprintf(__('%s was okay', 'bazo'), $product->get_name()) : __('Visit was fine', 'bazo'),
					'class' => 'bazo-review-neutral', 
					'icon' => '😐'
				],
				'thumbs-down' => [
					'title' => $product ? sprintf(__('Would not buy %s again', 'bazo'), $product->get_name()) : __('Would definitely not visit again', 'bazo'),
					'class' => 'bazo-review-thumbs-down',
					'icon' => '👎'
				],
				'comment' => [
					'title' => $product ? sprintf(__('Add note about %s', 'bazo'), $product->get_name()) : __('Add some note', 'bazo'),
					'class' => 'bazo-review-comment',
					'icon' => '💬'
				]
			];
			
			foreach ($review_actions as $action_key => $action) :
			?>
			<button class="bazo-review-action <?php echo esc_attr($action['class']); ?>" 
					title="<?php echo esc_attr($action['title']); ?>" 
					data-product-id="<?php echo esc_attr($product_id); ?>"
					data-action="<?php echo esc_attr($action_key); ?>">
				<span class="bazo-review-icon"><?php echo $action['icon']; ?></span>
			</button>
			<?php endforeach; ?>
		</div>

		<?php if ($show_review_options) : ?>
		<div class="bazo-review-note-section" style="display: none;">
			<textarea placeholder="<?php echo $product ? sprintf(__('Tell us about your experience with %s...', 'bazo'), $product->get_name()) : __('Tell us about your experience...', 'bazo'); ?>" rows="3"></textarea>
		</div>
		
		<!-- Review Submission Form -->
		<div class="bazo-review-form" style="display: none;">
			<form class="bazo-review-submit-form" method="post" action="">
				<?php wp_nonce_field('bazo_review_submit', 'bazo_review_nonce'); ?>
				<input type="hidden" name="bazo_review_product_id" value="<?php echo esc_attr($product_id); ?>">
				<input type="hidden" name="bazo_review_rating" id="bazo_review_rating" value="">
				<input type="hidden" name="bazo_review_note" id="bazo_review_note" value="">
				
				<div class="bazo-review-form-row">
					<div class="bazo-review-form-field">
						<label for="bazo_review_name"><?php _e('Your Name', 'bazo'); ?> *</label>
						<input type="text" id="bazo_review_name" name="bazo_review_name" required 
							   placeholder="<?php _e('Enter your name', 'bazo'); ?>">
					</div>
					<div class="bazo-review-form-field">
						<label for="bazo_review_email"><?php _e('Email Address', 'bazo'); ?> *</label>
						<input type="email" id="bazo_review_email" name="bazo_review_email" required 
							   placeholder="<?php _e('Enter your email', 'bazo'); ?>">
					</div>
				</div>
				
				<div class="bazo-review-form-actions">
					<button type="submit" class="bazo-review-submit-btn">
						<span class="bazo-review-submit-text"><?php _e('Submit Review', 'bazo'); ?></span>
						<span class="bazo-review-submit-loading" style="display: none;">
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M8 1.5C4.41015 1.5 1.5 4.41015 1.5 8C1.5 11.5899 4.41015 14.5 8 14.5C11.5899 14.5 14.5 11.5899 14.5 8C14.5 4.41015 11.5899 1.5 8 1.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M8 4.5V8L10.5 10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							<?php _e('Submitting...', 'bazo'); ?>
						</span>
					</button>
				</div>
				
				<div class="bazo-review-message" style="display: none;"></div>
			</form>
		</div>
		<?php endif; ?>
		
		<!-- Location Map Section -->
		<div class="bazo-review-location">
			<div class="bazo-review-location-header">
				<h4 class="bazo-review-location-title">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M8 8.95333C9.84095 8.95333 11.3333 7.46095 11.3333 5.62C11.3333 3.77905 9.84095 2.28667 8 2.28667C6.15905 2.28667 4.66667 3.77905 4.66667 5.62C4.66667 7.46095 6.15905 8.95333 8 8.95333Z" stroke="currentColor" stroke-width="1.5"/>
						<path d="M2.28667 13.7133C2.28667 11.8724 3.77905 10.38 5.62 10.38H10.38C12.2209 10.38 13.7133 11.8724 13.7133 13.7133" stroke="currentColor" stroke-width="1.5"/>
					</svg>
					<?php echo esc_html($map_data['name']); ?>
				</h4>
				<p class="bazo-review-location-address"><?php echo esc_html($map_data['address']); ?></p>
			</div>
			
			<div class="bazo-review-map-container">
				<div class="acf-map" data-zoom="16">
					<div class="marker" data-lat="<?php echo esc_attr($map_data['lat']); ?>" data-lng="<?php echo esc_attr($map_data['lng']); ?>">
						<?php echo esc_html($map_data['name']); ?>
					</div>
				</div>
			</div>
			
			<div class="bazo-review-location-actions">
				<a href="https://maps.google.com/?q=<?php echo esc_attr($map_data['lat']); ?>,<?php echo esc_attr($map_data['lng']); ?>" 
				   target="_blank" rel="noopener" class="bazo-review-location-action">
					<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M1 13V7.53846C1 5.88161 2.34315 4.53846 4 4.53846H13M13 4.53846L10.5 2M13 4.53846L10.5 6.65385" stroke="currentColor"/>
					</svg>
					<?php _e('Get Directions', 'bazo'); ?>
				</a>
			</div>
		</div>
	</div>
</div>

<?php
echo ob_get_clean();
