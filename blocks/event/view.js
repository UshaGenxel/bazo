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

        // Initial settings from render.php data attributes
        let page = 1;
        const postsToShow = gridBlock.getAttribute('data-posts-to-show');
        const postType = gridBlock.getAttribute('data-post-type');
        const taxonomy = gridBlock.getAttribute('data-taxonomy');
        // selectedCategories will now contain numbers (term_ids)
        let selectedCategories = JSON.parse(gridBlock.getAttribute('data-selected-categories') || '[]');

        // maxPages will be updated on every fetch based on API response headers
        let maxPages = loadMoreBtnContainer ? parseInt(loadMoreBtnContainer.getAttribute('data-max-pages')) : 1;

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

        function fetchEvents(reset = false) {
            // Use the 'taxonomy' variable to build the URL dynamically
            let url = `/bazo/wp-json/wp/v2/${postType}?per_page=${postsToShow}&page=${page}`;
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
                        data.forEach(post => {
                            console.log('Fetched data:', post);
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
                                    ${post.featured_media_url ? `<div class="bazo-event-card-image"><img src="${post.featured_media_url}" alt=""/></div>` : ''}
                                    <div class="bazo-event-card-content">
                                        <p class="bazo-event-card-category">${categories}</p>
                                        <h3>${post.title.rendered}</h3>
                                        ${post.event_date ? `<p class="bazo-event-card-date">${post.event_date}</p>` : ''}
                                        ${post.event_time ? `<p class="bazo-event-card-date">${post.event_time}</p>` : ''}
                                        <samp class="bazo-event-card-short-description">${trimmedExcerpt}</samp>
                                    </div>
                                </a>
                            `;

                            gridContainer.appendChild(box);
                        });
                    }

                    // Call this function after every fetch to update the button's visibility
                    updateLoadMoreButtonVisibility();
                })
                .catch(error => console.error('Error fetching events:', error));
        }

        // Initial call to set button visibility on page load
        updateLoadMoreButtonVisibility();
        
        // Initial call to set filter button states based on selected categories
        updateFilterButtonStates();

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

                page = 1; // Reset page to 1 for new filter
                fetchEvents(true); // Fetch new data and reset grid
            });
        });
    });
});