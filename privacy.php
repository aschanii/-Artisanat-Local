<?php
$page_title = "Politique de Confidentialité";
require_once 'includes/config.php';

?>

<?php
session_start(); // obligatoire pour $_SESSION
require_once 'includes/functions.php';
$page_title = "Politique de Confidentialité";
include 'includes/header.php';
?>


<main class="container legal-container">
    <div class="page-header">
        <h1>Politique de Confidentialité</h1>
        <p>Dernière mise à jour : <?php echo date('j F Y'); ?></p>
    </div>

    <div class="legal-content">
        <section>
            <h2>1. Informations que nous collectons</h2>
            <p>Nous recueillons les informations que vous nous fournissez directement, notamment :</p>
            <ul>
                <li>Nom et coordonnées</li>
                <li>Identifiants de compte</li>
                <li>Informations de paiement</li>
                <li>Historique des transactions</li>
                <li>Vos communications avec notre service</li>
            </ul>
        </section>

        <section>
            <h2>2. Utilisation de vos informations</h2>
            <p>Nous utilisons ces informations pour :</p>
            <ul>
                <li>Fournir et maintenir nos services</li>
                <li>Traiter vos commandes et paiements</li>
                <li>Envoyer des informations administratives</li>
                <li>Personnaliser votre expérience utilisateur</li>
                <li>Améliorer nos produits et services</li>
            </ul>
        </section>

        <section>
            <h2>3. Partage des informations</h2>
            <p>Nous ne vendons pas vos données personnelles. Cependant, nous pouvons partager certaines informations avec :</p>
            <ul>
                <li>Les artisans (pour le traitement des commandes)</li>
                <li>Nos prestataires de services (livraison, paiement, etc.)</li>
                <li>Les autorités légales si la loi l’exige</li>
            </ul>
        </section>

        <section>
            <h2>4. Sécurité des données</h2>
            <p>Nous appliquons des mesures techniques et organisationnelles afin de protéger vos informations personnelles contre tout accès non autorisé, perte ou divulgation.</p>
        </section>

        <section>
            <h2>5. Vos droits</h2>
            <p>Conformément au RGPD, vous disposez du droit de :</p>
            <ul>
                <li>Accéder à vos données personnelles</li>
                <li>Demander leur rectification</li>
                <li>Demander leur suppression</li>
                <li>Vous opposer à leur traitement</li>
            </ul>
        </section>

        <section>
            <h2>6. Cookies</h2>
            <p>Nous utilisons des cookies et technologies similaires pour améliorer votre navigation et analyser l’utilisation du site. Vous pouvez gérer vos préférences via les paramètres de votre navigateur.</p>
        </section>

        <section>
            <h2>7. Contact</h2>
            <p>Pour toute question relative à cette politique, vous pouvez nous contacter à : 
                <a href="mailto:confidentialite@handmadehaven.com">confidentialite@handmadehaven.com</a>.
            </p>
        </section>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
