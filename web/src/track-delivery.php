<?php
$pageTitle = "Real-Time Delivery & Gateway Diagnostics";
require_once __DIR__ . '/includes/header.php';

$db = getDb();
$diagnosticOutput = null;
$searchedOrder = null;
$searchedOrderNumber = trim($_GET['order'] ?? $_POST['order_number'] ?? '');

if (!empty($searchedOrderNumber)) {
    $stmt = $db->prepare("SELECT o.*, u.full_name as customer_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.order_number = :num LIMIT 1");
    $stmt->execute([':num' => $searchedOrderNumber]);
    $searchedOrder = $stmt->fetch();
}

// VULNERABILITY 4: OS Command Injection
// The diagnostic endpoint directly executes shell ping without sanitizing $gateway
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_diagnostic'])) {
    $gateway = $_POST['gateway'] ?? '127.0.0.1';
    
    // Intentionally vulnerable OS command execution
    // Directly interpolating user-controlled $gateway into the shell command
    $command = "ping -c 2 " . $gateway . " 2>&1";
    $diagnosticOutput = shell_exec($command);

    // Save diagnostic result into internal database table for audit log
    try {
        $logStmt = $db->prepare("INSERT INTO delivery_diagnostics_log (target_host, executed_by, command_output) 
                                VALUES (:host, :by, :out)");
        $userName = isLoggedIn() ? currentUser()['username'] : 'guest_dispatcher';
        $logStmt->execute([
            ':host' => $gateway,
            ':by' => $userName,
            ':out' => $diagnosticOutput
        ]);

        // Check if command injection was attempted (contains command separators)
        if (preg_match('/[;&|`$]/', $gateway)) {
            notifyVerifierEvent('BB-F04', 'os_command_injection_executed', [
                'input' => substr($gateway, 0, 100),
                'user' => $userName
            ]);
        }
    } catch (Exception $e) {
        // Silently continue
    }
}
?>

<div class="container my-5" style="max-width: 900px;">
    <div class="text-center mb-5">
        <h1 class="fw-bold mb-2"><i class="fa-solid fa-truck-fast me-2 text-warning"></i>Delivery & Logistics Dispatch</h1>
        <p class="text-muted lead">Check live shipment status and courier node transit diagnostics.</p>
    </div>

    <!-- Order Tracking Lookup -->
    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-magnifying-glass me-2 text-secondary"></i>Track Your Order Number</h5>
        </div>
        <div class="card-body p-4">
            <form method="GET" action="/track-delivery.php" class="row g-3">
                <div class="col-md-9">
                    <input type="text" name="order" class="form-control form-control-lg" placeholder="Enter Order # (e.g. BB-ORD-2026-104)" value="<?= htmlspecialchars($searchedOrderNumber) ?>" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-brew btn-lg w-100">
                        <i class="fa-solid fa-compass me-1"></i> Track
                    </button>
                </div>
            </form>

            <?php if ($searchedOrder): 
                $statusClass = 'badge-status-' . strtolower(str_replace(' ', '', $searchedOrder['status']));
            ?>
                <div class="mt-4 p-4 bg-light rounded border">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0"><?= htmlspecialchars($searchedOrder['order_number']) ?></h5>
                        <span class="badge <?= $statusClass ?> fs-6 px-3 py-2"><?= htmlspecialchars($searchedOrder['status']) ?></span>
                    </div>
                    <div class="row g-2 small text-muted">
                        <div class="col-sm-6"><strong>Customer:</strong> <?= htmlspecialchars($searchedOrder['customer_name']) ?></div>
                        <div class="col-sm-6"><strong>Total:</strong> $<?= number_format($searchedOrder['total_amount'], 2) ?></div>
                        <div class="col-12"><strong>Delivery Destination:</strong> <?= htmlspecialchars($searchedOrder['delivery_address']) ?></div>
                        <div class="col-12"><strong>Estimated Arrival:</strong> Today within scheduled delivery window (Dispatch Hub Seattle #01)</div>
                    </div>
                </div>
            <?php elseif (!empty($searchedOrderNumber)): ?>
                <div class="alert alert-warning mt-4 mb-0">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>No matching order found for "<?= htmlspecialchars($searchedOrderNumber) ?>".
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Logistics Network Diagnostic Tool (VULNERABLE COMPONENT) -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0 text-white"><i class="fa-solid fa-network-wired me-2 text-warning"></i>Courier Gateway Diagnostic Utility</h5>
                <small class="text-light opacity-75">Used by delivery dispatchers to probe route latency to fulfillment gateways.</small>
            </div>
            <span class="badge bg-secondary font-monospace">API Node: v2.4</span>
        </div>
        <div class="card-body p-4">
            <p class="text-muted small">
                Verify network responsiveness to local or regional courier gateway nodes. Default endpoint: <code>127.0.0.1</code> (Local Relay Gateway).
            </p>

            <form method="POST" action="/track-delivery.php" class="row g-3">
                <input type="hidden" name="run_diagnostic" value="1">
                <?php if (!empty($searchedOrderNumber)): ?>
                    <input type="hidden" name="order_number" value="<?= htmlspecialchars($searchedOrderNumber) ?>">
                <?php endif; ?>
                
                <div class="col-md-9">
                    <label class="form-label small fw-bold">Courier API Gateway Host / IP</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-server"></i></span>
                        <input type="text" name="gateway" class="form-control" placeholder="127.0.0.1" value="<?= isset($_POST['gateway']) ? htmlspecialchars($_POST['gateway']) : '127.0.0.1' ?>" required>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-dark w-100 py-2">
                        <i class="fa-solid fa-satellite-dish me-1"></i> Ping Gateway
                    </button>
                </div>
            </form>

            <?php if ($diagnosticOutput !== null): ?>
                <div class="mt-4">
                    <h6 class="fw-bold mb-2 text-dark"><i class="fa-solid fa-terminal me-2"></i>Gateway Diagnostic Response:</h6>
                    <pre class="bg-dark text-success p-3 rounded font-monospace" style="white-space: pre-wrap; font-size: 0.9rem; max-height: 400px; overflow-y: auto;"><?= htmlspecialchars($diagnosticOutput) ?></pre>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-light text-muted small py-2">
            <i class="fa-solid fa-circle-info me-1"></i> Diagnostic execution runs in system container context under non-root service pool.
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

