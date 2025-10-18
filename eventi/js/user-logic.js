// --- FUNZIONE DI SICUREZZA PER L'OUTPUT ---
const sanitizeHTML = (str) => {
    if (str === null || str === undefined) return '';
    const temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
};

// --- CODICE PER PROGRESSIVE WEB APP (PWA) ---
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(registration => {
            console.log('ServiceWorker registration successful');
        }, err => {
            console.log('ServiceWorker registration failed: ', err);
        });
    });
}

let deferredPrompt;
const installButton = document.getElementById('nav-install');

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    installButton.classList.remove('hidden');
});

installButton.addEventListener('click', (e) => {
    installButton.classList.add('hidden');
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then((choiceResult) => {
        deferredPrompt = null;
    });
});

// Logica per il banner di installazione iOS e contatore visite
document.addEventListener('DOMContentLoaded', () => {
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isInStandaloneMode = ('standalone' in window.navigator) && (window.navigator.standalone);
    const banner = document.getElementById('ios-install-banner');
    const closeBannerBtn = document.getElementById('close-ios-banner');

    if (isIOS && !isInStandaloneMode && !sessionStorage.getItem('iosBannerClosed')) {
        banner.classList.remove('hidden');
        setTimeout(() => { banner.style.transform = 'translateY(0)'; }, 100);
    }

    closeBannerBtn.addEventListener('click', () => {
        banner.style.transform = 'translateY(100%)';
        sessionStorage.setItem('iosBannerClosed', 'true');
    });

    if (sessionStorage.getItem('isAdminAuthenticated') !== 'true') {
        fetch('./api.php?action=track_visit').catch(err => console.error('Failed to track visit.'));
    }
});

// --- DATI STATICI DELL'APPLICAZIONE ---
const AppData = {
    categories: ["Sport", "Concerto", "Sagra", "Teatro", "Feste"]
};

// --- OGGETTO PRINCIPALE DELL'APP ---
const App = {
    cache: { activities: [], config: {}, locations: {} },
    init() {
        User.init();
        this.fetchInitialData();
    },
    async fetchInitialData() {
        try {
            const [activities, config, locations] = await Promise.all([
                this.fetchApi('get_activities'),
                this.fetchApi('get_config'),
                this.fetchApi('get_locations')
            ]);
            this.cache.activities = activities;
            this.cache.config = config;
            this.cache.locations = locations;

            User.populateSearchFilters();

            User.loadGlobalConfig();
            User.loadActivePromos();
            User.performSearch('home-events-list', 'home-load-more-container', User.getHomeFilters(), false);
        } catch (error) {
            console.error("Errore nel caricamento dei dati iniziali:", error);
            alert("Impossibile caricare i dati dell'applicazione. Controlla la connessione e la configurazione dell'API.");
        }
    },
    async fetchApi(action, options = {}) {
        const url = `./api.php?action=${action}&t=${new Date().getTime()}`;
        try {
            options.credentials = 'include';

            const response = await fetch(url, options);
            if (!response.ok) {
                const errorData = await response.json();
                if (response.status === 403) {
                    alert("La tua sessione di amministrazione è scaduta. Effettua nuovamente il login.");
                }
                throw new Error(errorData.error || `Errore HTTP: ${response.status}`);
            }
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) {
                return await response.json();
            }
            return await response.text();

        } catch (error) {
            console.error(`Errore nella chiamata API [${action}]:`, error);
            throw error;
        }
    },
    showLoading(elementId) {
        const el = document.getElementById(elementId);
        if(el) el.innerHTML = `<div class="text-center p-8"><i class="fas fa-spinner fa-spin text-3xl text-indigo-500"></i><p class="mt-2">Caricamento...</p></div>`;
    },
    showEmptyMessage(elementId, message) {
         const el = document.getElementById(elementId);
        if(el) el.innerHTML = `<div class="text-center p-8 text-gray-500"><i class="fas fa-info-circle text-3xl mb-2"></i><p>${message}</p></div>`;
    }
};

const User = {
    favorites: [], currentPromoIndex: 0, promoInterval: null, calendarDate: new Date(),
    historyStack: ['user-home'],
    searchState: { page: 1, filters: {}, isLoading: false, total: 0, loaded: 0 },
    homeState: { page: 1, filters: {}, isLoading: false, total: 0, loaded: 0 },
    init() {
        this.loadFavoritesFromStorage();
        this.addEventListeners();
        this.populateHomeCategoryFilters();
        this.setupCalendar();
    },
    addEventListeners() {
        document.getElementById('nav-home').addEventListener('click', (e) => { e.preventDefault(); this.navigate('user-home'); });
        document.getElementById('nav-search').addEventListener('click', (e) => { e.preventDefault(); this.navigate('user-search'); });
        document.getElementById('nav-favorites').addEventListener('click', (e) => { e.preventDefault(); this.navigate('user-favorites'); });

        document.getElementById('search-input').addEventListener('input', () => this.debounce(() => this.performSearch('search-results-list', 'search-load-more-container', this.getSearchFilters(), false), 500)());
        document.getElementById('search-category').addEventListener('change', () => this.performSearch('search-results-list', 'search-load-more-container', this.getSearchFilters(), false));
        document.getElementById('search-province').addEventListener('change', (e) => {
            this.populateCitySelect('search-city', e.target.value, true);
            this.performSearch('search-results-list', 'search-load-more-container', this.getSearchFilters(), false);
        });
        document.getElementById('search-city').addEventListener('change', () => this.performSearch('search-results-list', 'search-load-more-container', this.getSearchFilters(), false));
        document.getElementById('search-reset-btn').addEventListener('click', () => this.resetSearch());
        document.getElementById('open-calendar-btn').addEventListener('click', () => this.showCalendarModal());
        document.getElementById('close-calendar-modal').addEventListener('click', () => this.hideCalendarModal());
        document.getElementById('prev-month-modal').addEventListener('click', () => this.changeMonth(-1));
        document.getElementById('next-month-modal').addEventListener('click', () => this.changeMonth(1));
        document.getElementById('voice-assistant-btn').addEventListener('click', () => this.startVoiceRecognition());
    },
    debounce(func, delay) {
        let timeout;
        return function(...args) { clearTimeout(timeout); timeout = setTimeout(() => func.apply(this, args), delay); };
    },
    goBack() {
        if (this.historyStack.length > 1) {
            this.historyStack.pop();
            const previousPage = this.historyStack[this.historyStack.length - 1];
            this.navigate(previousPage, null, false);
        }
    },
    navigate(pageId, eventId = null, addToHistory = true) {
        if (addToHistory && pageId !== this.historyStack[this.historyStack.length - 1]) {
            this.historyStack.push(pageId);
        }
        document.querySelectorAll('.user-page').forEach(page => page.classList.remove('active'));
        document.getElementById(pageId)?.classList.add('active');

        const navPageId = pageId.replace('user-','');
        document.querySelectorAll('.user-nav-link').forEach(link => {
            link.classList.remove('text-indigo-600'); link.classList.add('text-gray-600');
            if (link.getAttribute('href') === `#${navPageId}`) {
                 link.classList.add('text-indigo-600'); link.classList.remove('text-gray-600');
            }
        });
        window.scrollTo(0, 0);
        switch(pageId) {
            case 'user-home': this.performSearch('home-events-list', 'home-load-more-container', this.getHomeFilters(), false); break;
            case 'user-search': this.performSearch('search-results-list', 'search-load-more-container', this.getSearchFilters(), false); break;
            case 'user-favorites': this.renderFavoritesPage(); break;
            case 'user-event-detail': if(eventId) this.renderEventDetail(eventId); break;
        }
    },
    loadGlobalConfig() {
        const config = App.cache.config;
        const logoEl = document.getElementById('user-app-logo');

        if (config.logoAppUrl) {
            logoEl.src = config.logoAppUrl;
        } else {
            logoEl.src = "https://placehold.co/150x40/000000/FFFFFF?text=Calabria+Events";
        }

        const socialContainer = document.getElementById('social-links-container');
        socialContainer.innerHTML = '';
        if (config.linkFacebook) socialContainer.innerHTML += `<a href="${config.linkFacebook}" target="_blank" class="text-gray-500 hover:text-blue-800"><i class="fab fa-facebook-square"></i></a>`;
        if (config.linkInstagram) socialContainer.innerHTML += `<a href="${config.linkInstagram}" target="_blank" class="text-gray-500 hover:text-pink-600"><i class="fab fa-instagram"></i></a>`;
        if (config.linkSitoWeb) socialContainer.innerHTML += `<a href="${config.linkSitoWeb}" target="_blank" class="text-gray-500 hover:text-gray-800"><i class="fas fa-globe"></i></a>`;
        document.getElementById('iscrivi-attivita-link').href = config.linkIscriviAttivita || '#';
    },
    populateHomeCategoryFilters() {
        const container = document.getElementById('home-category-filters');
        container.innerHTML = '';
        const categories = ['Tutto', ...AppData.categories];
        categories.forEach(cat => {
            const btn = document.createElement('button');
            btn.dataset.category = cat;
            btn.className = 'category-button text-sm py-2 px-3 bg-transparent border border-gray-400 text-gray-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 rounded-lg transition-colors duration-200';
            btn.textContent = cat;
            if (cat === 'Tutto') btn.classList.add('active');
            btn.addEventListener('click', () => {
                document.querySelector('#home-category-filters .active').classList.remove('active');
                btn.classList.add('active');
                this.performSearch('home-events-list', 'home-load-more-container', this.getHomeFilters(), false);
            });
            container.appendChild(btn);
        });
    },
    getHomeFilters() {
        const activeCategoryEl = document.querySelector('#home-category-filters .active');
        if (!activeCategoryEl) return { category: 'Tutto' }; // Fallback
        return { category: activeCategoryEl.dataset.category };
    },
    getSearchFilters() {
        return {
            category: document.getElementById('search-category').value,
            province: document.getElementById('search-province').value,
            city: document.getElementById('search-city').value,
            searchTerm: document.getElementById('search-input').value,
        };
    },
    async performSearch(listId, loadMoreContainerId, filters, loadMore) {
        const state = (listId === 'home-events-list') ? this.homeState : this.searchState;

        if (state.isLoading) return;
        state.isLoading = true;

        if (!loadMore) {
            state.page = 1;
            state.loaded = 0;
            state.total = 0;
            document.getElementById(listId).innerHTML = '';
            App.showLoading(listId);
        }

        const loadMoreContainer = document.getElementById(loadMoreContainerId);
        loadMoreContainer.innerHTML = `<i class="fas fa-spinner fa-spin text-2xl text-indigo-500"></i>`;

        let query = `page=${state.page}`;
        for (const key in filters) {
            if (filters[key]) {
                query += `&${key}=${encodeURIComponent(filters[key])}`;
            }
        }

        try {
            const data = await App.fetchApi(`get_events&${query}`);
            if (!loadMore) {
                document.getElementById(listId).innerHTML = '';
            }

            if (data.events.length === 0 && !loadMore) {
                App.showEmptyMessage(listId, "Nessun evento trovato con i filtri selezionati.");
            } else {
                data.events.forEach(event => {
                    document.getElementById(listId).innerHTML += this.createEventCard(event);
                });
            }

            state.page++;
            state.loaded += data.events.length;
            state.total = data.total;

            if (state.loaded < state.total) {
                loadMoreContainer.innerHTML = `<button onclick="User.performSearch('${listId}', '${loadMoreContainerId}', User.getFiltersForContext('${listId}'), true)" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg">Carica Altri</button>`;
            } else {
                loadMoreContainer.innerHTML = '';
            }
        } catch (error) {
            App.showEmptyMessage(listId, "Si è verificato un errore durante la ricerca.");
        } finally {
            state.isLoading = false;
            if (state.loaded >= state.total) {
                loadMoreContainer.innerHTML = '';
            }
        }
    },
    getFiltersForContext(listId) {
        return (listId === 'home-events-list') ? this.getHomeFilters() : this.getSearchFilters();
    },
    loadActivePromos() {
        const allActivities = App.cache.activities;
        if (!Array.isArray(allActivities)) return; // Safety check
        const now = new Date();
        const activePromos = allActivities.filter(act => new Date(act.dataFineVisualizzazione) >= now);

        const container = document.getElementById('promo-popup-container');
        if (activePromos.length > 0) {
            container.classList.remove('hidden');
            this.startPromoRotation(activePromos);
        } else {
            container.classList.add('hidden');
            if (this.promoInterval) clearInterval(this.promoInterval);
        }
    },
    startPromoRotation(promos) {
        if (this.promoInterval) clearInterval(this.promoInterval);
        const container = document.getElementById('promo-popup-container');
        const showPromo = () => {
            if (promos.length === 0) return;
            const promo = promos[this.currentPromoIndex];
            container.innerHTML = `<div class="max-w-md mx-auto"><a href="${sanitizeHTML(promo.linkDestinazione)}" target="_blank" class="block bg-gradient-to-r from-indigo-500 to-purple-600 p-4 rounded-lg shadow-lg text-white no-underline"><div class="flex items-center space-x-4"><img src="${sanitizeHTML(promo.logoUrl)}" alt="${sanitizeHTML(promo.nomeAttivita)}" class="w-12 h-12 rounded-full object-cover border-2 border-white flex-shrink-0"><div class="overflow-hidden"><p class="font-bold truncate">${sanitizeHTML(promo.nomeAttivita)}</p><p class="text-sm opacity-90">Scopri di più!</p></div></div></a></div>`;
            this.currentPromoIndex = (this.currentPromoIndex + 1) % promos.length;
        };
        showPromo();
        this.promoInterval = setInterval(showPromo, 20000);
    },
    loadFavoritesFromStorage() { this.favorites = JSON.parse(localStorage.getItem('calabria_events_favorites') || '[]'); },
    saveFavoritesToStorage() { localStorage.setItem('calabria_events_favorites', JSON.stringify(this.favorites)); },
    toggleFavorite(eventId, element) {
        eventId = parseInt(eventId);
        const index = this.favorites.indexOf(eventId);
        if (index > -1) {
            this.favorites.splice(index, 1);
            element.innerHTML = '<i class="far fa-heart"></i>'; element.classList.remove('text-red-500');
        } else {
            this.favorites.push(eventId);
            element.innerHTML = '<i class="fas fa-heart"></i>'; element.classList.add('text-red-500');
        }
        this.saveFavoritesToStorage();
        if (document.getElementById('user-favorites').classList.contains('active')) this.renderFavoritesPage();
    },
    async renderFavoritesPage() {
        const listId = 'favorites-list';
        App.showLoading(listId);
        if (this.favorites.length === 0) return App.showEmptyMessage(listId, 'Non hai ancora eventi preferiti.');

        try {
            const idString = this.favorites.join(',');
            const favoriteEvents = await App.fetchApi(`get_events_by_ids&ids=${idString}`);

            document.getElementById(listId).innerHTML = '';
            if (favoriteEvents.length === 0) return App.showEmptyMessage(listId, 'Nessuno dei tuoi preferiti è disponibile.');

            let html = '';
            favoriteEvents.forEach(event => html += this.createEventCard(event));
            document.getElementById(listId).innerHTML = html;
        } catch(e) {
            App.showEmptyMessage(listId, 'Errore nel caricamento dei preferiti.');
        }
    },
    populateSearchFilters() {
        const categorySelect = document.getElementById('search-category');
        const provinceSelect = document.getElementById('search-province');

        categorySelect.innerHTML = '<option value="">Tutte le Categorie</option>';
        AppData.categories.forEach(cat => categorySelect.innerHTML += `<option value="${cat}">${cat}</option>`);

        provinceSelect.innerHTML = '<option value="">Tutte le Province</option>';
        Object.keys(App.cache.locations).forEach(prov => provinceSelect.innerHTML += `<option value="${prov}">${prov}</option>`);

        this.populateCitySelect('search-city', '', true);
    },
    populateCitySelect(selectId, province, addAllCitiesOption = false) {
        const citySelect = document.getElementById(selectId);
        citySelect.innerHTML = '';
        if (addAllCitiesOption) {
            citySelect.innerHTML = '<option value="">Tutte le Città</option>';
        }
        if (province && App.cache.locations[province]) {
            App.cache.locations[province].forEach(city => {
                citySelect.innerHTML += `<option value="${city}">${city}</option>`;
            });
        }
    },
    resetSearch() {
        document.getElementById('search-input').value = '';
        document.getElementById('search-category').value = '';
        document.getElementById('search-province').value = '';
        this.populateCitySelect('search-city', '', true);
        this.performSearch('search-results-list', 'search-load-more-container', this.getSearchFilters(), false);
    },
    showCalendarModal() {
        document.getElementById('calendar-modal').classList.remove('hidden');
        this.updateCalendarCounts();
    },
    hideCalendarModal() { document.getElementById('calendar-modal').classList.add('hidden'); },
    showEventsForDate(dateString) {
        this.hideCalendarModal();
        const filters = { ...this.getSearchFilters(), date: dateString };
        this.performSearch('search-results-list', 'search-load-more-container', filters, false);
    },
    setupCalendar() { this.renderCalendar(); },
    renderCalendar() {
        const grid = document.getElementById('calendar-grid-modal');
        const monthYearEl = document.getElementById('calendar-month-year-modal');
        grid.innerHTML = '';
        const date = this.calendarDate;
        const month = date.getMonth(), year = date.getFullYear();
        monthYearEl.textContent = date.toLocaleDateString('it-IT', { month: 'long', year: 'numeric' });
        ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'].forEach(day => grid.innerHTML += `<div class="font-bold text-xs text-gray-500">${day}</div>`);
        const firstDayOfMonth = new Date(year, month, 1).getDay();
        for (let i = 0; i < (firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1); i++) grid.innerHTML += '<div></div>';
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        for (let i = 1; i <= daysInMonth; i++) {
            const dayString = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            grid.innerHTML += `<div id="day-modal-${dayString}" class="calendar-day p-2 cursor-pointer hover:bg-indigo-100 rounded-full transition-colors" onclick="User.showEventsForDate('${dayString}')">${i}</div>`;
        }
    },
    async updateCalendarCounts() {
        const allEvents = (await App.fetchApi('get_events&limit=10000')).events;
        document.querySelectorAll('.event-count').forEach(el => el.remove());
        const counts = {};
        allEvents.forEach(event => {
            const dateString = event.dataEvento.split(' ')[0];
            counts[dateString] = (counts[dateString] || 0) + 1;
        });
        for (const [date, count] of Object.entries(counts)) {
            const dayEl = document.getElementById(`day-modal-${date}`);
            if (dayEl) { dayEl.classList.add('has-events'); dayEl.innerHTML += `<span class="event-count">${count}</span>`; }
        }
    },
    changeMonth(direction) {
        this.calendarDate.setMonth(this.calendarDate.getMonth() + direction);
        this.renderCalendar();
        this.updateCalendarCounts();
    },
    createEventCard(event) {
        const isFav = this.favorites.includes(parseInt(event.id));
        const eventDate = new Date(event.dataEvento).toLocaleDateString('it-IT', { day: '2-digit', month: 'short', year: 'numeric' });
        return `<div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:-translate-y-1 transition-transform duration-300"><div class="relative"><img src="${sanitizeHTML(event.imageUrl)}" alt="${sanitizeHTML(event.titolo)}" class="w-full h-48 object-cover" onerror="this.src='https://placehold.co/600x400/cccccc/FFFFFF?text=Immagine+non+disponibile'"><button onclick="User.toggleFavorite(${event.id}, this)" class="absolute top-2 right-2 bg-white/80 w-10 h-10 rounded-full flex items-center justify-center text-xl ${isFav ? 'text-red-500' : 'text-gray-600'}"><i class="${isFav ? 'fas' : 'far'} fa-heart"></i></button></div><div class="p-4"><p class="text-sm text-indigo-500 font-semibold">${sanitizeHTML(event.categoria)}</p><h3 class="font-bold text-lg text-gray-800 truncate">${sanitizeHTML(event.titolo)}</h3><p class="text-sm text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i>${sanitizeHTML(event.citta)}, ${sanitizeHTML(event.provincia)}</p><p class="text-sm text-gray-500"><i class="fas fa-calendar-alt mr-1"></i>${eventDate} - ${sanitizeHTML(event.orarioInizio.substring(0, 5))}</p><button onclick="User.navigate('user-event-detail', ${event.id})" class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300">Dettagli</button></div></div>`;
    },
    async renderEventDetail(eventId) {
        App.showLoading('user-event-detail');
        try {
            const allEvents = (await App.fetchApi('get_events&limit=10000')).events;
            const event = allEvents.find(e => e.id == eventId);
            if (!event) return App.showEmptyMessage('user-event-detail', 'Evento non trovato.');

            const isFav = this.favorites.includes(parseInt(event.id));
            const eventDate = new Date(event.dataEvento).toLocaleDateString('it-IT', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            document.getElementById('user-event-detail').innerHTML = `<div><button onclick="User.goBack()" class="mb-4 text-indigo-600 hover:underline"><i class="fas fa-arrow-left mr-2"></i>Torna indietro</button><div class="relative"><img src="${sanitizeHTML(event.imageUrl)}" alt="${sanitizeHTML(event.titolo)}" class="w-full h-64 object-cover rounded-lg shadow-lg" onerror="this.src='https://placehold.co/800x400/cccccc/FFFFFF?text=Immagine+non+disponibile'"><button onclick="User.toggleFavorite(${event.id}, this)" class="absolute top-4 right-4 bg-white/80 w-12 h-12 rounded-full flex items-center justify-center text-2xl ${isFav ? 'text-red-500' : 'text-gray-600'}"><i class="${isFav ? 'fas' : 'far'} fa-heart"></i></button></div><div class="bg-white p-6 rounded-lg shadow-md -mt-10 relative z-10 mx-4"><span class="bg-indigo-100 text-indigo-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded-full">${sanitizeHTML(event.categoria)}</span><h2 class="text-3xl font-bold text-gray-900 mt-2">${sanitizeHTML(event.titolo)}</h2><p class="text-md text-gray-600 mt-1">Organizzato da: ${sanitizeHTML(event.nomeAttivita)}</p><div class="mt-6 space-y-4 text-gray-700"><div class="flex items-center"><i class="fas fa-calendar-alt w-6 text-indigo-500"></i><span>${eventDate} alle ${sanitizeHTML(event.orarioInizio.substring(0, 5))}</span></div><div class="flex items-center"><i class="fas fa-map-marker-alt w-6 text-indigo-500"></i><span>${sanitizeHTML(event.citta)}, ${sanitizeHTML(event.provincia)}</span></div><div class="flex items-center"><i class="fas fa-money-bill-wave w-6 text-indigo-500"></i><span>Ingresso: ${sanitizeHTML(event.costoIngresso)}</span></div></div><hr class="my-6"><h3 class="text-xl font-bold text-gray-800 mb-2">Descrizione</h3><p class="text-gray-600 whitespace-pre-wrap">${sanitizeHTML(event.descrizione)}</p><div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6"><a href="${sanitizeHTML(event.linkMappaGoogle)}" target="_blank" class="w-full text-center bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg transition duration-300"><i class="fas fa-map-signs mr-2"></i>Raggiungimi</a>${event.linkContattoPrenotazioni ? `<a href="${sanitizeHTML(event.linkContattoPrenotazioni)}" target="_blank" class="w-full text-center bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg transition duration-300"><i class="fas fa-ticket-alt mr-2"></i>Prenota Ora</a>` : ''}</div><div class="mt-6"><h3 class="text-xl font-bold text-gray-800 mb-2">Mappa</h3><div class="aspect-w-16 aspect-h-9 rounded-lg overflow-hidden border border-gray-200">${event.linkPreviewMappaEmbed}</div></div></div></div>`;
        } catch(e) {
            App.showEmptyMessage('user-event-detail', 'Errore nel caricamento dei dettagli.');
        }
    },
    showToast(message) {
        const toast = document.getElementById('voice-toast');
        toast.textContent = message;
        toast.classList.remove('opacity-0');
        setTimeout(() => toast.classList.add('opacity-0'), 3000);
    },
    speak(text) {
        if ('speechSynthesis' in window) {
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'it-IT';
            window.speechSynthesis.speak(utterance);
        }
    },
    startVoiceRecognition() {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) { this.showToast("Assistente vocale non supportato."); return; }
        const recognition = new SpeechRecognition();
        recognition.lang = 'it-IT';
        recognition.interimResults = false;
        const micIcon = document.querySelector('#voice-assistant-btn i');
        const voiceModal = document.getElementById('voice-recognition-modal');
        recognition.onstart = () => { micIcon.classList.add('text-red-500'); voiceModal.classList.remove('hidden'); };
        recognition.onerror = (event) => { micIcon.classList.remove('text-red-500'); voiceModal.classList.add('hidden'); this.showToast(`Errore: ${event.error}`); };
        recognition.onend = () => { micIcon.classList.remove('text-red-500'); voiceModal.classList.add('hidden'); };
        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript.toLowerCase().trim();
            this.showToast(`Hai detto: ${transcript}`);
            this.processVoiceCommand(transcript);
        };
        recognition.start();
    },
    processVoiceCommand(command) {
        if (command.includes("vai a casa") || command === "home") { this.navigate('user-home'); this.speak("Ok, torno alla home."); return; }
        if (command.includes("cerca") && !command.includes("eventi")) { this.navigate('user-search'); this.speak("Ok, vado alla ricerca."); return; }
        if (command.includes("preferiti")) { this.navigate('user-favorites'); this.speak("Ecco i tuoi preferiti."); return; }
        if (command.includes("reset") || command.includes("cancella filtri")) { this.resetSearch(); this.speak("Filtri resettati."); return; }
        this.navigate('user-search');
        let appliedFilter = false;
        AppData.categories.forEach(cat => { if (command.includes(cat.toLowerCase())) { document.getElementById('search-category').value = cat; appliedFilter = true; } });
        const allLocations = [...Object.keys(App.cache.locations), ...Object.values(App.cache.locations).flat()];
        allLocations.forEach(loc => {
            if (command.includes(loc.toLowerCase())) {
                if (App.cache.locations[loc]) { document.getElementById('search-province').value = loc; this.populateCitySelect('search-city', loc, true); }
                else {
                    for (const prov in App.cache.locations) {
                        if (App.cache.locations[prov].includes(loc)) {
                            document.getElementById('search-province').value = prov;
                            this.populateCitySelect('search-city', prov, true);
                            document.getElementById('search-city').value = loc;
                            break;
                        }
                    }
                }
                appliedFilter = true;
            }
        });
        if (appliedFilter) { this.performSearch('search-results-list', 'search-load-more-container', this.getSearchFilters(), false); this.speak("Ecco i risultati per la tua ricerca."); }
        else { this.speak("Non ho capito il comando, puoi ripetere?"); }
    }
};

// --- AVVIO APPLICAZIONE ---
document.addEventListener('DOMContentLoaded', () => App.init());
window.App = App; window.User = User;