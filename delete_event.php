<?php
require_once 'config.php';
require_once 'auth.php';

requireLogin();
header('Content-Type: application/json');

try {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        throw new Exception('ID evento non valido');
    }
    
    // Verifica permessi
    $stmt = $pdo->prepare("SELECT creato_da FROM eventi WHERE id = ?");
    $stmt->execute([$id]);
    $event = $stmt->fetch();
    
    if (!$event || $event['creato_da'] != $_SESSION['user_id']) {
        throw new Exception('Non hai i permessi per eliminare questo evento');
    }
    
    $stmt = $pdo->prepare("DELETE FROM eventi WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
