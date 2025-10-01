<?php
/**
 * The Template for displaying wishlist if a current user is owner.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/ti-wishlist.php.
 *
 * @version             2.3.3
 * @package           TInvWishlist\Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
wp_enqueue_script( 'tinvwl' );
?>
<div class="tinv-wishlist woocommerce tinv-wishlist-clear bazo__wishlist_container">
	<?php do_action( 'tinvwl_before_wishlist', $wishlist ); ?>
	<?php if ( function_exists( 'wc_print_notices' ) && isset( WC()->session ) ) {
		wc_print_notices();
	} ?>
	<?php $form_url = tinv_url_wishlist( $wishlist['share_key'], $wl_paged, true ); ?>
    <h1 class="bazo__wishlist_title"><?php echo __('Saved Events', 'bazo'); ?> </h1>

	<div class="tab">
		<button class="tablinks active" onclick="openCity(event, 'event-wishlist-item')">Events</button>
		<button class="tablinks" onclick="openCity(event, 'local-wishlist-item')">Local</button>
	</div>

	<form action="<?php echo esc_url( $form_url ); ?>" method="post" autocomplete="off"
		  data-tinvwl_paged="<?php echo $wl_paged; ?>" data-tinvwl_per_page="<?php echo $wl_per_page; ?>"
		  data-tinvwl_sharekey="<?php echo $wishlist['share_key'] ?>">
		<?php do_action( 'tinvwl_before_wishlist_table', $wishlist ); ?>

		<table id="event-wishlist-item" class="tabcontent tinvwl-table-manage-list bazo__wishlist_table" style="display: block;">
			<tbody>
			<?php do_action( 'tinvwl_wishlist_contents_before' ); ?>

			<?php

			global $product, $post;
			// store global product data.
			$_product_tmp = $product;
			// store global post data.
			$_post_tmp = $post;

			foreach ( $products as $wl_product ) {

				if ( empty( $wl_product['data'] ) ) {
					continue;
				}

				// Get the product object to check event type
				$product_temp = $wl_product['data'];
				
				// Only show products that are events
				if ( get_field('event_types', $product_temp->get_id()) !== 'event' ) {
					continue;
				}

				// override global product data.
				$product = apply_filters( 'tinvwl_wishlist_item', $wl_product['data'] );
				// override global post data.
				$post = get_post( $product->get_id() );

				unset( $wl_product['data'] );
				if ( $wl_product['quantity'] > 0 && apply_filters( 'tinvwl_wishlist_item_visible', true, $wl_product, $product ) ) {
					$product_url = apply_filters( 'tinvwl_wishlist_item_url', $product->get_permalink(), $wl_product, $product );
					do_action( 'tinvwl_wishlist_row_before', $wl_product, $product );
					?>
					<tr class="<?php echo esc_attr( apply_filters( 'tinvwl_wishlist_item_class', 'wishlist_item', $wl_product, $product ) ); ?>">
						<?php if ( isset( $wishlist_table['colm_checkbox'] ) && $wishlist_table['colm_checkbox'] ) { ?>
							<td class="product-cb">
								<?php
								echo apply_filters( 'tinvwl_wishlist_item_cb', sprintf( // WPCS: xss ok.
									'<input type="checkbox" name="wishlist_pr[]" class="input-checkbox" value="%d" title="%s">', esc_attr( $wl_product['ID'] ), __( 'Select for bulk action', 'ti-woocommerce-wishlist' )
								), $wl_product, $product );
								?>
							</td>
						<?php } ?>
						<td class="product-remove">
							<button type="submit" name="tinvwl-remove"
									value="<?php echo esc_attr( $wl_product['ID'] ); ?>"
									title="<?php _e( 'Remove', 'ti-woocommerce-wishlist' ) ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 19L19 5" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M19 19L5 5" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
							</button>
						</td>
						<td class="product-thumbnail">
							<?php
							$thumbnail = apply_filters( 'tinvwl_wishlist_item_thumbnail', $product->get_image('medium'), $wl_product, $product );

							if ( ! $product->is_visible() ) {
								echo $thumbnail; // WPCS: xss ok.
							} else {
								printf( '<a href="%s">%s</a>', esc_url( $product_url ), $thumbnail ); // WPCS: xss ok.
							}
							?>
						</td>
                        <td class="product-date">
                            <?php
                            // Assuming is an ACF field
                            $event_date = get_field('event_date', $product->get_id());
                            $event_time = get_field('event_time', $product->get_id());
                            ?>
                            <?php if ($event_date) : ?>
                                <p class="bazo-event-card-date"><?php echo esc_html($event_date); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($event_time) : ?>
                                <p class="bazo-event-card-date"><?php echo esc_html($event_time); ?></p>
                            <?php endif; ?>
						</td>
						<td class="product-name">
							<?php
							if ( ! $product->is_visible() ) {
								$item_name = apply_filters( 'tinvwl_wishlist_item_name', is_callable( array(
										$product,
										'get_name'
									) ) ? $product->get_name() : $product->get_title(), $wl_product, $product ) . '&nbsp;';
							} else {
								$item_name = apply_filters( 'tinvwl_wishlist_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_url ), is_callable( array(
									$product,
									'get_name'
								) ) ? $product->get_name() : $product->get_title() ), $wl_product, $product );
							}

							// Add product description below the name
							if ( $product ) {
								// Add product categories
								$product_categories = get_the_terms($product->get_id(), 'product_cat');
								if ($product_categories && !is_wp_error($product_categories)) {
									$item_name .= '<p class="bazo-event-card-category">';
									foreach ($product_categories as $category) {
										$item_name .= '<span class="bazo-event-category">' . esc_html($category->name) . '</span> ';
									}
									$item_name .= '</p>';
								}

								// Get the short description (excerpt) OR use full description
								$short_desc = $product->get_short_description();
								if ( ! $short_desc ) {
									$short_desc = $product->get_description();
								}
					
								if ( $short_desc ) {
									// Strip HTML and limit to 25 words
									$plain_text = wp_strip_all_tags( $short_desc );
									$words = preg_split( '/\s+/', $plain_text );
									if ( count( $words ) > 30 ) {
										$trimmed = implode( ' ', array_slice( $words, 0, 30 ) ) . '...';
									} else {
										$trimmed = $plain_text;
									}
					
									$item_name .= '<div class="tinvwl-event-description">' . esc_html( $trimmed ) . '</div>';
								}
							}
							
							echo $item_name; // WPCS: xss ok.

							echo apply_filters( 'tinvwl_wishlist_item_meta_data', tinv_wishlist_get_item_data( $product, $wl_product ), $wl_product, $product ); // WPCS: xss ok.
							?>
						</td>
						<?php if ( isset( $wishlist_table_row['colm_price'] ) && $wishlist_table_row['colm_price'] ) { ?>
							<td class="product-price">
								<?php
								echo apply_filters( 'tinvwl_wishlist_item_price', $product->get_price_html(), $wl_product, $product ); // WPCS: xss ok.
								?>
							</td>
						<?php } ?>
						<?php if ( isset( $wishlist_table_row['colm_date'] ) && $wishlist_table_row['colm_date'] ) { ?>
							<td class="product-date">
								<?php
								echo apply_filters( 'tinvwl_wishlist_item_date', sprintf( // WPCS: xss ok.
									'<time class="entry-date" datetime="%1$s">%2$s</time>', $wl_product['date'], mysql2date( get_option( 'date_format' ), $wl_product['date'] )
								), $wl_product, $product );
								?>
							</td>
						<?php } ?>
						<?php if ( isset( $wishlist_table_row['colm_stock'] ) && $wishlist_table_row['colm_stock'] ) { ?>
							<td class="product-stock">
								<?php
								$availability = (array) $product->get_availability();
								if ( ! array_key_exists( 'availability', $availability ) ) {
									$availability['availability'] = '';
								}
								if ( ! array_key_exists( 'class', $availability ) ) {
									$availability['class'] = '';
								}
								$availability_html = empty( $availability['availability'] ) ? '<p class="stock ' . esc_attr( $availability['class'] ) . '"><span><i class="ftinvwl ftinvwl-check"></i></span><span class="tinvwl-txt">' . esc_html__( 'In stock', 'ti-woocommerce-wishlist' ) . '</span></p>' : '<p class="stock ' . esc_attr( $availability['class'] ) . '"><span><i class="ftinvwl ftinvwl-' . ( ( 'out-of-stock' === esc_attr( $availability['class'] ) ? 'times' : 'check' ) ) . '"></i></span><span>' . wp_kses_post( $availability['availability'] ) . '</span></p>';

								echo apply_filters( 'tinvwl_wishlist_item_status', $availability_html, $availability['availability'], $wl_product, $product ); // WPCS: xss ok.
								?>
							</td>
						<?php } ?>
						<?php if ( isset( $wishlist_table_row['add_to_cart'] ) && $wishlist_table_row['add_to_cart'] ) { ?>
							<td class="product-action">
								<?php
								if ( apply_filters( 'tinvwl_wishlist_item_action_add_to_cart', $wishlist_table_row['add_to_cart'], $wl_product, $product ) ) {
									?>
									<button class="button alt" name="tinvwl-add-to-cart"
											value="<?php echo esc_attr( $wl_product['ID'] ); ?>"
											title="<?php echo esc_html( apply_filters( 'tinvwl_wishlist_item_add_to_cart', $wishlist_table_row['text_add_to_cart'], $wl_product, $product ) ); ?>">
										<i
											class="ftinvwl ftinvwl-shopping-cart"></i><span
											class="tinvwl-txt"><?php echo wp_kses_post( apply_filters( 'tinvwl_wishlist_item_add_to_cart', $wishlist_table_row['text_add_to_cart'], $wl_product, $product ) ); ?></span>
									</button>
								<?php } elseif ( apply_filters( 'tinvwl_wishlist_item_action_default_loop_button', $wishlist_table_row['add_to_cart'], $wl_product, $product ) ) {
									woocommerce_template_loop_add_to_cart();
								} ?>
							</td>
						<?php } ?>
					</tr>
					<?php
					do_action( 'tinvwl_wishlist_row_after', $wl_product, $product );
				} // End if().
			} // End foreach().
			// restore global product data.
			$product = $_product_tmp;
			// restore global post data.
			$post = $_post_tmp;
			?>
			<?php do_action( 'tinvwl_wishlist_contents_after' ); ?>
			</tbody>
			<?php /* <tfoot>
			<tr>
				<td colspan="100">
					<?php do_action( 'tinvwl_after_wishlist_table', $wishlist ); ?>
					<?php wp_nonce_field( 'tinvwl_wishlist_owner', 'wishlist_nonce' ); ?>
				</td>
			</tr>
			</tfoot> */ ?>
		</table>


		<table id="local-wishlist-item" class="tabcontent tinvwl-table-manage-list bazo__wishlist_table">
			<tbody>
			<?php do_action( 'tinvwl_wishlist_contents_before' ); ?>

			<?php

			global $product, $post;
			// store global product data.
			$_product_tmp = $product;
			// store global post data.
			$_post_tmp = $post;

			foreach ( $products as $wl_product ) {

				if ( empty( $wl_product['data'] ) ) {
					continue;
				}

				// Get the product object to check event type
				$product_temp = $wl_product['data'];
				
				// Only show products that are events
				if ( get_field('event_types', $product_temp->get_id()) !== 'local' ) {
					continue;
				}

				// override global product data.
				$product = apply_filters( 'tinvwl_wishlist_item', $wl_product['data'] );
				// override global post data.
				$post = get_post( $product->get_id() );

				unset( $wl_product['data'] );
				if ( $wl_product['quantity'] > 0 && apply_filters( 'tinvwl_wishlist_item_visible', true, $wl_product, $product ) ) {
					$product_url = apply_filters( 'tinvwl_wishlist_item_url', $product->get_permalink(), $wl_product, $product );
					do_action( 'tinvwl_wishlist_row_before', $wl_product, $product );
					?>
					<tr class="<?php echo esc_attr( apply_filters( 'tinvwl_wishlist_item_class', 'wishlist_item', $wl_product, $product ) ); ?>">
						<?php if ( isset( $wishlist_table['colm_checkbox'] ) && $wishlist_table['colm_checkbox'] ) { ?>
							<td class="product-cb">
								<?php
								echo apply_filters( 'tinvwl_wishlist_item_cb', sprintf( // WPCS: xss ok.
									'<input type="checkbox" name="wishlist_pr[]" class="input-checkbox" value="%d" title="%s">', esc_attr( $wl_product['ID'] ), __( 'Select for bulk action', 'ti-woocommerce-wishlist' )
								), $wl_product, $product );
								?>
							</td>
						<?php } ?>
						<td class="product-remove">
							<button type="submit" name="tinvwl-remove"
									value="<?php echo esc_attr( $wl_product['ID'] ); ?>"
									title="<?php _e( 'Remove', 'ti-woocommerce-wishlist' ) ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 19L19 5" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M19 19L5 5" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
							</button>
						</td>
						<td class="product-thumbnail">
							<?php
							$thumbnail = apply_filters( 'tinvwl_wishlist_item_thumbnail', $product->get_image('medium'), $wl_product, $product );

							if ( ! $product->is_visible() ) {
								echo $thumbnail; // WPCS: xss ok.
							} else {
								printf( '<a href="%s">%s</a>', esc_url( $product_url ), $thumbnail ); // WPCS: xss ok.
							}
							?>
						</td>
                        <td class="product-date">
                            <?php
                            // Assuming is an ACF field
                            $event_date = get_field('event_date', $product->get_id());
                            $event_time = get_field('event_time', $product->get_id());
                            ?>
                            <?php if ($event_date) : ?>
                                <p class="bazo-event-card-date"><?php echo esc_html($event_date); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($event_time) : ?>
                                <p class="bazo-event-card-date"><?php echo esc_html($event_time); ?></p>
                            <?php endif; ?>
						</td>
						<td class="product-name">
							<?php
							if ( ! $product->is_visible() ) {
								$item_name = apply_filters( 'tinvwl_wishlist_item_name', is_callable( array(
										$product,
										'get_name'
									) ) ? $product->get_name() : $product->get_title(), $wl_product, $product ) . '&nbsp;';
							} else {
								$item_name = apply_filters( 'tinvwl_wishlist_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_url ), is_callable( array(
									$product,
									'get_name'
								) ) ? $product->get_name() : $product->get_title() ), $wl_product, $product );
							}

							// Add product description below the name
							if ( $product ) {
								// Add product categories
								$product_categories = get_the_terms($product->get_id(), 'product_cat');
								if ($product_categories && !is_wp_error($product_categories)) {
									$item_name .= '<p class="bazo-event-card-category">';
									foreach ($product_categories as $category) {
										$item_name .= '<span class="bazo-event-category">' . esc_html($category->name) . '</span> ';
									}
									$item_name .= '</p>';
								}

								// Get the short description (excerpt) OR use full description
								$short_desc = $product->get_short_description();
								if ( ! $short_desc ) {
									$short_desc = $product->get_description();
								}
					
								if ( $short_desc ) {
									// Strip HTML and limit to 25 words
									$plain_text = wp_strip_all_tags( $short_desc );
									$words = preg_split( '/\s+/', $plain_text );
									if ( count( $words ) > 30 ) {
										$trimmed = implode( ' ', array_slice( $words, 0, 30 ) ) . '...';
									} else {
										$trimmed = $plain_text;
									}
					
									$item_name .= '<div class="tinvwl-event-description">' . esc_html( $trimmed ) . '</div>';
								}
							}
							
							echo $item_name; // WPCS: xss ok.

							echo apply_filters( 'tinvwl_wishlist_item_meta_data', tinv_wishlist_get_item_data( $product, $wl_product ), $wl_product, $product ); // WPCS: xss ok.
							?>
						</td>
						<?php if ( isset( $wishlist_table_row['colm_price'] ) && $wishlist_table_row['colm_price'] ) { ?>
							<td class="product-price">
								<?php
								echo apply_filters( 'tinvwl_wishlist_item_price', $product->get_price_html(), $wl_product, $product ); // WPCS: xss ok.
								?>
							</td>
						<?php } ?>
						<?php if ( isset( $wishlist_table_row['colm_date'] ) && $wishlist_table_row['colm_date'] ) { ?>
							<td class="product-date">
								<?php
								echo apply_filters( 'tinvwl_wishlist_item_date', sprintf( // WPCS: xss ok.
									'<time class="entry-date" datetime="%1$s">%2$s</time>', $wl_product['date'], mysql2date( get_option( 'date_format' ), $wl_product['date'] )
								), $wl_product, $product );
								?>
							</td>
						<?php } ?>
						<?php if ( isset( $wishlist_table_row['colm_stock'] ) && $wishlist_table_row['colm_stock'] ) { ?>
							<td class="product-stock">
								<?php
								$availability = (array) $product->get_availability();
								if ( ! array_key_exists( 'availability', $availability ) ) {
									$availability['availability'] = '';
								}
								if ( ! array_key_exists( 'class', $availability ) ) {
									$availability['class'] = '';
								}
								$availability_html = empty( $availability['availability'] ) ? '<p class="stock ' . esc_attr( $availability['class'] ) . '"><span><i class="ftinvwl ftinvwl-check"></i></span><span class="tinvwl-txt">' . esc_html__( 'In stock', 'ti-woocommerce-wishlist' ) . '</span></p>' : '<p class="stock ' . esc_attr( $availability['class'] ) . '"><span><i class="ftinvwl ftinvwl-' . ( ( 'out-of-stock' === esc_attr( $availability['class'] ) ? 'times' : 'check' ) ) . '"></i></span><span>' . wp_kses_post( $availability['availability'] ) . '</span></p>';

								echo apply_filters( 'tinvwl_wishlist_item_status', $availability_html, $availability['availability'], $wl_product, $product ); // WPCS: xss ok.
								?>
							</td>
						<?php } ?>
						<?php if ( isset( $wishlist_table_row['add_to_cart'] ) && $wishlist_table_row['add_to_cart'] ) { ?>
							<td class="product-action">
								<?php
								if ( apply_filters( 'tinvwl_wishlist_item_action_add_to_cart', $wishlist_table_row['add_to_cart'], $wl_product, $product ) ) {
									?>
									<button class="button alt" name="tinvwl-add-to-cart"
											value="<?php echo esc_attr( $wl_product['ID'] ); ?>"
											title="<?php echo esc_html( apply_filters( 'tinvwl_wishlist_item_add_to_cart', $wishlist_table_row['text_add_to_cart'], $wl_product, $product ) ); ?>">
										<i
											class="ftinvwl ftinvwl-shopping-cart"></i><span
											class="tinvwl-txt"><?php echo wp_kses_post( apply_filters( 'tinvwl_wishlist_item_add_to_cart', $wishlist_table_row['text_add_to_cart'], $wl_product, $product ) ); ?></span>
									</button>
								<?php } elseif ( apply_filters( 'tinvwl_wishlist_item_action_default_loop_button', $wishlist_table_row['add_to_cart'], $wl_product, $product ) ) {
									woocommerce_template_loop_add_to_cart();
								} ?>
							</td>
						<?php } ?>
					</tr>
					<?php
					do_action( 'tinvwl_wishlist_row_after', $wl_product, $product );
				} // End if().
			} // End foreach().
			// restore global product data.
			$product = $_product_tmp;
			// restore global post data.
			$post = $_post_tmp;
			?>
			<?php do_action( 'tinvwl_wishlist_contents_after' ); ?>
			</tbody>
			<?php /* <tfoot>
			<tr>
				<td colspan="100">
					<?php do_action( 'tinvwl_after_wishlist_table', $wishlist ); ?>
					<?php wp_nonce_field( 'tinvwl_wishlist_owner', 'wishlist_nonce' ); ?>
				</td>
			</tr>
			</tfoot> */ ?>
		</table>

		
	</form>
	<?php do_action( 'tinvwl_after_wishlist', $wishlist ); ?>
	<div class="tinv-lists-nav tinv-wishlist-clear">
		<?php do_action( 'tinvwl_pagenation_wishlist', $wishlist ); ?>
	</div>
</div>

<style>
	/* Style the tab */
	.tab {
	overflow: hidden;
	border: 1px solid #ccc;
	background-color: #f1f1f1;
	}

	/* Style the buttons that are used to open the tab content */
	.tab button {
	background-color: inherit;
	float: left;
	border: none;
	outline: none;
	cursor: pointer;
	padding: 14px 16px;
	transition: 0.3s;
	}

	/* Change background color of buttons on hover */
	.tab button:hover {
	background-color: #ddd;
	}

	/* Create an active/current tablink class */
	.tab button.active {
	background-color: #ccc;
	}

	/* Style the tab content */
	.tabcontent {
	display: none;
	padding: 6px 12px;
	border: 1px solid #ccc;
	border-top: none;
	}
</style>

<script>
	function openCity(evt, cityName) {
	// Declare all variables
	var i, tabcontent, tablinks;

	// Get all elements with class="tabcontent" and hide them
	tabcontent = document.getElementsByClassName("tabcontent");
	for (i = 0; i < tabcontent.length; i++) {
		tabcontent[i].style.display = "none";
	}

	// Get all elements with class="tablinks" and remove the class "active"
	tablinks = document.getElementsByClassName("tablinks");
	for (i = 0; i < tablinks.length; i++) {
		tablinks[i].className = tablinks[i].className.replace(" active", "");
	}

	// Show the current tab, and add an "active" class to the button that opened the tab
	document.getElementById(cityName).style.display = "block";
	evt.currentTarget.className += " active";
	}
</script>