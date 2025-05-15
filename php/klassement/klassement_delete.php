<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Alleen admin mag hier
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

// ID ophalen uit GET
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: ../../index.php');
    exit;
}
$id = (int) $_GET['id'];

// Verwijder de rij
$stmt = $linkDB->prepare("DELETE FROM klassement WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();

// Terug naar homepage
header('Location: ../../index.php');
exit;
