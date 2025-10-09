<?php
/**
 * Sezione Esperienze dei Visitatori
 * Mostra le foto approvate degli utenti per un articolo o provincia
 */

// Validazione parametri
if ((empty($article_id) && empty($province_id)) || !isset($db) || !$db->isConnected()) {
    return; // Non mostrare nulla se i dati necessari mancano
}

// Query per ottenere le esperienze approvate
$params = [];
$sql = "
    SELECT u.*, 
           a.title as article_title, 
           a.slug as article_slug,
           p.name as province_name
    FROM user_uploads u
    LEFT JOIN articles a ON u.article_id = a.id
    LEFT JOIN provinces p ON u.province_id = p.id
    WHERE u.status = 'approved'
";

if (!empty($article_id)) {
    $sql .= " AND u.article_id = ?";
    $params[] = intval($article_id);
}

if (!empty($province_id)) {
    $sql .= " AND (u.province_id = ? OR a.province_id = ?)";
    $params[] = intval($province_id);
    $params[] = intval($province_id);
}

$sql .= " ORDER BY u.created_at DESC LIMIT 12";

try {
    $stmt = $db->pdo->prepare($sql);
    $stmt->execute($params);
    $experiences = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Errore nel caricare le esperienze utente: " . $e->getMessage());
    $experiences = [];
}

// Se non ci sono esperienze, non mostrare la sezione
if (empty($experiences)) {
    return;
}

$total_count = count($experiences); // Semplificato per ora, si può aggiungere query di conteggio totale se necessario

?>

<section class="py-16 bg-gray-50" id="user-experiences">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full mb-6">
                <i data-lucide="camera" class="w-8 h-8 text-white"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">
                📸 Le Esperienze dei Visitatori
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Scopri le foto e le storie di chi ha già vissuto questa esperienza
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            <?php foreach ($experiences as $experience): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 group">
                <div class="relative aspect-w-4 aspect-h-3 overflow-hidden cursor-pointer" onclick="openExperienceModal(<?php echo htmlspecialchars(json_encode($experience)); ?>)">
                    <img src="image-loader.php?path=<?php echo urlencode(str_replace('uploads_protected/', '', $experience['image_path'] ?? '')); ?>"
                         alt="Esperienza di <?php echo htmlspecialchars($experience['user_name']); ?>"
                         class="w-full h-48 object-cover group-hover:scale-110 transition-transform duration-500"
                         loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="absolute bottom-4 left-4 right-4">
                            <p class="text-white font-semibold text-sm truncate">
                                📷 <?php echo htmlspecialchars($experience['user_name']); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-sm text-gray-700 leading-relaxed line-clamp-3">
                        <?php echo htmlspecialchars($experience['description']); ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_count > 12): ?>
        <div class="text-center">
            <a href="#" class="inline-flex items-center px-6 py-3 border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white font-medium rounded-lg transition-all duration-200">
                Vedi Tutte le <?php echo $total_count; ?> Esperienze
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<div id="experienceModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="relative">
            <button onclick="closeExperienceModal()" 
                    class="absolute top-4 right-4 z-10 w-8 h-8 bg-white/80 hover:bg-white rounded-full flex items-center justify-center text-gray-600 hover:text-gray-800 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <div id="experienceModalContent">
                </div>
        </div>
    </div>
</div>

<script>
// Experience Modal Functions
function openExperienceModal(experience) {
    const modal = document.getElementById('experienceModal');
    const content = document.getElementById('experienceModalContent');
    
    content.innerHTML = `
        <div class="relative">
            <img src="image-loader.php?path=${encodeURIComponent((experience.image_path || '').replace('uploads_protected/', ''))}"
                 alt="Esperienza di ${experience.user_name}"
                 class="w-full h-64 md:h-80 object-cover rounded-t-2xl">
            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-6">
                <div class="flex items-center text-white">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                        ${experience.user_name.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">${experience.user_name}</h3>
                        <p class="text-white/80 text-sm">${new Date(experience.created_at).toLocaleDateString('it-IT', {
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric'
                        })}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-6">
            ${experience.article_title ? `
            <div class="mb-4">
                <a href="articolo.php?slug=${experience.article_slug}" class="text-sm text-blue-600 font-medium hover:underline">📄 ${experience.article_title}</a>
            </div>
            ` : ''}
            <div class="prose prose-gray max-w-none">
                <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">${experience.description}</p>
            </div>
            <div class="mt-6 pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    Foto condivisa il ${new Date(experience.created_at).toLocaleDateString('it-IT')} 
                    ${experience.province_name ? `da ${experience.province_name}` : ''}
                </p>
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    lucide.createIcons(); // Ricrea le icone se necessario
}

function closeExperienceModal() {
    const modal = document.getElementById('experienceModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Event listeners per chiudere il modal
document.getElementById('experienceModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeExperienceModal();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('experienceModal').classList.contains('hidden')) {
        closeExperienceModal();
    }
});
</script>