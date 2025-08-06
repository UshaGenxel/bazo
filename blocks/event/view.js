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
                    console.log('Fetched data:', data);

                    if (data.length === 0) {
                        if (reset) {
                            gridContainer.innerHTML = '<p>No events found.</p>';
                        }
                    } else {
                        data.forEach(post => {
                            const box = document.createElement('div');
                            box.className = 'bazo-event-card';
                            box.innerHTML = `
                                <a href="${post.link}">
                                    ${post.featured_media_url ? `<div class="bazo-event-card-image"><img src="${post.featured_media_url}" alt=""/></div>` : ''}
                                    <div class="bazo-event-card-content">
                                        <h3>${post.title.rendered}</h3>
                                        <p class="bazo-event-card-date">${post.event_date ? post.event_date : ''}</p>
                                        <div class="bazo-event-card-excerpt">${post.excerpt && post.excerpt.rendered ? post.excerpt.rendered : ''}</div>
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

                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                if (term === 'all') {
                    selectedCategories = [];
                } else {
                    selectedCategories = [parseInt(term)]; // Parse term_id as an integer
                }

                page = 1; // Reset page to 1 for new filter
                fetchEvents(true); // Fetch new data and reset grid
            });
        });
    });
});