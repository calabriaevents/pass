<?php
// Assumiamo che $db sia già stato istanziato nel file che include questo parziale.
// e che $article_id o $province_id siano disponibili.

$user_uploads = [];
if (isset($article_id)) {
    $user_uploads = $db->getUserUploadsByArticle($article_id);
} elseif (isset($province_id)) {
    // Funzione da creare in database_mysql.php se non esiste
    // $user_uploads = $db->getUserUploadsByProvince($province_id);
}
?>

<?php if (!empty($user_uploads)): ?>
<section class="mt-12">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Esperienze dei Visitatori</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($user_uploads as $upload): ?>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <img src="image-loader.php?path=<?= urlencode($upload['image_path']) ?>"
                     alt="<?= htmlspecialchars($upload['description']) ?>"
                     class="w-full h-48 object-cover">
                <div class="p-4">
                    <p class="text-gray-700">"<?= htmlspecialchars($upload['description']) ?>"</p>
                    <p class="text-sm text-gray-500 mt-2 font-semibold">- <?= htmlspecialchars($upload['user_name']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>