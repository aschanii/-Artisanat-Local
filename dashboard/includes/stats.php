<?php
// Get dashboard statistics
function getDashboardStats($pdo, $user_id, $user_role) {
    $stats = [];

    if ($user_role === 'admin') {
        // Admin statistics
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
        $stats['total_users'] = $stmt->fetchColumn();

        $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
        $stats['total_products'] = $stmt->fetchColumn();

        $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'");
        $stats['total_orders'] = $stmt->fetchColumn();

        $stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'completed'");
        $stats['total_revenue'] = $stmt->fetchColumn() ?: 0;
    } elseif ($user_role === 'artisan') {
        // Artisan statistics
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE artisan_id = ? AND status = 'active'");
        $stmt->execute([$user_id]);
        $stats['total_products'] = $stmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE p.artisan_id = ? AND oi.status = 'completed'
        ");
        $stmt->execute([$user_id]);
        $stats['total_orders'] = $stmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT SUM(oi.quantity * oi.price) FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE p.artisan_id = ? AND oi.status = 'completed'
        ");
        $stmt->execute([$user_id]);
        $stats['total_revenue'] = $stmt->fetchColumn() ?: 0;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews r JOIN products p ON r.product_id = p.id WHERE p.artisan_id = ?");
        $stmt->execute([$user_id]);
        $stats['total_reviews'] = $stmt->fetchColumn();
    } else {
        // Customer statistics
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['total_orders'] = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['total_reviews'] = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['wishlist_items'] = $stmt->fetchColumn();
    }

    return $stats;
}

// Get recent products for artisan
function getRecentProducts($pdo, $user_id, $user_role, $limit = 5) {
    if ($user_role === 'artisan') {
        $stmt = $pdo->prepare("
            SELECT * FROM products 
            WHERE artisan_id = ? AND status = 'active'
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    return [];
}

// Get recent orders
function getRecentOrders($pdo, $user_id, $user_role, $limit = 5) {
    if ($user_role === 'artisan') {
        $stmt = $pdo->prepare("
            SELECT o.*, oi.product_id, oi.quantity, p.name as product_name
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN products p ON oi.product_id = p.id
            WHERE p.artisan_id = ?
            ORDER BY o.created_at DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } elseif ($user_role === 'customer') {
        $stmt = $pdo->prepare("
            SELECT * FROM orders 
            WHERE user_id = ?
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    return [];
}
?>