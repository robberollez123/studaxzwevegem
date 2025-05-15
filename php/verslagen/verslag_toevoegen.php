<?php
require_once __DIR__ . '/../../config/database.php'; // Databaseverbinding
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $begin_text = trim($_POST['begin_text']);
    $more_text = trim($_POST['more_text']);
    $season = trim($_POST['season']);
    $datum = date('Y-m-d H:i:s');
    
    // Controleer of alle velden ingevuld zijn
    if (empty($title) || empty($begin_text) || empty($more_text)) {
        echo "<script>alert('Alle velden zijn verplicht.'); window.history.back();</script>";
        exit();
    }
    
    $target_dir = "../uploads/";
    $image_name = basename($_FILES['image']['name']);
    $target_file = $target_dir . time() . "_" . $image_name; // Voorkom dubbele namen
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    $check = getimagesize($_FILES['image']['tmp_name']);
    if ($check === false) {
        echo "<script>alert('Het geselecteerde bestand is geen afbeelding.'); window.history.back();</script>";
        exit();
    }
    
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        echo "<script>alert('Alleen JPG, JPEG, PNG en GIF bestanden zijn toegestaan.'); window.history.back();</script>";
        exit();
    }
    
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        echo "<script>alert('Er is een fout opgetreden bij het uploaden van de afbeelding.'); window.history.back();</script>";
        exit();
    }
    
    $stmt = $linkDB->prepare("INSERT INTO verslagen (titel, begin_tekst, meer_tekst, afbeelding, datum, seizoen) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $title, $begin_text, $more_text, $target_file, $datum, $season);
    
    if ($stmt->execute()) {
        echo "<script>alert('Verslag succesvol toegevoegd.'); window.location.href = '../../index.php';</script>";
    } else {
        echo "<script>alert('Er is een fout opgetreden bij het toevoegen van het verslag.'); window.history.back();</script>";
    }
    
    $stmt->close();
    $linkDB->close();
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verslag toevoegen</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
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
        <div class="card p-4 bg-white rounded">
            <h2 class="text-center text-primary">Nieuw Verslag Toevoegen</h2>
            <form action="verslag_toevoegen.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="title" class="form-label">Titel:</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="mb-3">
                    <label for="begin_text" class="form-label">Intro:</label>
                    <textarea class="form-control" id="begin_text" name="begin_text" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="more_text" class="form-label">Tekst:</label>
                    <textarea class="form-control" id="more_text" name="more_text" rows="5" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="season" class="form-label">Kies een seizoen:</label>
                    <select class="form-select" id="season" name="season" required>
                        <option value="" disabled selected>Geen seizoen geselecteerd...</option>
                        <option value="24-25">24-25</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Afbeelding:</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Verslag Toevoegen</button>
            </form>
            <br>
            <a href="index.php" class="btn btn-secondary w-100">Terug naar Home</a>
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
