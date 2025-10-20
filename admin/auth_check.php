<?php
// Centralized Admin Authentication Check

// Ensure session is started. If a script including this file already started one, it won't throw an error.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and if their role is 'admin'.
// If not, redirect them to the login page and terminate the script.
if (!isset($_SESSION['user_logged_in']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // Redirect to the main login page, which will handle all user authentication.
    // Using a relative path for local development compatibility.
    header('Location: ../user-auth.php?action=login&unauthorized=true');
    exit;
}

// If the script reaches this point, the user is an authenticated admin.
?>