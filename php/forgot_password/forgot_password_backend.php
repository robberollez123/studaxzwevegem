<?php
session_start();
require_once __DIR__ . '/../../config/database.php';  // Database verbinding
require 'mail_function.php'; // Functie om e-mails te versturen

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    // Controleer of de gebruiker bestaat
    $stmt = $linkDB->prepare("SELECT id FROM gebruikers WHERE gebruikersnaam = ? AND email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(32)); // Genereer een veilige token
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Sla de token op in de database
        $stmt = $linkDB->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user['id'], $token, $expires);
        $stmt->execute();

        // Verstuur reset e-mail
        $reset_link = "https://studaxzwevegem.robberollez.be/php/forgot_password/reset_password.php?token=$token";
        $subject = "Wachtwoord resetten";
        $message = "
            <html>
            <head>
                <title>Wachtwoord Resetten</title>
            </head>
            <body>
                <p>Beste,</p>
                <p>We hebben een verzoek ontvangen om je wachtwoord te resetten. Klik op de onderstaande link om je wachtwoord opnieuw in te stellen:</p>
                <p><a href=\"$reset_link\">Wachtwoord resetten</a></p>
                <p>Als je dit verzoek niet hebt gedaan, neem zo snel mogelijk contact op.</p>
                <p>Met vriendelijke groet,<br>Studax Zwevegem</p>
            </body>
            </html>
        ";
        send_email($email, $subject, $message);

        header("Location: forgot_password.php?success=E-mail verzonden! Controleer je inbox.");
        exit;
    } else {
        header("Location: forgot_password.php?error=Gebruiker niet gevonden.");
        exit;
    }
}
?>
