<?php
session_start();  // Start de sessie om de loginstatus te kunnen controleren

// Als de gebruiker al ingelogd is, doorverwijzen naar de homepage
if (isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

// Als er een remember-me cookie is, probeer automatisch in te loggen
if (!isset($_SESSION['user']) && isset($_COOKIE['rememberme'])) {
    list($selector, $validator) = explode(':', $_COOKIE['rememberme']);

    require_once __DIR__ . '/../config/database.php';
    $stmt = mysqli_prepare(
        $linkDB,
        "SELECT user_id, token, expires_at 
           FROM auth_tokens 
          WHERE selector = ?"
    );
    mysqli_stmt_bind_param($stmt, 's', $selector);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $userId, $dbToken, $expiresAt);
    if (mysqli_stmt_fetch($stmt)) {
        if (new DateTime() < new DateTime($expiresAt) &&
            hash_equals($dbToken, hash('sha256', base64_decode($validator)))
        ) {
            // Login succesvol via cookie
            $stmt->close();
            $stmt = mysqli_prepare($linkDB, "SELECT naam FROM gebruikers WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $userId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $userName);
            mysqli_stmt_fetch($stmt);
            $_SESSION['user'] = $userName;
            header("Location: ../index.php");
            exit;
        }
    }
    mysqli_stmt_close($stmt);
    // Ongeldige cookie: verwijderen
    setcookie('rememberme', '', time() - 3600, '/');
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Studax Zwevegem</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="../php/kalender/kalender.php">Kalender</a></li>
                    <li class="nav-item"><a class="nav-link" href="../php/spelers/spelers.php">Spelers</a></li>
                    <li class="nav-item"><a class="nav-link" href="../php/inschrijven/inschrijven.php">Inschrijven</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card login-card">
                    <div class="card-body">
                        <h3 class="text-center mb-4">Inloggen</h3>

                        <?php
                        // login.php (fragment bovenaan, vóór je <form>)
                        // ============================================

                        if (isset($_GET['error'])) {
                            $error = $_GET['error'];
                            $email = isset($_GET['email']) ? htmlspecialchars($_GET['email'], ENT_QUOTES) : '';

                            // Zet de error‐code om naar een vriendelijke, Nederlandse tekst
                            if ($error === 'empty_fields') {
                                $msg = "Vul zowel je gebruikersnaam als wachtwoord in.";
                            }
                            elseif ($error === 'not_found') {
                                $msg = "De opgegeven gebruikersnaam bestaat niet. Controleer je invoer.";
                            }
                            elseif ($error === 'wrong_password') {
                                $msg = "Het ingevulde wachtwoord is onjuist. Probeer het opnieuw.";
                            }
                            elseif ($error === 'not_verified') {
                                // Bij een nog niet geverifieerd account bieden we een link om de mail opnieuw te sturen
                                $resendLink = "resend_verification.php?email=" . urlencode($email);
                                $msg = "Je account is nog niet geverifieerd. Controleer je e‑mail voor de verificatielink. "
                                    . "Heb je de e‑mail niet ontvangen? "
                                    . "<a href=\"{$resendLink}\">Klik hier om de verificatiemail opnieuw te sturen</a>.";
                            }
                            else {
                                // Algemene fallback‐melding
                                $msg = "Er is iets misgegaan. Probeer het nogmaals.";
                            }

                            echo '<div class="alert alert-danger" role="alert" style="word-wrap: break-word;">'
                                . $msg .
                                '</div>';
                        }
                        ?>
                        <form action="login_backend.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Gebruikersnaam</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Voer je gebruikersnaam in" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Wachtwoord</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Voer je wachtwoord in" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
                                <label class="form-check-label" for="rememberMe">Onthoud mij</label>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Inloggen</button>
                            </div>
                            <div class="mt-3 text-center">
                                <a href="../php/forgot_password/forgot_password.php">Wachtwoord vergeten?</a>
                            </div>
                        </form>
                        <div class="mt-3 text-center">
                            <p>Heb je nog geen account? <a href="register.php">Registreer hier</a></p> <!-- Link naar registratiepagina -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Studax Zwevegem. Alle rechten voorbehouden.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
