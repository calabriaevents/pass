<?php
// File: admin/forms/partials/seo_section.php
// This partial is included in the article forms.
// The variables $article and $json_data are available from the main file.

$seo_data = $json_data['seo'] ?? [];
?>

<div class="mt-6 p-6 bg-gray-50 rounded-lg border">
    <h3 class="text-lg font-semibold mb-4 text-gray-800 flex items-center">
        <i data-lucide="line-chart" class="w-5 h-5 mr-2 text-blue-600"></i>
        SEO & Anteprima Google
    </h3>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="space-y-4">
            <div>
                <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-1">Titolo SEO</label>
                <input type="text" name="json_data[seo][meta_title]" id="meta_title"
                       class="w-full px-3 py-2 border rounded-lg"
                       value="<?php echo htmlspecialchars($seo_data['meta_title'] ?? ''); ?>"
                       onkeyup="updatePreview()" maxlength="60">
                <p class="text-xs text-gray-500 mt-1">Massimo 60 caratteri. Se vuoto, verrà usato il titolo dell'articolo.</p>
            </div>
            <div>
                <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">Meta Descrizione</label>
                <textarea name="json_data[seo][meta_description]" id="meta_description" rows="4"
                          class="w-full px-3 py-2 border rounded-lg"
                          onkeyup="updatePreview()" maxlength="160"><?php echo htmlspecialchars($seo_data['meta_description'] ?? ''); ?></textarea>
                <p class="text-xs text-gray-500 mt-1">Massimo 160 caratteri. Se vuota, verrà usato l'estratto.</p>
            </div>
            <div>
                <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-1">Parole Chiave</label>
                <input type="text" name="json_data[seo][meta_keywords]" id="meta_keywords"
                       class="w-full px-3 py-2 border rounded-lg"
                       value="<?php echo htmlspecialchars($seo_data['meta_keywords'] ?? ''); ?>"
                       placeholder="es. hotel calabria, vacanze tropea, ristorante tipico">
                <p class="text-xs text-gray-500 mt-1">Parole chiave separate da virgola.</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Anteprima Risultati Google</label>
            <div class="border rounded-lg p-4 bg-white">
                <div id="preview-url" class="text-sm text-gray-600 truncate">
                    https://www.passionecalabria.it/articolo/<span id="preview-slug"></span>
                </div>
                <div id="preview-title" class="text-blue-800 text-xl font-medium truncate hover:underline">
                    Titolo del tuo articolo
                </div>
                <div id="preview-description" class="text-sm text-gray-600 mt-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    Questa è la descrizione che apparirà sotto il tuo titolo nei risultati di ricerca di Google...
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updatePreview() {
        // Title
        const metaTitle = document.getElementById('meta_title').value;
        const articleTitle = document.getElementById('title').value;
        document.getElementById('preview-title').textContent = metaTitle || articleTitle || 'Titolo del tuo articolo';

        // Description
        const metaDescription = document.getElementById('meta_description').value;
        const articleExcerpt = document.getElementById('excerpt') ? document.getElementById('excerpt').value : '';
        document.getElementById('preview-description').textContent = metaDescription || articleExcerpt || 'Questa è la descrizione che apparirà sotto il tuo titolo nei risultati di ricerca di Google...';

        // Slug
        const slug = document.getElementById('slug').value;
        document.getElementById('preview-slug').textContent = slug || 'il-tuo-articolo';
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Update the preview on page load and when the main fields change
        updatePreview();
        document.getElementById('title').addEventListener('keyup', updatePreview);

        const excerptEl = document.getElementById('excerpt');
        if (excerptEl) {
            excerptEl.addEventListener('keyup', updatePreview);
        }

        // The slug might not always be present, but the preview needs it
        const slugEl = document.getElementById('slug');
        if (slugEl) {
            slugEl.addEventListener('keyup', updatePreview);
        }
    });
</script>