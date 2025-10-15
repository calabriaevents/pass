<?php
// This file is included by articolo.php
// Variables available: $article, $db

$json_data = json_decode($article['json_data'] ?? '{}', true);
if (!is_array($json_data)) $json_data = [];

$activity_name = htmlspecialchars($json_data['activity_name'] ?? $article['title']);
$logo = $article['logo'] ?? null;
$hero_image = $article['hero_image'] ?? $article['featured_image'];

$p_iva = htmlspecialchars($json_data['p_iva'] ?? '');
$products = nl2br(htmlspecialchars($json_data['products'] ?? ''));
$workshops = nl2br(htmlspecialchars($json_data['workshops'] ?? ''));

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
                <img src="image-loader.php?path=<?php echo urlencode(str_replace('uploads_protected/', '', $hero_image ?? '')); ?>" alt="Hero image for <?php echo $activity_name; ?>" class="absolute inset-0 w-full h-full object-cover">
            <?php endif; ?>
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
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>

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
        <div class="absolute inset-0 flex flex-col justify-center items-center text-center p-8">
            <?php if($logo): ?>
                <img src="image-loader.php?path=<?php echo urlencode(str_replace('uploads_protected/', '', $logo ?? '')); ?>" alt="Logo di <?php echo $activity_name; ?>" class="w-24 h-24 mb-4 object-contain rounded-full bg-white/20 p-2 border-2 border-white/50 shadow-lg">
            <?php endif; ?>
            <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight text-shadow-lg"><?php echo $activity_name; ?></h1>
            <?php if ($article['city_id'] && $article['city_name']): ?>
                <a href="citta-dettaglio.php?id=<?php echo $article['city_id']; ?>" class="mt-6 bg-white text-gray-900 font-bold py-3 px-6 rounded-lg hover:bg-gray-200 transition-colors shadow-md">
                    Visita la città di <?php echo htmlspecialchars($article['city_name']); ?>
                </a>
            <?php endif; ?>
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

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 lg:items-start">
            <!-- Main column -->
            <div class="lg:col-span-2 space-y-12">
                <!-- Description -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Storia della Bottega</h2>
                    <div class="prose max-w-none text-gray-600">
                        <?php echo $description; ?>
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
                </section>

                <!-- Products -->
                <?php if ($products): ?>
                <section>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Prodotti e Creazioni</h2>
                    <div class="prose max-w-none text-gray-600">
                        <?php echo $products; ?>
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
                </section>
                <?php endif; ?>

                 <!-- Workshops -->
                <?php if ($workshops): ?>
                <section>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Mercatini e Workshop</h2>
                    <div class="prose max-w-none text-gray-600">
                        <?php echo $workshops; ?>
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
                </section>
                <?php endif; ?>

                <!-- Gallery -->
                <?php if (!empty($gallery_images)): ?>
                <section>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Galleria</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <?php foreach($gallery_images as $image): ?>
                        <a href="image-loader.php?path=<?php echo urlencode(str_replace('uploads_protected/', '', $image ?? '')); ?>" target="_blank" class="block group">
                            <img src="image-loader.php?path=<?php echo urlencode(str_replace('uploads_protected/', '', $image ?? '')); ?>" alt="Galleria immagine" class="w-full h-40 object-cover rounded-lg group-hover:opacity-80 transition-opacity shadow-md">
                        </a>
                        <?php endforeach; ?>
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
                </section>
                <?php endif; ?>

                 <!-- User Upload Placeholder -->
                <section>
                     <div class="p-6 border-2 border-dashed rounded-lg text-center">
                        <h3 class="text-lg font-semibold text-gray-700">Hai una foto di questo negozio?</h3>
                        <p class="text-gray-500 mt-2 mb-4">Mostra i tuoi acquisti alla community!</p>
                        <button onclick="openUploadModal(<?php echo $article['id']; ?>)" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                            <i data-lucide="camera" class="inline w-4 h-4 mr-2"></i>Condividi la tua esperienza
                        </button>
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
                </section>
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

            <!-- Sidebar / Info column -->
            <aside class="lg:col-span-1 sticky top-8">
                <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Informazioni e Contatti</h2>

                    <div class="space-y-4">
                         <?php if($address): ?>
                        <div class="flex items-start">
                            <i data-lucide="map-pin" class="w-5 h-5 text-gray-500 mr-3 mt-1 flex-shrink-0"></i>
                            <span class="text-gray-700"><?php echo $address; ?></span>
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
                        <?php endif; ?>
                        <?php if(!empty($contact_details['phone'])): ?>
                        <div class="flex items-center">
                            <i data-lucide="phone" class="w-5 h-5 text-gray-500 mr-3"></i>
                            <a href="tel:<?php echo htmlspecialchars($contact_details['phone']); ?>" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($contact_details['phone']); ?></a>
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
                        <?php endif; ?>
                        <?php if(!empty($contact_details['email'])): ?>
                        <div class="flex items-center">
                            <i data-lucide="mail" class="w-5 h-5 text-gray-500 mr-3"></i>
                            <a href="mailto:<?php echo htmlspecialchars($contact_details['email']); ?>" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($contact_details['email']); ?></a>
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
                        <?php endif; ?>
                        <?php if($p_iva): ?>
                        <div class="flex items-start pt-4 border-t mt-4">
                            <i data-lucide="file-text" class="w-5 h-5 text-gray-500 mr-3 mt-1 flex-shrink-0"></i>
                             <div>
                                <span class="font-semibold text-gray-800">P.IVA</span>
                                <p class="text-gray-700"><?php echo $p_iva; ?></p>
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
                        <?php endif; ?>
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

                    <?php if (!empty($article['google_maps_iframe'])): ?>
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Mappa</h3>
                        <?php echo $article['google_maps_iframe']; ?>
                    </div>
                    <?php elseif ($maps_link): ?>
                    <div class="mt-6">
                         <a href="<?php echo $maps_link; ?>" target="_blank" class="w-full bg-blue-600 text-white flex items-center justify-center px-4 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-md">
                            <i data-lucide="map" class="w-5 h-5 mr-2"></i>
                            <span>Vedi sulla Mappa</span>
                        </a>
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
                    <?php endif; ?>
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

                <!-- Include User Experiences Section -->
                <?php
                $article_id = $article['id'];
                $province_id = $article['province_id'] ?? null;
                include __DIR__ . '/../partials/user-experiences.php';
                ?>

                <?php include __DIR__ . '/../partials/reviews.php'; ?>
            </aside>
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
