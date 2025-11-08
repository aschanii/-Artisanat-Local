<?php
require_once 'includes/config.php';

$page_title = "Paiement - Artisanat Local";

// Vérifier que le panier n'est pas vide
$cart_items = $_SESSION['cart'] ?? [];
if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Calcul des totaux
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

// Traitement du formulaire de paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $country = $_POST['country'] ?? 'France';
    $payment_method = $_POST['payment_method'] ?? 'card';
    
    // Validation des données
    $errors = [];
    
    if (empty($name)) $errors[] = "Le nom est requis";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Un email valide est requis";
    if (empty($phone)) $errors[] = "Le téléphone est requis";
    if (empty($address)) $errors[] = "L'adresse est requise";
    if (empty($city)) $errors[] = "La ville est requise";
    if (empty($postal_code)) $errors[] = "Le code postal est requis";
    
    if (empty($errors)) {
        // Créer la commande
        $order_number = 'CMD-' . date('Ymd') . '-' . strtoupper(uniqid());
        
        try {
            $pdo->beginTransaction();
            
            // Insérer la commande
            $stmt = $pdo->prepare("INSERT INTO orders (order_number, user_id, total, subtotal, shipping, 
                                                      shipping_address, customer_name, customer_email, customer_phone, status) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            
            $shipping_address = "$address, $postal_code $city, $country";
            
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $stmt->execute([
                $order_number,
                $user_id,
                $total,
                $subtotal,
                $shipping,
                $shipping_address,
                $name,
                $email,
                $phone
            ]);
            
            $order_id = $pdo->lastInsertId();
            
            // Insérer les articles de la commande
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, artisan_id, quantity, price, total) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($cart_items as $item) {
                // Récupérer l'artisan du produit
                $artisan_stmt = $pdo->prepare("SELECT artisan_id FROM products WHERE id = ?");
                $artisan_stmt->execute([$item['id']]);
                $artisan_id = $artisan_stmt->fetchColumn();
                
                $item_total = $item['price'] * $item['quantity'];
                $stmt->execute([
                    $order_id,
                    $item['id'],
                    $artisan_id,
                    $item['quantity'],
                    $item['price'],
                    $item_total
                ]);
                
                // Mettre à jour le stock
                $update_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $update_stmt->execute([$item['quantity'], $item['id']]);
            }
            
            $pdo->commit();
            
            // Vider le panier
            $_SESSION['cart'] = [];
            
            // Rediriger vers la page de confirmation
            $_SESSION['last_order'] = $order_number;
            header('Location: order-confirmation.php');
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Erreur lors de la création de la commande: " . $e->getMessage();
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<main>
    <!-- En-tête de page -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1>Finaliser ma commande</h1>
                <div class="breadcrumb">
                    <a href="index.php">Accueil</a>
                    <span>/</span>
                    <a href="cart.php">Panier</a>
                    <span>/</span>
                    <span>Paiement</span>
                </div>
            </div>
        </div>
    </section>

    <section class="section checkout-section">
        <div class="container">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <h4>Erreurs de validation :</h4>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" id="checkout-form" class="checkout-form">
                <div class="checkout-layout">
                    <!-- Informations de livraison et paiement -->
                    <div class="checkout-main">
                        <!-- Informations personnelles -->
                        <div class="checkout-section-card">
                            <h2>Informations personnelles</h2>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="name" class="form-label">Nom complet *</label>
                                    <input type="text" id="name" name="name" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">Adresse email *</label>
                                    <input type="email" id="email" name="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone" class="form-label">Téléphone *</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Adresse de livraison -->
                        <div class="checkout-section-card">
                            <h2>Adresse de livraison</h2>
                            
                            <div class="form-group">
                                <label for="address" class="form-label">Adresse *</label>
                                <input type="text" id="address" name="address" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="city" class="form-label">Ville *</label>
                                    <input type="text" id="city" name="city" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="postal_code" class="form-label">Code postal *</label>
                                    <input type="text" id="postal_code" name="postal_code" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['postal_code'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="country" class="form-label">Pays *</label>
                                <select id="country" name="country" class="form-control" required>
                                    <option value="France" <?php echo ($_POST['country'] ?? 'France') === 'France' ? 'selected' : ''; ?>>France</option>
                                    <option value="Belgique" <?php echo ($_POST['country'] ?? '') === 'Belgique' ? 'selected' : ''; ?>>Belgique</option>
                                    <option value="Suisse" <?php echo ($_POST['country'] ?? '') === 'Suisse' ? 'selected' : ''; ?>>Suisse</option>
                                    <option value="Luxembourg" <?php echo ($_POST['country'] ?? '') === 'Luxembourg' ? 'selected' : ''; ?>>Luxembourg</option>
                                </select>
                            </div>
                        </div>

                        <!-- Méthode de paiement -->
                        <div class="checkout-section-card">
                            <h2>Méthode de paiement</h2>
                            
                            <div class="payment-methods">
                                <div class="payment-method">
                                    <input type="radio" id="payment-card" name="payment_method" value="card" 
                                           <?php echo ($_POST['payment_method'] ?? 'card') === 'card' ? 'checked' : ''; ?> required>
                                    <label for="payment-card">
                                        <i class="fab fa-cc-stripe"></i>
                                        <span>Carte bancaire</span>
                                        <small>Paiement sécurisé par Stripe</small>
                                    </label>
                                </div>
                                
                                <div class="payment-method">
                                    <input type="radio" id="payment-paypal" name="payment_method" value="paypal"
                                           <?php echo ($_POST['payment_method'] ?? '') === 'paypal' ? 'checked' : ''; ?>>
                                    <label for="payment-paypal">
                                        <i class="fab fa-paypal"></i>
                                        <span>PayPal</span>
                                        <small>Paiement rapide et sécurisé</small>
                                    </label>
                                </div>
                                
                                <div class="payment-method">
                                    <input type="radio" id="payment-transfer" name="payment_method" value="transfer"
                                           <?php echo ($_POST['payment_method'] ?? '') === 'transfer' ? 'checked' : ''; ?>>
                                    <label for="payment-transfer">
                                        <i class="fas fa-university"></i>
                                        <span>Virement bancaire</span>
                                        <small>Paiement par virement</small>
                                    </label>
                                </div>
                            </div>

                            <!-- Section de paiement par carte (affichée seulement si carte sélectionnée) -->
                            <div id="card-payment-section" class="card-payment-section">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="card-number" class="form-label">Numéro de carte</label>
                                        <input type="text" id="card-number" class="form-control" placeholder="1234 5678 9012 3456">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="card-expiry" class="form-label">Date d'expiration</label>
                                        <input type="text" id="card-expiry" class="form-control" placeholder="MM/AA">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="card-cvc" class="form-label">CVC</label>
                                        <input type="text" id="card-cvc" class="form-control" placeholder="123">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="card-name" class="form-label">Nom sur la carte</label>
                                    <input type="text" id="card-name" class="form-control" placeholder="John Doe">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Récapitulatif de commande -->
                    <div class="checkout-sidebar">
                        <div class="order-summary">
                            <h3>Votre commande</h3>
                            
                            <div class="order-items">
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="order-item">
                                        <div class="item-image">
                                            <img src="<?php echo $item['image'] ?: 'images/product-placeholder.jpg'; ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                                            <span class="item-quantity"><?php echo $item['quantity']; ?></span>
                                        </div>
                                        <div class="item-details">
                                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                            <p class="item-artisan"><?php echo htmlspecialchars($item['artisan_name']); ?></p>
                                        </div>
                                        <div class="item-price">
                                            <?php echo number_format($item['price'] * $item['quantity'], 2, ',', ' '); ?> €
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="order-totals">
                                <div class="total-row">
                                    <span>Sous-total</span>
                                    <span><?php echo number_format($subtotal, 2, ',', ' '); ?> €</span>
                                </div>
                                
                                <div class="total-row">
                                    <span>Livraison</span>
                                    <span>
                                        <?php if ($shipping > 0): ?>
                                            <?php echo number_format($shipping, 2, ',', ' '); ?> €
                                        <?php else: ?>
                                            <span class="free-shipping">Gratuite</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                
                                <div class="total-row grand-total">
                                    <span>Total</span>
                                    <span><?php echo number_format($total, 2, ',', ' '); ?> €</span>
                                </div>
                            </div>
                            
                            <div class="order-actions">
                                <button type="submit" class="btn btn-primary btn-large btn-block">
                                    <i class="fas fa-lock"></i>
                                    Payer maintenant
                                </button>
                                
                                <div class="security-notice">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Paiement 100% sécurisé - Vos données sont protégées</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>

<style>
.checkout-section {
    padding: 2rem 0;
}

.checkout-form {
    margin-bottom: 3rem;
}

.checkout-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
    align-items: start;
}

.checkout-main {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.checkout-section-card {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    padding: 2rem;
}

.checkout-section-card h2 {
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
    color: var(--dark-color);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--dark-color);
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--gray-light);
    border-radius: var(--border-radius);
    font-size: 1rem;
    transition: var(--transition);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(230, 126, 34, 0.1);
}

/* Méthodes de paiement */
.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.payment-method {
    position: relative;
}

.payment-method input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.payment-method label {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    border: 2px solid var(--gray-light);
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
}

.payment-method input[type="radio"]:checked + label {
    border-color: var(--primary-color);
    background: rgba(230, 126, 34, 0.05);
}

.payment-method label i {
    font-size: 1.5rem;
    color: var(--primary-color);
    width: 30px;
}

.payment-method label span {
    font-weight: 600;
    color: var(--dark-color);
}

.payment-method label small {
    display: block;
    color: var(--gray-dark);
    font-size: 0.9rem;
    margin-top: 0.2rem;
}

.card-payment-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--gray-light);
    display: none;
}

#payment-card:checked ~ .card-payment-section {
    display: block;
}

/* Récapitulatif de commande */
.checkout-sidebar {
    position: sticky;
    top: 100px;
}

.order-summary {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.order-summary h3 {
    padding: 1.5rem;
    margin: 0;
    background: var(--light-color);
    font-size: 1.3rem;
}

.order-items {
    max-height: 300px;
    overflow-y: auto;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--gray-light);
}

.order-item:last-child {
    border-bottom: none;
}

.order-item .item-image {
    position: relative;
    width: 60px;
    height: 60px;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.order-item .item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.order-item .item-quantity {
    position: absolute;
    top: -5px;
    right: -5px;
    background: var(--primary-color);
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 600;
}

.order-item .item-details {
    flex: 1;
    min-width: 0;
}

.order-item .item-details h4 {
    margin: 0 0 0.3rem 0;
    font-size: 0.9rem;
    line-height: 1.3;
}

.order-item .item-artisan {
    margin: 0;
    font-size: 0.8rem;
    color: var(--gray-dark);
}

.order-item .item-price {
    font-weight: 600;
    color: var(--primary-color);
    font-size: 0.9rem;
}

.order-totals {
    padding: 1.5rem;
    border-top: 1px solid var(--gray-light);
}

.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
}

.total-row.grand-total {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary-color);
    border-top: 1px solid var(--gray-light);
    margin-top: 0.5rem;
    padding-top: 1rem;
}

.free-shipping {
    color: var(--success-color);
    font-weight: 600;
}

.order-actions {
    padding: 1.5rem;
    border-top: 1px solid var(--gray-light);
}

.btn-block {
    width: 100%;
    justify-content: center;
}

.security-notice {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    margin-top: 1rem;
    padding: 1rem;
    background: var(--light-color);
    border-radius: var(--border-radius);
    font-size: 0.9rem;
    color: var(--gray-dark);
}

.security-notice i {
    color: var(--success-color);
}

/* Responsive */
@media (max-width: 968px) {
    .checkout-layout {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .checkout-sidebar {
        position: static;
        order: -1;
    }
}

@media (max-width: 768px) {
    .checkout-section-card {
        padding: 1.5rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .payment-method label {
        padding: 1rem;
    }
    
    .order-item {
        padding: 1rem;
    }
}

@media (max-width: 480px) {
    .checkout-section-card {
        padding: 1rem;
    }
    
    .order-summary h3 {
        padding: 1rem;
    }
    
    .order-item {
        gap: 0.8rem;
    }
    
    .order-item .item-image {
        width: 50px;
        height: 50px;
    }
}
</style>

<script>
// Gestion de l'affichage des méthodes de paiement
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            // Masquer toutes les sections de paiement spécifiques
            document.querySelectorAll('.payment-details').forEach(section => {
                section.style.display = 'none';
            });
            
            // Afficher la section correspondante
            const detailsSection = document.getElementById(this.value + '-details');
            if (detailsSection) {
                detailsSection.style.display = 'block';
            }
        });
    });
    
    // Validation du formulaire
    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        const requiredFields = this.querySelectorAll('[required]');
        let valid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                valid = false;
                field.classList.add('error');
            } else {
                field.classList.remove('error');
            }
        });
        
        if (!valid) {
            e.preventDefault();
            showAlert('Veuillez remplir tous les champs obligatoires', 'error');
        }
    });
    
    updateCartCount();
});
</script>