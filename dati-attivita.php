<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

$error = '';
$success = '';
$selected_package = null;
$selected_package_id = null;

// Ottieni il pacchetto selezionato dalla sessione o URL
if (isset($_GET['package_id']) && !empty($_GET['package_id'])) {
    $package_id = (int)$_GET['package_id'];
    $_SESSION['selected_package_id'] = $package_id;
} elseif (isset($_SESSION['selected_package_id'])) {
    $package_id = $_SESSION['selected_package_id'];
} else {
    // Reindirizza alla pagina di selezione pacchetti se non c'è pacchetto selezionato
    header('Location: iscrizione-attivita.php');
    exit;
}

// Carica informazioni del pacchetto selezionato
try {
    $db = new Database();
    $stmt = $db->pdo->prepare("SELECT * FROM business_packages WHERE id = ? AND is_active = 1");
    $stmt->execute([$package_id]);
    $selected_package = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$selected_package) {
        throw new Exception('Pacchetto non trovato o non disponibile');
    }
    
    $selected_package_id = $selected_package['id'];
} catch (Exception $e) {
    $error = 'Errore nel caricamento del pacchetto selezionato';
}

// Carica categorie per il dropdown
$categories = [];
try {
    $stmt = $db->pdo->prepare("SELECT id, name FROM categories ORDER BY name ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Gestione errore silenzioso per categorie
}

// Carica province per il dropdown
$provinces = [];
try {
    $stmt = $db->pdo->prepare("SELECT id, name FROM provinces ORDER BY name ASC");
    $stmt->execute();
    $provinces = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Gestione errore silenzioso per province
}

// Genera un nuovo token CSRF per il form
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Gestione del form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verifica del token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Errore di validazione CSRF.');
    }

    try {
        // Validazione dati
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $website = sanitize($_POST['website'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $province_id = (int)($_POST['province_id'] ?? 0);
        $city_name = sanitize($_POST['city_name'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // Validazioni obbligatorie
        if (empty($name)) throw new Exception('Il nome dell\'attività è obbligatorio');
        if (empty($email)) throw new Exception('L\'email è obbligatoria');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Email non valida');
        if (empty($password)) throw new Exception('La password è obbligatoria');
        if (strlen($password) < 8) throw new Exception('La password deve essere di almeno 8 caratteri');
        if ($password !== $password_confirm) throw new Exception('Le password non coincidono');
        if (empty($description)) throw new Exception('La descrizione è obbligatoria');
        if ($category_id <= 0) throw new Exception('Seleziona una categoria');
        if ($province_id <= 0) throw new Exception('Seleziona una provincia');
        
        // Controlla se email è già utilizzata
        $stmt = $db->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Questa email è già registrata. Se hai già un account, accedi.');
        }
        
        // Determina il subscription_type basato sul pacchetto
        $subscription_type = 'free';
        if ($selected_package['name'] === 'Business') {
            $subscription_type = 'basic';
        } elseif ($selected_package['name'] === 'Premium') {
            $subscription_type = 'premium';
        }
        
        $db->pdo->beginTransaction();

        // 1. Crea l'utente
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->pdo->prepare("
            INSERT INTO users (email, password, name, role, status, created_at)
            VALUES (?, ?, ?, 'business', 'active', NOW())
        ");
        $stmt->execute([$email, $hashed_password, $name]);
        $user_id = $db->pdo->lastInsertId();

        // 2. Inserisci la business nel database
        $stmt = $db->pdo->prepare("
            INSERT INTO businesses (
                name, email, phone, website, description, 
                category_id, province_id, address, 
                subscription_type, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        $stmt->execute([
            $name, $email, $phone, $website, $description,
            $category_id, $province_id, $address, $subscription_type
        ]);
        $business_id = $db->pdo->lastInsertId();

        // 3. Gestisci la sottoscrizione
        if ($selected_package['price'] == 0) {
            // Per pacchetti gratuiti, crea la sottoscrizione e approva subito
            $stmt = $db->pdo->prepare("
                INSERT INTO subscriptions (
                    business_id, package_id, status, 
                    start_date, end_date, amount, created_at
                ) VALUES (?, ?, 'active', NOW(), DATE_ADD(NOW(), INTERVAL ? MONTH), ?, NOW())
            ");
            $stmt->execute([
                $business_id, 
                $selected_package_id, 
                $selected_package['duration_months'] ?? 12, 
                $selected_package['price']
            ]);
            
            $stmt = $db->pdo->prepare("UPDATE businesses SET status = 'approved' WHERE id = ?");
            $stmt->execute([$business_id]);
            
            $db->pdo->commit();

            // Auto-login e redirect alla pagina di completamento
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['business_id'] = $business_id;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $name;
            $_SESSION['business_status'] = 'approved';

            // Imposta i dati per la pagina di ringraziamento
            $_SESSION['registration_success'] = true;
            $_SESSION['business_name'] = $name;
            $_SESSION['business_email'] = $email;
            $_SESSION['package_name'] = $selected_package['name'];
            $_SESSION['package_price'] = $selected_package['price'];
            
            header('Location: registrazione-completata.php?free=1');
            exit;
        } else {
            // Per pacchetti a pagamento, salva i dati in sessione e reindirizza a Stripe
            $db->pdo->commit();

            $_SESSION['business_id'] = $business_id;
            $_SESSION['package_id'] = $selected_package_id;
            $_SESSION['user_id_pending_payment'] = $user_id;
            
            header('Location: api/stripe-checkout.php');
            exit;
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dati Attività - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Inserisci i dati della tua attività per completare la registrazione">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>
    
    <main class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header con pacchetto selezionato -->
            <?php if ($selected_package): ?>
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Dati della tua Attività</h1>
                        <p class="text-gray-600 mt-2">Completa la registrazione con i dati della tua attività</p>
                    </div>
                    <div class="text-right">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="font-semibold text-blue-900">Pacchetto Selezionato</h3>
                            <p class="text-blue-700 font-medium"><?php echo htmlspecialchars($selected_package['name']); ?></p>
                            <p class="text-2xl font-bold text-blue-900">
                                <?php echo $selected_package['price'] == 0 ? 'Gratuito' : '€' . number_format($selected_package['price'], 2); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Form dati attività -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-8">
                    <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                        <i data-lucide="alert-circle" class="w-5 h-5 inline mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                        <i data-lucide="check-circle" class="w-5 h-5 inline mr-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <!-- Dati principali -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nome Attività <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="name" name="name" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Inserisci il nome della tua attività"
                                    value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" id="email" name="email" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="email@example.com"
                                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" id="password" name="password" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Scegli una password sicura">
                            </div>

                            <div>
                                <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-2">
                                    Conferma Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" id="password_confirm" name="password_confirm" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Conferma la password">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Telefono
                                </label>
                                <input type="tel" id="phone" name="phone"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="+39 123 456 7890"
                                    value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                            
                            <div>
                                <label for="website" class="block text-sm font-medium text-gray-700 mb-2">
                                    Sito Web
                                </label>
                                <input type="url" id="website" name="website"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="https://www.example.com"
                                    value="<?php echo htmlspecialchars($_POST['website'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <!-- Categoria e Provincia -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Categoria <span class="text-red-500">*</span>
                                </label>
                                <select id="category_id" name="category_id" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Seleziona una categoria</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                        <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label for="province_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Provincia <span class="text-red-500">*</span>
                                </label>
                                <select id="province_id" name="province_id" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Seleziona una provincia</option>
                                    <?php foreach ($provinces as $province): ?>
                                    <option value="<?php echo $province['id']; ?>"
                                        <?php echo (isset($_POST['province_id']) && $_POST['province_id'] == $province['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($province['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Città e Indirizzo -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="city_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Città
                                </label>
                                <input type="text" id="city_name" name="city_name"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Nome della città"
                                    value="<?php echo htmlspecialchars($_POST['city_name'] ?? ''); ?>">
                            </div>
                            
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                    Indirizzo
                                </label>
                                <input type="text" id="address" name="address"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Via, numero civico"
                                    value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <!-- Descrizione -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Descrizione Attività <span class="text-red-500">*</span>
                            </label>
                            <textarea id="description" name="description" rows="4" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Descrivi la tua attività, i servizi offerti e cosa rende unica la tua proposta..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Privacy e Termini -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="flex items-start">
                                <input type="checkbox" id="accept_terms" name="accept_terms" required
                                    class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-1">
                                <div class="ml-3">
                                    <label for="accept_terms" class="text-sm text-gray-700">
                                        Accetto i <a href="termini-servizio.php" class="text-blue-600 hover:underline" target="_blank">Termini di Servizio</a> 
                                        e la <a href="privacy-policy.php" class="text-blue-600 hover:underline" target="_blank">Privacy Policy</a>
                                        <span class="text-red-500">*</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pulsanti azione -->
                        <div class="flex flex-col sm:flex-row gap-4 pt-6">
                            <button type="button" onclick="window.history.back()" 
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-6 rounded-lg transition-colors">
                                <i data-lucide="arrow-left" class="w-5 h-5 inline mr-2"></i>
                                Torna Indietro
                            </button>
                            
                            <button type="submit" 
                                class="flex-1 bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 text-white font-medium py-3 px-6 rounded-lg transition-colors">
                                <i data-lucide="check" class="w-5 h-5 inline mr-2"></i>
                                <?php echo $selected_package && $selected_package['price'] == 0 ? 'Completa Registrazione' : 'Procedi al Pagamento'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Informazioni aggiuntive -->
            <div class="mt-12 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="font-semibold text-blue-900 mb-3">
                    <i data-lucide="info" class="w-5 h-5 inline mr-2"></i>
                    Cosa Succede Dopo?
                </h3>
                <div class="text-blue-800 space-y-2">
                    <?php if ($selected_package && $selected_package['price'] == 0): ?>
                    <p>• La tua attività sarà sottoposta a revisione dal nostro team</p>
                    <p>• Riceverai una conferma via email entro 24-48 ore</p>
                    <p>• Una volta approvata, la tua attività sarà visibile sul sito</p>
                    <?php else: ?>
                    <p>• Sarai reindirizzato alla pagina di pagamento sicuro Stripe</p>
                    <p>• Dopo il pagamento, la tua attività sarà attivata immediatamente</p>
                    <p>• Riceverai una conferma via email con i dettagli dell'abbonamento</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    <script>
        // Inizializza le icone Lucide
        lucide.createIcons();
        
        // Form validation migliorata
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const requiredFields = form.querySelectorAll('[required]');
            
            // Valida email in tempo reale
            const emailField = document.getElementById('email');
            emailField.addEventListener('blur', function() {
                if (this.value && !this.checkValidity()) {
                    this.classList.add('border-red-300', 'bg-red-50');
                } else {
                    this.classList.remove('border-red-300', 'bg-red-50');
                }
            });
            
            // Valida URL sito web
            const websiteField = document.getElementById('website');
            websiteField.addEventListener('blur', function() {
                if (this.value && !this.checkValidity()) {
                    this.classList.add('border-red-300', 'bg-red-50');
                } else {
                    this.classList.remove('border-red-300', 'bg-red-50');
                }
            });
            
            // Conta caratteri descrizione
            const descriptionField = document.getElementById('description');
            const maxLength = 1000;
            
            function updateCharCount() {
                const remaining = maxLength - descriptionField.value.length;
                let counterElement = document.getElementById('desc-counter');
                
                if (!counterElement) {
                    counterElement = document.createElement('p');
                    counterElement.id = 'desc-counter';
                    counterElement.className = 'text-sm text-gray-500 mt-1';
                    descriptionField.parentNode.appendChild(counterElement);
                }
                
                counterElement.textContent = `${descriptionField.value.length}/${maxLength} caratteri`;
                
                if (remaining < 0) {
                    counterElement.className = 'text-sm text-red-500 mt-1';
                } else if (remaining < 100) {
                    counterElement.className = 'text-sm text-yellow-500 mt-1';
                } else {
                    counterElement.className = 'text-sm text-gray-500 mt-1';
                }
            }
            
            descriptionField.addEventListener('input', updateCharCount);
            updateCharCount();
        });
    </script>
</body>
</html>