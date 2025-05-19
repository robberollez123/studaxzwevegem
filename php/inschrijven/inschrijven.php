<?php
session_start();
// Controleer login
$ingelogd = isset($_SESSION['user']) && $_SESSION['user'] !== '';

$userNaam  = $_SESSION['user']  ?? '';
$userEmail = $_SESSION['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inschrijven</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>    
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    rel="stylesheet"
  >
  <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="../../index.php">Studax Zwevegem</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
              data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="../../index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="../kalender/kalender.php">Kalender</a></li>
          <li class="nav-item"><a class="nav-link" href="../spelers/spelers.php">Spelers</a></li>
          <li class="nav-item"><a class="nav-link active" href="#">Inschrijven</a></li>
        </ul>
        <div class="d-flex">
          <?php if ($ingelogd): ?>
            <button class="btn btn-danger me-2" id="logout">Uitloggen</button>
          <?php else: ?>
            <button class="btn btn-outline-light me-2" id="login">Inloggen</button>
            <button class="btn btn-outline-light" id="register">Registreren</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <div class="container mt-4 mb-5">
    <h1>Inschrijven</h1>

  <?php if($ingelogd): ?>
    <p>
      Wil je lid worden van Studax Zwevegem? 
      Vul onderstaand formulier in. Na versturen nemen wij zo spoedig mogelijk contact met je op.
      Velden met <span class="text-danger">*</span> zijn verplicht.
    </p>

    <form action="inschrijven_process.php" method="post" class="row g-3">
      <div class="col-md-6">
        <label for="naam" class="form-label">Volledige naam <span class="text-danger">*</span></label>
        <input 
          type="text" 
          id="naam" 
          name="naam" 
          class="form-control" 
          required
          value="<?= htmlspecialchars($userNaam) ?>"
        >
      </div>

      <div class="col-md-6">
        <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
        <input 
          type="email" 
          id="email" 
          name="email" 
          class="form-control" 
          required
          value="<?= htmlspecialchars($userEmail) ?>"
          <?= $ingelogd ? 'readonly' : '' ?>
        >
      </div>

      <div class="col-md-4">
        <label for="geboortedatum" class="form-label">Geboortedatum <span class="text-danger">*</span></label>
        <input 
          type="date" 
          id="geboortedatum" 
          name="geboortedatum" 
          class="form-control" 
          required
        >
      </div>

      <div class="col-md-4">
        <label for="telefoon" class="form-label">Telefoonnummer</label>
        <input 
          type="tel" 
          id="telefoon" 
          name="telefoon" 
          class="form-control"
          placeholder="+32 123 45 67 89"
        >
      </div>

      <div class="col-md-4">
        <label for="adres" class="form-label">Adres</label>
        <input 
          type="text" 
          id="adres" 
          name="adres" 
          class="form-control"
          placeholder="Straatnaam 1, 8510 Zwevegem"
        >
      </div>

      <div class="col-12">
        <label for="opmerkingen" class="form-label">Opmerkingen / Vragen</label>
        <textarea 
          id="opmerkingen" 
          name="opmerkingen" 
          class="form-control" 
          rows="3"
          placeholder="Bijv. voorkeurspositie, medische info, etc."
        ></textarea>
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-paper-plane"></i> Verstuur
        </button>
        <a href="../../index.php" class="btn btn-secondary ms-2">Annuleren</a>
      </div>
    </form>
  <?php else: ?>
    <p>Je moet ingelogd zijn om je in te kunnen schrijven. <a href="../../login/login.php">Log in.</a></p>
  <?php endif; ?>
</div>



  <footer class="footer">
    <div class="container">
      <p>&copy; 2025 Studax Zwevegem. Alle rechten voorbehouden.</p>
    </div>
  </footer>

  <script src="../../scripts/script.js"></script>
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
    </script>  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
