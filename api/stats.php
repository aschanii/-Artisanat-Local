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
$user_role = $_SESSION['user_role'];

// Get statistics based on role
if ($user_role === 'admin') {
    // Admin statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE status = 'active'");
    $total_users = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products WHERE status = 'active'");
    $total_products = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders WHERE status = 'completed'");
    $total_orders = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT SUM(total_amount) as total_revenue FROM orders WHERE status = 'completed'");
    $total_revenue = $stmt->fetchColumn() ?: 0;

    // Sales data for last 30 days
    $stmt = $pdo->prepare("
        SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue
        FROM orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status = 'completed'
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $stmt->execute();
    $sales_data = $stmt->fetchAll();

    echo json_encode([
        'total_users' => $total_users,
        'total_products' => $total_products,
        'total_orders' => $total_orders,
        'total_revenue' => $total_revenue,
        'sales_data' => $sales_data
    ]);

} elseif ($user_role === 'artisan') {
    // Artisan statistics
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_products FROM products WHERE artisan_id = ? AND status = 'active'");
    $stmt->execute([$user_id]);
    $total_products = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_orders FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE p.artisan_id = ? AND oi.status = 'completed'
    ");
    $stmt->execute([$user_id]);
    $total_orders = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT SUM(oi.quantity * oi.price) as total_revenue FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE p.artisan_id = ? AND oi.status = 'completed'
    ");
    $stmt->execute([$user_id]);
    $total_revenue = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_reviews FROM reviews r 
        JOIN products p ON r.product_id = p.id 
        WHERE p.artisan_id = ? AND r.status = 'approved'
    ");
    $stmt->execute([$user_id]);
    $total_reviews = $stmt->fetchColumn();

    // Top selling products
    $stmt = $pdo->prepare("
        SELECT p.name, SUM(oi.quantity) as total_sold
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE p.artisan_id = ? AND oi.status = 'completed'
        GROUP BY p.id
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $top_products = $stmt->fetchAll();

    echo json_encode([
        'total_products' => $total_products,
        'total_orders' => $total_orders,
        'total_revenue' => $total_revenue,
        'total_reviews' => $total_reviews,
        'top_products' => $top_products
    ]);

} else {
    // Customer statistics
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_orders = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) as total_reviews FROM reviews WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_reviews = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) as wishlist_items FROM wishlist WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $wishlist_items = $stmt->fetchColumn();

    echo json_encode([
        'total_orders' => $total_orders,
        'total_reviews' => $total_reviews,
        'wishlist_items' => $wishlist_items
    ]);
}
?>