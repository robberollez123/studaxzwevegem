<?php
require_once __DIR__ . '/../../config/database.php'; // Databaseverbinding

if (isset($_POST['seizoen'])) {
    $seizoen = $_POST['seizoen'];

    // Haal verslagen op van het geselecteerde seizoen
    $stmt = $linkDB->prepare("SELECT id, titel FROM verslagen WHERE seizoen = ? ORDER BY id DESC");
    $stmt->bind_param("s", $seizoen);
    $stmt->execute();
    $result = $stmt->get_result();

    // Maak de tabelrijen aan
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['titel']}</td>
                <td><a href='verslag.php?id={$row['id']}' class='btn btn-primary btn-sm'>Openen</a></td>
              </tr>";
    }
    $stmt->close();
}
?>
