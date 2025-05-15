<?php
session_start();
require_once __DIR__ . '/../../config/database.php';  // Databaseverbinding inladen

// Controleer of de gebruiker ingelogd is en admin is
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: ../login/login.php");  // Redirect naar loginpagina als de gebruiker niet ingelogd is of geen admin is
    exit;
}

$id = $_GET['id'] ?? null;
$error_message = '';

if ($id) {
    // Haal de wedstrijd op uit de database
    $query = "SELECT * FROM wedstrijden WHERE id = ?";
    $stmt = mysqli_prepare($linkDB, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $wedstrijd = mysqli_fetch_assoc($result);

    if (!$wedstrijd) {
        $error_message = 'Wedstrijd niet gevonden.';
    }

    mysqli_stmt_close($stmt);
} else {
    $error_message = 'Geen wedstrijd geselecteerd.';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $thuisteam = mysqli_real_escape_string($linkDB, trim($_POST['thuisteam']));
    $uitteam = mysqli_real_escape_string($linkDB, trim($_POST['uitteam']));
    $datum = mysqli_real_escape_string($linkDB, trim($_POST['datum']));
    $tijd = mysqli_real_escape_string($linkDB, trim($_POST['tijd']));
    $locatie = mysqli_real_escape_string($linkDB, trim($_POST['locatie']));

    // Bijwerken van de wedstrijd in de database
    $query = "UPDATE wedstrijden SET thuisteam = ?, uitteam = ?, datum = ?, tijd = ?, locatie = ? WHERE id = ?";
    $stmt = mysqli_prepare($linkDB, $query);
    mysqli_stmt_bind_param($stmt, "sssssi", $thuisteam, $uitteam, $datum, $tijd, $locatie, $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: kalender.php");  // Redirect naar kalenderpagina na succesvol bewerken
        exit;
    } else {
        $error_message = 'Er is een fout opgetreden bij het bewerken van de wedstrijd.';
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wedstrijd Bewerken</title>
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
                    <li class="nav-item"><a class="nav-link" href="kalender.php">Kalender</a></li>
                    <li class="nav-item"><a class="nav-link" href="../spelers/spelers.php">Spelers</a></li>
                    <li class="nav-item"><a class="nav-link" href="../inschrijven/inschrijven.php">Inschrijven</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <h2>Wedstrijd Bewerken</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="wedstrijd_bewerken.php?id=<?php echo $id; ?>" method="POST">
            <div class="mb-3">
                <label for="thuisteam" class="form-label">Thuisteam</label>
                <input type="text" class="form-control" id="thuisteam" name="thuisteam" value="<?php echo htmlspecialchars($wedstrijd['thuisteam']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="uitteam" class="form-label">Uitteam</label>
                <input type="text" class="form-control" id="uitteam" name="uitteam" value="<?php echo htmlspecialchars($wedstrijd['uitteam']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="datum" class="form-label">Datum</label>
                <input type="date" class="form-control" id="datum" name="datum" value="<?php echo htmlspecialchars($wedstrijd['datum']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="tijd" class="form-label">Tijd</label>
                <input type="time" class="form-control" id="tijd" name="tijd" value="<?php echo htmlspecialchars($wedstrijd['tijd']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="locatie" class="form-label">Locatie</label>
                <input type="text" class="form-control" id="locatie" name="locatie" value="<?php echo htmlspecialchars($wedstrijd['locatie']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Wedstrijd Bewerken</button>
        </form>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Studax Zwevegem. Alle rechten voorbehouden.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
