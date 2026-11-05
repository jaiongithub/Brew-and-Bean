// BREW & BEAN Client Interactions
document.addEventListener('DOMContentLoaded', function () {
    // Cart management using localStorage
    window.addToCart = function (productId, productName, price) {
        let cart = JSON.parse(localStorage.getItem('brew_cart') || '[]');
        let existing = cart.find(item => item.id === productId);
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({ id: productId, name: productName, price: parseFloat(price), qty: 1 });
        }
        localStorage.setItem('brew_cart', JSON.stringify(cart));
        updateCartBadge();
        showToast(`Added "${productName}" to your order cart!`);
    };

    function updateCartBadge() {
        let cart = JSON.parse(localStorage.getItem('brew_cart') || '[]');
        let totalItems = cart.reduce((sum, item) => sum + item.qty, 0);
        let badge = document.getElementById('cart-badge-count');
        if (badge) {
            badge.textContent = totalItems;
            badge.style.display = totalItems > 0 ? 'inline-block' : 'none';
        }
    }

    function showToast(message) {
        let toastEl = document.getElementById('brew-toast');
        if (!toastEl) {
            toastEl = document.createElement('div');
            toastEl.id = 'brew-toast';
            toastEl.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#3e2712;color:#fff;padding:12px 20px;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.3);z-index:9999;transition:opacity 0.3s ease;';
            document.body.appendChild(toastEl);
        }
        toastEl.textContent = message;
        toastEl.style.opacity = '1';
        setTimeout(() => {
            toastEl.style.opacity = '0';
        }, 3000);
    }

    updateCartBadge();
});

