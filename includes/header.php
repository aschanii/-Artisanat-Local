<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <meta name="description" content="Plateforme e-commerce dédiée aux artisans locaux. Découvrez des produits uniques et faits main.">
    
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Barre de promotion -->
    <div class="promo-bar">
        <div class="container">
            <span>🎁 Livraison gratuite à partir de 50€ d'achat</span>
        </div>
    </div>

    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
                        <i class="fas fa-hands"></i>
                        <span><?php echo SITE_NAME; ?></span>
                    </a>
                </div>

                <nav class="main-nav">
                    <ul class="nav-links">
                        <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Accueil</a></li>
                        <li><a href="products.php" class="<?php echo $current_page == 'products.php' ? 'active' : ''; ?>">Boutique</a></li>
                        <li><a href="artisans.php" class="<?php echo $current_page == 'artisans.php' ? 'active' : ''; ?>">Artisans</a></li>
                        <li><a href="categories.php" class="<?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">Catégories</a></li>
                    </ul>
                </nav>

                <div class="header-actions">
                    <div class="search-box">
                        <form action="products.php" method="GET" class="search-form">
                            <input type="text" name="search" placeholder="Rechercher un produit..." class="search-input">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>

                    <div class="action-buttons">
                        <?php if (is_logged_in()): ?>
                            <div class="user-menu">
                                <button class="user-btn">
                                    <i class="fas fa-user"></i>
                                    <span><?php echo $_SESSION['user_name']; ?></span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <div class="user-dropdown">
                                    <a href="profile.php"><i class="fas fa-user-edit"></i> Mon profil</a>
                                    <a href="dashboard/"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
                                    <a href="wishlist.php"><i class="fas fa-heart"></i> Ma liste d'envies</a>
                                    <a href="orders.php"><i class="fas fa-shopping-bag"></i> Mes commandes</a>
                                    <div class="dropdown-divider"></div>
                                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="login.php" class="auth-btn">Connexion</a>
                            <a href="register.php" class="auth-btn register">Inscription</a>
                        <?php endif; ?>

                        <a href="cart.php" class="cart-btn">
                            <i class="fas fa-shopping-bag"></i>
                            <span class="cart-count" id="cart-count">0</span>
                        </a>
                    </div>
                </div>

                <button class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Navigation mobile -->
    <div class="mobile-nav">
        <div class="mobile-nav-content">
            <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Accueil
            </a>
            <a href="products.php" class="<?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                <i class="fas fa-store"></i> Boutique
            </a>
            <a href="artisans.php" class="<?php echo $current_page == 'artisans.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Artisans
            </a>
            <a href="cart.php">
                <i class="fas fa-shopping-bag"></i> Panier
            </a>
            <?php if (is_logged_in()): ?>
                <a href="profile.php">
                    <i class="fas fa-user"></i> Profil
                </a>
            <?php else: ?>
                <a href="login.php">
                    <i class="fas fa-sign-in-alt"></i> Connexion
                </a>
            <?php endif; ?>
        </div>
    </div>
    