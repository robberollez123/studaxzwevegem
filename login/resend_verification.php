<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/send_verification_link.php'; // zorg dat dit pad klopt

$feedbackMsg = '';
$errorMsg    = '';

// Bepaal de waarde om in het input‐veld te tonen:
$inputEmail = '';
// 1) Als er via GET een email is meegegeven (bijvoorbeeld door login.php), gebruik die:
if (isset($_GET['email']) && filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
    $inputEmail = trim($_GET['email']);
}
// 2) Als er een POST‐poging is gedaan, vul het ingevulde e‐mailadres terug in (bij fouten)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $inputEmail = trim($_POST['email']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Voer een geldig e‑mailadres in.";
    } else {
        // Zoek user_id op basis van e-mail. We gaan er vanuit dat e‑mail uniek is in 'gebruikers'.
        $stmt = mysqli_prepare(
            $linkDB,
            "SELECT id, is_active FROM gebruikers WHERE email = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $dbUserId, $dbIsActive);

        if (! mysqli_stmt_fetch($stmt)) {
            $errorMsg = "Er is geen account gekoppeld aan dit e‑mailadres.";
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);
            if ((int)$dbIsActive === 1) {
                $errorMsg = "Dit account is al geverifieerd, je kunt <a href=\"login.php\">inloggen</a>.";
            } else {
                // Verstuur nieuwe verificatielink
                // Haal domein dynamisch op, of zet handmatig je eigen domein:
                // Bijvoorbeeld: 'https://jouwdomein.nl'
                $linkDomain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
                              . "://{$_SERVER['HTTP_HOST']}";

                $sent = sendVerificationLink((int)$dbUserId, $email, $linkDomain);
                if ($sent) {
                    $feedbackMsg = "Er is een nieuwe verificatielink verstuurd naar <strong>" . htmlspecialchars($email, ENT_QUOTES) . "</strong>. Controleer je inbox (en eventueel je spamfolder).";
                } else {
                    $errorMsg = "Er is een fout opgetreden bij het verzenden. Probeer het later nogmaals.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Opnieuw Verificatielink Aanvragen</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <div class="col-md-6 offset-md-3">
      <h4>Nieuwe Verificatielink Aanvragen</h4>

      <?php if ($errorMsg !== ''): ?>
        <div class="alert alert-danger mt-3">
          <?= $errorMsg ?>
        </div>
      <?php endif; ?>

      <?php if ($feedbackMsg !== ''): ?>
        <div class="alert alert-success mt-3">
          <?= $feedbackMsg ?>
        </div>
      <?php else: ?>
        <form action="resend_verification.php" method="POST" class="mt-4">
          <div class="mb-3">
            <label for="email" class="form-label">E‑mailadres</label>
            <input

              type="email"
              name="email"
              id="email"
              class="form-control"
              required
              readonly
              value="<?= htmlspecialchars($inputEmail, ENT_QUOTES) ?>"
            >
          </div>
          <button type="submit" class="btn btn-primary">Verificatielink Versturen</button>
        </form>
      <?php endif; ?>

      <p class="mt-4">
        <a href="login.php">Terug naar inloggen</a>
      </p>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
