<?php
require_once 'config.php';

// Avvia la sessione se non è già stata avviata
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Controlla se l'utente è loggato
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Reindirizza alla pagina di login se l'utente non è loggato
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Verifica se l'utente è un amministratore
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Reindirizza alla pagina principale se l'utente è già loggato
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

// Restituisce i dati dell'utente loggato (nome e cognome)
function getLoggedUser() {
    if (isLoggedIn()) {
        return [
            'nome' => htmlspecialchars($_SESSION['nome']),
            'cognome' => htmlspecialchars($_SESSION['cognome']),
        ];
    }
    return null;
}
?>
