<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!is_logged_in()) {
    header('Location: ../login.php');
    exit();
}

if (!is_admin() && !is_seller()) {
    header('Location: ../index.php');
    exit();
}

$page_title = "Gestion des Produits - Artisanat Local";

// Déterminer si l'utilisateur est un artisan (peut seulement voir ses produits)
$user_id = $_SESSION['user_id'];
$is_seller = is_seller();

// Récupérer les produits
$where_clause = $is_seller ? "WHERE p.artisan_id = ?" : "";
$params = $is_seller ? [$user_id] : [];

$sql = "SELECT p.*, u.name as artisan_name, c.name as category_name 
        FROM products p 
        LEFT JOIN users u ON p.artisan_id = u.id 
        LEFT JOIN categories c ON p.category_id = c.id 
        $where_clause 
        ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = $_POST['product_id'] ?? '';
    
    if ($action === 'delete') {
        // Vérifier que l'utilisateur a le droit de supprimer ce produit
        if ($is_seller) {
            $check_stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? AND artisan_id = ?");
            $check_stmt->execute([$product_id, $user_id]);
            if (!$check_stmt->fetch()) {
                die("Accès non autorisé");
            }
        }
        
        $delete_stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $delete_stmt->execute([$product_id]);
        
        header('Location: products.php?success=Produit supprimé avec succès');
        exit();
    } elseif ($action === 'toggle_status') {
        $new_status = $_POST['status'] === 'active' ? 1 : 0;
        
        if ($is_seller) {
            $check_stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? AND artisan_id = ?");
            $check_stmt->execute([$product_id, $user_id]);
            if (!$check_stmt->fetch()) {
                die("Accès non autorisé");
            }
        }
        
        $update_stmt = $pdo->prepare("UPDATE products SET is_active = ? WHERE id = ?");
        $update_stmt->execute([$new_status, $product_id]);
        
        header('Location: products.php?success=Statut du produit mis à jour');
        exit();
    }
}

$success_message = $_GET['success'] ?? '';
?>
<?php include '../includes/header.php'; ?>

<main class="dashboard">
    <?php include 'includes/dashboard-nav.php'; ?>
    
    <div class="dashboard-content">
        <div class="container">
            <div class="dashboard-header">
                <div class="header-content">
                    <h1>Gestion des Produits</h1>
                    <p>Gérez vos produits et leurs disponibilités</p>
                </div>
                <div class="header-actions">
                    <a href="product-edit.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Ajouter un produit
                    </a>
                </div>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Liste des produits (<?php echo count($products); ?>)</h3>
                    <div class="filters">
                        <input type="text" id="search-products" placeholder="Rechercher un produit..." class="form-control">
                    </div>
                </div>
                <div class="card-content">
                    <div class="table-responsive">
                        <table class="table" id="products-table">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Catégorie</th>
                                    <?php if (!is_seller()): ?>
                                    <th>Artisan</th>
                                    <?php endif; ?>
                                    <th>Prix</th>
                                    <th>Stock</th>
                                    <th>Statut</th>
                                    <th>Vues</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="product-cell">
                                                <img src="<?php echo $product['image'] ?: '../images/product-placeholder.jpg'; ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                     class="product-thumb">
                                                <div class="product-info">
                                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                                    <p class="product-desc"><?php echo htmlspecialchars(substr($product['description'], 0, 50)); ?>...</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <?php if (!is_seller()): ?>
                                        <td><?php echo htmlspecialchars($product['artisan_name']); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo number_format($product['price'], 2, ',', ' '); ?> €</td>
                                        <td>
                                            <span class="stock-badge <?php echo $product['stock'] > 10 ? 'in-stock' : ($product['stock'] > 0 ? 'low-stock' : 'out-of-stock'); ?>">
                                                <?php echo $product['stock']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" class="status-form">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="status" value="<?php echo $product['is_active'] ? 'inactive' : 'active'; ?>">
                                                <button type="submit" class="status-toggle <?php echo $product['is_active'] ? 'active' : 'inactive'; ?>">
                                                    <?php echo $product['is_active'] ? 'Actif' : 'Inactif'; ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td><?php echo $product['views']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($product['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="../product.php?id=<?php echo $product['id']; ?>" 
                                                   class="btn btn-outline btn-small" 
                                                   target="_blank"
                                                   title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="product-edit.php?id=<?php echo $product['id']; ?>" 
                                                   class="btn btn-outline btn-small"
                                                   title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" class="delete-form" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <button type="submit" class="btn btn-danger btn-small" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (empty($products)): ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <h3>Aucun produit trouvé</h3>
                            <p>Commencez par ajouter votre premier produit.</p>
                            <a href="product-edit.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Ajouter un produit
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
<script src="js/dashboard.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Recherche en temps réel
    const searchInput = document.getElementById('search-products');
    const table = document.getElementById('products-table');
    
    if (searchInput && table) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
});
</script>

<style>
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-content h1 {
    margin-bottom: 0.5rem;
}

.header-actions {
    display: flex;
    gap: 1rem;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    background: var(--white);
}

.table th,
.table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--gray-light);
}

.table th {
    background: var(--light-color);
    font-weight: 600;
    color: var(--dark-color);
}

.table tbody tr:hover {
    background: #f8f9fa;
}

.product-cell {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.product-thumb {
    width: 50px;
    height: 50px;
    border-radius: var(--border-radius);
    object-fit: cover;
}

.product-info strong {
    display: block;
    margin-bottom: 0.3rem;
}

.product-desc {
    margin: 0;
    font-size: 0.8rem;
    color: var(--gray-dark);
}

.stock-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.stock-badge.in-stock {
    background: #d4edda;
    color: #155724;
}

.stock-badge.low-stock {
    background: #fff3cd;
    color: #856404;
}

.stock-badge.out-of-stock {
    background: #f8d7da;
    color: #721c24;
}

.status-form {
    display: inline;
}

.status-toggle {
    padding: 0.3rem 0.8rem;
    border: none;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
}

.status-toggle.active {
    background: #d4edda;
    color: #155724;
}

.status-toggle.inactive {
    background: #f8d7da;
    color: #721c24;
}

.status-toggle:hover {
    opacity: 0.8;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.action-buttons form {
    display: inline;
}

.btn-small {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
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

.filters {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.filters .form-control {
    min-width: 250px;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .header-actions {
        justify-content: center;
    }
    
    .table {
        font-size: 0.9rem;
    }
    
    .table th,
    .table td {
        padding: 0.5rem;
    }
    
    .product-cell {
        flex-direction: column;
        align-items: start;
        gap: 0.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .filters .form-control {
        min-width: 200px;
    }
}
</style>