<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';

// Disable PHP error output for webhook
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON response header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$db = new Database();

// Get Stripe settings
$stripeSecretKey = $db->getSetting('stripe_secret_key');
$stripeWebhookSecret = $db->getSetting('stripe_webhook_secret');

if (!$stripeSecretKey) {
    http_response_code(400);
    echo json_encode(['error' => 'Stripe not configured']);
    exit;
}

// Get the raw POST body
$payload = file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

// In a real implementation, you would verify the webhook signature
// For now, we'll simulate webhook handling

try {
    // Parse the webhook payload
    $event = json_decode($payload, true);
    
    if (!$event) {
        throw new Exception('Invalid JSON payload');
    }
    
    // Log the webhook for debugging (in production, you might want to be more selective)
    error_log('Stripe Webhook Received: ' . $event['type']);
    
    // Handle different event types
    switch ($event['type']) {
        case 'checkout.session.completed':
            handleCheckoutSessionCompleted($db, $event['data']['object']);
            break;
            
        case 'payment_intent.succeeded':
            handlePaymentIntentSucceeded($db, $event['data']['object']);
            break;
            
        case 'invoice.payment_succeeded':
            handleInvoicePaymentSucceeded($db, $event['data']['object']);
            break;
            
        case 'customer.subscription.created':
            handleSubscriptionCreated($db, $event['data']['object']);
            break;
            
        case 'customer.subscription.updated':
            handleSubscriptionUpdated($db, $event['data']['object']);
            break;
            
        case 'customer.subscription.deleted':
            handleSubscriptionDeleted($db, $event['data']['object']);
            break;
            
        default:
            // Unhandled event type
            error_log('Unhandled Stripe webhook event type: ' . $event['type']);
            break;
    }
    
    // Respond to Stripe
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    
} catch (Exception $e) {
    error_log('Stripe Webhook Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Handle successful checkout session completion
 */
function handleCheckoutSessionCompleted($db, $session) {
    try {
        $sessionId = $session['id'];
        $customerId = $session['customer'];
        $subscriptionId = $session['subscription'] ?? null;
        
        // Find the business associated with this session
        $stmt = $db->pdo->prepare('SELECT * FROM stripe_sessions WHERE session_id = ?');
        $stmt->execute([$sessionId]);
        $sessionData = $stmt->fetch();
        
        if (!$sessionData) {
            error_log('No session data found for session: ' . $sessionId);
            return;
        }
        
        $businessData = json_decode($sessionData['business_data'], true);
        $packageId = $sessionData['package_id'];
        $amount = $sessionData['amount'];
        
        // Create the business if it doesn't exist
        $businessId = createBusinessFromWebhook($db, $businessData);
        
        // Get package details
        $stmt = $db->pdo->prepare('SELECT * FROM business_packages WHERE id = ?');
        $stmt->execute([$packageId]);
        $package = $stmt->fetch();
        
        if (!$package) {
            error_log('Package not found: ' . $packageId);
            return;
        }
        
        // Create subscription
        $duration_months = $package['duration_months'] ?? 12;
        $stmt = $db->pdo->prepare('
            INSERT INTO subscriptions (
                business_id, package_id, stripe_subscription_id, status,
                start_date, end_date, amount, created_at
            ) VALUES (?, ?, ?, "active", NOW(),
                     DATE_ADD(NOW(), INTERVAL ? MONTH), ?, NOW())
        ');
        
        $stmt->execute([
            $businessId,
            $packageId,
            $subscriptionId,
            $duration_months,
            $amount
        ]);
        
        // Approve the business
        $stmt = $db->pdo->prepare('UPDATE businesses SET status = ? WHERE id = ?');
        $stmt->execute(['approved', $businessId]);
        
        // Update session status
        $stmt = $db->pdo->prepare('UPDATE stripe_sessions SET status = ? WHERE session_id = ?');
        $stmt->execute(['completed', $sessionId]);
        
        // Send confirmation email (you would implement this)
        sendConfirmationEmail($businessData, $package);
        
        error_log('Successfully processed checkout session: ' . $sessionId);
        
    } catch (Exception $e) {
        error_log('Error handling checkout session completion: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Handle successful payment intent
 */
function handlePaymentIntentSucceeded($db, $paymentIntent) {
    try {
        $paymentIntentId = $paymentIntent['id'];
        $amount = $paymentIntent['amount'] / 100; // Convert from cents
        
        error_log('Payment intent succeeded: ' . $paymentIntentId . ' for amount: ' . $amount);
        
        // You might want to update payment records or send notifications here
        
    } catch (Exception $e) {
        error_log('Error handling payment intent succeeded: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Handle successful invoice payment
 */
function handleInvoicePaymentSucceeded($db, $invoice) {
    try {
        $subscriptionId = $invoice['subscription'];
        $amount = $invoice['amount_paid'] / 100; // Convert from cents
        
        // Update subscription records
        $stmt = $db->pdo->prepare('
            UPDATE subscriptions 
            SET end_date = DATE_ADD(end_date, INTERVAL 1 MONTH)
            WHERE stripe_subscription_id = ?
        ');
        $stmt->execute([$subscriptionId]);
        
        error_log('Invoice payment succeeded for subscription: ' . $subscriptionId);
        
    } catch (Exception $e) {
        error_log('Error handling invoice payment succeeded: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Handle subscription creation
 */
function handleSubscriptionCreated($db, $subscription) {
    try {
        $subscriptionId = $subscription['id'];
        $status = $subscription['status'];
        
        error_log('Subscription created: ' . $subscriptionId . ' with status: ' . $status);
        
    } catch (Exception $e) {
        error_log('Error handling subscription created: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Handle subscription updates
 */
function handleSubscriptionUpdated($db, $subscription) {
    try {
        $subscriptionId = $subscription['id'];
        $status = $subscription['status'];
        
        // Update subscription status
        $stmt = $db->pdo->prepare('
            UPDATE subscriptions 
            SET status = ? 
            WHERE stripe_subscription_id = ?
        ');
        $stmt->execute([$status, $subscriptionId]);
        
        // If subscription is cancelled or ended, update business status
        if (in_array($status, ['canceled', 'unpaid', 'past_due'])) {
            $stmt = $db->pdo->prepare('
                UPDATE businesses b
                SET subscription_type = "free"
                WHERE EXISTS (
                    SELECT 1 FROM subscriptions s 
                    WHERE s.business_id = b.id AND s.stripe_subscription_id = ?
                )
            ');
            $stmt->execute([$subscriptionId]);
        }
        
        error_log('Subscription updated: ' . $subscriptionId . ' to status: ' . $status);
        
    } catch (Exception $e) {
        error_log('Error handling subscription updated: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Handle subscription deletion/cancellation
 */
function handleSubscriptionDeleted($db, $subscription) {
    try {
        $subscriptionId = $subscription['id'];
        
        // Mark subscription as cancelled
        $stmt = $db->pdo->prepare('
            UPDATE subscriptions 
            SET status = "cancelled" 
            WHERE stripe_subscription_id = ?
        ');
        $stmt->execute([$subscriptionId]);
        
        // Downgrade business to free plan
        $stmt = $db->pdo->prepare('
            UPDATE businesses b
            SET subscription_type = "free"
            WHERE EXISTS (
                SELECT 1 FROM subscriptions s 
                WHERE s.business_id = b.id AND s.stripe_subscription_id = ?
            )
        ');
        $stmt->execute([$subscriptionId]);
        
        error_log('Subscription cancelled: ' . $subscriptionId);
        
    } catch (Exception $e) {
        error_log('Error handling subscription deleted: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Create business from webhook data
 */
function createBusinessFromWebhook($db, $businessData) {
    try {
        // Check if business already exists by email
        $stmt = $db->pdo->prepare('SELECT id FROM businesses WHERE email = ?');
        $stmt->execute([$businessData['email']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            return $existing['id'];
        }
        
        // Create new business
        $stmt = $db->pdo->prepare('
            INSERT INTO businesses (
                name, email, phone, website, description,
                category_id, province_id, city_id, address,
                status, subscription_type, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ');
        
        $stmt->execute([
            $businessData['name'],
            $businessData['email'],
            $businessData['phone'] ?? null,
            $businessData['website'] ?? null,
            $businessData['description'] ?? null,
            $businessData['category_id'] ?? null,
            $businessData['province_id'] ?? null,
            $businessData['city_id'] ?? null,
            $businessData['address'] ?? null,
            'pending',
            'premium'
        ]);
        
        return $db->pdo->lastInsertId();
        
    } catch (Exception $e) {
        error_log('Error creating business from webhook: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Send confirmation email (placeholder)
 */
function sendConfirmationEmail($businessData, $package) {
    // In a real implementation, you would send an actual email here
    error_log('Would send confirmation email to: ' . $businessData['email'] . ' for package: ' . $package['name']);
    
    // Example email content:
    /*
    $to = $businessData['email'];
    $subject = 'Registrazione completata - Passione Calabria';
    $message = '
        <h1>Benvenuto in Passione Calabria!</h1>
        <p>La tua attività "' . $businessData['name'] . '" è stata registrata con successo.</p>
        <p>Pacchetto attivato: ' . $package['name'] . ' - €' . number_format($package['price'], 2) . '</p>
        <p>La tua attività sarà visibile sulla piattaforma entro pochi minuti.</p>
        <p>Grazie per aver scelto Passione Calabria!</p>
    ';
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: noreply@passionecalabria.it',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    mail($to, $subject, $message, implode("\r\n", $headers));
    */
}
?>