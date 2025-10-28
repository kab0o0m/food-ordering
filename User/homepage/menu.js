// Add smooth scrolling for navigation
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

// Order button click handler
document.querySelectorAll('.order-now-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const itemTitle = this.closest('.menu-item').querySelector('.menu-item-title').textContent;
        alert(`Adding "${itemTitle}" to cart...`);
    });
});

// Store locator functionality
document.getElementById('store-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        alert('Setting store location: ' + this.value);
    }
});