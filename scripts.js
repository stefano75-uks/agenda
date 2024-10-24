// scripts.js
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

// Precompila data e ora selezionata
function openNewEventModal(info) {
    document.getElementById('modalTitle').textContent = 'Nuovo Appuntamento';
    document.getElementById('eventForm').reset();
    document.getElementById('eventId').value = '';

    document.getElementById('eventStart').value = formatDateTime(info.start);
    document.getElementById('eventEnd').value = formatDateTime(info.end);

    document.getElementById('deleteEvent').style.display = 'none';
    document.getElementById('shareList').innerHTML = '';
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

// Formatta la data per l'input datetime-local
function formatDateTime(date) {
    if (!(date instanceof Date)) {
        date = new Date(date);
    }
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// Condividi intero calendario
async function shareEntireCalendar() {
    try {
        const response = await fetch('share_calendar.php', {
            method: 'POST',
            body: JSON.stringify({ share: true })
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
});

