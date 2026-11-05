<?php
$pageTitle = "Staff - Customer Review Moderation";
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn()) {
    header("Location: /login.php?redirect=/staff/reviews.php");
    exit;
}

$user = currentUser();
if (!in_array($user['role'], ['staff', 'manager', 'admin'])) {
    die("Access Denied: Staff authorization required.");
}

// Issue Staff Secret Token Cookie containing Flag BB-F05
// (HttpOnly is false so XSS demonstration can access document.cookie)
setcookie('staff_secret_token', FLAG_F05, time() + 86400, '/', '', false, false);

$db = getDb();

// Handle mark reviewed
if (isset($_GET['mark_reviewed'])) {
    $revId = (int)$_GET['mark_reviewed'];
    $uStmt = $db->prepare("UPDATE reviews SET is_reviewed_by_staff = 1 WHERE id = :id");
    $uStmt->execute([':id' => $revId]);
    header("Location: /staff/reviews.php");
    exit;
}

// Fetch all reviews (both reviewed and unreviewed)
$stmt = $db->query("SELECT r.*, p.name as product_name FROM reviews r LEFT JOIN products p ON r.product_id = p.id ORDER BY r.id DESC");
$reviews = $stmt->fetchAll();
?>

<div class="container my-5" style="max-width: 950px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fa-solid fa-comments text-warning me-2"></i>Review Moderation Queue</h2>
            <p class="text-muted mb-0">Authenticated Staff Feedback Terminal - Session: <code><?= htmlspecialchars($user['username']) ?></code></p>
        </div>
        <a href="/staff/index.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Staff Hub</a>
    </div>

    <!-- Staff Security Notice Banner -->
    <div class="alert alert-secondary small d-flex align-items-center justify-content-between mb-4">
        <div>
            <i class="fa-solid fa-shield-halved text-primary me-2"></i>
            Staff Internal Session Active. Verification Token Attached.
        </div>
        <span class="badge bg-dark font-monospace">STAFF-SEC-COOKIE: ACTIVE</span>
    </div>

    <div class="row g-4">
        <?php foreach ($reviews as $rev): ?>
            <div class="col-12">
                <div class="card shadow-sm border <?= ($rev['is_reviewed_by_staff'] == 0) ? 'border-warning' : '' ?>">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <div>
                            <strong>Review #<?= $rev['id'] ?></strong> by 
                            <span class="text-primary fw-semibold"><?= htmlspecialchars($rev['author_name']) ?></span>
                            for <span class="badge bg-secondary"><?= htmlspecialchars($rev['product_name'] ?? 'General') ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="text-warning small">
                                <?php for ($i = 0; $i < $rev['rating']; $i++): ?>
                                    <i class="fa-solid fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <?php if ($rev['is_reviewed_by_staff'] == 0): ?>
                                <span class="badge bg-warning text-dark">Unreviewed</span>
                            <?php else: ?>
                                <span class="badge bg-success">Reviewed</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <!-- ============================================================== -->
                        <!-- VULNERABILITY 5 (STORED XSS SINK)                              -->
                        <!-- Raw customer feedback output without HTML escaping/sanitization -->
                        <!-- ============================================================== -->
                        <div class="review-text-content fs-6 mb-3">
                            <?= $rev['comment'] ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top small text-muted">
                            <span><i class="fa-solid fa-calendar me-1"></i>Submitted: <?= date('M d, Y H:i:s', strtotime($rev['created_at'])) ?></span>
                            <div>
                                <?php if ($rev['is_reviewed_by_staff'] == 0): ?>
                                    <a href="/staff/reviews.php?mark_reviewed=<?= $rev['id'] ?>" class="btn btn-sm btn-outline-success">
                                        <i class="fa-solid fa-check me-1"></i> Mark as Reviewed
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

