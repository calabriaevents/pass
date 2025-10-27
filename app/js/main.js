document.addEventListener('DOMContentLoaded', () => {
    const API_URL = '../api/homepage.php';

    const heroSection = document.getElementById('hero-section');
    const categoriesGrid = document.getElementById('categories-grid');
    const articlesList = document.getElementById('articles-list');

    // Funzione per recuperare i dati dall'API
    async function fetchData() {
        try {
            const response = await fetch(API_URL);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();

            // Popola le sezioni una volta ricevuti i dati
            renderHero(data.hero);
            renderCategories(data.categories);
            renderArticles(data.articles);

        } catch (error) {
            console.error("Errore nel recuperare i dati:", error);
            displayError();
        }
    }

    // Funzione per renderizzare la sezione Hero
    function renderHero(hero) {
        if (!hero) return;

        // Simulo un'immagine di default se non presente
        const imageUrl = hero.image ? `../image-loader.php?src=${hero.image}` : 'https://via.placeholder.com/600x250.png?text=Passione+Calabria';

        heroSection.innerHTML = `
            <img src="${imageUrl}" alt="${hero.title}">
            <div class="hero-content">
                <h1>${hero.title}</h1>
                <p>${hero.subtitle}</p>
            </div>
        `;
    }

    // Funzione per renderizzare le categorie
    function renderCategories(categories) {
        if (!categories || categories.length === 0) {
            categoriesGrid.innerHTML = '<p>Nessuna categoria trovata.</p>';
            return;
        }

        categoriesGrid.innerHTML = categories.map(category => `
            <div class="category-card">
                <span class="icon">${category.icon || '🏞️'}</span>
                <span>${category.name}</span>
            </div>
        `).join('');
    }

    // Funzione per renderizzare gli articoli
    function renderArticles(articles) {
        if (!articles || articles.length === 0) {
            articlesList.innerHTML = '<p>Nessun articolo trovato.</p>';
            return;
        }

        articlesList.innerHTML = articles.map(article => {
            const imageUrl = article.featured_image ? `../image-loader.php?src=${article.featured_image}` : 'https://via.placeholder.com/80x80.png?text=IMG';
            return `
                <div class="article-card">
                    <img src="${imageUrl}" alt="${article.title}">
                    <div class="article-info">
                        <h3>${article.title}</h3>
                        <p>${article.nome_categoria}</p>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Funzione per mostrare un messaggio di errore generico
    function displayError() {
        heroSection.innerHTML = '<p>Impossibile caricare i contenuti. Riprova più tardi.</p>';
        categoriesGrid.innerHTML = '';
        articlesList.innerHTML = '';
    }

    // Avvia il recupero dei dati
    fetchData();
});
