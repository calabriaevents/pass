<?php
/**
 * Reusable Image Upload Widget.
 *
 * This partial displays a file input, a preview of the current image,
 * and a checkbox to mark the image for deletion.
 *
 * @param string $fieldName         The 'name' attribute for the file input (e.g., 'featured_image').
 * @param string $label             The text for the label.
 * @param ?string $currentImagePath The path to the currently saved image, if any.
 * @param string $acceptTypes       (Optional) The 'accept' attribute for the file input. Defaults to common image types.
 * @param ?string $helpText         (Optional) A small text to display below the input.
 */

// Set default values for optional parameters
$acceptTypes = $acceptTypes ?? 'image/jpeg,image/jpg,image/png,image/webp';
$deleteFieldName = 'delete_' . $fieldName;
$previewId = 'preview_' . $fieldName;
$checkboxId = 'checkbox_' . $deleteFieldName;

?>
<div class="mb-6 image-upload-widget">
    <label for="<?php echo htmlspecialchars($fieldName); ?>" class="block text-gray-700 font-bold mb-2"><?php echo htmlspecialchars($label); ?></label>

    <?php if (!empty($currentImagePath)): ?>
        <div id="<?php echo $previewId; ?>" class="relative inline-block mb-2">
            <img src="../image-loader.php?path=<?php echo urlencode(str_replace('uploads_protected/', '', $currentImagePath)); ?>"
                 alt="Anteprima" class="w-48 h-auto object-cover rounded-lg border p-1 bg-white">
            <div class="absolute top-2 right-2">
                <label for="<?php echo $checkboxId; ?>"
                       class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-1 px-2 rounded-full cursor-pointer transition-colors"
                       onclick="document.getElementById('<?php echo $previewId; ?>').style.opacity = '0.4'; this.textContent = 'Marcata per eliminazione';">
                    &times; Elimina
                </label>
                <input type="checkbox" name="<?php echo htmlspecialchars($deleteFieldName); ?>" id="<?php echo $checkboxId; ?>" value="1" class="hidden">
            </div>
        </div>
    <?php endif; ?>

    <input type="file"
           name="<?php echo htmlspecialchars($fieldName); ?>"
           id="<?php echo htmlspecialchars($fieldName); ?>"
           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
           accept="<?php echo htmlspecialchars($acceptTypes); ?>">

    <?php if (isset($helpText)): ?>
        <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($helpText); ?></p>
    <?php endif; ?>
</div>
