<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getCategories();
        break;
    case 'POST':
        createCategory();
        break;
    case 'PUT':
        updateCategory();
        break;
    case 'DELETE':
        deleteCategory();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}

function getCategories() {
    global $pdo;
    
    $id = $_GET['id'] ?? null;
    
    try {
        $sql = "SELECT c.*, COUNT(p.id) as product_count 
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1 
                GROUP BY c.id 
                ORDER BY c.name";
        
        if ($id) {
            $sql = "SELECT c.*, COUNT(p.id) as product_count 
                    FROM categories c 
                    LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1 
                    WHERE c.id = ? 
                    GROUP BY c.id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($category) {
                echo json_encode($category);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Catégorie non trouvée']);
            }
        } else {
            $stmt = $pdo->query($sql);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($categories);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la récupération des catégories: ' . $e->getMessage()]);
    }
}

function createCategory() {
    global $pdo;
    
    if (!isset($_SESSION['user_id']) || !is_admin()) {
        http_response_code(403);
        echo json_encode(['error' => 'Accès non autorisé']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $name = $input['name'] ?? '';
    $description = $input['description'] ?? '';
    $image = $input['image'] ?? '';
    
    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['error' => 'Le nom de la catégorie est requis']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description, image) VALUES (?, ?, ?)");
        $stmt->execute([$name, $description, $image]);
        
        $category_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'category_id' => $category_id,
            'message' => 'Catégorie créée avec succès'
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la création: ' . $e->getMessage()]);
    }
}

function updateCategory() {
    global $pdo;
    
    if (!isset($_SESSION['user_id']) || !is_admin()) {
        http_response_code(403);
        echo json_encode(['error' => 'Accès non autorisé']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de catégorie requis']);
        return;
    }
    
    try {
        $updates = [];
        $params = [];
        
        if (isset($input['name'])) {
            $updates[] = "name = ?";
            $params[] = $input['name'];
        }
        
        if (isset($input['description'])) {
            $updates[] = "description = ?";
            $params[] = $input['description'];
        }
        
        if (isset($input['image'])) {
            $updates[] = "image = ?";
            $params[] = $input['image'];
        }
        
        if (empty($updates)) {
            http_response_code(400);
            echo json_encode(['error' => 'Aucune donnée à mettre à jour']);
            return;
        }
        
        $params[] = $id;
        $sql = "UPDATE categories SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['success' => true, 'message' => 'Catégorie mise à jour avec succès']);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
    }
}

function deleteCategory() {
    global $pdo;
    
    if (!isset($_SESSION['user_id']) || !is_admin()) {
        http_response_code(403);
        echo json_encode(['error' => 'Accès non autorisé']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de catégorie requis']);
        return;
    }
    
    try {
        // Vérifier s'il y a des produits dans cette catégorie
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $checkStmt->execute([$id]);
        $product_count = $checkStmt->fetchColumn();
        
        if ($product_count > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Impossible de supprimer une catégorie contenant des produits']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Catégorie supprimée avec succès']);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
    }
}
?>