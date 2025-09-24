<?php
require_once 'config/database.php';

$currentUser = $userManager->getCurrentUser();
$flashMessage = getFlashMessage();

// Gestion du filtrage
$category = $_GET['category'] ?? 'all';
$search = $_GET['search'] ?? '';

// Récupérer tous les posts avec filtrage
if ($category === 'all') {
    $posts = $postManager->getAllPosts($search);
} else {
    $posts = $postManager->getPostsByCategory($category, $search);
}

$categories = [
    'all' => 'Tous',
    'strategies' => 'Stratégies',
    'team' => 'Équipes',
    'skins' => 'Skins',
    'events' => 'Événements'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brawl Forum - Tous les posts</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .posts-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .posts-header {
            background: rgba(0,0,0,0.3);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 3px solid #000;
            text-align: center;
        }
        
        .filters-section {
            background: rgba(0,0,0,0.3);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            border: 2px solid #000;
        }
        
        .category-filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .filter-btn {
            background: linear-gradient(45deg, #ff6b35, #ff4444);
            color: white;
            border: 2px solid #000;
            border-radius: 25px;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        
        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
        }
        
        .filter-btn.active {
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            color: #000;
        }
        
        .search-box {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        
        .search-input {
            background: rgba(255,255,255,0.1);
            border: 2px solid #000;
            border-radius: 25px;
            padding: 12px 20px;
            color: white;
            font-size: 1rem;
            width: 300px;
            max-width: 100%;
        }
        
        .search-input::placeholder {
            color: rgba(255,255,255,0.7);
        }
        
        .search-btn {
            background: linear-gradient(45deg, #ff6b35, #ff4444);
            color: white;
            border: 2px solid #000;
            border-radius: 25px;
            padding: 12px 20px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            transform: translateY(-2px);
        }
        
        .posts-grid {
            display: grid;
            gap: 20px;
        }
        
        .post-card {
            background: rgba(0,0,0,0.3);
            border-radius: 15px;
            padding: 25px;
            border: 2px solid #000;
            transition: all 0.3s ease;
        }
        
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        
        .post-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .post-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(45deg, #ff6b35, #ff4444);
            border: 2px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            font-weight: bold;
        }
        
        .post-meta {
            flex: 1;
        }
        
        .post-author {
            font-weight: bold;
            color: #ffd700;
            font-size: 1.1rem;
        }
        
        .post-date {
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
        }
        
        .post-category {
            background: linear-gradient(45deg, #ff6b35, #ff4444);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #000;
        }
        
        .post-title {
            font-size: 1.4rem;
            font-weight: bold;
            color: #ffd700;
            margin-bottom: 10px;
            text-decoration: none;
        }
        
        .post-title:hover {
            color: #ffed4e;
        }
        
        .post-content {
            color: white;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .post-stats {
            display: flex;
            gap: 20px;
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
        }
        
        .post-stat {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .no-posts {
            text-align: center;
            color: rgba(255,255,255,0.7);
            font-size: 1.2rem;
            padding: 50px;
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <nav class="nav-header">
        <div class="nav-links">
            <a href="index.php" class="nav-link">Accueil</a>
            <a href="posts.php" class="nav-link active">Tous les posts</a>
            <a href="add-post.php" class="nav-link">Ajouter un post</a>
        </div>
        
        <!-- Logo Central -->
        <div class="logo">
            <img src="assets/img/logo.png" alt="Brawl Forum Logo" class="logo-image">
        </div>
        
        <div class="nav-links">
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

    <div class="posts-container">
        <!-- En-tête -->
        <div class="posts-header fade-in">
            <h1 class="brawl-title" style="font-size: 3rem; margin-bottom: 10px; color: #ffd700;">
                <i class="fas fa-comments"></i> Tous les Posts
            </h1>
            <p style="color: rgba(255,255,255,0.8); font-size: 1.2rem;">
                Découvrez toutes les discussions de la communauté Brawl Stars
            </p>
        </div>
        
        <!-- Filtres -->
        <div class="filters-section fade-in">
            <div class="category-filters">
                <?php foreach ($categories as $cat => $label): ?>
                    <a href="posts.php?category=<?= $cat ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                       class="filter-btn <?= $category === $cat ? 'active' : '' ?>">
                        <?= $label ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <form method="GET" class="search-box">
                <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                <input type="text" name="search" placeholder="Rechercher dans les posts..." 
                       value="<?= htmlspecialchars($search) ?>" class="search-input">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Rechercher
                </button>
            </form>
        </div>
        
        <!-- Liste des posts -->
        <div class="posts-grid fade-in">
            <?php if (empty($posts)): ?>
                <div class="no-posts">
                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 20px; display: block;"></i>
                    Aucun post trouvé pour cette recherche.
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <div class="post-header">
                            <div class="post-avatar">
                                <?= strtoupper(substr($post['author'], 0, 1)) ?>
                            </div>
                            <div class="post-meta">
                                <div class="post-author"><?= htmlspecialchars($post['author']) ?></div>
                                <div class="post-date"><?= date('d/m/Y à H:i', strtotime($post['created_at'])) ?></div>
                            </div>
                            <div class="post-category"><?= htmlspecialchars($categories[$post['category']] ?? $post['category']) ?></div>
                        </div>
                        
                        <h3 class="post-title">
                            <a href="post.php?id=<?= $post['id'] ?>" style="color: inherit; text-decoration: none;">
                                <?= htmlspecialchars($post['title']) ?>
                            </a>
                        </h3>
                        
                        <div class="post-content">
                            <?= nl2br(htmlspecialchars(substr($post['content'], 0, 200))) ?>
                            <?php if (strlen($post['content']) > 200): ?>
                                <a href="post.php?id=<?= $post['id'] ?>" style="color: #ffd700;">... Lire la suite</a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="post-stats">
                            <div class="post-stat">
                                <i class="fas fa-comments"></i>
                                <span><?= $post['comments_count'] ?? 0 ?> commentaires</span>
                            </div>
                            <div class="post-stat">
                                <i class="fas fa-eye"></i>
                                <span><?= $post['views'] ?? 0 ?> vues</span>
                            </div>
                            <div class="post-stat">
                                <i class="fas fa-heart"></i>
                                <span><?= $post['likes'] ?? 0 ?> likes</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>