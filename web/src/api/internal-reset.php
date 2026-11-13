<?php
// BREW & BEAN - Internal Environment & Database Reset Handler
// Restores the target container and database to its pristine initial state

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

try {
    $db = getDb();

    // 1. Reset Reviews table: Remove student-submitted XSS exploits and flags
    $db->exec("DELETE FROM reviews WHERE id > 5");
    $db->exec("UPDATE reviews SET is_reviewed_by_staff = 1, staff_notes = 'Verified customer order - positive feedback.' WHERE id = 1");
    $db->exec("UPDATE reviews SET is_reviewed_by_staff = 1, staff_notes = 'Forwarded to head roaster.' WHERE id = 2");
    $db->exec("UPDATE reviews SET is_reviewed_by_staff = 1, staff_notes = 'Checked bar standards.' WHERE id = 3");
    $db->exec("UPDATE reviews SET is_reviewed_by_staff = 0, staff_notes = NULL WHERE id IN (4, 5)");
    $db->exec("ALTER TABLE reviews AUTO_INCREMENT = 6");

    // 2. Clear command injection diagnostic execution logs
    $db->exec("TRUNCATE TABLE delivery_diagnostics_log");

    // 3. Remove dynamically created users and orders (keep default seeded items)
    $db->exec("DELETE FROM users WHERE id > 7");
    $db->exec("ALTER TABLE users AUTO_INCREMENT = 8");
    $db->exec("DELETE FROM orders WHERE id > 110");
    $db->exec("ALTER TABLE orders AUTO_INCREMENT = 111");

    // 4. Restore cron privilege escalation script to original state
    $originalCronScript = "#!/bin/bash\n# BREW & BEAN Internal Maintenance - Cache Cleaner\n# Runs automatically every minute via system crontab\n\nCACHE_DIR=\"/var/www/html/tmp/cache\"\nLOG_FILE=\"/var/log/brew-maintenance.log\"\n\necho \"[\$(date '+%Y-%m-%d %H:%M:%S')] Starting automated cache cleanup...\" >> \"\$LOG_FILE\"\nif [ -d \"\$CACHE_DIR\" ]; then\n    find \"\$CACHE_DIR\" -type f -mmin +120 -delete 2>/dev/null || true\n    echo \"[\$(date '+%Y-%m-%d %H:%M:%S')] Cache cleanup completed successfully.\" >> \"\$LOG_FILE\"\nfi\n";

    if (file_exists('/cron/cleanup_cache.sh')) {
        @copy('/cron/cleanup_cache.sh', '/opt/scripts/cleanup_cache.sh');
    } else {
        @file_put_contents('/opt/scripts/cleanup_cache.sh', $originalCronScript);
    }
    @chmod('/opt/scripts/cleanup_cache.sh', 0777);

    // 5. Clean up any temporary exploit artifacts in /tmp and cache
    $tmpFiles = glob('/tmp/*');
    if ($tmpFiles) {
        foreach ($tmpFiles as $f) {
            if (is_file($f) && basename($f) !== '.gitkeep') {
                @unlink($f);
            }
        }
    }

    $cacheFiles = glob('/var/www/html/tmp/cache/*');
    if ($cacheFiles) {
        foreach ($cacheFiles as $cf) {
            if (is_file($cf) && basename($cf) !== '.gitkeep') {
                @unlink($cf);
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'BREW & BEAN target database and container environment reset to initial state.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
