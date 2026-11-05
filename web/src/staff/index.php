<?php
$pageTitle = "Staff Operations Portal";
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn()) {
    header("Location: /login.php?redirect=/staff/index.php");
    exit;
}

$user = currentUser();
if (!in_array($user['role'], ['staff', 'manager', 'admin'])) {
    echo '<div class="container my-5 text-center"><div class="alert alert-danger">Access Denied: Staff credentials required to view this portal.</div><a href="/index.php" class="btn btn-brew">Return Home</a></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$db = getDb();
$pendingOrdersCount = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();
$inProgressCount = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'In Progress'")->fetchColumn();
$unreadReviewsCount = $db->query("SELECT COUNT(*) FROM reviews WHERE is_reviewed_by_staff = 0")->fetchColumn();
?>

<div class="container my-5" style="max-width: 1000px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fa-solid fa-mug-saucer me-2 text-warning"></i>Staff Operations Dashboard</h2>
            <p class="text-muted mb-0">Store #01 - Roastery & Counter Fulfillment Operations</p>
        </div>
        <div>
            <span class="badge bg-success p-2"><i class="fa-solid fa-signal me-1"></i>Shift Active: <?= htmlspecialchars($user['full_name']) ?></span>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card p-4 border-0 shadow-sm bg-white text-center">
                <div class="fs-1 text-warning mb-2"><i class="fa-solid fa-clock"></i></div>
                <h3 class="fw-bold mb-0"><?= (int)$pendingOrdersCount ?></h3>
                <span class="text-muted small">Orders Pending Preparation</span>
                <a href="/staff/orders.php?status=Pending" class="btn btn-sm btn-outline-secondary mt-3">Manage Queue</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 border-0 shadow-sm bg-white text-center">
                <div class="fs-1 text-primary mb-2"><i class="fa-solid fa-blender"></i></div>
                <h3 class="fw-bold mb-0"><?= (int)$inProgressCount ?></h3>
                <span class="text-muted small">Orders In Progress</span>
                <a href="/staff/orders.php?status=In Progress" class="btn btn-sm btn-outline-secondary mt-3">View Active</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 border-0 shadow-sm bg-white text-center">
                <div class="fs-1 text-danger mb-2"><i class="fa-solid fa-comments"></i></div>
                <h3 class="fw-bold mb-0"><?= (int)$unreadReviewsCount ?></h3>
                <span class="text-muted small">Customer Reviews Awaiting Review</span>
                <a href="/staff/reviews.php" class="btn btn-sm btn-brew mt-3">Review Feedback</a>
            </div>
        </div>
    </div>

    <!-- Staff Workflows -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-list-check me-2 text-warning"></i>Barista Order Fulfillment</h5>
                <p class="text-muted small">View incoming orders, check ingredient customizations, print barista drink tickets, and advance order states.</p>
                <div class="mt-auto">
                    <a href="/staff/orders.php" class="btn btn-dark w-100"><i class="fa-solid fa-arrow-right me-1"></i>Open Order Station</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-comment-dots me-2 text-warning"></i>Customer Review Moderation</h5>
                <p class="text-muted small">Read live feedback submitted by patrons. All comments are reviewed before forwarding to head roasters.</p>
                <div class="mt-auto">
                    <a href="/staff/reviews.php" class="btn btn-brew w-100"><i class="fa-solid fa-arrow-right me-1"></i>Open Feedback Queue</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

