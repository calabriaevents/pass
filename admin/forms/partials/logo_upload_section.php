<div class="mb-6 border-t pt-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Logo</h3>
    <div class="flex items-center space-x-6">
        <?php if (isset($article) && !empty($article['logo'])): ?>
            <div class="flex-shrink-0">
                <img src="../<?php echo htmlspecialchars($article['logo']); ?>" alt="Logo attuale" class="h-16 w-16 object-contain rounded-lg border p-1">
                <p class="text-xs text-gray-500 mt-1 text-center">Logo attuale</p>
            </div>
        <?php endif; ?>
        <div class="flex-grow">
            <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">Carica un nuovo logo</label>
            <input type="file" name="logo" id="logo" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <p class="text-xs text-gray-500 mt-1">L'immagine dovrebbe essere in formato PNG, JPG o WebP. Verrà mostrata in aree dedicate.</p>
        </div>
    </div>
</div>