<div class="mb-6">
    <label for="logo" class="block text-gray-700 font-bold mb-2">Logo Articolo</label>
    <input type="file" name="logo" id="logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100" accept="image/jpeg,image/jpg,image/png,image/webp">
    <?php if (isset($article) && !empty($article['logo'])): ?>
        <div class="mt-4">
            <p class="text-sm text-gray-600 mb-2">Logo attuale:</p>
            <img src="../<?php echo htmlspecialchars($article['logo']); ?>" alt="Logo" class="w-24 h-24 object-contain rounded-lg border p-1">
        </div>
    <?php endif; ?>
</div>