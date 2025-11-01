<?php

class ImageProcessor {
    private $upload_dir;
    private $public_dir;
    private $last_error;

    public function __construct(string $base_subfolder = '') {
        $project_root = dirname(__DIR__);
        // Aggiunge la sotto-cartella di base al percorso di upload
        $this->upload_dir = $project_root . '/uploads_protected/' . ($base_subfolder ? trim($base_subfolder, '/') . '/' : '');
        $this->public_dir = $project_root . '/immagini/';

        // Controlla se la cartella di destinazione finale esiste, altrimenti la crea
        if (!is_dir($this->upload_dir)) {
            if (!mkdir($this->upload_dir, 0755, true)) {
                $this->last_error = "ERRORE CRITICO: La cartella '{$this->upload_dir}' non esiste e non può essere creata. Controlla i permessi.";
                error_log($this->last_error);
                // Lancia un'eccezione per fermare l'esecuzione se la cartella non è utilizzabile
                throw new Exception($this->last_error);
            }
        } elseif (!is_writable($this->upload_dir)) {
            $this->last_error = "ERRORE CRITICO: La cartella '{$this->upload_dir}' non è scrivibile. Controlla i permessi.";
            error_log($this->last_error);
            throw new Exception($this->last_error);
        }
    }

    public function getLastError(): ?string {
        return $this->last_error;
    }

    public function processUploadedImage(array $file, string $subfolder, int $max_width = 1200): ?string {
        $this->last_error = null;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->last_error = "Errore durante il caricamento del file. Codice: " . $file['error'];
            return null;
        }

        $target_dir = $this->upload_dir . $subfolder . '/';
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0755, true)) {
                $this->last_error = "Impossibile creare la sottocartella '{$subfolder}'. Controlla i permessi.";
                error_log($this->last_error);
                return null;
            }
        }

        if (!is_writable($target_dir)) {
            $this->last_error = "La cartella '{$target_dir}' non è scrivibile.";
            error_log($this->last_error);
            return null;
        }

        $new_filename = 'img_' . uniqid() . '_' . time() . '.webp';
        $upload_path = $target_dir . $new_filename;

        $image = $this->createImageFromFile($file['tmp_name']);
        if (!$image) {
            return null; // getLastError() è già stato impostato
        }

        $resized_image = $this->resizeImage($image, $max_width);
        imagedestroy($image);

        if (imagewebp($resized_image, $upload_path, 80)) {
            imagedestroy($resized_image);
            return $subfolder . '/' . $new_filename;
        }

        imagedestroy($resized_image);
        $this->last_error = "Impossibile salvare l'immagine convertita in WebP.";
        error_log($this->last_error . " Percorso: " . $upload_path);
        return null;
    }

    private function createImageFromFile(string $filepath): GdImage|bool {
        $image_info = getimagesize($filepath);
        if ($image_info === false) {
            $this->last_error = "Il file fornito non è un'immagine valida.";
            return false;
        }
        $mime_type = $image_info['mime'];

        switch ($mime_type) {
            case 'image/jpeg':
                return imagecreatefromjpeg($filepath);
            case 'image/png':
                return imagecreatefrompng($filepath);
            case 'image/gif':
                return imagecreatefromgif($filepath);
            case 'image/webp':
                 return imagecreatefromwebp($filepath);
            default:
                $this->last_error = "Tipo di immagine non supportato: {$mime_type}.";
                return false;
        }
    }

    private function resizeImage(GdImage $image, int $max_width): GdImage {
        $original_width = imagesx($image);
        $original_height = imagesy($image);

        if ($original_width <= $max_width) {
            return $image;
        }

        $aspect_ratio = $original_height / $original_width;
        $new_width = $max_width;
        $new_height = (int) ($new_width * $aspect_ratio);

        $resized_image = imagecreatetruecolor($new_width, $new_height);

        // Aggiunto controllo di fallimento per imagecreatetruecolor, possibile causa di crash.
        if ($resized_image === false) {
            error_log("ImageProcessor Error: imagecreatetruecolor fallito per dimensioni {$new_width}x{$new_height}. Potrebbe essere un problema di memoria. Restituisco l'immagine originale.");
            return $image; // Restituisce l'originale per non interrompere il flusso
        }

        imagealphablending($resized_image, false);
        imagesavealpha($resized_image, true);
        $transparent = imagecolorallocatealpha($resized_image, 255, 255, 255, 127);
        imagefill($resized_image, 0, 0, $transparent);

        imagecopyresampled($resized_image, $image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);

        return $resized_image;
    }

    public function deleteImage(string $relative_path): bool {
        if (empty($relative_path)) return false;

        $full_path = $this->upload_dir . '/' . $relative_path;
        if (file_exists($full_path) && is_writable($full_path)) {
            return unlink($full_path);
        }
        return false;
    }

    public function publishImage(string $source_protected_path, string $public_relative_path): bool {
        $this->last_error = null;

        // Normalize the source path to remove any leading "uploads/"
        if (strpos($source_protected_path, 'uploads/') === 0) {
            $source_protected_path = substr($source_protected_path, strlen('uploads/'));
        }

        $source_full_path = dirname(__DIR__) . '/uploads_protected/' . $source_protected_path;

        if (!file_exists($source_full_path)) {
            $this->last_error = "Impossibile pubblicare: file sorgente non trovato in '{$source_full_path}'.";
            error_log($this->last_error);
            return false;
        }

        $destination_full_path = $this->public_dir . $public_relative_path;
        $destination_dir = dirname($destination_full_path);

        if (!is_dir($destination_dir)) {
            if (!mkdir($destination_dir, 0755, true)) {
                $this->last_error = "Impossibile creare la cartella pubblica '{$destination_dir}'.";
                error_log($this->last_error);
                return false;
            }
        }

        if (!is_writable($destination_dir)) {
            $this->last_error = "La cartella pubblica '{$destination_dir}' non è scrivibile.";
             error_log($this->last_error);
            return false;
        }

        if (copy($source_full_path, $destination_full_path)) {
            return true;
        } else {
            $this->last_error = "Impossibile copiare il file da '{$source_full_path}' a '{$destination_full_path}'.";
            error_log($this->last_error);
            return false;
        }
    }

    public function unpublishImage(string $public_relative_path): bool {
        $this->last_error = null;

        if (empty($public_relative_path)) {
            return true;
        }

        $full_path = $this->public_dir . $public_relative_path;

        if (file_exists($full_path)) {
            if (is_writable($full_path) && unlink($full_path)) {
                $parent_dir = dirname($full_path);
                if (is_dir($parent_dir) && count(scandir($parent_dir)) == 2) {
                    rmdir($parent_dir);
                }
                return true;
            } else {
                $this->last_error = "Impossibile eliminare l'immagine pubblica '{$full_path}'. Controlla i permessi.";
                error_log($this->last_error);
                return false;
            }
        }

        return true;
    }

    public function unpublishDirectory(string $public_relative_path): bool {
        $this->last_error = null;

        if (empty($public_relative_path)) {
            return true;
        }

        $full_path = $this->public_dir . $public_relative_path;

        if (!is_dir($full_path)) {
            return true;
        }

        if (strpos(realpath($full_path), realpath($this->public_dir)) !== 0) {
             $this->last_error = "Tentativo di cancellazione non sicuro bloccato per il percorso: {$full_path}";
             error_log($this->last_error);
             return false;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($full_path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            if (!$todo($fileinfo->getRealPath())) {
                $this->last_error = "Impossibile eliminare '{$fileinfo->getRealPath()}' durante la pulizia della cartella pubblica.";
                error_log($this->last_error);
                return false;
            }
        }

        if (rmdir($full_path)) {
            return true;
        } else {
            $this->last_error = "Impossibile eliminare la cartella principale '{$full_path}'.";
            error_log($this->last_error);
            return false;
        }
    }
}