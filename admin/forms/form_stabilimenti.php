<?php
// This file is included by articoli.php.
// Variables available: $db, $article, $provinces, $cities
// Custom fields are stored in the 'json_data' column.
$json_data = isset($article['json_data']) ? json_decode($article['json_data'], true) : [];
if (!is_array($json_data)) $json_data = [];
?>

<!-- Basic Info -->
<div class="mb-4">
    <label for="title" class="block text-gray-700 font-bold mb-2">Nome Stabilimento Balneare</label>
    <input type="text" name="title" id="title" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($article['title'] ?? ''); ?>" required>
</div>

<div class="mb-4">
    <label for="slug" class="block text-gray-700 font-bold mb-2">Slug (URL)</label>
    <input type="text" name="slug" id="slug" class="w-full px-3 py-2 border rounded-lg bg-gray-100" value="<?php echo htmlspecialchars($article['slug'] ?? ''); ?>" required>
</div>

<!-- Custom Fields for Beach Resorts -->
<div class="mt-6 p-6 bg-cyan-50 rounded-lg border border-cyan-200">
    <h3 class="text-lg font-semibold mb-4 text-cyan-800">🏖️ Dettagli Stabilimento</h3>

    <div class="mb-4">
        <label for="p_iva" class="block text-gray-700 font-bold mb-2">Partita IVA (Obbligatorio)</label>
        <input type="text" name="json_data[p_iva]" id="p_iva" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($json_data['p_iva'] ?? ''); ?>" required>
    </div>

    <div class="mb-4">
        <label for="tariffe" class="block text-gray-700 font-bold mb-2">Tariffe</label>
        <textarea name="json_data[tariffe]" id="tariffe" rows="4" class="w-full px-3 py-2 border rounded-lg font-mono text-sm"><?php echo htmlspecialchars($json_data['tariffe'] ?? ''); ?></textarea>
        <p class="text-xs text-gray-500 mt-1">Una tariffa per riga. Esempio: <strong>Giornaliero (1 Ombrellone, 2 Lettini): 25€</strong></p>
    </div>
</div>

<!-- Services -->
<div class="mt-6 p-6 bg-gray-50 rounded-lg border">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">🔧 Servizi Offerti</h3>
    <?php
        $predefined_services = ['Noleggio Ombrelloni', 'Noleggio Lettini', 'Bar', 'Ristorante', 'Docce', 'Area Giochi', 'Cabine'];
        $current_services = $json_data['services'] ?? ['predefined' => [], 'custom' => ''];
        if(!is_array($current_services)) $current_services = ['predefined' => [], 'custom' => ''];
        if(!isset($current_services['predefined'])) $current_services['predefined'] = [];
    ?>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
        <?php foreach($predefined_services as $service): ?>
        <label class="flex items-center space-x-2">
            <input type="checkbox" name="json_data[services][predefined][]" value="<?php echo $service; ?>" <?php echo in_array($service, $current_services['predefined']) ? 'checked' : ''; ?> class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span><?php echo $service; ?></span>
        </label>
        <?php endforeach; ?>
    </div>
    <div>
        <label for="custom_services" class="block text-gray-700 font-bold mb-2">Altri Servizi (separati da virgola)</label>
        <input type="text" name="json_data[services][custom]" id="custom_services" class="w-full px-3 py-2 border rounded-lg" placeholder="Es. Pedalò, Animazione, ..." value="<?php echo htmlspecialchars($current_services['custom'] ?? ''); ?>">
    </div>
</div>


<!-- Location and Contacts -->
<div class="mt-6 p-6 bg-gray-50 rounded-lg border">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">📍 Localizzazione e Contatti</h3>
     <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
        <div>
            <label for="province_id" class="block text-gray-700 font-bold mb-2">Provincia</label>
            <select name="province_id" id="province_id" class="w-full px-3 py-2 border rounded-lg">
                <option value="">Nessuna</option>
                <?php foreach ($provinces as $province): ?>
                <option value="<?php echo $province['id']; ?>" <?php if (isset($article) && $article['province_id'] == $province['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($province['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="city_autocomplete" class="block text-gray-700 font-bold mb-2">Città</label>
            <input type="text" id="city_autocomplete" class="w-full px-3 py-2 border rounded-lg" placeholder="Inizia a digitare il nome della città..." value="<?php echo isset($article) && $article['city_id'] ? htmlspecialchars($article['city_name'] ?? '') : ''; ?>">
            <select name="city_id" id="city_id" class="w-full px-3 py-2 border rounded-lg" style="display: none;">
                <option value="">Nessuna</option>
                <?php foreach ($cities as $city): ?>
                <option value="<?php echo $city['id']; ?>" <?php if (isset($article) && $article['city_id'] == $city['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($city['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4">
            <label for="maps_link" class="block text-gray-700 font-bold mb-2">Link Google Maps</label>
            <input type="url" name="json_data[maps_link]" id="maps_link" class="w-full px-3 py-2 border rounded-lg" placeholder="https://maps.app.goo.gl/..." value="<?php echo htmlspecialchars($json_data['maps_link'] ?? ''); ?>">
        </div>
    </div>
</div>

<!-- Description -->
<div class="mt-6 p-6 bg-gray-50 rounded-lg border">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">📝 Descrizione Coinvolgente</h3>
    <textarea name="content" id="content" rows="8" class="w-full px-3 py-2 border rounded-lg"><?php echo htmlspecialchars($article['content'] ?? ''); ?></textarea>
</div>

<!-- Images -->
<div class="mt-6 p-6 bg-gray-50 rounded-lg border">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">🖼️ Immagini della Spiaggia</h3>

    <?php
    $fieldName = 'featured_image';
    $label = 'Immagine in Evidenza (per liste)';
    $currentImagePath = $article[$fieldName] ?? null;
    $helpText = 'Questa immagine appare nelle liste e nelle anteprime.';
    include __DIR__ . '/partials/image_upload_widget.php';

    $fieldName = 'hero_image';
    $label = 'Immagine Principale (Hero)';
    $currentImagePath = $article[$fieldName] ?? null;
    $helpText = 'Questa sarà l\'immagine grande mostrata in cima alla pagina.';
    include __DIR__ . '/partials/image_upload_widget.php';

    $fieldName = 'logo';
    $label = 'Logo Stabilimento (opzionale)';
    $currentImagePath = $article[$fieldName] ?? null;
    $helpText = 'Il logo appare di fianco al nome della struttura.';
    include __DIR__ . '/partials/image_upload_widget.php';
    ?>

    <div class="mb-4">
        <label for="gallery_images" class="block text-gray-700 font-bold mb-2">Galleria Immagini</label>
        <input type="file" name="gallery_images[]" id="gallery_images" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" multiple>

        <?php
        $gallery = isset($article['gallery_images']) ? json_decode($article['gallery_images'], true) : [];
        if (!empty($gallery)):
        ?>
        <div class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($gallery as $index => $imagePath): ?>
            <div class="relative group">
                <img src="../image-loader.php?path=<?php echo urlencode(str_replace('uploads_protected/', '', $imagePath)); ?>" class="w-full h-32 object-cover rounded-lg">
                <div class="absolute top-1 right-1">
                    <input type="checkbox" name="delete_gallery_images[]" value="<?php echo htmlspecialchars($imagePath); ?>" id="delete_gallery_<?php echo $index; ?>" class="hidden peer">
                    <label for="delete_gallery_<?php echo $index; ?>"
                           class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-1 px-2 rounded-full cursor-pointer transition-colors peer-checked:bg-yellow-500 peer-checked:text-black"
                           title="Seleziona per eliminare">
                        &times;
                    </label>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="mb-4">
    <label for="google_maps_iframe" class="block text-gray-700 font-bold mb-2">Google Maps Iframe</label>
    <textarea name="google_maps_iframe" id="google_maps_iframe" rows="4" class="w-full px-3 py-2 border rounded-lg"><?php echo htmlspecialchars($article['google_maps_iframe'] ?? ''); ?></textarea>
</div>

<?php include __DIR__ . '/partials/seo_section.php'; ?>

<!-- Settings -->
<div class="mt-6 p-6 bg-gray-50 rounded-lg border">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">⚙️ Impostazioni Articolo</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="author" class="block text-gray-700 font-bold mb-2">Autore</label>
            <input type="text" name="author" id="author" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($article['author'] ?? 'Admin'); ?>">
        </div>
        <div>
            <label for="status" class="block text-gray-700 font-bold mb-2">Stato</label>
            <select name="status" id="status" class="w-full px-3 py-2 border rounded-lg">
                <option value="draft" <?php if (isset($article) && $article['status'] === 'draft') echo 'selected'; ?>>Bozza</option>
                <option value="published" <?php if (isset($article) && $article['status'] === 'published') echo 'selected'; ?>>Pubblicato</option>
                <option value="archived" <?php if (isset($article) && $article['status'] === 'archived') echo 'selected'; ?>>Archiviato</option>
            </select>
        </div>
    </div>
</div>

<script>
// Re-using the same slug generation script
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.querySelector('#title');
    const slugInput = document.querySelector('#slug');
    if (titleInput && slugInput) {
        titleInput.addEventListener('keyup', () => {
            slugInput.value = generateSlug(titleInput.value);
        });
    }
    const generateSlug = (str) => {
        if (!str) return '';
        str = str.replace(/^\s+|\s+$/g, '');
        str = str.toLowerCase();
        const from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
        const to   = "aaaaeeeeiiiioooouuuunc------";
        for (let i = 0, l = from.length; i < l; i++) {
            str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
        }
        str = str.replace(/[^a-z0-9 -]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
        return str;
    };

    // Initialize City Autocomplete
    if (typeof CityAutocomplete !== 'undefined') {
        window.cityAutocomplete = new CityAutocomplete('city_autocomplete', 'province_id');
    }
});
</script>

<script src="js/city-autocomplete.js"></script>
