<?php
$pageTitle = "My Order History";
require_once __DIR__ . '/includes/header.php';

if (!isLoggedIn()) {
    header("Location: /login.php?redirect=/orders.php");
    exit;
}

$user = currentUser();
$db = getDb();

$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = :uid ORDER BY id DESC");
$stmt->execute([':uid' => $user['id']]);
$orders = $stmt->fetchAll();
?>

<div class="container my-5" style="max-width: 950px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fa-solid fa-receipt me-2 text-warning"></i>Your Orders</h2>
            <p class="text-muted mb-0">Track your current coffee roasts and order history.</p>
        </div>
        <a href="/menu.php" class="btn btn-brew"><i class="fa-solid fa-cart-plus me-1"></i>New Order</a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="card p-5 text-center shadow-sm border-0">
            <i class="fa-solid fa-receipt fs-1 text-muted mb-3"></i>
            <h5>No orders placed yet.</h5>
            <p class="text-muted">Explore our single-origin coffees and treat yourself today.</p>
            <div>
                <a href="/menu.php" class="btn btn-brew">Explore Menu</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            <th>Date Placed</th>
                            <th>Status</th>
                            <th>Total Amount</th>
                            <th>Delivery Address</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $ord): 
                            $statusClass = 'badge-status-' . strtolower(str_replace(' ', '', $ord['status']));
                        ?>
                            <tr>
                                <td class="fw-bold">
                                    <a href="/order-details.php?id=<?= $ord['id'] ?>" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($ord['order_number']) ?>
                                    </a>
                                </td>
                                <td class="small text-muted"><?= date('M d, Y H:i', strtotime($ord['created_at'])) ?></td>
                                <td>
                                    <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($ord['status']) ?></span>
                                </td>
                                <td class="fw-bold text-dark">$<?= number_format($ord['total_amount'], 2) ?></td>
                                <td class="small text-muted"><?= htmlspecialchars(substr($ord['delivery_address'], 0, 35)) ?>...</td>
                                <td class="text-end">
                                    <a href="/order-details.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="fa-solid fa-eye me-1"></i> Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

