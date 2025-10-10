/**
 * Sistema di Autocompletamento Città per Passione Calabria
 * Sostituisce i menu a tendina con input intelligente
 */

class CityAutocomplete {
    constructor(inputId, provinceSelectId, options = {}) {
        this.inputId = inputId;
        this.provinceSelectId = provinceSelectId;
        this.options = {
            placeholder: 'Inizia a scrivere il nome della città...',
            noResultsText: 'Nessuna città trovata',
            createNewText: 'Crea nuova città',
            minChars: 2,
            ...options
        };
        
        this.input = document.getElementById(inputId);
        this.provinceSelect = document.getElementById(provinceSelectId);
        this.selectedCityId = null;
        this.resultsContainer = null;
        this.debounceTimer = null;
        
        this.init();
    }
    
    init() {
        if (!this.input || !this.provinceSelect) {
            console.error('CityAutocomplete: Input o select provincia non trovati');
            return;
        }
        
        this.setupInput();
        this.createResultsContainer();
        this.bindEvents();
    }
    
    setupInput() {
        // Sostituisci il select città esistente con un input
        const citySelect = document.getElementById('city_id');
        if (citySelect) {
            // Salva il valore selezionato se presente
            const selectedOption = citySelect.querySelector('option[selected]') || citySelect.querySelector('option:checked');
            if (selectedOption && selectedOption.value) {
                this.selectedCityId = selectedOption.value;
                this.input.value = selectedOption.textContent.trim();
            }
            
            // Nascondi il select originale
            citySelect.style.display = 'none';
            citySelect.name = 'city_id_original'; // Cambia nome per evitare submit
        }
        
        // Configura l'input
        this.input.placeholder = this.options.placeholder;
        this.input.setAttribute('autocomplete', 'off');
        this.input.className = 'w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500';
        
        // Aggiungi campo hidden per il city_id
        this.hiddenInput = document.createElement('input');
        this.hiddenInput.type = 'hidden';
        this.hiddenInput.name = 'city_id';
        this.hiddenInput.value = this.selectedCityId || '';
        this.input.parentNode.appendChild(this.hiddenInput);
    }
    
    createResultsContainer() {
        this.resultsContainer = document.createElement('div');
        this.resultsContainer.className = 'absolute z-50 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto hidden';
        this.input.parentNode.style.position = 'relative';
        this.input.parentNode.appendChild(this.resultsContainer);
    }
    
    bindEvents() {
        // Input eventi
        this.input.addEventListener('input', (e) => {
            this.handleInput(e.target.value);
        });
        
        this.input.addEventListener('focus', (e) => {
            if (e.target.value.length >= this.options.minChars) {
                this.search(e.target.value);
            }
        });
        
        this.input.addEventListener('blur', (e) => {
            // Delay per permettere il click sui risultati
            setTimeout(() => {
                this.hideResults();
            }, 200);
        });
        
        // Navigazione con tastiera
        this.input.addEventListener('keydown', (e) => {
            this.handleKeydown(e);
        });
        
        // Chiudi risultati quando si clicca fuori
        document.addEventListener('click', (e) => {
            if (!this.input.contains(e.target) && !this.resultsContainer.contains(e.target)) {
                this.hideResults();
            }
        });
    }
    
    handleInput(value) {
        clearTimeout(this.debounceTimer);
        
        if (value.length < this.options.minChars) {
            this.hideResults();
            this.clearSelection();
            return;
        }
        
        this.debounceTimer = setTimeout(() => {
            this.search(value);
        }, 300);
    }
    
    async search(query) {
        try {
            const response = await fetch(`../api/search-cities.php?q=${encodeURIComponent(query)}`);
            const cities = await response.json();
            
            this.showResults(cities, query);
        } catch (error) {
            console.error('Errore nella ricerca città:', error);
            this.showError('Errore nella ricerca');
        }
    }
    
    showResults(cities, query) {
        this.resultsContainer.innerHTML = '';
        
        if (cities.length === 0) {
            this.showNoResults(query);
        } else {
            cities.forEach((city, index) => {
                const item = this.createResultItem(city, index === 0);
                this.resultsContainer.appendChild(item);
            });
            
            // Opzione per creare nuova città se non trovata
            if (cities.length < 5) {
                this.addCreateNewOption(query);
            }
        }
        
        this.showResultsContainer();
    }
    
    createResultItem(city, isFirst = false) {
        const item = document.createElement('div');
        item.className = `px-4 py-3 cursor-pointer hover:bg-blue-50 border-b border-gray-100 last:border-b-0 ${isFirst ? 'bg-blue-50' : ''}`;
        item.innerHTML = `
            <div class="font-medium text-gray-900">${this.highlightMatch(city.name, this.input.value)}</div>
            <div class="text-sm text-gray-500">${city.province}</div>
        `;
        
        item.addEventListener('click', () => {
            this.selectCity(city);
        });
        
        return item;
    }
    
    showNoResults(query) {
        const item = document.createElement('div');
        item.className = 'px-4 py-3 text-gray-500 text-center';
        item.textContent = this.options.noResultsText;
        this.resultsContainer.appendChild(item);
        
        this.addCreateNewOption(query);
    }
    
    addCreateNewOption(query) {
        const provinceId = this.provinceSelect.value;
        if (!provinceId || !query.trim()) return;
        
        const item = document.createElement('div');
        item.className = 'px-4 py-3 cursor-pointer hover:bg-green-50 border-t-2 border-green-200 bg-green-25';
        item.innerHTML = `
            <div class="flex items-center text-green-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                ${this.options.createNewText}: "${query.trim()}"
            </div>
        `;
        
        item.addEventListener('click', () => {
            this.createNewCity(query.trim(), provinceId);
        });
        
        this.resultsContainer.appendChild(item);
    }
    
    async createNewCity(cityName, provinceId) {
        try {
            const response = await fetch('../api/create-city.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    name: cityName,
                    province_id: parseInt(provinceId)
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                const newCity = {
                    id: result.city_id,
                    name: result.city_name,
                    province: result.province_name,
                    display: `${result.city_name} (${result.province_name})`
                };
                
                this.selectCity(newCity);
                this.showSuccessMessage(result.message);
            } else {
                this.showError(result.error || 'Errore nella creazione della città');
            }
        } catch (error) {
            console.error('Errore creazione città:', error);
            this.showError('Errore nella creazione della città');
        }
    }
    
    selectCity(city) {
        this.selectedCityId = city.id;
        this.input.value = city.name;
        this.hiddenInput.value = city.id;
        this.hideResults();
        
        // Trigger change event per altri handler
        this.input.dispatchEvent(new Event('change', { bubbles: true }));
        this.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
    
    clearSelection() {
        this.selectedCityId = null;
        this.hiddenInput.value = '';
    }
    
    showResultsContainer() {
        this.resultsContainer.classList.remove('hidden');
    }
    
    hideResults() {
        this.resultsContainer.classList.add('hidden');
    }
    
    highlightMatch(text, query) {
        const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
    }
    
    handleKeydown(e) {
        const items = this.resultsContainer.querySelectorAll('.cursor-pointer');
        if (items.length === 0) return;
        
        let activeIndex = Array.from(items).findIndex(item => 
            item.classList.contains('bg-blue-50') || item.classList.contains('bg-green-50')
        );
        
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
                item.classList.add('bg-blue-50');
                item.classList.remove('bg-green-50');
            } else {
                item.classList.remove('bg-blue-50', 'bg-green-50');
            }
        });
    }
    
    showError(message) {
        this.resultsContainer.innerHTML = `
            <div class="px-4 py-3 text-red-600 text-center">
                <div class="flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    ${message}
                </div>
            </div>
        `;
        this.showResultsContainer();
    }
    
    showSuccessMessage(message) {
        // Mostra temporaneamente un messaggio di successo
        const successDiv = document.createElement('div');
        successDiv.className = 'absolute top-0 right-0 bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded text-sm z-10';
        successDiv.textContent = message;
        
        this.input.parentNode.appendChild(successDiv);
        
        setTimeout(() => {
            if (successDiv.parentNode) {
                successDiv.parentNode.removeChild(successDiv);
            }
        }, 3000);
    }
    
    // Metodi pubblici
    getValue() {
        return this.selectedCityId;
    }
    
    setValue(cityId, cityName) {
        this.selectedCityId = cityId;
        this.input.value = cityName || '';
        this.hiddenInput.value = cityId || '';
    }
    
    clear() {
        this.input.value = '';
        this.clearSelection();
        this.hideResults();
    }
    
    disable() {
        this.input.disabled = true;
        this.input.classList.add('bg-gray-100');
    }
    
    enable() {
        this.input.disabled = false;
        this.input.classList.remove('bg-gray-100');
    }
}

// Funzione helper per inizializzare automaticamente
window.initCityAutocomplete = function(inputId = 'city_autocomplete', provinceSelectId = 'province_id') {
    return new CityAutocomplete(inputId, provinceSelectId);
};

// Auto-inizializzazione se gli elementi esistono
document.addEventListener('DOMContentLoaded', function() {
    const cityInput = document.getElementById('city_autocomplete');
    const provinceSelect = document.getElementById('province_id');
    
    if (cityInput && provinceSelect) {
        window.cityAutocomplete = new CityAutocomplete('city_autocomplete', 'province_id');
    }
});