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

$total_count = count($experiences);
?>

<section class="py-16 bg-gray-50" id="user-experiences">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">
                📸 Le Esperienze dei Visitatori
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            <?php foreach ($experiences as $experience): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden group">
                <div class="relative aspect-w-4 aspect-h-3 overflow-hidden cursor-pointer" onclick="openExperienceModal(<?php echo htmlspecialchars(json_encode($experience)); ?>)">
                    <img src="image-loader.php?path=<?php echo urlencode($experience['image_path']); ?>"
                         alt="Esperienza di <?php echo htmlspecialchars($experience['user_name']); ?>"
                         class="w-full h-48 object-cover group-hover:scale-110 transition-transform duration-500"
                         loading="lazy">
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div id="experienceModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="relative">
            <button onclick="closeExperienceModal()" 
                    class="absolute top-4 right-4 z-10 w-8 h-8 bg-white/80 hover:bg-white rounded-full flex items-center justify-center text-gray-600 hover:text-gray-800 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <div id="experienceModalContent"></div>
        </div>
    </div>
</div>

<script>
function openExperienceModal(experience) {
    const modal = document.getElementById('experienceModal');
    const content = document.getElementById('experienceModalContent');
    const imageUrl = `image-loader.php?path=${encodeURIComponent(experience.image_path)}`;
    
    content.innerHTML = `
        <div class="relative">
            <img src="${imageUrl}"
                 alt="Esperienza di ${experience.user_name}"
                 class="w-full h-64 md:h-80 object-cover rounded-t-2xl">
        </div>
        <div class="p-6">
            <p class="text-gray-700 leading-relaxed">${experience.description}</p>
        </div>
    `;
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    lucide.createIcons();
}

function closeExperienceModal() {
    const modal = document.getElementById('experienceModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

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