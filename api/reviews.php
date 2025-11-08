<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check HTTP method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get reviews for a product
        if (isset($_GET['product_id'])) {
            $product_id = (int)$_GET['product_id'];
            $stmt = $pdo->prepare("
                SELECT r.*, u.first_name, u.last_name 
                FROM reviews r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.product_id = ? AND r.status = 'approved'
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$product_id]);
            $reviews = $stmt->fetchAll();
            echo json_encode($reviews);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID is required']);
        }
        break;

    case 'POST':
        // Add new review
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            break;
        }

        $user_id = $_SESSION['user_id'];
        $product_id = (int)$data['product_id'];
        $rating = (int)$data['rating'];
        $comment = sanitizeInput($data['comment']);

        // Verify user purchased the product
        $stmt = $pdo->prepare("
            SELECT oi.id FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'completed'
        ");
        $stmt->execute([$user_id, $product_id]);
        
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['error' => 'You can only review purchased products']);
            break;
        }

        // Check if user already reviewed
        $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'You have already reviewed this product']);
            break;
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO reviews (user_id, product_id, rating, comment, status) 
                VALUES (?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$user_id, $product_id, $rating, $comment]);
            
            echo json_encode(['success' => 'Review submitted for approval']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>