<?php
// send_verification_link.php
// ===================================
// Functie om een verificatielink te genereren en per e‑mail te versturen.

require_once __DIR__ . '/../config/database.php'; // je database-connection ($linkDB)
                                                  // Je kunt ook de standaard mail() gebruiken, maar hier geven we een voorbeeld met PHP's mail().

// ==============================================
// Functie: sendVerificationLink
// parameters:
//   $userId    (int)   : ID van de gebruiker in 'gebruikers'-tabel.
//   $userEmail (string): Het e‑mailadres van de gebruiker.
//   $linkDomain(string): Het domein/URL (zonder slash) waarop verify.php draait, bijv. 'https://jouwdomein.nl'.
// ==============================================
function sendVerificationLink(int $userId, string $userEmail, string $linkDomain): bool {
    global $linkDB;

    // 1) Genereer een veilige random token (lengte 64 hex-tekens)
    try {
        $token = bin2hex(random_bytes(32)); // 32 bytes -> 64 hex-chars
    } catch (Exception $e) {
        error_log("Kon random_bytes niet genereren: " . $e->getMessage());
        return false;
    }

    // 2) Bepaal expiry-tijd (bv. 24 uur geldig)
    $expiresAt = date('Y-m-d H:i:s', time() + 24 * 60 * 60);

    // 3) Sla op in tabel 'verificaties'
    $stmt = mysqli_prepare(
        $linkDB,
        "INSERT INTO verificaties (user_id, token, expires_at) VALUES (?, ?, ?)"
    );
    if (! $stmt) {
        error_log("Prepared statement mislukt: " . mysqli_error($linkDB));
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'iss', $userId, $token, $expiresAt);
    if (! mysqli_stmt_execute($stmt)) {
        error_log("Uitvoeren INSERT mislukt: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
    mysqli_stmt_close($stmt);

    // 4) Maak verificatielink
    $verifyUrl = rtrim($linkDomain, '/') . "/verify.php?user_id={$userId}&token={$token}";

    // 5) Stel e‑mail samen
    $subject = "Bevestig je account";
    $message = "
    <html>
      <head>
        <title>Bevestig je account bij Studax Zwevegem</title>
      </head>
      <body>
        <p>Bevestig je account,</p>
        <p>Dank je wel voor je registratie. Klik op onderstaande link om je account te activeren:</p>
        <p><a href=\"{$verifyUrl}\" target=\"_blank\">Verifieer je account</a></p>
        <p>Deze link is 24 uur geldig. Indien je geen account hebt aangevraagd, kun je deze e‑mail negeren.</p>
        <br>
        <p>Met vriendelijke groet,<br>Studax Zwevegem.</p>
        <p>Deze e‑mail is automatisch gegenereerd. Gelieve niet te antwoorden.</p>
      </body>
    </html>
    ";

    // 6) Stuur de e‑mail (we maken een eenvoudige mail()‑aanroep met headers)
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    // Stel hier de “From” in naar een geldig e-mailadres op jouw domein:
    $headers .= "From: no-reply@studaxzwevegem.be\r\n";
    $headers .= "Reply-To: no-reply@studaxzwevegem.be\r\n";

    // Gebruik PHP's mail(). Let op: in productie kun je beter PHPMailer of een andere SMTP‑client gebruiken.
    $mailSuccess = mail($userEmail, $subject, $message, $headers);

    if (! $mailSuccess) {
        error_log("mail() gaf false bij verzenden naar {$userEmail}");
        return false;
    }

    return true;
}
