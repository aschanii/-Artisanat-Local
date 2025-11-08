<?php
require_once 'includes/config.php';

$page_title = "Confirmation de commande - Artisanat Local";

// Récupérer le numéro de commande de la session
$order_number = $_SESSION['last_order'] ?? null;

if (!$order_number) {
    header('Location: index.php');
    exit();
}

// Récupérer les détails de la commande
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
$stmt->execute([$order_number]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: index.php');
    exit();
}

// Récupérer les articles de la commande
$items_stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image as product_image, u.name as artisan_name 
                            FROM order_items oi 
                            LEFT JOIN products p ON oi.product_id = p.id 
                            LEFT JOIN users u ON oi.artisan_id = u.id 
                            WHERE oi.order_id = ?");
$items_stmt->execute([$order['id']]);
$order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Vider la session de commande
unset($_SESSION['last_order']);
?>
<?php include 'includes/header.php'; ?>

<main>
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1>Confirmation de commande</h1>
                <div class="breadcrumb">
                    <a href="index.php">Accueil</a>
                    <span>/</span>
                    <a href="cart.php">Panier</a>
                    <span>/</span>
                    <span>Confirmation</span>
                </div>
            </div>
        </div>
    </section>

    <section class="section confirmation-section">
        <div class="container">
            <div class="confirmation-card">
                <div class="confirmation-header">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h1>Commande confirmée !</h1>
                    <p class="order-number">Numéro de commande : <strong><?php echo $order['order_number']; ?></strong></p>
                    <p class="confirmation-message">
                        Merci pour votre commande. Nous avons bien reçu votre paiement et traitons votre commande.
                    </p>
                </div>

                <div class="confirmation-details">
                    <div class="details-grid">
                        <div class="detail-item">
                            <h3>Informations de livraison</h3>
                            <div class="detail-content">
                                <p><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></p>
                                <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                                <p>Email : <?php echo htmlspecialchars($order['customer_email']); ?></p>
                                <p>Téléphone : <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                            </div>
                        </div>

                        <div class="detail-item">
                            <h3>Résumé de la commande</h3>
                            <div class="detail-content">
                                <div class="summary-row">
                                    <span>Sous-total :</span>
                                    <span><?php echo number_format($order['subtotal'], 2, ',', ' '); ?> €</span>
                                </div>
                                <div class="summary-row">
                                    <span>Livraison :</span>
                                    <span><?php echo number_format($order['shipping'], 2, ',', ' '); ?> €</span>
                                </div>
                                <div class="summary-row total">
                                    <span>Total :</span>
                                    <span><?php echo number_format($order['total'], 2, ',', ' '); ?> €</span>
                                </div>
                            </div>
                        </div>

                        <div class="detail-item">
                            <h3>Statut de la commande</h3>
                            <div class="detail-content">
                                <div class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php
                                    $status_labels = [
                                        'pending' => 'En attente',
                                        'confirmed' => 'Confirmée',
                                        'processing' => 'En traitement',
                                        'shipped' => 'Expédiée',
                                        'delivered' => 'Livrée'
                                    ];
                                    echo $status_labels[$order['status']];
                                    ?>
                                </div>
                                <p class="status-info">
                                    Vous recevrez un email de confirmation avec les détails de suivi.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="order-items">
                    <h3>Articles commandés</h3>
                    <div class="items-list">
                        <?php foreach ($order_items as $item): ?>
                            <div class="order-item">
                                <img src="<?php echo $item['product_image'] ?: 'images/product-placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                     class="item-image">
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                    <p class="artisan">Artisan : <?php echo htmlspecialchars($item['artisan_name']); ?></p>
                                </div>
                                <div class="item-quantity">
                                    Quantité : <?php echo $item['quantity']; ?>
                                </div>
                                <div class="item-price">
                                    <?php echo number_format($item['total'], 2, ',', ' '); ?> €
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="confirmation-actions">
                    <div class="action-buttons">
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i>
                            Continuer mes achats
                        </a>
                        <a href="profile.php" class="btn btn-outline">
                            <i class="fas fa-user"></i>
                            Voir mes commandes
                        </a>
                    </div>
                    
                    <div class="support-info">
                        <h4>Besoin d'aide ?</h4>
                        <p>Notre équipe de support est là pour vous aider :</p>
                        <div class="contact-methods">
                            <div class="contact-method">
                                <i class="fas fa-envelope"></i>
                                <span>contact@artisanat-local.com</span>
                            </div>
                            <div class="contact-method">
                                <i class="fas fa-phone"></i>
                                <span>+33 1 23 45 67 89</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>

<style>
.confirmation-section {
    padding: 2rem 0;
}

.confirmation-card {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    padding: 3rem;
    max-width: 800px;
    margin: 0 auto;
}

.confirmation-header {
    text-align: center;
    margin-bottom: 3rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--gray-light);
}

.success-icon {
    font-size: 4rem;
    color: var(--success-color);
    margin-bottom: 1rem;
}

.confirmation-header h1 {
    color: var(--success-color);
    margin-bottom: 1rem;
}

.order-number {
    font-size: 1.2rem;
    color: var(--dark-color);
    margin-bottom: 1rem;
}

.confirmation-message {
    color: var(--gray-dark);
    font-size: 1.1rem;
    line-height: 1.6;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.detail-item h3 {
    margin-bottom: 1rem;
    color: var(--dark-color);
    font-size: 1.2rem;
}

.detail-content {
    background: var(--light-color);
    padding: 1.5rem;
    border-radius: var(--border-radius);
}

.detail-content p {
    margin-bottom: 0.5rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--gray-light);
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-row.total {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--primary-color);
}

.status-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-confirmed {
    background: #cce7ff;
    color: #004085;
}

.status-processing {
    background: #d1ecf1;
    color: #0c5460;
}

.status-shipped {
    background: #d4edda;
    color: #155724;
}

.status-delivered {
    background: #d1f7e7;
    color: #0f5132;
}

.status-info {
    margin-top: 1rem;
    font-size: 0.9rem;
    color: var(--gray-dark);
}

.order-items {
    margin-bottom: 3rem;
}

.order-items h3 {
    margin-bottom: 1.5rem;
    color: var(--dark-color);
}

.items-list {
    border: 1px solid var(--gray-light);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.order-item {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-light);
    background: var(--light-color);
}

.order-item:last-child {
    border-bottom: none;
}

.order-item .item-image {
    width: 60px;
    height: 60px;
    border-radius: var(--border-radius);
    object-fit: cover;
    margin-right: 1rem;
}

.item-details {
    flex: 1;
}

.item-details h4 {
    margin-bottom: 0.5rem;
    color: var(--dark-color);
}

.artisan {
    color: var(--gray-dark);
    font-size: 0.9rem;
    margin: 0;
}

.item-quantity {
    margin-right: 2rem;
    color: var(--gray-dark);
}

.item-price {
    font-weight: 700;
    color: var(--primary-color);
    font-size: 1.1rem;
}

.confirmation-actions {
    text-align: center;
    padding-top: 2rem;
    border-top: 1px solid var(--gray-light);
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 2rem;
}

.support-info {
    background: var(--light-color);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    text-align: center;
}

.support-info h4 {
    margin-bottom: 1rem;
    color: var(--dark-color);
}

.contact-methods {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-top: 1rem;
}

.contact-method {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--gray-dark);
}

.contact-method i {
    color: var(--primary-color);
}

@media (max-width: 768px) {
    .confirmation-card {
        padding: 1.5rem;
        margin: 0 1rem;
    }
    
    .details-grid {
        grid-template-columns: 1fr;
    }
    
    .order-item {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .item-quantity {
        margin-right: 0;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .contact-methods {
        flex-direction: column;
        gap: 1rem;
    }
}
</style>