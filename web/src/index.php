<?php
$pageTitle = "Fresh Roasted Specialty Coffee";
require_once __DIR__ . '/includes/header.php';

$db = getDb();
$stmt = $db->query("SELECT * FROM products WHERE featured = 1 ORDER BY id ASC LIMIT 4");
$featuredProducts = $stmt->fetchAll();

$revStmt = $db->query("SELECT * FROM reviews ORDER BY id DESC LIMIT 3");
$recentReviews = $revStmt->fetchAll();
?>

<!-- Hero Banner -->
<div class="hero-banner">
    <div class="container text-center text-md-start">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="badge bg-warning text-dark px-3 py-2 text-uppercase fw-bold mb-3">Direct Trade & Artisan Roasting</span>
                <h1 class="display-4 fw-bold mb-3">Exceptional Coffee, Roasted with Passion.</h1>
                <p class="lead mb-4">Discover our meticulously curated selection of single-origin coffee beans, cold brew reserves, and handcrafted bakery goods delivered directly to your doorstep.</p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-md-start">
                    <a href="/menu.php" class="btn btn-brew btn-lg px-4"><i class="fa-solid fa-mug-hot me-2"></i>Explore Menu</a>
                    <a href="/track-delivery.php" class="btn btn-outline-light btn-lg px-4"><i class="fa-solid fa-truck-fast me-2"></i>Track Delivery</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Callout -->
<div class="container my-5">
    <div class="row g-4 text-center">
        <div class="col-md-4">
            <div class="p-4 bg-white rounded-3 shadow-sm h-100 border">
                <div class="fs-1 text-warning mb-3"><i class="fa-solid fa-seedling"></i></div>
                <h4 class="fw-bold">Ethically Sourced</h4>
                <p class="text-muted">We partner directly with smallholder farmers in Huila, Yirgacheffe, and Sumatra, paying above fair-trade premiums.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 bg-white rounded-3 shadow-sm h-100 border">
                <div class="fs-1 text-warning mb-3"><i class="fa-solid fa-fire-burner"></i></div>
                <h4 class="fw-bold">Small-Batch Roasting</h4>
                <p class="text-muted">Roasted daily on our custom cast-iron drum roasters to unlock the unique terroir and tasting notes of each harvest.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 bg-white rounded-3 shadow-sm h-100 border">
                <div class="fs-1 text-warning mb-3"><i class="fa-solid fa-truck-ramp-box"></i></div>
                <h4 class="fw-bold">Fresh Local Dispatch</h4>
                <p class="text-muted">Integrated logistics and automated courier diagnostics ensure same-day dispatch from our Seattle roastery.</p>
            </div>
        </div>
    </div>
</div>

<!-- Featured Products -->
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Featured Roastery Selections</h2>
            <p class="text-muted mb-0">Handpicked favorites loved by our cafe patrons.</p>
        </div>
        <a href="/menu.php" class="btn btn-brew-outline">View All Products <i class="fa-solid fa-arrow-right ms-1"></i></a>
    </div>

    <div class="row g-4">
        <?php foreach ($featuredProducts as $item): ?>
            <div class="col-sm-6 col-lg-3">
                <div class="card-product">
                    <div class="position-relative">
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="card-product-img w-100" onerror="this.src='https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=500&q=80'">
                        <span class="badge badge-roast position-absolute top-0 end-0 m-2"><?= htmlspecialchars($item['roast_level']) ?></span>
                    </div>
                    <div class="p-3 d-flex flex-column flex-grow-1">
                        <small class="text-muted mb-1"><i class="fa-solid fa-earth-americas me-1"></i><?= htmlspecialchars($item['origin']) ?></small>
                        <h5 class="fw-bold mb-2"><?= htmlspecialchars($item['name']) ?></h5>
                        <p class="small text-muted flex-grow-1"><?= htmlspecialchars(substr($item['description'], 0, 95)) ?>...</p>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <span class="product-price">$<?= number_format($item['price'], 2) ?></span>
                            <button class="btn btn-sm btn-brew" onclick="addToCart(<?= $item['id'] ?>, '<?= addslashes($item['name']) ?>', <?= $item['price'] ?>)">
                                <i class="fa-solid fa-cart-plus me-1"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Customer Reviews Section -->
<div class="bg-light py-5 border-top border-bottom my-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold">What Our Community Says</h2>
            <p class="text-muted">Real reviews from coffee lovers and morning regulars.</p>
        </div>
        <div class="row g-4">
            <?php foreach ($recentReviews as $rev): ?>
                <div class="col-md-4">
                    <div class="card h-100 p-4 border shadow-sm">
                        <div class="text-warning mb-2">
                            <?php for ($i = 0; $i < $rev['rating']; $i++): ?>
                                <i class="fa-solid fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="fst-italic text-dark mb-3">"<?= htmlspecialchars($rev['comment']) ?>"</p>
                        <div class="d-flex align-items-center mt-auto">
                            <i class="fa-solid fa-circle-user fs-4 text-secondary me-2"></i>
                            <div>
                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($rev['author_name']) ?></h6>
                                <small class="text-muted"><?= date('M d, Y', strtotime($rev['created_at'])) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="/reviews.php" class="btn btn-brew">Submit or View More Reviews <i class="fa-solid fa-comment-dots ms-1"></i></a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

