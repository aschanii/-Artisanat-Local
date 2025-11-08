<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!is_logged_in()) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Gestion des Commandes - Artisanat Local";

// Récupérer les commandes en fonction du rôle
$user_id = $_SESSION['user_id'];
$is_admin = is_admin();
$is_seller = is_seller();

if ($is_admin) {
    $sql = "SELECT o.*, u.name as customer_name, u.email as customer_email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC";
    $orders = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} elseif ($is_seller) {
    $sql = "SELECT DISTINCT o.*, u.name as customer_name, u.email as customer_email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            WHERE oi.artisan_id = ? 
            ORDER BY o.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header('Location: ../index.php');
    exit();
}

// Gestion des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $order_id = $_POST['order_id'] ?? '';
    
    if ($action === 'update_status' && $is_admin) {
        $new_status = $_POST['status'] ?? '';
        $tracking_number = $_POST['tracking_number'] ?? '';
        
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, tracking_number = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$new_status, $tracking_number, $order_id]);
        
        $_SESSION['flash_message'] = 'Statut de commande mis à jour';
        header('Location: orders.php');
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
                <h1>Gestion des Commandes</h1>
                <div class="header-actions">
                    <div class="filters">
                        <select id="status-filter" class="form-control">
                            <option value="">Tous les statuts</option>
                            <option value="pending">En attente</option>
                            <option value="confirmed">Confirmée</option>
                            <option value="processing">En traitement</option>
                            <option value="shipped">Expédiée</option>
                            <option value="delivered">Livrée</option>
                            <option value="cancelled">Annulée</option>
                        </select>
                    </div>
                </div>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-card">
                <div class="card-content">
                    <div class="table-responsive">
                        <table class="table" id="orders-table">
                            <thead>
                                <tr>
                                    <th>N° Commande</th>
                                    <th>Client</th>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Suivi</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $order['order_number']; ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                                <br>
                                                <small><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                            </div>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td><?php echo number_format($order['total'], 2, ',', ' '); ?> €</td>
                                        <td>
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
                                        </td>
                                        <td>
                                            <?php if ($order['tracking_number']): ?>
                                                <code><?php echo $order['tracking_number']; ?></code>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                                                   class="btn btn-outline btn-small">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($is_admin): ?>
                                                    <button class="btn btn-outline btn-small" 
                                                            onclick="openStatusModal(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>', '<?php echo $order['tracking_number']; ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (empty($orders)): ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <h3>Aucune commande trouvée</h3>
                            <p>Les commandes de vos clients apparaîtront ici.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal de modification de statut -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Modifier le statut de la commande</h3>
            <button class="close" onclick="closeStatusModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="statusForm" method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" id="modal_order_id">
                
                <div class="form-group">
                    <label for="status" class="form-label">Statut</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="pending">En attente</option>
                        <option value="confirmed">Confirmée</option>
                        <option value="processing">En traitement</option>
                        <option value="shipped">Expédiée</option>
                        <option value="delivered">Livrée</option>
                        <option value="cancelled">Annulée</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="tracking_number" class="form-label">Numéro de suivi</label>
                    <input type="text" id="tracking_number" name="tracking_number" class="form-control">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    <button type="button" class="btn btn-outline" onclick="closeStatusModal()">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
function openStatusModal(orderId, currentStatus, trackingNumber) {
    document.getElementById('modal_order_id').value = orderId;
    document.getElementById('status').value = currentStatus;
    document.getElementById('tracking_number').value = trackingNumber || '';
    document.getElementById('statusModal').style.display = 'block';
}

function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
}

// Filtrage des commandes
document.getElementById('status-filter').addEventListener('change', function() {
    const status = this.value;
    const rows = document.querySelectorAll('#orders-table tbody tr');
    
    rows.forEach(row => {
        if (!status || row.querySelector('.status-badge').classList.contains(`status-${status}`)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Recherche en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Rechercher une commande...';
    searchInput.className = 'form-control';
    searchInput.style.marginBottom = '1rem';
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#orders-table tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    document.querySelector('.header-actions').prepend(searchInput);
});
</script>

<style>
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
    padding: 0;
    border-radius: var(--border-radius);
    width: 90%;
    max-width: 500px;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-light);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
}

.modal-body {
    padding: 1.5rem;
}

.close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--gray-dark);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1.5rem;
}
</style>