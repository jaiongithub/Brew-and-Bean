<?php
$pageTitle = "Sign In to BREW & BEAN";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';

$error = null;
$unlockedFlag = null;
$redirect = $_GET['redirect'] ?? '/index.php';

if (isLoggedIn()) {
    header("Location: " . $redirect);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // VULNERABILITY 1: attemptLogin() produces distinguishable responses for valid vs invalid usernames
        $result = attemptLogin($username, $password);
        if ($result['success']) {
            if ($username === 'alex_morgan') {
                $unlockedFlag = FLAG_F01;
            } else {
                header("Location: " . $redirect);
                exit;
            }
        } else {
            $error = $result['error'];
        }
    }
}
?>

<div class="container my-5" style="max-width: 500px;">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="fs-1 text-warning mb-2"><i class="fa-solid fa-mug-hot"></i></div>
                <h3 class="fw-bold">Sign In to Your Account</h3>
                <p class="text-muted small">Access your coffee orders, bean subscriptions, and member discounts.</p>
            </div>

            <?php if ($unlockedFlag): ?>
                <!-- BB-F01 Flag Release Banner upon successful compromise of alex_morgan -->
                <div class="flag-banner mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-flag text-warning fs-4"></i>
                        <h6 class="mb-0 fw-bold text-light">CHALLENGE FLAG VERIFICATION [BB-F01]</h6>
                    </div>
                    <div class="mb-2">You successfully enumerated account <strong>alex_morgan</strong> and audited the weak password!</div>
                    <div class="p-2 bg-dark rounded text-warning font-monospace fs-6">
                        <?= htmlspecialchars($unlockedFlag) ?>
                    </div>
                    <div class="mt-3 text-end">
                        <a href="/orders.php" class="btn btn-sm btn-success">Proceed to Customer Portal &rarr;</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center small" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login.php<?= !empty($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-user"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="e.g. alex_morgan" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-bold mb-0">Password</label>
                        <a href="#" class="small text-muted text-decoration-none">Forgot Password?</a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-key"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-brew btn-lg w-100 mb-3">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In
                </button>
            </form>

            <div class="text-center small text-muted">
                Don't have an account yet? <a href="/register.php" class="text-warning fw-semibold">Register here</a>
            </div>
        </div>
        <div class="card-footer bg-light text-center small text-muted py-3">
            <i class="fa-solid fa-shield-halved me-1"></i> Authorized cafe members and staff access only.
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

