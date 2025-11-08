<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getUsers();
        break;
    case 'PUT':
        updateUser();
        break;
    case 'DELETE':
        deleteUser();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}

function getUsers() {
    global $pdo;
    
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Accès non autorisé']);
        return;
    }
    
    $user_id = $_GET['id'] ?? null;
    $role = $_GET['role'] ?? null;
    $limit = $_GET['limit'] ?? 20;
    $offset = $_GET['offset'] ?? 0;
    
    try {
        $sql = "SELECT id, name, email, role, avatar, bio, phone, address, social_facebook, social_instagram, created_at, last_login 
                FROM users WHERE 1=1";
        $params = [];
        
        if ($user_id) {
            $sql .= " AND id = ?";
            $params[] = $user_id;
        }
        
        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
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
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ajouter les statistiques pour les artisans
        foreach ($users as &$user) {
            if ($user['role'] === 'seller') {
                $statsStmt = $pdo->prepare("SELECT 
                    COUNT(*) as product_count,
                    COALESCE(SUM(oi.quantity), 0) as total_sales,
                    COALESCE(SUM(oi.total), 0) as total_revenue
                    FROM products p 
                    LEFT JOIN order_items oi ON p.id = oi.product_id 
                    WHERE p.artisan_id = ?");
                $statsStmt->execute([$user['id']]);
                $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
                
                $user['stats'] = [
                    'product_count' => intval($stats['product_count']),
                    'total_sales' => intval($stats['total_sales']),
                    'total_revenue' => floatval($stats['total_revenue'])
                ];
            }
        }
        
        if ($user_id && count($users) > 0) {
            echo json_encode($users[0]);
        } else {
            echo json_encode($users);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la récupération des utilisateurs: ' . $e->getMessage()]);
    }
}

function updateUser() {
    global $pdo;
    
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Accès non autorisé']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input['id'] ?? null;
    $role = $input['role'] ?? null;
    
    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID utilisateur requis']);
        return;
    }
    
    try {
        // Vérifier que l'utilisateur existe
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $checkStmt->execute([$user_id]);
        
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Utilisateur non trouvé']);
            return;
        }
        
        // Construire la mise à jour
        $updates = [];
        $params = [];
        
        if ($role) {
            $allowed_roles = ['admin', 'seller'];
            if (!in_array($role, $allowed_roles)) {
                http_response_code(400);
                echo json_encode(['error' => 'Rôle invalide']);
                return;
            }
            $updates[] = "role = ?";
            $params[] = $role;
        }
        
        if (isset($input['name'])) {
            $updates[] = "name = ?";
            $params[] = $input['name'];
        }
        
        if (isset($input['email'])) {
            // Vérifier que l'email n'est pas déjà utilisé
            $emailStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $emailStmt->execute([$input['email'], $user_id]);
            if ($emailStmt->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => 'Cet email est déjà utilisé']);
                return;
            }
            $updates[] = "email = ?";
            $params[] = $input['email'];
        }
        
        if (empty($updates)) {
            http_response_code(400);
            echo json_encode(['error' => 'Aucune donnée à mettre à jour']);
            return;
        }
        
        $updates[] = "updated_at = NOW()";
        $params[] = $user_id;
        
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['success' => true, 'message' => 'Utilisateur mis à jour avec succès']);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
    }
}

function deleteUser() {
    global $pdo;
    
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Accès non autorisé']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input['id'] ?? null;
    
    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID utilisateur requis']);
        return;
    }
    
    try {
        // Empêcher la suppression de son propre compte
        if ($user_id == $_SESSION['user_id']) {
            http_response_code(400);
            echo json_encode(['error' => 'Vous ne pouvez pas supprimer votre propre compte']);
            return;
        }
        
        // Vérifier que l'utilisateur existe
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $checkStmt->execute([$user_id]);
        
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Utilisateur non trouvé']);
            return;
        }
        
        // Vérifier s'il y a des commandes associées
        $ordersStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $ordersStmt->execute([$user_id]);
        $order_count = $ordersStmt->fetchColumn();
        
        // Pour les artisans, vérifier les produits
        $productsStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE artisan_id = ?");
        $productsStmt->execute([$user_id]);
        $product_count = $productsStmt->fetchColumn();
        
        if ($order_count > 0 || $product_count > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Impossible de supprimer un utilisateur avec des commandes ou produits associés']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé avec succès']);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
    }
}
?>