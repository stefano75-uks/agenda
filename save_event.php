<?php
require_once 'config.php';
require_once 'auth.php';

requireLogin();
header('Content-Type: application/json');

// Funzione per debug
function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo json_encode(['debug' => $output]);
    error_log(print_r($output, true));
}

try {
    $pdo->beginTransaction();
    
    // Debug: vediamo cosa arriva
    error_log("Dati ricevuti in save_event: " . print_r($_POST, true));
    
    // Validazione input
    if (empty($_POST['descrizione']) || empty($_POST['data_inizio']) || empty($_POST['data_fine'])) {
        throw new Exception('Tutti i campi sono obbligatori');
    }
    
    $id = $_POST['id'] ?? null;
    $isNew = empty($id);
    
    if ($isNew) {
        $stmt = $pdo->prepare("
            INSERT INTO eventi (descrizione, data_inizio, data_fine, creato_da)
            VALUES (?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $_POST['descrizione'],
            $_POST['data_inizio'],
            $_POST['data_fine'],
            $_SESSION['user_id']
        ]);
        
        if (!$result) {
            throw new Exception('Errore nell\'inserimento dell\'evento');
        }
        
        $id = $pdo->lastInsertId();
        error_log("Nuovo evento creato con ID: " . $id);
    } else {
        // Verifica permessi
        $stmt = $pdo->prepare("SELECT creato_da FROM eventi WHERE id = ?");
        $stmt->execute([$id]);
        $event = $stmt->fetch();
        
        if (!$event || $event['creato_da'] != $_SESSION['user_id']) {
            throw new Exception('Non hai i permessi per modificare questo evento');
        }
        
        $stmt = $pdo->prepare("
            UPDATE eventi 
            SET descrizione = ?, data_inizio = ?, data_fine = ?
            WHERE id = ?
        ");
        
        $result = $stmt->execute([
            $_POST['descrizione'],
            $_POST['data_inizio'],
            $_POST['data_fine'],
            $id
        ]);
        
        if (!$result) {
            throw new Exception('Errore nell\'aggiornamento dell\'evento');
        }
        
        // Rimuovi le vecchie condivisioni
        $stmt = $pdo->prepare("DELETE FROM eventi_condivisi WHERE evento_id = ?");
        $stmt->execute([$id]);
    }
    
    // Gestione condivisioni
    if (!empty($_POST['share_with']) && is_array($_POST['share_with'])) {
        $stmt = $pdo->prepare("
            INSERT INTO eventi_condivisi (evento_id, user_id) 
            VALUES (?, ?)
        ");
        
        foreach ($_POST['share_with'] as $userId) {
            if (!empty($userId)) {
                $stmt->execute([$id, $userId]);
            }
        }
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'event_id' => $id]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Errore in save_event.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>