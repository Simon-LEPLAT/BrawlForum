<?php
require_once 'config/database.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

// Obtenir les données de l'utilisateur connecté
$user = $userManager->getCurrentUser();
$userPosts = $postManager->getUserPosts($user['id']);
$flashMessage = getFlashMessage();
$currentUser = $userManager->getCurrentUser();

// Gestion de la modification du profil
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $newUsername = Utils::sanitize($_POST['username'] ?? '');
    $newEmail = Utils::sanitize($_POST['email'] ?? '');
    $newAvatar = $_POST['avatar'] ?? '';
    $newBrawlStarsId = Utils::sanitize($_POST['brawl_stars_id'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Validation de l'ID Brawl Stars
    $brawlStarsIdError = '';
    if (!empty($newBrawlStarsId)) {
        require_once 'config/BrawlStarsAPI.php';
        if (!BrawlStarsAPI::isValidPlayerTag($newBrawlStarsId)) {
            $brawlStarsIdError = 'Format d\'ID Brawl Stars invalide. Utilisez le format #2PP ou 2PP avec des caractères alphanumériques (0-9, A-Z).';
        } else {
            // Formater l'ID correctement
            $newBrawlStarsId = BrawlStarsAPI::formatPlayerTag($newBrawlStarsId);
        }
    }
    
    // Vérification du token CSRF
    if (!Utils::verifyCSRFToken($csrf_token)) {
        $error = 'Token de sécurité invalide.';
    } elseif (!empty($brawlStarsIdError)) {
        $error = $brawlStarsIdError;
    } elseif (empty($newUsername) || empty($newEmail)) {
        $error = 'Le nom d\'utilisateur et l\'email sont obligatoires.';
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif (!empty($newPassword) && strlen($newPassword) < 6) {
        $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
    } elseif (!empty($newPassword) && $newPassword !== $confirmPassword) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (!empty($newPassword) && !$userManager->verifyCurrentPassword($user['id'], $currentPassword)) {
        $error = 'Le mot de passe actuel est incorrect.';
    } else {
        // Mettre à jour le profil en base de données
        $updateResult = $userManager->updateProfile(
            $user['id'], 
            $newUsername, 
            $newEmail, 
            $newAvatar, 
            !empty($newPassword) ? $newPassword : null,
            $newBrawlStarsId
        );
        
        if ($updateResult['success']) {
            $success = $updateResult['message'];
            // Recharger les données utilisateur
            $user = $userManager->getCurrentUser();
        } else {
            $error = $updateResult['message'];
        }
    }
}

// Gestion du filtrage des posts
$filter = $_GET['filter'] ?? 'all';
$filteredPosts = $userPosts;

if ($filter !== 'all') {
    $filteredPosts = array_filter($userPosts, function($post) use ($filter) {
        return $post['category'] === $filter;
    });
}

$csrf_token = Utils::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mon profil BrawlForum : informations du compte, mise à jour du profil, consultation de mes posts et activités.">
    <title>Brawl Forum - Mon Profil</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/profile.css">
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

    <div class="profile-container">
        <!-- En-tête du profil -->
        <div class="profile-header fade-in">
            <div class="profile-avatar">
                <img src="assets/img/<?= htmlspecialchars($user['avatar']) ?>.svg" alt="Avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
            </div>
            <h1 class="profile-username"><?= htmlspecialchars($user['username']) ?></h1>
            <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-number"><?= $user['posts_count'] ?></span>
                    <span class="stat-label">Posts</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= $user['likes_received'] ?></span>
                    <span class="stat-label">Likes</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= $user['level'] ?></span>
                    <span class="stat-label">Niveau</span>
                </div>
            </div>
        </div>
        
        <!-- Sections principales -->
        <div class="profile-sections">
            <!-- Section modification du profil -->
            <div class="edit-profile-section fade-in">
                <h2 class="section-title">
                    <i class="fas fa-edit"></i> Modifier mon profil
                </h2>
                
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
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="form-group">
                        <label for="username" class="form-label">
                            <i class="fas fa-user"></i> Nom d'utilisateur
                        </label>
                        <input type="text" id="username" name="username" class="form-input" 
                               value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Adresse email
                        </label>
                        <input type="email" id="email" name="email" class="form-input" 
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="brawl_stars_id" class="form-label">
                            <i class="fas fa-gamepad"></i> ID Brawl Stars
                        </label>
                        <input type="text" id="brawl_stars_id" name="brawl_stars_id" class="form-input" 
                               value="<?= htmlspecialchars($user['brawl_stars_id'] ?? '') ?>" 
                               placeholder="Ex: #2PP ou 2PP (optionnel)"
                               pattern="^#?[0-9A-Za-z]{3,9}$"
                               title="Format valide: #2PP ou 2PP (caractères autorisés: 0-9, A-Z)">
                        <small style="color: #ccc; font-size: 0.9rem; margin-top: 5px; display: block;">
                            <i class="fas fa-info-circle"></i> Votre tag de joueur Brawl Stars pour afficher vos statistiques
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-image"></i> Avatar
                        </label>
                        <div class="avatar-selection">
                            <label class="avatar-option">
                                <input type="radio" name="avatar" value="avatar1" <?= $user['avatar'] === 'avatar1' ? 'checked' : '' ?>>
                                <img src="assets/img/avatar1.svg" alt="Shelly" class="avatar-img">
                                <span class="avatar-name">Shelly</span>
                            </label>
                            <label class="avatar-option">
                                <input type="radio" name="avatar" value="avatar2" <?= $user['avatar'] === 'avatar2' ? 'checked' : '' ?>>
                                <img src="assets/img/avatar2.svg" alt="Colt" class="avatar-img">
                                <span class="avatar-name">Colt</span>
                            </label>
                            <label class="avatar-option">
                                <input type="radio" name="avatar" value="avatar3" <?= $user['avatar'] === 'avatar3' ? 'checked' : '' ?>>
                                <img src="assets/img/avatar3.svg" alt="Nita" class="avatar-img">
                                <span class="avatar-name">Nita</span>
                            </label>
                            <label class="avatar-option">
                                <input type="radio" name="avatar" value="avatar4" <?= $user['avatar'] === 'avatar4' ? 'checked' : '' ?>>
                                <img src="assets/img/avatar4.svg" alt="Bull" class="avatar-img">
                                <span class="avatar-name">Bull</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="current_password" class="form-label">
                            <i class="fas fa-lock"></i> Mot de passe actuel (optionnel)
                        </label>
                        <input type="password" id="current_password" name="current_password" class="form-input" 
                               placeholder="Laissez vide si vous ne voulez pas changer le mot de passe">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password" class="form-label">
                            <i class="fas fa-key"></i> Nouveau mot de passe
                        </label>
                        <input type="password" id="new_password" name="new_password" class="form-input" 
                               placeholder="Nouveau mot de passe (minimum 6 caractères)">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-check"></i> Confirmer le nouveau mot de passe
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" 
                               placeholder="Confirmez votre nouveau mot de passe">
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn-update">
                        <i class="fas fa-save"></i> Mettre à jour le profil
                    </button>
                </form>
            </div>
            
            <!-- Section posts -->
            <div class="posts-section fade-in">
                <h2 class="section-title">
                    <i class="fas fa-comments"></i> Mes Posts (<?= count($filteredPosts) ?>)
                </h2>
                
                <?php if (empty($filteredPosts)): ?>
                    <div class="no-posts">
                        <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                        Vous n'avez pas encore créé de posts.
                        <br><br>
                        <a href="add-post.php" style="color: #ffd700; text-decoration: none;">
                            <i class="fas fa-plus"></i> Créer mon premier post
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($filteredPosts as $post): ?>
                        <div class="post-item">
                            <div class="post-avatar">
                            <img src="assets/img/<?= htmlspecialchars($post['avatar']) ?>.svg" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        </div>
                            <div class="post-content">
                                <div class="post-title"><?= htmlspecialchars($post['title']) ?></div>
                                <div class="post-text"><?= htmlspecialchars(substr($post['content'], 0, 100)) ?>...</div>
                                <div class="post-time">
                                    <i class="fas fa-clock"></i> <?= date('d/m/Y à H:i', strtotime($post['created_at'])) ?>
                                </div>
                            </div>
                            <div class="post-category"><?= htmlspecialchars($post['category']) ?></div>
                        </div>
                    <?php endforeach; ?>
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

    <script src="assets/js/main.js"></script>
</body>
</html>