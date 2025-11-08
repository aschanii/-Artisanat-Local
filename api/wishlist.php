<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get user's wishlist
        $stmt = $pdo->prepare("
            SELECT w.*, p.name, p.price, p.image_url 
            FROM wishlist w 
            JOIN products p ON w.product_id = p.id 
            WHERE w.user_id = ? AND p.status = 'active'
        ");
        $stmt->execute([$user_id]);
        $wishlist = $stmt->fetchAll();
        echo json_encode($wishlist);
        break;

    case 'POST':
        // Add product to wishlist
        $data = json_decode(file_get_contents('php://input'), true);
        $product_id = (int)$data['product_id'];

        // Verify product exists
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$product_id]);
        
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            break;
        }

        // Check if product already in wishlist
        $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Product already in wishlist']);
            break;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $product_id]);
            echo json_encode(['success' => 'Product added to wishlist']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error']);
        }
        break;

    case 'DELETE':
        // Remove product from wishlist
        $data = json_decode(file_get_contents('php://input'), true);
        $product_id = (int)$data['product_id'];

        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => 'Product removed from wishlist']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found in wishlist']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>