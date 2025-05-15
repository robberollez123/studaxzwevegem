<?php
session_start();
require_once __DIR__ . '/../../config/database.php'; // Database verbinding

if (!isset($_GET['token'])) {
    die('Geen token opgegeven.');
}

$token = $_GET['token'];

// Controleer of de token bestaat en geldig is
$stmt = $linkDB->prepare("SELECT pr.id, pr.user_id, pr.expires_at, u.gebruikersnaam, u.email FROM password_resets pr JOIN gebruikers u ON pr.user_id = u.id WHERE pr.token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die('Ongeldige of verlopen token.');
}

$row = $result->fetch_assoc();
$expires_at = new DateTime($row['expires_at']);
$current_time = new DateTime();

if ($expires_at < $current_time) {
    die('De reset link is verlopen.');
}

// Verwerk het nieuwe wachtwoord
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Basis validatie
    if (empty($new_password) || empty($confirm_password)) {
        die('Vul beide velden in.');
    } elseif ($new_password !== $confirm_password) {
        die('De wachtwoorden komen niet overeen.');
    } else {
        // Wachtwoord hashen
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Werk het wachtwoord bij in de database
        $stmt = $linkDB->prepare("UPDATE gebruikers SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $row['user_id']);
        $stmt->execute();

        // Verwijder de token zodat deze niet opnieuw kan worden gebruikt
        $stmt = $linkDB->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        header("Location: login.php?success=Wachtwoord succesvol gewijzigd! Je kunt nu inloggen.");
        exit;
    }
}
?>
