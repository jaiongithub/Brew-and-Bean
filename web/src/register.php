<?php
$pageTitle = "Create Customer Account";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';

$error = null;

if (isLoggedIn()) {
    header("Location: /orders.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
        $error = "Please fill in all required fields.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters long.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $res = registerUser($username, $email, $password, $fullName, $phone, $address);
        if ($res['success']) {
            header("Location: /orders.php");
            exit;
        } else {
            $error = $res['error'];
        }
    }
}
?>

<div class="container my-5" style="max-width: 600px;">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="fs-1 text-warning mb-2"><i class="fa-solid fa-mug-saucer"></i></div>
                <h3 class="fw-bold">Join BREW & BEAN Club</h3>
                <p class="text-muted small">Create an account to order roasted coffee blends, track shipments, and leave feedback.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center small" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="/register.php">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" placeholder="e.g. John Doe" value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Username *</label>
                        <input type="text" name="username" class="form-control" placeholder="e.g. johndoe" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="john@example.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Contact Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="555-0199" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Delivery Street Address</label>
                        <input type="text" name="address" class="form-control" placeholder="123 Coffee Lane, Seattle, WA" value="<?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Choose Password *</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-brew btn-lg w-100">
                        <i class="fa-solid fa-user-plus me-2"></i>Create Customer Account
                    </button>
                </div>
            </form>

            <div class="text-center small text-muted mt-3">
                Already registered? <a href="/login.php" class="text-warning fw-semibold">Sign in here</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

