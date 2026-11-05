<?php
$pageTitle = "Full Coffee & Food Menu";
require_once __DIR__ . '/includes/header.php';

$db = getDb();

// Category filter
$catSlug = $_GET['category'] ?? 'all';

$cats = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();

if ($catSlug !== 'all') {
    $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE c.slug = :slug ORDER BY p.id ASC");
    $stmt->execute([':slug' => $catSlug]);
} else {
    $stmt = $db->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.category_id ASC, p.id ASC");
}
$products = $stmt->fetchAll();
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold mb-2">Our Crafted Offerings</h1>
        <p class="text-muted lead">Sourced globally, roasted locally in Seattle, prepared fresh every morning.</p>
        
        <!-- Category Pill Navigation -->
        <div class="d-flex justify-content-center flex-wrap gap-2 mt-4">
            <a href="/menu.php?category=all" class="btn <?= ($catSlug === 'all') ? 'btn-brew' : 'btn-outline-secondary' ?> rounded-pill px-4">All Items</a>
            <?php foreach ($cats as $cat): ?>
                <a href="/menu.php?category=<?= urlencode($cat['slug']) ?>" class="btn <?= ($catSlug === $cat['slug']) ? 'btn-brew' : 'btn-outline-secondary' ?> rounded-pill px-4">
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="row g-4">
        <?php if (empty($products)): ?>
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-mug-saucer fs-1 text-muted mb-3"></i>
                <h5>No items found in this category.</h5>
            </div>
        <?php else: ?>
            <?php foreach ($products as $p): ?>
                <div class="col-sm-6 col-lg-4">
                    <div class="card-product">
                        <div class="position-relative">
                            <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="card-product-img w-100" onerror="this.src='https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=500&q=80'">
                            <?php if (!empty($p['roast_level'])): ?>
                                <span class="badge badge-roast position-absolute top-0 end-0 m-2"><?= htmlspecialchars($p['roast_level']) ?></span>
                            <?php endif; ?>
                            <span class="badge bg-light text-dark position-absolute bottom-0 start-0 m-2 shadow-sm"><?= htmlspecialchars($p['category_name']) ?></span>
                        </div>
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <?php if (!empty($p['origin'])): ?>
                                <small class="text-muted mb-1"><i class="fa-solid fa-earth-americas me-1 text-warning"></i><?= htmlspecialchars($p['origin']) ?></small>
                            <?php endif; ?>
                            <h5 class="fw-bold mb-2"><?= htmlspecialchars($p['name']) ?></h5>
                            <p class="small text-muted flex-grow-1"><?= htmlspecialchars($p['description']) ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <div>
                                    <span class="product-price">$<?= number_format($p['price'], 2) ?></span>
                                    <small class="text-muted d-block" style="font-size:0.75rem;">In Stock: <?= (int)$p['stock'] ?> units</small>
                                </div>
                                <button class="btn btn-brew" onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes($p['name']) ?>', <?= $p['price'] ?>)">
                                    <i class="fa-solid fa-cart-plus me-1"></i> Order Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

