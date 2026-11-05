<?php
$pageTitle = "Staff - Order Management";
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn()) {
    header("Location: /login.php?redirect=/staff/orders.php");
    exit;
}

$user = currentUser();
if (!in_array($user['role'], ['staff', 'manager', 'admin'])) {
    die("Access Denied.");
}

$db = getDb();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = $_POST['status'];
    $validStatuses = ['Pending', 'In Progress', 'Out for Delivery', 'Completed', 'Cancelled'];
    if (in_array($newStatus, $validStatuses)) {
        $upStmt = $db->prepare("UPDATE orders SET status = :st WHERE id = :id");
        $upStmt->execute([':st' => $newStatus, ':id' => $orderId]);
    }
}

$filterStatus = $_GET['status'] ?? 'all';
if ($filterStatus !== 'all') {
    $stmt = $db->prepare("SELECT o.*, u.full_name as customer_name, u.phone FROM orders o JOIN users u ON o.user_id = u.id WHERE o.status = :st ORDER BY o.id DESC");
    $stmt->execute([':st' => $filterStatus]);
} else {
    $stmt = $db->query("SELECT o.*, u.full_name as customer_name, u.phone FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC");
}
$orders = $stmt->fetchAll();
?>

<div class="container my-5" style="max-width: 1100px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fa-solid fa-clipboard-list me-2 text-warning"></i>Kitchen & Barista Orders</h2>
            <p class="text-muted mb-0">Update preparation status and check delivery dispatch tickets.</p>
        </div>
        <a href="/staff/index.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Staff Dashboard</a>
    </div>

    <!-- Filter Buttons -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <a href="/staff/orders.php?status=all" class="btn btn-sm <?= ($filterStatus === 'all') ? 'btn-dark' : 'btn-outline-dark' ?>">All Orders</a>
        <a href="/staff/orders.php?status=Pending" class="btn btn-sm <?= ($filterStatus === 'Pending') ? 'btn-warning' : 'btn-outline-warning' ?>">Pending</a>
        <a href="/staff/orders.php?status=In Progress" class="btn btn-sm <?= ($filterStatus === 'In Progress') ? 'btn-primary' : 'btn-outline-primary' ?>">In Progress</a>
        <a href="/staff/orders.php?status=Out for Delivery" class="btn btn-sm <?= ($filterStatus === 'Out for Delivery') ? 'btn-info' : 'btn-outline-info' ?>">Out for Delivery</a>
        <a href="/staff/orders.php?status=Completed" class="btn btn-sm <?= ($filterStatus === 'Completed') ? 'btn-success' : 'btn-outline-success' ?>">Completed</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Placed</th>
                        <th>Total</th>
                        <th>Special Notes</th>
                        <th>Current Status</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $ord): 
                        $statusClass = 'badge-status-' . strtolower(str_replace(' ', '', $ord['status']));
                    ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($ord['order_number']) ?></td>
                            <td>
                                <div><?= htmlspecialchars($ord['customer_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($ord['phone'] ?? '') ?></small>
                            </td>
                            <td class="small text-muted"><?= date('M d, H:i', strtotime($ord['created_at'])) ?></td>
                            <td class="fw-bold">$<?= number_format($ord['total_amount'], 2) ?></td>
                            <td class="small text-muted" style="max-width: 200px;">
                                <?= htmlspecialchars(substr($ord['special_instructions'] ?? 'None', 0, 45)) ?>
                            </td>
                            <td>
                                <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($ord['status']) ?></span>
                            </td>
                            <td>
                                <form method="POST" action="/staff/orders.php<?= ($filterStatus !== 'all') ? '?status=' . urlencode($filterStatus) : '' ?>" class="d-flex gap-1">
                                    <input type="hidden" name="update_status" value="1">
                                    <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" style="width: 140px;">
                                        <option value="Pending" <?= $ord['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="In Progress" <?= $ord['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="Out for Delivery" <?= $ord['status'] === 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                        <option value="Completed" <?= $ord['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="Cancelled" <?= $ord['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-dark" title="Save"><i class="fa-solid fa-check"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

