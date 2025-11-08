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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="assets/img/favicon.png" type="image/png" />
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
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
        
        .profile-email {
            font-size: 1.2rem;
            color: #ccc;
            margin-bottom: 20px;
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
        
        .profile-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 768px) {
            .profile-sections {
                grid-template-columns: 1fr;
            }
        }
        
        .edit-profile-section, .posts-section {
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
        
        .avatar-selection {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        
        .avatar-option {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid #333;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .avatar-option:hover {
            border-color: #ffd700;
            transform: scale(1.1);
        }
        
        .avatar-option.selected {
            border-color: #ffd700;
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.5);
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
            background: linear-gradient(45deg, #ff6b35, #ff4444);
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
        
        .no-posts {
            text-align: center;
            color: #888;
            font-style: italic;
            padding: 40px;
        }
        
        .error-message {
            background: rgba(255, 0, 0, 0.1);
            border: 2px solid #ff4444;
            color: #ff6666;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success-message {
            background: rgba(0, 255, 0, 0.1);
            border: 2px solid #44ff44;
            color: #66ff66;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
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

    <script src="assets/js/main.js"></script>
</body>
</html>