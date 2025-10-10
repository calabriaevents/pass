<?php
// This file is included by articoli.php.
// Variables available: $db, $article, $provinces, $cities
$json_data = isset($article['json_data']) ? json_decode($article['json_data'], true) : [];
if (!is_array($json_data)) $json_data = [];
?>

<!-- Basic Info -->
<div class="mb-4">
    <label for="title" class="block text-gray-700 font-bold mb-2">Nome Centro Benessere / Spa</label>
    <input type="text" name="title" id="title" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($article['title'] ?? ''); ?>" required>
</div>

<div class="mb-4">
    <label for="slug" class="block text-gray-700 font-bold mb-2">Slug (URL)</label>
    <input type="text" name="slug" id="slug" class="w-full px-3 py-2 border rounded-lg bg-gray-100" value="<?php echo htmlspecialchars($article['slug'] ?? ''); ?>" required>
</div>

<!-- Custom Fields for Wellness -->
<div class="mt-6 p-6 bg-indigo-50 rounded-lg border border-indigo-200">
    <h3 class="text-lg font-semibold mb-4 text-indigo-800">💆 Dettagli Centro</h3>

    <div class="mb-4">
        <label for="treatments" class="block text-gray-700 font-bold mb-2">Trattamenti Offerti</label>
        <textarea name="json_data[treatments]" id="treatments" rows="4" class="w-full px-3 py-2 border rounded-lg"><?php echo htmlspecialchars($json_data['treatments'] ?? ''); ?></textarea>
        <p class="text-xs text-gray-500 mt-1">Descrivi qui i trattamenti principali.</p>
    </div>

    <div class="mb-4">
        <label for="opening_hours" class="block text-gray-700 font-bold mb-2">Orari di Apertura</label>
        <textarea name="json_data[opening_hours]" id="opening_hours" rows="3" class="w-full px-3 py-2 border rounded-lg"><?php echo htmlspecialchars($json_data['opening_hours'] ?? ''); ?></textarea>
    </div>

    <div class="mb-4">
        <label for="price_range" class="block text-gray-700 font-bold mb-2">Fasce di Prezzo</label>
        <input type="text" name="json_data[price_range]" id="price_range" class="w-full px-3 py-2 border rounded-lg" placeholder="Es. Trattamenti a partire da 50€" value="<?php echo htmlspecialchars($json_data['price_range'] ?? ''); ?>">
    </div>
</div>

<!-- Services -->
<div class="mt-6 p-6 bg-gray-50 rounded-lg border">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">Servizi</h3>
    <?php
        $predefined_services = ['Massaggi', 'Piscina Termale', 'Sauna', 'Bagno Turco', 'Idromassaggio'];
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
        <input type="text" name="json_data[services][custom]" id="custom_services" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($current_services['custom'] ?? ''); ?>">
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
            <label for="contact_phone" class="block text-gray-700 font-bold mb-2">Telefono</label>
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
    <h3 class="text-lg font-semibold mb-4 text-gray-800">📝 Descrizione</h3>
    <textarea name="content" id="content" rows="10" class="w-full px-3 py-2 border rounded-lg"><?php echo htmlspecialchars($article['content'] ?? ''); ?></textarea>
</div>

<!-- Images -->
<div class="mt-6 p-6 bg-gray-50 rounded-lg border">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">🖼️ Immagini</h3>
     <div class="mb-6">
        <label for="logo" class="block text-gray-700 font-bold mb-2">Logo</label>
        <input type="file" name="logo" id="logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">
    </div>
    <div class="mb-6">
        <label for="hero_image" class="block text-gray-700 font-bold mb-2">Immagine Principale (Hero)</label>
        <input type="file" name="hero_image" id="hero_image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
    </div>
    <div class="mb-4">
        <label for="gallery_images" class="block text-gray-700 font-bold mb-2">Galleria Immagini</label>
        <input type="file" name="gallery_images[]" id="gallery_images" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" multiple>
    </div>
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

    // Initialize City Autocomplete
    if (typeof CityAutocomplete !== 'undefined') {
        window.cityAutocomplete = new CityAutocomplete('city_autocomplete', 'province_id');
    }
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
