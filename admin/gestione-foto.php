<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';
require_once 'auth_check.php';

$db = new Database();

// Filtri
$province_id = isset($_GET['province_id']) ? (int)$_GET['province_id'] : null;
$city_id = isset($_GET['city_id']) ? (int)$_GET['city_id'] : null;
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Carica dati per i filtri
$provinces = $db->getProvinces();
$categories = $db->getCategories();
$cities = $province_id ? $db->getCitiesByProvince($province_id) : [];

// Carica tutte le immagini con i filtri applicati
$allImages = $db->getAllImagesWithDetails($province_id, $city_id, $category_id, $search_term);

include 'partials/header.php';
?>

<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8">
        <div>
            <h2 class="text-2xl font-semibold leading-tight">Gestione Globale delle Foto</h2>
        </div>

        <form method="GET" class="my-4 p-4 bg-white rounded-lg shadow">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <select name="province_id" onchange="this.form.submit()" class="form-select">
                    <option value="">Tutte le Province</option>
                    <?php foreach ($provinces as $province): ?>
                        <option value="<?php echo $province['id']; ?>" <?php if ($province_id == $province['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($province['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="city_id" class="form-select">
                    <option value="">Tutte le Città</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?php echo $city['id']; ?>" <?php if ($city_id == $city['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($city['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="category_id" class="form-select">
                    <option value="">Tutte le Categorie</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php if ($category_id == $category['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="text" name="search" placeholder="Cerca per percorso o descrizione..." value="<?php echo htmlspecialchars($search_term); ?>" class="form-input">
            </div>
            <div class="mt-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Filtra
                </button>
                <a href="gestione-foto.php" class="ml-2 text-gray-600">Resetta filtri</a>
            </div>
        </form>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php if (empty($allImages)): ?>
                <p class="text-center col-span-full">Nessuna immagine trovata con i filtri selezionati.</p>
            <?php else: ?>
                <?php foreach ($allImages as $image): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden" id="image-card-<?php echo md5($image['image_path']); ?>">
                        <img src="../image-loader.php?path=<?php echo urlencode(str_replace(['uploads_protected/', 'uploads/'], '', $image['image_path'])); ?>" alt="<?php echo htmlspecialchars($image['description']); ?>" class="w-full h-32 object-cover">
                        <div class="p-2 text-xs">
                            <p><strong>Fonte:</strong> <?php echo htmlspecialchars($image['source']); ?></p>
                            <?php if ($image['city_name']): ?>
                                <p><strong>Città:</strong> <?php echo htmlspecialchars($image['city_name']); ?></p>
                            <?php endif; ?>
                            <?php if ($image['article_title']): ?>
                                <p><strong>Articolo:</strong> <?php echo htmlspecialchars($image['article_title']); ?></p>
                            <?php endif; ?>
                            <button
                                onclick="deleteImage('<?php echo htmlspecialchars(addslashes($image['image_path'])); ?>', '<?php echo $image['source']; ?>', <?php echo (int)$image['source_id']; ?>, '<?php echo md5($image['image_path']); ?>')"
                                class="mt-2 w-full bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs">
                                Elimina
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteImage(path, source, sourceId, cardId) {
    if (!confirm('Sei sicuro di voler eliminare questa immagine? L\'azione è irreversibile.')) {
        return;
    }

    fetch('../api/delete_image.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            image_path: path,
            source: source,
            source_id: sourceId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const card = document.getElementById('image-card-' + cardId);
            if (card) {
                card.remove();
            }
            alert('Immagine eliminata con successo!');
        } else {
            alert('Errore: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        alert('Si è verificato un errore di rete.');
    });
}
</script>

<?php include 'partials/footer.php'; ?>