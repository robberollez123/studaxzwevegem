<?php
session_start();
require_once __DIR__ . '/../../config/database.php';  // Databaseverbinding inladen

// Controleer of de gebruiker ingelogd is en admin is
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: ../../login/login.php");  // Redirect naar loginpagina als de gebruiker niet ingelogd is of geen admin is
    exit;
}

$id = $_GET['id'] ?? null;
$error_message = '';

if ($id) {
    // Verwijder de wedstrijd uit de database
    $query = "DELETE FROM wedstrijden WHERE id = ?";
    $stmt = mysqli_prepare($linkDB, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: kalender.php");  // Redirect naar kalenderpagina na succesvol verwijderen
        exit;
    } else {
        $error_message = 'Er is een fout opgetreden bij het verwijderen van de wedstrijd.';
    }

    mysqli_stmt_close($stmt);
} else {
    $error_message = 'Geen wedstrijd geselecteerd.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wedstrijd Verwijderen</title>
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
                    <li class="nav-item"><a class="nav-link" href="kalender.php">Kalender</a></li>
                    <li class="nav-item"><a class="nav-link" href="../spelers/spelers.php">Spelers</a></li>
                    <li class="nav-item"><a class="nav-link" href="../inschrijven/inschrijven.php">Inschrijven</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h2>Wedstrijd Verwijderen</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <p>Weet je zeker dat je deze wedstrijd wilt verwijderen?</p>
        <a href="wedstrijd_verwijderen.php?id=<?php echo $id; ?>&confirm=true" class="btn btn-danger">Ja, Verwijderen</a>
        <a href="kalender.php" class="btn btn-secondary">Annuleren</a>
    </div>

        <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Studax Zwevegem. Alle rechten voorbehouden.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
