<?php
require_once 'includes/config.php';

// Récupérer les artisans
try {
    $stmt = $pdo->query("
        SELECT a.*, u.name, u.email, c.name as category_name 
        FROM artisans a 
        JOIN users u ON a.user_id = u.id 
        JOIN categories c ON a.category_id = c.id 
        WHERE a.status = 'active'
    ");
    $artisans = $stmt->fetchAll();
} catch (PDOException $e) {
    $artisans = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tous les Artisans - Artisanat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center mb-5">Nos Artisans</h1>
        
        <div class="row">
            <?php if (empty($artisans)): ?>
                <div class="col-12">
                    <div class="alert alert-info">Aucun artisan trouvé pour le moment.</div>
                </div>
            <?php else: ?>
                <?php foreach ($artisans as $artisan): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?php echo $artisan['profile_image'] ?: 'assets/images/default-artisan.jpg'; ?>" 
                                 class="card-img-top" alt="<?php echo $artisan['name']; ?>" 
                                 style="height: 250px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $artisan['name']; ?></h5>
                                <p class="card-text">
                                    <span class="badge bg-primary"><?php echo $artisan['category_name']; ?></span>
                                </p>
                                <p class="card-text"><?php echo substr($artisan['description'], 0, 100); ?>...</p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo $artisan['city']; ?>
                                    </small>
                                </p>
                                <a href="artisan.php?id=<?php echo $artisan['id']; ?>" class="btn btn-primary">
                                    Voir le profil
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