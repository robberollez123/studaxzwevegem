<?php
session_start();
require_once __DIR__ . '/../../config/database.php'; // Database verbinding

$ingelogd = isset($_SESSION['user']) && $_SESSION['user'] !== '';
$isAdmin = isset($_SESSION['user']) && $_SESSION['user'] == 'admin';


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_season'])) {
    $newSeason = mysqli_real_escape_string($linkDB, trim($_POST['new_season']));
    // Controleer of het seizoen al bestaat
    $checkQuery = "SELECT COUNT(*) AS count FROM wedstrijden WHERE seizoen = '$newSeason'";
    $checkResult = mysqli_query($linkDB, $checkQuery);
    $checkRow = mysqli_fetch_assoc($checkResult);

    if ($checkRow['count'] == 0) {
        // Seizoen toevoegen (dummywedstrijd, anders geen invoer mogelijk)
        $insertQuery = "INSERT INTO wedstrijden (seizoen, thuisteam, uitteam, datum, tijd, locatie) 
                        VALUES ('$newSeason', 'Nog te bepalen', 'Nog te bepalen', '2000-01-01', '00:00:00', 'Nader te bepalen')";
        
        if (mysqli_query($linkDB, $insertQuery)) {
            header("Location: kalender.php?season=$newSeason");
            exit;
        } else {
            echo "<div class='alert alert-danger'>Fout bij toevoegen van seizoen.</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>Seizoen bestaat al.</div>";
    }
}

$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$selectedSeason = isset($_GET['season']) ? $_GET['season'] : date("Y");

$seasonQuery = "SELECT DISTINCT seizoen FROM wedstrijden ORDER BY seizoen DESC";
$seasonResult = mysqli_query($linkDB, $seasonQuery);

$totalQuery = "SELECT COUNT(*) AS total FROM wedstrijden WHERE seizoen = '$selectedSeason'";
$totalResult = mysqli_query($linkDB, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalMatches = $totalRow['total'];

$query = "SELECT * FROM wedstrijden WHERE seizoen = '$selectedSeason' ORDER BY datum DESC, tijd ASC LIMIT $limit OFFSET $offset";
$result = mysqli_query($linkDB, $query);

$totalPages = ceil($totalMatches / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <li class="nav-item"><a class="nav-link active" href="#">Kalender</a></li>
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

<div class="container mt-4">
    <h1>Kalender</h1>

    <?php if (!$ingelogd): ?>
        <p>Je moet <a href="../../login/login.php">inloggen</a> om de kalender te bekijken.</p>
    <?php else: ?>
        
        <form method="GET" class="mb-3">
            <label for="season" class="form-label">Selecteer seizoen:</label>
            <select id="season" name="season" class="form-select" onchange="this.form.submit()">
                <option value="" <?php echo ($selectedSeason == "") ? 'selected' : ''; ?>>Selecteer een seizoen</option>
                <?php while ($season = mysqli_fetch_assoc($seasonResult)): ?>
                    <option value="<?php echo $season['seizoen']; ?>" <?php echo ($season['seizoen'] == $selectedSeason) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($season['seizoen']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <?php if ($isAdmin): ?>
            <form method="POST" class="mb-3">
                <label for="new_season" class="form-label">Nieuw seizoen toevoegen:</label>
                <div class="input-group">
                    <input type="text" id="new_season" name="new_season" class="form-control" placeholder="Bijv. 25-26" required>
                    <button type="submit" name="add_season" class="btn btn-primary">Toevoegen</button>
                </div>
            </form>
            <a href="wedstrijd_toevoegen.php?season=<?php echo $selectedSeason; ?>" class="btn btn-success mb-3">Wedstrijd toevoegen</a>
        <?php endif; ?>

        <div class="row">
            <?php while ($wedstrijd = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($wedstrijd['thuisteam']); ?> vs <?php echo htmlspecialchars($wedstrijd['uitteam']); ?></h5>
                            <p class="card-text"><strong>Datum:</strong> <?php echo htmlspecialchars($wedstrijd['datum']); ?></p>
                            <p class="card-text"><strong>Tijd:</strong> <?php echo htmlspecialchars($wedstrijd['tijd']); ?></p>
                            <p class="card-text"><strong>Locatie:</strong> <?php echo htmlspecialchars($wedstrijd['locatie']); ?></p>
                            
                            <?php if ($isAdmin): ?>
                                <div class="d-flex">
                                    <a href="wedstrijd_bewerken.php?id=<?php echo $wedstrijd['id']; ?>&season=<?php echo $selectedSeason; ?>"" class="btn btn-warning btn-sm me-2">Bewerken</a>
                                    <a href="wedstrijd_verwijderen.php?id=<?php echo $wedstrijd['id']; ?>&season=<?php echo $selectedSeason; ?>" class="btn btn-danger btn-sm">Verwijderen</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <nav aria-label="Pagina-navigatie">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?season=<?php echo $selectedSeason; ?>&page=<?php echo $page - 1; ?>" aria-label="Vorige">&laquo;</a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?season=<?php echo $selectedSeason; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?season=<?php echo $selectedSeason; ?>&page=<?php echo $page + 1; ?>" aria-label="Volgende">&raquo;</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
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
    </body>
</html>
