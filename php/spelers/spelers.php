<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

$ingelogd   = isset($_SESSION['user']) && $_SESSION['user'] !== '';
$isAdmin    = $ingelogd && $_SESSION['user'] === 'admin';

// Let op de $ vóór variabelen!
$targetSeizoen = $_GET['seizoen'] ?? null;

// Eerste query: distinct seizoenen
$stmtSeizoenen = $linkDB->prepare("SELECT DISTINCT seizoen FROM spelers ORDER BY seizoen DESC");
$stmtSeizoenen->execute();
$resultSeizoenen = $stmtSeizoenen->get_result();
$seizoenen = [];
while ($row = $resultSeizoenen->fetch_assoc()) {
    $seizoenen[] = $row['seizoen'];
}
if (empty($seizoenen)) {
    die('Geen seizoenen gevonden.');
}
if (!$targetSeizoen || !in_array($targetSeizoen, $seizoenen)) {
    $targetSeizoen = $seizoenen[0];
}

// Haal spelers voor het geselecteerde seizoen
$oStmt = $linkDB->prepare(
    "SELECT id, naam, wedstrijden, goals, gele_kaarten, rode_kaarten
     FROM spelers WHERE seizoen = ? ORDER BY naam"
);
$oStmt->bind_param('s', $targetSeizoen);
$oStmt->execute();
$spelers = $oStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spelers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="../../css/style.css">
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
                    <li class="nav-item"><a class="nav-link active" href="#">Spelers</a></li>
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
    <div class="container mt-4">
        <h1 class="mb-4">Spelersoverzicht</h1>

        <!-- Seizoenselectie -->
        <form method="get" class="mb-3">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <label for="seizoen" class="col-form-label">Seizoen:</label>
                </div>
                <div class="col-auto">
                    <select name="seizoen" id="seizoen" class="form-select" onchange="this.form.submit()">
                        <?php foreach ($seizoenen as $seizoen): ?>
                            <option value="<?= htmlspecialchars($seizoen) ?>" <?= $seizoen === $targetSeizoen ? 'selected' : '' ?>>
                                <?= htmlspecialchars($seizoen) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($isAdmin): ?>
                    <div class="col-auto ms-auto">
                        <a href="speler_add.php?seizoen=<?= urlencode($targetSeizoen) ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Speler toevoegen
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Naam</th>
                        <th class="text-center">Wedstrijden</th>
                        <th class="text-center">Goals</th>
                        <th class="text-center">Gele kaarten</th>
                        <th class="text-center">Rode kaarten</th>
                        <?php if ($isAdmin): ?><th class="text-center">Acties</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($spelers->num_rows === 0): ?>
                        <tr>
                            <td colspan="<?= $isAdmin ? 6 : 5 ?>" class="text-center text-muted">
                                Geen spelers voor seizoen <?= htmlspecialchars($targetSeizoen) ?>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($speler = $spelers->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($speler['naam'], ENT_QUOTES) ?></td>
                                <td class="text-center"><?= (int)$speler['wedstrijden'] ?></td>
                                <td class="text-center"><?= (int)$speler['goals'] ?></td>
                                <td class="text-center"><?= (int)$speler['gele_kaarten'] ?></td>
                                <td class="text-center"><?= (int)$speler['rode_kaarten'] ?></td>
                                <?php if ($isAdmin): ?>
                                    <td class="text-center">
                                        <a href="speler_edit.php?id=<?= $speler['id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="speler_delete.php?id=<?= $speler['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Weet je het zeker?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Studax Zwevegem. Alle rechten voorbehouden.</p>
        </div>
    </footer>

    <script>
        $(document).ready(function () {
        $("#login").click(function () {
            window.location.href = "../../login/login.php";
        });

        $("#register").click(function () {
            window.location.href = "../../login/register.php";
        });

        $("#logout").click(function () {
            window.location.href = "../../login/logout.php";
        });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
