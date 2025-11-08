<?php
require_once 'includes/config.php';

$page_title = "Artisanat Local - Produits Uniques Faits Main";
?>
<?php include 'includes/header.php'; ?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Découvrez l'Artisanat Local</h1>
                <p>Des créations uniques et faites main par des artisans passionnés. Soutenez l'artisanat local et trouvez des pièces exceptionnelles.</p>
                <div class="hero-buttons">
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-store"></i>
                        Explorer la boutique
                    </a>
                    <a href="#artisans" class="btn btn-secondary">
                        <i class="fas fa-users"></i>
                        Découvrir les artisans
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Catégories Section -->
    <section class="section" id="categories">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Nos Catégories</h2>
                <p class="section-subtitle">Parcourez nos différentes catégories d'artisanat</p>
            </div>
            <div class="categories-grid" id="categories-grid">
                <!-- Les catégories seront chargées dynamiquement -->
            </div>
        </div>
    </section>

    <!-- Produits Populaires -->
    <section class="section bg-light">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Produits Populaires</h2>
                <p class="section-subtitle">Découvrez nos produits les plus appréciés</p>
            </div>
            <div class="products-grid" id="featured-products">
                <!-- Les produits populaires seront chargés dynamiquement -->
            </div>
            <div class="text-center" style="margin-top: 3rem;">
                <a href="products.php" class="btn btn-outline">
                    Voir tous les produits
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Nouveaux Produits -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Nouveautés</h2>
                <p class="section-subtitle">Les dernières créations de nos artisans</p>
            </div>
            <div class="products-grid" id="new-products">
                <!-- Les nouveaux produits seront chargés dynamiquement -->
            </div>
        </div>
    </section>

    <!-- Artisans Section -->
    <section class="section bg-light" id="artisans">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Nos Artisans</h2>
                <p class="section-subtitle">Rencontrez les talents derrière nos créations</p>
            </div>
            <div class="artisans-grid" id="artisans-grid">
                <!-- Les artisans seront chargés dynamiquement -->
            </div>
            <div class="text-center" style="margin-top: 3rem;">
                <a href="artisans.php" class="btn btn-outline">
                    Voir tous les artisans
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Paiement Sécurisé</h3>
                    <p>Transactions cryptées et sécurisées pour vos achats en toute confiance</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3>Livraison Rapide</h3>
                    <p>Expédition sous 48h et suivi de colis pour une livraison en temps voulu</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-undo-alt"></i>
                    </div>
                    <h3>Retour Facile</h3>
                    <p>14 jours pour changer d'avis et retourner votre commande</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Support 7j/7</h3>
                    <p>Notre équipe est là pour vous accompagner dans vos achats</p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
<script src="js/app.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    loadFeaturedProducts();
    loadNewProducts();
    loadArtisans();
    updateCartCount();
});
</script>