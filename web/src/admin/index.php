<?php
$pageTitle = "Administrator Console";
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn()) {
    header("Location: /login.php?redirect=/admin/index.php");
    exit;
}

$user = currentUser();
if (!isAdmin()) {
    echo '<div class="container my-5 text-center"><div class="alert alert-danger">Access Denied: You must possess administrative role ("admin") to view this portal.</div><a href="/index.php" class="btn btn-brew">Return Home</a></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$db = getDb();
$totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders")->fetchColumn();
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
?>

<div class="container my-5" style="max-width: 1000px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fa-solid fa-shield-halved text-danger me-2"></i>Executive Administration Portal</h2>
            <p class="text-muted mb-0">System Infrastructure and Corporate Management Console</p>
        </div>
        <span class="badge bg-danger p-2"><i class="fa-solid fa-user-shield me-1"></i>Administrator: <?= htmlspecialchars($user['username']) ?></span>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm bg-white text-center">
                <div class="fs-2 text-primary mb-1"><i class="fa-solid fa-users"></i></div>
                <h4 class="fw-bold mb-0"><?= (int)$totalUsers ?></h4>
                <small class="text-muted">Registered Users</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm bg-white text-center">
                <div class="fs-2 text-success mb-1"><i class="fa-solid fa-receipt"></i></div>
                <h4 class="fw-bold mb-0"><?= (int)$totalOrders ?></h4>
                <small class="text-muted">Orders Processed</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm bg-white text-center">
                <div class="fs-2 text-warning mb-1"><i class="fa-solid fa-dollar-sign"></i></div>
                <h4 class="fw-bold mb-0">$<?= number_format($totalRevenue, 2) ?></h4>
                <small class="text-muted">Gross Revenue</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm bg-white text-center">
                <div class="fs-2 text-info mb-1"><i class="fa-solid fa-boxes-stacked"></i></div>
                <h4 class="fw-bold mb-0"><?= (int)$totalProducts ?></h4>
                <small class="text-muted">Products Listed</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-2"><i class="fa-solid fa-warehouse me-2 text-warning"></i>Inventory Management</h5>
                <p class="text-muted small">Manage warehouse stock allocations, green coffee bean bags and supply chains.</p>
                <div class="mt-auto">
                    <a href="/admin/inventory.php" class="btn btn-outline-dark w-100">Open Inventory Console</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-2"><i class="fa-solid fa-file-shield me-2 text-danger"></i>Confidential Corporate Reports</h5>
                <p class="text-muted small">Access financial audits, quarterly metrics, vault codes and compliance assessments.</p>
                <div class="mt-auto">
                    <a href="/admin/reports.php" class="btn btn-danger w-100">Open Reports Portal</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

