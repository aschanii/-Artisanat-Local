<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$page_title = "Mon Profil - Artisanat Local";

// Récupérer les informations de l'utilisateur
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les commandes de l'utilisateur
$orders_stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$orders_stmt->execute([$user_id]);
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion de la mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $social_facebook = $_POST['social_facebook'] ?? '';
    $social_instagram = $_POST['social_instagram'] ?? '';
    
    $errors = [];
    
    if (empty($name) || empty($email)) {
        $errors[] = "Le nom et l'email sont obligatoires";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse email invalide";
    }
    
    // Vérifier si l'email est déjà utilisé par un autre utilisateur
    $email_check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $email_check->execute([$email, $user_id]);
    if ($email_check->fetch()) {
        $errors[] = "Cet email est déjà utilisé par un autre compte";
    }
    
    if (empty($errors)) {
        try {
            $update_stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, bio = ?, social_facebook = ?, social_instagram = ?, updated_at = NOW() WHERE id = ?");
            $update_stmt->execute([$name, $email, $phone, $address, $bio, $social_facebook, $social_instagram, $user_id]);
            
            // Mettre à jour la session
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            $success_message = "Profil mis à jour avec succès";
            
            // Recharger les données utilisateur
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $errors[] = "Erreur lors de la mise à jour: " . $e->getMessage();
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<main>
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1>Mon Profil</h1>
                <div class="breadcrumb">
                    <a href="index.php">Accueil</a>
                    <span>/</span>
                    <span>Mon Profil</span>
                </div>
            </div>
        </div>
    </section>

    <section class="section profile-section">
        <div class="container">
            <div class="profile-layout">
                <!-- Menu de navigation du profil -->
                <div class="profile-sidebar">
                    <div class="user-card">
                        <div class="user-avatar">
                            <img src="<?php echo $user['avatar'] ?: 'images/avatar-placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($user['name']); ?>">
                        </div>
                        <div class="user-info">
                            <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                            <p class="user-role"><?php echo $user['role'] === 'admin' ? 'Administrateur' : 'Artisan'; ?></p>
                            <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                    
                    <nav class="profile-nav">
                        <a href="#profile" class="nav-item active" data-tab="profile">
                            <i class="fas fa-user"></i>
                            Informations personnelles
                        </a>
                        <a href="#orders" class="nav-item" data-tab="orders">
                            <i class="fas fa-shopping-bag"></i>
                            Mes commandes
                        </a>
                        <?php if (is_seller()): ?>
                        <a href="#products" class="nav-item" data-tab="products">
                            <i class="fas fa-box"></i>
                            Mes produits
                        </a>
                        <?php endif; ?>
                        <a href="#security" class="nav-item" data-tab="security">
                            <i class="fas fa-shield-alt"></i>
                            Sécurité
                        </a>
                    </nav>
                </div>

                <!-- Contenu du profil -->
                <div class="profile-content">
                    <!-- Onglet Informations personnelles -->
                    <div id="profile" class="tab-content active">
                        <div class="tab-header">
                            <h2>Informations personnelles</h2>
                            <p>Gérez vos informations de profil</p>
                        </div>

                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success_message); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-error">
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="profile-form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="name" class="form-label">Nom complet *</label>
                                    <input type="text" id="name" name="name" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">Adresse email *</label>
                                    <input type="email" id="email" name="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="address" class="form-label">Adresse</label>
                                <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea id="bio" name="bio" class="form-control" rows="4" placeholder="Parlez-nous de vous..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="social_facebook" class="form-label">Facebook</label>
                                    <input type="url" id="social_facebook" name="social_facebook" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['social_facebook'] ?? ''); ?>" placeholder="https://facebook.com/votre-profil">
                                </div>
                                
                                <div class="form-group">
                                    <label for="social_instagram" class="form-label">Instagram</label>
                                    <input type="url" id="social_instagram" name="social_instagram" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['social_instagram'] ?? ''); ?>" placeholder="https://instagram.com/votre-profil">
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Onglet Commandes -->
                    <div id="orders" class="tab-content">
                        <div class="tab-header">
                            <h2>Mes commandes</h2>
                            <p>Historique de vos commandes récentes</p>
                        </div>

                        <?php if (empty($orders)): ?>
                            <div class="empty-state">
                                <i class="fas fa-shopping-bag"></i>
                                <h3>Aucune commande</h3>
                                <p>Vous n'avez pas encore passé de commande.</p>
                                <a href="products.php" class="btn btn-primary">Découvrir la boutique</a>
                            </div>
                        <?php else: ?>
                            <div class="orders-list">
                                <?php foreach ($orders as $order): ?>
                                    <div class="order-card">
                                        <div class="order-header">
                                            <div class="order-info">
                                                <h4>Commande #<?php echo $order['order_number']; ?></h4>
                                                <p class="order-date"><?php echo date('d/m/Y à H:i', strtotime($order['created_at'])); ?></p>
                                            </div>
                                            <div class="order-status">
                                                <span class="status-badge status-<?php echo $order['status']; ?>">
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
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="order-details">
                                            <div class="order-amount">
                                                <strong><?php echo number_format($order['total'], 2, ',', ' '); ?> €</strong>
                                            </div>
                                            <div class="order-actions">
                                                <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-outline btn-small">
                                                    <i class="fas fa-eye"></i>
                                                    Voir les détails
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="view-all-orders">
                                <a href="orders.php" class="btn btn-outline">
                                    Voir toutes mes commandes
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Onglet Produits (pour les artisans) -->
                    <?php if (is_seller()): ?>
                    <div id="products" class="tab-content">
                        <div class="tab-header">
                            <h2>Mes produits</h2>
                            <p>Gérez vos créations</p>
                        </div>
                        
                        <div class="products-actions">
                            <a href="dashboard/products.php" class="btn btn-primary">
                                <i class="fas fa-box"></i>
                                Gérer mes produits
                            </a>
                            <a href="dashboard/product-edit.php" class="btn btn-outline">
                                <i class="fas fa-plus"></i>
                                Ajouter un produit
                            </a>
                        </div>
                        
                        <div class="stats-cards">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="stat-info">
                                    <?php
                                    $product_count = $pdo->prepare("SELECT COUNT(*) FROM products WHERE artisan_id = ?")->execute([$user_id]);
                                    $product_count = $stmt->fetchColumn();
                                    ?>
                                    <h3><?php echo $product_count; ?></h3>
                                    <p>Produits actifs</p>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="stat-info">
                                    <?php
                                    $sales_count = $pdo->prepare("SELECT COUNT(*) FROM order_items oi 
                                                                JOIN orders o ON oi.order_id = o.id 
                                                                WHERE oi.artisan_id = ? AND o.status != 'cancelled'")->execute([$user_id]);
                                    $sales_count = $stmt->fetchColumn();
                                    ?>
                                    <h3><?php echo $sales_count; ?></h3>
                                    <p>Ventes totales</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Onglet Sécurité -->
                    <div id="security" class="tab-content">
                        <div class="tab-header">
                            <h2>Sécurité du compte</h2>
                            <p>Gérez la sécurité de votre compte</p>
                        </div>
                        
                        <div class="security-sections">
                            <div class="security-section">
                                <h4>Changer le mot de passe</h4>
                                <form class="password-form">
                                    <div class="form-group">
                                        <label for="current_password" class="form-label">Mot de passe actuel</label>
                                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-key"></i>
                                        Changer le mot de passe
                                    </button>
                                </form>
                            </div>
                            
                            <div class="security-section">
                                <h4>Sessions actives</h4>
                                <p>Vous êtes actuellement connecté sur cet appareil.</p>
                                <button class="btn btn-outline">
                                    <i class="fas fa-sign-out-alt"></i>
                                    Se déconnecter de tous les appareils
                                </button>
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
.profile-section {
    padding: 2rem 0;
}

.profile-layout {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 3rem;
}

.profile-sidebar {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.user-card {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    padding: 2rem;
    text-align: center;
    margin-bottom: 2rem;
}

.user-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    margin: 0 auto 1.5rem;
    border: 3px solid var(--primary-color);
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-info h3 {
    margin-bottom: 0.5rem;
    color: var(--dark-color);
}

.user-role {
    background: var(--primary-color);
    color: white;
    padding: 0.3rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    display: inline-block;
    margin-bottom: 0.5rem;
}

.user-email {
    color: var(--gray-dark);
    margin: 0;
}

.profile-nav {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    text-decoration: none;
    color: var(--dark-color);
    border-left: 3px solid transparent;
    transition: var(--transition);
}

.nav-item:hover,
.nav-item.active {
    background: var(--light-color);
    border-left-color: var(--primary-color);
    color: var(--primary-color);
}

.nav-item i {
    width: 20px;
    text-align: center;
}

.profile-content {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.tab-content {
    display: none;
    padding: 2rem;
}

.tab-content.active {
    display: block;
}

.tab-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray-light);
}

.tab-header h2 {
    margin-bottom: 0.5rem;
    color: var(--dark-color);
}

.tab-header p {
    color: var(--gray-dark);
    margin: 0;
}

.profile-form {
    max-width: 600px;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.order-card {
    background: var(--light-color);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    border: 1px solid var(--gray-light);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
}

.order-info h4 {
    margin-bottom: 0.5rem;
    color: var(--dark-color);
}

.order-date {
    color: var(--gray-dark);
    margin: 0;
    font-size: 0.9rem;
}

.order-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-amount {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary-color);
}

.view-all-orders {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--gray-light);
}

.products-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.stat-card {
    background: var(--light-color);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.stat-info h3 {
    font-size: 1.8rem;
    margin-bottom: 0.2rem;
    color: var(--dark-color);
}

.stat-info p {
    margin: 0;
    color: var(--gray-dark);
    font-size: 0.9rem;
}

.security-sections {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.security-section {
    padding: 1.5rem;
    background: var(--light-color);
    border-radius: var(--border-radius);
}

.security-section h4 {
    margin-bottom: 1rem;
    color: var(--dark-color);
}

.password-form {
    max-width: 400px;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--gray-dark);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h3 {
    margin-bottom: 0.5rem;
    color: var(--dark-color);
}

@media (max-width: 968px) {
    .profile-layout {
        grid-template-columns: 1fr;
    }
    
    .profile-sidebar {
        position: static;
    }
}

@media (max-width: 768px) {
    .tab-content {
        padding: 1.5rem;
    }
    
    .order-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .order-details {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .products-actions {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Navigation par onglets
    const navItems = document.querySelectorAll('.nav-item');
    const tabContents = document.querySelectorAll('.tab-content');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Retirer la classe active de tous les éléments
            navItems.forEach(nav => nav.classList.remove('active'));
            tabContents.forEach(tab => tab.classList.remove('active'));
            
            // Activer l'élément cliqué
            this.classList.add('active');
            
            // Afficher le contenu correspondant
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Validation du formulaire de mot de passe
    const passwordForm = document.querySelector('.password-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                alert('Les mots de passe ne correspondent pas');
                return;
            }
            
            if (newPassword.length < 6) {
                alert('Le mot de passe doit contenir au moins 6 caractères');
                return;
            }
            
            // Simuler le changement de mot de passe
            alert('Mot de passe changé avec succès!');
            this.reset();
        });
    }
});
</script>