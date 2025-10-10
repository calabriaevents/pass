<?php
// This file is included by articoli.php.
// Variables available: $db, $article, $provinces, $cities
$json_data = isset($article['json_data']) ? json_decode($article['json_data'], true) : [];
if (!is_array($json_data)) $json_data = [];
?>

<!-- Basic Info -->
<div class="mb-4">
    <label for="title" class="block text-gray-700 font-bold mb-2">Nome Tour / Servizio Guida</label>
    <input type="text" name="title" id="title" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($article['title'] ?? ''); ?>" required>
</div>

<div class="mb-4">
    <label for="slug" class="block text-gray-700 font-bold mb-2">Slug (URL)</label>
    <input type="text" name="slug" id="slug" class="w-full px-3 py-2 border rounded-lg bg-gray-100" value="<?php echo htmlspecialchars($article['slug'] ?? ''); ?>" required>
</div>

<!-- Custom Fields for Tours -->
<div class="mt-6 p-6 bg-teal-50 rounded-lg border border-teal-200">
    <h3 class="text-lg font-semibold mb-4 text-teal-800">🚶 Dettagli Tour</h3>
    <div class="mb-4">
        <label for="prices" class="block text-gray-700 font-bold mb-2">Prezzi</label>
        <input type="text" name="json_data[prices]" id="prices" class="w-full px-3 py-2 border rounded-lg" placeholder="Es. 50€ a persona" value="<?php echo htmlspecialchars($json_data['prices'] ?? ''); ?>">
    </div>
    <div class="mb-4">
        <label for="duration" class="block text-gray-700 font-bold mb-2">Durata</label>
        <input type="text" name="json_data[duration]" id="duration" class="w-full px-3 py-2 border rounded-lg" placeholder="Es. Mezza giornata, 3 ore" value="<?php echo htmlspecialchars($json_data['duration'] ?? ''); ?>">
    </div>
</div>

<!-- Services -->
<div class="mt-6 p-6 bg-gray-50 rounded-lg border">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">Servizi Inclusi</h3>
    <?php
        $predefined_services = ['Tour a Piedi', 'Escursione in Barca', 'Guida Turistica', 'Trasporto'];
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
    <h3 class="text-lg font-semibold mb-4 text-gray-800">📍 Contatti Guida / Tour Operator</h3>
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
    <h3 class="text-lg font-semibold mb-4 text-gray-800">📝 Descrizione del Tour</h3>
    <textarea name="content" id="content" rows="10" class="w-full px-3 py-2 border rounded-lg"><?php echo htmlspecialchars($article['content'] ?? ''); ?></textarea>
</div>

<!-- Images -->
<div class="mt-6 p-6 bg-gray-50 rounded-lg border">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">🖼️ Immagini</h3>
    <div class="mb-6">
        <label for="hero_image" class="block text-gray-700 font-bold mb-2">Immagine Rappresentativa</label>
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
});
</script>
