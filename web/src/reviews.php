<?php
$pageTitle = "Customer Feedback & Reviews";
require_once __DIR__ . '/includes/header.php';

$db = getDb();
$user = currentUser();
$msg = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $author = trim($_POST['author_name'] ?? '');
    $rating = (int)($_POST['rating'] ?? 5);
    $comment = $_POST['comment'] ?? ''; // VULNERABILITY 5: Not sanitizing or escaping HTML!
    $productId = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : 1;

    if (empty($author) || empty($comment)) {
        $error = "Please provide both your name and feedback comments.";
    } else {
        // Raw insert allows Stored XSS payload to be saved
        $stmt = $db->prepare("INSERT INTO reviews (user_id, product_id, author_name, rating, comment, is_reviewed_by_staff) 
                              VALUES (:uid, :pid, :author, :rating, :comment, 0)");
        $stmt->execute([
            ':uid' => $user ? $user['id'] : null,
            ':pid' => $productId,
            ':author' => $author,
            ':rating' => max(1, min(5, $rating)),
            ':comment' => $comment
        ]);
        $msg = "Thank you! Your feedback has been submitted to our Roastery Quality & Staff Team for review.";
    }
}

// Fetch products for dropdown
$products = $db->query("SELECT id, name FROM products ORDER BY id ASC")->fetchAll();

// Fetch approved / recent reviews
$stmt = $db->query("SELECT r.*, p.name as product_name FROM reviews r LEFT JOIN products p ON r.product_id = p.id ORDER BY r.id DESC LIMIT 15");
$allReviews = $stmt->fetchAll();
?>

<div class="container my-5" style="max-width: 900px;">
    <div class="text-center mb-5">
        <h1 class="fw-bold mb-2"><i class="fa-solid fa-star text-warning me-2"></i>Customer Reviews & Roast Feedback</h1>
        <p class="text-muted lead">Share your coffee experience. Our barista team reviews all comments to maintain roasting quality.</p>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Submit Review Form -->
    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-pen-to-square me-2 text-warning"></i>Write a Coffee Review</h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="/reviews.php">
                <input type="hidden" name="submit_review" value="1">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Your Name / Handle</label>
                        <input type="text" name="author_name" class="form-control" placeholder="e.g. Alex M." value="<?= $user ? htmlspecialchars($user['full_name']) : '' ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Product Reviewed</label>
                        <select name="product_id" class="form-select">
                            <?php foreach ($products as $pr): ?>
                                <option value="<?= $pr['id'] ?>"><?= htmlspecialchars($pr['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Rating</label>
                        <select name="rating" class="form-select">
                            <option value="5">⭐⭐⭐⭐⭐ (5 Stars)</option>
                            <option value="4">⭐⭐⭐⭐ (4 Stars)</option>
                            <option value="3">⭐⭐⭐ (3 Stars)</option>
                            <option value="2">⭐⭐ (2 Stars)</option>
                            <option value="1">⭐ (1 Star)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Feedback / Tasting Notes</label>
                        <textarea name="comment" class="form-control" rows="4" placeholder="How did this roast brew? Share tasting notes, aroma, brew method..." required></textarea>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-brew px-4">
                            <i class="fa-solid fa-paper-plane me-1"></i> Submit Feedback
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Existing Reviews List -->
    <h4 class="fw-bold mb-3">Recent Community Reviews</h4>
    <div class="row g-4">
        <?php foreach ($allReviews as $rev): ?>
            <div class="col-md-6">
                <div class="card h-100 p-4 border shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($rev['product_name'] ?? 'Artisan Coffee') ?></span>
                        <div class="text-warning">
                            <?php for ($i = 0; $i < $rev['rating']; $i++): ?>
                                <i class="fa-solid fa-star"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <!-- Public display is sanitized; Staff portal is the intended XSS sink! -->
                    <p class="text-dark mb-3"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                    <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top small text-muted">
                        <span class="fw-bold"><i class="fa-solid fa-circle-user me-1"></i><?= htmlspecialchars($rev['author_name']) ?></span>
                        <span><?= date('M d, Y', strtotime($rev['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

