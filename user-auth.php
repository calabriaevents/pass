<?php
ob_start();
session_start();
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

$db = new Database();
$message = '';
$error = '';

// Check database connection
if (!$db->isConnected()) {
    $error = 'Il sistema è temporaneamente non disponibile. Riprova più tardi.';
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Clear all session data
    $_SESSION = [];
    
    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to home page with message
    header('Location: index.php?message=logout_success');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email e password sono obbligatori.';
    } elseif (!$db->isConnected()) {
        $error = 'Il sistema è temporaneamente non disponibile. Riprova più tardi.';
    } else {
        // Check credentials using the new generic method
        $user = $db->authenticateUserByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            // Successful login, set common session variables
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Update last login
            $db->updateUserLastLogin($user['id']);

            // Role-based redirection
            if ($user['role'] === 'admin') {
                // Set admin-specific session
                $_SESSION['admin_logged_in'] = true;
                // Redirect to admin dashboard
                header('Location: admin/index.php');
                exit;
            } else {
                // For business users, set their specific session variables
                $_SESSION['business_id'] = $user['business_id'];
                $_SESSION['business_status'] = $user['business_status'];

                // Redirect to user dashboard
                header('Location: user-dashboard.php');
                exit;
            }
        } else {
            $error = 'Credenziali non valide. Verifica email e password.';
        }
    }
}

// Check if already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    header('Location: user-dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accesso Attività - Passione Calabria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="index.php" class="inline-flex items-center text-white hover:text-indigo-200 transition-colors group">
                <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Torna alla Home
            </a>
        </div>

        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <a href="index.php" class="inline-block hover:scale-105 transition-transform">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full mb-4 hover:shadow-lg transition-shadow">
                    <span class="text-2xl font-bold text-indigo-600">PC</span>
                </div>
            </a>
            <h1 class="text-3xl font-bold text-white mb-2">
                <a href="index.php" class="hover:text-indigo-200 transition-colors">Passione Calabria</a>
            </h1>
            <p class="text-indigo-100">Accesso Area Attività</p>
        </div>

        <!-- Form Container -->
        <div class="glass-effect rounded-2xl shadow-2xl p-8">
            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Success Message for logout -->
            <?php if (isset($_GET['message']) && $_GET['message'] === 'logout_success'): ?>
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                    Logout effettuato con successo!
                </div>
            <?php endif; ?>

            <!-- Info Box -->
            <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium">Come accedere</h3>
                        <p class="text-sm mt-1">
                            Le credenziali vengono create quando acquisti un pacchetto per la tua attività. 
                            Se non hai ancora un account, <a href="/iscrizione-attivita.php" class="font-medium underline">scegli un pacchetto qui</a>.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Login Form -->
            <form method="POST" action="user-auth.php" class="space-y-6">
                <div>
                    <label class="block text-white text-sm font-medium mb-2">Email</label>
                    <input type="email" name="email" required 
                           class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                           placeholder="la-tua-email@esempio.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div>
                    <label class="block text-white text-sm font-medium mb-2">Password</label>
                    <input type="password" name="password" required 
                           class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                           placeholder="••••••••">
                </div>

                <button type="submit" 
                        class="w-full bg-white text-indigo-600 font-semibold py-3 px-4 rounded-lg hover:bg-opacity-90 transition-all transform hover:scale-105">
                    Accedi
                </button>
            </form>

            <!-- Help Section -->
            <div class="mt-8 text-center space-y-4">
                <div class="border-t border-white border-opacity-20 pt-6">
                    <h4 class="text-white font-medium mb-3">Non hai ancora un account?</h4>
                    <p class="text-white text-opacity-80 text-sm mb-4">
                        Per accedere all'area attività devi prima scegliere un pacchetto. 
                        Durante l'acquisto creerai le tue credenziali di accesso.
                    </p>
                    <a href="/iscrizione-attivita.php"
                       class="inline-flex items-center justify-center w-full bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-3 px-4 rounded-lg transition-all transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Scegli un Pacchetto
                    </a>
                </div>

                <div class="text-center">
                    <p class="text-white text-opacity-80 text-sm">
                        Hai problemi di accesso?<br>
                        Contattaci al <strong class="text-white">+39 XXX XXX XXXX</strong>
                    </p>
                </div>
            </div>


        </div>
    </div>
</body>
</html>