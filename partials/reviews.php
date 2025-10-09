<?php
// This partial is included in the article view templates.
// It needs the $article variable to be available.
// A $db object should also be available.

// Questa riga è corretta e recupera solo i commenti approvati.
$approved_comments = $db->getApprovedCommentsByArticleId($article['id']);
$average_rating = 0;
if (count($approved_comments) > 0) {
    $total_rating = 0;
    foreach ($approved_comments as $comment) {
        $total_rating += $comment['rating'];
    }
    $average_rating = round($total_rating / count($approved_comments), 1);
}
?>
<section id="reviews" class="mt-12">
    <div class="bg-gray-50 p-6 rounded-lg shadow-inner">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4">Recensioni e Commenti</h2>

        <?php if (count($approved_comments) > 0): ?>
        <div class="flex items-center mb-6">
            <div class="flex items-center">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i data-lucide="star" class="w-6 h-6 <?php echo ($i <= $average_rating) ? 'text-yellow-400 fill-current' : 'text-gray-300'; ?>"></i>
                <?php endfor; ?>
            </div>
            <p class="ml-4 text-lg text-gray-700"><strong class="font-semibold"><?php echo $average_rating; ?></strong> su 5 stelle</p>
            <p class="ml-2 text-gray-500">(basato su <?php echo count($approved_comments); ?> recensioni)</p>
        </div>
        <?php endif; ?>

        <div class="space-y-6 mb-8">
            <?php if (empty($approved_comments)): ?>
                <div class="text-center py-8 bg-gray-100 rounded-lg">
                    <i data-lucide="message-square" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                    <p class="text-gray-600 font-semibold">Non ci sono ancora recensioni.</p>
                    <p class="text-gray-500 text-sm">Sii il primo a lasciare un commento!</p>
                </div>
            <?php else: ?>
                <?php foreach($approved_comments as $comment): ?>
                <div class="border-b pb-4">
                    <div class="flex items-center mb-2">
                        <div class="flex items-center">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <i data-lucide="star" class="w-4 h-4 <?php echo ($i < $comment['rating']) ? 'text-yellow-400 fill-current' : 'text-gray-300'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="ml-3 font-semibold text-gray-800"><?php echo htmlspecialchars($comment['author_name']); ?></p>
                    </div>
                    <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                    <p class="text-xs text-gray-400 mt-2"><?php echo date('d M Y', strtotime($comment['created_at'])); ?></p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="pt-8 border-t border-gray-200">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Lascia la tua recensione</h3>
            <form id="review-form" class="space-y-4">
                <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="author_name" class="block text-sm font-medium text-gray-700">Nome</label>
                        <input type="text" name="author_name" id="author_name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="author_email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="author_email" id="author_email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">La tua valutazione</label>
                    <div class="flex items-center mt-1" id="star-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i data-lucide="star" class="star w-6 h-6 text-gray-300 cursor-pointer" data-value="<?php echo $i; ?>"></i>
                        <?php endfor; ?>
                        <input type="hidden" name="rating" id="rating" value="0">
                    </div>
                </div>
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700">La tua recensione</label>
                    <textarea name="content" id="content" rows="4" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <div>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Invia Recensione
                    </button>
                </div>
                <div id="form-message" class="text-sm mt-2"></div>
            </form>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ratingInput = document.getElementById('rating');
    const starRatingContainer = document.getElementById('star-rating');

    starRatingContainer.addEventListener('click', function(event) {
        // Use closest to handle clicks on SVG paths inside the icon
        const star = event.target.closest('.star');
        if (!star || !star.dataset.value) return;

        const value = parseInt(star.dataset.value);
        ratingInput.value = value;

        const allStars = starRatingContainer.querySelectorAll('.star');
        allStars.forEach((s) => {
            if (parseInt(s.dataset.value) <= value) {
                s.classList.add('text-yellow-400');
                s.style.fill = 'currentColor';
            } else {
                s.classList.remove('text-yellow-400');
                s.style.fill = 'none';
            }
        });
    });

    const form = document.getElementById('review-form');
    const messageDiv = document.getElementById('form-message');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        if (parseInt(data.rating) === 0) {
            messageDiv.textContent = 'Per favore, seleziona una valutazione a stelle.';
            messageDiv.className = 'text-red-600';
            return;
        }

        messageDiv.textContent = 'Invio in corso...';
        messageDiv.className = 'text-gray-600';

        fetch('api/submit_review.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                messageDiv.textContent = result.message;
                messageDiv.className = 'text-green-600';
                form.reset();
                ratingInput.value = '0'; // Also reset the hidden input
                // Reset stars
                const allStars = starRatingContainer.querySelectorAll('.star');
                allStars.forEach(s => {
                    s.classList.remove('text-yellow-400');
                    s.style.fill = 'none';
                });
            } else {
                messageDiv.textContent = result.message || 'Si è verificato un errore.';
                messageDiv.className = 'text-red-600';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.textContent = 'Errore di connessione. Riprova più tardi.';
            messageDiv.className = 'text-red-600';
        });
    });
});
</script>