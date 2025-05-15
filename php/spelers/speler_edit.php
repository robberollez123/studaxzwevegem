<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Alleen admin mag bewerken
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit('Toegang geweigerd.');
}

// ID ophalen en valideren
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    exit('Ongeldig spelers-ID.');
}

// Speler ophalen
$stmt = $linkDB->prepare("SELECT naam, wedstrijden, goals, gele_kaarten, rode_kaarten, seizoen FROM spelers WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$speler = $result->fetch_assoc();
if (!$speler) {
    exit('Speler niet gevonden.');
}

$errors = [];
// Formulierverwerking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam         = trim($_POST['naam']);
    $wedstrijden  = intval($_POST['wedstrijden']);
    $goals        = intval($_POST['goals']);
    $gele_kaarten = intval($_POST['gele_kaarten']);
    $rode_kaarten = intval($_POST['rode_kaarten']);
    $seizoen      = trim($_POST['seizoen']);

    // Validatie
    if ($naam === '') {
        $errors[] = 'Naam is verplicht.';
    }
    if ($seizoen === '') {
        $errors[] = 'Seizoen is verplicht.';
    }

    if (empty($errors)) {
        $upd = $linkDB->prepare(
            "UPDATE spelers SET naam = ?, wedstrijden = ?, goals = ?, gele_kaarten = ?, rode_kaarten = ?, seizoen = ? WHERE id = ?"
        );
        $upd->bind_param('siiiisi', $naam, $wedstrijden, $goals, $gele_kaarten, $rode_kaarten, $seizoen, $id);
        if ($upd->execute()) {
            header('Location: spelers.php?seizoen=' . urlencode($seizoen));
            exit;
        } else {
            $errors[] = 'Fout bij opslaan: ' . $upd->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Speler bewerken</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Speler bewerken</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label for="naam" class="form-label">Naam</label>
                <input type="text" class="form-control" id="naam" name="naam" value="<?= htmlspecialchars($speler['naam']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="wedstrijden" class="form-label">Wedstrijden</label>
                <input type="number" class="form-control" id="wedstrijden" name="wedstrijden" min="0" value="<?= $speler['wedstrijden'] ?>">
            </div>
            <div class="mb-3">
                <label for="goals" class="form-label">Goals</label>
                <input type="number" class="form-control" id="goals" name="goals" min="0" value="<?= $speler['goals'] ?>">
            </div>
            <div class="mb-3">
                <label for="gele_kaarten" class="form-label">Gele kaarten</label>
                <input type="number" class="form-control" id="gele_kaarten" name="gele_kaarten" min="0" value="<?= $speler['gele_kaarten'] ?>">
            </div>
            <div class="mb-3">
                <label for="rode_kaarten" class="form-label">Rode kaarten</label>
                <input type="number" class="form-control" id="rode_kaarten" name="rode_kaarten" min="0" value="<?= $speler['rode_kaarten'] ?>">
            </div>
            <div class="mb-3">
                <label for="seizoen" class="form-label">Seizoen</label>
                <input type="text" class="form-control" id="seizoen" name="seizoen" value="<?= htmlspecialchars($speler['seizoen']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Opslaan</button>
            <a href="spelers.php?seizoen=<?= urlencode($speler['seizoen']) ?>" class="btn btn-secondary">Annuleren</a>
        </form>
    </div>
</body>
</html>