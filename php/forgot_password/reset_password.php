<?php
session_start();
require_once __DIR__ . '/../../config/database.php'; // Database verbinding

// Haal het token op uit de URL
if (!isset($_GET['token'])) {
    die('Geen token opgegeven.');
}

$token = $_GET['token'];

// Controleer of de token bestaat en geldig is
$stmt = $linkDB->prepare("SELECT pr.id, pr.user_id, pr.expires_at, u.gebruikersnaam, u.email 
                          FROM password_resets pr 
                          JOIN gebruikers u ON pr.user_id = u.id 
                          WHERE pr.token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die('Ongeldige of verlopen token.');
}

$row = $result->fetch_assoc();
$expires_at = new DateTime($row['expires_at']);
$current_time = new DateTime();

// Controleer of de token is verlopen
if ($expires_at < $current_time) {
    die('De reset link is verlopen.');
}

// Als de token geldig is, laat de gebruiker hun wachtwoord invoeren
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verkrijg het nieuwe wachtwoord van de gebruiker
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Basis validatie
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Vul beide velden in.";
    } elseif ($new_password !== $confirm_password) {
        $error = "De wachtwoorden komen niet overeen.";
    } else {
        // Wachtwoord hashen
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Werk het wachtwoord bij in de database
        $stmt = $linkDB->prepare("UPDATE gebruikers SET wachtwoord = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $row['user_id']);
        if ($stmt->execute()) {
            // Verwijder de token (zodat deze niet opnieuw kan worden gebruikt)
            $stmt = $linkDB->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();

            // Succesbericht
            $success = "Wachtwoord succesvol gewijzigd! Je kunt nu inloggen met je nieuwe wachtwoord.";
        } else {
            $error = "Er is een fout opgetreden bij het bijwerken van het wachtwoord.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wachtwoord Resetten</title>
    <link rel="stylesheet" href="../../css/style.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../../index.php">Studax Zwevegem</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="../../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="../kalender/kalender.php">Kalender</a></li>
                    <li class="nav-item"><a class="nav-link" href="../spelers/spelers.php">Spelers</a></li>
                    <li class="nav-item"><a class="nav-link" href="../inschrijven/inschrijven.php">Inschrijven</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3 class="text-center">Reset je Wachtwoord</h3>

                        <!-- Foutmelding -->
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Succesbericht -->
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                            <a href="../../login/login.php" class="btn btn-primary btn-block">Ga naar Inloggen</a>
                        <?php else: ?>
                            <form action="" method="POST">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nieuw Wachtwoord</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Voer je nieuwe wachtwoord in" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Bevestig Wachtwoord</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Bevestig je nieuwe wachtwoord" required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Wachtwoord Wijzigen</button>
                            </form>
                        <?php endif; ?>

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
