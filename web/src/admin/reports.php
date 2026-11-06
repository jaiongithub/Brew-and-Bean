<?php
$pageTitle = "Confidential Corporate Reports";
require_once __DIR__ . '/../includes/header.php';

// ==============================================================================
// VULNERABILITY 3: Vertical Privilege Escalation (Broken Function-Level Auth)
// Notice: Checks if user is authenticated, but FAILS to verify role == 'admin'!
// Any authenticated customer or staff user can access this sensitive admin report!
// ==============================================================================
if (!isLoggedIn()) {
    header("Location: /login.php?redirect=/admin/reports.php");
    exit;
}

$currentUser = currentUser();
$db = getDb();

// Fetch reports
$stmt = $db->query("SELECT * FROM reports ORDER BY id ASC");
$reports = $stmt->fetchAll();

$isVerticalExploit = ($currentUser['role'] !== 'admin');
if ($isVerticalExploit) {
    notifyVerifierEvent('BB-F03', 'bfla_report_access', ['user' => $currentUser['username'], 'role' => $currentUser['role']]);
}
?>

<div class="container my-5" style="max-width: 950px;">
    <!-- Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fa-solid fa-file-shield text-danger me-2"></i>Executive Audit & Financial Reports</h2>
            <p class="text-muted mb-0">Confidential Internal Reports - Head Roastery Management</p>
        </div>
        <div>
            <span class="badge bg-secondary p-2">Session: <?= htmlspecialchars($currentUser['username']) ?> (<?= htmlspecialchars($currentUser['role']) ?>)</span>
        </div>
    </div>

    <?php if ($isVerticalExploit): ?>
        <!-- Vertical Privilege Escalation Callout Banner -->
        <div class="alert alert-warning border-2 shadow-sm mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fa-solid fa-triangle-exclamation text-warning fs-4"></i>
                <h5 class="alert-heading mb-0 fw-bold">Vertical Privilege Escalation Demonstrated (BFLA)</h5>
            </div>
            <p class="mb-2">
                You have accessed a restricted administrative endpoint (<code>/admin/reports.php</code>) while authenticated with the lower-privileged role: 
                <span class="badge bg-danger text-uppercase"><?= htmlspecialchars($currentUser['role']) ?></span>.
            </p>
            <div class="p-3 bg-dark text-success rounded font-monospace mt-2">
                <div class="fw-bold text-light mb-1"><i class="fa-solid fa-flag me-2 text-danger"></i>CHALLENGE FLAG VERIFICATION [BB-F03]:</div>
                <span class="fs-5 text-warning"><?= FLAG_F03 ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Reports Accordion -->
    <div class="accordion shadow-sm" id="reportsAccordion">
        <?php foreach ($reports as $idx => $rep): ?>
            <div class="accordion-item border mb-3 rounded overflow-hidden">
                <h2 class="accordion-header" id="heading<?= $rep['id'] ?>">
                    <button class="accordion-button <?= ($idx === 0) ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $rep['id'] ?>">
                        <div class="d-flex align-items-center gap-3 w-100">
                            <span class="badge bg-danger"><?= htmlspecialchars($rep['report_type']) ?></span>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($rep['title']) ?></span>
                            <small class="text-muted ms-auto me-3"><i class="fa-solid fa-user-pen me-1"></i><?= htmlspecialchars($rep['created_by']) ?></small>
                        </div>
                    </button>
                </h2>
                <div id="collapse<?= $rep['id'] ?>" class="accordion-collapse collapse <?= ($idx === 0) ? 'show' : '' ?>" data-bs-parent="#reportsAccordion">
                    <div class="accordion-body p-4 bg-white">
                        <h6 class="fw-bold text-muted text-uppercase small mb-2">Executive Summary</h6>
                        <p class="text-secondary"><?= htmlspecialchars($rep['summary']) ?></p>

                        <h6 class="fw-bold text-danger text-uppercase small mb-2 mt-4"><i class="fa-solid fa-lock me-1"></i>Classified Internal Content</h6>
                        <pre class="bg-light p-3 rounded border font-monospace text-dark" style="white-space: pre-wrap; font-size: 0.88rem;"><?= htmlspecialchars($rep['confidential_content']) ?></pre>

                        <div class="small text-muted text-end mt-3">
                            <i class="fa-solid fa-clock me-1"></i>Archived Timestamp: <?= date('F d, Y - H:i', strtotime($rep['created_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

