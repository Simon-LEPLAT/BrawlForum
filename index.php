<?php
require_once 'config/database.php';

// Obtenir les données dynamiques
$categories = [
    ['name' => 'Stratégies', 'icon' => 'fas fa-chess', 'count' => 156, 'class' => 'strategies'],
    ['name' => 'Équipes', 'icon' => 'fas fa-users', 'count' => 89, 'class' => 'team'],
    ['name' => 'Skins', 'icon' => 'fas fa-palette', 'count' => 234, 'class' => 'skins'],
    ['name' => 'Événements', 'icon' => 'fas fa-calendar', 'count' => 67, 'class' => 'events']
];

$recentDiscussions = $postManager->getRecentPosts(5);
$currentUser = $userManager->getCurrentUser();
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brawl Forum - Accueil</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation Header -->
    <nav class="nav-header">
        <div class="nav-links">
            <a href="index.php" class="nav-link active">Accueil</a>
            <a href="posts.php" class="nav-link">Tous les posts</a>
            <a href="add-post.php" class="nav-link">Ajouter un post</a>
        </div>
        
        <!-- Logo Central -->
        <div class="logo">
            <img src="assets/img/logo.png" alt="Brawl Forum Logo" class="logo-image">
        </div>
        
        <div class="nav-links">
            <a href="#" class="nav-link">Filtrer</a>
            <?php if ($currentUser): ?>
                <a href="profile.php" class="nav-link">Mon profil</a>
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

    <div class="container">
        <!-- Image Shelly -->
        <div class="shelly-section fade-in">
            <img src="assets/img/shelly.png" alt="Shelly" class="shelly-image">
        </div>
        
        <!-- Section Catégories -->
        <div class="panel fade-in">
            <h2 class="brawl-title" style="font-size: 2.5rem; text-align: center; margin-bottom: 30px; color: #ffd700;">
                Catégories
            </h2>
            
            <div class="categories-grid">
                <!-- Stratégies -->
                <div class="category-card strategies" onclick="filterByCategory('strategies')">
                    <i class="fas fa-chess category-icon"></i>
                    <h3 class="category-title">Stratégies</h3>
                </div>
                
                <!-- Équipe -->
                <div class="category-card team" onclick="filterByCategory('team')">
                    <i class="fas fa-users category-icon"></i>
                    <h3 class="category-title">Équipe</h3>
                </div>
                
                <!-- Skins -->
                <div class="category-card skins" onclick="filterByCategory('skins')">
                    <i class="fas fa-palette category-icon"></i>
                    <h3 class="category-title">Skins</h3>
                </div>
                
                <!-- Événements -->
                <div class="category-card events" onclick="filterByCategory('events')">
                    <i class="fas fa-calendar-alt category-icon"></i>
                    <h3 class="category-title">Événements</h3>
                </div>
            </div>
        </div>

        <!-- Section Dernières Discussions -->
        <div class="panel fade-in">
            <h2 class="brawl-title" style="font-size: 2rem; margin-bottom: 25px; color: #ffd700;">
                Dernières discussions
            </h2>
            
            <div class="discussions-list">
                <?php foreach ($recentDiscussions as $discussion): ?>
                    <div class="discussion-item fade-in">
                        <div class="discussion-avatar">
                            <img src="<?= $discussion['avatar'] ?? 'https://cdn.brawlstats.com/player-icons/28000000.png' ?>" alt="Avatar">
                        </div>
                        <div class="discussion-content">
                            <h4><?= htmlspecialchars($discussion['title']) ?></h4>
                            <div class="discussion-meta">
                                <span class="author">Par <?= htmlspecialchars($discussion['author']) ?></span>
                                <span class="replies"><?= $discussion['replies'] ?> réponses</span>
                                <span class="time">Il y a <?= $discussion['last_activity'] ?></span>
                                <span class="category category-<?= $discussion['category'] ?>"><?= ucfirst($discussion['category']) ?></span>
                            </div>
                        </div>
                        <div class="discussion-stats">
                            <i class="fas fa-comments"></i>
                            <span><?= $discussion['replies'] ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>