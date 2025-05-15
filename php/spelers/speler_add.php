<?php
// speler_add.php
session_start();
require_once __DIR__ . '/../../config/database.php'; // Databaseverbinding

// Controle of ingelogd en admin
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit('Toegang geweigerd.');
}

// Bepaal seizoen vanuit GET
$seizoen = $_GET['seizoen'] ?? '';

// Foutmeldingen array
$errors = [];

// Verwerk formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam          = trim($_POST['naam'] ?? '');
    $wedstrijden   = intval($_POST['wedstrijden'] ?? 0);
    $goals         = intval($_POST['goals'] ?? 0);
    $gele_kaarten  = intval($_POST['gele_kaarten'] ?? 0);
    $rode_kaarten  = intval($_POST['rode_kaarten'] ?? 0);
    $seizoen_input = trim($_POST['seizoen'] ?? '');

    // Validatie
    if ($naam === '') {
        $errors[] = 'Naam is verplicht.';
    }
    if ($seizoen_input === '') {
        $errors[] = 'Seizoen is verplicht.';
    }

    if (empty($errors)) {
        // Insert in database
        $stmt = $linkDB->prepare(
            "INSERT INTO spelers (naam, wedstrijden, goals, gele_kaarten, rode_kaarten, seizoen)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            'siiiis',
            $naam,
            $wedstrijden,
            $goals,
            $gele_kaarten,
            $rode_kaarten,
            $seizoen_input
        );
        if ($stmt->execute()) {
            // Redirect terug naar overzicht met hetzelfde seizoen
            header('Location: spelers.php?seizoen=' . urlencode($seizoen_input));
            exit;
        } else {
            $errors[] = 'Fout bij opslaan: ' . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Speler toevoegen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Speler toevoegen</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label for="naam" class="form-label">Naam</label>
                <input type="text" class="form-control" id="naam" name="naam" value="<?= htmlspecialchars($_POST['naam'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="wedstrijden" class="form-label">Wedstrijden</label>
                <input type="number" class="form-control" id="wedstrijden" name="wedstrijden" min="0" value="<?= htmlspecialchars($_POST['wedstrijden'] ?? '0') ?>">
            </div>
            <div class="mb-3">
                <label for="goals" class="form-label">Goals</label>
                <input type="number" class="form-control" id="goals" name="goals" min="0" value="<?= htmlspecialchars($_POST['goals'] ?? '0') ?>">
            </div>
            <div class="mb-3">
                <label for="gele_kaarten" class="form-label">Gele kaarten</label>
                <input type="number" class="form-control" id="gele_kaarten" name="gele_kaarten" min="0" value="<?= htmlspecialchars($_POST['gele_kaarten'] ?? '0') ?>">
            </div>
            <div class="mb-3">
                <label for="rode_kaarten" class="form-label">Rode kaarten</label>
                <input type="number" class="form-control" id="rode_kaarten" name="rode_kaarten" min="0" value="<?= htmlspecialchars($_POST['rode_kaarten'] ?? '0') ?>">
            </div>
            <div class="mb-3">
                <label for="seizoen" class="form-label">Seizoen</label>
                <input type="text" class="form-control" id="seizoen" name="seizoen" value="<?= htmlspecialchars($seizoen) ?>" required>
            </div>
            <button type="submit" class="btn btn-success">Opslaan</button>
            <a href="spelers.php?seizoen=<?= urlencode($seizoen) ?>" class="btn btn-secondary">Annuleren</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>