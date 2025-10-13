<?php
/**
 * Genera l'URL corretto per un'immagine, gestendo percorsi protetti e di default.
 *
 * @param string|null $path Il percorso dell'immagine dal database.
 * @param string $default_image Il percorso dell'immagine di fallback di default.
 * @return string L'URL dell'immagine processato e sicuro.
 */
function get_image_url(?string $path, string $default_image = 'assets/images/default-placeholder.png'): string {
    // Se il percorso è nullo o vuoto, usa l'immagine di default.
    if (empty($path)) {
        return htmlspecialchars($default_image);
    }

    // Se il percorso inizia con 'assets/', è un'immagine pubblica.
    if (strpos($path, 'assets/') === 0) {
        return htmlspecialchars($path);
    }

    // Per tutte le altre immagini, si presume che siano in 'uploads_protected/'.
    // Rimuovi il prefisso se presente, per garantire un percorso pulito per image-loader.
    $clean_path = str_replace('uploads_protected/', '', $path);

    return 'image-loader.php?path=' . urlencode($clean_path);
}
?>