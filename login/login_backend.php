<?php
session_start();
require_once __DIR__ . '/../config/database.php';  // Database verbinding

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($linkDB, trim($_POST["username"]));
    $password = $_POST["password"];
    
    if (empty($username) || empty($password)) {
        $error_message = "Vul zowel je gebruikersnaam als je wachtwoord in.";
        header("Location: login.php?error=" . urlencode($error_message));
        exit;
    }

    $query = "SELECT id, naam, wachtwoord FROM gebruikers WHERE gebruikersnaam = ?";
    $stmt = mysqli_prepare($linkDB, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $userId, $userName, $hashedPassword);
        mysqli_stmt_fetch($stmt);

        if (password_verify($password, $hashedPassword)) {
            $_SESSION['user'] = $userName;  // Sessie instellen

            // Redirect naar de homepagina
            header("Location: ../index.php");
            exit;
        } else {
            $error_message = "Onjuist wachtwoord.";
            header("Location: login.php?error=" . urlencode($error_message));
            exit;
        }
    } else {
        $error_message = "Gebruikersnaam niet gevonden.";
        header("Location: login.php?error=" . urlencode($error_message));
        exit;
    }

    mysqli_stmt_close($stmt);
}
?>
