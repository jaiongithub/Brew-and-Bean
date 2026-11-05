<?php
// BREW & BEAN Database and Global Configuration

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = getenv('DB_HOST') ?: 'brew-db';
$db_port = getenv('DB_PORT') ?: '3306';
$db_name = getenv('DB_NAME') ?: 'brewandbean';
$db_user = getenv('DB_USER') ?: 'brewuser';
$db_pass = getenv('DB_PASSWORD') ?: 'BrewBeanSecurePass2026!';

// Challenge Flag Definitions
define('FLAG_F01', getenv('FLAG_F01') ?: 'FLAG{BREW_USER_ENUMERATION_01}');
define('FLAG_F02', getenv('FLAG_F02') ?: 'FLAG{BREW_IDOR_02}');
define('FLAG_F03', getenv('FLAG_F03') ?: 'FLAG{BREW_VERTICAL_ACCESS_03}');
define('FLAG_F04', getenv('FLAG_F04') ?: 'FLAG{BREW_COMMAND_INJECTION_04}');
define('FLAG_F05', getenv('FLAG_F05') ?: 'FLAG{BREW_STORED_XSS_05}');
define('FLAG_F06', getenv('FLAG_F06') ?: 'FLAG{BREW_ROOT_ESCALATION_06}');

function getDb(): PDO {
    global $db_host, $db_port, $db_name, $db_user, $db_pass;
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        } catch (PDOException $e) {
            die("Database Connection Error: " . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['role'],
        'email' => $_SESSION['email'] ?? ''
    ];
}

function hasRole(string $role): bool {
    $user = currentUser();
    return $user && ($user['role'] === $role);
}

function isStaffOrAbove(): bool {
    $user = currentUser();
    return $user && in_array($user['role'], ['staff', 'manager', 'admin']);
}

function isAdmin(): bool {
    $user = currentUser();
    return $user && ($user['role'] === 'admin');
}

