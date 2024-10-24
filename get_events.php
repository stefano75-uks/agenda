<?php
require_once 'config.php';
require_once 'auth.php';

requireLogin();
header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("
        SELECT e.*, u.nome as creator_nome, u.cognome as creator_cognome 
        FROM eventi e
        LEFT JOIN users u ON e.creato_da = u.id
        WHERE e.creato_da = ? 
        OR e.id IN (SELECT evento_id FROM eventi_condivisi WHERE user_id = ?)
    ");
    
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $events = [];
    
    while ($row = $stmt->fetch()) {
        $events[] = [
            'id' => $row['id'],
            'title' => $row['descrizione'], // Usiamo la descrizione come titolo
            'description' => $row['descrizione'],
            'start' => $row['data_inizio'],
            'end' => $row['data_fine'],
            'creator' => $row['creator_nome'] . ' ' . $row['creator_cognome'],
            'canEdit' => $row['creato_da'] == $_SESSION['user_id']
        ];
    }
    
    echo json_encode($events);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Errore nel caricamento eventi']);
}
?>
