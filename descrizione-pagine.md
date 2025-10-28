# Descrizione Dettagliata delle Pagine Utente

Questo documento descrive la struttura e il layout di tutte le pagine accessibili agli utenti del sito "Passione Calabria".

## Componenti Comuni

Questi elementi sono presenti in quasi tutte le pagine del sito per garantire una navigazione coerente.

### Header (`includes/header.php`)

L'header è a sfondo gradiente (blu, azzurro, giallo) e si divide in due parti principali:

1.  **Top Bar (Barra Superiore):**
    *   Sfondo nero semi-trasparente.
    *   A sinistra: icona di una puntina e testo "Scopri la Calabria".
    *   Al centro (solo su schermi medi e grandi): testo "Benvenuto in Passione Calabria".

2.  **Navigation (Barra di Navigazione Principale):**
    *   **A sinistra:** Logo del sito, composto da un cerchio con le lettere "PC" e il nome "Passione Calabria" con il payoff "La tua guida alla Calabria".
    *   **Al centro (solo su schermi grandi):** Menu di navigazione principale con le voci:
        *   Home
        *   Città
        *   Mappa
        *   Contatti
        *   Un pulsante in evidenza "Iscrivi la tua attività".
    *   **A destra (solo su schermi grandi):**
        *   Se l'utente **non è loggato**: Un pulsante "Area Attività" con un'icona a forma di utente.
        *   Se l'utente **è loggato**: Un menu a tendina che mostra il nome dell'utente, la sua email, lo stato dell'attività e i link a "Dashboard" e "Logout".
    *   **Mobile (schermi piccoli):** A destra sono presenti due icone: un'icona utente per l'accesso all'area attività (o alla dashboard se loggato) e un'icona "hamburger" (menu) che apre un menu a tendina a schermo intero con tutti i link di navigazione.

### Footer (`includes/footer.php`)

Il footer ha uno sfondo scuro (grigio/nero) ed è diviso in quattro colonne principali:

1.  **Prima Colonna (About):**
    *   Logo e nome del sito.
    *   Breve descrizione del portale.
    *   Icone dei social media (Facebook, Instagram, Twitter, YouTube).
2.  **Seconda Colonna (Esplora):**
    *   Link rapidi a sezioni del sito come "Tutte le Categorie", "Le Province", "Mappa Interattiva" e "Tutti gli Articoli".
3.  **Terza Colonna (Informazioni):**
    *   Link a pagine di servizio come "Chi Siamo", "Collabora con Noi", "Suggerisci un Luogo", "Contatti" e "Privacy Policy".
4.  **Quarta Colonna (Contatti):**
    *   Informazioni di contatto: indirizzo, email, numero di telefono.
    *   Un modulo per l'iscrizione alla newsletter.

Sotto le quattro colonne, c'è una **barra inferiore** con il testo del copyright e i link a "Termini di Servizio" e "Privacy". È presente anche un pulsante "Torna in Alto" flottante che appare in basso a destra durante lo scorrimento della pagina.

---

## Pagine Principali

### `index.php` (Homepage)

La homepage è la pagina più ricca di contenuti e sezioni:

1.  **Hero Section:**
    *   Immagine di sfondo a tutto schermo con un gradiente semi-trasparente.
    *   Titolo principale, sottotitolo e un paragrafo descrittivo al centro.
    *   Due pulsanti di invito all'azione ("Scopri la Calabria" e "Visualizza Mappa").
    *   Un **widget di ricerca** in primo piano con campi per cercare per parola chiave, provincia e un pulsante di ricerca.
2.  **Events Section:**
    *   Titolo e descrizione che invitano a scoprire gli eventi tramite l'app.
    *   Badge per il download dall'App Store e Google Play.
    *   Pulsanti "Vai all'App" e "Suggerisci Evento".
3.  **Categories Section:**
    *   Titolo e descrizione.
    *   Una griglia di "card" per ogni categoria. Ogni card contiene:
        *   Icona e nome della categoria.
        *   Un mini-slider interno che mostra alcuni articoli di quella categoria con immagine, titolo e data.
        *   Pulsante per esplorare la categoria.
    *   Un pulsante finale per "Vedere Tutte le Categorie".
4.  **Provinces Section:**
    *   Titolo e descrizione.
    *   Una griglia di card per le province principali, ognuna con un'immagine di copertina, nome, descrizione, alcune località principali e un link per esplorare la provincia.
5.  **Map Section:**
    *   Titolo e descrizione.
    *   Una mappa interattiva incorporata che mostra la posizione di alcuni articoli in evidenza.
    *   Un link per visualizzare la mappa completa.
6.  **CTA (Call to Action) Section:**
    *   Sfondo a gradiente con un titolo e una descrizione che invitano gli utenti a collaborare.
    *   Pulsanti "Collabora con Noi" e "Suggerisci un Luogo".
7.  **Newsletter Section:**
    *   Titolo, descrizione e un modulo per l'iscrizione alla newsletter.
    *   Icone social per seguire il sito.

### `articoli.php`

Questa pagina mostra un elenco di tutti gli articoli pubblicati.

*   **Titolo:** "Tutti gli Articoli" in grande al centro.
*   **Contenuto:** Una griglia di card, dove ogni card rappresenta un articolo e contiene:
    *   Immagine in evidenza (se presente).
    *   Titolo dell'articolo.
    *   Breve estratto del contenuto (excerpt).
    *   Link "Leggi di più" che porta alla pagina di dettaglio.

### `articolo.php` e `templates/`

Questa pagina visualizza il singolo articolo. La sua struttura cambia in base alla categoria dell'articolo, caricando un template diverso.

*   **Template di Default (`view_default.php`):**
    *   **Hero Section:** Un'immagine di copertina a tutta larghezza con il titolo dell'attività in sovrimpressione.
    *   **Contenuto Principale (colonna sinistra):**
        *   Sezione "Descrizione" con il testo completo dell'articolo.
        *   Sezione "Galleria" con una griglia di immagini.
        *   Un box che invita l'utente a caricare una propria foto del luogo.
    *   **Sidebar (colonna destra):**
        *   Box "Informazioni e Contatti" con indirizzo, telefono, email e sito web.
        *   Una mappa di Google Maps incorporata.
        *   Una sezione per le recensioni (se presenti).

*   **Template Hotel (`view_hotel.php`):**
    *   Simile al default, ma con sezioni aggiuntive specifiche per gli hotel:
        *   **Servizi:** Una lista puntata dei servizi offerti (es. Wi-Fi, parcheggio).
        *   **Camere e Prezzi:** Un elenco delle tipologie di camere con i relativi prezzi e descrizioni.
        *   Il logo dell'attività è mostrato in evidenza nella hero section.

### `categoria.php`

Mostra tutti gli articoli appartenenti a una singola categoria.

*   **Hero Categoria:** Una sezione colorata con l'icona, il nome e la descrizione della categoria.
*   **Contenuto:** Una griglia di card degli articoli, simile a quella di `articoli.php`, ma con un layout più ricco che include la provincia, le visite e la data di pubblicazione.
*   **Sezione "Esplora Altre Categorie":** In fondo alla pagina, vengono mostrate alcune altre categorie per incoraggiare la navigazione.

### `citta.php`

Pagina che elenca tutte le città inserite nel portale.

*   **Hero:** Titolo "Città della Calabria" e descrizione.
*   **Filtri di Ricerca:** Una sezione che permette di cercare una città per nome o di filtrarle per provincia.
*   **Griglia Città:** Una griglia di card, dove ogni card rappresenta una città e mostra:
    *   Immagine di copertina.
    *   Nome della città e della provincia.
    *   Numero di articoli associati.
    *   Link "Esplora".
*   È presente un selettore per visualizzare i risultati come **griglia** o come **lista**.

### `citta-dettaglio.php`

Pagina dedicata a una singola città.

*   **Hero Section Cinematografico:** Immagine di copertina a tutta altezza con il nome della città e statistiche (numero di contenuti, foto, recensioni).
*   **Contenuto Principale (colonna sinistra):**
    *   **Sezione App Eventi:** Invito a scaricare l'app per scoprire gli eventi della città.
    *   **Galleria Unificata:** Mostra sia le foto caricate dallo staff sia quelle caricate dagli utenti, con il nome dell'autore visibile al passaggio del mouse.
    *   **Cosa Fare:** Elenco di tutti gli articoli relativi a quella città, raggruppati per categoria.
    *   **Sezione Recensioni:** Un modulo per lasciare una recensione e la lista delle recensioni approvate.
*   **Sidebar (colonna destra):**
    *   Mappa interattiva della città.
    *   Box con "Info Utili" (provincia, coordinate, etc.).
    *   Invito a suggerire un luogo.

### `province.php`

Mostra l'elenco di tutte le province.

*   **Titolo:** "Province della Calabria".
*   **Contenuto:** Una griglia di card, una per ogni provincia, con immagine, nome, descrizione e link per esplorare i dettagli.

### `provincia.php`

Pagina dedicata a una singola provincia.

*   **Hero Provincia:** Simile a quello di `categoria.php`, con nome, descrizione e statistiche.
*   **Invito a Suggerire:** Un box che invita l'utente a suggerire un luogo in quella provincia.
*   **Articoli della Provincia:** Una griglia con tutti gli articoli appartenenti a quella provincia.
*   **Mappa Interattiva:** Una mappa che mostra la posizione di tutti gli articoli della provincia.
*   **Altre Province:** In fondo, una sezione per navigare verso le altre province.

### `mappa.php`

Pagina a tutta larghezza che mostra la mappa interattiva.

*   **Titolo:** "Mappa della Calabria".
*   **Contenuto:** Una grande mappa Leaflet che occupa la maggior parte della pagina, con dei marker (puntine) per ogni articolo che ha coordinate geografiche. Cliccando su un marker si apre un popup con le informazioni base dell'articolo e un link per leggerlo.

---

## Pagine di Servizio e Utente

### `contatti.php`

*   **Titolo:** "Contatti".
*   **Contenuto:** Layout a due colonne:
    *   **Colonna Sinistra:** Mappa di Google Maps con la sede e informazioni di contatto (indirizzo, email, telefono).
    *   **Colonna Destra:** Un modulo di contatto per inviare un messaggio.

### `chi-siamo.php`

*   **Titolo:** "Chi Siamo".
*   **Contenuto:** Una pagina semplice con un box centrale che contiene il testo descrittivo del progetto, caricato da un file statico.

### `user-auth.php`

Pagina per il login delle attività.

*   **Layout:** Sfondo gradiente con un box centrale "effetto vetro".
*   **Contenuto:**
    *   Logo e titolo "Accesso Area Attività".
    *   Un box informativo che spiega come ottenere le credenziali.
    *   Modulo di login con campi per email e password.
    *   Link e informazioni per chi non ha ancora un account.

### `user-dashboard.php`

Pannello di controllo per le attività registrate.

*   **Layout:** Struttura a due colonne su schermi grandi.
*   **Colonna Principale (sinistra):**
    *   Box "Stato Crediti" che mostra il saldo dei crediti a consumo.
    *   Tabella con lo "Storico Utilizzi Crediti".
    *   Box "Stato Attività" con i dati anagrafici dell'attività.
    *   Box "Abbonamento Attuale" con i dettagli del piano attivo e le date di inizio/scadenza.
*   **Colonna Secondaria (destra):**
    *   Box "Azioni Rapide" (link al sito, modifica profilo).
    *   Box "Piani e Pacchetti" per effettuare upgrade/downgrade o acquistare crediti.
    *   Box "Assistenza" con i contatti di supporto.

### `user-profile.php`

Pagina per la modifica dei dati dell'attività e dell'utente.

*   **Titolo:** "Modifica Profilo".
*   **Contenuto:** Un unico grande modulo diviso in sezioni:
    *   **Dati Account:** Per modificare nome e email del titolare.
    *   **Dati Attività:** Per modificare nome, telefono, sito, indirizzo e descrizione dell'attività.
    *   **Modifica Password:** Campi per inserire una nuova password.
    *   Pulsante "Salva Modifiche" in fondo.

### `iscrizione-attivita.php`

Pagina per la registrazione di nuove attività.

*   **Hero Section:** Titolo e descrizione che spiegano i vantaggi dell'iscrizione.
*   **Piani di Abbonamento:** Griglia di card che illustrano i diversi pacchetti di abbonamento (es. Gratuito, Business), con elenco delle funzionalità e pulsante di selezione.
*   **Pacchetti a Consumo:** Griglia simile per i pacchetti di crediti.
*   **Benefits Section:** Icone e testi che riassumono i vantaggi (visibilità, target, etc.).
*   **FAQ:** Sezione con domande e risposte frequenti.

### `collabora.php` e `suggerisci.php`

Pagine semplici con un titolo e un modulo di contatto centrale per permettere agli utenti di inviare, rispettivamente, proposte di collaborazione o suggerimenti su nuovi luoghi da inserire.

---

## Pagine Legali

### `termini-servizio.php`, `privacy-policy.php`, `cookie-policy.php`

Queste tre pagine hanno una struttura identica:

*   **Titolo:** Titolo della policy (es. "Termini di Servizio").
*   **Contenuto:** Un box centrale a colonna singola che mostra il testo legale, caricato da un file statico.

---

## Altre Pagine

### `ricerca.php`

Pagina che mostra i risultati di una ricerca effettuata dall'utente.

*   **Header di Ricerca:** Contiene il termine cercato e un modulo di ricerca completo per affinare i risultati con filtri per categoria e provincia.
*   **Contenuto:**
    *   Se ci sono risultati, viene mostrata una griglia di articoli corrispondenti alla ricerca, simile a quella di `articoli.php`.
    *   Se non ci sono risultati, viene mostrato un messaggio di "Nessun risultato trovato" con link per esplorare le categorie o le province.
    *   In fondo alla pagina è presente la paginazione se i risultati sono più di una pagina.
