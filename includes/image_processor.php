<?php

class ImageProcessor {
    private string $project_root;
    private string $upload_dir_name = 'uploads';
    private string $upload_dir_abs;

    /**
     * The constructor calculates an absolute path to the project's root directory.
     * This makes all internal file operations consistent and reliable.
     * It assumes this file is located in a directory one level down from the project root (e.g., /includes/).
     */
    public function __construct() {
        $this->project_root = dirname(__DIR__);
        $this->upload_dir_abs = $this->project_root . '/' . $this->upload_dir_name;

        if (!is_dir($this->upload_dir_abs)) {
            mkdir($this->upload_dir_abs, 0755, true);
        }
    }

    /**
     * Processes an uploaded image: validates, resizes, converts to WebP, and saves it.
     * Returns a web-accessible, root-relative path for storage in the database.
     */
    public function processUploadedImage(array $file, string $subfolder, int $max_width = 1200): ?string {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $target_dir_abs = $this->upload_dir_abs . '/' . $subfolder . '/';
        if (!is_dir($target_dir_abs)) {
            mkdir($target_dir_abs, 0755, true);
        }

        $new_filename = 'img_' . uniqid() . '_' . time() . '.webp';
        $upload_path_abs = $target_dir_abs . $new_filename;

        $image = $this->createImageFromFile($file['tmp_name']);
        if (!$image) {
            return null;
        }

        $resized_image = $this->resizeImage($image, $max_width);
        imagedestroy($image);

        if (imagewebp($resized_image, $upload_path_abs, 80)) { // Use absolute path for saving
            imagedestroy($resized_image);
            // Return a path relative to the project root for the database
            return $this->upload_dir_name . '/' . $subfolder . '/' . $new_filename;
        }

        imagedestroy($resized_image);
        return null;
    }

    /**
     * Creates a GD image resource from a file path.
     */
    private function createImageFromFile(string $filepath): GdImage|bool {
        $image_info = @getimagesize($filepath);
        if (!$image_info) return false;

        switch ($image_info['mime']) {
            case 'image/jpeg':
                return imagecreatefromjpeg($filepath);
            case 'image/png':
                $img = imagecreatefrompng($filepath);
                imagepalettetotruecolor($img);
                imagealphablending($img, true);
                imagesavealpha($img, true);
                return $img;
            case 'image/gif':
                return imagecreatefromgif($filepath);
            case 'image/webp':
                 return imagecreatefromwebp($filepath);
            default:
                return false;
        }
    }

    /**
     * Resizes a GD image resource, maintaining aspect ratio.
     */
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
        imagefilledrectangle($resized_image, 0, 0, $new_width, $new_height, $transparent);
        imagecopyresampled($resized_image, $image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);

        return $resized_image;
    }

    /**
     * Deletes an image file using its root-relative path.
     */
    public function deleteImage(string $relative_path): bool {
        if (empty($relative_path)) return false;

        // Construct the full, absolute path from the project root and the relative path.
        $full_path = $this->project_root . '/' . ltrim($relative_path, '/');

        if (file_exists($full_path) && is_writable($full_path)) {
            return unlink($full_path);
        }
        return false;
    }
}