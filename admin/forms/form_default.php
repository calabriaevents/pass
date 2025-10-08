<?php
// This file is included by articoli.php.
// Variables available: $db, $article, $provinces, $cities
$json_data = isset($article['json_data']) ? json_decode($article['json_data'], true) : [];
if (!is_array($json_data)) $json_data = [];
?>

<div class="mb-4">
    <label for="title" class="block text-gray-700 font-bold mb-2">Titolo</label>
    <input type="text" name="title" id="title" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($article['title'] ?? ''); ?>" required>
</div>
<div class="mb-4">
    <label for="slug" class="block text-gray-700 font-bold mb-2">Slug</label>
    <input type="text" name="slug" id="slug" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($article['slug'] ?? ''); ?>" required>
</div>
<div class="mb-4">
    <label for="content" class="block text-gray-700 font-bold mb-2">Contenuto</label>
    <textarea name="content" id="content" rows="10" class="w-full px-3 py-2 border rounded-lg"><?php echo htmlspecialchars($article['content'] ?? ''); ?></textarea>
</div>
<div class="mb-4">
    <label for="excerpt" class="block text-gray-700 font-bold mb-2">Estratto</label>
    <textarea name="excerpt" id="excerpt" rows="3" class="w-full px-3 py-2 border rounded-lg"><?php echo htmlspecialchars($article['excerpt'] ?? ''); ?></textarea>
</div>

<!-- Image Upload Section -->
<div class="mb-6 p-6 bg-gray-50 rounded-lg border">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">🖼️ Gestione Immagini</h3>

    <div class="mb-6">
        <label for="featured_image" class="block text-gray-700 font-bold mb-2">Immagine in evidenza</label>
        <input type="file" name="featured_image" id="featured_image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept="image/jpeg,image/jpg,image/png,image/webp">
    </div>

    <div class="mb-4">
        <label for="gallery_images" class="block text-gray-700 font-bold mb-2">Galleria immagini</label>
        <input type="file" name="gallery_images[]" id="gallery_images" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" accept="image/jpeg,image/jpg,image/png,image/webp" multiple>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
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
        <!-- Mantieni il select originale nascosto per compatibilità -->
        <select name="city_id" id="city_id" class="w-full px-3 py-2 border rounded-lg" style="display: none;">
            <option value="">Nessuna</option>
            <?php foreach ($cities as $city): ?>
            <option value="<?php echo $city['id']; ?>" <?php if (isset($article) && $article['city_id'] == $city['id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($city['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

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

<?php include __DIR__ . '/partials/seo_section.php'; ?>

<!-- Include City Autocomplete JavaScript -->
<script src="js/city-autocomplete.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof CityAutocomplete !== 'undefined') {
        window.cityAutocomplete = new CityAutocomplete('city_autocomplete', 'province_id');
    }
});
</script>