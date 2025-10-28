<?php
require_once '../includes/config.php';
require_once 'auth_check.php';
require_once '../includes/database_mysql.php';

$pages = [
    'chi-siamo' => [
        'title' => 'Chi Siamo',
        'file' => '../partials/static_content/chi-siamo.html'
    ],
    'contatti' => [
        'title' => 'Contatti',
        'file' => '../partials/static_content/contatti.html'
    ]
];

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['page'], $_POST['content']) && isset($pages[$_POST['page']])) {
        $pageKey = $_POST['page'];
        $content = $_POST['content'];
        $filePath = $pages[$pageKey]['file'];

        if (file_put_contents($filePath, $content) !== false) {
            $success_message = 'Contenuto della pagina "' . $pages[$pageKey]['title'] . '" aggiornato con successo.';
        } else {
            $error_message = 'Errore durante il salvataggio del file.';
        }
    } else {
        $error_message = 'Dati non validi.';
    }
}

$active_page = $_GET['page'] ?? 'chi-siamo';
if (!isset($pages[$active_page])) {
    $active_page = 'chi-siamo';
}

$current_content = '';
if (file_exists($pages[$active_page]['file'])) {
    $current_content = file_get_contents($pages[$active_page]['file']);
}

include 'partials/header.php';
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Gestione Pagine Statiche</h1>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-3">
            <div class="list-group">
                <?php foreach ($pages as $key => $page): ?>
                    <a href="?page=<?php echo $key; ?>" class="list-group-item list-group-item-action <?php echo ($active_page === $key) ? 'active' : ''; ?>">
                        <?php echo $page['title']; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Modifica <?php echo $pages[$active_page]['title']; ?></h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="page" value="<?php echo $active_page; ?>">
                        <div class="form-group">
                            <textarea name="content" class="form-control" rows="15"><?php echo htmlspecialchars($current_content); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
