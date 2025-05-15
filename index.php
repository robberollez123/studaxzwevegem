<?php
session_start();  // Start de sessie om de loginstatus te kunnen controleren
require_once __DIR__ . '/config/database.php'; // Databaseverbinding

$ingelogd = isset($_SESSION['user']) && $_SESSION['user'] !== '';  // Controleer of de gebruiker ingelogd is

$adminUser = $_SESSION['user'] == 'admin';

// Haal de drie nieuwste verslagen op uit de database
$query = "SELECT titel, begin_tekst, meer_tekst, afbeelding FROM verslagen ORDER BY id DESC LIMIT 3";
$result = $linkDB->query($query);
$verslagen = $result->fetch_all(MYSQLI_ASSOC);

  $seasonsRes = $linkDB->query("
    SELECT DISTINCT seizoen 
    FROM klassement 
    ORDER BY seizoen DESC
  ");
  $seasons = $seasonsRes->fetch_all(MYSQLI_ASSOC);

  $selectedSeason = $_GET['seizoen'] 
    ?? ($seasons[0]['seizoen'] ?? '24-25');

  $klassementSQL = "
    SELECT 
      id,
      ploeg,
      gespeeld AS G,
      gewonnen AS W,
      gelijk AS D,
      verloren AS L,
      goalsVoor AS GF,
      goalsTegen AS GA
    FROM klassement
    WHERE seizoen = ?
  ";
  $stmt = $linkDB->prepare($klassementSQL);
  $stmt->bind_param('s', $selectedSeason);
  $stmt->execute();
  $raw = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Bereken punten en doelsaldo
foreach ($raw as &$team) {
  $team['P'] = $team['W'] * 3 + $team['D'];
  $team['GD'] = $team['GF'] - $team['GA'];
}
unset($team);

// Sorteer op punten en GD
usort($raw, function($a, $b) {
  return [$b['P'], $b['GD']] <=> [$a['P'], $a['GD']];
});

// Ken positie toe
foreach ($raw as $i => &$team) {
  $team['positie'] = $i + 1;
}
unset($team);

$klassement = $raw;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Studax Zwevegem</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="php/kalender/kalender.php">Kalender</a></li>
                    <li class="nav-item"><a class="nav-link" href="php/spelers/spelers.php">Spelers</a></li>
                    <li class="nav-item"><a class="nav-link" href="php/inschrijven/inschrijven.php">Inschrijven</a></li>
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
    <h1>Welkom bij Studax Zwevegem</h1>

    <div class="insta-txt container text-left mt-4 d-flex align-items-center" style="background-color:rgb(243, 243, 243); padding: 15px; margin-bottom: 20px; border-radius: 5px;">
        <p class="update-text fs-5 mb-0 me-3">Volg ons voor de nieuwste updates: </p>
        <a href="https://www.instagram.com/studax.zwevegem/" target="_blank" rel="noopener noreferrer" class="instagram-btn btn btn-primary me-3">
            <i class="fab fa-instagram"></i> <span>Instagram</span>
        </a>
    </div>

    <p class="d-flex justify-content-between">
        <span>Nieuwste wedstrijdverslagen:</span>
        <a href="php/verslagen/wedstrijdverslagen.php" class="text-dark fw-bold">Alle verslagen <i class="bi bi-arrow-right-circle"></i></a>
    </p>

    <?php if ($adminUser): ?>
        <button class="btn btn-primary" id="verslag-toevoegen">Verslag toevoegen</button>
    <?php endif; ?>

    <div class="row">
        <?php foreach ($verslagen as $verslag): ?>
        <div class="col-sm-4 mb-4">
            <div class="card">
                <img src="<?php echo htmlspecialchars($verslag['afbeelding']); ?>" class="card-img-top fixed-height-img" alt="Verslag afbeelding">
                <div class="card-body">
                    <h5 class="card-title"> <?php echo htmlspecialchars($verslag['titel']); ?> </h5>
                    <p class="card-text"> <?php echo htmlspecialchars($verslag['begin_tekst']); ?>...</p>
                    <p id="more" class="card-text" style="display: none;"> <?php echo htmlspecialchars($verslag['meer_tekst']); ?> </p>
                    <a class="lees-meer btn btn-primary" href="#">Lees meer...</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="container mt-5">
  <!-- === Toolbar: Seizoenselectie + Nieuw seizoen knop === -->
  <div class="d-flex align-items-center mb-3">
    <!-- Seizoen dropdown -->
    <form method="get" class="me-auto">
      <select name="seizoen" class="form-select" onchange="this.form.submit()">
        <?php foreach ($seasons as $s): ?>
          <option 
            value="<?= htmlspecialchars($s['seizoen']) ?>" 
            <?= $s['seizoen'] === $selectedSeason ? 'selected' : '' ?>>
            <?= htmlspecialchars($s['seizoen']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>

    <!-- Nieuw seizoen toevoegen knop -->
    <?php if ($adminUser): ?>
      <button class="btn btn-outline-primary ms-2"
        onclick="
          let nieuw = prompt('Nieuw seizoen toevoegen (bijv. 25-26):');
          if (nieuw) window.location.href = 'php/klassement/klassement_toevoegen.php?seizoen=' + encodeURIComponent(nieuw);
        ">
        <i class="fas fa-calendar-plus"></i> Nieuw Seizoen
      </button>
    <?php endif; ?>
  </div>

  <h2 class="mb-3">Klassement <?= htmlspecialchars($selectedSeason) ?></h2>

  <?php if ($adminUser): ?>
    <div class="d-flex justify-content-end mb-2">
      <a href="php/klassement/klassement_toevoegen.php?seizoen=<?= urlencode($selectedSeason) ?>" class="btn btn-success">
        <i class="fas fa-plus"></i> Nieuw Team Toevoegen
      </a>
    </div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Ploeg</th>
          <th>PTN</th>
          <th>M</th>
          <th>G</th>
          <th>V</th>
          <th>+</th>
          <th>–</th>
          <th>+/-</th>
          <?php if ($adminUser): ?>
            <th>Acties</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php
          $klassementSQL = "
            SELECT id, ploeg, gespeeld AS G, gewonnen AS W, gelijk AS D, verloren AS L, goalsVoor AS GF, goalsTegen AS GA
            FROM klassement
            WHERE seizoen = ?
          ";
          $stmt = $linkDB->prepare($klassementSQL);
          $stmt->bind_param("s", $selectedSeason);
          $stmt->execute();
          $result = $stmt->get_result();
          $raw = $result->fetch_all(MYSQLI_ASSOC);

          // Punten + doelsaldo berekenen
          foreach ($raw as &$team) {
            $team['P'] = $team['W'] * 3 + $team['D'];
            $team['GD'] = $team['GF'] - $team['GA'];
          }
          unset($team);

          // Sorteren
          usort($raw, fn($a, $b) => [$b['P'], $b['GD']] <=> [$a['P'], $a['GD']]);

          // Positie toewijzen
          foreach ($raw as $i => &$team) {
            $team['positie'] = $i + 1;
          }
          unset($team);

          foreach ($raw as $row):
        ?>
        <tr>
          <td><?= $row['positie'] ?></td>
          <td><?= htmlspecialchars($row['ploeg']) ?></td>
          <td><span class="badge bg-primary fs-6"><?= $row['P'] ?></span></td>
          <td><?= $row['G'] ?></td>
          <td><?= $row['D'] ?></td>
          <td><?= $row['L'] ?></td>
          <td><?= $row['GF'] ?></td>
          <td><?= $row['GA'] ?></td>
          <td><?= $row['GD'] ?></td>
          <?php if ($adminUser): ?>
          <td>
            <a href="php/klassement/klassement_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1">
              <i class="fas fa-edit"></i>
            </a>
            <a href="php/klassement/klassement_delete.php?id=<?= $row['id'] ?>&seizoen=<?= urlencode($selectedSeason) ?>"
               class="btn btn-sm btn-danger"
               onclick="return confirm('Weet je zeker dat je dit team wilt verwijderen?');">
              <i class="fas fa-trash-alt"></i>
            </a>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

</div>

</div>

</div>

</div>

    <!-- Footer -->

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Studax Zwevegem. Alle rechten voorbehouden.</p>
        </div>
    </footer>

    <script>
        window.addEventListener('resize', function() {
        const updateText = document.querySelector('.update-text');
        const instaBtn = document.querySelector('.instagram-btn');
        if (window.innerWidth <= 600) {
            updateText.textContent = "Volg ons:";
        } else {
            updateText.textContent = "Volg ons voor de nieuwste updates:";
        }
    });

    // Initial call to set the correct text on page load
    window.dispatchEvent(new Event('resize'));
    </script>
    <!-- Bootstrap JS -->
    <script src="scripts/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
