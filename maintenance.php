<?php
// maintenance.php

// File di configurazione e flag
$config_file = __DIR__ . '/maintenance_config.json';
$flag_file = __DIR__ . '/maintenance.flag';

// Se la modalità manutenzione NON è attiva (il file flag non esiste),
// reindirizza l'utente alla homepage.
// L'eccezione è se un admin vuole vedere un'anteprima.
if (!file_exists($flag_file) && !isset($_GET['preview'])) {
    header('Location: index.php');
    exit();
}

// Valori di default
$config = [
    'message' => 'Sito in manutenzione. Torneremo presto online!',
    'end_time' => null
];

// Carica la configurazione dal file JSON se esiste
if (file_exists($config_file)) {
    $config = array_merge($config, json_decode(file_get_contents($config_file), true));
}

$end_time_js = $config['end_time'] ? date('Y-m-d\TH:i:s', strtotime($config['end_time'])) : null;

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sito in Manutenzione</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-xl w-full bg-white p-8 rounded-2xl shadow-lg text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-full mb-6">
            <i data-lucide="wrench" class="w-8 h-8 text-yellow-600"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Torneremo Presto Online</h1>
        <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($config['message']); ?></p>

        <?php if ($end_time_js): ?>
        <div id="countdown" class="flex justify-center space-x-4 text-center my-8">
            <div>
                <div id="days" class="text-4xl font-bold text-blue-600">00</div>
                <div class="text-xs text-gray-500">Giorni</div>
            </div>
            <div>
                <div id="hours" class="text-4xl font-bold text-blue-600">00</div>
                <div class="text-xs text-gray-500">Ore</div>
            </div>
            <div>
                <div id="minutes" class="text-4xl font-bold text-blue-600">00</div>
                <div class="text-xs text-gray-500">Minuti</div>
            </div>
            <div>
                <div id="seconds" class="text-4xl font-bold text-blue-600">00</div>
                <div class="text-xs text-gray-500">Secondi</div>
            </div>
        </div>
        <?php endif; ?>

        <div class="mt-8">
            <p class="text-sm text-gray-500">Grazie per la vostra pazienza.</p>
        </div>
    </div>

    <script>
        lucide.createIcons();

        const endTime = <?php echo $end_time_js ? "'" . $end_time_js . "'" : 'null'; ?>;

        if (endTime) {
            const countdownElement = document.getElementById('countdown');
            const daysEl = document.getElementById('days');
            const hoursEl = document.getElementById('hours');
            const minutesEl = document.getElementById('minutes');
            const secondsEl = document.getElementById('seconds');

            const timer = setInterval(() => {
                const now = new Date().getTime();
                const distance = new Date(endTime).getTime() - now;

                if (distance < 0) {
                    clearInterval(timer);
                    countdownElement.innerHTML = '<p class="text-lg font-semibold text-green-600">Dovremmo essere di nuovo online!</p>';
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                daysEl.textContent = String(days).padStart(2, '0');
                hoursEl.textContent = String(hours).padStart(2, '0');
                minutesEl.textContent = String(minutes).padStart(2, '0');
                secondsEl.textContent = String(seconds).padStart(2, '0');

            }, 1000);
        }
    </script>
</body>
</html>
