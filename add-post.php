<?php
session_start();
require_once 'config/database.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

$error = '';
$success = '';
$currentUser = $userManager->getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = Utils::sanitize($_POST['title'] ?? '');
    $content = Utils::sanitize($_POST['content'] ?? '');
    $category = $_POST['category'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Vérification du token CSRF
    if (!Utils::verifyCSRFToken($csrf_token)) {
        $error = 'Token de sécurité invalide.';
    } elseif (empty($title) || empty($content) || empty($category)) {
        $error = 'Tous les champs sont obligatoires.';
    } else {
        $result = $postManager->createPost($title, $content, $category, $currentUser['id']);
        if ($result['success']) {
            setFlashMessage($result['message'], 'success');
            Utils::redirect('profile.php');
        } else {
            $error = $result['message'];
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
    <meta name="description" content="Créer un post sur BrawlForum : rédigez un titre et un contenu, choisissez une catégorie (stratégies, équipe, skins, événements) et publiez.">
    <title>Brawl Forum - Ajouter un post</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/add_post.css">
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

    <div class="add-post-container">
        <div class="add-post-panel fade-in">
            <h1 class="page-title brawl-title">Ajouter un post</h1>
            
            <!-- Messages d'erreur -->
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <!-- Message de succès -->
            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulaire d'ajout de post -->
            <form method="POST" action="" class="post-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <!-- Titre -->
                <div class="form-group">
                    <label class="form-label">Titre</label>
                    <input type="text" 
                           name="title" 
                           class="form-input" 
                           placeholder="Entrez le titre de votre post..."
                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                           required>
                </div>
                
                <!-- Contenu -->
                <div class="form-group">
                    <label class="form-label">Contenu</label>
                    <textarea name="content" 
                              class="form-input form-textarea" 
                              placeholder="Rédigez le contenu de votre post..."
                              required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                </div>
                
                <!-- Catégorie -->
                <div class="form-group">
                    <label class="form-label">Catégorie</label>
                    <div class="select-wrapper">
                        <select name="category" class="form-select" id="categorySelect" required>
                            <option value="">Sélectionnez une catégorie</option>
                            <option value="strategies" <?= ($_POST['category'] ?? '') === 'strategies' ? 'selected' : '' ?>>Stratégies</option>
                            <option value="team" <?= ($_POST['category'] ?? '') === 'team' ? 'selected' : '' ?>>Équipe</option>
                            <option value="skins" <?= ($_POST['category'] ?? '') === 'skins' ? 'selected' : '' ?>>Skins</option>
                            <option value="events" <?= ($_POST['category'] ?? '') === 'events' ? 'selected' : '' ?>>Événements</option>
                        </select>
                    </div>
                    
                    <!-- Aperçu des catégories -->
                    <div class="category-preview">
                        <div class="category-badge strategies" data-category="strategies">
                            <i class="fas fa-chess"></i> Stratégies
                        </div>
                        <div class="category-badge team" data-category="team">
                            <i class="fas fa-users"></i> Équipe
                        </div>
                        <div class="category-badge skins" data-category="skins">
                            <i class="fas fa-palette"></i> Skins
                        </div>
                        <div class="category-badge events" data-category="events">
                            <i class="fas fa-calendar-alt"></i> Événements
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn-publish">
                        <i class="fas fa-paper-plane"></i> Publier
                    </button>
                    <a href="index.php" class="btn-cancel">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Gestion de la sélection de catégorie
        const categorySelect = document.getElementById('categorySelect');
        const categoryBadges = document.querySelectorAll('.category-badge');
        
        // Synchroniser le select avec les badges
        categorySelect.addEventListener('change', function() {
            updateCategoryBadges(this.value);
        });
        
        // Permettre la sélection via les badges
        categoryBadges.forEach(badge => {
            badge.addEventListener('click', function() {
                const category = this.dataset.category;
                categorySelect.value = category;
                updateCategoryBadges(category);
            });
        });
        
        function updateCategoryBadges(selectedCategory) {
            categoryBadges.forEach(badge => {
                if (badge.dataset.category === selectedCategory) {
                    badge.classList.add('selected');
                } else {
                    badge.classList.remove('selected');
                }
            });
        }
        
        // Initialiser l'état des badges au chargement
        document.addEventListener('DOMContentLoaded', function() {
            updateCategoryBadges(categorySelect.value);
            
            // Animation d'entrée
            const panel = document.querySelector('.add-post-panel');
            panel.style.opacity = '0';
            panel.style.transform = 'translateY(50px)';
            
            setTimeout(() => {
                panel.style.transition = 'all 0.8s ease-out';
                panel.style.opacity = '1';
                panel.style.transform = 'translateY(0)';
            }, 100);
        });
        
        // Validation en temps réel
        const inputs = document.querySelectorAll('.form-input, .form-select');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.value.trim().length > 0) {
                    this.style.borderColor = '#4CAF50';
                } else {
                    this.style.borderColor = '#000';
                }
            });
        });
    </script>

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
</body>
</html>