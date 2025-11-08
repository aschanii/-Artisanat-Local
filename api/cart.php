<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    // Pour les utilisateurs non connectés, utiliser le panier en session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $product_id = $input['product_id'] ?? '';
    $quantity = $input['quantity'] ?? 1;
    
    switch ($action) {
        case 'add':
            // Récupérer les informations du produit
            $stmt = $pdo->prepare("SELECT p.*, u.name as artisan_name FROM products p 
                                  LEFT JOIN users u ON p.artisan_id = u.id 
                                  WHERE p.id = ? AND p.is_active = 1");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
                exit();
            }
            
            if ($product['stock'] < $quantity) {
                echo json_encode(['success' => false, 'message' => 'Stock insuffisant']);
                exit();
            }
            
            // Ajouter au panier
            $cart_item = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'artisan_name' => $product['artisan_name'],
                'quantity' => $quantity
            ];
            
            $found = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] == $product_id) {
                    $item['quantity'] += $quantity;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $_SESSION['cart'][] = $cart_item;
            }
            
            echo json_encode(['success' => true, 'cart_count' => count($_SESSION['cart'])]);
            break;
            
        case 'update':
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] == $product_id) {
                    if ($quantity <= 0) {
                        // Supprimer l'article
                        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($product_id) {
                            return $item['id'] != $product_id;
                        });
                    } else {
                        // Vérifier le stock
                        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
                        $stmt->execute([$product_id]);
                        $stock = $stmt->fetchColumn();
                        
                        if ($quantity > $stock) {
                            echo json_encode(['success' => false, 'message' => 'Stock insuffisant']);
                            exit();
                        }
                        
                        $item['quantity'] = $quantity;
                    }
                    break;
                }
            }
            echo json_encode(['success' => true]);
            break;
            
        case 'remove':
            $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($product_id) {
                return $item['id'] != $product_id;
            });
            echo json_encode(['success' => true]);
            break;
            
        case 'clear':
            $_SESSION['cart'] = [];
            echo json_encode(['success' => true]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
            break;
    }
} elseif ($method === 'GET') {
    echo json_encode([
        'success' => true,
        'cart' => $_SESSION['cart'] ?? [],
        'cart_count' => count($_SESSION['cart'] ?? [])
    ]);
}
?>