// Remove Deal functionality
document.querySelector('.remove-deal-btn').addEventListener('click', function() {
    if (confirm('Are you sure you want to remove this deal?')) {
        document.querySelector('.deal-card').remove();
        updateCartTotal();
    }
});

// Remove item functionality
document.querySelectorAll('.action-btn.remove').forEach(btn => {
    btn.addEventListener('click', function() {
        const cartItem = this.closest('.cart-item');
        const itemName = cartItem.querySelector('.item-name').textContent;
        
        if (confirm(`Remove ${itemName} from cart?`)) {
            cartItem.remove();
            updateCartTotal();
        }
    });
});

// Edit item functionality
document.querySelectorAll('.action-btn.edit').forEach(btn => {
    btn.addEventListener('click', function() {
        const itemName = this.closest('.cart-item').querySelector('.item-name').textContent;
        alert(`Editing ${itemName}...`);
    });
});

// Swap item functionality
document.querySelectorAll('.action-btn.swap').forEach(btn => {
    btn.addEventListener('click', function() {
        const itemName = this.closest('.cart-item').querySelector('.item-name').textContent;
        alert(`Swapping ${itemName}...`);
    });
});

// Add item buttons
document.querySelectorAll('.add-item-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const itemType = this.textContent.includes('Pizza') ? 'Pizza' : 'Drink';
        alert(`Adding a ${itemType}...`);
    });
});

// Apply voucher
document.querySelector('.apply-btn').addEventListener('click', function() {
    const voucherCode = document.querySelector('.voucher-input').value;
    if (voucherCode) {
        alert(`Applying voucher: ${voucherCode}`);
    } else {
        alert('Please enter a voucher code');
    }
});

// Finish Order
document.querySelector('.checkout-btn').addEventListener('click', function() {
    // Redirect to checkout page
    window.location.href = 'checkout.html';
});

// Collapse sections
document.querySelectorAll('.collapse-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const section = this.closest('.voucher-section, .order-details-section');
        const content = section.querySelector('.voucher-input-group, .deal-card, .cart-items');
        
        if (content) {
            content.style.display = content.style.display === 'none' ? 'block' : 'none';
            this.textContent = content.style.display === 'none' ? '>' : '∨';
        }
    });
});

// Update cart total (placeholder function)
function updateCartTotal() {
    const itemCount = document.querySelectorAll('.cart-item').length;
    document.querySelector('.checkout-items').textContent = `${itemCount} items | $63.80`;
}

// Change delivery details
document.querySelector('.change-btn').addEventListener('click', function() {
    alert('Change delivery details...');
});
