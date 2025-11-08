<?php
require_once 'includes/config.php';

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = $_GET['id'];

// Récupérer le produit
$stmt = $pdo->prepare("SELECT p.*, u.name as artisan_name, u.avatar as artisan_avatar, 
                               u.bio as artisan_bio, u.social_facebook, u.social_instagram,
                               c.name as category_name,
                               COUNT(r.id) as review_count, AVG(r.rating) as average_rating
                        FROM products p 
                        LEFT JOIN users u ON p.artisan_id = u.id 
                        LEFT JOIN categories c ON p.category_id = c.id
                        LEFT JOIN reviews r ON p.id = r.product_id 
                        WHERE p.id = ? AND p.is_active = 1
                        GROUP BY p.id");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: products.php');
    exit();
}

// Incrémenter le compteur de vues
$pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?")->execute([$product_id]);

// Récupérer les produits similaires
$similar_products = $pdo->prepare("SELECT p.*, u.name as artisan_name 
                                   FROM products p 
                                   LEFT JOIN users u ON p.artisan_id = u.id 
                                   WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1 
                                   ORDER BY RAND() LIMIT 4")
                         ->execute([$product['category_id'], $product_id]);
$similar_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les avis
$reviews = $pdo->prepare("SELECT r.*, u.name as user_name, u.avatar as user_avatar 
                          FROM reviews r 
                          LEFT JOIN users u ON r.user_id = u.id 
                          WHERE r.product_id = ? AND r.is_approved = 1 
                          ORDER BY r.created_at DESC LIMIT 10")
                ->execute([$product_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = $product['name'] . " - Artisanat Local";
?>
<?php include 'includes/header.php'; ?>

<main>
    <!-- En-tête de page -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <div class="breadcrumb">
                    <a href="index.php">Accueil</a>
                    <span>/</span>
                    <a href="products.php">Boutique</a>
                    <span>/</span>
                    <a href="products.php?category=<?php echo $product['category_id']; ?>">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </a>
                    <span>/</span>
                    <span><?php echo htmlspecialchars($product['name']); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- Détail du produit -->
    <section class="section product-detail-section">
        <div class="container">
            <div class="product-detail">
                <!-- Galerie d'images -->
                <div class="product-gallery">
                    <div class="main-image">
                        <img src="<?php echo $product['image'] ?: 'images/product-placeholder.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             id="main-product-image">
                    </div>
                    <?php if (!empty($product['gallery'])): 
                        $gallery = json_decode($product['gallery'], true);
                    ?>
                        <div class="image-thumbnails">
                            <div class="thumbnail active" onclick="changeMainImage('<?php echo $product['image']; ?>')">
                                <img src="<?php echo $product['image']; ?>" alt="Image principale">
                            </div>
                            <?php foreach ($gallery as $image): ?>
                                <div class="thumbnail" onclick="changeMainImage('<?php echo $image; ?>')">
                                    <img src="<?php echo $image; ?>" alt="Galerie produit">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Informations produit -->
                <div class="product-info">
                    <div class="product-header">
                        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                        
                        <div class="product-meta">
                            <div class="artisan-info">
                                <img src="<?php echo $product['artisan_avatar'] ?: 'images/avatar-placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['artisan_name']); ?>" 
                                     class="artisan-avatar">
                                <div>
                                    <span class="artisan-name"><?php echo htmlspecialchars($product['artisan_name']); ?></span>
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
                                        <span class="rating-count">(<?php echo $product['review_count'] ?: 0; ?> avis)</span>
                                    </div>
                                </div>
                            </div>
                            
                            <button class="wishlist-btn" onclick="toggleWishlist(<?php echo $product['id']; ?>)">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                    </div>

                    <div class="product-price">
                        <span class="current-price"><?php echo number_format($product['price'], 2, ',', ' '); ?> €</span>
                        <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                            <span class="original-price"><?php echo number_format($product['compare_price'], 2, ',', ' '); ?> €</span>
                            <span class="discount-badge">
                                -<?php echo number_format((($product['compare_price'] - $product['price']) / $product['compare_price']) * 100, 0); ?>%
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="product-stock">
                        <?php if ($product['stock'] > 0): ?>
                            <span class="stock-available">
                                <i class="fas fa-check-circle"></i>
                                En stock (<?php echo $product['stock']; ?> disponible(s))
                            </span>
                        <?php else: ?>
                            <span class="stock-out">
                                <i class="fas fa-times-circle"></i>
                                Rupture de stock
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="product-description">
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        
                        <?php if (!empty($product['detailed_description'])): ?>
                            <div class="detailed-description">
                                <?php echo nl2br(htmlspecialchars($product['detailed_description'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="product-actions">
                        <div class="quantity-selector">
                            <button type="button" class="quantity-btn" onclick="decreaseQuantity()">-</button>
                            <input type="number" id="product-quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                            <button type="button" class="quantity-btn" onclick="increaseQuantity()">+</button>
                        </div>

                        <button class="btn btn-primary add-to-cart-large" 
                                onclick="addToCart(<?php echo $product['id']; ?>, getQuantity())"
                                <?php echo $product['stock'] === 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-bag"></i>
                            <?php echo $product['stock'] === 0 ? 'Rupture de stock' : 'Ajouter au panier'; ?>
                        </button>

                        <button class="btn btn-outline" onclick="toggleWishlist(<?php echo $product['id']; ?>)">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>

                    <div class="product-features">
                        <div class="feature">
                            <i class="fas fa-shipping-fast"></i>
                            <span>Livraison gratuite à partir de 50€</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-undo-alt"></i>
                            <span>Retour gratuit sous 14 jours</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-shield-alt"></i>
                            <span>Paiement sécurisé</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Informations détaillées -->
    <section class="section product-tabs-section">
        <div class="container">
            <div class="product-tabs">
                <div class="tab-headers">
                    <button class="tab-header active" onclick="openTab('description')">Description</button>
                    <button class="tab-header" onclick="openTab('artisan')">L'Artisan</button>
                    <button class="tab-header" onclick="openTab('reviews')">Avis (<?php echo $product['review_count'] ?: 0; ?>)</button>
                    <button class="tab-header" onclick="openTab('shipping')">Livraison</button>
                </div>

                <div class="tab-content">
                    <!-- Description -->
                    <div id="description" class="tab-panel active">
                        <h3>À propos de ce produit</h3>
                        <div class="tab-content-inner">
                            <?php if (!empty($product['detailed_description'])): ?>
                                <?php echo nl2br(htmlspecialchars($product['detailed_description'])); ?>
                            <?php else: ?>
                                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($product['tags'])): 
                                $tags = json_decode($product['tags'], true);
                            ?>
                                <div class="product-tags">
                                    <h4>Tags</h4>
                                    <div class="tags-list">
                                        <?php foreach ($tags as $tag): ?>
                                            <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Artisan -->
                    <div id="artisan" class="tab-panel">
                        <div class="artisan-profile">
                            <div class="artisan-header">
                                <img src="<?php echo $product['artisan_avatar'] ?: 'images/avatar-placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['artisan_name']); ?>" 
                                     class="artisan-avatar-large">
                                <div class="artisan-info">
                                    <h3><?php echo htmlspecialchars($product['artisan_name']); ?></h3>
                                    <div class="artisan-stats">
                                        <span class="stat">
                                            <strong><?php 
                                                $product_count = $pdo->prepare("SELECT COUNT(*) FROM products WHERE artisan_id = ? AND is_active = 1")
                                                                   ->execute([$product['artisan_id']]);
                                                echo $stmt->fetchColumn();
                                            ?></strong> produits
                                        </span>
                                        <span class="stat">
                                            <strong><?php echo $product['review_count'] ?: 0; ?></strong> avis
                                        </span>
                                    </div>
                                    <div class="artisan-social">
                                        <?php if (!empty($product['social_facebook'])): ?>
                                            <a href="<?php echo $product['social_facebook']; ?>" class="social-link" target="_blank">
                                                <i class="fab fa-facebook"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($product['social_instagram'])): ?>
                                            <a href="<?php echo $product['social_instagram']; ?>" class="social-link" target="_blank">
                                                <i class="fab fa-instagram"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($product['artisan_bio'])): ?>
                                <div class="artisan-bio">
                                    <h4>À propos de l'artisan</h4>
                                    <p><?php echo nl2br(htmlspecialchars($product['artisan_bio'])); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <a href="artisan.php?id=<?php echo $product['artisan_id']; ?>" class="btn btn-outline">
                                Voir le profil complet
                            </a>
                        </div>
                    </div>

                    <!-- Avis -->
                    <div id="reviews" class="tab-panel">
                        <div class="reviews-header">
                            <div class="rating-overview">
                                <div class="average-rating">
                                    <span class="rating-number"><?php echo number_format($product['average_rating'] ?: 0, 1); ?></span>
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
                                    <span class="review-count"><?php echo $product['review_count'] ?: 0; ?> avis</span>
                                </div>
                            </div>
                            
                            <?php if (is_logged_in()): ?>
                                <button class="btn btn-primary" onclick="openReviewModal()">
                                    <i class="fas fa-pen"></i>
                                    Donner mon avis
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="reviews-list">
                            <?php if (empty($reviews)): ?>
                                <div class="empty-reviews">
                                    <i class="fas fa-comment-slash"></i>
                                    <p>Aucun avis pour le moment</p>
                                    <?php if (is_logged_in()): ?>
                                        <p>Soyez le premier à donner votre avis !</p>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <?php foreach ($reviews as $review): ?>
                                    <div class="review-item">
                                        <div class="review-header">
                                            <div class="reviewer-info">
                                                <img src="<?php echo $review['user_avatar'] ?: 'images/avatar-placeholder.jpg'; ?>" 
                                                     alt="<?php echo htmlspecialchars($review['user_name']); ?>" 
                                                     class="reviewer-avatar">
                                                <div>
                                                    <span class="reviewer-name"><?php echo htmlspecialchars($review['user_name']); ?></span>
                                                    <div class="review-rating">
                                                        <div class="stars">
                                                            <?php
                                                            for ($i = 1; $i <= 5; $i++) {
                                                                if ($i <= $review['rating']) {
                                                                    echo '<i class="fas fa-star"></i>';
                                                                } else {
                                                                    echo '<i class="far fa-star"></i>';
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                        <span class="review-date">
                                                            <?php echo date('d/m/Y', strtotime($review['created_at'])); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="review-content">
                                            <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Livraison -->
                    <div id="shipping" class="tab-panel">
                        <div class="shipping-info">
                            <h3>Informations de livraison</h3>
                            <div class="shipping-options">
                                <div class="shipping-option">
                                    <i class="fas fa-shipping-fast"></i>
                                    <div>
                                        <h4>Livraison standard</h4>
                                        <p>Livraison sous 3-5 jours ouvrés</p>
                                        <span class="shipping-price">4,90 € (Gratuite à partir de 50€)</span>
                                    </div>
                                </div>
                                <div class="shipping-option">
                                    <i class="fas fa-rocket"></i>
                                    <div>
                                        <h4>Livraison express</h4>
                                        <p>Livraison sous 24-48h</p>
                                        <span class="shipping-price">9,90 €</span>
                                    </div>
                                </div>
                                <div class="shipping-option">
                                    <i class="fas fa-store"></i>
                                    <div>
                                        <h4>Retrait en atelier</h4>
                                        <p>Retirez votre commande directement chez l'artisan</p>
                                        <span class="shipping-price">Gratuit</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="return-policy">
                                <h4>Politique de retour</h4>
                                <p>Vous disposez de 14 jours à compter de la réception de votre commande pour nous retourner tout article qui ne vous conviendrait pas. Les articles doivent être retournés dans leur état d'origine, non portés, non lavés et avec l'étiquette attachée.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Produits similaires -->
    <?php if (!empty($similar_products)): ?>
    <section class="section similar-products-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Produits similaires</h2>
                <p class="section-subtitle">Découvrez d'autres créations de la même catégorie</p>
            </div>
            <div class="products-grid">
                <?php foreach ($similar_products as $similar_product): ?>
                    <div class="product-card">
                        <div class="product-image-container">
                            <img src="<?php echo $similar_product['image'] ?: 'images/product-placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($similar_product['name']); ?>" 
                                 class="product-image"
                                 onclick="window.location.href='product.php?id=<?php echo $similar_product['id']; ?>'">
                            <div class="product-actions">
                                <button class="action-btn wishlist-btn" onclick="toggleWishlist(<?php echo $similar_product['id']; ?>)">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <button class="action-btn quick-view-btn" onclick="quickView(<?php echo $similar_product['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="product-content">
                            <div class="product-meta">
                                <div class="artisan-info">
                                    <img src="<?php echo $similar_product['artisan_avatar'] ?: 'images/avatar-placeholder.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($similar_product['artisan_name']); ?>" 
                                         class="artisan-avatar">
                                    <span><?php echo htmlspecialchars($similar_product['artisan_name']); ?></span>
                                </div>
                            </div>
                            
                            <h3 class="product-name" onclick="window.location.href='product.php?id=<?php echo $similar_product['id']; ?>'">
                                <?php echo htmlspecialchars($similar_product['name']); ?>
                            </h3>
                            
                            <div class="product-price">
                                <span class="current-price"><?php echo number_format($similar_product['price'], 2, ',', ' '); ?> €</span>
                            </div>
                            
                            <button class="btn btn-primary add-to-cart-btn" onclick="addToCart(<?php echo $similar_product['id']; ?>)">
                                <i class="fas fa-shopping-bag"></i>
                                Ajouter au panier
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>

<!-- Modal d'avis -->
<div id="reviewModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeReviewModal()">&times;</span>
        <h2>Donner votre avis</h2>
        <form id="reviewForm" onsubmit="submitReview(event)">
            <div class="form-group">
                <label>Note</label>
                <div class="rating-input">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                        <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="form-group">
                <label for="reviewComment">Votre avis</label>
                <textarea id="reviewComment" name="comment" class="form-control" rows="5" 
                          placeholder="Partagez votre expérience avec ce produit..." required></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline" onclick="closeReviewModal()">Annuler</button>
                <button type="submit" class="btn btn-primary">Publier l'avis</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.product-detail {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    margin-bottom: 4rem;
}

.product-gallery {
    position: sticky;
    top: 100px;
}

.main-image img {
    width: 100%;
    border-radius: var(--border-radius-lg);
    cursor: zoom-in;
}

.image-thumbnails {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.thumbnail {
    width: 80px;
    height: 80px;
    border-radius: var(--border-radius);
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    opacity: 0.7;
    transition: var(--transition);
}

.thumbnail.active,
.thumbnail:hover {
    opacity: 1;
    border-color: var(--primary-color);
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-header {
    margin-bottom: 1.5rem;
}

.product-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.product-meta {
    display: flex;
    justify-content: space-between;
    align-items: start;
}

.artisan-info {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.artisan-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.artisan-name {
    font-weight: 600;
    display: block;
}

.product-price {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.current-price {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
}

.original-price {
    font-size: 1.5rem;
    color: var(--gray-dark);
    text-decoration: line-through;
}

.discount-badge {
    background: var(--error-color);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
}

.product-stock {
    margin-bottom: 1.5rem;
}

.stock-available {
    color: var(--success-color);
    font-weight: 600;
}

.stock-out {
    color: var(--error-color);
    font-weight: 600;
}

.product-description {
    margin-bottom: 2rem;
    line-height: 1.8;
}

.detailed-description {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--gray-light);
}

.product-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.quantity-selector {
    display: flex;
    align-items: center;
    border: 1px solid var(--gray-light);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.quantity-btn {
    background: var(--light-color);
    border: none;
    padding: 0.8rem 1rem;
    cursor: pointer;
    transition: var(--transition);
}

.quantity-btn:hover {
    background: var(--gray-light);
}

#product-quantity {
    width: 60px;
    border: none;
    text-align: center;
    padding: 0.8rem 0;
    background: white;
}

.add-to-cart-large {
    flex: 1;
    justify-content: center;
}

.product-features {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.feature {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: var(--gray-dark);
}

.feature i {
    color: var(--primary-color);
    width: 20px;
}

/* Tabs */
.product-tabs {
    margin-top: 4rem;
}

.tab-headers {
    display: flex;
    border-bottom: 1px solid var(--gray-light);
    margin-bottom: 2rem;
}

.tab-header {
    background: none;
    border: none;
    padding: 1rem 2rem;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: var(--transition);
    font-weight: 500;
}

.tab-header.active {
    border-bottom-color: var(--primary-color);
    color: var(--primary-color);
}

.tab-panel {
    display: none;
}

.tab-panel.active {
    display: block;
}

.tab-content-inner {
    line-height: 1.8;
}

.product-tags {
    margin-top: 2rem;
}

.tags-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1rem;
}

.tag {
    background: var(--light-color);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

/* Artisan profile */
.artisan-profile {
    padding: 2rem 0;
}

.artisan-header {
    display: flex;
    align-items: center;
    gap: 2rem;
    margin-bottom: 2rem;
}

.artisan-avatar-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
}

.artisan-stats {
    display: flex;
    gap: 2rem;
    margin: 1rem 0;
}

.stat {
    font-size: 0.9rem;
    color: var(--gray-dark);
}

.artisan-social {
    display: flex;
    gap: 1rem;
}

.artisan-bio {
    margin-bottom: 2rem;
    line-height: 1.8;
}

/* Reviews */
.reviews-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.rating-overview {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.average-rating {
    text-align: center;
}

.rating-number {
    font-size: 3rem;
    font-weight: 700;
    display: block;
    line-height: 1;
}

.review-count {
    color: var(--gray-dark);
    margin-top: 0.5rem;
    display: block;
}

.review-item {
    padding: 2rem 0;
    border-bottom: 1px solid var(--gray-light);
}

.review-item:last-child {
    border-bottom: none;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
}

.reviewer-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.reviewer-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.reviewer-name {
    font-weight: 600;
    display: block;
}

.review-rating {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 0.5rem;
}

.review-date {
    color: var(--gray-dark);
    font-size: 0.9rem;
}

.review-content {
    line-height: 1.6;
}

.empty-reviews {
    text-align: center;
    padding: 3rem;
    color: var(--gray-dark);
}

.empty-reviews i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* Shipping */
.shipping-options {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.shipping-option {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: var(--light-color);
    border-radius: var(--border-radius);
}

.shipping-option i {
    font-size: 1.5rem;
    color: var(--primary-color);
    width: 40px;
}

.shipping-price {
    font-weight: 600;
    color: var(--primary-color);
}

.return-policy {
    padding: 1.5rem;
    background: var(--light-color);
    border-radius: var(--border-radius);
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: var(--white);
    margin: 5% auto;
    padding: 2rem;
    border-radius: var(--border-radius-lg);
    width: 90%;
    max-width: 500px;
    position: relative;
}

.close {
    position: absolute;
    right: 1rem;
    top: 1rem;
    font-size: 1.5rem;
    cursor: pointer;
}

.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 0.5rem;
}

.rating-input input {
    display: none;
}

.rating-input label {
    cursor: pointer;
    font-size: 1.5rem;
    color: var(--gray-light);
    transition: var(--transition);
}

.rating-input input:checked ~ label,
.rating-input label:hover,
.rating-input label:hover ~ label {
    color: var(--warning-color);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
}

/* Responsive */
@media (max-width: 768px) {
    .product-detail {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .product-gallery {
        position: static;
    }
    
    .product-title {
        font-size: 1.5rem;
    }
    
    .current-price {
        font-size: 1.5rem;
    }
    
    .product-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .quantity-selector {
        justify-content: center;
    }
    
    .tab-headers {
        flex-wrap: wrap;
    }
    
    .tab-header {
        flex: 1;
        min-width: 120px;
        text-align: center;
        padding: 0.8rem 1rem;
    }
    
    .reviews-header {
        flex-direction: column;
        gap: 1rem;
        align-items: start;
    }
    
    .artisan-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .artisan-stats {
        justify-content: center;
    }
}
</style>

<script>
function changeMainImage(src) {
    document.getElementById('main-product-image').src = src;
    document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
    event.target.closest('.thumbnail').classList.add('active');
}

function getQuantity() {
    return parseInt(document.getElementById('product-quantity').value);
}

function increaseQuantity() {
    const input = document.getElementById('product-quantity');
    const max = parseInt(input.max);
    if (input.value < max) {
        input.value = parseInt(input.value) + 1;
    }
}

function decreaseQuantity() {
    const input = document.getElementById('product-quantity');
    if (input.value > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

function openTab(tabName) {
    // Masquer tous les onglets
    document.querySelectorAll('.tab-panel').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-header').forEach(header => header.classList.remove('active'));
    
    // Afficher l'onglet sélectionné
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}

function openReviewModal() {
    document.getElementById('reviewModal').style.display = 'block';
}

function closeReviewModal() {
    document.getElementById('reviewModal').style.display = 'none';
}

function submitReview(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const rating = formData.get('rating');
    const comment = formData.get('comment');
    
    // Ici, vous enverriez les données à l'API
    fetch('api/reviews.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: <?php echo $product_id; ?>,
            rating: rating,
            comment: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Votre avis a été publié avec succès', 'success');
            closeReviewModal();
            // Recharger la page ou mettre à jour les avis
            location.reload();
        } else {
            showAlert('Erreur lors de la publication de l\'avis', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showAlert('Erreur lors de la publication de l\'avis', 'error');
    });
}

// Fermer la modal en cliquant à l'extérieur
window.onclick = function(event) {
    const modal = document.getElementById('reviewModal');
    if (event.target === modal) {
        closeReviewModal();
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});
</script>