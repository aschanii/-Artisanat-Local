<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
$page_title = "Conditions d'utilisation";
require_once 'includes/functions.php';
include 'includes/header.php';
?>


<?php 
session_start();
$page_title = "Conditions d'utilisation";
require_once 'includes/functions.php';
include 'includes/header.php';
?>    

<main class="container legal-container">
    <div class="page-header">
        <h1>Conditions d'utilisation</h1>
        <p>Dernière mise à jour : <?php echo date('j F Y'); ?></p>
    </div>

    <div class="legal-content">
        <section>
            <h2>1. Acceptation des conditions</h2>
            <p>En accédant et en utilisant Handmade Haven, vous acceptez d'être lié par les termes et dispositions du présent accord.</p>
        </section>

        <section>
            <h2>2. Licence d'utilisation</h2>
            <p>L'autorisation est accordée d'utiliser temporairement Handmade Haven pour un usage personnel et non commercial, à des fins de consultation transitoire uniquement.</p>
        </section>

        <section>
            <h2>3. Création de compte</h2>
            <p>Vous devez avoir au moins 18 ans pour créer un compte. Vous êtes responsable de la sécurité de votre compte.</p>
        </section>

        <section>
            <h2>4. Responsabilités des artisans</h2>
            <ul>
                <li>Décrire leurs produits avec précision</li>
                <li>Expédier les articles dans les délais prévus</li>
                <li>Maintenir la qualité des produits</li>
                <li>Gérer le service client lié à leurs produits</li>
            </ul>
        </section>

        <section>
            <h2>5. Achats et paiements</h2>
            <p>Tous les achats sont soumis à disponibilité. Nous nous réservons le droit d'annuler toute commande pour quelque raison que ce soit.</p>
        </section>

        <section>
            <h2>6. Retours et remboursements</h2>
            <p>Les politiques de retour sont définies par chaque artisan. Veuillez les consulter avant tout achat.</p>
        </section>

        <section>
            <h2>7. Utilisations interdites</h2>
            <ul>
                <li>À des fins illégales</li>
                <li>Pour harceler, abuser ou insulter d'autres personnes</li>
                <li>Pour soumettre des informations fausses ou trompeuses</li>
                <li>Pour télécharger des virus ou du code malveillant</li>
            </ul>
        </section>

        <section>
            <h2>8. Propriété intellectuelle</h2>
            <p>Le contenu de Handmade Haven nous appartient ou appartient à nos fournisseurs de contenu et est protégé par les lois internationales sur le droit d’auteur.</p>
        </section>

        <section>
            <h2>9. Résiliation</h2>
            <p>Nous pouvons suspendre ou mettre fin à votre accès à nos services immédiatement, sans préavis, en cas de violation des présentes conditions.</p>
        </section>

        <section>
            <h2>10. Modifications des conditions</h2>
            <p>Nous nous réservons le droit de modifier ces conditions à tout moment. Les utilisateurs seront informés de toute modification importante.</p>
        </section>

        <section>
            <h2>11. Informations de contact</h2>
            <p>Pour toute question concernant ces conditions, veuillez nous contacter à l’adresse suivante : 
               <a href="mailto:legal@handmadehaven.com">legal@handmadehaven.com</a>.
            </p>
        </section>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
