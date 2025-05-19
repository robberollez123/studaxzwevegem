<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registreren</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Studax Zwevegem</a>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="../php/kalender/kalender.php">Kalender</a></li>
                    <li class="nav-item"><a class="nav-link" href="../php/spelers/spelers.php">Spelers</a></li>
                    <li class="nav-item"><a class="nav-link" href="../php/inschrijven/inschrijven.php">Inschrijven</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3 class="text-center mb-4">Registreren</h3>

                        <?php if (isset($_GET['status']) && $_GET['status'] === 'error' && isset($_GET['message'])): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($_GET['message']); ?>
                            </div>
                        <?php elseif (isset($_GET['status']) && $_GET['status'] === 'pending_verification'): ?>
                            <div class="alert alert-info">
                                Er is een verificatielink verstuurd. Controleer je e‑mail.
                            </div>
                        <?php endif; ?>

                        <form action="register_backend.php" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Volledige naam</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Gebruikersnaam</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Wachtwoord</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Bevestig wachtwoord</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                            </div>
                            <div class="mt-3 text-center">
                                <p>Heb je al een account? <a href="login.php">Inloggen</a></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Verificatiemethode</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="verify_method" id="via_email" value="email" checked onchange="togglePhone()">
                                    <label class="form-check-label" for="via_email">E-mail</label>
                                </div>
                            </div>
                            <div class="mb-3" id="phone-group" style="display:none;">
                                <label for="phone" class="form-label">Telefoonnummer</label>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="+32XXXXXXXXX">
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Registreren</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Studax Zwevegem. Alle rechten voorbehouden.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePhone() {
            const viaEmail = document.getElementById('via_email').checked;
            document.getElementById('phone-group').style.display = viaEmail ? 'none' : 'block';
        }
    </script>
</body>
</html>
