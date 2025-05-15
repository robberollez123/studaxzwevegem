<?php
session_start();
require_once __DIR__ . '/../../config/database.php'; // Databaseverbinding

// Haal alle unieke seizoenen op uit de database
$query = "SELECT DISTINCT seizoen FROM verslagen ORDER BY seizoen DESC";
$result = $linkDB->query($query);
$seizoenen = [];
while ($row = $result->fetch_assoc()) {
    $seizoenen[] = $row['seizoen'];
}

$selectedSeason = isset($_GET['seizoen']) ? $_GET['seizoen'] : "";

?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wedstrijdverslagen</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body class="bg-light">
    <!-- Navbar -->
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
                <div class="d-flex">
                    <?php if (isset($_SESSION['user'])): ?>
                        <button class="btn btn-danger" id="logout">Uitloggen</button>
                    <?php else: ?> 
                        <button class="btn btn-outline-light me-2" id="login">Inloggen</button>
                        <button class="btn btn-outline-light me-2" id="register">Registreren</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container mt-5">
        <h2 class="text-center text-primary">Wedstrijdverslagen</h2>

        <!-- Seizoen selecteren -->
        <div class="mb-3">
            <label for="seizoenSelect" class="form-label">Selecteer Seizoen:</label>
            <select class="form-select" id="seizoenSelect">
                <option value="" <?php echo ($selectedSeason == "") ? 'selected' : ''; ?>>Selecteer een seizoen</option>
                <?php foreach ($seizoenen as $seizoen): ?>
                    <option value="<?php echo $seizoen; ?>"><?php echo $seizoen; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Tabel met verslagen -->
        <div class="card p-4 bg-white rounded">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Titel</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="verslagTable">
                </tbody>
            </table>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        function loadVerslagen(seizoen) {
            $.ajax({
                url: "fetch_verslagen.php",
                type: "POST",
                data: { seizoen: seizoen },
                success: function(data) {
                    $("#verslagTable").html(data);
                }
            });
        }

        let eersteSeizoen = $("#seizoenSelect").val();
        loadVerslagen(eersteSeizoen);

        $("#seizoenSelect").change(function() {
            let seizoen = $(this).val();
            loadVerslagen(seizoen);
        });
    });
    </script>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Studax Zwevegem. Alle rechten voorbehouden.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
     <script src="../../scripts/login.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
