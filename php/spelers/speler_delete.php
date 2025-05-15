<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Alleen admin mag verwijderen
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit('Toegang geweigerd.');
}

// ID ophalen en valideren
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    exit('Ongeldig spelers-ID.');
}

// Eerst seizoen ophalen zodat we kunnen terugkeren
$stmt = $linkDB->prepare("SELECT seizoen FROM spelers WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) {
    exit('Speler niet gevonden.');
}
$seizoen = $row['seizoen'];

// Verwijderen
$del = $linkDB->prepare("DELETE FROM spelers WHERE id = ?");
$del->bind_param('i', $id);
$del->execute();

// Terug naar overzicht
header('Location: spelers.php?seizoen=' . urlencode($seizoen));
exit;
?>
