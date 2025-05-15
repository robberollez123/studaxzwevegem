<?php
session_start();

// 1) Lees en sanitize de POST‐waarden
$naam          = trim($_POST['naam'] ?? '');
$email         = trim($_POST['email'] ?? '');
$geboortedatum = $_POST['geboortedatum'] ?? '';
$telefoon      = trim($_POST['telefoon'] ?? '');
$adres         = trim($_POST['adres'] ?? '');
$opmerkingen   = nl2br(htmlspecialchars(trim($_POST['opmerkingen'] ?? '')));

// 2) Validatie
$errors = [];
if ($naam === '') {
    $errors[] = 'Naam is verplicht.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Ongeldig e-mailadres.';
}
if ($geboortedatum === '') {
    $errors[] = 'Geboortedatum is verplicht.';
}

if (!empty($errors)) {
    // Toon foutmeldingen
    echo "<h2>Er zijn fouten:</h2><ul>";
    foreach ($errors as $err) {
        echo "<li>" . htmlspecialchars($err) . "</li>";
    }
    echo "</ul><p><a href='javascript:history.back()'>Ga terug</a></p>";
    exit;
}

// 3) Bouw de HTML‐email op
$message = "
<html>
<head>
  <title>Nieuwe inschrijving Studax Zwevegem</title>
</head>
<body>
  <h2>Nieuwe inschrijving binnengekomen</h2>
  <p><strong>Naam:</strong> " . htmlspecialchars($naam) . "</p>
  <p><strong>E-mail:</strong> " . htmlspecialchars($email) . "</p>
  <p><strong>Geboortedatum:</strong> " . htmlspecialchars($geboortedatum) . "</p>
  <p><strong>Telefoon:</strong> " . htmlspecialchars($telefoon) . "</p>
  <p><strong>Adres:</strong> " . htmlspecialchars($adres) . "</p>
  <p><strong>Opmerkingen / Vragen:</strong><br>" . $opmerkingen . "</p>
  <p><em>Inschrijvingsdatum: " . date('Y-m-d H:i') . "</em></p>
</body>
</html>
";

require_once __DIR__ . '/forgot_password/mail_function.php';

$to      = 'studaxzwevegem@robberollez.be';
$subject = "Inschrijving: " . $naam;

if (send_email($to, $subject, $message)) {
    // Succesvolle verzending
    header('Location: inschrijven.php');
    exit;
} else {
    echo "<h2>Er ging iets mis bij het versturen van de e-mail.</h2>";
    echo "<p>Probeer het later opnieuw of neem contact op met de club.</p>";
    exit;
}
