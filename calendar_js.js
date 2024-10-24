let calendar;
let eventModal;
let users = [];

document.addEventListener('DOMContentLoaded', function() {
    eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
    loadUsers();
    initializeCalendar();
    setupEventListeners();
});

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
        events: 'get_events.php',
        selectable: true,
        select: function(info) {
            openNewEventModal(info);
        },
        eventClick: function(info) {
            openEditEventModal(info.event);
        },
        eventDidMount: function(info) {
            const event = info.event;
            const isCreator = event.extendedProps.canEdit;
            const sharedCount = event.extendedProps.sharedCount || 0;
            
            // Aggiunge badge per il numero di condivisioni
            if (isCreator && sharedCount > 0) {
                const badge = document.createElement('span');
                badge.className = 'badge-share';
                badge.textContent = sharedCount;
                info.el.appendChild(badge);
            }

            // Aggiunge tooltip
            let tooltipContent = `
                <div class="event-tooltip">
                    <strong>${event.extendedProps.description}</strong><br>
                    <i class="fas fa-user me-1"></i> ${isCreator ? 'Creato da te' : 'Condiviso da: ' + event.extendedProps.creator}<br>
                    <i class="fas fa-clock me-1"></i> ${formatDateTime(event.start)} - ${formatDateTime(event.end)}
            `;
            
            if (event.extendedProps.sharedWith) {
                tooltipContent += `<br><i class="fas fa-share-alt me-1"></i> Condiviso con: ${event.extendedProps.sharedWith}`;
            }

            tooltipContent += `<br><i class="fas ${isCreator ? 'fa-edit' : 'fa-lock'} me-1"></i> 
                             ${isCreator ? 'Puoi modificare' : event.extendedProps.canEdit ? 'Puoi modificare' : 'Sola lettura'}`;
            
            tooltipContent += '</div>';
            
            tippy(info.el, {
                content: tooltipContent,
                allowHTML: true,
                placement: 'top'
            });
        }
    });
    calendar.render();
}

function setupEventListeners() {
    document.getElementById('saveEvent').addEventListener('click', saveEvent);
    document.getElementById('deleteEvent').addEventListener('click', deleteEvent);
    document.getElementById('addShare').addEventListener('click', addShareField);
    
    // Delegazione eventi per i pulsanti di rimozione condivisione
    document.getElementById('shareList').addEventListener('click', function(e) {
        if (e.target.closest('.remove-share')) {
            e.target.closest('.share-item').remove();
        }
    });
}

function addShareField() {
    const template = document.getElementById('shareTemplate');
    const shareList = document.getElementById('shareList');
    const clone = template.content.cloneNode(true);
    
    // Popola select utenti
    const userSelect = clone.querySelector('.user-select');
    users.forEach(user => {
        const option = document.createElement('option');
        option.value = user.id;
        option.textContent = `${user.nome} ${user.cognome}`;
        userSelect.appendChild(option);
    });
    
    shareList.appendChild(clone);
}

function loadUsers() {
    fetch('get_users.php')
        .then(response => response.json())
        .then(data => {
            users = data;
        })
        .catch(error => {
            console.error('Errore nel caricamento degli utenti:', error);
        });
}

// ... [Resto delle funzioni precedenti] ...

function saveEvent() {
    const form = document.getElementById('eventForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    
    // Aggiungi le condivisioni
    const shares = [];
    form.querySelectorAll('.share-item').forEach(item => {
        shares.push({
            user_id: item.querySelector('.user-select').value,
            permission: item.querySelector('.permission-select').value
        });
    });
    formData.append('shares', JSON.stringify(shares));
    
    fetch('save_event.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            calendar.refetchEvents();
            eventModal.hide();
        } else {
            alert(data.error || 'Errore durante il salvataggio');
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        alert('Errore durante il salvataggio');
    });
}

function formatDateTime(date) {
    if (!(date instanceof Date)) {
        date = new Date(date);
    }
    return date.toLocaleDateString('it-IT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
