<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Alleen admin mag hier
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

// ID ophalen uit GET, anders redirect
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: ../../index.php');
    exit;
}
$id = (int) $_GET['id'];

// Bij POST: verwerk form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Velden sanitiseren / casten
    $seizoen = trim($_POST['seizoen']);
    $ploeg    = trim($_POST['ploeg']);
    $positie  = (int) $_POST['positie'];
    $G        = (int) $_POST['G'];
    $W        = (int) $_POST['W'];
    $D        = (int) $_POST['D'];
    $L        = (int) $_POST['L'];
    $GF       = (int) $_POST['GF'];
    $GA       = (int) $_POST['GA'];

    // Update statement
    $stmt = $linkDB->prepare("
        UPDATE klassement
        SET seizoen = ?, ploeg = ?, positie = ?, gespeeld = ?, 
            gewonnen = ?, gelijk = ?, 
            verloren = ?, goalsVoor = ?, goalsTegen = ?
        WHERE id = ?
    ");
    $stmt->bind_param(
        'ssiiiiiiii',
        $seizoen, $ploeg, $positie,
        $G, $W, $D, $L, $GF, $GA,
        $id
    );
    $stmt->execute();
    header('Location: ../../index.php');
    exit;
}

// Bij GET: haal bestaande data op om in het formulier voor te vullen
$stmt = $linkDB->prepare("
    SELECT seizoen, ploeg, positie, gespeeld, 
           gewonnen, gelijk, 
           verloren, goalsVoor, goalsTegen
    FROM klassement
    WHERE id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    header('Location: ../../index.php');
    exit;
}
$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <title>Wijzig Klassement</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <h1>Wijzig Team: <?= htmlspecialchars($row['ploeg']) ?></h1>
  <form method="post" class="mt-3">
    <div class="mb-3">
      <label class="form-label">Seizoen</label>
      <input name="seizoen" type="text" class="form-control" required
             value="<?= htmlspecialchars($row['seizoen']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Ploeg</label>
      <input name="ploeg" type="text" class="form-control" required
             value="<?= htmlspecialchars($row['ploeg']) ?>">
    </div>
    <div class="row g-3">
      <?php
      $fields = [
        'positie' => 'Positie', 'G' => 'Gespeeld',
        'W' => 'Gewonnen', 'D' => 'Gelijk', 'L' => 'Verloren',
        'GF' => 'Goals Voor', 'GA' => 'Goals Tegen'
      ];
      foreach ($fields as $name => $label): ?>
      <div class="col-md-3 mb-3">
        <label class="form-label"><?= $label ?></label>
        <input name="<?= $name ?>" type="number" min="0" class="form-control" required
               value="<?= htmlspecialchars($row[
                   ($name==='G'?'gespeeld':
                   ($name==='W'?'gewonnen':
                   ($name==='D'?'gelijk':
                   ($name==='L'?'verloren':
                   ($name==='GF'?'goalsVoor':'goalsTegen')))))
               ]) ?>">
      </div>
      <?php endforeach; ?>
    </div>
    <button type="submit" class="btn btn-primary">Opslaan</button>
    <a href="../../index.php" class="btn btn-secondary">Annuleer</a>
  </form>
</body>
</html>
