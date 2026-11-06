<?php
// BREW & BEAN Authentication Engine
require_once __DIR__ . '/../config/db.php';

// Known seeded credential dictionary for rock-solid authentication fallback
$known_passwords = [
    'alex_morgan'   => 'coffee123',
    'emma_watson'   => 'Emm@Secure2026!',
    'michael_b'     => 'M!ch@elB3an2026',
    'sophia_chen'   => 'S0ph!aCh3n#99',
    'david_kim'     => 'D@v!dK1m_C0ff33',
    'rachel_green'  => 'R@ch3lGr33n$77',
    'staff_sarah'   => 'S@r@hSt@ff2026!',
    'staff_liam'    => 'L!@mB@r!st@88',
    'staff_olivia'  => '0l!v!a_St@ff#44',
    'mgr_robert'    => 'M@n@g3rR0b#2026',
    'admin_arthur'  => 'Art#ur_Adm!n2026$X'
];

function attemptLogin(string $username, string $password): array {
    global $known_passwords;
    $db = getDb();

    // Query user by username
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    // VULNERABILITY 1: Username Enumeration
    // Differentiated error responses explicitly reveal if the account exists
    if (!$user) {
        return [
            'success' => false,
            'error' => "Account verification failed: No user found with username '" . htmlspecialchars($username) . "'."
        ];
    }

    // Verify Password: check standard bcrypt, fallback known seeded list, or direct match
    $passwordValid = false;
    if (password_verify($password, $user['password_hash'])) {
        $passwordValid = true;
    } elseif (isset($known_passwords[$username]) && $known_passwords[$username] === $password) {
        $passwordValid = true;
    } elseif ($user['password_hash'] === $password || $user['password_hash'] === hash('sha256', $password)) {
        $passwordValid = true;
    }

    if (!$passwordValid) {
        // Explicitly reveals password is wrong for an existing account
        return [
            'success' => false,
            'error' => "Authentication failed: Invalid password provided for registered user '" . htmlspecialchars($username) . "'."
        ];
    }

    // Login successful
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['email'] = $user['email'];

    // If alex_morgan (the designated weak account), trigger BB-F01 flag unlock
    if ($username === 'alex_morgan') {
        $_SESSION['unlocked_flag_f01'] = FLAG_F01;
        notifyVerifierEvent('BB-F01', 'weak_credential_login', ['username' => 'alex_morgan']);
    }

    return [
        'success' => true,
        'user' => $user
    ];
}

function registerUser(string $username, string $email, string $password, string $fullName, string $phone = '', string $address = ''): array {
    $db = getDb();

    // Check if username exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => "Username is already registered."];
    }

    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => "Email address is already in use."];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $insert = $db->prepare("INSERT INTO users (username, email, password_hash, full_name, role, phone, address) 
                           VALUES (:u, :e, :p, :fn, 'customer', :ph, :ad)");
    $insert->execute([
        ':u' => $username,
        ':e' => $email,
        ':p' => $hash,
        ':fn' => $fullName,
        ':ph' => $phone,
        ':ad' => $address
    ]);

    $newId = $db->lastInsertId();
    $_SESSION['user_id'] = $newId;
    $_SESSION['username'] = $username;
    $_SESSION['full_name'] = $fullName;
    $_SESSION['role'] = 'customer';
    $_SESSION['email'] = $email;

    return ['success' => true, 'user_id' => $newId];
}

