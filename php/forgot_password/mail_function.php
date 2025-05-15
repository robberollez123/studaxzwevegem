<?php

// Functie om e-mails te versturen
function send_email($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@studaxzwevegem.be" . "\r\n"; // Zet hier jouw 'from' e-mailadres
    $headers .= "Reply-To: studaxzwevegem@robberollez.be" . "\r\n";  // Optioneel: toevoegen van een reply-to adres

    // Verstuur e-mail
    if (mail($to, $subject, $message, $headers)) {
        return true;
    } else {
        return false;
    }
}
?>
