<?php
// This file is included by articolo.php
// Variables available: $article, $db

$json_data = json_decode($article['json_data'] ?? '{}', true);
if (!is_array($json_data)) $json_data = [];

$activity_name = htmlspecialchars($json_data['activity_name'] ?? $article['title']);
$logo = $article['logo'] ?? null;
$hero_image = $article['hero_image'] ?? $article['featured_image'];

$stops = nl2br(htmlspecialchars($json_data['stops'] ?? ''));

$services = $json_data['services'] ?? ['predefined' => [], 'custom' => ''];
$all_services = $services['predefined'] ?? [];
if (!empty($services['custom'])) {
    $custom_services = array_map('trim', explode(',', $services['custom']));
    $all_services = array_merge($all_services, $custom_services);
}

$description = nl2br(htmlspecialchars($article['content'] ?? ''));
$gallery_images = json_decode($article['gallery_images'] ?? '[]', true);
?>

<div class="bg-white">
    <!-- Hero Section -->
    <div class="relative bg-gray-800 text-white">
        <div class="h-96 md:h-[500px] w-full">
            <?php if ($hero_image): ?>
                <img src="/<?php echo htmlspecialchars($hero_image); ?>" alt="Hero image for <?php echo $activity_name; ?>" class="absolute inset-0 w-full h-full object-cover">
            <?php endif; ?>
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
        <div class="absolute inset-0 flex flex-col justify-center items-center text-center p-8">
            <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight text-shadow-lg"><?php echo $activity_name; ?></h1>
            <p class="text-xl mt-2 text-white/90">Un percorso indimenticabile in Calabria</p>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 lg:items-start">
            <!-- Main column -->
            <div class="lg:col-span-2 space-y-12">
                <!-- Description -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Descrizione dell'Itinerario</h2>
                    <div class="prose max-w-none text-gray-600">
                        <?php echo $description; ?>
                    </div>
                </section>

                <div class="bg-white p-6 rounded-lg shadow-md mt-6">
                    <h3 class="text-xl font-bold mb-4 text-gray-800">Dettagli Itinerario</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php if (!empty($json_data['starting_point'])): ?>
                            <div>
                                <h4 class="font-semibold text-gray-700">Punto di Partenza</h4>
                                <p class="text-gray-600"><?php echo htmlspecialchars($json_data['starting_point']); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($json_data['length_km'])): ?>
                            <div>
                                <h4 class="font-semibold text-gray-700">Lunghezza</h4>
                                <p class="text-gray-600"><?php echo htmlspecialchars($json_data['length_km']); ?> km</p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($json_data['duration_hours'])): ?>
                            <div>
                                <h4 class="font-semibold text-gray-700">Durata Stimata</h4>
                                <p class="text-gray-600"><?php echo htmlspecialchars($json_data['duration_hours']); ?> ore</p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($json_data['difficulty'])): ?>
                            <div>
                                <h4 class="font-semibold text-gray-700">Difficoltà</h4>
                                <p class="text-gray-600"><?php echo htmlspecialchars($json_data['difficulty']); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($json_data['maps_link'])): ?>
                            <div class="md:col-span-2">
                                <h4 class="font-semibold text-gray-700">Mappa del Percorso</h4>
                                <a href="<?php echo htmlspecialchars($json_data['maps_link']); ?>" target="_blank" class="text-blue-600 hover:underline">Visualizza il percorso su Google Maps</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Stops -->
                <?php if ($stops): ?>
                <section>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Tappe Principali</h2>
                    <div class="prose max-w-none text-gray-600">
                        <?php echo $stops; ?>
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
                            <img src="/<?php echo htmlspecialchars($image); ?>" alt="Galleria immagine" class="w-full h-40 object-cover rounded-lg group-hover:opacity-80 transition-opacity shadow-md">
                        </a>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

                 <!-- User Upload Placeholder -->
                <section>
                     <div class="p-6 border-2 border-dashed rounded-lg text-center">
                        <h3 class="text-lg font-semibold text-gray-700">Hai percorso questo itinerario?</h3>
                        <p class="text-gray-500 mt-2 mb-4">Condividi la tua esperienza! Carica una foto.</p>
                        <button onclick="openUploadModal(<?php echo $article['id']; ?>)" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                            <i data-lucide="camera" class="inline w-4 h-4 mr-2"></i>Condividi la tua esperienza
                        </button>
                    </div>
                </section>
            </div>

            <!-- Sidebar / Info column -->
            <aside class="lg:col-span-1 sticky top-8">
                <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Dettagli</h2>
                    <div class="space-y-4">
                        <?php if (!empty($all_services)): ?>
                        <div class="flex items-start">
                            <i data-lucide="concierge-bell" class="w-5 h-5 text-gray-500 mr-3 mt-1 flex-shrink-0"></i>
                             <div>
                                <span class="font-semibold text-gray-800">Servizi Disponibili</span>
                                <ul class="list-disc list-inside text-gray-700 mt-1">
                                <?php foreach($all_services as $service): ?>
                                    <li><?php echo htmlspecialchars($service); ?></li>
                                <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Include User Experiences Section -->
                <?php
                $article_id = $article['id'];
                $province_id = $article['province_id'] ?? null;
                include __DIR__ . '/../partials/user-experiences.php';
                ?>

                <?php include __DIR__ . '/../partials/reviews.php'; ?>
            </aside>
        </div>
    </main>
</div>

<!-- Include User Upload Modal -->
<?php include __DIR__ . '/../partials/user-upload-modal.php'; ?>

<!-- Initialize UserUploadModal -->
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