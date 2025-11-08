<?php
require_once 'includes/config.php';

$page_title = "Panier - Artisanat Local";

// Gestion des actions du panier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = $_POST['product_id'] ?? '';
    
    if ($action === 'update') {
        $quantity = intval($_POST['quantity']);
        if ($quantity > 0) {
            // Mettre à jour la quantité dans la session
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] == $product_id) {
                    $item['quantity'] = $quantity;
                    break;
                }
            }
        } else {
            // Supprimer l'article
            $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($product_id) {
                return $item['id'] != $product_id;
            });
        }
    } elseif ($action === 'remove') {
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($product_id) {
            return $item['id'] != $product_id;
        });
    }
    
    header('Location: cart.php');
    exit();
}

$cart_items = $_SESSION['cart'] ?? [];
$subtotal = 0;
$shipping = 4.90;
$free_shipping_threshold = 50;

foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

if ($subtotal >= $free_shipping_threshold) {
    $shipping = 0;
}

$total = $subtotal + $shipping;
?>
<?php include 'includes/header.php'; ?>

<main>
    <!-- En-tête de page -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1>Votre Panier</h1>
                <div class="breadcrumb">
                    <a href="index.php">Accueil</a>
                    <span>/</span>
                    <span>Panier</span>
                </div>
            </div>
        </div>
    </section>

    <section class="section cart-section">
        <div class="container">
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <div class="empty-cart-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h2>Votre panier est vide</h2>
                    <p>Découvrez nos créations artisanales et remplissez votre panier de pièces uniques.</p>
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-store"></i>
                        Découvrir la boutique
                    </a>
                </div>
            <?php else: ?>
                <div class="cart-layout">
                    <!-- Liste des articles -->
                    <div class="cart-items">
                        <div class="cart-header">
                            <h2>Vos articles (<?php echo count($cart_items); ?>)</h2>
                        </div>
                        
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item" data-product-id="<?php echo $item['id']; ?>">
                                <div class="item-image">
                                    <img src="<?php echo $item['image'] ?: 'images/product-placeholder.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                
                                <div class="item-details">
                                    <h3 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="item-artisan">
                                        Par <strong><?php echo htmlspecialchars($item['artisan_name']); ?></strong>
                                    </p>
                                    <div class="item-price-mobile">
                                        <?php echo number_format($item['price'], 2, ',', ' '); ?> €
                                    </div>
                                </div>
                                
                                <div class="item-quantity">
                                    <div class="quantity-selector">
                                        <button type="button" class="quantity-btn" 
                                                onclick="updateCartQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                                            -
                                        </button>
                                        <span class="quantity-display"><?php echo $item['quantity']; ?></span>
                                        <button type="button" class="quantity-btn" 
                                                onclick="updateCartQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                            +
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="item-price">
                                    <span class="price"><?php echo number_format($item['price'] * $item['quantity'], 2, ',', ' '); ?> €</span>
                                    <span class="unit-price"><?php echo number_format($item['price'], 2, ',', ' '); ?> € l'unité</span>
                                </div>
                                
                                <div class="item-actions">
                                    <button class="remove-btn" onclick="removeFromCart(<?php echo $item['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Résumé de commande -->
                    <div class="cart-summary">
                        <div class="summary-card">
                            <h3>Résumé de commande</h3>
                            
                            <div class="summary-row">
                                <span>Sous-total</span>
                                <span><?php echo number_format($subtotal, 2, ',', ' '); ?> €</span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Livraison</span>
                                <span>
                                    <?php if ($shipping > 0): ?>
                                        <?php echo number_format($shipping, 2, ',', ' '); ?> €
                                        <small>Gratuite à partir de <?php echo $free_shipping_threshold; ?> €</small>
                                    <?php else: ?>
                                        <span class="free-shipping">Gratuite</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            
                            <?php if ($shipping > 0 && $subtotal < $free_shipping_threshold): ?>
                                <div class="shipping-progress">
                                    <div class="progress-bar">
                                        <div class="progress" style="width: <?php echo ($subtotal / $free_shipping_threshold) * 100; ?>%"></div>
                                    </div>
                                    <p>Plus que <?php echo number_format($free_shipping_threshold - $subtotal, 2, ',', ' '); ?> € pour la livraison gratuite</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="summary-row total">
                                <span>Total</span>
                                <span><?php echo number_format($total, 2, ',', ' '); ?> €</span>
                            </div>
                            
                            <div class="summary-actions">
                                <a href="checkout.php" class="btn btn-primary btn-large">
                                    <i class="fas fa-lock"></i>
                                    Procéder au paiement
                                </a>
                                <a href="products.php" class="btn btn-outline">
                                    <i class="fas fa-arrow-left"></i>
                                    Continuer mes achats
                                </a>
                            </div>
                            
                            <div class="security-badges">
                                <div class="badge">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Paiement sécurisé</span>
                                </div>
                                <div class="badge">
                                    <i class="fas fa-truck"></i>
                                    <span>Livraison rapide</span>
                                </div>
                                <div class="badge">
                                    <i class="fas fa-undo-alt"></i>
                                    <span>Retour facile</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Produits recommandés -->
                <section class="section recommended-products">
                    <div class="container">
                        <h2>Vous aimerez aussi</h2>
                        <div class="products-grid" id="recommended-products">
                            <!-- Les produits recommandés seront chargés dynamiquement -->
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>

<style>
.cart-section {
    padding: 2rem 0;
}

.empty-cart {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-cart-icon {
    font-size: 4rem;
    color: var(--gray-light);
    margin-bottom: 2rem;
}

.empty-cart h2 {
    margin-bottom: 1rem;
    color: var(--dark-color);
}

.empty-cart p {
    color: var(--gray-dark);
    margin-bottom: 2rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.cart-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
    align-items: start;
}

.cart-items {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.cart-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-light);
    background: var(--light-color);
}

.cart-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr auto auto auto;
    gap: 1.5rem;
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-light);
    align-items: center;
}

.cart-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 100px;
    height: 100px;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details {
    min-width: 0;
}

.item-name {
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.item-artisan {
    color: var(--gray-dark);
    font-size: 0.9rem;
    margin: 0;
}

.item-price-mobile {
    display: none;
    font-weight: 600;
    color: var(--primary-color);
    font-size: 1.1rem;
    margin-top: 0.5rem;
}

.item-quantity .quantity-selector {
    display: flex;
    align-items: center;
    border: 1px solid var(--gray-light);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.item-quantity .quantity-btn {
    background: var(--light-color);
    border: none;
    padding: 0.5rem 0.8rem;
    cursor: pointer;
    transition: var(--transition);
}

.item-quantity .quantity-btn:hover {
    background: var(--gray-light);
}

.item-quantity .quantity-display {
    padding: 0.5rem 1rem;
    min-width: 50px;
    text-align: center;
    background: white;
}

.item-price {
    text-align: right;
}

.item-price .price {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary-color);
    display: block;
}

.item-price .unit-price {
    font-size: 0.9rem;
    color: var(--gray-dark);
}

.item-actions {
    text-align: center;
}

.remove-btn {
    background: none;
    border: none;
    color: var(--error-color);
    cursor: pointer;
    padding: 0.5rem;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.remove-btn:hover {
    background: var(--error-color);
    color: white;
}

.cart-summary {
    position: sticky;
    top: 100px;
}

.summary-card {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    padding: 2rem;
}

.summary-card h3 {
    margin-bottom: 1.5rem;
    font-size: 1.3rem;
    text-align: center;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid var(--gray-light);
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-row.total {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary-color);
}

.free-shipping {
    color: var(--success-color);
    font-weight: 600;
}

.summary-row small {
    display: block;
    font-size: 0.8rem;
    color: var(--gray-dark);
    margin-top: 0.2rem;
}

.shipping-progress {
    margin: 1rem 0;
    padding: 1rem;
    background: var(--light-color);
    border-radius: var(--border-radius);
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: var(--gray-light);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress {
    height: 100%;
    background: var(--primary-color);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.shipping-progress p {
    margin: 0;
    font-size: 0.9rem;
    color: var(--gray-dark);
    text-align: center;
}

.summary-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin: 2rem 0;
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1.1rem;
}

.security-badges {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.badge {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.8rem;
    background: var(--light-color);
    border-radius: var(--border-radius);
    font-size: 0.9rem;
}

.badge i {
    color: var(--primary-color);
    width: 20px;
}

.recommended-products {
    margin-top: 4rem;
    padding-top: 3rem;
    border-top: 1px solid var(--gray-light);
}

.recommended-products h2 {
    text-align: center;
    margin-bottom: 2rem;
    font-size: 1.8rem;
}

/* Responsive */
@media (max-width: 968px) {
    .cart-layout {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .cart-summary {
        position: static;
    }
}

@media (max-width: 768px) {
    .cart-item {
        grid-template-columns: 80px 1fr auto;
        gap: 1rem;
        padding: 1rem;
        position: relative;
    }
    
    .item-image {
        width: 80px;
        height: 80px;
    }
    
    .item-quantity {
        grid-column: 1 / 3;
        justify-self: start;
        margin-top: 1rem;
    }
    
    .item-price {
        display: none;
    }
    
    .item-price-mobile {
        display: block;
    }
    
    .item-actions {
        position: absolute;
        top: 1rem;
        right: 1rem;
    }
    
    .summary-actions .btn {
        text-align: center;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .cart-item {
        grid-template-columns: 60px 1fr auto;
    }
    
    .item-image {
        width: 60px;
        height: 60px;
    }
    
    .item-name {
        font-size: 1rem;
    }
    
    .summary-card {
        padding: 1.5rem;
    }
}
</style>

<script>
function updateCartQuantity(productId, newQuantity) {
    if (newQuantity < 1) return;
    
    fetch('api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'update',
            product_id: productId,
            quantity: newQuantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showAlert('Erreur lors de la mise à jour du panier', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showAlert('Erreur lors de la mise à jour du panier', 'error');
    });
}

function removeFromCart(productId) {
    if (!confirm('Êtes-vous sûr de vouloir retirer cet article de votre panier ?')) {
        return;
    }
    
    fetch('api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'remove',
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showAlert('Erreur lors de la suppression de l\'article', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showAlert('Erreur lors de la suppression de l\'article', 'error');
    });
}

// Charger les produits recommandés
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('recommended-products')) {
        loadRecommendedProducts();
    }
    updateCartCount();
});

async function loadRecommendedProducts() {
    try {
        const response = await fetch('api/products.php?featured=true&limit=4');
        const products = await response.json();
        
        const grid = document.getElementById('recommended-products');
        if (grid) {
            renderProductsGrid(products, grid);
        }
    } catch (error) {
        console.error('Erreur chargement produits recommandés:', error);
    }
}
</script>