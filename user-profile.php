<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

// Controlla se l'utente è loggato
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: user-auth.php');
    exit;
}

$db = new Database();
$user_id = $_SESSION['user_id'] ?? null;
$business_id = $_SESSION['business_id'] ?? null;
$message = '';
$error = '';

// Controllo di sicurezza
if (!$user_id || !$business_id) {
    session_destroy();
    header('Location: user-auth.php?error=session_expired');
    exit;
}

// Gestione aggiornamento profilo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    try {
        // Dati Utente
        $user_name = sanitize($_POST['user_name'] ?? '');
        $user_email = filter_var(sanitize($_POST['user_email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // Dati Attività
        $business_name = sanitize($_POST['business_name'] ?? '');
        $business_phone = sanitize($_POST['business_phone'] ?? '');
        $business_website = filter_var(sanitize($_POST['business_website'] ?? ''), FILTER_VALIDATE_URL);
        $business_address = sanitize($_POST['business_address'] ?? '');
        $business_description = sanitize($_POST['business_description'] ?? '');

        if (empty($user_name) || empty($user_email) || empty($business_name)) {
            throw new Exception("Nome utente, email e nome attività sono obbligatori.");
        }

        // Aggiornamento Password (solo se fornita)
        if (!empty($password)) {
            if (strlen($password) < 8) {
                throw new Exception("La nuova password deve essere di almeno 8 caratteri.");
            }
            if ($password !== $password_confirm) {
                throw new Exception("Le password non coincidono.");
            }
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);
        }

        // Aggiornamento Dati Utente
        $stmt = $db->pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$user_name, $user_email, $user_id]);

        // Aggiornamento Dati Attività
        $stmt = $db->pdo->prepare("UPDATE businesses SET name = ?, phone = ?, website = ?, address = ?, description = ? WHERE id = ?");
        $stmt->execute([$business_name, $business_phone, $business_website, $business_address, $business_description, $business_id]);
        
        // Aggiorna i dati in sessione
        $_SESSION['user_name'] = $user_name;
        $_SESSION['user_email'] = $user_email;

        echo json_encode(['success' => true, 'message' => 'Profilo aggiornato con successo!']);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}


// Recupera i dati correnti
$user_data = $db->getUserBusinessData($user_id);
if (!$user_data) {
    die("Impossibile caricare i dati del profilo.");
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Profilo - Passione Calabria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex items-center mb-8">
            <a href="user-dashboard.php" class="text-gray-500 hover:text-gray-800 mr-4">
                <i data-lucide="arrow-left" class="w-6 h-6"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Modifica Profilo</h1>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                ✅ <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="user-profile.php" class="bg-white rounded-lg shadow-sm p-8 space-y-8 ajax-form">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                    <i data-lucide="user" class="w-5 h-5 mr-3 text-blue-600"></i>
                    Dati Account
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="user_name" class="block text-sm font-medium text-gray-700 mb-2">Nome Titolare/Referente</label>
                        <input type="text" id="user_name" name="user_name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label for="user_email" class="block text-sm font-medium text-gray-700 mb-2">Email di Accesso</label>
                        <input type="email" id="user_email" name="user_email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                    <i data-lucide="building-2" class="w-5 h-5 mr-3 text-blue-600"></i>
                    Dati Attività
                </h2>
                <div class="space-y-6">
                    <div>
                        <label for="business_name" class="block text-sm font-medium text-gray-700 mb-2">Nome Attività</label>
                        <input type="text" id="business_name" name="business_name" value="<?php echo htmlspecialchars($user_data['business_name']); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="business_phone" class="block text-sm font-medium text-gray-700 mb-2">Telefono</label>
                            <input type="tel" id="business_phone" name="business_phone" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label for="business_website" class="block text-sm font-medium text-gray-700 mb-2">Sito Web</label>
                            <input type="url" id="business_website" name="business_website" value="<?php echo htmlspecialchars($user_data['website'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                     <div>
                        <label for="business_address" class="block text-sm font-medium text-gray-700 mb-2">Indirizzo</label>
                        <input type="text" id="business_address" name="business_address" value="<?php echo htmlspecialchars($user_data['address'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label for="business_description" class="block text-sm font-medium text-gray-700 mb-2">Descrizione</label>
                        <textarea id="business_description" name="business_description" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-lg"><?php echo htmlspecialchars($user_data['description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                    <i data-lucide="lock" class="w-5 h-5 mr-3 text-blue-600"></i>
                    Modifica Password
                </h2>
                <div class="bg-blue-50 p-4 rounded-lg">
                     <p class="text-sm text-blue-700 mb-4">Lascia i seguenti campi vuoti se non desideri modificare la password.</p>
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Nuova Password</label>
                            <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-2">Conferma Nuova Password</label>
                            <input type="password" id="password_confirm" name="password_confirm" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-200 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                    <i data-lucide="save" class="w-5 h-5 inline-block -mt-1 mr-2"></i>
                    Salva Modifiche
                </button>
            </div>
        </form>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>