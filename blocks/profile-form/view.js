document.addEventListener('DOMContentLoaded', function() {
    // Initialize state dropdown only if WooCommerce is active
    if (window.bazoProfileFormData && window.bazoProfileFormData.woocommerceActive) {
        const countrySelect = document.getElementById('country-select');
        const stateSelect = document.getElementById('state-select');
        
        function populateStates(countryCode, selectedState = '') {
            if (!countryCode || !window.bazoProfileFormData.states[countryCode]) {
                stateSelect.innerHTML = '<option value="" disabled>Select country first</option>';
                return;
            }
            
            const states = window.bazoProfileFormData.states[countryCode];
            let options = '<option value="" disabled>Select state</option>';
            
            for (const [code, name] of Object.entries(states)) {
                options += `<option value="${code}" ${selectedState === code ? 'selected' : ''}>${name}</option>`;
            }
            
            stateSelect.innerHTML = options;
        }
        
        if (countrySelect && stateSelect) {
            countrySelect.addEventListener('change', function() {
                populateStates(this.value);
            });
            
            // Initialize with user's country if set
            if (countrySelect.value) {
                populateStates(countrySelect.value, window.bazoProfileFormData.savedState);
            }
        }
    }
    
    // Handle profile image upload
    const imageUpload = document.getElementById('profile-image-upload');
    if (imageUpload) {
        imageUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                showImageMessage('Please select a valid image file', 'error');
                return;
            }
            
            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                showImageMessage('Image file size must be less than 5MB', 'error');
                return;
            }
            
            const preview = document.getElementById('profile-image-preview');
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                // Store the file for later upload with form submission
                preview.dataset.hasNewImage = 'true';
            };
            
            reader.readAsDataURL(file);
        });
    }
    
    // Handle form submission
    const profileForm = document.getElementById('bazo-profile-form');
    if (profileForm) {
        // Add real-time validation
        const firstNameInput = profileForm.querySelector('[name="first_name"]');
        const lastNameInput = profileForm.querySelector('[name="last_name"]');
        const emailInput = profileForm.querySelector('[name="user_email"]');
        const passwordInput = profileForm.querySelector('[name="password"]');
        const passwordConfirmInput = profileForm.querySelector('[name="password_confirm"]');
        
        // Real-time validation for first name
        if (firstNameInput) {
            firstNameInput.addEventListener('blur', function() {
                const value = this.value.trim();
                if (!value) {
                    showFieldMessage('first_name', 'First name is required', 'error');
                } else {
                    showFieldMessage('first_name', '', 'success');
                }
            });
            
            // Clear message when user starts typing
            firstNameInput.addEventListener('input', function() {
                if (this.value.trim()) {
                    showFieldMessage('first_name', '', 'success');
                }
            });
        }
        
        // Real-time validation for last name
        if (lastNameInput) {
            lastNameInput.addEventListener('blur', function() {
                const value = this.value.trim();
                if (!value) {
                    showFieldMessage('last_name', 'Last name is required', 'error');
                } else {
                    showFieldMessage('last_name', '', 'success');
                }
            });
            
            // Clear message when user starts typing
            lastNameInput.addEventListener('input', function() {
                if (this.value.trim()) {
                    showFieldMessage('last_name', '', 'success');
                }
            });
        }
        
        // Real-time validation for email
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                const value = this.value.trim();
                if (!value) {
                    showFieldMessage('user_email', 'Email is required', 'error');
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    showFieldMessage('user_email', 'Please enter a valid email address', 'error');
                } else {
                    showFieldMessage('user_email', '', 'success');
                }
            });
            
            // Clear message when user starts typing
            emailInput.addEventListener('input', function() {
                const value = this.value.trim();
                if (value && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    showFieldMessage('user_email', '', 'success');
                }
            });
        }
        
        // Real-time validation for password confirmation
        if (passwordInput && passwordConfirmInput) {
            // Clear messages when users start typing in password fields
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const confirmPassword = passwordConfirmInput.value;
                
                // Clear error messages when user starts typing
                if (password && password.length >= 6) {
                    if (confirmPassword && password === confirmPassword) {
                        showFieldMessage('password', 'Passwords match', 'success');
                    } else if (confirmPassword) {
                        showFieldMessage('password', 'Passwords do not match', 'error');
                    } else {
                        showFieldMessage('password', '', 'success');
                    }
                } else if (password && password.length < 6) {
                    showFieldMessage('password', 'Password must be at least 6 characters long', 'error');
                } else {
                    showFieldMessage('password', '', '');
                }
            });
            
            passwordConfirmInput.addEventListener('input', function() {
                const password = passwordInput.value;
                const confirmPassword = this.value;
                
                // Clear error messages when user starts typing
                if (password && confirmPassword) {
                    if (password.length < 6) {
                        showFieldMessage('password', 'Password must be at least 6 characters long', 'error');
                    } else if (password === confirmPassword) {
                        showFieldMessage('password', 'Passwords match', 'success');
                    } else {
                        showFieldMessage('password', 'Passwords do not match', 'error');
                    }
                } else {
                    showFieldMessage('password', '', '');
                }
            });
            
            passwordConfirmInput.addEventListener('blur', function() {
                const password = passwordInput.value;
                const confirmPassword = this.value;
                
                // Clear message if both fields are empty
                if (!password && !confirmPassword) {
                    showFieldMessage('password', '', '');
                    return;
                }
                
                // Only validate if both passwords are entered
                if (password && confirmPassword) {
                    if (password !== confirmPassword) {
                        showFieldMessage('password', 'Passwords do not match', 'error');
                    } else if (password.length < 6) {
                        showFieldMessage('password', 'Password must be at least 6 characters long', 'error');
                    } else {
                        showFieldMessage('password', 'Passwords match', 'success');
                    }
                } else if (password && password.length < 6) {
                    // Only show length error if password is entered but too short
                    showFieldMessage('password', 'Password must be at least 6 characters long', 'error');
                } else {
                    // Clear message if conditions aren't met
                    showFieldMessage('password', '', '');
                }
            });
        }
        
        profileForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitButton = this.querySelector('.submit-button');
            const buttonText = submitButton.querySelector('.button-text');
            const spinner = submitButton.querySelector('.spinner');
            const formMessage = this.querySelector('.form-message-global');
            
            // Reset UI and clear all field messages
            formMessage.textContent = '';
            formMessage.className = 'form-message-global';
            this.querySelectorAll('[class^="form-message-"]').forEach(msg => {
                msg.textContent = '';
                msg.className = msg.className.replace(/\s+(success|error)/g, '');
            });
            clearImageMessage(); // Clear image message on form submission
            
            submitButton.disabled = true;
            buttonText.textContent = 'Updating...';
            spinner.style.display = 'inline-block';
            
            try {
                // Validate form
                const firstName = this.querySelector('[name="first_name"]').value.trim();
                if (!firstName) {
                    showFieldMessage('first_name', 'First name is required', 'error');
                    return; // Stop form submission
                }
                
                const lastName = this.querySelector('[name="last_name"]').value.trim();
                if (!lastName) {
                    showFieldMessage('last_name', 'Last name is required', 'error');
                    return; // Stop form submission
                }
                
                const email = this.querySelector('[name="user_email"]').value;
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    showFieldMessage('user_email', 'Please enter a valid email address', 'error');
                    return; // Stop form submission
                }
                
                const password = this.querySelector('[name="password"]').value;
                const passwordConfirm = this.querySelector('[name="password_confirm"]').value;
                
                // Only validate passwords if at least one is entered
                if (password || passwordConfirm) {
                    if (password && password.length < 6) {
                        showFieldMessage('password', 'Password must be at least 6 characters long', 'error');
                        return; // Stop form submission without throwing error
                    }
                    
                    if (password && passwordConfirm && password !== passwordConfirm) {
                        showFieldMessage('password', 'Passwords do not match', 'error');
                        return; // Stop form submission without throwing error
                    }
                }
                
                // Prepare form data
                const formData = new FormData(this);
                formData.append('action', 'bazo_profile_update');
                formData.append('security', window.bazoProfileFormData.nonce);
                
                // Add profile image if a new one was selected
                const imageUpload = document.getElementById('profile-image-upload');
                if (imageUpload && imageUpload.files[0]) {
                    formData.append('profile_image', imageUpload.files[0]);
                }
                
                // Send request
                const response = await fetch(window.bazoProfileFormData.ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error('Network error');
                }
                
                const result = await response.json();
                
                if (result.success) {
                    // Success
                    showMessage(result.data.message || 'Profile updated successfully!', 'success');
                    // Update the image preview with the new URL
                    if (result.data && result.data.image_url) {
                        const preview = document.getElementById('profile-image-preview');
                        if (preview) {
                            preview.src = result.data.image_url;
                        }
                    }
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    // Error from server - only show unique errors
                    const uniqueErrors = [];
                    if (Array.isArray(result.data)) {
                        result.data.forEach(error => {
                            // Check for field-specific errors
                            if (error.includes('First name')) {
                                showFieldMessage('first_name', error, 'error');
                            } else if (error.includes('Last name')) {
                                showFieldMessage('last_name', error, 'error');
                            } else if (error.includes('email')) {
                                showFieldMessage('user_email', error, 'error');
                            } else if (error.includes('Password')) {
                                showFieldMessage('password', error, 'error');
                            } else if (error.includes('Phone')) {
                                showFieldMessage('billing_phone', error, 'error');
                            } else if (error.includes('file') || error.includes('image') || error.includes('Image') || error.includes('size')) {
                                // Show image-related errors in the profile image container
                                showImageMessage(error, 'error');
                            } else {
                                // Only add general errors if not already shown as field-specific
                                if (!uniqueErrors.includes(error)) {
                                    uniqueErrors.push(error);
                                }
                            }
                        });
                        
                        // Show unique general errors
                        if (uniqueErrors.length > 0) {
                            showMessage(uniqueErrors.join('\n'), 'error');
                        }
                    } else {
                        throw new Error(result.data || 'Failed to update profile');
                    }
                }
            } catch (error) {
                // Only handle actual errors (network, unexpected)
                console.error('Profile update error:', error);
                showMessage('An unexpected error occurred. Please try again.', 'error');
            } finally {
                // Reset button
                submitButton.disabled = false;
                buttonText.textContent = 'Update Profile';
                spinner.style.display = 'none';
            }
        });
    }
    
    function showMessage(message, type = 'success') {
        const formMessage = document.querySelector('.form-message-global');
        if (formMessage) {
            formMessage.textContent = message;
            formMessage.className = `form-message-global ${type}`;
        }
    }
    
    function showFieldMessage(fieldName, message, type = 'error') {
        const fieldMessage = document.querySelector(`.form-message-${fieldName}`);
        const inputField = document.querySelector(`[name="${fieldName}"]`);
        
        if (fieldMessage) {
            fieldMessage.textContent = message;
            // Clear all classes and add the base class
            fieldMessage.className = `form-message-${fieldName}`;
            // Add type class only if there's a message and type
            if (message && type) {
                fieldMessage.classList.add(type);
            }
        }
        
        // Update input field styling
        if (inputField) {
            // Remove existing error/success classes
            inputField.classList.remove('error', 'success');
            // Add appropriate class if there's a message
            if (message && type) {
                inputField.classList.add(type);
            }
        }
    }

    function showImageMessage(message, type = 'success') {
        const imageMessageContainer = document.querySelector('.form-message-profile-image');
        if (imageMessageContainer) {
            imageMessageContainer.textContent = message;
            imageMessageContainer.className = `form-message-profile-image ${type}`;
        }
    }
    
    function clearImageMessage() {
        const imageMessageContainer = document.querySelector('.form-message-profile-image');
        if (imageMessageContainer) {
            imageMessageContainer.textContent = '';
            imageMessageContainer.className = 'form-message-profile-image';
        }
    }
});