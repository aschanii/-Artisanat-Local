<?php
// Démarrage de session sécurisé
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/functions.php';
$page_title = "À propos de Handmade Haven";
include 'includes/header.php';
?>

<main class="container">
    <div class="page-header">
        <h1>À propos de Handmade Haven</h1>
        <p>Célébrer l'artisanat et soutenir les artisans indépendants</p>
    </div>
    
    <section class="about-hero">
        <div class="hero-content">
            <h2>Notre Histoire</h2>
            <p>Fondée en 2024, Handmade Haven est née d'une passion pour l'artisanat authentique et du désir de créer une plateforme où les artisans peuvent partager leurs créations uniques avec le monde. Nous croyons que chaque objet fait main raconte une histoire et porte l'âme de son créateur.</p>
        </div>
        <div class="hero-image">
            <img src="assets/images/about-hero.jpg" alt="Notre Histoire" style="max-width: 100%; height: auto;">
        </div>
    </section>
    
    <section class="mission-vision">
        <div class="mission">
            <h3>Notre Mission</h3>
            <p>Mettre en relation des artisans talentueux avec des clients qui apprécient leur travail, en favorisant une communauté qui valorise la qualité, la créativité et la touche humaine dans chaque produit.</p>
        </div>
        <div class="vision">
            <h3>Notre Vision</h3>
            <p>Devenir la plateforme la plus fiable au monde pour les produits faits main, où l'artisanat est célébré et où les artisans peuvent développer des activités durables.</p>
        </div>
    </section>
    
    <section class="values">
        <h2>Nos Valeurs</h2>
        <div class="values-grid">
            <div class="value-item">
                <i class="fas fa-handshake"></i>
                <h4>Authenticité</h4>
                <p>Chaque produit sur notre plateforme est véritablement fait main par des artisans qualifiés.</p>
            </div>
            <div class="value-item">
                <i class="fas fa-heart"></i>
                <h4>Qualité</h4>
                <p>Nous maintenons des standards élevés pour l'artisanat et les matériaux.</p>
            </div>
            <div class="value-item">
                <i class="fas fa-users"></i>
                <h4>Communauté</h4>
                <p>Nous créons des liens entre les créateurs et les acheteurs à travers le monde.</p>
            </div>
            <div class="value-item">
                <i class="fas fa-leaf"></i>
                <h4>Durabilité</h4>
                <p>Nous promouvons des pratiques écologiques et l'utilisation de matériaux durables.</p>
            </div>
        </div>
    </section>
    
    <section class="team">
        <h2>Rencontrez Notre Équipe</h2>
        <div class="team-grid">
            <div class="team-member">
                <img src="assets/images/team1.jpg" alt="Membre de l'équipe" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                <h4>Sarah Johnson</h4>
                <p>Fondatrice & PDG</p>
            </div>
            <div class="team-member">
                <img src="assets/images/team2.jpg" alt="Membre de l'équipe" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                <h4>Mike Chen</h4>
                <p>Responsable des opérations</p>
            </div>
            <div class="team-member">
                <img src="assets/images/team3.jpg" alt="Membre de l'équipe" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                <h4>Emily Davis</h4>
                <p>Responsable de la communauté</p>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>