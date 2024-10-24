<?php
require_once 'config.php';
require_once 'auth.php';

requireLogin();

// Verifica che l'utente sia admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

// Gestione creazione nuovo utente
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    $username = trim($_POST['username']);
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $nome = trim($_POST['nome']);
                    $cognome = trim($_POST['cognome']);

                    // Verifica se username esiste già
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if ($stmt->fetchColumn() > 0) {
                        throw new Exception("Username già in uso");
                    }

                    $stmt = $pdo->prepare("
                        INSERT INTO users (username, password, nome, cognome, is_admin)
                        VALUES (?, ?, ?, ?, 0)
                    ");
                    
                    if ($stmt->execute([$username, $password, $nome, $cognome])) {
                        $message = "Utente creato con successo";
                        $messageType = "success";
                    }
                    break;

                case 'delete':
                    $userId = $_POST['user_id'];
                    // Impedisci eliminazione dell'admin
                    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $user = $stmt->fetch();
                    
                    if ($user['is_admin']) {
                        throw new Exception("Non puoi eliminare un amministratore");
                    }

                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    if ($stmt->execute([$userId])) {
                        $message = "Utente eliminato con successo";
                        $messageType = "success";
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = "danger";
    }
}

// Recupera lista utenti
$stmt = $pdo->query("SELECT id, username, nome, cognome, is_admin FROM users ORDER BY nome, cognome");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Utenti - Agenda Condivisa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            border-radius: 0 0 20px 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .btn {
            border-radius: 10px;
        }
        .form-control {
            border-radius: 10px;
        }
        .user-card {
            transition: all 0.3s ease;
        }
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-users me-2"></i>Gestione Utenti</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-calendar me-2"></i>Torna al Calendario
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Form Nuovo Utente -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white py-3" style="border-radius: 20px 20px 0 0;">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-plus me-2"></i>Nuovo Utente
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="newUserForm">
                            <input type="hidden" name="action" value="create">
                            
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nome</label>
                                <input type="text" name="nome" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Cognome</label>
                                <input type="text" name="cognome" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus-circle me-2"></i>Crea Utente
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Lista Utenti -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white py-3" style="border-radius: 20px 20px 0 0;">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>Utenti Registrati
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-1 row-cols-md-2 g-4">
                            <?php foreach ($users as $user): ?>
                            <div class="col">
                                <div class="card h-100 user-card">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <?php echo htmlspecialchars($user['nome'] . ' ' . $user['cognome']); ?>
                                            <?php if ($user['is_admin']): ?>
                                                <span class="badge bg-warning text-dark">Admin</span>
                                            <?php endif; ?>
                                        </h5>
                                        <p class="card-text text-muted">
                                            <small>Username: <?php echo htmlspecialchars($user['username']); ?></small>
                                        </p>
                                        <?php if (!$user['is_admin']): ?>
                                        <form method="POST" action="" class="mt-3" onsubmit="return confirm('Sei sicuro di voler eliminare questo utente?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash me-2"></i>Elimina
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
