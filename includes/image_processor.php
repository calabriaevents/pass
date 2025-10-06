<?php

class ImageProcessor {
    private $upload_dir;

    public function __construct(string $base_dir = '../uploads/') {
        // Adjust base_dir if the script is run from the root
        if (strpos(getcwd(), 'admin') === false) {
             $this->upload_dir = 'uploads/';
        } else {
             $this->upload_dir = $base_dir;
        }

        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
    }

    public function processUploadedImage(array $file, string $subfolder, int $max_width = 1200): ?string {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $target_dir = $this->upload_dir . $subfolder . '/';
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $original_name = pathinfo($file['name'], PATHINFO_FILENAME);
        $sanitized_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $original_name);
        $new_filename = 'img_' . uniqid() . '_' . time() . '.webp';
        $upload_path = $target_dir . $new_filename;

        $image = $this->createImageFromFile($file['tmp_name']);
        if (!$image) {
            return null;
        }

        $resized_image = $this->resizeImage($image, $max_width);
        imagedestroy($image);

        if (imagewebp($resized_image, $upload_path, 80)) {
            imagedestroy($resized_image);
            // Return a path relative to the project root
            return str_replace('../', '', $this->upload_dir) . $subfolder . '/' . $new_filename;
        }

        imagedestroy($resized_image);
        return null;
    }

    private function createImageFromFile(string $filepath): GdImage|bool {
        $image_info = @getimagesize($filepath);
        if (!$image_info) {
            return false;
        }
        $mime_type = $image_info['mime'];

        switch ($mime_type) {
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

    public function deleteImage(string $relative_path): bool {
        // Path should be relative to the project root, e.g., 'uploads/cities/hero/img.webp'
        if (empty($relative_path)) return false;

        $full_path = dirname(__DIR__) . '/' . $relative_path;
        if (file_exists($full_path) && is_writable($full_path)) {
            return unlink($full_path);
        }
        return false;
    }
}