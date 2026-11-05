<?php
$pageTitle = "My Customer Profile";
require_once __DIR__ . '/includes/header.php';

if (!isLoggedIn()) {
    header("Location: /login.php");
    exit;
}

$user = currentUser();
$db = getDb();
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user['id']]);
$userData = $stmt->fetch();
?>

<div class="container my-5" style="max-width: 700px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-id-card me-2 text-warning"></i>Customer Profile Account</h5>
            <span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($userData['role']) ?></span>
        </div>
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                <div class="fs-1 text-secondary"><i class="fa-solid fa-circle-user"></i></div>
                <div>
                    <h4 class="fw-bold mb-0"><?= htmlspecialchars($userData['full_name']) ?></h4>
                    <span class="text-muted small">@<?= htmlspecialchars($userData['username']) ?> &bull; Joined <?= date('F Y', strtotime($userData['created_at'])) ?></span>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="small text-muted fw-bold">Email Address</label>
                    <div class="fw-semibold"><?= htmlspecialchars($userData['email']) ?></div>
                </div>
                <div class="col-sm-6">
                    <label class="small text-muted fw-bold">Phone Number</label>
                    <div class="fw-semibold"><?= htmlspecialchars($userData['phone'] ?: 'Not specified') ?></div>
                </div>
                <div class="col-12">
                    <label class="small text-muted fw-bold">Primary Delivery Address</label>
                    <div class="fw-semibold"><?= htmlspecialchars($userData['address'] ?: 'Not specified') ?></div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between">
                <a href="/orders.php" class="btn btn-brew"><i class="fa-solid fa-receipt me-1"></i>View My Orders</a>
                <a href="/logout.php" class="btn btn-outline-danger"><i class="fa-solid fa-arrow-right-from-bracket me-1"></i>Sign Out</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

