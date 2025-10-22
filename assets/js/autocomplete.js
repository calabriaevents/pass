/**
 * Sistema di Autocompletamento Globale per Passione Calabria
 * Gestisce la ricerca per articoli, città, categorie, etc.
 */

class Autocomplete {
    constructor(inputId, resultsContainerId, options = {}) {
        this.inputId = inputId;
        this.resultsContainerId = resultsContainerId;
        this.options = {
            placeholder: 'Luoghi, eventi, tradizioni...',
            noResultsText: 'Nessun risultato trovato',
            minChars: 2,
            apiEndpoint: 'api/search.php',
            ...options
        };

        this.input = document.getElementById(this.inputId);
        this.resultsContainer = document.getElementById(this.resultsContainerId);
        this.debounceTimer = null;

        this.init();
    }

    init() {
        if (!this.input || !this.resultsContainer) {
            console.error('Autocomplete: Input o contenitore risultati non trovati');
            return;
        }

        this.setupInput();
        this.bindEvents();
        this.setupPositioning();
    }

    setupPositioning() {
        // Funzione per posizionare il dropdown rispetto all'input
        this.positionDropdown = () => {
            const inputRect = this.input.getBoundingClientRect();
            
            // Verifica che l'input sia effettivamente visibile e abbia dimensioni valide
            if (inputRect.width === 0 || inputRect.height === 0 || 
                (inputRect.top === 0 && inputRect.left === 0 && inputRect.right === 0 && inputRect.bottom === 0)) {
                console.warn('Input non visibile, riprovo il posizionamento...');
                // Riprova dopo un breve delay
                setTimeout(() => this.positionDropdown(), 50);
                return;
            }
            
            this.resultsContainer.style.position = 'fixed';
            this.resultsContainer.style.top = (inputRect.bottom + 2) + 'px';
            this.resultsContainer.style.left = inputRect.left + 'px';
            this.resultsContainer.style.width = inputRect.width + 'px';
            this.resultsContainer.style.zIndex = '9999';
        };
        
        // Riposiziona al resize della finestra
        window.addEventListener('resize', () => {
            if (!this.resultsContainer.classList.contains('hidden')) {
                this.positionDropdown();
            }
        });
        
        // Riposiziona al scroll
        window.addEventListener('scroll', () => {
            if (!this.resultsContainer.classList.contains('hidden')) {
                this.positionDropdown();
            }
        });
    }

    setupInput() {
        this.input.placeholder = this.options.placeholder;
        this.input.setAttribute('autocomplete', 'off');
    }

    bindEvents() {
        this.input.addEventListener('input', (e) => {
            this.handleInput(e.target.value);
        });

        this.input.addEventListener('focus', (e) => {
            // Delay per assicurarsi che l'input sia completamente renderizzato
            setTimeout(() => {
                this.positionDropdown();
            }, 10);
            if (e.target.value.length >= this.options.minChars) {
                this.search(e.target.value);
            }
        });

        this.input.addEventListener('blur', () => {
            // Delay per permettere il click sui risultati
            setTimeout(() => this.hideResults(), 200);
        });

        this.input.addEventListener('keydown', (e) => {
            this.handleKeydown(e);
        });

        // Gestione click su un risultato (delegation)
        this.resultsContainer.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link) {
                e.preventDefault();
                window.location.href = link.href;
            }
        });
    }

    handleInput(value) {
        clearTimeout(this.debounceTimer);

        if (value.length < this.options.minChars) {
            this.hideResults();
            return;
        }

        this.debounceTimer = setTimeout(() => {
            this.search(value);
        }, 300);
    }

    async search(query) {
        try {
            const url = `${this.options.apiEndpoint}?autocomplete=true&q=${encodeURIComponent(query)}`;
            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                this.showResults(data.results, query);
            } else {
                this.showError('Nessun risultato');
            }
        } catch (error) {
            console.error('Errore nella ricerca:', error);
            this.showError('Errore di connessione');
        }
    }

    showResults(results, query) {
        this.resultsContainer.innerHTML = '';

        if (results.length === 0) {
            this.showNoResults();
        } else {
            results.forEach((result, index) => {
                const item = this.createResultItem(result, index === 0);
                this.resultsContainer.appendChild(item);
            });
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        this.showResultsContainer();
    }

    createResultItem(result, isFirst = false) {
        const item = document.createElement('a');
        item.href = result.url;
        item.className = `block p-3 hover:bg-gray-100 transition-colors duration-200 border-b last:border-b-0 cursor-pointer ${isFirst ? 'bg-gray-50' : ''}`;

        let icon = `<i data-lucide="${result.icon || 'search'}" class="w-5 h-5 inline-block mr-3 text-slate-500"></i>`;

        item.innerHTML = `
            <div class="flex items-center">
                ${icon}
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-gray-900">${this.highlightMatch(result.title, this.input.value)}</div>
                    <div class="text-xs text-gray-500 truncate">${result.description}</div>
                </div>
            </div>
        `;
        return item;
    }

    showNoResults() {
        const item = document.createElement('div');
        item.className = 'p-3 text-center text-sm text-gray-500';
        item.textContent = this.options.noResultsText;
        this.resultsContainer.appendChild(item);
        this.showResultsContainer();
    }

    showResultsContainer() {
        // Assicurati che il posizionamento avvenga dopo che l'elemento è visibile
        this.resultsContainer.classList.remove('hidden');
        // Posiziona dopo un breve delay per assicurarsi che tutto sia renderizzato
        setTimeout(() => {
            this.positionDropdown();
        }, 5);
    }

    hideResults() {
        this.resultsContainer.classList.add('hidden');
    }

    highlightMatch(text, query) {
        if (!query) return text;
        const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<mark class="bg-transparent text-blue-600 font-bold">$1</mark>');
    }

    handleKeydown(e) {
        const items = this.resultsContainer.querySelectorAll('a');
        if (items.length === 0) return;

        let activeIndex = Array.from(items).findIndex(item => item.classList.contains('bg-gray-50'));

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                activeIndex = activeIndex < items.length - 1 ? activeIndex + 1 : 0;
                this.setActiveItem(items, activeIndex);
                break;

            case 'ArrowUp':
                e.preventDefault();
                activeIndex = activeIndex > 0 ? activeIndex - 1 : items.length - 1;
                this.setActiveItem(items, activeIndex);
                break;

            case 'Enter':
                e.preventDefault();
                if (activeIndex >= 0 && items[activeIndex]) {
                    items[activeIndex].click();
                }
                break;

            case 'Escape':
                this.hideResults();
                break;
        }
    }

    setActiveItem(items, activeIndex) {
        items.forEach((item, index) => {
            if (index === activeIndex) {
                item.classList.add('bg-gray-50');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('bg-gray-50');
            }
        });
    }

    showError(message) {
        this.resultsContainer.innerHTML = `
            <div class="p-3 text-center text-sm text-red-600">
                ${message}
            </div>
        `;
        this.showResultsContainer();
    }

    clear() {
        this.input.value = '';
        this.hideResults();
    }
}
