<?php
// first_setup.php
require_once 'config.php';

// Controlla se esiste già un admin
function checkIfSetupNeeded($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    return $stmt->fetchColumn() == 0;
}

// Se il setup è già stato fatto, reindirizza
if (!checkIfSetupNeeded($pdo)) {
    header("Location: login.php");
    exit;
}

// Processa il form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_admin'])) {
    try {
        $username = trim($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $nome = trim($_POST['nome']);
        $cognome = trim($_POST['cognome']);

        if (empty($username) || empty($_POST['password']) || empty($nome) || empty($cognome)) {
            throw new Exception("Tutti i campi sono obbligatori");
        }

        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, nome, cognome, is_admin) 
            VALUES (?, ?, ?, ?, 1)
        ");
        
        if ($stmt->execute([$username, $password, $nome, $cognome])) {
            header("Location: login.php?setup=success");
            exit;
        }
    } catch (Exception $e) {
        $error = "Errore durante il setup: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Iniziale Agenda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .setup-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-control {
            border-radius: 10px;
            margin-bottom: 15px;
        }
        .btn {
            border-radius: 10px;
        }
        .logo {
            font-size: 3em;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="setup-container bg-white">
            <div class="logo">📅</div>
            <h2 class="text-center mb-4">Setup Iniziale Agenda</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="needs-validation" novalidate>
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

                <button type="submit" name="setup_admin" class="btn btn-primary w-100">
                    Crea Amministratore
                </button>
            </form>
        </div>
    </div>
</body>
</html>