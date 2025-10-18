<?php
// Assumiamo che $db sia un'istanza della classe Database, definita in database_mysql.php

/**
 * Recupera tutti gli eventi.
 * @param bool $approved Se TRUE, recupera solo gli eventi approvati e futuri. Se FALSE, tutti.
 * @return array Lista di eventi.
 */
function get_all_events($approved = false) {
    global $db;
    $where = $approved ? "WHERE e.approvato = 1 AND e.data_evento >= CURDATE()" : "";
    $sql = "SELECT e.*, c.nome AS nome_citta, c.slug AS slug_citta, cat.nome AS nome_categoria
            FROM eventi e
            LEFT JOIN cities c ON e.citta_id = c.id
            LEFT JOIN categories cat ON e.categoria_id = cat.id
            {$where}
            ORDER BY e.data_evento ASC, e.ora_inizio ASC";
    $stmt = $db->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Recupera un singolo evento per ID.
 * @param int $id ID dell'evento.
 * @return array|null Dati dell'evento o NULL.
 */
function get_event_by_id($id) {
    global $db;
    $sql = "SELECT e.*, c.nome AS nome_citta, c.slug AS slug_citta, cat.nome AS nome_categoria
            FROM eventi e
            LEFT JOIN cities c ON e.citta_id = c.id
            LEFT JOIN categories cat ON e.categoria_id = cat.id
            WHERE e.id = ?";
    $stmt = $db->pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Inserisce o aggiorna un evento.
 * @param array $data Dati dell'evento.
 * @param int|null $id ID dell'evento (per update).
 * @return bool Successo dell'operazione.
 */
function save_event($data, $id = null) {
    global $db;

    if (empty($data['titolo']) || empty($data['descrizione']) || empty($data['data_evento']) || empty($data['luogo'])) {
        return false;
    }

    $ora_inizio = !empty($data['ora_inizio']) ? $data['ora_inizio'] : null;
    $ora_fine = !empty($data['ora_fine']) ? $data['ora_fine'] : null;
    $citta_id = !empty($data['citta_id']) ? (int)$data['citta_id'] : null;
    $categoria_id = !empty($data['categoria_id']) ? (int)$data['categoria_id'] : null;
    $immagine = !empty($data['immagine']) ? $data['immagine'] : null;
    $approvato = isset($data['approvato']) ? (int)$data['approvato'] : 0;
    $utente_id = isset($data['utente_id']) ? (int)$data['utente_id'] : ($_SESSION['user_id'] ?? null);

    if ($id) {
        // UPDATE
        $sql = "UPDATE eventi SET
                titolo = :titolo,
                descrizione = :descrizione,
                data_evento = :data_evento,
                ora_inizio = :ora_inizio,
                ora_fine = :ora_fine,
                luogo = :luogo,
                citta_id = :citta_id,
                categoria_id = :categoria_id,
                approvato = :approvato";
        if ($immagine) {
             $sql .= ", immagine = :immagine";
        }
        $sql .= " WHERE id = :id";
    } else {
        // INSERT
        $sql = "INSERT INTO eventi (titolo, descrizione, data_evento, ora_inizio, ora_fine, luogo, citta_id, categoria_id, immagine, utente_id, approvato)
                VALUES (:titolo, :descrizione, :data_evento, :ora_inizio, :ora_fine, :luogo, :citta_id, :categoria_id, :immagine, :utente_id, :approvato)";
    }

    $stmt = $db->pdo->prepare($sql);
    $params = [
        ':titolo' => $data['titolo'],
        ':descrizione' => $data['descrizione'],
        ':data_evento' => $data['data_evento'],
        ':ora_inizio' => $ora_inizio,
        ':ora_fine' => $ora_fine,
        ':luogo' => $data['luogo'],
        ':citta_id' => $citta_id,
        ':categoria_id' => $categoria_id,
        ':approvato' => $approvato
    ];

    if ($id) {
        $params[':id'] = $id;
        if ($immagine) {
            $params[':immagine'] = $immagine;
        }
    } else {
        $params[':immagine'] = $immagine;
        $params[':utente_id'] = $utente_id;
    }

    return $stmt->execute($params);
}

/**
 * Cancella un evento per ID.
 */
function delete_event($id) {
    global $db;
    $sql = "DELETE FROM eventi WHERE id = ?";
    $stmt = $db->pdo->prepare($sql);
    return $stmt->execute([$id]);
}

/**
 * Approva un evento.
 */
function approve_event($id) {
    global $db;
    $sql = "UPDATE eventi SET approvato = 1 WHERE id = ?";
    $stmt = $db->pdo->prepare($sql);
    return $stmt->execute([$id]);
}

/**
 * Funzione per prendere tutte le città e categorie per i dropdown del form.
 */
function get_citta_and_categorie() {
    global $db;

    $citta = $db->getCities();
    $categorie = $db->getCategories();

    return ['citta' => $citta, 'categorie' => $categorie];
}
