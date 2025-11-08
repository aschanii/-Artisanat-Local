<?php
require_once 'includes/config.php';

// Récupérer les catégories
try {
    $stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catégories - Artisanat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center mb-5">Catégories d'Artisanat</h1>
        
        <div class="row">
            <?php if (empty($categories)): ?>
                <div class="col-12">
                    <div class="alert alert-info">Aucune catégorie trouvée pour le moment.</div>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card category-card">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo $category['name']; ?></h5>
                                <p class="card-text"><?php echo $category['description']; ?></p>
                                <a href="category-products.php?id=<?php echo $category['id']; ?>" 
                                   class="btn btn-outline-primary">
                                    Voir les produits
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>