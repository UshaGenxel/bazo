document.addEventListener('DOMContentLoaded', () => {
    // Get references to all modal elements and buttons
    const openLoginModalBtn = document.getElementById('open-login-modal');
    const openMyAccountModalBtn = document.getElementById('open-my-account-modal-btn');
    
    const authModal = document.getElementById('auth-modal');
    const forgotPasswordModal = document.getElementById('forgot-password-modal');
    const myAccountModal = document.getElementById('my-account-modal');

    const allModals = [authModal, forgotPasswordModal, myAccountModal];
    
    const loginFormWrapper = document.getElementById('login-form-wrapper');
    const signupFormWrapper = document.getElementById('signup-form-wrapper');

    const showSignupBtn = document.getElementById('show-signup');
    const showLoginBtn = document.getElementById('show-login');
    const showForgotPasswordBtn = document.getElementById('show-forgot-password');
    const forgotToLoginBtn = document.getElementById('forgot-to-login');

    // Form elements
    const loginForm = document.getElementById('login-form');
    const signupForm = document.getElementById('signup-form');
    const forgotPasswordForm = document.getElementById('forgot-password-form');
    const logoutBtn = document.querySelector('.logout-btn');

    // Message areas
    const loginMessage = document.getElementById('login-message');
    const signupMessage = document.getElementById('signup-message');
    const forgotPasswordMessage = document.getElementById('forgot-password-message');

    // Function to show a specific modal
    const showModal = (modalElement) => {
        // First, hide all other modals
        allModals.forEach(m => {
            if (m !== modalElement) {
                hideModal(m);
            }
        });

        modalElement.classList.remove('is-hidden');
        modalElement.classList.add('is-visible');
    };

    // Function to hide a specific modal
    const hideModal = (modalElement) => {
        modalElement.classList.remove('is-visible');
        modalElement.classList.add('is-hidden');
    };

    // Function to display a message
    const showMessage = (element, message, type = 'error') => {
        element.textContent = message;
        element.style.color = type === 'error' ? 'red' : 'green';
        element.style.padding = '0.5rem';
        element.style.textAlign = 'center';
        element.style.borderRadius = '0.5rem';
        element.style.marginBottom = '1rem';
        element.style.backgroundColor = type === 'error' ? '#fecaca' : '#dcfce7';
    };

    // Event listeners for opening modals
    if (openLoginModalBtn) {
      openLoginModalBtn.addEventListener('click', () => showModal(authModal));
    }
    if (openMyAccountModalBtn) {
      openMyAccountModalBtn.addEventListener('click', () => showModal(myAccountModal));
    }

    // Event listeners for closing modals
    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', (e) => {
            const modalToClose = e.target.closest('.modal-container');
            if (modalToClose) {
                hideModal(modalToClose);
            }
        });
    });

    // Event listeners for modal toggles
    if (showSignupBtn) {
      showSignupBtn.addEventListener('click', (e) => {
          e.preventDefault();
          loginFormWrapper.classList.add('hidden');
          signupFormWrapper.classList.remove('hidden');
      });
    }

    if (showLoginBtn) {
      showLoginBtn.addEventListener('click', (e) => {
          e.preventDefault();
          signupFormWrapper.classList.add('hidden');
          loginFormWrapper.classList.remove('hidden');
      });
    }

    if (showForgotPasswordBtn) {
      showForgotPasswordBtn.addEventListener('click', (e) => {
          e.preventDefault();
          hideModal(authModal);
          setTimeout(() => showModal(forgotPasswordModal), 300); // Wait for auth modal to fade out
      });
    }

    if (forgotToLoginBtn) {
      forgotToLoginBtn.addEventListener('click', (e) => {
          e.preventDefault();
          hideModal(forgotPasswordModal);
          setTimeout(() => showModal(authModal), 300); // Wait for password modal to fade out
      });
    }
    
    // Close modal when clicking outside the content
    document.querySelectorAll('.modal-container').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                hideModal(modal);
            }
        });
    });

    // AJAX Submission Handlers
    loginForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(loginForm);
        
        fetch(bazo_myaccount_ajax.ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(loginMessage, data.data.message, 'success');
                setTimeout(() => {
                    window.location.reload(); // Reload the page on successful login
                }, 1000);
            } else {
                showMessage(loginMessage, data.data, 'error');
            }
        })
        .catch(error => {
            showMessage(loginMessage, 'An error occurred. Please try again.', 'error');
        });
    });

    signupForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(signupForm);

        // Basic password check
        if (formData.get('user_password') !== formData.get('confirm_password')) {
            showMessage(signupMessage, 'Passwords do not match.', 'error');
            return;
        }

        fetch(bazo_myaccount_ajax.ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(signupMessage, data.data.message, 'success');
                setTimeout(() => {
                    window.location.reload(); // Reload the page on successful signup
                }, 1000);
            } else {
                showMessage(signupMessage, data.data, 'error');
            }
        })
        .catch(error => {
            showMessage(signupMessage, 'An error occurred. Please try again.', 'error');
        });
    });

    forgotPasswordForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(forgotPasswordForm);
        
        fetch(bazo_myaccount_ajax.ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(forgotPasswordMessage, data.data.message, 'success');
            } else {
                showMessage(forgotPasswordMessage, data.data, 'error');
            }
        })
        .catch(error => {
            showMessage(forgotPasswordMessage, 'An error occurred. Please try again.', 'error');
        });
    });
    
    // Handle Logout
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.href = bazo_myaccount_ajax.logoutUrl; // FIX: Changed to use the localized URL
        });
    }
});
