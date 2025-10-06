<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';
require_once '../includes/image_processor.php';

$db = new Database();
$imageProcessor = new ImageProcessor();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['city_name'] ?? '';
    $province_id = $_POST['city_province_id'] ?? '';
    $description = $_POST['city_description'] ?? '';
    $latitude = !empty($_POST['city_latitude']) ? (float)$_POST['city_latitude'] : null;
    $longitude = !empty($_POST['city_longitude']) ? (float)$_POST['city_longitude'] : null;
    $google_maps_link = $_POST['city_google_maps_link'] ?? '';

    $upload_error = '';
    $hero_image_path = null;
    $gallery_images_json = null;

    if ($action === 'edit' && $id) {
        $existingCity = $db->getCityById($id);
        $hero_image_path = $existingCity['hero_image'] ?? null;
        $gallery_images_json = $existingCity['gallery_images'] ?? null;
    }

    if (!empty($_FILES['hero_image']['name'])) {
        $new_hero_path = $imageProcessor->processUploadedImage($_FILES['hero_image'], 'cities/hero', 1920);
        if ($new_hero_path) {
            if ($hero_image_path) {
                $imageProcessor->deleteImage($hero_image_path);
            }
            $hero_image_path = $new_hero_path;
        } else {
            $upload_error = 'Errore nel caricamento dell\'immagine hero.';
        }
    }

    if (!empty($_FILES['gallery_images']['name'][0])) {
        $gallery_images = $gallery_images_json ? json_decode($gallery_images_json, true) : [];
        foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if (!empty($tmp_name)) {
                $file_data = ['name' => $_FILES['gallery_images']['name'][$key],'type' => $_FILES['gallery_images']['type'][$key],'tmp_name' => $tmp_name,'error' => $_FILES['gallery_images']['error'][$key],'size' => $_FILES['gallery_images']['size'][$key]];
                $new_gallery_path = $imageProcessor->processUploadedImage($file_data, 'cities/gallery', 1280);
                if ($new_gallery_path) {
                    $gallery_images[] = $new_gallery_path;
                } else {
                    $upload_error = 'Errore nel caricamento di un\'immagine della galleria.';
                    break;
                }
            }
        }
        if (empty($upload_error)) {
            $gallery_images_json = json_encode(array_values($gallery_images));
        }
    }

    if (empty($upload_error)) {
        if ($action === 'edit' && $id) {
            $db->updateCityExtended($id, $name, $province_id, $description, $latitude, $longitude, $hero_image_path, $google_maps_link, $gallery_images_json);
        } else {
            $db->createCityExtended($name, $province_id, $description, $latitude, $longitude, $hero_image_path, $google_maps_link, $gallery_images_json);
        }
        header('Location: citta.php?action=' . ($action === 'edit' ? 'edit&id=' . $id : 'list') . '&success=1');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_gallery_image']) && $id) {
    $city = $db->getCityById($id);
    if ($city && $city['gallery_images']) {
        $gallery_images = json_decode($city['gallery_images'], true) ?: [];
        $image_to_delete = $_POST['delete_gallery_image'];
        $gallery_images = array_filter($gallery_images, fn($img) => $img !== $image_to_delete);
        $imageProcessor->deleteImage($image_to_delete);
        $gallery_images_json = json_encode(array_values($gallery_images));
        $db->updateCityExtended($id, $city['name'], $city['province_id'], $city['description'], $city['latitude'], $city['longitude'], $city['hero_image'], $city['google_maps_link'], $gallery_images_json);
        header('Location: citta.php?action=edit&id=' . $id);
        exit;
    }
}

if ($action === 'delete' && $id) {
    $city = $db->getCityById($id);
    if ($city) {
        if ($city['hero_image']) $imageProcessor->deleteImage($city['hero_image']);
        if ($city['gallery_images']) {
            $gallery_images = json_decode($city['gallery_images'], true) ?: [];
            foreach ($gallery_images as $image) $imageProcessor->deleteImage($image);
        }
    }
    $db->deleteCity($id);
    header('Location: citta.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="it"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Gestione Città</title><script src="https://cdn.tailwindcss.com"></script><script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script><style>.form-loading-overlay{position:absolute;top:0;left:0;right:0;bottom:0;background-color:rgba(255,255,255,0.7);z-index:50;display:flex;align-items:center;justify-content:center;border-radius:1rem;}.form-loading-overlay .spinner{width:3rem;height:3rem;border-top:3px solid #3b82f6;border-right:3px solid transparent;border-radius:50%;animation:spin 1s linear infinite;}@keyframes spin{to{transform:rotate(360deg);}}</style></head>
<body class="bg-gray-100">
<div class="flex">
    <?php include 'partials/menu.php'; ?>
    <main class="flex-1 p-6">
        <h1 class="text-2xl font-bold mb-4">Gestione Città</h1>
        <?php if ($action === 'list'): ?>
            <a href="?action=new" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">Nuova Città</a>
            <table class="w-full bg-white rounded shadow">
                <thead><tr><th class="p-4 text-left">Nome</th><th class="p-4 text-left">Azioni</th></tr></thead>
                <tbody>
                <?php foreach ($db->getCities() as $city): ?>
                    <tr>
                        <td class="p-4 border-t"><?php echo htmlspecialchars($city['name']); ?></td>
                        <td class="p-4 border-t"><a href="?action=edit&id=<?php echo $city['id']; ?>" class="text-blue-500">Modifica</a> | <a href="?action=delete&id=<?php echo $city['id']; ?>" onclick="return confirm('Sei sicuro?')" class="text-red-500">Elimina</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($action === 'new' || $action === 'edit'):
            $cityData = ($action === 'edit' && $id) ? $db->getCityById($id) : null;
            $gallery_images = $cityData && $cityData['gallery_images'] ? json_decode($cityData['gallery_images'], true) : [];
        ?>
            <div class="relative max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-6"><?php echo $action === 'edit' ? 'Modifica' : 'Nuova'; ?> Città</h2>
                <form id="city-form" action="?action=<?php echo $action; ?><?php if ($id) echo '&id='.$id; ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div><label for="city_name" class="block font-medium">Nome *</label><input type="text" name="city_name" id="city_name" value="<?php echo htmlspecialchars($cityData['name'] ?? ''); ?>" required class="w-full mt-1 p-2 border rounded"></div>
                    <div><label for="city_province_id" class="block font-medium">Provincia *</label><select name="city_province_id" id="city_province_id" required class="w-full mt-1 p-2 border rounded"><option value="">Seleziona</option><?php foreach ($db->getProvinces() as $p):?><option value="<?php echo $p['id']; ?>"<?php if(isset($cityData['province_id']) && $cityData['province_id'] == $p['id']) echo ' selected';?>><?php echo htmlspecialchars($p['name']);?></option><?php endforeach; ?></select></div>
                    <div><label for="city_description" class="block font-medium">Descrizione</label><textarea name="city_description" id="city_description" rows="4" class="w-full mt-1 p-2 border rounded"><?php echo htmlspecialchars($cityData['description'] ?? ''); ?></textarea></div>
                    <div><label class="block font-medium">Immagine Hero</label><?php if($cityData && $cityData['hero_image']):?><img src="../image-loader.php?path=<?php echo urlencode($cityData['hero_image']); ?>" class="w-32 my-2 rounded border"><?php endif; ?><input type="file" name="hero_image" class="w-full mt-1 p-2 border rounded"></div>
                    <div><label class="block font-medium">Galleria</label><div class="flex flex-wrap gap-4 my-2"><?php if(!empty($gallery_images)): foreach($gallery_images as $img):?><div class="relative"><img src="../image-loader.php?path=<?php echo urlencode($img);?>" class="w-24 h-24 object-cover rounded border"><button type="button" onclick="deleteGalleryImage('<?php echo htmlspecialchars($img);?>')" class="absolute top-0 right-0 bg-red-500 text-white rounded-full w-6 h-6">&times;</button></div><?php endforeach; endif;?></div><input type="file" name="gallery_images[]" multiple class="w-full mt-1 p-2 border rounded"></div>
                    <div class="flex justify-end"><button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded">Salva</button></div>
                </form>
            </div>
        <?php endif; ?>
    </main>
</div>
<form id="deleteImageForm" method="POST" style="display:none;"><input type="hidden" name="delete_gallery_image" id="imageToDelete"></form>
<script>lucide.createIcons();document.getElementById('city-form')?.addEventListener('submit',function(){const o=document.createElement('div');o.className='form-loading-overlay';o.innerHTML='<div class="spinner"></div>';this.parentElement.appendChild(o)});function deleteGalleryImage(p){if(confirm('Sei sicuro di voler eliminare questa immagine?')){document.getElementById('imageToDelete').value=p;document.getElementById('deleteImageForm').submit()}}</script>
</body></html>