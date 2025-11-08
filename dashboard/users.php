<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: ../index.php');
    exit();
}

$page_title = "Gestion des Utilisateurs - Artisanat Local";

// Récupérer les utilisateurs
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

// Gestion des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    
    if ($action === 'delete') {
        // Empêcher la suppression de son propre compte
        if ($user_id == $_SESSION['user_id']) {
            header('Location: users.php?error=Impossible de supprimer votre propre compte');
            exit();
        }
        
        $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $deleteStmt->execute([$user_id]);
        
        header('Location: users.php?success=Utilisateur supprimé');
        exit();
    } elseif ($action === 'update_role') {
        $new_role = $_POST['role'] ?? '';
        
        $updateStmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $updateStmt->execute([$new_role, $user_id]);
        
        header('Location: users.php?success=Rôle mis à jour');
        exit();
    }
}

$success_message = $_GET['success'] ?? '';
$error_message = $_GET['error'] ?? '';
?>
<?php include '../includes/header.php'; ?>

<main class="dashboard">
    <?php include 'includes/dashboard-nav.php'; ?>
    
    <div class="dashboard-content">
        <div class="container">
            <div class="dashboard-header">
                <h1>Gestion des Utilisateurs</h1>
                <p>Gérez les comptes utilisateurs et leurs permissions</p>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Liste des utilisateurs (<?php echo count($users); ?>)</h3>
                </div>
                <div class="card-content">
                    <div class="table-responsive">
                        <table class="table" id="users-table">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Date d'inscription</th>
                                    <th>Dernière connexion</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="user-cell">
                                                <img src="<?php echo $user['avatar'] ?: '../images/avatar-placeholder.jpg'; ?>" 
                                                     alt="<?php echo htmlspecialchars($user['name']); ?>" 
                                                     class="user-avatar">
                                                <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <form method="POST" class="role-form">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="update_role">
                                                <select name="role" onchange="this.form.submit()" class="role-select">
                                                    <option value="seller" <?php echo $user['role'] === 'seller' ? 'selected' : ''; ?>>Artisan</option>
                                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <?php if ($user['last_login']): ?>
                                                <?php echo date('d/m/Y H:i', strtotime($user['last_login'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Jamais</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <form method="POST" class="delete-form" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <button type="submit" class="btn btn-danger btn-small">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

<style>
.user-cell {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.role-form {
    display: inline;
}

.role-select {
    padding: 0.3rem 0.8rem;
    border: 1px solid var(--gray-light);
    border-radius: var(--border-radius);
    background: white;
}
</style>