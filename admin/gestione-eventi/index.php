<?php
session_start();
require_once '../../includes/db_config.php';
require_once '../../includes/database_mysql.php';
require_once '../auth_check.php';

$db = new Database();
$pdo = $db->pdo;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Eventi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="flex">
    <?php include '../partials/menu.php'; ?>

    <main class="flex-1 p-8">
        <div class="container mx-auto">
            <h1 class="text-4xl font-bold mb-8">Gestione Eventi</h1>

            <!-- Event Management Section -->
            <div id="event-management" class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-semibold mb-4">Tutti gli Eventi</h2>
                <div class="flex justify-between mb-4">
                    <input type="text" id="event-search" class="w-1/2 p-2 border rounded" placeholder="Cerca eventi...">
                    <button id="add-event-btn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        <i class="fas fa-plus"></i> Aggiungi Evento
                    </button>
                </div>
                <div id="events-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Event cards will be loaded here -->
                </div>
                <div id="event-pagination" class="mt-6 flex justify-center">
                    <!-- Pagination controls will be generated here -->
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Add/Edit Event Modal -->
<div id="event-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-2xl w-11/12 md:w-2/3 lg:w-1/2 max-h-[90vh] overflow-y-auto">
        <div class="p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 id="modal-title" class="text-3xl font-bold">Aggiungi Nuovo Evento</h2>
                <button id="close-modal-btn" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
            </div>
            <form id="event-form" class="space-y-6">
                <input type="hidden" id="event-id" name="id">
                <input type="hidden" id="hiddenImageUrl" name="hiddenImageUrl">

                <div>
                    <label for="titolo" class="block text-sm font-medium text-gray-700">Titolo Evento</label>
                    <input type="text" id="titolo" name="titolo" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="nomeAttivita" class="block text-sm font-medium text-gray-700">Nome Attività Organizzatrice</label>
                    <input type="text" id="nomeAttivita" name="nomeAttivita" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                </div>

                <div>
                    <label for="descrizione" class="block text-sm font-medium text-gray-700">Descrizione</label>
                    <textarea id="descrizione" name="descrizione" rows="4" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="categoria" class="block text-sm font-medium text-gray-700">Categoria</label>
                        <select id="categoria" name="categoria" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                           <option value="Sagra">Sagra</option>
                            <option value="Festa">Festa</option>
                            <option value="Concerto">Concerto</option>
                            <option value="Cultura">Cultura</option>
                            <option value="Sport">Sport</option>
                        </select>
                    </div>
                    <div>
                        <label for="provincia" class="block text-sm font-medium text-gray-700">Provincia</label>
                         <select id="provincia" name="provincia" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                            <option value="Cosenza">Cosenza</option>
                            <option value="Catanzaro">Catanzaro</option>
                            <option value="Reggio di Calabria">Reggio di Calabria</option>
                            <option value="Crotone">Crotone</option>
                            <option value="Vibo Valentia">Vibo Valentia</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="citta" class="block text-sm font-medium text-gray-700">Città</label>
                    <input type="text" id="citta" name="citta" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="dataEvento" class="block text-sm font-medium text-gray-700">Data e Ora Evento</label>
                        <input type="datetime-local" id="dataEvento" name="dataEvento" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label for="orarioInizio" class="block text-sm font-medium text-gray-700">Orario Inizio</label>
                        <input type="time" id="orarioInizio" name="orarioInizio" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                </div>

                <div>
                    <label for="costoIngresso" class="block text-sm font-medium text-gray-700">Costo Ingresso</label>
                    <input type="text" id="costoIngresso" name="costoIngresso" value="Gratuito" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                </div>

                <div>
                    <label for="imageFile" class="block text-sm font-medium text-gray-700">Immagine Evento</label>
                    <input type="file" id="imageFile" name="imageFile" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <img id="imagePreview" src="#" alt="Anteprima immagine" class="mt-4 h-40 w-auto object-cover rounded-md hidden">
                </div>

                <div>
                    <label for="linkMappaGoogle" class="block text-sm font-medium text-gray-700">Link Mappa Google</label>
                    <input type="url" id="linkMappaGoogle" name="linkMappaGoogle" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                </div>

                <div>
                    <label for="linkPreviewMappaEmbed" class="block text-sm font-medium text-gray-700">Link Anteprima Mappa (Embed)</label>
                    <textarea id="linkPreviewMappaEmbed" name="linkPreviewMappaEmbed" rows="3" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"></textarea>
                </div>

                <div>
                    <label for="linkContattoPrenotazioni" class="block text-sm font-medium text-gray-700">Link Contatto Prenotazioni (es. WhatsApp)</label>
                    <input type="url" id="linkContattoPrenotazioni" name="linkContattoPrenotazioni" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                </div>

                <div class="flex justify-end pt-4">
                    <button type="button" id="cancel-btn" class="bg-gray-300 text-gray-800 px-6 py-2 rounded-md mr-4 hover:bg-gray-400">Annulla</button>
                    <button type="submit" id="save-btn" class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600">Salva Evento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const API_URL = 'api.php';
    const eventsList = document.getElementById('events-list');
    const pagination = document.getElementById('event-pagination');
    const addEventBtn = document.getElementById('add-event-btn');
    const modal = document.getElementById('event-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const eventForm = document.getElementById('event-form');
    const modalTitle = document.getElementById('modal-title');
    const imagePreview = document.getElementById('imagePreview');
    const imageFile = document.getElementById('imageFile');
    const searchInput = document.getElementById('event-search');

    let currentPage = 1;
    const limit = 12;

    const fetchEvents = async (page = 1, searchTerm = '') => {
        try {
            const response = await fetch(`${API_URL}?action=get_events&page=${page}&limit=${limit}&searchTerm=${encodeURIComponent(searchTerm)}`);
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            renderEvents(data.events);
            renderPagination(data.total, page);
        } catch (error) {
            console.error('Fetch error:', error);
            eventsList.innerHTML = '<p class="text-red-500 col-span-full">Errore nel caricamento degli eventi.</p>';
        }
    };

    const renderEvents = (events) => {
        eventsList.innerHTML = '';
        if (events.length === 0) {
            eventsList.innerHTML = '<p class="text-gray-500 col-span-full">Nessun evento trovato.</p>';
            return;
        }
        events.forEach(event => {
            const eventCard = `
                <div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:-translate-y-1 transition-transform duration-300">
                    <img src="../../eventi/${event.imageUrl}" alt="${event.titolo}" class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="text-xl font-bold mb-2">${event.titolo}</h3>
                        <p class="text-gray-600 text-sm mb-1"><i class="fas fa-map-marker-alt mr-2"></i>${event.citta}, ${event.provincia}</p>
                        <p class="text-gray-600 text-sm mb-3"><i class="fas fa-calendar-alt mr-2"></i>${new Date(event.dataEvento).toLocaleString('it-IT')}</p>
                        <div class="flex justify-end space-x-2">
                            <button class="edit-btn text-blue-500 hover:text-blue-700" data-id="${event.id}"><i class="fas fa-edit"></i></button>
                            <button class="delete-btn text-red-500 hover:text-red-700" data-id="${event.id}"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            `;
            eventsList.innerHTML += eventCard;
        });
    };

    const renderPagination = (total, page) => {
        pagination.innerHTML = '';
        const totalPages = Math.ceil(total / limit);
        if (totalPages <= 1) return;

        for (let i = 1; i <= totalPages; i++) {
            const pageButton = document.createElement('button');
            pageButton.textContent = i;
            pageButton.className = `px-4 py-2 mx-1 rounded ${i === page ? 'bg-blue-500 text-white' : 'bg-gray-200'}`;
            pageButton.addEventListener('click', () => {
                currentPage = i;
                fetchEvents(currentPage, searchInput.value);
            });
            pagination.appendChild(pageButton);
        }
    };

    const openModal = (event = null) => {
        eventForm.reset();
        imagePreview.classList.add('hidden');
        if (event) {
            modalTitle.textContent = 'Modifica Evento';
            document.getElementById('event-id').value = event.id;
            document.getElementById('titolo').value = event.titolo;
            document.getElementById('nomeAttivita').value = event.nomeAttivita;
            document.getElementById('descrizione').value = event.descrizione;
            document.getElementById('categoria').value = event.categoria;
            document.getElementById('provincia').value = event.provincia;
            document.getElementById('citta').value = event.citta;
            document.getElementById('dataEvento').value = event.dataEvento.slice(0, 16);
            document.getElementById('orarioInizio').value = event.orarioInizio;
            document.getElementById('costoIngresso').value = event.costoIngresso;
            document.getElementById('hiddenImageUrl').value = event.imageUrl;
            document.getElementById('linkMappaGoogle').value = event.linkMappaGoogle;
            document.getElementById('linkPreviewMappaEmbed').value = event.linkPreviewMappaEmbed;
            document.getElementById('linkContattoPrenotazioni').value = event.linkContattoPrenotazioni;
            if(event.imageUrl) {
                imagePreview.src = `../../eventi/${event.imageUrl}`;
                imagePreview.classList.remove('hidden');
            }
        } else {
            modalTitle.textContent = 'Aggiungi Nuovo Evento';
        }
        modal.classList.remove('hidden');
    };

    const closeModal = () => {
        modal.classList.add('hidden');
    };

    addEventBtn.addEventListener('click', () => openModal());
    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    imageFile.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        }
    });

    eventForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(eventForm);
        try {
            const response = await fetch(`${API_URL}?action=save_event`, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                closeModal();
                fetchEvents(currentPage, searchInput.value);
            } else {
                alert('Errore: ' + result.error);
            }
        } catch (error) {
            console.error('Save error:', error);
            alert('Si è verificato un errore durante il salvataggio.');
        }
    });

    eventsList.addEventListener('click', async (e) => {
        const editBtn = e.target.closest('.edit-btn');
        const deleteBtn = e.target.closest('.delete-btn');

        if (editBtn) {
            const eventId = editBtn.dataset.id;
            const response = await fetch(`${API_URL}?action=get_events_by_ids&ids=${eventId}`);
            const events = await response.json();
            if (events.length > 0) {
                openModal(events[0]);
            }
        }

        if (deleteBtn) {
            const eventId = deleteBtn.dataset.id;
            if (confirm('Sei sicuro di voler eliminare questo evento?')) {
                try {
                    const response = await fetch(`${API_URL}?action=delete_event`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: eventId })
                    });
                    const result = await response.json();
                    if (result.success) {
                        fetchEvents(currentPage, searchInput.value);
                    } else {
                        alert('Errore: ' + result.error);
                    }
                } catch (error) {
                    console.error('Delete error:', error);
                    alert('Si è verificato un errore durante l\'eliminazione.');
                }
            }
        }
    });

    searchInput.addEventListener('input', () => {
        currentPage = 1;
        fetchEvents(currentPage, searchInput.value);
    });

    fetchEvents();
});
</script>

</body>
</html>
