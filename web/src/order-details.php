<?php
$pageTitle = "Order Details";
require_once __DIR__ . '/includes/header.php';

if (!isLoggedIn()) {
    header("Location: /login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$currentUser = currentUser();
$db = getDb();

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// VULNERABILITY 2: Insecure Direct Object Reference (IDOR)
// The query retrieves the order by primary key WITHOUT verifying that o.user_id matches the session user!
$stmt = $db->prepare("SELECT o.*, u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone, u.username as customer_username 
                      FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      WHERE o.id = :id LIMIT 1");
$stmt->execute([':id' => $orderId]);
$order = $stmt->fetch();

if (!$order) {
    echo '<div class="container my-5 text-center"><div class="alert alert-danger">Order record #' . htmlspecialchars($orderId) . ' not found.</div><a href="/orders.php" class="btn btn-brew">Back to Orders</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch order items
$itemsStmt = $db->prepare("SELECT oi.*, p.name as product_name, p.image_url 
                          FROM order_items oi 
                          JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = :oid");
$itemsStmt->execute([':oid' => $orderId]);
$orderItems = $itemsStmt->fetchAll();

$isCrossAccountAccess = ($order['user_id'] != $currentUser['id']);
$statusClass = 'badge-status-' . strtolower(str_replace(' ', '', $order['status']));
?>

<div class="container my-5" style="max-width: 900px;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/orders.php" class="text-decoration-none">My Orders</a></li>
            <li class="breadcrumb-item active">Order #<?= htmlspecialchars($order['order_number']) ?></li>
        </ol>
    </nav>

    <?php if ($isCrossAccountAccess): ?>
        <!-- IDOR Notification Callout -->
        <div class="alert alert-warning border-2 shadow-sm mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fa-solid fa-triangle-exclamation text-warning fs-4"></i>
                <h5 class="alert-heading mb-0 fw-bold">Cross-Account Record Access (IDOR Vulnerability)</h5>
            </div>
            <p class="mb-2">
                You are currently viewing an order belonging to customer 
                <strong><?= htmlspecialchars($order['customer_name']) ?></strong> (<code><?= htmlspecialchars($order['customer_username']) ?></code>), 
                while authenticated as <strong><?= htmlspecialchars($currentUser['username']) ?></strong>.
            </p>
            <?php if (!empty($order['flag_data']) || $order['id'] == 104): 
                notifyVerifierEvent('BB-F02', 'idor_cross_account_order', ['accessed_order' => $orderId, 'viewer' => $currentUser['username']]);
            ?>
                <div class="p-3 bg-dark text-success rounded font-monospace mt-2">
                    <div class="fw-bold text-light mb-1"><i class="fa-solid fa-flag me-2 text-danger"></i>CHALLENGE FLAG VERIFICATION [BB-F02]:</div>
                    <span class="fs-5 text-warning"><?= htmlspecialchars($order['flag_data'] ?: FLAG_F02) ?></span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <span class="text-muted small">Order Identifier:</span>
                <h4 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($order['order_number']) ?></h4>
            </div>
            <div>
                <span class="badge <?= $statusClass ?> fs-6 px-3 py-2"><?= htmlspecialchars($order['status']) ?></span>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase small fw-bold">Customer Details</h6>
                    <div class="fw-bold text-dark fs-5"><?= htmlspecialchars($order['customer_name']) ?></div>
                    <div class="small text-muted"><i class="fa-solid fa-envelope me-1"></i><?= htmlspecialchars($order['customer_email']) ?></div>
                    <?php if (!empty($order['customer_phone'])): ?>
                        <div class="small text-muted"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($order['customer_phone']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase small fw-bold">Delivery & Timing</h6>
                    <div class="small text-muted"><i class="fa-solid fa-calendar me-1"></i>Placed: <?= date('F d, Y - H:i', strtotime($order['created_at'])) ?></div>
                    <div class="small text-muted mt-1"><i class="fa-solid fa-location-dot me-1"></i>Address: <?= htmlspecialchars($order['delivery_address']) ?></div>
                </div>
            </div>

            <?php if (!empty($order['special_instructions'])): ?>
                <div class="p-3 bg-light rounded border mb-4">
                    <strong class="d-block small text-muted text-uppercase mb-1"><i class="fa-solid fa-clipboard-list me-1"></i>Delivery Notes / Gate Instructions</strong>
                    <div><?= nl2br(htmlspecialchars($order['special_instructions'])) ?></div>
                </div>
            <?php endif; ?>

            <h5 class="fw-bold mb-3">Ordered Items</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;" onerror="this.src='https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=150&q=80'">
                                        <span class="fw-semibold"><?= htmlspecialchars($item['product_name']) ?></span>
                                    </div>
                                </td>
                                <td>$<?= number_format($item['unit_price'], 2) ?></td>
                                <td><?= (int)$item['quantity'] ?></td>
                                <td class="text-end fw-bold">$<?= number_format($item['subtotal'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="border-top">
                        <tr>
                            <td colspan="3" class="text-end fw-bold fs-5">Total Amount:</td>
                            <td class="text-end fw-bold fs-5 text-dark">$<?= number_format($order['total_amount'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card-footer bg-light py-3 d-flex justify-content-between align-items-center">
            <a href="/orders.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Back to Orders</a>
            <a href="/track-delivery.php?order=<?= urlencode($order['order_number']) ?>" class="btn btn-brew"><i class="fa-solid fa-truck-fast me-1"></i>Track Shipment</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

