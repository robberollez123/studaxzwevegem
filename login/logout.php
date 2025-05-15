<?php
session_start();  // Start de sessie

// Verwijder alle sessievariabelen
session_unset();

// Vernietig de sessie
session_destroy();

// Redirect naar de homepage
header("Location: ../index.php");
exit;
?>
