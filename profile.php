<?php
require_once 'config/database.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

// Obtenir les données de l'utilisateur connecté
$user = $userManager->getCurrentUser();
$userPosts = $postManager->getUserPosts($user['id']);
$flashMessage = getFlashMessage();

// Gestion du filtrage des posts
$filter = $_GET['filter'] ?? 'all';
$filteredPosts = $userPosts;

if ($filter !== 'all') {
    $filteredPosts = array_filter($userPosts, function($post) use ($filter) {
        return $post['category'] === $filter;
    });
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brawl Forum - Mon Profil</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-header {
            background: rgba(0,0,0,0.3);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 3px solid #000;
            text-align: center;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(45deg, #ff6b35, #ff4444);
            border: 5px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 3rem;
            color: white;
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
        }
        
        .profile-username {
            font-size: 2.5rem;
            color: #ffd700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 0px #000;
        }
        
        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 20px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #ffd700;
            display: block;
        }
        
        .stat-label {
            font-size: 1rem;
            color: #ccc;
            text-transform: uppercase;
        }
        
        .posts-section {
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
        
        .post-item {
            background: rgba(0,0,0,0.2);
            border-radius: 15px;
            padding: 20px;
            margin: 15px 0;
            border: 2px solid #333;
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s;
        }
        
        .post-item:hover {
            background: rgba(0,0,0,0.4);
            border-color: #ffd700;
            transform: translateX(10px);
        }
        
        .post-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            flex-shrink: 0;
        }
        
        .post-content {
            flex: 1;
        }
        
        .post-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 5px;
            color: white;
        }
        
        .post-text {
            color: #ccc;
            font-size: 1rem;
            margin-bottom: 5px;
        }
        
        .post-time {
            color: #888;
            font-size: 0.9rem;
        }
        
        .post-stats {
            text-align: right;
            color: #ffd700;
            font-weight: bold;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 5px;
        }
        
        .post-stats-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 10px 20px;
            border: 2px solid #000;
            border-radius: 10px;
            background: rgba(0,0,0,0.3);
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-btn.active,
        .filter-btn:hover {
            background: linear-gradient(45deg, #ffd700, #ffaa00);
            color: #000;
        }
        
        @media (max-width: 768px) {
            .profile-stats {
                flex-direction: column;
                gap: 20px;
            }
            
            .post-item {
                flex-direction: column;
                text-align: center;
            }
            
            .post-stats {
                align-items: center;
            }
            
            .filter-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <nav class="nav-header">
        <div class="nav-links">
            <a href="index.php" class="nav-link">Accueil</a>
            <a href="#" class="nav-link">Tous les posts</a>
            <a href="add-post.php" class="nav-link">Ajouter un post</a>
        </div>
        
        <!-- Logo Central -->
        <div class="logo">
            <img src="assets/img/logo.png" alt="Brawl Forum Logo" class="logo-image">
        </div>
        
        <div class="nav-links">
            <a href="#" class="nav-link">Filtrer</a>
            <a href="profile.php" class="nav-link" style="color: #ffd700;">Mon profil</a>
            <a href="logout.php" class="nav-link">Déconnexion</a>
        </div>
    </nav>

    <div class="container">
        <!-- Messages flash -->
        <?php if ($flashMessage): ?>
            <div class="flash-message flash-<?= $flashMessage['type'] ?>">
                <i class="fas fa-<?= $flashMessage['type'] === 'success' ? 'check' : ($flashMessage['type'] === 'error' ? 'times' : 'info') ?>-circle"></i>
                <?= htmlspecialchars($flashMessage['message']) ?>
            </div>
        <?php endif; ?>

        <!-- En-tête du profil -->
        <div class="profile-header fade-in">
            <div class="profile-avatar">
                <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
            </div>
            <div class="profile-info">
                <h1 class="username"><?= htmlspecialchars($user['username']) ?></h1>
                <div class="profile-stats">
                    <div class="stat">
                        <i class="fas fa-calendar"></i>
                        <span>Membre depuis <?= date('F Y', strtotime($user['join_date'])) ?></span>
                    </div>
                    <div class="stat">
                        <i class="fas fa-edit"></i>
                        <span><?= $user['posts_count'] ?> posts</span>
                    </div>
                    <div class="stat">
                        <i class="fas fa-heart"></i>
                        <span><?= $user['likes_received'] ?> likes reçus</span>
                    </div>
                    <div class="stat">
                        <i class="fas fa-trophy"></i>
                        <span>Niveau <?= $user['level'] ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section des posts -->
        <div class="posts-section fade-in">
            <h2 class="section-title brawl-title">Mes Articles</h2>
            
            <!-- Boutons de filtre -->
            <div class="filter-buttons">
                <button class="filter-btn active" onclick="filterPosts('all')">Tous</button>
                <button class="filter-btn" onclick="filterPosts('strategies')">Stratégies</button>
                <button class="filter-btn" onclick="filterPosts('team')">Équipe</button>
                <button class="filter-btn" onclick="filterPosts('skins')">Skins</button>
                <button class="filter-btn" onclick="filterPosts('events')">Événements</button>
            </div>
            
            <!-- Liste des posts -->
            <?php if (empty($filteredPosts)): ?>
                <div class="no-posts">
                    <i class="fas fa-edit"></i>
                    <h3>Aucun post trouvé</h3>
                    <p>
                        <?php if ($filter === 'all'): ?>
                            Vous n'avez pas encore créé de posts. <a href="add-post.php">Créer votre premier post</a>
                        <?php else: ?>
                            Aucun post dans cette catégorie. <a href="?filter=all">Voir tous les posts</a>
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($filteredPosts as $index => $post): ?>
                <div class="post-item" data-category="<?= $index % 4 == 0 ? 'strategies' : ($index % 4 == 1 ? 'team' : ($index % 4 == 2 ? 'skins' : 'events')) ?>">
                    <div class="post-avatar">
                        <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
                    </div>
                    <div class="post-content">
                        <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>
                        <p class="post-text"><?= htmlspecialchars(substr($post['content'], 0, 150)) ?>...</p>
                        <div class="post-meta">
                            <span class="category category-<?= $post['category'] ?>"><?= ucfirst($post['category']) ?></span>
                            <span><?= htmlspecialchars($post['time']) ?></span>
                        </div>
                    </div>
                    <div class="post-stats">
                        <div class="post-stats-row">
                            <i class="fas fa-heart" style="color: #e74c3c;"></i>
                            <span><?= $post['likes'] ?></span>
                            <i class="fas fa-comments" style="color: #3498db; margin-left: 10px;"></i>
                            <span><?= $post['comments'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterPosts(category) {
            const posts = document.querySelectorAll('.post-item');
            const buttons = document.querySelectorAll('.filter-btn');
            
            // Mettre à jour les boutons
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filtrer les posts
            posts.forEach(post => {
                if (category === 'all' || post.dataset.category === category) {
                    post.style.display = 'flex';
                    post.style.animation = 'fadeIn 0.5s ease-out';
                } else {
                    post.style.display = 'none';
                }
            });
        }
        
        // Animation d'entrée
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    element.style.transition = 'all 0.6s ease-out';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>
</html>