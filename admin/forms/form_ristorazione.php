<?php
// This file is included by articoli.php.
// Variables available: $db, $article, $provinces, $cities
// Custom fields are stored in the 'json_data' column.
$json_data = isset($article['json_data']) ? json_decode($article['json_data'], true) : [];
if (!is_array($json_data)) $json_data = [];
?>

<!-- Basic Info -->
<div class="mb-4">
    <label for="title" class="block text-gray-700 font-bold mb-2">Nome Ristorante/Locale</label>
    <input type="text" name="title" id="title" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($article['title'] ?? ''); ?>" required>
</div>

<div class="mb-4">
    <label for="slug" class="block text-gray-700 font-bold mb-2">Slug (URL)</label>
    <input type="text" name="slug" id="slug" class="w-full px-3 py-2 border rounded-lg bg-gray-100" value="<?php echo htmlspecialchars($article['slug'] ?? ''); ?>" required>
</div>

<!-- Custom Fields for Restaurants -->
<div class="mt-6 p-6 bg-green-50 rounded-lg border border-green-200">
    <h3 class="text-lg font-semibold mb-4 text-green-800">🍝 Dettagli Ristorante</h3>

    <div class="mb-4">
        <label for="p_iva" class="block text-gray-700 font-bold mb-2">Partita IVA (Obbligatorio)</label>
        <input type="text" name="json_data[p_iva]" id="p_iva" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($json_data['p_iva'] ?? ''); ?>" required>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="mb-4">
            <label for="cuisine_type" class="block text-gray-700 font-bold mb-2">Tipo di Cucina</label>
            <input type="text" name="json_data[cuisine_type]" id="cuisine_type" class="w-full px-3 py-2 border rounded-lg" placeholder="Es. Tradizionale, Pesce" value="<?php echo htmlspecialchars($json_data['cuisine_type'] ?? ''); ?>">
        </div>
        <div class="mb-4">
            <label for="max_seats" class="block text-gray-700 font-bold mb-2">Numero Coperti</label>
            <input type="number" name="json_data[max_seats]" id="max_seats" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($json_data['max_seats'] ?? ''); ?>">
        </div>
        <div class="mb-4">
            <label for="atmosphere" class="block text-gray-700 font-bold mb-2">Atmosfera</label>
            <input type="text" name="json_data[atmosphere]" id="atmosphere" class="w-full px-3 py-2 border rounded-lg" placeholder="Es. Familiare, Romantica" value="<?php echo htmlspecialchars($json_data['atmosphere'] ?? ''); ?>">
        </div>
    </div>
     <div class="mb-4">
        <label for="opening_hours" class="block text-gray-700 font-bold mb-2">Orari di Apertura</label>
        <textarea name="json_data[opening_hours]" id="opening_hours" rows="4" class="w-full px-3 py-2 border rounded-lg font-mono text-sm"><?php echo htmlspecialchars($json_data['opening_hours'] ?? ''); ?></textarea>
        <p class="text-xs text-gray-500 mt-1">Un giorno per riga. Es: <strong>Lunedì: 12:00-15:00, 19:00-23:00</strong></p>
    </div>
</div>

<!-- Menu Section -->
<div class="mt-6 p-6 bg-gray-50 rounded-lg border">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">📜 Menù e Specialità</h3>
     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="mb-4">
            <label for="menu_pdf" class="block text-gray-700 font-bold mb-2">Carica Menù PDF</label>
            <input type="file" name="menu_pdf" id="menu_pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept=".pdf">
        </div>
        <div class="mb-4">
            <label for="menu_digital_link" class="block text-gray-700 font-bold mb-2">Link a Menù Digitale (es. QR Code)</label>
            <input type="url" name="json_data[menu_digital_link]" id="menu_digital_link" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($json_data['menu_digital_link'] ?? ''); ?>">
        </div>
    </div>
</div>

<!-- Location and Contacts -->
<div class="mt-6 p-6 bg-gray-50 rounded-lg border">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">📍 Localizzazione e Contatti</h3>
    <div class="mb-4">
        <label for="address" class="block text-gray-700 font-bold mb-2">Indirizzo</label>
        <input type="text" name="json_data[address]" id="address" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($json_data['address'] ?? ''); ?>">
    </div>
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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="contact_phone" class="block text-gray-700 font-bold mb-2">Telefono per Prenotazioni</label>
            <input type="tel" name="json_data[contact_details][phone]" id="contact_phone" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($json_data['contact_details']['phone'] ?? ''); ?>">
        </div>
        <div>
            <label for="contact_email" class="block text-gray-700 font-bold mb-2">Email</label>
            <input type="email" name="json_data[contact_details][email]" id="contact_email" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($json_data['contact_details']['email'] ?? ''); ?>">
        </div>
    </div>
</div>

<!-- Description -->
<div class="mt-6 p-6 bg-gray-50 rounded-lg border">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">📝 Descrizione e Storia</h3>
    <textarea name="content" id="content" rows="8" class="w-full px-3 py-2 border rounded-lg"><?php echo htmlspecialchars($article['content'] ?? ''); ?></textarea>
</div>

<!-- Images -->
<div class="mt-6 p-6 bg-gray-50 rounded-lg border">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">🖼️ Immagini del Locale e Piatti</h3>
    <div class="mb-6">
        <label for="hero_image" class="block text-gray-700 font-bold mb-2">Immagine Principale (Hero)</label>
        <input type="file" name="hero_image" id="hero_image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
    </div>
    <div class="mb-4">
        <label for="gallery_images" class="block text-gray-700 font-bold mb-2">Galleria Immagini</label>
        <input type="file" name="gallery_images[]" id="gallery_images" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" multiple>
    </div>
</div>

<?php include __DIR__ . '/partials/logo_upload_section.php'; ?>
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
