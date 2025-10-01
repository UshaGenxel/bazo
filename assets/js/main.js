jQuery(document).ready(function($) {
    $('.bazo-event-card-wishlist-button').on('click', function(e) {
        e.preventDefault(); // prevent default link behavior if it's <a>
        $('#open-login-modal').trigger('click');
    });
});