<?php
session_start();
require_once 'config/database.php';

$currentUser = $userManager->getCurrentUser();
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Politique de confidentialité BrawlForum : données collectées, utilisation, protection et droits des utilisateurs.">
    <title>Politique de confidentialité - Brawl Forum</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/privacy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="assets/img/favicon.png" type="image/png">
</head>
<body>
    <!-- Navbar retirée sur cette page -->

    <?php if ($flashMessage): ?>
        <div class="flash-message flash-<?= $flashMessage['type'] ?>">
            <i class="fas fa-<?= $flashMessage['type'] === 'success' ? 'check' : ($flashMessage['type'] === 'error' ? 'times' : 'info') ?>-circle"></i>
            <?= htmlspecialchars($flashMessage['message']) ?>
        </div>
    <?php endif; ?>

    <div class="privacy-container">
        <div class="privacy-panel">
            <h1 class="privacy-title">Politique de confidentialité</h1>
            
            <div class="privacy-content">
                <div class="privacy-item">
                    <div class="privacy-icon icon-chart">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="privacy-text">
                        <h3>Données collectées</h3>
                        <p>Nous collectons des données sur votre nom d'utilisateur, votre adresse e-mail et des informations sur votre activité sur notre forum.</p>
                    </div>
                </div>

                <div class="privacy-item">
                    <div class="privacy-icon icon-gear">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="privacy-text">
                        <h3>Utilisation des données</h3>
                        <p>Nous utilisons vos données pour fournir, améliorer et protéger notre forum ainsi que pour communiquer avec vous.</p>
                    </div>
                </div>

                <div class="privacy-item">
                    <div class="privacy-icon icon-lock">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="privacy-text">
                        <h3>Partage et sécurité</h3>
                        <p>Nous ne partageons pas vos données avec des tiers sans votre consentement.</p>
                    </div>
                </div>

                <div class="privacy-item">
                    <div class="privacy-icon icon-shield">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="privacy-text">
                        <h3>Droits des utilisateurs</h3>
                        <p>Vous pouvez demander l'accès, la rectification ou la suppression de vos données personnelles à tout moment.</p>
                    </div>
                </div>
            </div>

            <div class="privacy-footer">
                <p>Nous nous conformons au RGPD et autres lois applicables en matière de protection des données.</p>
            </div>

            <div class="back-button">
                <a href="index.php" class="btn-back">Retour à l'accueil</a>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>