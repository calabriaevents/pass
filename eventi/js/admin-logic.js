// --- LOGICA SEZIONE ADMIN ---
const Admin = {
    isAuthenticated: false,
    init() {
        this.isAuthenticated = sessionStorage.getItem('isAdminAuthenticated') === 'true';
        this.addEventListeners();
    },
    addEventListeners() {
        document.getElementById('event-form').addEventListener('submit', this.handleEventFormSubmit.bind(this));
        document.getElementById('activity-form').addEventListener('submit', this.handleActivityFormSubmit.bind(this));
        document.getElementById('settings-form').addEventListener('submit', this.handleSettingsFormSubmit.bind(this));
        document.getElementById('provincia').addEventListener('change', (e) => this.updateCityDatalist(e.target.value));
        document.getElementById('admin-login-form').addEventListener('submit', this.handleLogin.bind(this));
    },
    navigate(pageId) {
        document.querySelectorAll('.admin-page').forEach(page => page.style.display = 'none');
        document.getElementById(pageId).style.display = 'block';
        document.querySelectorAll('.admin-nav-link').forEach(link => {
            link.classList.remove('bg-gray-900', 'font-bold');
            if (link.getAttribute('href') === `#${pageId}`) link.classList.add('bg-gray-900', 'font-bold');
        });
        switch(pageId) {
            case 'admin-dashboard': this.loadDashboardStats(); break;
            case 'admin-manage-events': this.loadEvents(); break;
            case 'admin-manage-activities': this.loadActivities(); break;
            case 'admin-settings': this.loadSettings(); break;
        }
    },
    populateSelects() {
        const categorySelect = document.getElementById('categoria');
        const provinceSelect = document.getElementById('provincia');

        categorySelect.innerHTML = '';
        AppData.categories.forEach(cat => categorySelect.innerHTML += `<option value="${cat}">${cat}</option>`);

        provinceSelect.innerHTML = '';
        Object.keys(App.cache.locations).forEach(prov => provinceSelect.innerHTML += `<option value="${prov}">${prov}</option>`);

        this.updateCityDatalist(provinceSelect.value);
    },
    updateCityDatalist(province) {
        const datalist = document.getElementById('citta-list');
        datalist.innerHTML = '';
        if (province && App.cache.locations[province]) {
            App.cache.locations[province].forEach(city => {
                datalist.innerHTML += `<option value="${city}">`;
            });
        }
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
    async loadEvents() {
        const container = document.getElementById('admin-events-list');
        App.showLoading('admin-events-list');
        try {
            const events = await App.fetchApi('get_events&limit=1000');
            if (!events.events || events.events.length === 0) return App.showEmptyMessage('admin-events-list', 'Nessun evento trovato. Creane uno nuovo!');
            let html = '';
            events.events.forEach(event => {
                const eventDate = new Date(event.dataEvento).toLocaleDateString('it-IT');
                html += `<div class="bg-white p-4 rounded-lg shadow flex justify-between items-center"><div><h4 class="font-bold text-lg text-gray-800">${sanitizeHTML(event.titolo)}</h4><p class="text-sm text-gray-500">${sanitizeHTML(event.citta)}, ${sanitizeHTML(event.provincia)} - ${eventDate}</p></div><div class="space-x-2"><button onclick="Admin.showEventForm(${event.id})" class="bg-yellow-500 text-white w-10 h-10 rounded-full hover:bg-yellow-600"><i class="fas fa-pencil-alt"></i></button><button onclick="Admin.deleteEvent(${event.id}, '${sanitizeHTML(event.titolo.replace(/'/g, "\\'"))}')" class="bg-red-500 text-white w-10 h-10 rounded-full hover:bg-red-600"><i class="fas fa-trash"></i></button></div></div>`;
            });
            container.innerHTML = html;
        } catch(e) {
            App.showEmptyMessage('admin-events-list', 'Errore nel caricamento degli eventi.');
        }
    },
    async showEventForm(eventId = null) {
        this.navigate('admin-event-form-page');
        const form = document.getElementById('event-form');
        form.reset();
        document.getElementById('eventId').value = '';
        document.getElementById('hiddenImageUrl').value = '';
        document.getElementById('image-preview-container').innerHTML = '';
        document.getElementById('citta').value = '';

        if (eventId) {
            document.getElementById('event-form-title').innerText = 'Modifica Evento';
            const allEvents = (await App.fetchApi('get_events&limit=10000')).events;
            const event = allEvents.find(e => e.id == eventId);
            if (event) {
                document.getElementById('eventId').value = event.id;
                document.getElementById('titolo').value = event.titolo || '';
                document.getElementById('nomeAttivita').value = event.nomeAttivita || '';
                document.getElementById('descrizione').value = event.descrizione || '';
                document.getElementById('categoria').value = event.categoria || '';
                document.getElementById('provincia').value = event.provincia || '';
                this.updateCityDatalist(event.provincia);
                document.getElementById('citta').value = event.citta || '';
                document.getElementById('dataEvento').value = event.dataEvento.split(' ')[0];
                document.getElementById('orarioInizio').value = event.orarioInizio || '';
                document.getElementById('costoIngresso').value = event.costoIngresso || '';
                document.getElementById('hiddenImageUrl').value = event.imageUrl || '';
                if(event.imageUrl) {
                    document.getElementById('image-preview-container').innerHTML = `<p class="text-sm text-gray-500 mt-2">Immagine attuale:</p><img src="${event.imageUrl}" class="mt-2 rounded-lg max-h-40">`;
                }
                document.getElementById('linkMappaGoogle').value = event.linkMappaGoogle || '';
                document.getElementById('linkPreviewMappaEmbed').value = event.linkPreviewMappaEmbed || '';
                document.getElementById('linkContattoPrenotazioni').value = event.linkContattoPrenotazioni || '';
            }
        } else {
            document.getElementById('event-form-title').innerText = 'Nuovo Evento';
            this.updateCityDatalist(document.getElementById('provincia').value);
        }
    },
    async handleEventFormSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        formData.append('id', document.getElementById('eventId').value);
        formData.append('titolo', document.getElementById('titolo').value);
        formData.append('nomeAttivita', document.getElementById('nomeAttivita').value);
        formData.append('descrizione', document.getElementById('descrizione').value);
        formData.append('categoria', document.getElementById('categoria').value);
        formData.append('provincia', document.getElementById('provincia').value);
        formData.append('citta', document.getElementById('citta').value);
        formData.append('dataEvento', `${document.getElementById('dataEvento').value} ${document.getElementById('orarioInizio').value}`);
        formData.append('orarioInizio', document.getElementById('orarioInizio').value);
        formData.append('costoIngresso', document.getElementById('costoIngresso').value);
        formData.append('hiddenImageUrl', document.getElementById('hiddenImageUrl').value);
        formData.append('linkMappaGoogle', document.getElementById('linkMappaGoogle').value);
        formData.append('linkPreviewMappaEmbed', document.getElementById('linkPreviewMappaEmbed').value);
        formData.append('linkContattoPrenotazioni', document.getElementById('linkContattoPrenotazioni').value);

        const imageFile = document.getElementById('imageFile').files[0];
        if (imageFile) {
            formData.append('imageFile', imageFile);
        }

        try {
            await App.fetchApi('save_event', { method: 'POST', body: formData });
            alert('Evento salvato!');
            await App.fetchInitialData();
            this.navigate('admin-manage-events');
        } catch (error) {
            alert(`Errore nel salvataggio dell'evento: ${error.message}`);
        }
    },
    async deleteEvent(eventId, title) {
        App.showDeleteModal(title, async () => {
            try {
                await App.fetchApi('delete_event', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ id: eventId })
                });
                this.loadEvents();
            } catch (error) { alert("Errore durante l'eliminazione."); }
        });
    },
    loadActivities() {
        const activities = App.cache.activities;
        if (!activities || activities.length === 0) return App.showEmptyMessage('admin-activities-list', 'Nessuna attività trovata.');
        let html = '';
        activities.forEach(activity => {
            const expiryDate = new Date(activity.dataFineVisualizzazione).toLocaleDateString('it-IT');
            const isActive = new Date(activity.dataFineVisualizzazione) >= new Date();
            html += `<div class="bg-white p-4 rounded-lg shadow flex justify-between items-center"><div><h4 class="font-bold text-lg text-gray-800">${sanitizeHTML(activity.nomeAttivita)}</h4><p class="text-sm text-gray-500">Scade il: ${expiryDate} <span class="ml-2 font-bold ${isActive ? 'text-green-500' : 'text-red-500'}">${isActive ? 'ATTIVA' : 'SCADUTA'}</span></p></div><div class="space-x-2"><button onclick="Admin.showActivityForm(${activity.id})" class="bg-yellow-500 text-white w-10 h-10 rounded-full hover:bg-yellow-600"><i class="fas fa-pencil-alt"></i></button><button onclick="Admin.deleteActivity(${activity.id}, '${sanitizeHTML(activity.nomeAttivita.replace(/'/g, "\\'"))}')" class="bg-red-500 text-white w-10 h-10 rounded-full hover:bg-red-600"><i class="fas fa-trash"></i></button></div></div>`;
        });
        document.getElementById('admin-activities-list').innerHTML = html;
    },
    showActivityForm(activityId = null) {
        this.navigate('admin-activity-form-page');
        document.getElementById('activity-form').reset();
        document.getElementById('activityId').value = '';
        document.getElementById('hiddenLogoUrl').value = '';
        document.getElementById('logo-preview-container').innerHTML = '';

        if (activityId) {
            document.getElementById('activity-form-title').innerText = 'Modifica Attività';
            const activity = App.cache.activities.find(a => a.id == activityId);
            if (activity) {
                document.getElementById('activityId').value = activity.id;
                document.getElementById('activity-nomeAttivita').value = activity.nomeAttivita || '';
                document.getElementById('activity-linkDestinazione').value = activity.linkDestinazione || '';
                document.getElementById('hiddenLogoUrl').value = activity.logoUrl || '';
                 if(activity.logoUrl) {
                    document.getElementById('logo-preview-container').innerHTML = `<p class="text-sm text-gray-500 mt-2">Logo attuale:</p><img src="${activity.logoUrl}" class="mt-2 rounded-lg max-h-20">`;
                }
                document.getElementById('activity-dataFineVisualizzazione').value = activity.dataFineVisualizzazione;
            }
        } else {
            document.getElementById('activity-form-title').innerText = 'Nuova Attività';
        }
    },
    async handleActivityFormSubmit(e) {
        e.preventDefault();
        const formData = new FormData();
        formData.append('id', document.getElementById('activityId').value);
        formData.append('nomeAttivita', document.getElementById('activity-nomeAttivita').value);
        formData.append('linkDestinazione', document.getElementById('activity-linkDestinazione').value);
        formData.append('hiddenLogoUrl', document.getElementById('hiddenLogoUrl').value);
        formData.append('dataFineVisualizzazione', document.getElementById('activity-dataFineVisualizzazione').value);
        const logoFile = document.getElementById('logoFile').files[0];
        if (logoFile) {
            formData.append('logoFile', logoFile);
        }
        try {
            await App.fetchApi('save_activity', { method: 'POST', body: formData });
            alert('Attività salvata!');
            await App.fetchInitialData();
            this.navigate('admin-manage-activities');
        } catch (error) { alert(`Errore nel salvataggio dell'attività: ${error.message}`); }
    },
    async deleteActivity(activityId, title) {
        App.showDeleteModal(title, async () => {
            try {
                await App.fetchApi('delete_activity', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ id: activityId })
                });
                await App.fetchInitialData();
                this.loadActivities();
            } catch (error) { alert("Errore durante l'eliminazione."); }
        });
    },
    loadSettings() {
        const config = App.cache.config;
        document.getElementById('hiddenLogoAppUrl').value = config.logoAppUrl || '';
         if(config.logoAppUrl) {
            document.getElementById('logo-app-preview-container').innerHTML = `<p class="text-sm text-gray-500 mt-2">Logo attuale:</p><img src="${config.logoAppUrl}" class="mt-2 rounded-lg max-h-20">`;
        }
        document.getElementById('settings-linkInstagram').value = config.linkInstagram || '';
        document.getElementById('settings-linkFacebook').value = config.linkFacebook || '';
        document.getElementById('settings-linkSitoWeb').value = config.linkSitoWeb || '';
        document.getElementById('settings-linkIscriviAttivita').value = config.linkIscriviAttivita || '';
    },
    async handleSettingsFormSubmit(e) {
        e.preventDefault();
        const formData = new FormData();
        formData.append('hiddenLogoAppUrl', document.getElementById('hiddenLogoAppUrl').value);
        formData.append('linkInstagram', document.getElementById('settings-linkInstagram').value);
        formData.append('linkFacebook', document.getElementById('settings-linkFacebook').value);
        formData.append('linkSitoWeb', document.getElementById('settings-linkSitoWeb').value);
        formData.append('linkIscriviAttivita', document.getElementById('settings-linkIscriviAttivita').value);
        const logoAppFile = document.getElementById('logoAppFile').files[0];
        if (logoAppFile) {
            formData.append('logoAppFile', logoAppFile);
        }
        try {
            await App.fetchApi('save_settings', { method: 'POST', body: formData });
            alert('Impostazioni salvate!');
            await App.fetchInitialData();
        } catch (error) { alert(`Errore nel salvataggio delle impostazioni: ${error.message}`); }
    },
    async loadDashboardStats() {
        try {
            const stats = await App.fetchApi('get_dashboard_stats');
            document.getElementById('stats-active-events').textContent = stats.active_events || 0;
            document.getElementById('stats-active-activities').textContent = stats.active_activities || 0;
            document.getElementById('stats-total-visits').textContent = stats.total_visits || 0;
            document.getElementById('stats-current-month-visits').textContent = stats.current_month_visits || 0;
            document.getElementById('stats-previous-month-visits').textContent = stats.previous_month_visits || 0;
        } catch (error) {
            console.error("Errore caricamento statistiche:", error);
        }
    },
    showLoginModal() {
        document.getElementById('admin-login-modal').classList.remove('hidden');
    },
    hideLoginModal() {
        document.getElementById('admin-login-modal').classList.add('hidden');
        document.getElementById('login-error-message').classList.add('hidden');
        document.getElementById('admin-login-form').reset();
    },
    async handleLogin(e) {
        e.preventDefault();
        const loginButton = document.getElementById('admin-login-btn');
        loginButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i>`;
        loginButton.disabled = true;

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const errorMessage = document.getElementById('login-error-message');
        errorMessage.classList.add('hidden');

        try {
            const response = await App.fetchApi('login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });
            if (response.success) {
                this.isAuthenticated = true;
                sessionStorage.setItem('isAdminAuthenticated', 'true');
                this.hideLoginModal();
                App.switchToAdminView();
            }
        } catch (error) {
            errorMessage.textContent = error.message || 'Credenziali non valide.';
            errorMessage.classList.remove('hidden');
        } finally {
            loginButton.innerHTML = `Accedi`;
            loginButton.disabled = false;
        }
    },
    logout() {
        App.fetchApi('logout').then(() => {
            sessionStorage.removeItem('isAdminAuthenticated');
            this.isAuthenticated = false;
            App.switchToUserView();
        }).catch(err => {
            console.error("Errore durante il logout:", err);
            sessionStorage.removeItem('isAdminAuthenticated');
            this.isAuthenticated = false;
            App.switchToUserView();
        });
    },
    checkAuth() {
        return this.isAuthenticated;
    }
};

const AdminSequence = {
    sequence: ['home', 'search', 'favorites', 'logo'],
    currentStep: 0,
    timer: null,
    startTimer() {
        clearTimeout(this.timer);
        this.timer = setTimeout(() => { this.reset(); }, 4000);
    },
    reset() {
        this.currentStep = 0;
        clearTimeout(this.timer);
    },
    registerTap(key) {
        if (this.currentStep === 0 && key !== this.sequence[0]) {
            this.reset();
            return;
        }
        if (this.currentStep === 0 && key === this.sequence[0]) {
            this.startTimer();
        }
        if (key === this.sequence[this.currentStep]) {
            this.currentStep++;
            if (this.currentStep === this.sequence.length) {
                this.reset();
                Admin.showLoginModal();
            }
        } else {
            this.reset();
        }
    }
};