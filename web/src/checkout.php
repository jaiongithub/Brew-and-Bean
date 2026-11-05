<?php
require_once __DIR__ . '/config/db.php';

if (!isLoggedIn()) {
    header("Location: /login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /cart.php");
    exit;
}

$user = currentUser();
$db = getDb();

$payload = json_decode($_POST['cart_payload'] ?? '[]', true);
$address = trim($_POST['delivery_address'] ?? 'Customer Delivery Address');
$specialInstructions = trim($_POST['special_instructions'] ?? '');

if (empty($payload)) {
    header("Location: /cart.php");
    exit;
}

// Calculate total
$totalAmount = 0.0;
foreach ($payload as $item) {
    $totalAmount += (float)$item['price'] * (int)$item['qty'];
}

// Generate unique order number
$orderNumber = 'BB-ORD-' . date('Y') . '-' . rand(1000, 9999);

try {
    $db->beginTransaction();

    $stmt = $db->prepare("INSERT INTO orders (user_id, order_number, status, total_amount, delivery_address, special_instructions) 
                          VALUES (:uid, :onum, 'Pending', :tot, :addr, :spec)");
    $stmt->execute([
        ':uid' => $user['id'],
        ':onum' => $orderNumber,
        ':tot' => $totalAmount,
        ':addr' => $address,
        ':spec' => $specialInstructions
    ]);
    $orderId = $db->lastInsertId();

    $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) 
                             VALUES (:oid, :pid, :qty, :uprice, :subtot)");
    foreach ($payload as $item) {
        $pId = (int)$item['id'];
        $qty = (int)$item['qty'];
        $uPrice = (float)$item['price'];
        $sub = $qty * $uPrice;
        $itemStmt->execute([
            ':oid' => $orderId,
            ':pid' => $pId,
            ':qty' => $qty,
            ':uprice' => $uPrice,
            ':subtot' => $sub
        ]);
    }

    $db->commit();
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    die("Order processing error: " . htmlspecialchars($e->getMessage()));
}

// Clear cart via JavaScript redirect
$pageTitle = "Order Placed Successfully";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5 text-center" style="max-width: 600px;">
    <div class="card p-5 shadow-sm border-0">
        <div class="text-success mb-3 fs-1">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <h2 class="fw-bold mb-2">Order Confirmed!</h2>
        <p class="text-muted">Thank you for ordering with BREW & BEAN. Our baristas are preparing your freshly roasted selection.</p>
        
        <div class="bg-light p-3 rounded my-3 border">
            <div class="small text-muted">Order Tracking Number:</div>
            <h4 class="fw-bold text-dark mb-0"><?= htmlspecialchars($orderNumber) ?></h4>
        </div>

        <div class="d-flex justify-content-center gap-3 mt-4">
            <a href="/order-details.php?id=<?= $orderId ?>" class="btn btn-brew">View Order Receipt</a>
            <a href="/menu.php" class="btn btn-outline-secondary">Return to Menu</a>
        </div>
    </div>
</div>

<script>
// Clear local storage cart
localStorage.removeItem('brew_cart');
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

