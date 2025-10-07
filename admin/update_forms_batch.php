<?php
/**
 * Script per aggiornare automaticamente tutti i form admin
 * Sostituisce i menu a tendina città con autocompletamento
 */

$formsDir = __DIR__ . '/forms/';
$forms = glob($formsDir . 'form_*.php');

// Pattern da cercare e sostituire
$oldCitySelectPattern = '/\s*<div>\s*<label for="city_id"[^>]*>[^<]*<\/label>\s*<select name="city_id"[^>]*>.*?<\/select>\s*<\/div>/s';

$newCityAutocomplete = '        <div>
            <label for="city_autocomplete" class="block text-gray-700 font-bold mb-2">Città</label>
            <input type="text" id="city_autocomplete" class="w-full px-3 py-2 border rounded-lg" placeholder="Inizia a digitare il nome della città..." value="<?php echo isset($article) && $article[\'city_id\'] ? htmlspecialchars($article[\'city_name\'] ?? \'\') : \'\'; ?>">
            <select name="city_id" id="city_id" class="w-full px-3 py-2 border rounded-lg" style="display: none;">
                <option value="">Nessuna</option>
                <?php foreach ($cities as $city): ?>
                <option value="<?php echo $city[\'id\']; ?>" <?php if (isset($article) && $article[\'city_id\'] == $city[\'id\']) echo \'selected\'; ?>>
                    <?php echo htmlspecialchars($city[\'name\']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>';

// Script JavaScript da aggiungere alla fine
$jsToAdd = '
<script src="js/city-autocomplete.js"></script>
<script>
document.addEventListener(\'DOMContentLoaded\', function() {
    // Initialize City Autocomplete
    if (typeof CityAutocomplete !== \'undefined\') {
        window.cityAutocomplete = new CityAutocomplete(\'city_autocomplete\', \'province_id\');
    }
});
</script>';

$updatedFiles = [];
$skippedFiles = ['form_default.php', 'form_hotel.php', 'form_ristorazione.php']; // Già aggiornati

foreach ($forms as $formFile) {
    $filename = basename($formFile);
    
    // Salta i file già aggiornati
    if (in_array($filename, $skippedFiles)) {
        continue;
    }
    
    $content = file_get_contents($formFile);
    
    // Controlla se il form contiene un select città
    if (strpos($content, 'name="city_id"') === false) {
        continue;
    }
    
    // Sostituisci il select città con autocompletamento
    $updatedContent = preg_replace($oldCitySelectPattern, $newCityAutocomplete, $content);
    
    // Aggiunge il JavaScript se non è già presente
    if (strpos($updatedContent, 'city-autocomplete.js') === false) {
        // Cerca la posizione dove inserire lo script
        if (strpos($updatedContent, '</script>') !== false) {
            // Trova l'ultimo </script> e inserisce dopo
            $lastScriptPos = strrpos($updatedContent, '</script>');
            if ($lastScriptPos !== false) {
                $insertPos = $lastScriptPos + strlen('</script>');
                $updatedContent = substr($updatedContent, 0, $insertPos) . $jsToAdd . substr($updatedContent, $insertPos);
            }
        } else {
            // Se non ci sono script, aggiunge alla fine
            $updatedContent .= $jsToAdd;
        }
    }
    
    // Verifica se ci sono state modifiche
    if ($content !== $updatedContent) {
        // Crea backup
        $backupFile = $formFile . '.backup.' . date('Y-m-d-H-i-s');
        copy($formFile, $backupFile);
        
        // Salva il file aggiornato
        file_put_contents($formFile, $updatedContent);
        $updatedFiles[] = $filename;
    }
}

echo "=== AGGIORNAMENTO BATCH FORM ADMIN COMPLETATO ===\n";
echo "Form aggiornati: " . count($updatedFiles) . "\n";

if (!empty($updatedFiles)) {
    echo "\nFile modificati:\n";
    foreach ($updatedFiles as $file) {
        echo "✅ $file\n";
    }
    echo "\n✨ I file originali sono stati salvati come backup con timestamp.\n";
} else {
    echo "\n💡 Nessun form necessitava di aggiornamento.\n";
}

echo "\n🔧 MODIFICHE APPLICATE:\n";
echo "1. Sostituito select città con input autocompletamento\n";
echo "2. Aggiunto script city-autocomplete.js\n";
echo "3. Aggiunto inizializzazione JavaScript\n";

echo "\n🎯 PROSSIMI PASSI:\n";
echo "1. Testare l'autocompletamento nell'admin\n";
echo "2. Verificare il salvataggio degli articoli\n";
echo "3. Testare la creazione di nuove città\n";

echo "\n✅ SISTEMA AMMINISTRAZIONE CITTÀ: PRONTO!\n";
?>