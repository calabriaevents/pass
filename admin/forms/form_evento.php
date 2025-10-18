<div class="space-y-4">
    <div>
        <label for="titolo" class="block text-sm font-medium text-gray-700">Titolo Evento</label>
        <input type="text" name="titolo" id="titolo" value="<?php echo htmlspecialchars($event['titolo'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
    </div>

    <div>
        <label for="nomeAttivita" class="block text-sm font-medium text-gray-700">Nome Attività</label>
        <input type="text" name="nomeAttivita" id="nomeAttivita" value="<?php echo htmlspecialchars($event['nomeAttivita'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
    </div>

    <div>
        <label for="descrizione" class="block text-sm font-medium text-gray-700">Descrizione</label>
        <textarea name="descrizione" id="descrizione" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"><?php echo htmlspecialchars($event['descrizione'] ?? ''); ?></textarea>
    </div>

    <div>
        <label for="categoria" class="block text-sm font-medium text-gray-700">Categoria</label>
        <input type="text" name="categoria" id="categoria" value="<?php echo htmlspecialchars($event['categoria'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="provincia_id" class="block text-sm font-medium text-gray-700">Provincia</label>
            <select name="provincia_id" id="provincia_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Seleziona una provincia</option>
                <?php foreach ($provinces as $province): ?>
                    <option value="<?php echo $province['id']; ?>" <?php echo (isset($event['provincia_id']) && $event['provincia_id'] == $province['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($province['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="citta_id" class="block text-sm font-medium text-gray-700">Città</label>
            <select name="citta_id" id="citta_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Seleziona una città</option>
                <?php foreach ($cities as $city): ?>
                    <option value="<?php echo $city['id']; ?>" data-province="<?php echo $city['province_id']; ?>" <?php echo (isset($event['citta_id']) && $event['citta_id'] == $city['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($city['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="dataEvento" class="block text-sm font-medium text-gray-700">Data Evento</label>
            <input type="datetime-local" name="dataEvento" id="dataEvento" value="<?php echo isset($event['dataEvento']) ? date('Y-m-d\TH:i', strtotime($event['dataEvento'])) : ''; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        <div>
            <label for="orarioInizio" class="block text-sm font-medium text-gray-700">Orario Inizio</label>
            <input type="time" name="orarioInizio" id="orarioInizio" value="<?php echo htmlspecialchars($event['orarioInizio'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
    </div>

    <div>
        <label for="costoIngresso" class="block text-sm font-medium text-gray-700">Costo Ingresso</label>
        <input type="text" name="costoIngresso" id="costoIngresso" value="<?php echo htmlspecialchars($event['costoIngresso'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
    </div>

    <div>
        <label for="imageUrl" class="block text-sm font-medium text-gray-700">Immagine Evento</label>
        <input type="file" name="imageUrl" id="imageUrl" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
        <?php if (!empty($event['imageUrl'])): ?>
            <div class="mt-2">
                <img src="../image-loader.php?path=<?php echo urlencode(str_replace('uploads_protected/', '', $event['imageUrl'])); ?>" alt="Immagine attuale" class="h-20 rounded-md">
            </div>
        <?php endif; ?>
    </div>

    <div>
        <label for="linkMappaGoogle" class="block text-sm font-medium text-gray-700">Link Mappa Google</label>
        <input type="url" name="linkMappaGoogle" id="linkMappaGoogle" value="<?php echo htmlspecialchars($event['linkMappaGoogle'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
    </div>

    <div>
        <label for="linkPreviewMappaEmbed" class="block text-sm font-medium text-gray-700">Link Preview Mappa (Embed)</label>
        <textarea name="linkPreviewMappaEmbed" id="linkPreviewMappaEmbed" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"><?php echo htmlspecialchars($event['linkPreviewMappaEmbed'] ?? ''); ?></textarea>
    </div>

    <div>
        <label for="linkContattoPrenotazioni" class="block text-sm font-medium text-gray-700">Link Contatto Prenotazioni</label>
        <input type="url" name="linkContattoPrenotazioni" id="linkContattoPrenotazioni" value="<?php echo htmlspecialchars($event['linkContattoPrenotazioni'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const provinceSelect = document.getElementById('provincia_id');
        const citySelect = document.getElementById('citta_id');
        const allCities = Array.from(citySelect.options);

        function filterCities() {
            const selectedProvince = provinceSelect.value;
            citySelect.innerHTML = '';

            const placeholder = allCities.find(opt => opt.value === '');
            if (placeholder) {
                citySelect.add(placeholder.cloneNode(true));
            }

            allCities.forEach(option => {
                if (option.dataset.province === selectedProvince) {
                    citySelect.add(option.cloneNode(true));
                }
            });
        }

        provinceSelect.addEventListener('change', filterCities);

        // Initial filter
        filterCities();
    });
</script>