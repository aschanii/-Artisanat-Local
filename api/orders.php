<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getOrders();
        break;
    case 'POST':
        createOrder();
        break;
    case 'PUT':
        updateOrder();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}

function getOrders() {
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Non authentifié']);
        return;
    }
    
    $order_id = $_GET['id'] ?? null;
    $status = $_GET['status'] ?? null;
    $limit = $_GET['limit'] ?? 20;
    $offset = $_GET['offset'] ?? 0;
    
    try {
        // Construire la requête en fonction du rôle
        if ($_SESSION['user_role'] === 'admin') {
            $sql = "SELECT o.*, u.name as customer_name, u.email as customer_email 
                    FROM orders o 
                    LEFT JOIN users u ON o.user_id = u.id 
                    WHERE 1=1";
            $params = [];
        } elseif ($_SESSION['user_role'] === 'seller') {
            $sql = "SELECT DISTINCT o.*, u.name as customer_name, u.email as customer_email 
                    FROM orders o 
                    LEFT JOIN users u ON o.user_id = u.id 
                    LEFT JOIN order_items oi ON o.id = oi.order_id 
                    WHERE oi.artisan_id = ?";
            $params = [$_SESSION['user_id']];
        } else {
            $sql = "SELECT o.*, u.name as customer_name, u.email as customer_email 
                    FROM orders o 
                    LEFT JOIN users u ON o.user_id = u.id 
                    WHERE o.user_id = ?";
            $params = [$_SESSION['user_id']];
        }
        
        if ($order_id) {
            $sql .= " AND o.id = ?";
            $params[] = $order_id;
        }
        
        if ($status) {
            $sql .= " AND o.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = (int)$limit;
        }
        
        if ($offset) {
            $sql .= " OFFSET ?";
            $params[] = (int)$offset;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupérer les articles pour chaque commande
        foreach ($orders as &$order) {
            $itemsStmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image as product_image, u.name as artisan_name 
                                       FROM order_items oi 
                                       LEFT JOIN products p ON oi.product_id = p.id 
                                       LEFT JOIN users u ON oi.artisan_id = u.id 
                                       WHERE oi.order_id = ?");
            $itemsStmt->execute([$order['id']]);
            $order['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convertir les prix en float
            $order['total'] = floatval($order['total']);
            $order['subtotal'] = floatval($order['subtotal']);
            $order['shipping'] = floatval($order['shipping']);
            $order['tax'] = floatval($order['tax']);
        }
        
        if ($order_id && count($orders) > 0) {
            echo json_encode($orders[0]);
        } else {
            echo json_encode($orders);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la récupération des commandes: ' . $e->getMessage()]);
    }
}

function createOrder() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validation des données
    $required = ['customer_name', 'customer_email', 'customer_phone', 'shipping_address', 'items'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Le champ '$field' est requis"]);
            return;
        }
    }
    
    if (!is_array($input['items']) || empty($input['items'])) {
        http_response_code(400);
        echo json_encode(['error' => 'La commande doit contenir au moins un article']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Calculer les totaux
        $subtotal = 0;
        foreach ($input['items'] as $item) {
            if (empty($item['product_id']) || empty($item['quantity']) || empty($item['price'])) {
                throw new Exception('Données d\'article invalides');
            }
            
            // Vérifier le stock
            $stockStmt = $pdo->prepare("SELECT stock, name FROM products WHERE id = ? AND is_active = 1");
            $stockStmt->execute([$item['product_id']]);
            $product = $stockStmt->fetch();
            
            if (!$product) {
                throw new Exception("Produit non trouvé: {$item['product_id']}");
            }
            
            if ($product['stock'] < $item['quantity']) {
                throw new Exception("Stock insuffisant pour: {$product['name']}");
            }
            
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        // Calculer les frais de livraison
        $shipping = $input['shipping'] ?? 4.90;
        $free_shipping_threshold = 50;
        if ($subtotal >= $free_shipping_threshold) {
            $shipping = 0;
        }
        
        $total = $subtotal + $shipping;
        
        // Créer le numéro de commande
        $order_number = 'CMD-' . date('Ymd') . '-' . strtoupper(uniqid());
        
        // Créer la commande
        $orderStmt = $pdo->prepare("INSERT INTO orders (order_number, user_id, total, subtotal, shipping, shipping_address, customer_name, customer_email, customer_phone, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $notes = $input['notes'] ?? '';
        
        $orderStmt->execute([
            $order_number,
            $user_id,
            $total,
            $subtotal,
            $shipping,
            $input['shipping_address'],
            $input['customer_name'],
            $input['customer_email'],
            $input['customer_phone'],
            $notes
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Ajouter les articles
        $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, artisan_id, quantity, price, total) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($input['items'] as $item) {
            // Récupérer l'artisan du produit
            $artisanStmt = $pdo->prepare("SELECT artisan_id FROM products WHERE id = ?");
            $artisanStmt->execute([$item['product_id']]);
            $artisan_id = $artisanStmt->fetchColumn();
            
            $item_total = $item['price'] * $item['quantity'];
            
            $itemStmt->execute([
                $order_id,
                $item['product_id'],
                $artisan_id,
                $item['quantity'],
                $item['price'],
                $item_total
            ]);
            
            // Mettre à jour le stock
            $updateStockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $updateStockStmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'order_id' => $order_id,
            'order_number' => $order_number,
            'total' => $total,
            'message' => 'Commande créée avec succès'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la création de la commande: ' . $e->getMessage()]);
    }
}

function updateOrder() {
    global $pdo;
    
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Accès non autorisé']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $order_id = $input['id'] ?? null;
    $status = $input['status'] ?? null;
    $tracking_number = $input['tracking_number'] ?? null;
    
    if (!$order_id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de commande requis']);
        return;
    }
    
    try {
        // Vérifier que la commande existe
        $checkStmt = $pdo->prepare("SELECT id FROM orders WHERE id = ?");
        $checkStmt->execute([$order_id]);
        
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Commande non trouvée']);
            return;
        }
        
        // Construire la mise à jour
        $updates = [];
        $params = [];
        
        if ($status) {
            $allowed_statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (!in_array($status, $allowed_statuses)) {
                http_response_code(400);
                echo json_encode(['error' => 'Statut invalide']);
                return;
            }
            $updates[] = "status = ?";
            $params[] = $status;
        }
        
        if ($tracking_number !== null) {
            $updates[] = "tracking_number = ?";
            $params[] = $tracking_number;
        }
        
        if (empty($updates)) {
            http_response_code(400);
            echo json_encode(['error' => 'Aucune donnée à mettre à jour']);
            return;
        }
        
        $updates[] = "updated_at = NOW()";
        $params[] = $order_id;
        
        $sql = "UPDATE orders SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['success' => true, 'message' => 'Commande mise à jour avec succès']);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
    }
}
?>