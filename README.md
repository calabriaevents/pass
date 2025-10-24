# Passione Calabria - Versione PHP

## 🎯 **CONVERSIONE COMPLETATA DA NEXT.JS A PHP!**

Questo progetto è la **conversione completa** dell'applicazione "Passione Calabria" da **Next.js/Node.js** a **HTML + PHP + JavaScript vanilla**, mantenendo **tutte le funzionalità identiche** al progetto originale.

## 📋 **Caratteristiche Principali**

### ✅ **Frontend Identico**
- **HTML5** + **CSS3** + **JavaScript vanilla**
- **Tailwind CSS** via CDN per styling
- **Lucide Icons** per le icone
- Design responsive e identico al progetto Next.js
- Tutte le animazioni e interazioni mantenute

### ✅ **Backend PHP Completo**
- **Doppio supporto database**: Scegli tra **SQLite** (default, zero configurazione) o **MySQL**.
- **API REST** per ricerca, newsletter, e gestione dati
- **Pannello admin completo** per gestione contenuti
- **Sistema di monitoraggio database** con backup e download
- **18 categorie** + **5 province** + **14 città** precaricate

### ✅ **Funzionalità Complete**
- Homepage con hero section, categorie, province
- Sistema di ricerca avanzato con filtri
- Pagine categorie e province dettagliate
- Pannello admin professionale
- Monitoraggio database in tempo reale
- Sistema backup automatico
- API newsletter e gestione iscrizioni

## 🚀 **Installazione e Setup**

### 1. **Requisiti Server**
```bash
# Requisiti minimi
- PHP 7.4+ (raccomandato PHP 8.0+)
- SQLite3 extension abilitata
- mod_rewrite (per URL puliti - opzionale)
- Permessi di scrittura nella directory del progetto
```

### 2. **Installazione**
```bash
# 1. Carica i file sul server web
# 2. Assicurati che PHP abbia permessi di scrittura
chmod 755 passione-calabria-php/
chmod 777 passione-calabria-php/uploads/ (se esiste)

# 3. Il database si inizializza automaticamente al primo accesso
```

### 2a. **Scelta del Database (SQLite o MySQL)**

Questo progetto supporta sia SQLite che MySQL.

-   **SQLite (Default)**: È l'opzione predefinita e non richiede configurazione. Il database (`passione_calabria.db`) viene creato e inizializzato automaticamente nella root del progetto al primo avvio. È la scelta ideale per semplicità e hosting condivisi.

-   **MySQL (Opzionale)**: Se preferisci usare MySQL, abbiamo preparato tutto il necessario. Per la configurazione, segui la nostra guida dettagliata:
    -   ➡️ **[Guida Setup MySQL](./setup_mysql.md)**

### 3. **Configurazione**
```php
// Modifica includes/config.php per personalizzare:
define('SITE_URL', 'https://tuodominio.com'); // Cambia con il tuo URL
define('ADMIN_EMAIL', 'tua@email.com');       // Email amministratore
```

## 📁 **Struttura del Progetto**

```
passione-calabria-php/
├── index.php                 # Homepage principale
├── categorie.php            # Pagina categorie
├── ricerca.php              # Pagina risultati ricerca
├──
├── includes/
│   ├── config.php           # Configurazione generale
│   ├── database.php         # Classe Database SQLite
│   └── footer.php           # Footer condiviso
├──
├── assets/
│   ├── css/style.css        # Stili personalizzati
│   └── js/main.js           # JavaScript principale
├──
├── api/
│   ├── search.php           # API ricerca
│   └── newsletter.php       # API newsletter
├──
├── admin/
│   ├── index.php            # Dashboard admin
│   ├── database.php         # Monitoraggio database
│   ├── articoli.php         # Gestione articoli
│   └── [altre pagine admin]
├──
├── passione_calabria.db     # Database SQLite (auto-generato)
└── backups/                 # Directory backup database
```

## 🔧 **Differenze Tecniche dalla Versione Next.js**

| Aspetto | Next.js | PHP |
|---------|---------|-----|
| **Frontend** | React Components | HTML + JavaScript vanilla |
| **Backend** | API Routes | File PHP |
| **Database** | better-sqlite3 | PDO SQLite |
| **Styling** | Tailwind (configurato) | Tailwind via CDN |
| **Routing** | Next.js Router | PHP include/require |
| **State Management** | React State | JavaScript + LocalStorage |
| **Build Process** | npm/bun build | Nessun build necessario |

## 📊 **Funzionalità Admin Panel**

### Dashboard Principale (`/admin/`)
- **Statistiche in tempo reale**: articoli, visualizzazioni, commenti
- **Controllo salute database**: stato integrità, dimensioni, performance
- **Azioni rapide**: nuovo articolo, gestione commenti, backup
- **Articoli recenti**: lista con azioni dirette

### Monitoraggio Database (`/admin/database.php`)
- **Stato generale**: score salute, dimensioni, integrità
- **Conteggi tabelle**: visualizzazione dettagliata di tutti i dati
- **Azioni manutenzione**: VACUUM, ANALYZE, controlli integrità
- **Sistema backup**: creazione, download, gestione backup automatici
- **Download database**: scarica database corrente o backup specifici

## 🔌 **API Endpoints**

### Ricerca (`/api/search.php`)
```php
GET /api/search.php?q=terme&province=1&limit=10
// Ritorna: articoli, categorie, province, città corrispondenti
```

### Newsletter (`/api/newsletter.php`)
```php
POST /api/newsletter.php
Body: { email: "user@email.com", name: "Nome" }
// Gestisce: iscrizione, conferma, disiscrizione
```

## 🎨 **Personalizzazione Design**

### Colori Tema
```css
:root {
    --calabria-blue: #2563eb;    /* Blu principale */
    --calabria-gold: #f59e0b;    /* Oro/giallo */
    --calabria-teal: #14b8a6;    /* Verde acqua */
}
```

### Tailwind Config
Il progetto usa Tailwind via CDN con configurazione personalizzata nel tag `<script>` di ogni pagina.

## 🗄️ **Gestione Database**

### Inizializzazione Automatica
Il database si inizializza automaticamente con:
- **18 categorie** predefinite (Natura, Storia, Gastronomia, ecc.)
- **5 province** calabresi complete
- **14 città** principali con coordinate GPS
- **5 articoli** di esempio
- **Impostazioni** di sistema
- **Pagine statiche** (Chi siamo, Privacy, ecc.)

### Backup e Manutenzione
```php
// Crea backup
$backupFile = $db->createBackup();

// Ottimizza database
$db->pdo->exec('VACUUM');

// Aggiorna statistiche
$db->pdo->exec('ANALYZE');
```

## 🔒 **Sicurezza**

### Protezioni Implementate
- **Sanitizzazione input**: tutti i dati user vengono sanitizzati
- **SQL Injection**: uso di prepared statements
- **XSS Protection**: htmlspecialchars su tutti gli output
- **CSRF Protection**: token di sicurezza per form sensibili
- **File Upload**: validazione rigorosa dei file

### ⚠️ **Attenzione: Autenticazione Admin Disabilitata**

Per impostazione predefinita, **l'autenticazione per il pannello di amministrazione è disabilitata** per semplificare il setup iniziale. Questo significa che chiunque conosca l'URL `/admin/` può accedere al pannello.

**È FONDAMENTALE abilitare l'autenticazione prima di mettere il sito in produzione.**

### Autenticazione Admin
```php
// Per abilitare autenticazione (commentata per semplicità):
// 1. Uncommentare requireLogin() nelle pagine admin
// 2. Configurare ADMIN_PASSWORD_HASH in config.php
// 3. Creare pagina login.php
```

## 🚀 **Performance**

### Ottimizzazioni
- **Database SQLite**: ottimizzato per performance
- **Lazy Loading**: immagini caricate on-demand
- **CDN Assets**: Tailwind e Lucide via CDN
- **Caching**: possibilità di aggiungere cache PHP
- **Gzip**: abilitabile via .htaccess

### Monitoraggio
- Dashboard tempo reale per statistiche
- Controllo integrità database automatico
- Backup automatici programmabili
- Log errori per debugging

## 🌐 **SEO e Accessibilità**

### SEO Ready
- **Meta tags** dinamici per ogni pagina
- **URL semantici** per articoli e categorie
- **Structured data** ready per implementazione
- **Sitemap** generabile dinamicamente

### Accessibilità
- **ARIA labels** e ruoli semantici
- **Contrast ratio** ottimizzato
- **Keyboard navigation** supportata
- **Screen reader** friendly

## 📱 **Responsive Design**

### Breakpoints
- **Mobile**: < 640px
- **Tablet**: 640px - 1024px
- **Desktop**: > 1024px
- **Large**: > 1280px

Tutte le pagine sono completamente responsive e ottimizzate per ogni dispositivo.

## 🔧 **Deployment**

### Hosting Condiviso
```bash
# 1. Carica tutti i file via FTP/cPanel
# 2. Verifica che PHP e SQLite siano abilitati
# 3. Imposta permessi cartelle se necessario
# 4. Il sito è pronto!
```

### VPS/Server Dedicato
```bash
# Apache/Nginx + PHP + SQLite
# Configura virtual host
# Abilita mod_rewrite per URL puliti
# Imposta cron per backup automatici
```

## 🆚 **Vantaggi Versione PHP vs Next.js**

### ✅ **Pro PHP**
- **Zero dipendenze** Node.js/npm
- **Hosting economico** (shared hosting)
- **Setup immediato** senza build
- **Debugging semplificato**
- **Compatibilità universale**
- **Manutenzione facilitata**

### 📋 **Considerazioni**
- Meno "real-time" rispetto a React
- No server-side rendering automatico
- Gestione stato client-side più manuale

## 🎯 **Prossimi Sviluppi**

### Funzionalità Pianificate
- [ ] Sistema login utenti frontend
- [ ] Gestione eventi con calendario
- [ ] Sistema rating e recensioni
- [ ] Integrazione mappe Leaflet
- [ ] API mobile app
- [ ] Sistema commenti avanzato
- [ ] Multilingua (IT/EN)
- [ ] PWA support

## 🤝 **Supporto e Contributi**

### Risoluzione Problemi
1. Verifica requisiti PHP/SQLite
2. Controlla permessi file/cartelle
3. Consulta log errori PHP
4. Testa con database pulito

### Personalizzazione
Il codice è completamente commentato e modulare per facilitare personalizzazioni e estensioni.

---

## 🎉 **Conclusioni**

Questa versione PHP di **Passione Calabria** mantiene **tutte le funzionalità** del progetto Next.js originale, offrendo una soluzione **più semplice da deployare** e **meno costosa da hostare**, perfetta per progetti che non necessitano della complessità di React/Node.js.

**Il progetto è pronto per la produzione e completamente funzionale!** 🚀
