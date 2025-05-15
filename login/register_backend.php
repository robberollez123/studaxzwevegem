<?php
session_start();

// Databaseverbinding
require_once __DIR__ . '/../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Haal de input op en beveilig deze
    $naam = mysqli_real_escape_string($linkDB, trim($_POST["name"]));
    $username = mysqli_real_escape_string($linkDB, trim($_POST["username"]));
    $email = mysqli_real_escape_string($linkDB, trim($_POST["email"]));
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];
    $isAdmin = isset($_POST["isAdmin"]) ? 1 : 0;

    // Controleer of alle velden zijn ingevuld
    if (empty($naam) || empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error_message = "Vul alle velden in!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Ongeldig e-mailadres!";
    } elseif ($password !== $confirmPassword) {
        $error_message = "De wachtwoorden komen niet overeen!";
    } else {
        // Controleer of de gebruikersnaam of e-mail al bestaat
        $query = "SELECT id FROM gebruikers WHERE gebruikersnaam = ? OR email = ?";
        $stmt = mysqli_prepare($linkDB, $query);
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error_message = "Deze gebruikersnaam of e-mail is al in gebruik!";
        } else {
            // Hash het wachtwoord
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Voeg de gebruiker toe aan de database
            $query = "INSERT INTO gebruikers (naam, gebruikersnaam, email, wachtwoord, isAdmin, timestamp) 
                      VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = mysqli_prepare($linkDB, $query);
            mysqli_stmt_bind_param($stmt, "ssssi", $naam, $username, $email, $hashedPassword, $isAdmin);

            if (mysqli_stmt_execute($stmt)) {
                // Redirect naar register.php met een succesmelding
                header("Location: register.php?status=account_created");
                exit;  // Zorg ervoor dat het script hier stopt
            } else {
                $error_message = "Account toevoegen mislukt. Probeer opnieuw.";
            }
        }
        mysqli_stmt_close($stmt);
    }

    // Als er een fout is, stuur de gebruiker terug naar register.php met de foutmelding
    if (isset($error_message)) {
        header("Location: register.php?status=error&message=" . urlencode($error_message));
        exit;
    }

    mysqli_close($linkDB);
}
?>
