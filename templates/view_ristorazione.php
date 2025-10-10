<?php
// This file is included by articolo.php
// Variables available: $article, $db

// Decode JSON data for easy access
$json_data = json_decode($article['json_data'] ?? '{}', true);
if (!is_array($json_data)) $json_data = [];

// --- Extract data with fallbacks ---
$activity_name = htmlspecialchars($json_data['activity_name'] ?? $article['title']);
$logo = $article['logo'] ?? null;
$hero_image = $article['hero_image'] ?? $article['featured_image'];

$p_iva = htmlspecialchars($json_data['p_iva'] ?? '');
$cuisine_type = htmlspecialchars($json_data['cuisine_type'] ?? '');
$max_seats = htmlspecialchars($json_data['max_seats'] ?? '');
$atmosphere = htmlspecialchars($json_data['atmosphere'] ?? '');
$opening_hours_raw = $json_data['opening_hours'] ?? '';
$menu_pdf_path = $json_data['menu_pdf_path'] ?? null;
$menu_digital_link = htmlspecialchars($json_data['menu_digital_link'] ?? '');

$address = htmlspecialchars($json_data['address'] ?? '');
$maps_link = htmlspecialchars($json_data['maps_link'] ?? '');
$contact_details = $json_data['contact_details'] ?? [];

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
                <img src="/<?php echo htmlspecialchars($logo); ?>" alt="Logo di <?php echo $activity_name; ?>" class="w-24 h-24 mb-4 object-contain rounded-full bg-white/20 p-2 border-2 border-white/50 shadow-lg">
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
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Il Ristorante</h2>
                    <div class="prose max-w-none text-gray-600">
                        <?php echo $description; ?>
                    </div>
                </section>

                <div class="bg-white p-6 rounded-lg shadow-md mt-6">
                    <h3 class="text-xl font-bold mb-4 text-gray-800">Dettagli Ristorante</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php if (!empty($json_data['address'])): ?>
                            <div>
                                <h4 class="font-semibold text-gray-700">Indirizzo</h4>
                                <p class="text-gray-600"><?php echo htmlspecialchars($json_data['address']); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($json_data['opening_hours'])): ?>
                            <div>
                                <h4 class="font-semibold text-gray-700">Orari di Apertura</h4>
                                <p class="text-gray-600 whitespace-pre-line"><?php echo htmlspecialchars($json_data['opening_hours']); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($json_data['cuisine_type'])): ?>
                            <div>
                                <h4 class="font-semibold text-gray-700">Tipo di Cucina</h4>
                                <p class="text-gray-600"><?php echo htmlspecialchars($json_data['cuisine_type']); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($json_data['price_range'])): ?>
                            <div>
                                <h4 class="font-semibold text-gray-700">Fascia di Prezzo</h4>
                                <p class="text-gray-600"><?php echo htmlspecialchars($json_data['price_range']); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php
                        $services = $json_data['services']['predefined'] ?? [];
                        if (!empty($json_data['services']['custom'])) {
                            $custom_services = array_map('trim', explode(',', $json_data['services']['custom']));
                            $services = array_merge($services, $custom_services);
                        }
                        ?>
                        <?php if (!empty($services)): ?>
                            <div>
                                <h4 class="font-semibold text-gray-700">Servizi Offerti</h4>
                                <ul class="list-disc list-inside text-gray-600">
                                    <?php foreach ($services as $service): ?>
                                        <li><?php echo htmlspecialchars($service); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($json_data['contact_details']['phone']) || !empty($json_data['contact_details']['email'])): ?>
                            <div>
                                <h4 class="font-semibold text-gray-700">Contatti</h4>
                                <?php if (!empty($json_data['contact_details']['phone'])): ?>
                                    <p class="text-gray-600"><strong>Telefono:</strong> <?php echo htmlspecialchars($json_data['contact_details']['phone']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($json_data['contact_details']['email'])): ?>
                                    <p class="text-gray-600"><strong>Email:</strong> <?php echo htmlspecialchars($json_data['contact_details']['email']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($json_data['menu_pdf_path'])): ?>
                             <div>
                                <h4 class="font-semibold text-gray-700">Menu</h4>
                                <a href="<?php echo SITE_URL . '/' . htmlspecialchars($json_data['menu_pdf_path']); ?>" target="_blank" class="text-blue-600 hover:underline">Visualizza il Menu (PDF)</a>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($json_data['maps_link'])): ?>
                            <div>
                                <h4 class="font-semibold text-gray-700">Mappa</h4>
                                <a href="<?php echo htmlspecialchars($json_data['maps_link']); ?>" target="_blank" class="text-blue-600 hover:underline">Vedi su Google Maps</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Menu -->
                <?php if ($menu_pdf_path || $menu_digital_link): ?>
                <section>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Menù e Specialità</h2>
                    <div class="flex items-center space-x-4">
                        <?php if($menu_pdf_path): ?>
                        <a href="/<?php echo htmlspecialchars($menu_pdf_path); ?>" target="_blank" class="bg-red-600 text-white flex items-center justify-center px-4 py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors">
                            <i data-lucide="file-text" class="w-5 h-5 mr-2"></i>
                            <span>Visualizza Menù PDF</span>
                        </a>
                        <?php endif; ?>
                        <?php if($menu_digital_link): ?>
                        <a href="<?php echo $menu_digital_link; ?>" target="_blank" class="bg-gray-800 text-white flex items-center justify-center px-4 py-3 rounded-lg font-semibold hover:bg-gray-900 transition-colors">
                            <i data-lucide="qr-code" class="w-5 h-5 mr-2"></i>
                            <span>Menù Digitale</span>
                        </a>
                        <?php endif; ?>
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

                <!-- Pulsante Condividi Esperienza (se nessuna hero image) -->
                <?php if (!$hero_image): ?>
                <section class="text-center">
                    <button onclick="openUploadModal(<?php echo $article['id']; ?>)" 
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-lg font-medium shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                        <i data-lucide="camera" class="w-5 h-5 mr-2"></i>
                        Condividi la Tua Esperienza
                    </button>
                    <p class="mt-2 text-sm text-gray-500">
                        Hai mangiato qui? Carica la tua foto e racconta la tua esperienza culinaria!
                    </p>
                </section>
                <?php endif; ?>
            </div>

            <!-- Sidebar / Info column -->
            <aside class="lg:col-span-1 sticky top-8">
                <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Informazioni Pratiche</h2>

                    <div class="space-y-4">
                        <?php if($cuisine_type): ?>
                        <div class="flex items-start">
                            <i data-lucide="utensils-crossed" class="w-5 h-5 text-gray-500 mr-3 mt-1 flex-shrink-0"></i>
                            <div>
                                <span class="font-semibold text-gray-800">Tipo Cucina</span>
                                <p class="text-gray-700"><?php echo $cuisine_type; ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if($atmosphere): ?>
                        <div class="flex items-start">
                            <i data-lucide="smile" class="w-5 h-5 text-gray-500 mr-3 mt-1 flex-shrink-0"></i>
                             <div>
                                <span class="font-semibold text-gray-800">Atmosfera</span>
                                <p class="text-gray-700"><?php echo $atmosphere; ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if($max_seats): ?>
                        <div class="flex items-start">
                            <i data-lucide="users" class="w-5 h-5 text-gray-500 mr-3 mt-1 flex-shrink-0"></i>
                             <div>
                                <span class="font-semibold text-gray-800">Coperti</span>
                                <p class="text-gray-700"><?php echo $max_seats; ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if(!empty($opening_hours_raw)): ?>
                        <div class="flex items-start">
                            <i data-lucide="clock" class="w-5 h-5 text-gray-500 mr-3 mt-1 flex-shrink-0"></i>
                             <div>
                                <span class="font-semibold text-gray-800">Orari</span>
                                <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($opening_hours_raw); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                         <?php if($p_iva): ?>
                        <div class="flex items-start pt-4 border-t mt-4">
                            <i data-lucide="file-text" class="w-5 h-5 text-gray-500 mr-3 mt-1 flex-shrink-0"></i>
                             <div>
                                <span class="font-semibold text-gray-800">P.IVA</span>
                                <p class="text-gray-700"><?php echo $p_iva; ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-8 bg-gray-50 p-6 rounded-lg shadow-sm">
                     <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Contatti e Prenotazioni</h2>
                     <div class="space-y-3">
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
