document.addEventListener('DOMContentLoaded', function() {
    // Select all review sections
    const reviewSections = document.querySelectorAll('.wp-block-bazo-review-section');
    
    reviewSections.forEach(section => {
        const thumbsUpBtn = section.querySelector('.bazo-review-thumbs-up');
        const neutralBtn = section.querySelector('.bazo-review-neutral');
        const thumbsDownBtn = section.querySelector('.bazo-review-thumbs-down');
        const commentBtn = section.querySelector('.bazo-review-comment');
        const noteSection = section.querySelector('.bazo-review-note-section');
        
        // Handle action button clicks
        const actionButtons = [thumbsUpBtn, neutralBtn, thumbsDownBtn, commentBtn];
        actionButtons.forEach(btn => {
            if (btn) {
                btn.addEventListener('click', function() {
                    // Remove active class from all buttons
                    actionButtons.forEach(b => b.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Show note section if comment button is clicked
                    if (this === commentBtn && noteSection) {
                        noteSection.style.display = 'block';
                    } else if (noteSection) {
                        noteSection.style.display = 'none';
                    }
                    
                    // You can add AJAX call here to save the review
                    console.log('Action clicked:', this.className);
                });
            }
        });
        
        // Handle note textarea
        const noteTextarea = section.querySelector('.bazo-review-note-section textarea');
        if (noteTextarea) {
            noteTextarea.addEventListener('input', function() {
                // You can add AJAX call here to save the note
                console.log('Note updated:', this.value);
            });
        }
    });
});
