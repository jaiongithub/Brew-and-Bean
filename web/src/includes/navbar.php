<?php
$user = currentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-brew sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="/index.php">
            <i class="fa-solid fa-mug-hot"></i>
            <span>BREW & BEAN</span>
        </a>
        <button class="navbar-toggler text-white border-light" type="button" data-bs-toggle="collapse" data-bs-target="#brewNavbar">
            <i class="fa-solid fa-bars text-white"></i>
        </button>

        <div class="collapse navbar-collapse" id="brewNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'index.php') ? 'active' : '' ?>" href="/index.php">
                        <i class="fa-solid fa-house me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'menu.php') ? 'active' : '' ?>" href="/menu.php">
                        <i class="fa-solid fa-book-open me-1"></i> Menu & Roasts
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'track-delivery.php') ? 'active' : '' ?>" href="/track-delivery.php">
                        <i class="fa-solid fa-truck-fast me-1"></i> Track Delivery
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'reviews.php') ? 'active' : '' ?>" href="/reviews.php">
                        <i class="fa-solid fa-star me-1"></i> Reviews
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-2">
                    <a class="nav-link position-relative" href="/cart.php" title="Shopping Cart">
                        <i class="fa-solid fa-bag-shopping fs-5"></i>
                        <span id="cart-badge-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;">0</span>
                    </a>
                </li>

                <?php if ($user): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userMenu" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-circle-user fs-5 text-warning"></i>
                            <span><?= htmlspecialchars($user['full_name']) ?></span>
                            <span class="badge bg-secondary text-uppercase" style="font-size:0.65rem;"><?= htmlspecialchars($user['role']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><h6 class="dropdown-header">Signed in as <strong><?= htmlspecialchars($user['username']) ?></strong></h6></li>
                            <li><a class="dropdown-item" href="/orders.php"><i class="fa-solid fa-receipt me-2"></i>My Orders</a></li>
                            <li><a class="dropdown-item" href="/profile.php"><i class="fa-solid fa-id-card me-2"></i>My Profile</a></li>
                            
                            <?php if (in_array($user['role'], ['staff', 'manager', 'admin'])): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-primary" href="/staff/index.php"><i class="fa-solid fa-mug-saucer me-2"></i>Staff Portal</a></li>
                            <?php endif; ?>

                            <?php if ($user['role'] === 'admin'): ?>
                                <li><a class="dropdown-item text-danger" href="/admin/index.php"><i class="fa-solid fa-shield-halved me-2"></i>Admin Console</a></li>
                            <?php endif; ?>

                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Sign Out</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage === 'login.php') ? 'active' : '' ?>" href="/login.php">
                            <i class="fa-solid fa-right-to-bracket me-1"></i> Sign In
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-brew ms-2" href="/register.php">
                            <i class="fa-solid fa-user-plus me-1"></i> Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

