<?php
require_once 'includes/config.php';

$page_title = "Boutique - Artisanat Local";
$search = $_GET['search'] ?? '';
$category_id = $_GET['category'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Construire la requête SQL
$sql = "SELECT p.*, u.name as artisan_name, u.avatar as artisan_avatar, 
               COUNT(r.id) as review_count, AVG(r.rating) as average_rating,
               c.name as category_name
        FROM products p 
        LEFT JOIN users u ON p.artisan_id = u.id 
        LEFT JOIN reviews r ON p.id = r.product_id 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = 1";

$params = [];

if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.tags LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($category_id)) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_id;
}

if (!empty($min_price)) {
    $sql .= " AND p.price >= ?";
    $params[] = $min_price;
}

if (!empty($max_price)) {
    $sql .= " AND p.price <= ?";
    $params[] = $max_price;
}

$sql .= " GROUP BY p.id";

// Tri
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
// $page = $_GET['page'] ?? 1;
// $limit = 12;
// $offset = ($page - 1) * $limit;

// $stmt = $pdo->prepare($sql);
// $stmt->execute($params);
// $sql .= " LIMIT $limit OFFSET $offset";

// $stmt = $pdo->prepare($sql);
// $stmt->execute($params);

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// D'abord, compter le nombre total de produits sans LIMIT
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$total_products = $stmt->rowCount();

// Ajouter LIMIT et OFFSET directement (MySQL n'accepte pas les ? ici)
$sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

// Exécuter la requête paginée
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les catégories pour les filtres
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<main>
    <!-- En-tête de page -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1>Notre Boutique</h1>
                <p>Découvrez toutes nos créations artisanales</p>
                <div class="breadcrumb">
                    <a href="index.php">Accueil</a>
                    <span>/</span>
                    <span>Boutique</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Filtres et recherche -->
    <section class="filters-section">
        <div class="container">
            <div class="filters-content">
                <div class="filter-group">
                    <div class="filter-item">
                        <label for="category-filter">Catégorie:</label>
                        <select id="category-filter" class="filter-select" onchange="updateFilters()">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                    <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-item">
                        <label for="price-filter">Prix:</label>
                        <select id="price-filter" class="filter-select" onchange="updateFilters()">
                            <option value="">Tous les prix</option>
                            <option value="0-50" <?php echo $min_price == 0 && $max_price == 50 ? 'selected' : ''; ?>>0€ - 50€</option>
                            <option value="50-100" <?php echo $min_price == 50 && $max_price == 100 ? 'selected' : ''; ?>>50€ - 100€</option>
                            <option value="100-200" <?php echo $min_price == 100 && $max_price == 200 ? 'selected' : ''; ?>>100€ - 200€</option>
                            <option value="200-9999" <?php echo $min_price == 200 ? 'selected' : ''; ?>>200€ et plus</option>
                        </select>
                    </div>

                    <div class="filter-item">
                        <label for="sort-filter">Trier par:</label>
                        <select id="sort-filter" class="filter-select" onchange="updateFilters()">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Nouveautés</option>
                            <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Populaires</option>
                            <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Prix croissant</option>
                            <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Prix décroissant</option>
                            <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Meilleures notes</option>
                        </select>
                    </div>
                </div>

                <div class="filter-results">
                    <span><?php echo $total_products; ?> produit(s) trouvé(s)</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Liste des produits -->
    <section class="section">
        <div class="container">
            <?php if (!empty($search)): ?>
                <div class="search-results-header">
                    <h2>Résultats pour "<?php echo htmlspecialchars($search); ?>"</h2>
                    <a href="products.php" class="btn btn-outline btn-small">
                        <i class="fas fa-times"></i>
                        Effacer la recherche
                    </a>
                </div>
            <?php endif; ?>

            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Aucun produit trouvé</h3>
                    <p>Essayez de modifier vos critères de recherche ou explorez nos autres catégories.</p>
                    <a href="products.php" class="btn btn-primary">Voir tous les produits</a>
                </div>
            <?php else: ?>
                <div class="products-grid" id="products-container">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card" data-product-id="<?php echo $product['id']; ?>">
                            <?php if ($product['is_featured']): ?>
                                <span class="product-badge featured">Populaire</span>
                            <?php endif; ?>
                            <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                                <span class="product-badge sale">Promo</span>
                            <?php endif; ?>

                            <div class="product-image-container">
                                <img src="<?php echo $product['image'] ?: 'images/product-placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="product-image"
                                     onclick="window.location.href='product.php?id=<?php echo $product['id']; ?>'">
                                <div class="product-actions">
                                    <button class="action-btn wishlist-btn" onclick="toggleWishlist(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                    <button class="action-btn quick-view-btn" onclick="quickView(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="product-content">
                                <div class="product-meta">
                                    <div class="artisan-info">
                                        <img src="<?php echo $product['artisan_avatar'] ?: 'images/avatar-placeholder.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($product['artisan_name']); ?>" 
                                             class="artisan-avatar">
                                        <span><?php echo htmlspecialchars($product['artisan_name']); ?></span>
                                    </div>
                                    <button class="wishlist-btn" onclick="toggleWishlist(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                </div>

                                <h3 class="product-name" onclick="window.location.href='product.php?id=<?php echo $product['id']; ?>'">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </h3>
                                
                                <p class="product-description">
                                    <?php echo htmlspecialchars($product['description']); ?>
                                </p>

                                <div class="product-price">
                                    <span class="current-price">
                                        <?php echo number_format($product['price'], 2, ',', ' '); ?> €
                                    </span>
                                    <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                                        <span class="original-price">
                                            <?php echo number_format($product['compare_price'], 2, ',', ' '); ?> €
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="product-rating">
                                    <div class="stars">
                                        <?php
                                        $rating = $product['average_rating'] ?: 0;
                                        $full_stars = floor($rating);
                                        $half_star = $rating - $full_stars >= 0.5;
                                        $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                                        
                                        for ($i = 0; $i < $full_stars; $i++) {
                                            echo '<i class="fas fa-star"></i>';
                                        }
                                        if ($half_star) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        }
                                        for ($i = 0; $i < $empty_stars; $i++) {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                        ?>
                                    </div>
                                    <span class="rating-count">(<?php echo $product['review_count'] ?: 0; ?>)</span>
                                </div>

                                <button class="btn btn-primary add-to-cart-btn" 
                                        onclick="addToCart(<?php echo $product['id']; ?>)" 
                                        <?php echo $product['stock'] === 0 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-shopping-bag"></i>
                                    <?php echo $product['stock'] === 0 ? 'Rupture de stock' : 'Ajouter au panier'; ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_products > $limit): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                               class="pagination-btn">
                                <i class="fas fa-chevron-left"></i>
                                Précédent
                            </a>
                        <?php endif; ?>

                        <?php
                        $total_pages = ceil($total_products / $limit);
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                               class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                               class="pagination-btn">
                                Suivant
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
<script>
function updateFilters() {
    const category = document.getElementById('category-filter').value;
    const price = document.getElementById('price-filter').value;
    const sort = document.getElementById('sort-filter').value;
    
    const params = new URLSearchParams();
    
    if (category) params.append('category', category);
    if (price) {
        const [min, max] = price.split('-');
        params.append('min_price', min);
        params.append('max_price', max);
    }
    if (sort) params.append('sort', sort);
    
    window.location.href = 'products.php?' + params.toString();
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});
</script>