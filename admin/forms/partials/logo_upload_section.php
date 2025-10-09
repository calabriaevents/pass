<div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Logo Attività</h3>
    <div class="flex items-center space-x-6">
        <div class="shrink-0">
            <?php if (!empty($article['logo'])): ?>
                <img id="logo_preview" class="h-24 w-24 object-contain rounded-lg border p-1 bg-white" src="../image-loader.php?path=<?php echo urlencode($article['logo']); ?>" alt="Logo attuale">
            <?php else: ?>
                <img id="logo_preview" class="h-24 w-24 object-contain rounded-lg border p-1 bg-white" src="https://via.placeholder.com/100" alt="Anteprima Logo">
            <?php endif; ?>
        </div>
        <label class="block w-full">
            <span class="sr-only">Scegli un logo</span>
            <input type="file" name="logo" id="logo" onchange="document.getElementById('logo_preview').src = window.URL.createObjectURL(this.files[0])" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
        </label>
    </div>
    <p class="text-xs text-gray-500 mt-2">Consigliato: file PNG con sfondo trasparente.</p>
</div>