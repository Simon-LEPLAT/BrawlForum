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
    <meta name="description" content="Conditions d’utilisation BrawlForum : règles du forum, comptes, responsabilités, modération et bonnes pratiques.">
    <title>Conditions d'utilisation - Brawl Forum</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/terms.css">
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

    <div class="terms-container">
        <div class="terms-panel">
            <h1 class="terms-title">Conditions d'utilisation</h1>
            
            <div class="terms-content">
                <div class="terms-item">
                    <div class="terms-icon icon-star">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="terms-text">
                        <h3>1. Objet</h3>
                        <p>Les présentes conditions d'utilisation régissent l'utilisation du forum Brawl Forum.</p>
                    </div>
                </div>

                <div class="terms-item">
                    <div class="terms-icon icon-star">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="terms-text">
                        <h3>Comptes utilisateurs</h3>
                        <p>Vous devez créer un compte pour publier. Vous êtes responsable de la sécurité de votre compte.</p>
                    </div>
                </div>

                <div class="terms-item">
                    <div class="terms-icon icon-shield">
                        <i class="fas fa-times"></i>
                    </div>
                    <div class="terms-text">
                        <h3>Règles de conduite</h3>
                        <p>Vous vous engagez à ne pas publier de contenu offensant, illégal ou enfreignant les règles du forum.</p>
                    </div>
                </div>

                <div class="terms-item">
                    <div class="terms-icon icon-lock">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="terms-text">
                        <h3>Propriété intellectuelle</h3>
                        <p>Les contenus publiés restent la propriété de leurs auteurs, mais vous accordez au forum une licence d'utilisation.</p>
                    </div>
                </div>

                <div class="terms-item">
                    <div class="terms-icon icon-warning">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <div class="terms-text">
                        <h3>Sanctions</h3>
                        <p>En cas de violation des règles, votre compte peut être suspendu ou banni.</p>
                    </div>
                </div>
            </div>

            <div class="terms-buttons">
                <a href="index.php" class="btn-accept">J'accepte</a>
                <a href="index.php" class="btn-refuse">Refuser</a>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>