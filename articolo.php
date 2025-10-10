<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

if (!isset($_GET['slug'])) {
    header('Location: index.php');
    exit;
}

$slug = $_GET['slug'];
$db = new Database();
$article = $db->getArticleBySlug($slug);

if (!$article) {
    header('HTTP/1.0 404 Not Found');
    echo 'Articolo non trovato';
    exit;
}

$db->incrementArticleViews($article['id']);

// --- SEO Data Extraction with Fallbacks ---
$json_data = json_decode($article['json_data'] ?? '{}', true);
if (!is_array($json_data)) $json_data = [];

$seo_data = $json_data['seo'] ?? [];

$meta_title = !empty($seo_data['meta_title']) ? htmlspecialchars($seo_data['meta_title']) : htmlspecialchars($article['title']);
$meta_description = !empty($seo_data['meta_description']) ? htmlspecialchars($seo_data['meta_description']) : htmlspecialchars(substr(strip_tags($article['excerpt'] ?? ''), 0, 160));
$meta_keywords = !empty($seo_data['meta_keywords']) ? htmlspecialchars($seo_data['meta_keywords']) : '';
$canonical_url = SITE_URL . '/articolo.php?slug=' . urlencode($slug);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo $meta_title; ?> - Passione Calabria</title>
    <meta name="description" content="<?php echo $meta_description; ?>">
    <?php if ($meta_keywords): ?>
    <meta name="keywords" content="<?php echo $meta_keywords; ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?php echo $canonical_url; ?>" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <?php
    // --- Template Dispatcher ---
    $category_name = trim($article['category_name'] ?? '');

    $template_map = [
        'Hotel e Alloggi' => 'view_hotel.php',
        'Ristorazione' => 'view_ristorazione.php',
        'Stabilimenti Balneari' => 'view_stabilimenti.php',
        'Arte e Cultura' => 'view_arte_cultura.php',
        'Musei e Gallerie' => 'view_musei_gallerie.php',
        'Patrimonio Storico' => 'view_patrimonio_storico.php',
        'Piazze e Vie Storiche' => 'view_piazze_vie_storiche.php',
        'Siti Archeologici' => 'view_siti_archeologici.php',
        'Chiese e Santuari' => 'view_chiese_santuari.php',
        'Teatri e Anfiteatri' => 'view_teatri_anfiteatri.php',
        'Parchi e Aree Verdi' => 'view_parchi_aree_verdi.php',
        'Attività Sportive e Avventura' => 'view_attivita_sportive_avventura.php',
        'Itinerari Tematici' => 'view_itinerari_tematici.php',
        'Tour e Guide' => 'view_tour_guide.php',
        'Shopping e Artigianato' => 'view_shopping_artigianato.php',
        'Benessere e Relax' => 'view_benessere_relax.php',
        'Trasporti' => 'view_trasporti.php',
    ];

    $template_to_include = $template_map[$category_name] ?? 'view_default.php';

    if (file_exists('templates/' . $template_to_include)) {
        include 'templates/' . $template_to_include;
    } else {
        include 'templates/view_default.php';
    }
    ?>

    <?php include 'includes/footer.php'; ?>
    
    <?php include 'partials/user-upload-modal.php'; ?>
    
    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof UserUploadModal !== 'undefined') {
                UserUploadModal.init();
            }
        });
    </script>
</body>
</html>