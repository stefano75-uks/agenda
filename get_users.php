<?php
require_once 'config.php';
require_once 'auth.php';

requireLogin();
header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("
        SELECT id, nome, cognome 
        FROM users 
        WHERE id != ? 
        ORDER BY nome, cognome
    ");
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    error_log("Errore in get_users.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Errore nel caricamento degli utenti']);
}
?>