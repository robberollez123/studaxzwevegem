<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
    header("Location: login.php");
    exit;
}

$username   = mysqli_real_escape_string($linkDB, trim($_POST["username"]));
$password   = $_POST["password"];
$rememberMe = isset($_POST["rememberMe"]);

if (empty($username) || empty($password)) {
    header("Location: login.php?error=empty_fields");
    exit;
}

// 1) Haal gebruiker op inclusief verificatiestatus en e-mail
$stmt = mysqli_prepare(
    $linkDB,
    "SELECT id, naam, wachtwoord, is_active, email
       FROM gebruikers
      WHERE gebruikersnaam = ?
      LIMIT 1"
);
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) === 0) {
    mysqli_stmt_close($stmt);
    header("Location: login.php?error=not_found");
    exit;
}

mysqli_stmt_bind_result($stmt, $userId, $userName, $hashedPassword, $isActive, $email);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// 2) Controleer wachtwoord
if (!password_verify($password, $hashedPassword)) {
    header("Location: login.php?error=wrong_password");
    exit;
}

// 3) Controleer of account geverifieerd is (is_active = 1)
if ((int)$isActive !== 1) {
    // Account is nog niet geactiveerd: stuur een korte code + email door
    header(
        "Location: login.php"
        . "?error=not_verified"
        . "&email=" . urlencode($email)
    );
    exit;
}

// 4) Alles in orde: maak sessie aan
$_SESSION['user'] = $userName;

// 5) Remember Me: genereer token en sla op in auth_tokens
if ($rememberMe) {
    $selector  = bin2hex(random_bytes(8));
    $validator = random_bytes(32);
    $expires   = new DateTime('+30 days');

    $hashedValidator = hash('sha256', $validator);

    $stmt2 = mysqli_prepare(
        $linkDB,
        "INSERT INTO auth_tokens (user_id, selector, token, expires_at)
         VALUES (?, ?, ?, ?)"
    );
    $expiresAt = $expires->format('Y-m-d H:i:s');
    mysqli_stmt_bind_param($stmt2, 'isss', $userId, $selector, $hashedValidator, $expiresAt);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);

    setcookie(
        'rememberme',
        $selector . ':' . base64_encode($validator),
        time() + 60 * 60 * 24 * 30,
        '/',
        '',
        true,
        true
    );
}

header("Location: ../index.php");
exit;
