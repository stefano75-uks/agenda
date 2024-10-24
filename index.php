<!-- index.php -->
<?php
require_once 'auth.php';
requireLogin();
$user = getLoggedUser();
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Condivisa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />


    <link href="style.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">📅 Agenda Condivisa</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link">
                    Benvenuto <?php echo $user['nome'] . ' ' . $user['cognome']; ?>
                </span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="calendar-legend">
            <div class="legend-item">
                <div class="legend-color event-personal"></div>
                I tuoi appuntamenti
            </div>
            <div class="legend-item">
                <div class="legend-color event-shared"></div>
                Appuntamenti condivisi
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <!-- Modal per la gestione degli eventi -->
    <div class="modal fade" id="eventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuovo Appuntamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="eventForm">
                        <input type="hidden" id="eventId" name="id">
                        <div class="mb-3">
                            <label class="form-label">Descrizione Appuntamento</label>
                            <textarea class="form-control" id="eventDescription" name="descrizione" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Data e Ora Inizio</label>
                            <input type="datetime-local" class="form-control" id="eventStart" name="data_inizio" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Data e Ora Fine</label>
                            <input type="datetime-local" class="form-control" id="eventEnd" name="data_fine" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <span>Condividi con</span>
                                <button type="button" class="btn btn-outline-primary btn-sm ms-2" onclick="addShare()">
                                    + Aggiungi
                                </button>
                            </label>
                            <div id="shareList"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <span>Condividi intero calendario</span>
                                <button type="button" class="btn btn-outline-primary btn-sm ms-2" onclick="shareEntireCalendar()">
                                    Condividi
                                </button>
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <button type="button" class="btn btn-danger" id="deleteEvent" style="display:none">Elimina</button>
                    <button type="button" class="btn btn-primary" id="saveEvent">Salva</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/it.js"></script>
    <script src="scripts.js"></script>
</body>

</html>