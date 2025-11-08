<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'create_intent':
            createPaymentIntent($input);
            break;
        case 'confirm_payment':
            confirmPayment($input);
            break;
        case 'process_payment':
            processPayment($input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action non reconnue']);
            break;
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
}

function createPaymentIntent($input) {
    // Simulation d'intention de paiement (en production, intégrer Stripe/ PayPal)
    $amount = $input['amount'] ?? 0;
    $currency = $input['currency'] ?? 'eur';
    $order_id = $input['order_id'] ?? null;
    
    if ($amount <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Montant invalide']);
        return;
    }
    
    try {
        // Générer un ID de paiement simulé
        $payment_intent_id = 'pi_' . uniqid() . '_' . time();
        $client_secret = 'cs_' . bin2hex(random_bytes(16));
        
        // Enregistrer la tentative de paiement
        $_SESSION['payment_intent'] = [
            'id' => $payment_intent_id,
            'amount' => $amount,
            'currency' => $currency,
            'order_id' => $order_id,
            'status' => 'requires_payment_method',
            'created_at' => time()
        ];
        
        echo json_encode([
            'success' => true,
            'payment_intent' => [
                'id' => $payment_intent_id,
                'client_secret' => $client_secret,
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'requires_payment_method'
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la création du paiement: ' . $e->getMessage()]);
    }
}

function confirmPayment($input) {
    $payment_intent_id = $input['payment_intent_id'] ?? '';
    $payment_method_id = $input['payment_method_id'] ?? '';
    
    if (empty($payment_intent_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de paiement manquant']);
        return;
    }
    
    try {
        // Simuler la confirmation de paiement
        // En production, vous utiliseriez l'API Stripe ici
        
        $payment_intent = $_SESSION['payment_intent'] ?? null;
        
        if (!$payment_intent || $payment_intent['id'] !== $payment_intent_id) {
            http_response_code(404);
            echo json_encode(['error' => 'Paiement non trouvé']);
            return;
        }
        
        // Simuler un traitement de paiement
        sleep(2); // Simulation du temps de traitement
        
        // 90% de chance de succès, 10% d'échec pour la démo
        $success = rand(1, 10) <= 9;
        
        if ($success) {
            $payment_intent['status'] = 'succeeded';
            $payment_intent['payment_method'] = $payment_method_id;
            $payment_intent['confirmed_at'] = time();
            
            $_SESSION['payment_intent'] = $payment_intent;
            
            // Mettre à jour le statut de la commande
            if ($payment_intent['order_id']) {
                updateOrderStatus($payment_intent['order_id'], 'confirmed');
            }
            
            echo json_encode([
                'success' => true,
                'payment_intent' => [
                    'id' => $payment_intent_id,
                    'status' => 'succeeded',
                    'amount_received' => $payment_intent['amount']
                ]
            ]);
        } else {
            $payment_intent['status'] = 'failed';
            $_SESSION['payment_intent'] = $payment_intent;
            
            http_response_code(402);
            echo json_encode([
                'error' => 'Paiement échoué',
                'payment_intent' => [
                    'id' => $payment_intent_id,
                    'status' => 'failed'
                ]
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la confirmation: ' . $e->getMessage()]);
    }
}

function processPayment($input) {
    $order_id = $input['order_id'] ?? '';
    $payment_method = $input['payment_method'] ?? '';
    $payment_details = $input['payment_details'] ?? [];
    
    if (empty($order_id) || empty($payment_method)) {
        http_response_code(400);
        echo json_encode(['error' => 'Données de paiement incomplètes']);
        return;
    }
    
    try {
        // Simulation de différents types de paiement
        switch ($payment_method) {
            case 'card':
                $result = processCardPayment($payment_details);
                break;
            case 'paypal':
                $result = processPayPalPayment($payment_details);
                break;
            case 'transfer':
                $result = processTransferPayment($payment_details);
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Méthode de paiement non supportée']);
                return;
        }
        
        if ($result['success']) {
            // Mettre à jour le statut de la commande
            updateOrderStatus($order_id, 'confirmed');
            
            echo json_encode([
                'success' => true,
                'transaction_id' => $result['transaction_id'],
                'payment_method' => $payment_method,
                'status' => 'completed'
            ]);
        } else {
            http_response_code(402);
            echo json_encode([
                'error' => $result['error'] ?? 'Paiement échoué',
                'payment_method' => $payment_method
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors du traitement: ' . $e->getMessage()]);
    }
}

function processCardPayment($details) {
    // Simulation de paiement par carte
    $card_number = $details['card_number'] ?? '';
    $expiry = $details['expiry'] ?? '';
    $cvc = $details['cvc'] ?? '';
    $cardholder = $details['cardholder'] ?? '';
    
    // Validation basique
    if (empty($card_number) || empty($expiry) || empty($cvc) || empty($cardholder)) {
        return ['success' => false, 'error' => 'Informations carte incomplètes'];
    }
    
    // Simuler une vérification de carte
    $card_valid = validateCardNumber($card_number);
    $expiry_valid = validateExpiryDate($expiry);
    
    if (!$card_valid || !$expiry_valid) {
        return ['success' => false, 'error' => 'Carte invalide'];
    }
    
    // Simuler un traitement (attendre 1-2 secondes)
    sleep(1);
    
    // 95% de chance de succès pour les cartes valides
    $success = rand(1, 100) <= 95;
    
    if ($success) {
        return [
            'success' => true,
            'transaction_id' => 'tx_' . uniqid() . '_' . time(),
            'amount' => $details['amount'] ?? 0
        ];
    } else {
        return [
            'success' => false, 
            'error' => 'Transaction refusée par la banque'
        ];
    }
}

function processPayPalPayment($details) {
    // Simulation de paiement PayPal
    $email = $details['email'] ?? '';
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Email PayPal invalide'];
    }
    
    sleep(2); // Simulation du temps de traitement PayPal
    
    // 98% de chance de succès pour PayPal
    $success = rand(1, 100) <= 98;
    
    if ($success) {
        return [
            'success' => true,
            'transaction_id' => 'pp_' . uniqid() . '_' . time(),
            'payer_email' => $email
        ];
    } else {
        return [
            'success' => false,
            'error' => 'Échec de l\'autorisation PayPal'
        ];
    }
}

function processTransferPayment($details) {
    // Simulation de paiement par virement
    $bank_name = $details['bank_name'] ?? '';
    $account_holder = $details['account_holder'] ?? '';
    
    if (empty($bank_name) || empty($account_holder)) {
        return ['success' => false, 'error' => 'Informations bancaires incomplètes'];
    }
    
    sleep(1);
    
    // Toujours réussi pour les virements (attente de confirmation)
    return [
        'success' => true,
        'transaction_id' => 'tr_' . uniqid() . '_' . time(),
        'status' => 'pending_confirmation',
        'instructions' => 'Veuillez effectuer le virement sur le compte suivant...'
    ];
}

function validateCardNumber($number) {
    // Validation simple du numéro de carte (simulation)
    $clean_number = preg_replace('/\s+/', '', $number);
    return strlen($clean_number) >= 13 && strlen($clean_number) <= 19;
}

function validateExpiryDate($expiry) {
    // Validation de la date d'expiration
    if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry)) {
        return false;
    }
    
    list($month, $year) = explode('/', $expiry);
    $current_year = date('y');
    $current_month = date('m');
    
    return ($year > $current_year) || ($year == $current_year && $month >= $current_month);
}

function updateOrderStatus($order_id, $status) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        return true;
    } catch (Exception $e) {
        error_log("Erreur mise à jour commande: " . $e->getMessage());
        return false;
    }
}
?>