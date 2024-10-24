<?php
require_once 'config.php';
require_once 'auth.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Condivisa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .navbar {
            border-radius: 0 0 20px 20px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .fc {
            background: white;
            padding: 20px;
            border-radius: 20px;
        }

        .fc-toolbar-title {
            font-size: 1.5em !important;
        }

        .fc-button {
            border-radius: 10px !important;
            padding: 8px 15px !important;
        }

        .fc-event {
            border-radius: 10px !important;
            padding: 3px 10px !important;
            margin: 2px !important;
            border: none !important;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
        }

        .form-control,
        .form-select {
            border-radius: 15px !important;
            padding: 12px 20px;
        }

        .btn {
            border-radius: 15px;
        }

        .event-personal {
            background-color: #ff4f4f !important;
        }

        /* Rosso per eventi personali */
        .event-shared {
            background-color: #4caf50 !important;
        }

        /* Verde per eventi condivisi */

        .calendar-legend {
            background: white;
            padding: 15px;
            border-radius: 20px;
            margin-bottom: 20px;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            margin-right: 20px;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 6px;
            margin-right: 8px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">📅 Agenda Condivisa</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link">
                    Benvenuto <?php echo htmlspecialchars($_SESSION['nome'] . ' ' . $_SESSION['cognome']); ?>
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

    <script>
        let calendar;
        let users = [];
        const eventModal = new bootstrap.Modal(document.getElementById('eventModal'));

        // Carica gli utenti all'avvio
        async function loadUsers() {
            try {
                const response = await fetch('get_users.php');
                const data = await response.json();
                users = data;
            } catch (error) {
                console.error('Errore nel caricamento degli utenti:', error);
            }
        }

        // Inizializza il calendario
        function initializeCalendar() {
            const calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'it',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: {
                    url: 'get_events.php',
                    failure: function() {
                        alert('Errore nel caricamento degli eventi');
                    }
                },
                selectable: true,
                select: function(info) {
                    openNewEventModal(info);
                },
                eventClick: function(info) {
                    openEditEventModal(info.event);
                },
                eventClassNames: function(arg) {
                    return [arg.event.extendedProps.isCreator ? 'event-personal' : 'event-shared'];
                },
                dayMaxEvents: true,
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                }
            });
            calendar.render();
        }

        // Gestione degli eventi del form
        function setupEventListeners() {
            document.getElementById('saveEvent').addEventListener('click', saveEvent);
            document.getElementById('deleteEvent').addEventListener('click', deleteEvent);

            // Previeni la chiusura del modal quando si clicca al suo interno
            document.querySelector('.modal-content').addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        // Aggiunge un campo per la condivisione
        function addShare() {
            const shareList = document.getElementById('shareList');
            const shareDiv = document.createElement('div');
            shareDiv.className = 'mt-2';

            const userOptions = users.map(user =>
                `<option value="${user.id}">${user.nome} ${user.cognome}</option>`
            ).join('');

            shareDiv.innerHTML = `
                <div class="input-group">
                    <select class="form-select" name="share_with[]" required>
                        <option value="">Seleziona utente</option>
                        ${userOptions}
                    </select>
                    <button type="button" class="btn btn-outline-danger" onclick="this.closest('.mt-2').remove()">
                        ×
                    </button>
                </div>
            `;

            shareList.appendChild(shareDiv);
        }

        // Apre il modal per un nuovo evento, precompilando la data e l'orario selezionati
        function openNewEventModal(info) {
            document.getElementById('modalTitle').textContent = 'Nuovo Appuntamento';
            document.getElementById('eventForm').reset();
            document.getElementById('eventId').value = '';

            // Precompila la data e l'orario di inizio e fine con quelli selezionati
            document.getElementById('eventStart').value = formatDateTime(info.start);
            document.getElementById('eventEnd').value = formatDateTime(info.end);

            document.getElementById('deleteEvent').style.display = 'none';
            document.getElementById('shareList').innerHTML = '';
            eventModal.show();
        }

        // Apre il modal per modificare un evento
        function openEditEventModal(event) {
            document.getElementById('modalTitle').textContent = 'Modifica Appuntamento';
            document.getElementById('eventId').value = event.id;
            document.getElementById('eventDescription').value = event.extendedProps.description;
            document.getElementById('eventStart').value = formatDateTime(event.start);
            document.getElementById('eventEnd').value = formatDateTime(event.end);
            document.getElementById('deleteEvent').style.display = event.extendedProps.canEdit ? 'block' : 'none';

            document.getElementById('shareList').innerHTML = '';
            if (event.extendedProps.shares) {
                event.extendedProps.shares.forEach(() => {
                    addShare();
                    const lastShare = document.querySelector('#shareList .mt-2:last-child select');
                    if (lastShare) {
                        lastShare.value = share.user_id;
                    }
                });
            }

            eventModal.show();
        }

        // Salva un evento
        async function saveEvent() {
            const form = document.getElementById('eventForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            try {
                const response = await fetch('save_event.php', {
                    method: 'POST',
                    body: new FormData(form)
                });

                const data = await response.json();

                if (data.success) {
                    calendar.refetchEvents();
                    eventModal.hide();
                } else {
                    alert(data.error || 'Errore durante il salvataggio');
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore durante il salvataggio');
            }
        }

        // Elimina un evento
        async function deleteEvent() {
            if (!confirm('Sei sicuro di voler eliminare questo appuntamento?')) {
                return;
            }

            const eventId = document.getElementById('eventId').value;

            try {
                const response = await fetch('delete_event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${eventId}`
                });

                const data = await response.json();

                if (data.success) {
                    calendar.refetchEvents();
                    eventModal.hide();
                } else {
                    alert(data.error || 'Errore durante l\'eliminazione');
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore durante l\'eliminazione');
            }
        }

        // Formatta la data per l'input datetime-local, includendo data e ora
        function formatDateTime(date) {
            if (!(date instanceof Date)) {
                date = new Date(date);
            }
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Mese a due cifre
            const day = String(date.getDate()).padStart(2, '0'); // Giorno a due cifre
            const hours = String(date.getHours()).padStart(2, '0'); // Ora a due cifre
            const minutes = String(date.getMinutes()).padStart(2, '0'); // Minuti a due cifre

            // Formattazione per input datetime-local: "YYYY-MM-DDTHH:MM"
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        // Condividi l'intero calendario
        async function shareEntireCalendar() {
            try {
                const response = await fetch('share_calendar.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        share: true
                    })
                });
                const data = await response.json();
                if (data.success) {
                    alert('Calendario condiviso con successo!');
                } else {
                    alert('Errore durante la condivisione del calendario.');
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore durante la condivisione del calendario.');
            }
        }

        // Inizializzazione
        document.addEventListener('DOMContentLoaded', async function() {
            await loadUsers();
            initializeCalendar();
            setupEventListeners();
        });
    </script>
</body>

</html>