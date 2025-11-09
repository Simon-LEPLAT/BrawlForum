<?php
require_once 'config/database.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

// Vérifier si l'utilisateur est admin
$currentUser = $userManager->getCurrentUser();
if (!isset($currentUser['role']) || $currentUser['role'] !== 'admin') {
    Utils::redirect('index.php');
}

$error = '';
$success = '';

// Gestion des actions d'administration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!Utils::verifyCSRFToken($csrf_token)) {
        $error = 'Token de sécurité invalide.';
    } else {
        switch ($action) {
            case 'delete_user':
                $userId = (int)($_POST['user_id'] ?? 0);
                if ($userId > 0 && $userId !== $currentUser['id']) {
                    if ($userManager->deleteUser($userId)) {
                        $success = 'Utilisateur supprimé avec succès.';
                    } else {
                        $error = 'Erreur lors de la suppression de l\'utilisateur.';
                    }
                } else {
                    $error = 'Impossible de supprimer cet utilisateur.';
                }
                break;
                
            case 'delete_post':
                $postId = (int)($_POST['post_id'] ?? 0);
                if ($postId > 0) {
                    if ($postManager->deletePost($postId)) {
                        $success = 'Post supprimé avec succès.';
                    } else {
                        $error = 'Erreur lors de la suppression du post.';
                    }
                }
                break;
        }
    }
}

// Récupérer les statistiques réelles de la base de données
$stats = [
    'total_users' => $userManager->getTotalUsers(),
    'total_posts' => $postManager->getTotalPosts(),
    'deleted_posts' => $postManager->getDeletedPosts()
];

// Récupérer tous les utilisateurs
$users = $userManager->getAllUsers();

// Récupérer tous les posts
$posts = $postManager->getAllPosts();

$csrf_token = Utils::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Administration BrawlForum : gestion des utilisateurs et des posts, statistiques globales et actions de modération.">
    <title>Brawl Forum - Administration</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="assets/img/favicon.png" type="image/png">
</head>
<body class="admin-page">
    <div class="admin-header">
        <div class="admin-nav">
            <a href="index.php" class="nav-item">
                <i class="fas fa-home"></i> Accueil
            </a>
            <a href="posts.php" class="nav-item">
                <i class="fas fa-comments"></i> Posts
            </a>
            <a href="profile.php" class="nav-item">
                <i class="fas fa-user"></i> Profil
            </a>
            <a href="admin.php" class="nav-item active">
                <i class="fas fa-cog"></i> Administration
            </a>
        </div>
        
        <h1 class="admin-title">ADMINISTRATION</h1>
        
        <div class="stats-overview">
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['total_users']) ?></div>
                <div class="stat-label">
                    <i class="fas fa-users"></i> Total comptes
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['total_posts']) ?></div>
                <div class="stat-label">
                    <i class="fas fa-comments"></i> Posts publiés
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['deleted_posts']) ?></div>
                <div class="stat-label">
                    <i class="fas fa-trash"></i> Posts supprimés
                </div>
            </div>
        </div>
    </div>

    <div class="admin-container">
        <!-- Messages d'alerte -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- Section Utilisateurs -->
        <div class="admin-section">
            <h2 class="section-title">
                <i class="fas fa-users"></i> Utilisateurs
            </h2>
            
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Pseudo</th>
                        <th>Email</th>
                        <th>Date d'inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <img src="https://cdn.brawlstats.com/player-icons/28000000.png" alt="Avatar" class="user-avatar">
                                <?= htmlspecialchars($user['username']) ?>
                            </td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <?php if ($user['username'] !== 'admin'): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="action-btn danger">
                                            <i class="fas fa-trash"></i> Supprimer le compte
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="action-btn info" disabled>
                                        <i class="fas fa-shield-alt"></i> Administrateur
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Section Posts -->
        <div class="admin-section">
            <h2 class="section-title">
                <i class="fas fa-comments"></i> Posts récents
            </h2>
            
            <table class="posts-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Catégorie</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($posts, 0, 10) as $post): ?>
                        <tr>
                            <td><?= htmlspecialchars($post['title']) ?></td>
                            <td><?= htmlspecialchars($post['author']) ?></td>
                            <td><?= htmlspecialchars($post['category']) ?></td>
                            <td><?= date('d.m.Y', strtotime($post['created_at'])) ?></td>
                            <td>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce post ?')">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                    <input type="hidden" name="action" value="delete_post">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <button type="submit" class="action-btn danger">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
</body>
</html>