function selectService(service) {
    const deliveryBtn = document.querySelector('.toggle-btn.delivery');
    const pickupBtn = document.querySelector('.toggle-btn.pickup');

    if (service === 'delivery') {
        alert('Delivery service selected! Enter your address to start ordering.');
    } else {
        alert('Pickup service selected! Choose your nearest location.');
    }
}

// Add click handlers for order buttons
document.querySelectorAll('.order-btn, .deal-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
        if (!e.target.closest('.hero-large') && !e.target.closest('.promo-card')) {
            alert('Redirecting to menu page...');
        }
    });
});