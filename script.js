document.addEventListener('DOMContentLoaded', function() {
    // Countdown Timer
    function startTimer(duration) {
        let timer = duration;
        const hoursElement = document.getElementById('hours');
        const minutesElement = document.getElementById('minutes');
        const secondsElement = document.getElementById('seconds');

        const countdown = setInterval(function() {
            const hours = Math.floor(timer / 3600);
            const minutes = Math.floor((timer % 3600) / 60);
            const seconds = timer % 60;

            hoursElement.textContent = hours.toString().padStart(2, '0');
            minutesElement.textContent = minutes.toString().padStart(2, '0');
            secondsElement.textContent = seconds.toString().padStart(2, '0');

            if (--timer < 0) {
                clearInterval(countdown);
                const saleTimer = document.querySelector('.sale-timer');
                if (saleTimer) {
                    saleTimer.style.display = 'none';
                }
            }
        }, 1000);
    }

    // Start 5-hour countdown
    startTimer(5 * 60 * 60); // 5 hours in seconds

    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');

    // Define destination mappings
    const destinationMappings = {
        'greece': 'destinations/greece.html',
        'greek islands': 'destinations/greece.html',
        'santorini': 'destinations/greece.html',
        'mykonos': 'destinations/greece.html',
        'crete': 'destinations/greece.html',
        'croatia': 'destinations/croatia.html',
        'croatian coast': 'destinations/croatia.html',
        'dubrovnik': 'destinations/croatia.html',
        'split': 'destinations/croatia.html',
        'hvar': 'destinations/croatia.html',
        'maldives': 'destinations/maldives.html',
        'amalfi': 'destinations/amalfi.html',
        'amalfi coast': 'destinations/amalfi.html',
        'positano': 'destinations/amalfi.html',
        'capri': 'destinations/amalfi.html'
    };

    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        // If searching for "destinations" or "destination", navigate to destinations page
        if (searchTerm === 'destinations' || searchTerm === 'destination') {
            window.location.href = 'destinations.html';
            return;
        }
        
        // Check if the search term matches any destination
        for (const [key, value] of Object.entries(destinationMappings)) {
            if (searchTerm.includes(key)) {
                window.location.href = value;
                return;
            }
        }

        // If no exact match is found, show a message
        alert('Destination not found. Please try searching for: Greece, Croatia, Maldives, or Amalfi Coast');
    }

    // Search on button click
    if (searchButton) {
        searchButton.addEventListener('click', performSearch);
    }

    // Search on Enter key
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }

    // Remove real-time search as it was causing issues
    // searchInput.addEventListener('input', performSearch);
});

// Modal functionality
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = 'none';
    document.body.style.overflow = 'auto'; // Restore scrolling
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modals = document.getElementsByClassName('modal');
        for (let modal of modals) {
            if (modal.style.display === 'block') {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
    }
});

// Booking form functionality
function showBookingForm(formId) {
    // Close the destination modal first
    const destinationModal = document.querySelector('.modal:not(.booking-modal)');
    if (destinationModal) {
        destinationModal.style.display = 'none';
    }
    
    // Show the booking form modal
    const bookingModal = document.getElementById(formId);
    bookingModal.style.display = 'block';
}

// Reservation System
const reservations = {
    greece: {
        '2024-06-15': { total: 20, booked: 0 },
        '2024-07-10': { total: 20, booked: 0 },
        '2024-08-05': { total: 20, booked: 0 },
        '2024-09-01': { total: 20, booked: 0 }
    },
    croatia: {
        '2024-06-15': { total: 20, booked: 0 },
        '2024-07-10': { total: 20, booked: 0 },
        '2024-08-05': { total: 20, booked: 0 },
        '2024-09-01': { total: 20, booked: 0 }
    }
};

// Update availability display
function updateAvailabilityDisplay() {
    const dateSelects = document.querySelectorAll('select[name="trip-date"]');
    dateSelects.forEach(select => {
        const destination = select.closest('form').id.includes('greece') ? 'greece' : 'croatia';
        const options = select.options;
        
        for (let i = 1; i < options.length; i++) {
            const date = options[i].value;
            const availability = reservations[destination][date];
            const remaining = availability.total - availability.booked;
            
            if (remaining <= 0) {
                options[i].disabled = true;
                options[i].textContent = `${options[i].textContent} (Sold Out)`;
            } else {
                options[i].disabled = false;
                options[i].textContent = `${options[i].textContent} (${remaining} spots left)`;
            }
        }
    });
}

// Check availability before form submission
function checkAvailability(destination, date, guests) {
    const availability = reservations[destination][date];
    if (!availability) return false;
    
    const remaining = availability.total - availability.booked;
    return remaining >= guests;
}

// Update the payment button click handler
document.addEventListener('DOMContentLoaded', function() {
    const paymentButtons = document.querySelectorAll('.payment-button');
    
    paymentButtons.forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('form');
            const formData = new FormData(form);
            const bookingData = {};
            
            // Collect form data
            for (let [key, value] of formData.entries()) {
                bookingData[key] = value;
            }
            
            // Determine destination
            const destination = form.id.includes('greece') ? 'greece' : 'croatia';
            
            // Check availability
            if (!checkAvailability(destination, bookingData['trip-date'], parseInt(bookingData.guests))) {
                alert('Sorry, this date is no longer available for the selected number of guests. Please choose a different date or reduce the number of guests.');
                return;
            }
            
            // Update reservations
            reservations[destination][bookingData['trip-date']].booked += parseInt(bookingData.guests);
            updateAvailabilityDisplay();
            
            const isCardPayment = this.classList.contains('card-payment');
            
            if (isCardPayment) {
                // Simulate redirect to payment gateway
                alert('Redirecting to payment gateway...');
                // Here you would normally redirect to your payment processor
            } else {
                // Show office payment confirmation
                alert(`Booking confirmed! Please visit our office to complete the payment.\n\nBooking details:\nName: ${bookingData.fullname}\nEmail: ${bookingData.email}\nPhone: ${bookingData.phone}\nDate: ${bookingData['trip-date']}\nGuests: ${bookingData.guests}`);
            }
            
            // Close the modal
            this.closest('.modal').style.display = 'none';
            document.body.style.overflow = 'auto';
        });
    });
    
    // Initialize availability display
    updateAvailabilityDisplay();
});

// Authentication Functions
function switchTab(tab) {
    // Update tabs
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.auth-tab[onclick="switchTab('${tab}')"]`).classList.add('active');

    // Update forms
    document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
    document.getElementById(`${tab}-form`).classList.add('active');
}

// Handle form submissions
document.addEventListener('DOMContentLoaded', function() {
    // Login form submission
    const loginForm = document.querySelector('#login-form form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            const rememberMe = document.querySelector('#login-form input[type="checkbox"]').checked;

            // Here you would typically make an API call to your backend
            console.log('Login attempt:', { email, password, rememberMe });
            
            // For demo purposes, show success message
            alert('Login successful!');
        });
    }

    // Register form submission
    const registerForm = document.querySelector('#register-form form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('register-name').value;
            const email = document.getElementById('register-email').value;
            const password = document.getElementById('register-password').value;
            const confirmPassword = document.getElementById('register-confirm-password').value;

            // Basic validation
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }

            // Here you would typically make an API call to your backend
            console.log('Register attempt:', { name, email, password });
            
            // For demo purposes, show success message
            alert('Registration successful!');
        });
    }

    // Social login handlers
    document.querySelectorAll('.social-button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const platform = this.classList.contains('google') ? 'Google' : 'Facebook';
            console.log(`${platform} login clicked`);
            // Here you would implement the social login logic
        });
    });
}); 