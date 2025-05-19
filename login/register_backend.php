<?php
// register_backend.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Databaseverbinding
require_once __DIR__ . '/../config/database.php';

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    // Haal en filter de input
    $naam            = mysqli_real_escape_string($linkDB, trim($_POST['name']));
    $username        = mysqli_real_escape_string($linkDB, trim($_POST['username']));
    $email           = mysqli_real_escape_string($linkDB, trim($_POST['email']));
    $password        = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validatie
    if (empty($naam) || empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "Vul alle verplichte velden in!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Ongeldig e-mailadres!";
    } elseif ($password !== $confirmPassword) {
        $error = "De wachtwoorden komen niet overeen!";
    } else {
        // Controleer of gebruikersnaam of e-mail al bestaat
        $stmt = mysqli_prepare(
            $linkDB,
            "SELECT id FROM gebruikers WHERE gebruikersnaam = ? OR email = ?"
        );
        mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Deze gebruikersnaam of e-mail is al in gebruik!";
        }
        mysqli_stmt_close($stmt);
    }

    // Bij foutmelding terugsturen
    if (isset($error)) {
        header("Location: register.php?status=error&message=" . urlencode($error));
        exit;
    }

    // Maak gebruiker aan als inactief (is_active = 0)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare(
        $linkDB,
        "INSERT INTO gebruikers
        (naam, gebruikersnaam, wachtwoord, email, isAdmin, is_active, timestamp)
        VALUES (?, ?, ?, ?, 0, 0, NOW())"
    );
    mysqli_stmt_bind_param(
        $stmt,
        'ssss',
        $naam,
        $username,
        $hashedPassword,
        $email
    );

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: register.php?status=error&message=" . urlencode("Fout bij het aanmaken van account. Probeer opnieuw."));
        exit;
    }
    $userId = mysqli_insert_id($linkDB);
    mysqli_stmt_close($stmt);

    // ====== NIEUW: genereer cryptografisch veilige token ipv numerieke code ======
    try {
        $token = bin2hex(random_bytes(32)); // 64 hex-tekens
    } catch (Exception $e) {
        // Fout bij random_bytes
        header("Location: register.php?status=error&message=" . urlencode("Er is iets misgegaan. Probeer opnieuw."));
        exit;
    }

    // Bepaal expiry (bijv. 24 uur geldig)
    $expiresAt = date('Y-m-d H:i:s', time() + 24 * 60 * 60);

    // Sla token op in tabel 'verificaties' (mét kolom 'method' = 'email')
    $stmt2 = mysqli_prepare(
        $linkDB,
        "INSERT INTO verificaties (user_id, token, method, expires_at)
         VALUES (?, ?, 'email', ?)"
    );
    mysqli_stmt_bind_param(
        $stmt2,
        'iss',
        $userId,
        $token,
        $expiresAt
    );
    if (!mysqli_stmt_execute($stmt2)) {
        header("Location: register.php?status=error&message=" . urlencode("Er is iets misgegaan. Probeer het later opnieuw."));
        exit;
    }
    mysqli_stmt_close($stmt2);

    // Verstuur de e‑mail met de verificatielink
    // Bepaal domein + pad naar verify.php
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host     = $_SERVER['HTTP_HOST'];
    // Stel je eigen pad in, bijvoorbeeld '/php/inschrijven' afhankelijk van jouw folderstructuur
    $verifyPath = dirname($_SERVER['REQUEST_URI']) . '/verify.php';
    $verifyUrl  = "{$protocol}://{$host}{$verifyPath}?user_id={$userId}&token={$token}";

    $subject = 'Bevestig je account bij Studax Zwevegem';
    $message = "
    Beste {$naam},\n\n
    Bedankt voor je registratie. Klik op de link hieronder om je account te verifiëren:\n\n
    {$verifyUrl}\n\n
    Deze link is 24 uur geldig. Als je geen account hebt aangevraagd, kun je deze e‑mail negeren.\n\n
    Met vriendelijke groet, Studax Zwevegem.\n
    Deze e‑mail is automatisch gegenereerd. Gelieve niet te antwoorden.\n
    ";
    // Stuur een eenvoudige platte‐tekst‑mail
    $headers  = "From: no-reply@studaxzwevegem.be\r\n";
    $headers .= "Reply-To: no-reply@studaxzwevegem.be\r\n";

    mail($email, $subject, $message, $headers);
    // ============================================================================

    // Redirect terug naar register.php met status=pending_verification (geen redirect naar verify.php!)
    header("Location: register.php?status=pending_verification");
    exit;
}
