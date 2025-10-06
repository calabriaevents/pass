<?php
// This file is included by articolo.php
// Variables available: $article, $db

// Decode JSON data for easy access
$json_data = json_decode($article['json_data'] ?? '{}', true);
if (!is_array($json_data)) $json_data = [];

// --- Extract data with fallbacks ---
// Hero and main identity
$activity_name = htmlspecialchars($json_data['activity_name'] ?? $article['title']);
$logo = $article['logo'] ?? null;
$hero_image = $article['hero_image'] ?? $article['featured_image']; // Fallback to featured image

// Content
$description = nl2br(htmlspecialchars($article['content'] ?? ''));

// Location & Contacts
$address = htmlspecialchars($json_data['address'] ?? '');
$maps_link = htmlspecialchars($json_data['maps_link'] ?? '');
$contact_details = $json_data['contact_details'] ?? [];

// Practical Details
$average_price = htmlspecialchars($json_data['average_price'] ?? '');
$room_details_raw = $json_data['room_details'] ?? '';

// Services
$services = $json_data['services'] ?? ['predefined' => [], 'custom' => ''];
$all_services = $services['predefined'] ?? [];
if (!empty($services['custom'])) {
    $custom_services = array_map('trim', explode(',', $services['custom']));
    $all_services = array_merge($all_services, $custom_services);
}

// Gallery
$gallery_images = json_decode($article['gallery_images'] ?? '[]', true);

?>

<div class="bg-white">
    <!-- Hero Section -->
    <div class="relative bg-gray-800 text-white">
        <div class="h-96 md:h-[500px] w-full">
            <?php if ($hero_image): ?>
                <img src="image-proxy.php?file=<?php echo htmlspecialchars($hero_image); ?>" alt="Hero image for <?php echo $activity_name; ?>" class="absolute inset-0 w-full h-full object-cover">
            <?php endif; ?>
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
        
        <!-- Pulsante Città (overlay top-right) -->
        <?php if (!empty($article['city_id']) && !empty($article['city_name'])): ?>
        <div class="absolute top-4 right-4">
            <a href="citta-dettaglio.php?id=<?php echo $article['city_id']; ?>" 
               class="inline-flex items-center px-4 py-2 bg-white/90 hover:bg-white backdrop-blur-sm text-gray-900 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 font-medium">
                <i data-lucide="map-pin" class="w-4 h-4 mr-2 text-blue-600"></i>
                <?php echo htmlspecialchars($article['city_name']); ?>
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Pulsante Condividi Esperienza (overlay bottom-left) -->
        <div class="absolute bottom-4 left-4">
            <button onclick="openUploadModal(<?php echo $article['id']; ?>)" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600/90 hover:bg-blue-700 backdrop-blur-sm text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 font-medium">
                <i data-lucide="camera" class="w-4 h-4 mr-2"></i>
                Condividi la tua foto
            </button>
        </div>
        
        <div class="absolute inset-0 flex flex-col justify-center items-center text-center p-8">
            <?php if($logo): ?>
                <img src="image-proxy.php?file=<?php echo htmlspecialchars($logo); ?>" alt="Logo di <?php echo $activity_name; ?>" class="w-24 h-24 mb-4 object-contain rounded-full bg-white/20 p-2 border-2 border-white/50 shadow-lg">
            <?php endif; ?>
            <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight text-shadow-lg"><?php echo $activity_name; ?></h1>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 lg:items-start">
            <!-- Main column -->
            <div class="lg:col-span-2 space-y-12">
                <!-- Description -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Storia e Unicità</h2>
                    <div class="prose max-w-none text-gray-600">
                        <?php echo $description; ?>
                    </div>
                </section>

                <!-- Services -->
                <?php if (!empty($all_services)): ?>
                <section>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Servizi</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4">
                        <?php foreach($all_services as $service): ?>
                        <div class="flex items-center">
                            <i data-lucide="check-circle-2" class="w-5 h-5 text-green-500 mr-2 flex-shrink-0"></i>
                            <span class="text-gray-700"><?php echo htmlspecialchars($service); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Rooms -->
                <?php if(!empty($room_details_raw)): ?>
                <section>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Camere e Prezzi</h2>
                    <div class="space-y-4">
                    <?php
                        $rooms = explode("\n", trim($room_details_raw));
                        foreach($rooms as $room):
                            $parts = explode(':', $room, 3);
                            $room_name = htmlspecialchars(trim($parts[0]));
                            $room_price = isset($parts[1]) ? htmlspecialchars(trim($parts[1])) : '';
                            $room_desc = isset($parts[2]) ? htmlspecialchars(trim($parts[2])) : '';
                    ?>
                        <div class="p-4 border rounded-lg bg-gray-50 flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-gray-800"><?php echo $room_name; ?></h3>
                                <?php if($room_desc): ?>
                                <p class="text-gray-600 mt-1 text-sm"><?php echo $room_desc; ?></p>
                                <?php endif; ?>
                            </div>
                             <?php if($room_price): ?>
                            <div class="text-lg font-semibold text-blue-600 bg-blue-100 px-3 py-1 rounded-full text-center ml-4 flex-shrink-0">
                                <?php echo $room_price; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Gallery -->
                <?php if (!empty($gallery_images)): ?>
                <section>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Galleria</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <?php foreach($gallery_images as $image): ?>
                        <a href="/<?php echo htmlspecialchars($image); ?>" target="_blank" class="block group">
                            <img src="image-proxy.php?file=<?php echo htmlspecialchars($image); ?>" alt="Galleria immagine" class="w-full h-40 object-cover rounded-lg group-hover:opacity-80 transition-opacity shadow-md">
                        </a>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Pulsante Condividi Esperienza (se nessuna hero image) -->
                <?php if (!$hero_image): ?>
                <section class="text-center">
                    <button onclick="openUploadModal(<?php echo $article['id']; ?>)" 
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-lg font-medium shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                        <i data-lucide="camera" class="w-5 h-5 mr-2"></i>
                        Condividi la Tua Esperienza
                    </button>
                    <p class="mt-2 text-sm text-gray-500">
                        Hai soggiornato qui? Carica la tua foto e racconta la tua esperienza!
                    </p>
                </section>
                <?php endif; ?>

            </div>

            <!-- Sidebar / Info column -->
            <aside class="lg:col-span-1 sticky top-8">
                <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Informazioni</h2>

                    <div class="space-y-4">
                        <?php if($address): ?>
                        <div class="flex items-start">
                            <i data-lucide="map-pin" class="w-5 h-5 text-gray-500 mr-3 mt-1 flex-shrink-0"></i>
                            <span class="text-gray-700"><?php echo $address; ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($contact_details['phone'])): ?>
                        <div class="flex items-center">
                            <i data-lucide="phone" class="w-5 h-5 text-gray-500 mr-3"></i>
                            <a href="tel:<?php echo htmlspecialchars($contact_details['phone']); ?>" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($contact_details['phone']); ?></a>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($contact_details['email'])): ?>
                        <div class="flex items-center">
                            <i data-lucide="mail" class="w-5 h-5 text-gray-500 mr-3"></i>
                            <a href="mailto:<?php echo htmlspecialchars($contact_details['email']); ?>" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($contact_details['email']); ?></a>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($contact_details['website'])): ?>
                        <div class="flex items-center">
                            <i data-lucide="globe" class="w-5 h-5 text-gray-500 mr-3"></i>
                            <a href="<?php echo htmlspecialchars($contact_details['website']); ?>" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline truncate"><?php echo htmlspecialchars($contact_details['website']); ?></a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if($maps_link): ?>
                    <div class="mt-6">
                         <a href="<?php echo $maps_link; ?>" target="_blank" class="w-full bg-blue-600 text-white flex items-center justify-center px-4 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-md">
                            <i data-lucide="map" class="w-5 h-5 mr-2"></i>
                            <span>Apri in Google Maps</span>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <?php include __DIR__ . '/../partials/reviews.php'; ?>
            </aside>
        </div>
    </main>
</div>

<!-- Include User Experiences Section -->
<?php
$article_id = $article['id'];
$province_id = $article['province_id'] ?? null;
include __DIR__ . '/../partials/user-experiences.php';
?>

<!-- Include User Upload Modal -->
<?php include __DIR__ . '/../partials/user-upload-modal.php'; ?>

<!-- Initialize Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize upload modal
    if (typeof UserUploadModal !== 'undefined') {
        UserUploadModal.init();
    }
    
    // Create Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
