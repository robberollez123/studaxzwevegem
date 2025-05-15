<?php
session_start();
// Alleen admin
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../../config/database.php';

// Verwerk formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seizoen = trim($_POST['seizoen']);
    $ploeg   = trim($_POST['ploeg']);
    $G        = (int) $_POST['G'];   // gespeeld
    $W        = (int) $_POST['W'];   // gewonnen
    $D        = (int) $_POST['D'];   // gelijk
    $L        = (int) $_POST['L'];   // verloren
    $GF       = (int) $_POST['GF'];  // goalsVoor
    $GA       = (int) $_POST['GA'];  // goalsTegen

    $stmt = $linkDB->prepare("
        INSERT INTO klassement 
            (seizoen, ploeg, gespeeld, gewonnen, gelijk, verloren, goalsVoor, goalsTegen)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    // Let op: 2×s voor seizoen+ploeg, dan 6×i voor de cijfers
    $stmt->bind_param(
        'ssiiiiii',
        $seizoen, $ploeg,
        $G, $W, $D, $L, $GF, $GA
    );
    $stmt->execute();
    header('Location: ../../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nieuw team toevoegen</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h1 class="mb-4">Nieuw team toevoegen</h1>
    <form method="post" class="row g-3">
      <div class="col-md-4">
        <label for="seizoen" class="form-label">Seizoen</label>
        <input id="seizoen" name="seizoen" type="text" class="form-control" placeholder="bijv. 24-25" required>
      </div>
      <div class="col-md-8">
        <label for="ploeg" class="form-label">Teamnaam</label>
        <input id="ploeg" name="ploeg" type="text" class="form-control" required>
      </div>
      <div class="col-md-2">
        <label for="G" class="form-label">Gespeeld (G)</label>
        <input id="G" name="G" type="number" min="0" class="form-control" required>
      </div>
      <div class="col-md-2">
        <label for="W" class="form-label">Gewonnen (W)</label>
        <input id="W" name="W" type="number" min="0" class="form-control" required>
      </div>
      <div class="col-md-2">
        <label for="D" class="form-label">Gelijk (D)</label>
        <input id="D" name="D" type="number" min="0" class="form-control" required>
      </div>
      <div class="col-md-2">
        <label for="L" class="form-label">Verloren (L)</label>
        <input id="L" name="L" type="number" min="0" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label for="GF" class="form-label">Goals Voor (GF)</label>
        <input id="GF" name="GF" type="number" min="0" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label for="GA" class="form-label">Goals Tegen (GA)</label>
        <input id="GA" name="GA" type="number" min="0" class="form-control" required>
      </div>
      <div class="col-12 mt-4">
        <button type="submit" class="btn btn-success">
          <i class="fas fa-plus"></i> Toevoegen
        </button>
        <a href="../../index.php" class="btn btn-secondary ms-2">Annuleren</a>
      </div>
    </form>
  </div>

  <!-- FontAwesome voor iconen -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
