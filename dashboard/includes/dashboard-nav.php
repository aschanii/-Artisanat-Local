<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="dashboard-nav">
    <div class="container">
        <ul class="dashboard-nav-links">
            <li>
                <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    Tableau de bord
                </a>
            </li>
            <?php if (is_admin()): ?>
            <li>
                <a href="users.php" class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    Utilisateurs
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="products.php" class="<?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i>
                    Produits
                </a>
            </li>
            <li>
                <a href="orders.php" class="<?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-bag"></i>
                    Commandes
                </a>
            </li>
            <?php if (is_admin()): ?>
            <li>
                <a href="categories.php" class="<?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tags"></i>
                    Catégories
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="settings.php" class="<?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    Paramètres
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
.dashboard-nav {
    background: var(--white);
    box-shadow: var(--shadow);
    border-bottom: 1px solid var(--gray-light);
}

.dashboard-nav-links {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    overflow-x: auto;
}

.dashboard-nav-links li {
    margin: 0;
}

.dashboard-nav-links a {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    text-decoration: none;
    color: var(--gray-dark);
    border-bottom: 3px solid transparent;
    transition: var(--transition);
    white-space: nowrap;
}

.dashboard-nav-links a:hover,
.dashboard-nav-links a.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
    background: rgba(230, 126, 34, 0.05);
}

.dashboard-nav-links a i {
    width: 16px;
    text-align: center;
}

@media (max-width: 768px) {
    .dashboard-nav-links {
        gap: 0;
    }
    
    .dashboard-nav-links a {
        padding: 0.8rem 1rem;
        font-size: 0.9rem;
    }
}
</style>