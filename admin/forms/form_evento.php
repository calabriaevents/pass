<?php
// Richiede le funzioni ausiliarie per caricare città e categorie.
if (!function_exists('get_citta_and_categorie')) {
    include_once '../includes/eventi_manager.php';
}
$lookup_data = get_citta_and_categorie();
$citta = $lookup_data['citta'];
$categorie = $lookup_data['categorie'];

// Variabili per il form (se in modalità modifica, $evento è definito in eventi.php)
$evento_id = $evento['id'] ?? '';
$titolo = $evento['titolo'] ?? '';
$descrizione = $evento['descrizione'] ?? '';
$data_evento = $evento['data_evento'] ?? date('Y-m-d');
$ora_inizio = $evento['ora_inizio'] ?? '';
$ora_fine = $evento['ora_fine'] ?? '';
$luogo = $evento['luogo'] ?? '';
$citta_selezionata = $evento['citta_id'] ?? '';
$categoria_selezionata = $evento['categoria_id'] ?? '';
$immagine_corrente = $evento['immagine'] ?? '';
$approvato_checked = ($evento['approvato'] ?? 0) ? 'checked' : '';

?>

<form action="" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= htmlspecialchars($evento_id) ?>">
    <input type="hidden" name="action" value="<?= $evento_id ? 'edit' : 'add' ?>">

    <div class="form-group">
        <label for="titolo">Titolo Evento *</label>
        <input type="text" class="form-control" id="titolo" name="titolo" value="<?= htmlspecialchars($titolo) ?>" required>
    </div>

    <div class="form-group">
        <label for="descrizione">Descrizione *</label>
        <textarea class="form-control" id="descrizione" name="descrizione" rows="5" required><?= htmlspecialchars($descrizione) ?></textarea>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label for="data_evento">Data Evento *</label>
            <input type="date" class="form-control" id="data_evento" name="data_evento" value="<?= htmlspecialchars($data_evento) ?>" required>
        </div>
        <div class="col-md-4 form-group">
            <label for="ora_inizio">Ora Inizio</label>
            <input type="time" class="form-control" id="ora_inizio" name="ora_inizio" value="<?= htmlspecialchars($ora_inizio) ?>">
        </div>
        <div class="col-md-4 form-group">
            <label for="ora_fine">Ora Fine</label>
            <input type="time" class="form-control" id="ora_fine" name="ora_fine" value="<?= htmlspecialchars($ora_fine) ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="luogo">Luogo (Indirizzo/Nome del Posto) *</label>
        <input type="text" class="form-control" id="luogo" name="luogo" value="<?= htmlspecialchars($luogo) ?>" required>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label for="citta_id">Città</label>
            <select class="form-control" id="citta_id" name="citta_id">
                <option value="">Seleziona Città</option>
                <?php foreach ($citta as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $citta_selezionata == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6 form-group">
            <label for="categoria_id">Categoria</label>
            <select class="form-control" id="categoria_id" name="categoria_id">
                <option value="">Seleziona Categoria</option>
                <?php foreach ($categorie as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categoria_selezionata == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="immagine">Immagine Evento (Consigliata: 1200x630)</label>
        <?php if ($immagine_corrente): ?>
            <p>Immagine attuale: <img src="../image-loader.php?path=<?= htmlspecialchars('eventi/' . $immagine_corrente) ?>&w=150&h=100&m=crop" style="max-width: 150px; height: auto;" alt="Immagine Evento"></p>
            <input type="hidden" name="immagine_corrente" value="<?= htmlspecialchars($immagine_corrente) ?>">
        <?php endif; ?>
        <input type="file" class="form-control-file" id="immagine" name="immagine" accept="image/*">
    </div>

    <div class="form-group form-check">
        <input type="checkbox" class="form-check-input" id="approvato" name="approvato" value="1" <?= $approvato_checked ?>>
        <label class="form-check-label" for="approvato">Evento Approvato (Visibile sul sito)</label>
    </div>

    <button type="submit" class="btn btn-primary mt-3">Salva Evento</button>
    <a href="eventi.php" class="btn btn-secondary mt-3">Annulla</a>
</form>