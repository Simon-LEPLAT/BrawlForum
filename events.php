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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="assets/img/favicon.png" type="image/png" />
    <style>
        .events-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            background: rgba(0,0,0,0.3);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 3px solid #000;
            text-align: center;
        }
        
        .page-title {
            font-size: 3rem;
            color: #ffd700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 0px #000;
        }
        
        .page-subtitle {
            font-size: 1.2rem;
            color: #ccc;
        }
        
        .events-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 1024px) {
            .events-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .stats-section, .events-section {
            background: rgba(0,0,0,0.3);
            border-radius: 20px;
            padding: 30px;
            border: 3px solid #000;
        }
        
        .section-title {
            font-size: 2rem;
            color: #ffd700;
            margin-bottom: 25px;
            text-align: center;
            text-shadow: 2px 2px 0px #000;
        }
        
        .no-id-message {
            text-align: center;
            padding: 40px 20px;
            background: rgba(255, 107, 53, 0.1);
            border-radius: 15px;
            border: 2px solid #ff6b35;
            margin-bottom: 20px;
        }
        
        .no-id-message i {
            font-size: 3rem;
            color: #ff6b35;
            margin-bottom: 15px;
            display: block;
        }
        
        .brawl-id-form {
            background: rgba(0,0,0,0.2);
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            color: #ffd700;
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #000;
            border-radius: 25px;
            background: rgba(255,255,255,0.1);
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #ffd700;
            background: rgba(255,255,255,0.15);
        }
        
        .btn-update {
            background: linear-gradient(45deg, #ff6b35, #ff4444);
            color: white;
            border: 2px solid #000;
            border-radius: 25px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            text-transform: uppercase;
        }
        
        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
        }
        
        .player-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(0,0,0,0.2);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            border: 2px solid #333;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            border-color: #ffd700;
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: block;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #ffd700;
            display: block;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #ccc;
            font-size: 1rem;
            text-transform: uppercase;
        }
        
        .player-info {
            background: rgba(0,0,0,0.2);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .player-name {
            font-size: 2rem;
            color: #ffd700;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .player-tag {
            font-size: 1.2rem;
            color: #ccc;
            margin-bottom: 15px;
        }
        
        .event-item {
            background: rgba(0,0,0,0.2);
            border-radius: 15px;
            padding: 20px;
            margin: 15px 0;
            border: 2px solid #333;
            transition: all 0.3s ease;
        }
        
        .event-item:hover {
            border-color: #ffd700;
            transform: translateX(10px);
        }
        
        .event-mode {
            font-size: 1.3rem;
            font-weight: bold;
            color: #ffd700;
            margin-bottom: 5px;
        }
        
        .event-map {
            color: #ccc;
            font-size: 1rem;
            margin-bottom: 10px;
        }
        
        .event-time {
            color: #ff6b35;
            font-size: 0.9rem;
        }
        
        .error-message {
            background: rgba(255, 0, 0, 0.1);
            border: 2px solid #ff4444;
            border-radius: 15px;
            padding: 15px;
            margin: 15px 0;
            color: #ff4444;
            text-align: center;
        }
        
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }
    </style>
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