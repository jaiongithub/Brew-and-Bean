<?php
$pageTitle = "Inventory Management";
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn()) {
    header("Location: /login.php?redirect=/admin/inventory.php");
    exit;
}

$db = getDb();
$products = $db->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC")->fetchAll();
?>

<div class="container my-5" style="max-width: 1000px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fa-solid fa-boxes-stacked text-warning me-2"></i>Roastery Warehouse Inventory</h2>
            <p class="text-muted mb-0">Live stock counts and supplier origins.</p>
        </div>
        <a href="/admin/index.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Admin Console</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Origin / Roasting</th>
                        <th>Unit Price</th>
                        <th>Stock Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $pr): ?>
                        <tr>
                            <td>#<?= $pr['id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($pr['name']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($pr['category_name']) ?></span></td>
                            <td class="small text-muted"><?= htmlspecialchars($pr['origin'] ?: 'In-house') ?></td>
                            <td class="fw-bold">$<?= number_format($pr['price'], 2) ?></td>
                            <td><?= (int)$pr['stock'] ?> units</td>
                            <td>
                                <?php if ($pr['stock'] > 40): ?>
                                    <span class="badge bg-success">Optimal</span>
                                <?php elseif ($pr['stock'] > 15): ?>
                                    <span class="badge bg-warning text-dark">Adequate</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Low Stock</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

