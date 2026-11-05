<?php
$pageTitle = "Your Order Cart";
require_once __DIR__ . '/includes/header.php';
$user = currentUser();
?>

<div class="container my-5" style="max-width: 900px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="fw-bold mb-0"><i class="fa-solid fa-bag-shopping me-2 text-warning"></i>Your Order Cart</h2>
        <a href="/menu.php" class="btn btn-brew-outline btn-sm"><i class="fa-solid fa-plus me-1"></i>Add More Items</a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div id="cart-empty-message" class="text-center py-5" style="display:none;">
                <i class="fa-solid fa-cart-shopping fs-1 text-muted mb-3"></i>
                <h5>Your cart is currently empty!</h5>
                <p class="text-muted">Explore our single-origin coffees and pastries.</p>
                <a href="/menu.php" class="btn btn-brew mt-2">Browse Menu</a>
            </div>

            <div id="cart-content-wrapper">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th style="width: 140px;">Price</th>
                                <th style="width: 120px;">Quantity</th>
                                <th style="width: 140px;">Subtotal</th>
                                <th style="width: 60px;"></th>
                            </tr>
                        </thead>
                        <tbody id="cart-items-body">
                            <!-- Populated via Javascript -->
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <div>
                        <button class="btn btn-outline-danger btn-sm" onclick="clearCart()">
                            <i class="fa-solid fa-trash-can me-1"></i> Clear Cart
                        </button>
                    </div>
                    <div class="text-end">
                        <h5 class="text-muted mb-1">Estimated Total:</h5>
                        <h3 class="fw-bold text-dark" id="cart-total-amount">$0.00</h3>
                    </div>
                </div>

                <hr class="my-4">

                <?php if ($user): ?>
                    <form action="/checkout.php" method="POST" id="checkout-form" onsubmit="prepareCheckout(event)">
                        <input type="hidden" name="cart_payload" id="cart_payload_input">
                        
                        <h5 class="fw-bold mb-3">Delivery Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Customer Name</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Contact Email</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Delivery Street Address</label>
                                <input type="text" name="delivery_address" class="form-control" required placeholder="e.g. 742 Evergreen Terrace, Suite 100" value="404 Espresso Blvd, Seattle, WA">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Special Delivery Instructions / Gate Notes</label>
                                <textarea name="special_instructions" class="form-control" rows="2" placeholder="e.g. Leave with doorman, ring bell 3 times"></textarea>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-brew btn-lg px-4">
                                <i class="fa-solid fa-credit-card me-2"></i> Confirm & Place Order
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning d-flex align-items-center justify-content-between mb-0">
                        <div>
                            <i class="fa-solid fa-circle-exclamation me-2"></i>
                            Please sign in or create an account to finalize your order.
                        </div>
                        <a href="/login.php?redirect=/cart.php" class="btn btn-dark btn-sm">Sign In to Checkout</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function renderCartPage() {
    let cart = JSON.parse(localStorage.getItem('brew_cart') || '[]');
    let emptyMsg = document.getElementById('cart-empty-message');
    let contentWrapper = document.getElementById('cart-content-wrapper');
    let tbody = document.getElementById('cart-items-body');
    let totalEl = document.getElementById('cart-total-amount');

    if (cart.length === 0) {
        emptyMsg.style.display = 'block';
        contentWrapper.style.display = 'none';
        return;
    }

    emptyMsg.style.display = 'none';
    contentWrapper.style.display = 'block';
    tbody.innerHTML = '';

    let grandTotal = 0;
    cart.forEach((item, index) => {
        let subtotal = item.price * item.qty;
        grandTotal += subtotal;

        let tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong>${item.name}</strong></td>
            <td>$${item.price.toFixed(2)}</td>
            <td>
                <input type="number" min="1" max="50" class="form-control form-control-sm" style="width: 70px;" value="${item.qty}" onchange="updateItemQty(${index}, this.value)">
            </td>
            <td class="fw-bold">$${subtotal.toFixed(2)}</td>
            <td>
                <button class="btn btn-link text-danger p-0" onclick="removeItem(${index})" title="Remove"><i class="fa-solid fa-xmark"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    totalEl.textContent = `$${grandTotal.toFixed(2)}`;
}

function updateItemQty(index, qty) {
    let cart = JSON.parse(localStorage.getItem('brew_cart') || '[]');
    let parsed = parseInt(qty);
    if (parsed > 0 && cart[index]) {
        cart[index].qty = parsed;
        localStorage.setItem('brew_cart', JSON.stringify(cart));
        renderCartPage();
    }
}

function removeItem(index) {
    let cart = JSON.parse(localStorage.getItem('brew_cart') || '[]');
    cart.splice(index, 1);
    localStorage.setItem('brew_cart', JSON.stringify(cart));
    renderCartPage();
}

function clearCart() {
    localStorage.removeItem('brew_cart');
    renderCartPage();
}

function prepareCheckout(e) {
    let cart = JSON.parse(localStorage.getItem('brew_cart') || '[]');
    if (cart.length === 0) {
        alert('Your cart is empty.');
        e.preventDefault();
        return;
    }
    document.getElementById('cart_payload_input').value = JSON.stringify(cart);
}

document.addEventListener('DOMContentLoaded', renderCartPage);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

