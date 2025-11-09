<?php
require_once 'config/database.php';

$currentUser = $userManager->getCurrentUser();
$flashMessage = getFlashMessage();

// Gestion de l'ajout de commentaires
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    if (!$currentUser) {
        setFlashMessage('Vous devez être connecté pour commenter.', 'error');
    } else {
        $postId = (int)($_POST['post_id'] ?? 0);
        $content = $_POST['comment_content'] ?? '';
        
        if ($postId > 0 && !empty(trim($content))) {
            $result = $postManager->addComment($postId, $currentUser['id'], $content);
            if (!$result['success'] && isset($result['message'])) {
                setFlashMessage($result['message'], 'error');
            }
        } else {
            setFlashMessage('Veuillez saisir un commentaire valide.', 'error');
        }
    }
    
    // Redirection pour éviter la resoumission
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// Gestion du filtrage
$category = $_GET['category'] ?? 'all';
$search = $_GET['search'] ?? '';

// Récupérer tous les posts avec filtrage
if ($category === 'all') {
    $posts = $postManager->getAllPosts($search);
} else {
    $posts = $postManager->getPostsByCategory($category, $search);
}

// Ajouter le nombre de commentaires pour chaque post
foreach ($posts as &$post) {
    $post['comments_count'] = $postManager->getCommentsCount($post['id']);
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
    <meta name="description" content="Tous les posts Brawl Stars : filtre par catégories, recherche, consultation des discussions et commentaires sur BrawlForum.">
    <title>Brawl Forum - Tous les posts</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/posts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="assets/img/favicon.png" type="image/png">
</head>
<body>
    <!-- Ancre de haut de page -->
    <div id="top"></div>
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

    <div class="posts-container">
        <!-- En-tête -->
        <div class="posts-header fade-in">
            <h1 id="postsTitle" class="brawl-title" style="font-size: 3rem; margin-bottom: 10px; color: #ffd700;">
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
                    <div class="post-card" id="post-<?= $post['id'] ?>">
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
                            <a href="posts.php#post-<?= $post['id'] ?>" style="color: inherit; text-decoration: none;">
                                <?= htmlspecialchars($post['title']) ?>
                            </a>
                        </h3>
                        
                        <div class="post-content">
                            <?= nl2br(htmlspecialchars(substr($post['content'], 0, 200))) ?>
                            <?php if (strlen($post['content']) > 200): ?>
                                <a href="posts.php#post-<?= $post['id'] ?>" style="color: #ffd700;">... Lire la suite</a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="post-stats">
                            <div class="post-stat comments-toggle" onclick="toggleComments(<?= $post['id'] ?>)">
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
                        
                        <!-- Section des commentaires (cachée par défaut) -->
                        <div class="comments-section" id="comments-<?= $post['id'] ?>">
                            <div class="comments-list">
                                <?php 
                                $comments = $postManager->getCommentsByPostId($post['id']);
                                if (empty($comments)): 
                                ?>
                                    <div class="no-comments">
                                        <i class="fas fa-comment-slash"></i>
                                        Aucun commentaire pour le moment. Soyez le premier à commenter !
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($comments as $comment): ?>
                                        <div class="comment-item">
                                            <div class="comment-header">
                                                <div class="comment-avatar">
                                                    <?= strtoupper(substr($comment['username'], 0, 1)) ?>
                                                </div>
                                                <div class="comment-meta">
                                                    <div class="comment-author"><?= htmlspecialchars($comment['username']) ?></div>
                                                    <div class="comment-date"><?= date('d/m/Y à H:i', strtotime($comment['created_at'])) ?></div>
                                                </div>
                                            </div>
                                            <div class="comment-content">
                                                <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Formulaire d'ajout de commentaire -->
                            <?php if ($currentUser): ?>
                                <div class="comment-form">
                                    <h4><i class="fas fa-plus-circle"></i> Ajouter un commentaire</h4>
                                    <form method="POST" action="">
                                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                        <textarea name="comment_content" class="comment-textarea" 
                                                placeholder="Écrivez votre commentaire ici..." required></textarea>
                                        <button type="submit" name="add_comment" class="comment-submit">
                                            <i class="fas fa-paper-plane"></i> Publier le commentaire
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="comment-form">
                                    <p style="color: rgba(255,255,255,0.7); text-align: center;">
                                        <i class="fas fa-sign-in-alt"></i> 
                                        <a href="login.php" style="color: #ffd700;">Connectez-vous</a> pour ajouter un commentaire.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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

    <script src="assets/js/main.js"></script>
    <script>
        // Fonction pour basculer l'affichage des commentaires
        function toggleComments(postId) {
            const commentsSection = document.getElementById('comments-' + postId);
            
            if (commentsSection.classList.contains('expanded')) {
                // Fermer les commentaires
                commentsSection.classList.remove('expanded');
                commentsSection.style.display = 'none';
            } else {
                // Ouvrir les commentaires
                commentsSection.style.display = 'block';
                commentsSection.classList.add('expanded');
                
                // Scroll vers la section des commentaires
                setTimeout(() => {
                    commentsSection.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'nearest' 
                    });
                }, 100);
            }
        }
        
        // Fermer les autres sections de commentaires quand on en ouvre une nouvelle
        function closeOtherComments(currentPostId) {
            const allCommentsSections = document.querySelectorAll('.comments-section');
            allCommentsSections.forEach(section => {
                if (section.id !== 'comments-' + currentPostId && section.classList.contains('expanded')) {
                    section.classList.remove('expanded');
                    section.style.display = 'none';
                }
            });
        }
        
        // Améliorer la fonction toggleComments pour fermer les autres
        function toggleComments(postId) {
            const commentsSection = document.getElementById('comments-' + postId);
            
            if (commentsSection.classList.contains('expanded')) {
                // Fermer les commentaires
                commentsSection.classList.remove('expanded');
                commentsSection.style.display = 'none';
            } else {
                // Fermer les autres sections ouvertes
                closeOtherComments(postId);
                
                // Ouvrir les commentaires
                commentsSection.style.display = 'block';
                commentsSection.classList.add('expanded');
                
                // Scroll vers la section des commentaires
                setTimeout(() => {
                    commentsSection.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'nearest' 
                    });
                }, 100);
            }
        }
        
        // Animation d'apparition des posts au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const postCards = document.querySelectorAll('.post-card');
            postCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>

    <script>
        // Si on arrive avec un hash (#post-123) ou ?post_id=123, scroller et ouvrir les commentaires
        document.addEventListener('DOMContentLoaded', function() {
            let targetId = null;
            const hash = window.location.hash;
            if (hash && hash.startsWith('#post-')) {
                targetId = hash.replace('#post-', '');
            } else {
                const params = new URLSearchParams(window.location.search);
                const pid = params.get('post_id');
                if (pid) targetId = pid;
            }

            if (targetId) {
                const card = document.getElementById('post-' + targetId);
                if (card) {
                    card.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    setTimeout(() => toggleComments(targetId), 200);
                }
            }
        });
    </script>

    

    <!-- Lien d'ancrage Retour en haut -->
    <a href="#top" id="backToTop" class="back-to-top" aria-label="Remonter en haut">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script>
        // Afficher/Masquer le lien "Retour en haut" en fonction de la visibilité du titre
        document.addEventListener('DOMContentLoaded', function() {
            const titleEl = document.getElementById('postsTitle');
            const backLink = document.getElementById('backToTop');
            if (!titleEl || !backLink) return;

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        backLink.classList.remove('show');
                    } else {
                        backLink.classList.add('show');
                    }
                });
            }, { root: null, threshold: 0 });
            observer.observe(titleEl);
        });
    </script>


</body>
</html>