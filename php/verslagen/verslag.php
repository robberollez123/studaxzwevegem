<?php
session_start();  // Start de sessie om de loginstatus te kunnen controleren
require_once __DIR__ . '/../../config/database.php'; // Databaseverbinding

$ingelogd = isset($_SESSION['user']) && $_SESSION['user'] !== '';  // Controleer of de gebruiker ingelogd is

// Verkrijg het verslag ID uit de URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $verslag_id = (int)$_GET['id'];

    // Haal het verslag op uit de database op basis van het ID
    $query = "SELECT titel, begin_tekst, meer_tekst, afbeelding FROM verslagen WHERE id = $verslag_id";
    $result = $linkDB->query($query);

    if ($result) {
        $verslag = $result->fetch_assoc();
        if (!$verslag) {
            echo "<script>alert('Verslag niet gevonden.'); window.location.href = 'php/verslagen/wedstrijdverslagen.php';</script>";
            exit();
        }
    } else {
        // Als er een fout is met de query
        echo "<script>alert('Fout bij het ophalen van verslag.');</script>";
        exit();
    }
} else {
    // Als het ID niet geldig is
    echo "<script>alert('Ongeldig verslag ID.'); window.location.href = 'php/verslagen/wedstrijdverslagen.php';</script>";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verslag - <?php echo htmlspecialchars($verslag['titel']); ?></title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
                    <li class="nav-item"><a class="nav-link" href="../../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="../kalender/kalender.php">Kalender</a></li>
                    <li class="nav-item"><a class="nav-link" href="../spelers/spelers.php">Spelers</a></li>
                    <li class="nav-item"><a class="nav-link" href="../inschrijven/inschrijven.php">Inschrijven</a></li>
                </ul>
                <div class="d-flex">
                    <?php if ($ingelogd): ?>
                        <button class="btn btn-danger" id="logout">Uitloggen</button>
                    <?php else: ?> 
                        <button class="btn btn-outline-light me-2" id="login">Inloggen</button>
                        <button class="btn btn-outline-light me-2" id="register">Registreren</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="container mt-4">
        <h1 class="text-center"><?php echo htmlspecialchars($verslag['titel']); ?></h1>

        <div class="row mb-4">
            <div class="col-md-12">
                <img src="<?php echo htmlspecialchars($verslag['afbeelding']); ?>" class="img-fluid mb-4" alt="Verslag afbeelding">
                <p><?php echo nl2br(htmlspecialchars($verslag['begin_tekst'])); ?></p>
                <hr>
                <p><?php echo nl2br(htmlspecialchars($verslag['meer_tekst'])); ?></p>
            </div>
        </div>

        <!-- Terugknop -->
        <div class="row">
            <div class="col-md-12">
                <a href="wedstrijdverslagen.php" class="btn btn-secondary">Terug naar alle verslagen</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-5">
        <div class="container">
            <p>&copy; 2025 Studax Zwevegem. Alle rechten voorbehouden.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="../../scripts/login.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
