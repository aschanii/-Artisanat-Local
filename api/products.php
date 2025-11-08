<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getProducts();
        break;
    case 'POST':
        createProduct();
        break;
    case 'PUT':
        updateProduct();
        break;
    case 'DELETE':
        deleteProduct();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}

function getProducts() {
    global $pdo;
    
    $id = $_GET['id'] ?? null;
    $category_id = $_GET['category_id'] ?? null;
    $artisan_id = $_GET['artisan_id'] ?? null;
    $featured = $_GET['featured'] ?? null;
    $new = $_GET['new'] ?? null;
    $search = $_GET['search'] ?? null;
    $limit = $_GET['limit'] ?? 20;
    $offset = $_GET['offset'] ?? 0;
    
    try {
        $sql = "SELECT p.*, u.name as artisan_name, u.avatar as artisan_avatar, 
                       c.name as category_name,
                       COUNT(r.id) as review_count, 
                       COALESCE(AVG(r.rating), 0) as average_rating
                FROM products p 
                LEFT JOIN users u ON p.artisan_id = u.id 
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN reviews r ON p.id = r.product_id AND r.is_approved = 1 
                WHERE p.is_active = 1";
        
        $params = [];
        $conditions = [];
        
        if ($id) {
            $conditions[] = "p.id = ?";
            $params[] = $id;
        }
        
        if ($category_id) {
            $conditions[] = "p.category_id = ?";
            $params[] = $category_id;
        }
        
        if ($artisan_id) {
            $conditions[] = "p.artisan_id = ?";
            $params[] = $artisan_id;
        }
        
        if ($featured) {
            $conditions[] = "p.is_featured = 1";
        }
        
        if ($new) {
            $conditions[] = "p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        }
        
        if ($search) {
            $conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.tags LIKE ?)";
            $search_term = "%$search%";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }
        
        $sql .= " GROUP BY p.id";
        
        // Tri
        $sort = $_GET['sort'] ?? 'newest';
        switch ($sort) {
            case 'price_asc':
                $sql .= " ORDER BY p.price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY p.price DESC";
                break;
            case 'popular':
                $sql .= " ORDER BY p.views DESC, p.created_at DESC";
                break;
            case 'rating':
                $sql .= " ORDER BY average_rating DESC, p.created_at DESC";
                break;
            default:
                $sql .= " ORDER BY p.created_at DESC";
                break;
        }
        
        // Pagination
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
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formater les données
        foreach ($products as &$product) {
            $product['price'] = floatval($product['price']);
            $product['compare_price'] = $product['compare_price'] ? floatval($product['compare_price']) : null;
            $product['average_rating'] = floatval($product['average_rating']);
            $product['review_count'] = intval($product['review_count']);
            $product['stock'] = intval($product['stock']);
            $product['views'] = intval($product['views']);
            
            // Convertir les champs JSON
            if ($product['gallery']) {
                $product['gallery'] = json_decode($product['gallery'], true);
            } else {
                $product['gallery'] = [];
            }
            
            if ($product['tags']) {
                $product['tags'] = json_decode($product['tags'], true);
            } else {
                $product['tags'] = [];
            }
        }
        
        if ($id && count($products) > 0) {
            echo json_encode($products[0]);
        } else {
            echo json_encode($products);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la récupération des produits: ' . $e->getMessage()]);
    }
}

function createProduct() {
    global $pdo;
    
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
        http_response_code(403);
        echo json_encode(['error' => 'Accès non autorisé']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validation des champs requis
    $required = ['name', 'description', 'price', 'category_id', 'stock'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Le champ '$field' est requis"]);
            return;
        }
    }
    
    try {
        // Gérer l'upload d'image si présent
        $image_path = $input['image'] ?? null;
        
        // Préparer les données pour la base de données
        $name = $input['name'];
        $description = $input['description'];
        $detailed_description = $input['detailed_description'] ?? '';
        $price = floatval($input['price']);
        $compare_price = isset($input['compare_price']) ? floatval($input['compare_price']) : null;
        $category_id = intval($input['category_id']);
        $stock = intval($input['stock']);
        $sku = $input['sku'] ?? null;
        $tags = isset($input['tags']) ? json_encode($input['tags']) : null;
        $is_featured = isset($input['is_featured']) ? (bool)$input['is_featured'] : false;
        
        $stmt = $pdo->prepare("INSERT INTO products (name, description, detailed_description, price, compare_price, image, category_id, artisan_id, stock, sku, tags, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $name,
            $description,
            $detailed_description,
            $price,
            $compare_price,
            $image_path,
            $category_id,
            $_SESSION['user_id'],
            $stock,
            $sku,
            $tags,
            $is_featured
        ]);
        
        $product_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'product_id' => $product_id,
            'message' => 'Produit créé avec succès'
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la création du produit: ' . $e->getMessage()]);
    }
}

function updateProduct() {
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Non authentifié']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $product_id = $input['id'] ?? null;
    
    if (!$product_id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID du produit requis']);
        return;
    }
    
    try {
        // Vérifier que l'utilisateur peut modifier ce produit
        $checkStmt = $pdo->prepare("SELECT artisan_id FROM products WHERE id = ?");
        $checkStmt->execute([$product_id]);
        $product = $checkStmt->fetch();
        
        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Produit non trouvé']);
            return;
        }
        
        // Seul l'artisan propriétaire ou un admin peut modifier
        if ($_SESSION['user_role'] !== 'admin' && $product['artisan_id'] != $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Accès non autorisé']);
            return;
        }
        
        // Construire la requête de mise à jour dynamiquement
        $updates = [];
        $params = [];
        
        $fields = [
            'name', 'description', 'detailed_description', 'price', 'compare_price',
            'image', 'category_id', 'stock', 'sku', 'tags', 'is_featured', 'is_active'
        ];
        
        foreach ($fields as $field) {
            if (isset($input[$field])) {
                $updates[] = "$field = ?";
                
                if ($field === 'price' || $field === 'compare_price') {
                    $params[] = floatval($input[$field]);
                } elseif ($field === 'category_id' || $field === 'stock') {
                    $params[] = intval($input[$field]);
                } elseif ($field === 'is_featured' || $field === 'is_active') {
                    $params[] = (bool)$input[$field];
                } elseif ($field === 'tags' && is_array($input[$field])) {
                    $params[] = json_encode($input[$field]);
                } else {
                    $params[] = $input[$field];
                }
            }
        }
        
        if (empty($updates)) {
            http_response_code(400);
            echo json_encode(['error' => 'Aucune donnée à mettre à jour']);
            return;
        }
        
        $updates[] = "updated_at = NOW()";
        $params[] = $product_id;
        
        $sql = "UPDATE products SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['success' => true, 'message' => 'Produit mis à jour avec succès']);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
    }
}

function deleteProduct() {
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Non authentifié']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $product_id = $input['id'] ?? null;
    
    if (!$product_id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID du produit requis']);
        return;
    }
    
    try {
        // Vérifier que l'utilisateur peut supprimer ce produit
        $checkStmt = $pdo->prepare("SELECT artisan_id FROM products WHERE id = ?");
        $checkStmt->execute([$product_id]);
        $product = $checkStmt->fetch();
        
        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Produit non trouvé']);
            return;
        }
        
        // Seul l'artisan propriétaire ou un admin peut supprimer
        if ($_SESSION['user_role'] !== 'admin' && $product['artisan_id'] != $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Accès non autorisé']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        
        echo json_encode(['success' => true, 'message' => 'Produit supprimé avec succès']);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
    }
}
?>