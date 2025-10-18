<?php
// Includi la configurazione e il controllo di autenticazione
require_once '../includes/config.php';
require_once 'auth_check.php'; // Fondamentale per la sicurezza

// Includi l'header del pannello admin
include 'partials/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Gestione Attività</h1>

    <!-- I contenitori HTML verranno popolati dinamicamente da JavaScript -->
    <div id="admin-content-container">
        <!-- Qui verrà renderizzato il form e la lista delle attività -->
    </div>
</div>

<!--
    Includi uno script JS dedicato che conterrà la logica JS estratta dalla PWA.
    Questo script viene caricato SOLO per gli admin autenticati.
-->
<script src="/eventi/js/admin-logic.js"></script>
<script>
    // Inizializza la sezione specifica del pannello di amministrazione
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Admin !== 'undefined') {
            // Passiamo il container e la vista da caricare ('eventi', 'attivita', ecc.)
            Admin.init(document.getElementById('admin-content-container'), 'activities');
        }
    });
</script>

<?php
// Includi il footer del pannello admin
include 'partials/footer.php';
?>