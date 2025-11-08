<?php
require_once 'includes/config.php';

if (!isset($_GET['id'])) {
    header('Location: artisans.php');
    exit;
}

$artisan_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT a.*, u.name, u.email, c.name as category_name 
        FROM artisans a 
        JOIN users u ON a.user_id = u.id 
        JOIN categories c ON a.category_id = c.id 
        WHERE a.id = ? AND a.status = 'active'
    ");
    $stmt->execute([$artisan_id]);
    $artisan = $stmt->fetch();
    
    if (!$artisan) {
        header('Location: 404.php');
        exit;
    }
    
    // Récupérer les produits de l'artisan
    $stmt = $pdo->prepare("SELECT * FROM products WHERE artisan_id = ? AND status = 'active'");
    $stmt->execute([$artisan_id]);
    $products = $stmt->fetchAll();
    
} catch (PDOException $e) {
    header('Location: 500.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $artisan['name']; ?> - Artisanat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <img src="<?php echo $artisan['profile_image'] ?: 'assets/images/default-artisan.jpg'; ?>" 
                     class="img-fluid rounded" alt="<?php echo $artisan['name']; ?>">
            </div>
            <div class="col-md-8">
                <h1><?php echo $artisan['name']; ?></h1>
                <p class="lead"><?php echo $artisan['tagline']; ?></p>
                <p><strong>Catégorie:</strong> <?php echo $artisan['category_name']; ?></p>
                <p><strong>Localisation:</strong> <?php echo $artisan['city']; ?></p>
                <p><strong>Expérience:</strong> <?php echo $artisan['experience']; ?> ans</p>
                
                <div class="mt-4">
                    <h4>À propos</h4>
                    <p><?php echo $artisan['description']; ?></p>
                </div>
                
                <div class="mt-4">
                    <h4>Contact</h4>
                    <p><strong>Email:</strong> <?php echo $artisan['email']; ?></p>
                    <p><strong>Téléphone:</strong> <?php echo $artisan['phone'] ?: 'Non renseigné'; ?></p>
                </div>
            </div>
        </div>
        
        <?php if (!empty($products)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3>Produits de cet artisan</h3>
                <div class="row">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-3 mb-4">
                            <div class="card h-100">
                                <img src="<?php echo $product['image'] ?: 'assets/images/default-product.jpg'; ?>" 
                                     class="card-img-top" alt="<?php echo $product['name']; ?>"
                                     style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                    <p class="card-text"><?php echo substr($product['description'], 0, 80); ?>...</p>
                                    <p class="card-text"><strong><?php echo $product['price']; ?> €</strong></p>
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">
                                        Voir le produit
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>