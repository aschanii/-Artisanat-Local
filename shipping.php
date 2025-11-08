<?php
$page_title = "Informations de Livraison";
require_once 'includes/config.php';
?>

<?php
session_start(); // obligatoire pour $_SESSION
require_once 'includes/functions.php';
include 'includes/header.php';
?>
<main class="container shipping-container">
    <div class="page-header">
        <h1>Informations de Livraison</h1>
        <p>Découvrez nos politiques de livraison et délais</p>
    </div>

    <div class="shipping-content">
        <section class="shipping-section">
            <h2>Options & Délais de Livraison</h2>
            <div class="shipping-options">
                <div class="shipping-option">
                    <h3>Livraison Standard</h3>
                    <p class="delivery-time">5-7 jours ouvrables</p>
                    <p class="price">4,99€</p>
                    <p>Gratuite à partir de 50€ d'achat</p>
                </div>
                <div class="shipping-option">
                    <h3>Livraison Express</h3>
                    <p class="delivery-time">2-3 jours ouvrables</p>
                    <p class="price">12,99€</p>
                    <p>Disponible pour la plupart des articles</p>
                </div>
                <div class="shipping-option">
                    <h3>Livraison en 24h</h3>
                    <p class="delivery-time">1 jour ouvrable</p>
                    <p class="price">24,99€</p>
                    <p>Commandez avant 14h</p>
                </div>
            </div>
        </section>

        <section class="shipping-section">
            <h2>Livraison Internationale</h2>
            <div class="international-shipping">
                <p>Nous livrons dans plus de 50 pays. Les délais et coûts varient selon la destination.</p>
                <div class="international-rates">
                    <div class="region">
                        <h4>Canada & Mexique</h4>
                        <p>7-14 jours ouvrables • 14,99€</p>
                    </div>
                    <div class="region">
                        <h4>Europe</h4>
                        <p>10-21 jours ouvrables • 19,99€</p>
                    </div>
                    <div class="region">
                        <h4>Asie & Australie</h4>
                        <p>14-28 jours ouvrables • 24,99€</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="shipping-section">
            <h2>Traitement des Articles Artisanaux</h2>
            <div class="processing-info">
                <p>Comme nos produits sont faits main, veuillez prévoir un délai supplémentaire :</p>
                <ul>
                    <li><strong>Articles prêts à expédier :</strong> expédiés sous 1-2 jours ouvrables</li>
                    <li><strong>Articles sur commande :</strong> 3-10 jours ouvrables de traitement avant expédition</li>
                    <li><strong>Articles personnalisés :</strong> délai défini par l’artisan (généralement 2-4 semaines)</li>
                </ul>
            </div>
        </section>

        <section class="shipping-section">
            <h2>Suivi de Votre Commande</h2>
            <p>Une fois votre commande expédiée, vous recevrez un numéro de suivi par email. Vous pouvez également suivre vos commandes depuis votre compte.</p>
        </section>

        <section class="shipping-section">
            <h2>Restrictions de Livraison</h2>
            <div class="restrictions">
                <p>Certains articles ont des restrictions de livraison :</p>
                <ul>
                    <li>Articles fragiles nécessitant un emballage spécial</li>
                    <li>Certaines substances chimiques ou matériaux avec limitations</li>
                    <li>Meubles volumineux pouvant engendrer des frais supplémentaires</li>
                </ul>
            </div>
        </section>

        <div class="shipping-contact">
            <p>Des questions sur la livraison ? <a href="contact.php">Contactez notre support</a>.</p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<style>
/* === Styles spécifiques à la page Shipping avec animation === */
body.shipping-page {
    background: linear-gradient(135deg, #f0f4f8, #dfe9f3);
    background-attachment: fixed;
    overflow-x: hidden;
    position: relative;
    font-family: 'Inter', sans-serif;
    color: var(--dark-color);
}

/* Overlay animé léger pour effet subtil */
body.shipping-page::before {
    content: '';
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background-image: radial-gradient(rgba(255,255,255,0.05) 1px, transparent 1px);
    background-size: 50px 50px;
    animation: moveBackground 60s linear infinite;
    pointer-events: none;
    z-index: 0;
}

@keyframes moveBackground {
    0% { background-position: 0 0; }
    100% { background-position: 1000px 1000px; }
}

/* Container principal */
.shipping-container {
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.95);
    padding: 2rem;
    border-radius: var(--border-radius-lg);
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
    margin: 2rem auto;
}

/* Titres avec dégradé unique */
h1, h2, h3, h4 {
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Sections */
.shipping-section {
    margin-bottom: 2rem;
}

.shipping-option, .region {
    background: rgba(255, 255, 255, 0.8);
    padding: 1rem 1.5rem;
    margin-bottom: 1rem;
    border-radius: var(--border-radius);
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.shipping-option:hover, .region:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

/* Delivery times & prices */
.delivery-time { font-weight: 600; color: var(--primary-color); }
.price { font-weight: 700; color: var(--accent-color); }

/* Liens */
.shipping-contact a {
    color: var(--primary-color);
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
}

.shipping-contact a:hover {
    color: var(--accent-color);
    text-decoration: underline;
}
</style>

<script>
// Ajout d'une classe body spécifique pour cette page
document.body.classList.add('shipping-page');
</script>
