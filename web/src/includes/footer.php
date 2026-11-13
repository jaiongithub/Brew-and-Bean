</main>

<footer>
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="text-white fw-bold"><i class="fa-solid fa-mug-hot me-2 text-warning"></i>BREW & BEAN</h5>
                <p class="small mb-2" style="color: #ede3d4;">Artisan micro-roastery and specialty cafe dedicated to ethically sourced single-origin coffees, small-batch pastries and exceptional brewing craft.</p>
                <p class="small" style="color: #ede3d4;"><i class="fa-solid fa-location-dot me-1 text-warning"></i> 404 Espresso Blvd, Seattle, WA</p>
            </div>
            <div class="col-md-3">
                <h6 class="text-white fw-semibold mb-2">Quick Links</h6>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-1"><a href="/menu.php"><i class="fa-solid fa-chevron-right me-1 small"></i>Seasonal Coffee Offerings</a></li>
                    <li class="mb-1"><a href="/track-delivery.php"><i class="fa-solid fa-chevron-right me-1 small"></i>Live Delivery Tracker</a></li>
                    <li class="mb-1"><a href="/reviews.php"><i class="fa-solid fa-chevron-right me-1 small"></i>Customer Reviews</a></li>
                    <li class="mb-1"><a href="/login.php"><i class="fa-solid fa-chevron-right me-1 small"></i>Partner & Staff Login</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6 class="text-white fw-semibold mb-2">Operating Hours</h6>
                <p class="small mb-1" style="color: #ede3d4;">Monday - Friday: 06:00 AM - 08:00 PM</p>
                <p class="small mb-1" style="color: #ede3d4;">Saturday - Sunday: 07:00 AM - 09:00 PM</p>
                <p class="small" style="color: #ede3d4;"><i class="fa-solid fa-phone me-1 text-warning"></i> +1 (555) 019-BREW</p>
            </div>
            <div class="col-md-2">
                <h6 class="text-white fw-semibold mb-2">Cyber Range Lab</h6>
                <a href="http://localhost:8000" target="_blank" class="btn btn-outline-warning btn-sm w-100 mb-2 py-1 fw-bold" style="font-size: 0.75rem;">
                    <i class="fa-solid fa-shield-halved me-1"></i> Verifier Portal
                </a>
                <button type="button" class="btn btn-outline-danger btn-sm w-100 py-1 fw-bold" style="font-size: 0.75rem;" onclick="resetLabEnvironment()">
                    <i class="fa-solid fa-arrows-rotate me-1"></i> Reset Lab
                </button>
            </div>
        </div>
        <hr class="my-4" style="border-color: rgba(199, 156, 110, 0.3);">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center small" style="color: #d8cebe;">
            <p class="mb-0">&copy; <?= date('Y') ?> BREW & BEAN Co. All rights reserved. Intentionally Vulnerable Cyber Range Lab.</p>
            <div>
                <a href="/menu.php" class="me-3">Menu</a>
                <a href="/reviews.php">Feedback</a>
            </div>
        </div>
    </div>
</footer>

<script>
async function resetLabEnvironment() {
    if (!confirm('⚠️ WARNING: This will reset the target database, clear all active user sessions, delete student comments, clear exploit files, and reset candidate progress. Reset lab now?')) {
        return;
    }
    try {
        // Clear client cookies
        document.cookie = 'PHPSESSID=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT; Max-Age=0;';
        await fetch('/api/internal-reset.php', { method: 'POST' });
        try {
            await fetch('http://' + window.location.hostname + ':8000/api/reset_lab', { method: 'POST' });
        } catch (_) {}
        alert('Lab environment, user sessions, and database successfully reset to fresh initial state.');
        window.location.href = '/index.php';
    } catch (e) {
        alert('Reset request error: ' + e.message);
    }
}
</script>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Application Script -->
<script src="/assets/js/app.js"></script>
</body>
</html>

