<?php
require_once 'config/database.php';
require_once 'config/BrawlStarsAPI.php';
require_once 'config/api_config.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

// Obtenir les données de l'utilisateur connecté
$currentUser = $userManager->getCurrentUser();
$flashMessage = getFlashMessage();
$user = $userManager->getCurrentUser();

// Initialiser l'API Brawl Stars
$brawlAPI = new BrawlStarsAPI(BRAWL_STARS_API_KEY);

// Variables pour les données
$playerData = null;
$events = null;
$error = '';
$success = '';

// Si l'utilisateur a un ID Brawl Stars, récupérer ses données
if (!empty($user['brawl_stars_id'])) {
    $playerData = $brawlAPI->getPlayer($user['brawl_stars_id']);
    if (!$playerData) {
        $error = 'Impossible de récupérer vos statistiques Brawl Stars. Vérifiez votre ID ou réessayez plus tard.';
    }
}

// Récupérer les événements en cours
$events = $brawlAPI->getEvents();

// Gestion de la mise à jour de l'ID Brawl Stars
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_brawl_id'])) {
    $newBrawlId = Utils::sanitize($_POST['brawl_stars_id'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!Utils::verifyCSRFToken($csrf_token)) {
        $error = 'Token de sécurité invalide.';
    } elseif (empty($newBrawlId)) {
        $error = 'Veuillez saisir votre ID Brawl Stars.';
    } elseif (!BrawlStarsAPI::isValidPlayerTag($newBrawlId)) {
        $error = 'Format d\'ID Brawl Stars invalide. Utilisez le format #2PP ou 2PP avec des caractères alphanumériques (0-9, A-Z).';
    } else {
        $formattedId = BrawlStarsAPI::formatPlayerTag($newBrawlId);
        
        // Tester l'ID avec l'API
        $testData = $brawlAPI->getPlayer($formattedId);
        if (!$testData) {
            $error = 'ID Brawl Stars introuvable. Vérifiez que votre tag est correct.';
        } else {
            // Mettre à jour l'ID dans le profil
            $updateResult = $userManager->updateProfile(
                $user['id'], 
                $user['username'], 
                $user['email'], 
                $user['avatar'], 
                null,
                $formattedId
            );
            
            if ($updateResult['success']) {
                $success = 'ID Brawl Stars mis à jour avec succès !';
                $user['brawl_stars_id'] = $formattedId;
                $playerData = $testData;
            } else {
                $error = 'Erreur lors de la mise à jour de votre ID.';
            }
        }
    }
}

$csrf_token = Utils::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Brawl Stars — Événements et statistiques : rotation des modes et cartes, connectez votre ID pour afficher vos stats sur BrawlForum.">
    <title>Brawl Forum - Événements & Statistiques</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/events.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="assets/img/favicon.png" type="image/png">
</head>
<body>
    <!-- Navigation Header -->
    <nav class="nav-header">
        <div class="nav-links">
            <a href="index.php" class="nav-link active">Accueil</a>
            <a href="posts.php" class="nav-link">Tous les posts</a>
            <a href="add-post.php" class="nav-link">Ajouter un post</a>
            <a href="events.php" class="nav-link">Événements</a>
        </div>
        
        <!-- Logo Central -->
        <div class="logo">
            <img src="assets/img/logo.png" alt="Brawl Forum Logo" class="logo-image">
        </div>
        
        <div class="nav-links">
            <?php if ($currentUser): ?>
                <a href="profile.php" class="nav-link">Mon profil</a>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <a href="admin.php" class="nav-link">Administration</a>
                <?php endif; ?>
                <a href="logout.php" class="nav-link">Déconnexion</a>
                <span class="user-welcome">Bienvenue, <?= htmlspecialchars($currentUser['username']) ?> !</span>
            <?php else: ?>
                <a href="login.php" class="nav-link">Connexion</a>
                <a href="register.php" class="nav-link">Inscription</a>
            <?php endif; ?>
        </div>
    </nav>
    
    <?php if ($flashMessage): ?>
        <div class="flash-message flash-<?= $flashMessage['type'] ?>">
            <i class="fas fa-<?= $flashMessage['type'] === 'success' ? 'check' : ($flashMessage['type'] === 'error' ? 'times' : 'info') ?>-circle"></i>
            <?= htmlspecialchars($flashMessage['message']) ?>
        </div>
    <?php endif; ?>
    
    <div class="events-container">
        <!-- En-tête de la page -->
        <div class="page-header fade-in">
            <h1 class="page-title">
                <i class="fas fa-trophy"></i> Événements & Statistiques
            </h1>
            <p class="page-subtitle">
                Découvrez les événements en cours et consultez vos statistiques Brawl Stars
            </p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <!-- Grille principale -->
        <div class="events-grid">
            <!-- Section Statistiques -->
            <div class="stats-section fade-in">
                <h2 class="section-title">
                    <i class="fas fa-chart-bar"></i> Mes Statistiques
                </h2>
                
                <?php if (empty($user['brawl_stars_id'])): ?>
                    <div class="no-id-message">
                        <i class="fas fa-gamepad"></i>
                        <h3>Connectez votre compte Brawl Stars</h3>
                        <p>Ajoutez votre ID Brawl Stars pour voir vos statistiques ici !</p>
                    </div>
                    
                    <div class="brawl-id-form">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            
                            <div class="form-group">
                                <label for="brawl_stars_id" class="form-label">
                                    <i class="fas fa-gamepad"></i> Votre ID Brawl Stars
                                </label>
                                <input type="text" id="brawl_stars_id" name="brawl_stars_id" class="form-input" 
                                       placeholder="Ex: #2PP ou 2PP" required
                                       pattern="^#?[0-9A-Za-z]{3,9}$"
                                       title="Format valide: #2PP ou 2PP (caractères autorisés: 0-9, A-Z)">
                                <small style="color: #ccc; font-size: 0.9rem; margin-top: 5px; display: block;">
                                    <i class="fas fa-info-circle"></i> Vous pouvez trouver votre tag dans le jeu, dans votre profil
                                </small>
                            </div>
                            
                            <button type="submit" name="update_brawl_id" class="btn-update">
                                <i class="fas fa-link"></i> Connecter mon compte
                            </button>
                        </form>
                    </div>
                    
                <?php elseif ($playerData): ?>
                    <div class="player-info">
                        <div class="player-name"><?= htmlspecialchars($playerData['name']) ?></div>
                        <div class="player-tag"><?= htmlspecialchars($playerData['tag']) ?></div>
                    </div>
                    
                    <div class="player-stats">
                        <div class="stat-card">
                            <i class="fas fa-trophy stat-icon" style="color: #ffd700;"></i>
                            <span class="stat-value"><?= number_format($playerData['trophies']) ?></span>
                            <span class="stat-label">Trophées</span>
                        </div>
                        
                        <div class="stat-card">
                            <i class="fas fa-crown stat-icon" style="color: #ff6b35;"></i>
                            <span class="stat-value"><?= number_format($playerData['highestTrophies']) ?></span>
                            <span class="stat-label">Record</span>
                        </div>
                        
                        <div class="stat-card">
                            <i class="fas fa-star stat-icon" style="color: #00ff00;"></i>
                            <span class="stat-value"><?= $playerData['expLevel'] ?></span>
                            <span class="stat-label">Niveau</span>
                        </div>
                        
                        <div class="stat-card">
                            <i class="fas fa-users stat-icon" style="color: #00bfff;"></i>
                            <span class="stat-value"><?= isset($playerData['3vs3Victories']) ? number_format($playerData['3vs3Victories']) : '0' ?></span>
                            <span class="stat-label">Victoires 3v3</span>
                        </div>
                        
                        <div class="stat-card">
                            <i class="fas fa-user stat-icon" style="color: #ff69b4;"></i>
                            <span class="stat-value"><?= isset($playerData['soloVictories']) ? number_format($playerData['soloVictories']) : '0' ?></span>
                            <span class="stat-label">Victoires Solo</span>
                        </div>
                        
                        <div class="stat-card">
                            <i class="fas fa-users-cog stat-icon" style="color: #9370db;"></i>
                            <span class="stat-value"><?= isset($playerData['duoVictories']) ? number_format($playerData['duoVictories']) : '0' ?></span>
                            <span class="stat-label">Victoires Duo</span>
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <small style="color: #ccc;">
                            <i class="fas fa-sync-alt"></i> Dernière mise à jour: maintenant
                        </small>
                    </div>
                    
                <?php else: ?>
                    <div class="no-id-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>Erreur de connexion</h3>
                        <p>Impossible de récupérer vos statistiques. Vérifiez votre ID ou réessayez plus tard.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Section Événements -->
            <div class="events-section fade-in">
                <h2 class="section-title">
                    <i class="fas fa-calendar-alt"></i> Événements en cours
                </h2>
                
                <?php if ($events && !empty($events)): ?>
                    <?php foreach ($events as $event): ?>
                        <div class="event-item">
                            <div class="event-mode">
                                <i class="fas fa-gamepad"></i> 
                                <?= htmlspecialchars($event['event']['mode'] ?? 'Mode inconnu') ?>
                            </div>
                            <div class="event-map">
                                <i class="fas fa-map"></i> 
                                <?= htmlspecialchars($event['event']['map'] ?? 'Carte inconnue') ?>
                            </div>
                            <div class="event-time">
                                <i class="fas fa-clock"></i> 
                                <?php 
                                if (isset($event['startTime']) && isset($event['endTime'])) {
                                    try {
                                        // Gérer le format ISO 8601 avec millisecondes
                                        $startTimeStr = str_replace('.000Z', 'Z', $event['startTime']);
                                        $endTimeStr = str_replace('.000Z', 'Z', $event['endTime']);
                                        
                                        $start = new DateTime($startTimeStr);
                                        $end = new DateTime($endTimeStr);
                                        echo 'Jusqu\'au ' . $end->format('d/m à H:i');
                                    } catch (Exception $e) {
                                        echo 'Horaires non disponibles';
                                    }
                                } else {
                                    echo 'Horaires non disponibles';
                                }
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-id-message">
                        <i class="fas fa-calendar-times"></i>
                        <h3>Événements indisponibles</h3>
                        <p>Impossible de récupérer les événements en cours. Réessayez plus tard.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <footer class="site-footer" role="contentinfo" aria-label="Pied de page">
        <div class="footer-container">
            <div class="footer-brand">BrawlForum</div>
            <div class="footer-links">
                <a href="privacy.php" class="footer-link" aria-label="Politique de confidentialité">Confidentialité</a>
                <a href="terms.php" class="footer-link" aria-label="Conditions d'utilisation">Conditions</a>
            </div>
            <div class="footer-copy">© <?= date('Y') ?> BrawlForum. Tous droits réservés.</div>
        </div>
    </footer>

    <script>
        // Animation d'apparition
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
    
    <script>
        // Animation d'apparition
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>
</html>