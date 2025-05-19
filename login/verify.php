<?php
session_start();

// Tijdens development: foutmeldingen tonen
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Databaseverbinding
require_once __DIR__ . '/../config/database.php';

// Haal GET-waarden op: user_id + token
$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
$token  = isset($_GET['token'])   ? trim($_GET['token'])  : '';

if ($userId <= 0 || $token === '') {
    // Ongeldige aanvraag (missende of onjuiste parameters)
    $errorMsg = "Ongeldige verificatie-aanvraag.";
} else {
    // 1) Probeer precies die token op te halen bij deze user_id
    $stmt = mysqli_prepare(
        $linkDB,
        "SELECT expires_at
           FROM verificaties
          WHERE user_id = ?
            AND token = ?
          LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 'is', $userId, $token);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $expiresAtRaw);

    if (! mysqli_stmt_fetch($stmt)) {
        // Geen rij gevonden die exact bij deze user_id én token hoort
        mysqli_stmt_close($stmt);
        $errorMsg = "Ongeldige verificatielink. Vraag een nieuwe link aan.";
    } else {
        mysqli_stmt_close($stmt);

        $expiresAt = trim($expiresAtRaw);

        // 2) Controleer expiry
        if (time() > strtotime($expiresAt)) {
            $errorMsg = "De verificatielink is verlopen. Vraag een nieuwe aan.";
        } else {
            // Alles oké: activeer account
            $stmt2 = mysqli_prepare(
                $linkDB,
                "UPDATE gebruikers
                    SET is_active = 1
                  WHERE id = ?"
            );
            mysqli_stmt_bind_param($stmt2, 'i', $userId);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);

            // Verwijder alle tokens voor deze user (voor de veiligheid)
            $deleteStmt = mysqli_prepare(
                $linkDB,
                "DELETE FROM verificaties
                  WHERE user_id = ?"
            );
            mysqli_stmt_bind_param($deleteStmt, 'i', $userId);
            mysqli_stmt_execute($deleteStmt);
            mysqli_stmt_close($deleteStmt);

            $successMsg = "Je account is succesvol geverifieerd. Je kunt nu <a href=\"login.php\">inloggen</a>.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Account Verificatie</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <div class="col-md-6 offset-md-3 text-center">
      <h4>Account Verificatie</h4>

      <?php if (isset($errorMsg)): ?>
        <div class="alert alert-danger mt-4">
          <?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <p>
          Heb je nog geen link ontvangen of is je link verlopen? 
          <a href="resend_verification.php">Klik hier om een nieuwe verificatielink aan te vragen</a>.
        </p>
      <?php elseif (isset($successMsg)): ?>
        <div class="alert alert-success mt-4">
          <?= $successMsg /* Let op: bevat een veilige <a> naar login.php */ ?>
        </div>
      <?php endif; ?>

    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
