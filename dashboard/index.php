<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!is_logged_in()) {
    header('Location: ../login.php');
    exit();
}

if (!is_admin()) {
    header('Location: ../index.php');
    exit();
}

// Récupérer les statistiques
$users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$sellers_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn();
$products_count = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$orders_count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$revenue = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status = 'delivered'")->fetchColumn();

// Récupérer les commandes récentes
$recent_orders = $pdo->query("SELECT o.*, u.name as customer_name 
                             FROM orders o 
                             LEFT JOIN users u ON o.user_id = u.id 
                             ORDER BY o.created_at DESC 
                             LIMIT 5")->fetchAll();

// Récupérer les produits populaires
$popular_products = $pdo->query("SELECT p.*, u.name as artisan_name, COUNT(oi.id) as sales_count
                                FROM products p 
                                LEFT JOIN users u ON p.artisan_id = u.id 
                                LEFT JOIN order_items oi ON p.id = oi.product_id 
                                WHERE p.is_active = 1 
                                GROUP BY p.id 
                                ORDER BY sales_count DESC, p.views DESC 
                                LIMIT 5")->fetchAll();

// Récupérer les statistiques mensuelles
$monthly_stats = $pdo->query("SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as order_count,
    SUM(total) as revenue
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 6")->fetchAll();

$page_title = "Tableau de Bord - Artisanat Local";
?>
<?php include '../includes/header.php'; ?>

<main class="dashboard">
    <?php include 'includes/dashboard-nav.php'; ?>
    
    <div class="dashboard-content">
        <div class="container">
            <div class="dashboard-header">
                <h1>Tableau de Bord Administrateur</h1>
                <p>Bienvenue, <?php echo $_SESSION['user_name']; ?> ! Voici un aperçu de votre activité.</p>
            </div>

            <!-- Cartes de statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $users_count; ?></h3>
                        <p>Utilisateurs total</p>
                    </div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i>
                        12%
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $sellers_count; ?></h3>
                        <p>Artisans</p>
                    </div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i>
                        8%
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $products_count; ?></h3>
                        <p>Produits actifs</p>
                    </div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i>
                        15%
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $orders_count; ?></h3>
                        <p>Commandes totales</p>
                    </div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i>
                        23%
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($revenue, 2, ',', ' '); ?> €</h3>
                        <p>Chiffre d'affaires</p>
                    </div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i>
                        18%
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3>4.8/5</h3>
                        <p>Note moyenne</p>
                    </div>
                    <div class="stat-trend neutral">
                        <i class="fas fa-minus"></i>
                        0%
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Graphique des revenus -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Revenus mensuels</h3>
                        <select id="chart-period" class="form-select">
                            <option value="6">6 derniers mois</option>
                            <option value="12">12 derniers mois</option>
                        </select>
                    </div>
                    <div class="card-content">
                        <canvas id="revenueChart" height="250"></canvas>
                    </div>
                </div>

                <!-- Commandes récentes -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Commandes récentes</h3>
                        <a href="orders.php" class="btn btn-outline btn-small">Voir tout</a>
                    </div>
                    <div class="card-content">
                        <div class="orders-list">
                            <?php foreach ($recent_orders as $order): ?>
                                <div class="order-item">
                                    <div class="order-info">
                                        <h4>#<?php echo $order['order_number']; ?></h4>
                                        <p><?php echo $order['customer_name'] ?: 'Client non connecté'; ?></p>
                                        <span class="order-date"><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></span>
                                    </div>
                                    <div class="order-meta">
                                        <span class="order-amount"><?php echo number_format($order['total'], 2, ',', ' '); ?> €</span>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php 
                                            $status_labels = [
                                                'pending' => 'En attente',
                                                'confirmed' => 'Confirmée',
                                                'processing' => 'En traitement',
                                                'shipped' => 'Expédiée',
                                                'delivered' => 'Livrée',
                                                'cancelled' => 'Annulée'
                                            ];
                                            echo $status_labels[$order['status']];
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Produits populaires -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Produits populaires</h3>
                        <a href="products.php" class="btn btn-outline btn-small">Voir tout</a>
                    </div>
                    <div class="card-content">
                        <div class="products-list">
                            <?php foreach ($popular_products as $product): ?>
                                <div class="product-item">
                                    <img src="<?php echo $product['image'] ?: '../images/product-placeholder.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="product-image">
                                    <div class="product-info">
                                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                        <p><?php echo htmlspecialchars($product['artisan_name']); ?></p>
                                        <div class="product-stats">
                                            <span class="sales"><?php echo $product['sales_count']; ?> ventes</span>
                                            <span class="views"><?php echo $product['views']; ?> vues</span>
                                        </div>
                                    </div>
                                    <div class="product-price">
                                        <?php echo number_format($product['price'], 2, ',', ' '); ?> €
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Statistiques rapides -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Statistiques rapides</h3>
                    </div>
                    <div class="card-content">
                        <div class="quick-stats">
                            <div class="quick-stat">
                                <div class="stat-value"><?php 
                                    $pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
                                    echo $pending_orders;
                                ?></div>
                                <div class="stat-label">Commandes en attente</div>
                            </div>
                            <div class="quick-stat">
                                <div class="stat-value"><?php 
                                    $low_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock < 10 AND stock > 0")->fetchColumn();
                                    echo $low_stock;
                                ?></div>
                                <div class="stat-label">Produits en rupture</div>
                            </div>
                            <div class="quick-stat">
                                <div class="stat-value"><?php 
                                    $new_users = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
                                    echo $new_users;
                                ?></div>
                                <div class="stat-label">Nouveaux utilisateurs (7j)</div>
                            </div>
                            <div class="quick-stat">
                                <div class="stat-value"><?php 
                                    $avg_order = $pdo->query("SELECT COALESCE(AVG(total), 0) FROM orders WHERE status = 'delivered'")->fetchColumn();
                                    echo number_format($avg_order, 2, ',', ' ');
                                ?> €</div>
                                <div class="stat-label">Panier moyen</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/dashboard.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Données pour le graphique
    const monthlyData = <?php echo json_encode($monthly_stats); ?>;
    
    // Préparer les données pour Chart.js
    const months = monthlyData.map(stat => {
        const date = new Date(stat.month + '-01');
        return date.toLocaleDateString('fr-FR', { month: 'short', year: 'numeric' });
    }).reverse();
    
    const revenues = monthlyData.map(stat => parseFloat(stat.revenue)).reverse();
    const orders = monthlyData.map(stat => parseInt(stat.order_count)).reverse();
    
    // Créer le graphique
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Revenus (€)',
                    data: revenues,
                    borderColor: '#e67e22',
                    backgroundColor: 'rgba(230, 126, 34, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                },
                {
                    label: 'Commandes',
                    data: orders,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Revenus (€)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Commandes'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
});
</script>

<style>
.dashboard {
    margin-top: 80px;
    min-height: calc(100vh - 80px);
    background: #f8f9fa;
}

.dashboard-content {
    padding: 2rem 0;
}

.dashboard-header {
    margin-bottom: 2rem;
}

.dashboard-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
}

.dashboard-header p {
    color: var(--gray-dark);
    font-size: 1.1rem;
}

/* Cartes de statistiques */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-info h3 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 0.2rem;
    line-height: 1;
}

.stat-info p {
    color: var(--gray-dark);
    margin: 0;
}

.stat-trend {
    margin-left: auto;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.stat-trend.up {
    background: #d4edda;
    color: #155724;
}

.stat-trend.down {
    background: #f8d7da;
    color: #721c24;
}

.stat-trend.neutral {
    background: #fff3cd;
    color: #856404;
}

/* Grille du dashboard */
.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.dashboard-card {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    margin-bottom: 2rem;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-light);
}

.card-header h3 {
    margin: 0;
    font-size: 1.3rem;
    color: var(--dark-color);
}

.card-content {
    padding: 1.5rem;
}

/* Liste des commandes */
.orders-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-radius: var(--border-radius);
    background: var(--light-color);
    transition: var(--transition);
}

.order-item:hover {
    background: #e9ecef;
}

.order-info h4 {
    margin: 0 0 0.3rem 0;
    font-size: 0.9rem;
    color: var(--dark-color);
}

.order-info p {
    margin: 0 0 0.3rem 0;
    font-size: 0.8rem;
    color: var(--gray-dark);
}

.order-date {
    font-size: 0.7rem;
    color: var(--gray-dark);
}

.order-meta {
    text-align: right;
}

.order-amount {
    display: block;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 0.3rem;
}

.status-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
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

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

/* Liste des produits */
.products-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.product-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-radius: var(--border-radius);
    background: var(--light-color);
    transition: var(--transition);
}

.product-item:hover {
    background: #e9ecef;
}

.product-item .product-image {
    width: 50px;
    height: 50px;
    border-radius: var(--border-radius);
    object-fit: cover;
}

.product-info {
    flex: 1;
}

.product-info h4 {
    margin: 0 0 0.3rem 0;
    font-size: 0.9rem;
    color: var(--dark-color);
}

.product-info p {
    margin: 0 0 0.5rem 0;
    font-size: 0.8rem;
    color: var(--gray-dark);
}

.product-stats {
    display: flex;
    gap: 1rem;
    font-size: 0.7rem;
}

.product-stats .sales {
    color: var(--success-color);
}

.product-stats .views {
    color: var(--gray-dark);
}

.product-price {
    font-weight: 600;
    color: var(--primary-color);
}

/* Statistiques rapides */
.quick-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.quick-stat {
    text-align: center;
    padding: 1.5rem;
    background: var(--light-color);
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.quick-stat:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.quick-stat .stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.quick-stat .stat-label {
    font-size: 0.9rem;
    color: var(--gray-dark);
}

/* Responsive */
@media (max-width: 1200px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .dashboard-header h1 {
        font-size: 2rem;
    }
    
    .card-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .quick-stats {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .dashboard-content {
        padding: 1rem 0;
    }
    
    .stat-card {
        flex-direction: column;
        text-align: center;
    }
    
    .stat-trend {
        margin-left: 0;
        margin-top: 0.5rem;
    }
}
</style>