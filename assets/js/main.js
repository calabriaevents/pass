// Passione Calabria - JavaScript Principale

document.addEventListener('DOMContentLoaded', function() {
    // Inizializza tutte le funzionalità
    initMobileMenu();
    initSearch();
    initSmoothScroll();
    initNewsletterForm();
    initLazyLoading();
    initTooltips();
    initCategoriesSlider();
    initArticlesSliders();
    
    // Inizializza mappa homepage se presente
    if (document.getElementById('homepage-map')) {
        initHomepageMap();
    }

    // Inizializza Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

// Mobile Menu - Versione migliorata e robusta
function initMobileMenu() {
    console.log('Inizializzazione menu mobile...');
    
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    console.log('Elementi trovati:', {
        btn: !!mobileMenuBtn,
        menu: !!mobileMenu
    });

    if (mobileMenuBtn && mobileMenu) {
        console.log('Menu mobile inizializzato correttamente');
        
        mobileMenuBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Click sul pulsante menu mobile');
            
            // Toggle del menu
            const isHidden = mobileMenu.classList.contains('hidden');
            
            if (isHidden) {
                mobileMenu.classList.remove('hidden');
                console.log('Menu aperto');
            } else {
                mobileMenu.classList.add('hidden');
                console.log('Menu chiuso');
            }

            // Cambia icona
            const icon = mobileMenuBtn.querySelector('[data-lucide]');
            if (icon) {
                const newIcon = isHidden ? 'x' : 'menu';
                icon.setAttribute('data-lucide', newIcon);
                console.log('Icona cambiata a:', newIcon);
                
                // Ricrea icone Lucide se disponibile
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                } else {
                    console.warn('Lucide non disponibile per ricreare le icone');
                }
            }
        });

        // Chiudi menu quando si clicca su un link
        const menuLinks = mobileMenu.querySelectorAll('a');
        console.log('Link del menu trovati:', menuLinks.length);
        
        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                console.log('Click su link del menu - chiusura menu');
                mobileMenu.classList.add('hidden');
                
                const icon = mobileMenuBtn.querySelector('[data-lucide]');
                if (icon) {
                    icon.setAttribute('data-lucide', 'menu');
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }
            });
        });

        // Chiudi menu quando si clicca fuori
        document.addEventListener('click', function(e) {
            if (!mobileMenuBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
                if (!mobileMenu.classList.contains('hidden')) {
                    console.log('Click esterno - chiusura menu');
                    mobileMenu.classList.add('hidden');
                    
                    const icon = mobileMenuBtn.querySelector('[data-lucide]');
                    if (icon) {
                        icon.setAttribute('data-lucide', 'menu');
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    }
                }
            }
        });

        // Chiudi menu su tasto ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !mobileMenu.classList.contains('hidden')) {
                console.log('ESC premuto - chiusura menu');
                mobileMenu.classList.add('hidden');
                
                const icon = mobileMenuBtn.querySelector('[data-lucide]');
                if (icon) {
                    icon.setAttribute('data-lucide', 'menu');
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }
            }
        });
        
    } else {
        console.error('ERRORE: Elementi menu mobile non trovati!', {
            'mobile-menu-btn trovato': !!mobileMenuBtn,
            'mobile-menu trovato': !!mobileMenu
        });
        
        // Debug: mostra tutti gli elementi con ID che iniziano con 'mobile'
        const allElements = document.querySelectorAll('[id*="mobile"]');
        console.log('Elementi con "mobile" nell\'ID:', Array.from(allElements).map(el => ({
            id: el.id,
            tagName: el.tagName,
            classes: el.className
        })));
    }
}

// Search functionality
function initSearch() {
    const searchInputs = document.querySelectorAll('input[type="text"][placeholder*="Cerca"]');

    searchInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch(this.value);
            }
        });
    });

    // Live search (debounced)
    let searchTimeout;
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length >= 3) {
                searchTimeout = setTimeout(() => {
                    performLiveSearch(query);
                }, 300);
            }
        });
    });
}

function performSearch(query) {
    if (query.trim()) {
        window.location.href = `ricerca.php?q=${encodeURIComponent(query)}`;
    }
}

function performLiveSearch(query) {
    // Implementazione ricerca live con AJAX (manteniamo per compatibilità)
    fetch(`api/search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            showSearchResults(data);
        })
        .catch(error => {
            console.error('Errore ricerca:', error);
        });
}

function showSearchResults(results) {
    // Mostra risultati in un dropdown o modal
    console.log('Risultati ricerca:', results);
}

// Smooth scroll
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');

    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Scroll to top
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Newsletter form
function initNewsletterForm() {
    const newsletterForms = document.querySelectorAll('form[action*="newsletter"]');

    newsletterForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const email = formData.get('email');

            if (!isValidEmail(email)) {
                showNotification('Inserisci un indirizzo email valido', 'error');
                return;
            }

            // Invia richiesta
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Iscrizione avvenuta con successo!', 'success');
                    this.reset();
                } else {
                    showNotification(data.message || 'Errore durante l\'iscrizione', 'error');
                }
            })
            .catch(error => {
                console.error('Errore:', error);
                showNotification('Errore di connessione', 'error');
            });
        });
    });
}

// Lazy loading images
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');

    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('opacity-0');
                    img.classList.add('opacity-100', 'transition-opacity', 'duration-300');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback per browser più vecchi
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    }
}

// Tooltips
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');

    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const text = e.target.getAttribute('data-tooltip');
    const tooltip = document.createElement('div');

    tooltip.className = 'absolute z-50 px-2 py-1 text-sm text-white bg-gray-900 rounded shadow-lg pointer-events-none';
    tooltip.textContent = text;
    tooltip.id = 'tooltip';

    document.body.appendChild(tooltip);

    const rect = e.target.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
}

function hideTooltip() {
    const tooltip = document.getElementById('tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

// Notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    const bgColor = {
        'success': 'bg-green-500',
        'error': 'bg-red-500',
        'warning': 'bg-yellow-500',
        'info': 'bg-blue-500'
    }[type] || 'bg-blue-500';

    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 text-white rounded-lg shadow-lg transform transition-all duration-300 ${bgColor}`;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Animazione entrata
    setTimeout(() => {
        notification.classList.add('translate-x-0');
    }, 100);

    // Auto-remove dopo 5 secondi
    setTimeout(() => {
        notification.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// Utility functions
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('it-IT', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('it-IT', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Loading state management
function showLoading(element) {
    element.classList.add('opacity-50', 'pointer-events-none');
    const loader = document.createElement('div');
    loader.className = 'absolute inset-0 flex items-center justify-center';
    loader.innerHTML = '<div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>';
    loader.id = 'loading-' + Date.now();

    element.style.position = 'relative';
    element.appendChild(loader);

    return loader.id;
}

function hideLoading(element, loaderId) {
    element.classList.remove('opacity-50', 'pointer-events-none');
    const loader = document.getElementById(loaderId);
    if (loader) {
        loader.remove();
    }
}

// Form utilities
function serializeForm(form) {
    const formData = new FormData(form);
    const data = {};

    for (let [key, value] of formData.entries()) {
        if (data[key]) {
            if (Array.isArray(data[key])) {
                data[key].push(value);
            } else {
                data[key] = [data[key], value];
            }
        } else {
            data[key] = value;
        }
    }

    return data;
}

function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('border-red-500');
            isValid = false;
        } else {
            field.classList.remove('border-red-500');
        }
    });

    return isValid;
}

// Articles Sliders inside Category Cards
function initArticlesSliders() {
    const sliders = document.querySelectorAll('.articles-slider[data-category]');
    
    sliders.forEach(sliderElement => {
        const categoryIdValue = sliderElement.getAttribute('data-category');
        if (!categoryIdValue) return;
        
        const slider = document.querySelector(`.articles-slider[data-category="${categoryIdValue}"]`);
        const prevBtn = document.querySelector(`.articles-prev[data-category="${categoryIdValue}"]`);
        const nextBtn = document.querySelector(`.articles-next[data-category="${categoryIdValue}"]`);
        const indicators = document.querySelectorAll(`.article-indicator[data-category="${categoryIdValue}"]`);
        
        if (!slider || !prevBtn || !nextBtn) return;
        
        let currentSlide = 0;
        const totalSlides = slider.children.length;
        
        // Update slider position
        function updateArticlesSlider() {
            const translateX = -(currentSlide * 100);
            slider.style.transform = `translateX(${translateX}%)`;
            
            // Update indicators
            indicators.forEach((indicator, index) => {
                if (index === currentSlide) {
                    indicator.classList.remove('bg-gray-300');
                    indicator.classList.add('bg-blue-500');
                } else {
                    indicator.classList.remove('bg-blue-500');
                    indicator.classList.add('bg-gray-300');
                }
            });
            
            // Update navigation buttons
            prevBtn.style.opacity = currentSlide === 0 ? '0.5' : '1';
            nextBtn.style.opacity = currentSlide === totalSlides - 1 ? '0.5' : '1';
        }
        
        // Navigate to specific slide
        function goToArticleSlide(slideIndex) {
            if (slideIndex < 0) slideIndex = totalSlides - 1;
            if (slideIndex >= totalSlides) slideIndex = 0;
            currentSlide = slideIndex;
            updateArticlesSlider();
        }
        
        // Event listeners
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            goToArticleSlide(currentSlide - 1);
        });
        
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            goToArticleSlide(currentSlide + 1);
        });
        
        // Indicator click events
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', (e) => {
                e.preventDefault();
                goToArticleSlide(index);
            });
        });
        
        // Initialize
        updateArticlesSlider();
        
        // Auto-scroll every 8 seconds (slower than main slider)
        let autoSlideInterval = setInterval(() => {
            goToArticleSlide(currentSlide + 1);
        }, 8000);
        
        // Pause on hover
        const categoryCard = slider.closest('.group');
        if (categoryCard) {
            categoryCard.addEventListener('mouseenter', () => {
                clearInterval(autoSlideInterval);
            });
            
            categoryCard.addEventListener('mouseleave', () => {
                autoSlideInterval = setInterval(() => {
                    goToArticleSlide(currentSlide + 1);
                }, 8000);
            });
        }
    });
}

// Export functions for global use
window.PassioneCalabria = {
    showNotification,
    performSearch,
    scrollToTop,
    showLoading,
    hideLoading,
    formatDate,
    formatDateTime,
    isValidEmail,
    validateForm
};

// Back to top button
window.addEventListener('scroll', function() {
    const backToTop = document.getElementById('back-to-top');
    if (backToTop) {
        if (window.pageYOffset > 300) {
            backToTop.classList.remove('hidden');
        } else {
            backToTop.classList.add('hidden');
        }
    }
});

// Print functionality
function printPage() {
    window.print();
}

// Share functionality
function shareArticle(url, title) {
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        }).catch(console.error);
    } else {
        // Fallback
        const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
}

// Categories Slider functionality
function initCategoriesSlider() {
    const slider = document.getElementById('categories-slider');
    const prevBtn = document.getElementById('categories-prev');
    const nextBtn = document.getElementById('categories-next');
    const indicators = document.querySelectorAll('.slider-indicator');
    
    if (!slider || !prevBtn || !nextBtn) return;
    
    const cards = slider.children;
    const totalCards = cards.length;
    let cardsPerSlide = getCardsPerSlide();
    let totalSlides = Math.ceil(totalCards / cardsPerSlide);
    let currentSlide = 0;
    let autoSlideInterval;
    
    // Responsive cards per slide
    function getCardsPerSlide() {
        if (window.innerWidth >= 1024) return 4; // lg
        if (window.innerWidth >= 768) return 2;  // md  
        return 1; // sm
    }
    
    // Update slider position
    function updateSlider() {
        const translateX = -(currentSlide * 100);
        slider.style.transform = `translateX(${translateX}%)`;
        
        // Update indicators
        indicators.forEach((indicator, index) => {
            if (index === currentSlide) {
                indicator.classList.remove('bg-gray-300');
                indicator.classList.add('bg-blue-500');
            } else {
                indicator.classList.remove('bg-blue-500');
                indicator.classList.add('bg-gray-300');
            }
        });
        
        // Update navigation buttons
        prevBtn.style.opacity = currentSlide === 0 ? '0.5' : '1';
        nextBtn.style.opacity = currentSlide === totalSlides - 1 ? '0.5' : '1';
    }
    
    // Navigate to specific slide
    function goToSlide(slideIndex) {
        if (slideIndex < 0) slideIndex = totalSlides - 1;
        if (slideIndex >= totalSlides) slideIndex = 0;
        currentSlide = slideIndex;
        updateSlider();
    }
    
    // Auto-scroll every 15 seconds
    function startAutoSlide() {
        stopAutoSlide();
        autoSlideInterval = setInterval(() => {
            goToSlide(currentSlide + 1);
        }, 15000);
    }
    
    function stopAutoSlide() {
        if (autoSlideInterval) {
            clearInterval(autoSlideInterval);
        }
    }
    
    // Event listeners
    prevBtn.addEventListener('click', () => {
        goToSlide(currentSlide - 1);
        stopAutoSlide();
        setTimeout(startAutoSlide, 5000); // Restart auto-slide after 5 seconds
    });
    
    nextBtn.addEventListener('click', () => {
        goToSlide(currentSlide + 1);
        stopAutoSlide();
        setTimeout(startAutoSlide, 5000); // Restart auto-slide after 5 seconds
    });
    
    // Indicator click events
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            goToSlide(index);
            stopAutoSlide();
            setTimeout(startAutoSlide, 5000);
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', debounce(() => {
        cardsPerSlide = getCardsPerSlide();
        totalSlides = Math.ceil(totalCards / cardsPerSlide);
        if (currentSlide >= totalSlides) {
            currentSlide = totalSlides - 1;
        }
        updateSlider();
    }, 250));
    
    // Pause auto-scroll on hover
    slider.parentElement.addEventListener('mouseenter', stopAutoSlide);
    slider.parentElement.addEventListener('mouseleave', startAutoSlide);
    
    // Initialize
    updateSlider();
    startAutoSlide();
}

// --- TARGETED AJAX FORM HANDLING WITH UPLOAD PROGRESS ---

const MAX_TOTAL_UPLOAD_SIZE = 8 * 1024 * 1024; // 8 MB

/**
 * Injects the CSS for the loading overlay and progress bar.
 */
function injectUploadProgressStyles() {
    const styleId = 'upload-progress-styles';
    if (document.getElementById(styleId)) return;

    const styles = `
        .upload-progress-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 10000;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            color: white; font-family: 'Inter', sans-serif;
            backdrop-filter: blur(4px);
        }
        .upload-progress-spinner {
            width: 48px; height: 48px;
            border: 5px solid #fff;
            border-bottom-color: #3b82f6;
            border-radius: 50%;
            display: inline-block;
            animation: rotation 1s linear infinite;
        }
        @keyframes rotation { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .upload-progress-text {
            margin-top: 20px; font-size: 1.25rem; font-weight: 500;
        }
        .upload-progress-bar-container {
            width: 300px; height: 10px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 5px; margin-top: 15px; overflow: hidden;
        }
        .upload-progress-bar {
            width: 0%; height: 100%;
            background-color: #3b82f6;
            border-radius: 5px; transition: width 0.2s ease-in-out;
        }
    `;
    const styleSheet = document.createElement('style');
    styleSheet.id = styleId;
    styleSheet.type = 'text/css';
    styleSheet.innerText = styles;
    document.head.appendChild(styleSheet);
}

/**
 * Handles the submission of a specific form with file uploads, showing a progress bar.
 */
function handleFormWithUploadProgress(form) {
    const formData = new FormData(form);
    const xhr = new XMLHttpRequest();

    injectUploadProgressStyles();
    const overlay = document.createElement('div');
    overlay.className = 'upload-progress-overlay';
    overlay.innerHTML = `
        <div class="upload-progress-spinner"></div>
        <div class="upload-progress-text">Caricamento... 0%</div>
        <div class="upload-progress-bar-container">
            <div class="upload-progress-bar" style="width: 0%;"></div>
        </div>
    `;
    document.body.appendChild(overlay);

    const progressBar = overlay.querySelector('.upload-progress-bar');
    const progressText = overlay.querySelector('.upload-progress-text');

    xhr.upload.addEventListener('progress', (event) => {
        if (event.lengthComputable) {
            const percentComplete = Math.round((event.loaded / event.total) * 100);
            progressBar.style.width = percentComplete + '%';
            progressText.textContent = `Caricamento... ${percentComplete}%`;
        }
    });

    xhr.addEventListener('load', () => {
        overlay.remove();
        try {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                if (response.redirect_url) {
                    window.location.href = response.redirect_url;
                } else {
                    showNotification('Operazione completata!', 'success');
                }
            } else {
                showNotification(response.error || 'Errore sconosciuto.', 'error');
            }
        } catch (e) {
            showNotification('Errore nella risposta del server.', 'error');
        }
    });

    xhr.addEventListener('error', () => {
        overlay.remove();
        showNotification('Errore di rete.', 'error');
    });

    xhr.open('POST', form.action, true);
    xhr.send(formData);
}

/**
 * Initializes AJAX handling for forms marked with 'data-ajax-upload'.
 */
function initTargetedAjaxUpload() {
    const formsToHandle = document.querySelectorAll('form[data-ajax-upload="true"]');

    formsToHandle.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Always prevent default for these forms

            const fileInputs = this.querySelectorAll('input[type="file"]');
            let totalSize = 0;

            if (fileInputs.length > 0) {
                fileInputs.forEach(input => {
                    for (const file of input.files) {
                        totalSize += file.size;
                    }
                });
            }

            if (totalSize > MAX_TOTAL_UPLOAD_SIZE) {
                showNotification(`La dimensione totale dei file (${(totalSize / 1024 / 1024).toFixed(1)} MB) supera il limite di 8 MB.`, 'error');
                return;
            }

            handleFormWithUploadProgress(this);
        });
    });
}

// Add the new initializer to the main DOMContentLoaded event
document.addEventListener('DOMContentLoaded', initTargetedAjaxUpload);
