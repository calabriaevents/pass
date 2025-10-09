<?php

class ImageProcessor {
    private $upload_dir;
    private $last_error;

    public function __construct(string $base_dir = '') {
        // Garantisce che il percorso base sia sempre la root del progetto
        $project_root = dirname(__DIR__);

        if (empty($base_dir)) {
            // Percorso canonico e corretto per le immagini protette
            $this->upload_dir = $project_root . '/uploads_protected/';
        } else {
            $this->upload_dir = $base_dir;
        }

        // Controlla se la cartella esiste e se è scrivibile, altrimenti prova a crearla
        if (!is_dir($this->upload_dir)) {
            if (!mkdir($this->upload_dir, 0755, true)) {
                $this->last_error = "ERRORE CRITICO: La cartella '{$this->upload_dir}' non esiste e non può essere creata. Controlla i permessi della cartella principale.";
                error_log($this->last_error);
            }
        } elseif (!is_writable($this->upload_dir)) {
            $this->last_error = "ERRORE CRITICO: La cartella '{$this->upload_dir}' non è scrivibile. Controlla i permessi.";
            error_log($this->last_error);
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
}