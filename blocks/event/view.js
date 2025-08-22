document.addEventListener('DOMContentLoaded', function () {
    // Select all instances of the event grid block
    const eventGrids = document.querySelectorAll('.wp-block-bazo-event-grid');

    eventGrids.forEach(gridBlock => {
        const gridContainer = gridBlock.querySelector('.bazo-event-grid');
        const loadMoreBtnContainer = gridBlock.querySelector('.bazo-event-load-more');
        const loadMoreBtn = gridBlock.querySelector('.bazo-load-more-button');
        const filterButtons = gridBlock.querySelectorAll('.bazo-event-filter-button');
        const filterIcon = gridBlock.querySelector('.bazo-event-filter-icon-button');
        const filtersContainer = gridBlock.querySelector('.bazo-event-filters');
        const filterCloseBtn = gridBlock.querySelector('.bazo-event-filter-close');
        const loader = gridBlock.querySelector('.bazo-event-loader');

        // Initial settings from render.php data attributes
        let page = 1;
        const postsToShow = gridBlock.getAttribute('data-posts-to-show');
        const postType = gridBlock.getAttribute('data-post-type');
        const taxonomy = gridBlock.getAttribute('data-taxonomy');
        const showLoader = JSON.parse(gridBlock.getAttribute('data-show-loader') || 'true');
        const showAds = JSON.parse(gridBlock.getAttribute('data-show-ads') || 'true');
        const adPositions = JSON.parse(gridBlock.getAttribute('data-ad-positions') || '[]');
        const placeholderUrl = gridBlock.getAttribute('data-placeholder-url') || '/wp-content/themes/bazo/assets/images/placeholder.png';
        // selectedCategories will now contain numbers (term_ids)
        let selectedCategories = JSON.parse(gridBlock.getAttribute('data-selected-categories') || '[]');

        // maxPages will be updated on every fetch based on API response headers
        let maxPages = loadMoreBtnContainer ? parseInt(loadMoreBtnContainer.getAttribute('data-max-pages')) : 1;

        // Function to show/hide loader
        function showLoaderElement() {
            if (loader && showLoader) {
                loader.style.display = 'flex';
            }
        }

        function hideLoaderElement() {
            if (loader && showLoader) {
                loader.style.display = 'none';
            }
        }

        // Function to update the visibility of the Load More button
        function updateLoadMoreButtonVisibility() {
            if (loadMoreBtn) {
                if (page >= maxPages) {
                    loadMoreBtn.style.display = 'none';
                } else {
                    loadMoreBtn.style.display = 'block';
                }
            }
        }

        // Function to update filter button active states
        function updateFilterButtonStates() {
            filterButtons.forEach(btn => {
                const term = btn.getAttribute('data-term');
                if (term === 'all') {
                    btn.classList.toggle('active', selectedCategories.length === 0);
                } else {
                    btn.classList.toggle('active', selectedCategories.includes(parseInt(term)));
                }
            });
        }

        // Function to create ad block element
        function createAdBlock(ad) {
            const adBlock = document.createElement('div');
            adBlock.className = `bazo-ad-block bazo-ad-span-${ad.span}`;
            adBlock.innerHTML = `
                <div class="bazo-ad-content">
                    <span class="bazo-ad-text">${ad.text}</span>
                </div>
            `;
            return adBlock;
        }

        // Function to insert ads at correct positions
        function insertAdsAtPositions(posts, startPosition = 0) {
            if (!showAds || adPositions.length === 0) {
                return posts;
            }

            const postsWithAds = [...posts];
            let adOffset = 0;

            adPositions.forEach(ad => {
                const adjustedPosition = ad.position - startPosition;
                if (adjustedPosition > 0 && adjustedPosition <= postsWithAds.length) {
                    const adBlock = createAdBlock(ad);
                    postsWithAds.splice(adjustedPosition + adOffset, 0, adBlock);
                    adOffset++;
                }
            });

            return postsWithAds;
        }

        function fetchEvents(reset = false) {
            // Show loader at the start of fetch
            showLoaderElement();
            
            // Use the 'taxonomy' variable to build the URL dynamically
            let url = `/wp-json/wp/v2/${postType}?per_page=${postsToShow}&page=${page}`;
            if (selectedCategories.length) {
                // Use the dynamic taxonomy for the filter parameter
                url += `&${taxonomy}=${selectedCategories.join(',')}`;
            }

            fetch(url)
                .then(res => {
                    // Update maxPages from the API response header
                    maxPages = res.headers.get('X-WP-TotalPages') ? parseInt(res.headers.get('X-WP-TotalPages')) : 1;
                    return res.json();
                })
                .then(data => {
                    if (reset) {
                        gridContainer.innerHTML = '';
                    }

                    if (data.length === 0) {
                        if (reset) {
                            gridContainer.innerHTML = '<p>No events found.</p>';
                        }
                    } else {
                        const posts = data.map(post => {
                            // console.log('Fetched data:', post.wishlist_html);
                            const box = document.createElement('div');
                            box.className = 'bazo-event-card';

                            // Get category names from taxonomy (assume they are passed as `post.taxonomy_terms`)
                            const categories = post.taxonomy_terms && Array.isArray(post.taxonomy_terms)
                                ? post.taxonomy_terms.map(term => `<span class="bazo-event-category">${term.name}</span>`).join(' ')
                                : '';

                            // Get trimmed excerpt (first 10 words)
                            const excerptText = post.excerpt && post.excerpt.rendered
                                ? post.excerpt.rendered.replace(/(<([^>]+)>)/gi, '') // strip HTML
                                : '';
                            const trimmedExcerpt = excerptText.split(/\s+/).slice(0, 10).join(' ') + (excerptText.split(/\s+/).length > 10 ? '...' : '');

                            box.innerHTML = `
                                <a href="${post.link}">
                                    <div class="bazo-event-card-image">
                                        ${post.featured_media_url ? `<img src="${post.featured_media_url}" alt=""/>` : `<img src="${placeholderUrl}" alt="Event placeholder" />`}
                                    </div>
                                    <div class="bazo-event-card-content">
                                        <p class="bazo-event-card-category">${categories}</p>
                                        <h3>${post.title.rendered}</h3>
                                        ${post.event_date ? `<p class="bazo-event-card-date">${post.event_date}</p>` : ''}
                                        ${post.event_time ? `<p class="bazo-event-card-date">${post.event_time}</p>` : ''}
                                        <samp class="bazo-event-card-short-description">${trimmedExcerpt}</samp>
                                    </div>
                                </a>
                                <div class="wishlist-wrap">
                                    ${post.wishlist_html ? post.wishlist_html : ''}
                                </div>
                            `;

                            return box;
                        });

                        // Insert ads at correct positions
                        const postsWithAds = insertAdsAtPositions(posts, reset ? 0 : (page - 1) * postsToShow);

                        // Append all elements to the grid
                        postsWithAds.forEach(element => {
                            gridContainer.appendChild(element);
                        });

                        // Initialize wishlist functionality for new elements
                        if (typeof tinvwl_add_to_wishlist === 'function') {
                            tinvwl_add_to_wishlist();
                        }
                    }

                    // Call this function after every fetch to update the button's visibility
                    updateLoadMoreButtonVisibility();
                    
                    // Hide loader after successful fetch
                    hideLoaderElement();
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    // Hide loader on error too
                    hideLoaderElement();
                });
        }

        // Initial call to set button visibility on page load
        updateLoadMoreButtonVisibility();
        
        // Initial call to set filter button states based on selected categories
        updateFilterButtonStates();
        
        // Show initial loader if enabled
        if (showLoader && loader) {
            showLoaderElement();
            // Hide loader after a short delay to show it was working
            setTimeout(() => {
                hideLoaderElement();
            }, 500);
        }

        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function () {
                page++;
                fetchEvents();
            });
        }
        
        // Event listener for the filter icon button
        if (filterIcon && filtersContainer) {
            filterIcon.addEventListener('click', function () {
                filtersContainer.classList.toggle('is-visible');
            });
        }

        // Event listener for the close button
        if (filterCloseBtn && filtersContainer) {
            filterCloseBtn.addEventListener('click', function () {
                filtersContainer.classList.remove('is-visible');
            });
        }

        filterButtons.forEach(button => {
            button.addEventListener('click', function () {
                const term = this.getAttribute('data-term'); // Gets the term_id as a string

                if (term === 'all') {
                    // Show all posts from selected categories (or all if none selected)
                    selectedCategories = JSON.parse(gridBlock.getAttribute('data-selected-categories') || '[]');
                } else {
                    // Show only posts from the specific clicked category
                    selectedCategories = [parseInt(term)];
                }

                // Update filter button states
                updateFilterButtonStates();

                // Hide the filters popup after selection
                if (filtersContainer) {
                    filtersContainer.classList.remove('is-visible');
                }

                page = 1; // Reset page to 1 for new filter
                fetchEvents(true); // Fetch new data and reset grid
            });
        });
    });
});