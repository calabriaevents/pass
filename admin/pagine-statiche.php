<?php
require_once '../includes/config.php';
require_once 'auth_check.php';
require_once '../includes/database_mysql.php';

$db = new Database();

// Definisci le pagine statiche che possono essere modificate
$staticPages = [
    'chi-siamo.php' => [
        'label' => 'Chi Siamo',
        'description' => 'Modifica il contenuto della pagina "Chi Siamo".'
    ],
    'contatti.php' => [
        'label' => 'Contatti',
        'description' => 'Modifica le informazioni di contatto e la mappa.'
    ],
    'privacy-policy.php' => [
        'label' => 'Privacy Policy',
        'description' => 'Aggiorna il testo della Privacy Policy.'
    ],
    'termini-servizio.php' => [
        'label' => 'Termini di Servizio',
        'description' => 'Aggiorna i termini e le condizioni del servizio.'
    ],
];

$success_message = '';
$error_message = '';
$selected_page = $_GET['page'] ?? null;
$content = '';

// Se una pagina è stata selezionata, leggi il suo contenuto
if ($selected_page && isset($staticPages[$selected_page])) {
    $filePath = dirname(__DIR__) . '/' . $selected_page;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
    } else {
        $error_message = "File non trovato: {$selected_page}";
    }
}

// Gestione del salvataggio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content']) && isset($_POST['page_filename'])) {
    $page_filename = $_POST['page_filename'];
    $new_content = $_POST['content'];

    if (isset($staticPages[$page_filename])) {
        $filePath = dirname(__DIR__) . '/' . $page_filename;
        if (file_put_contents($filePath, $new_content) !== false) {
            $success_message = "Pagina '{$staticPages[$page_filename]['label']}' aggiornata con successo!";
            // Ricarica il contenuto per visualizzarlo aggiornato nell'editor
            $content = $new_content;
            $selected_page = $page_filename;
        } else {
            $error_message = "Errore durante il salvataggio del file.";
        }
    } else {
        $error_message = "Pagina non valida.";
    }
}

include 'partials/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestione Pagine Statiche</h1>
    </div>

    <?php if ($success_message): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Seleziona una Pagina</h6>
                </div>
                <div class="card-body">
                    <p>Seleziona una pagina dall'elenco per modificarne il contenuto.</p>
                    <ul class="list-group">
                        <?php foreach ($staticPages as $filename => $details): ?>
                            <a href="?page=<?php echo $filename; ?>" class="list-group-item list-group-item-action <?php echo ($selected_page === $filename) ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($details['label']); ?>
                                <small class="d-block text-muted"><?php echo htmlspecialchars($details['description']); ?></small>
                            </a>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <?php if ($selected_page && isset($staticPages[$selected_page])): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Modifica Contenuto: <?php echo htmlspecialchars($staticPages[$selected_page]['label']); ?></h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="page_filename" value="<?php echo $selected_page; ?>">
                        <div class="form-group">
                            <textarea name="content" id="editor"><?php echo htmlspecialchars($content); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="card shadow mb-4">
                <div class="card-body">
                    <p class="text-center">Seleziona una pagina dalla lista a sinistra per iniziare a modificare.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/35.4.0/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#editor'))
        .catch(error => {
            console.error(error);
        });
</script>

<?php include 'partials/footer.php'; ?>