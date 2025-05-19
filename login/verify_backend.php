<?php
session_start();

// Tijdens development helpen foutmeldingen om te debuggen
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Databaseverbinding
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: verify.php');
    exit;
}

// Haal POST-waarden op
$userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
$code   = isset($_POST['code'])    ? trim($_POST['code'])        : '';

if ($userId <= 0 || $code === '') {
    header("Location: verify.php?user_id={$userId}&error=" . urlencode('Ongeldige aanvraag.'));
    exit;
}

// Haal meest recente verificatie op
$stmt = mysqli_prepare(
    $linkDB,
    "SELECT code, expires_at 
       FROM verificaties 
      WHERE user_id = ? 
      ORDER BY id DESC 
      LIMIT 1"
);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $dbCodeRaw, $expiresAtRaw);

if (! mysqli_stmt_fetch($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: verify.php?user_id={$userId}&error=" . urlencode('Geen verificatiecode gevonden.'));
    exit;
}
mysqli_stmt_close($stmt);

// Trim waarden
$dbCode    = trim($dbCodeRaw);
$expiresAt = trim($expiresAtRaw);

// Optioneel: log wat je opvraagt (kijk in je PHP-error log)
error_log("Verificatie check - user {$userId}: dbCode='{$dbCode}', expiresAt='{$expiresAt}', provided='{$code}'");

// 1) Check of code overeenkomt
if ($code !== $dbCode) {
    header("Location: verify.php?user_id={$userId}&error=" . urlencode('Onjuiste verificatiecode.'));
    exit;
}

// 2) Check expiry met strtotime
if (time() > strtotime($expiresAt)) {
    header("Location: verify.php?user_id={$userId}&error=" . urlencode('Verificatiecode is verlopen.'));
    exit;
}

// Alles ok: activeer account
$stmt = mysqli_prepare(
    $linkDB,
    "UPDATE gebruikers 
        SET is_active = 1 
      WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// (Optioneel) verwijder oude codes
mysqli_query($linkDB, "DELETE FROM verificaties WHERE user_id = {$userId}");

header('Location: login.php?status=verified');
exit;
