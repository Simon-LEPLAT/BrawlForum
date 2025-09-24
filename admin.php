<?php
require_once 'config/database.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

// Vérifier si l'utilisateur est admin (pour l'instant, on vérifie si c'est l'utilisateur 'admin')
$currentUser = $userManager->getCurrentUser();
if ($currentUser['username'] !== 'admin') {
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

// Récupérer les statistiques
$stats = [
    'total_users' => 2314,
    'total_posts' => 6540,
    'deleted_posts' => 127
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
    <title>Brawl Forum - Administration</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-page {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .admin-header {
            background: rgba(0,0,0,0.3);
            border-radius: 20px;
            padding: 30px;
            margin: 0 auto 30px;
            max-width: 1200px;
            border: 3px solid #000;
            text-align: center;
        }
        
        .admin-nav {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .nav-item {
            background: #ff6b35;
            color: white;
            padding: 15px 25px;
            border-radius: 15px;
            text-decoration: none;
            font-weight: bold;
            border: 3px solid #000;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }
        
        .nav-item:hover {
            background: #ff8c42;
            transform: translateY(-2px);
        }
        
        .nav-item.active {
            background: #ffcc02;
            color: #000;
        }
        
        .admin-title {
            color: #ffcc02;
            font-size: 3rem;
            font-weight: bold;
            text-shadow: 3px 3px 0px #000;
            margin-bottom: 20px;
        }
        
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .stat-card {
            background: rgba(0,0,0,0.4);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            border: 3px solid #000;
            color: white;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #ffcc02;
            text-shadow: 2px 2px 0px #000;
        }
        
        .stat-label {
            font-size: 1rem;
            margin-top: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .admin-section {
            background: rgba(0,0,0,0.4);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 3px solid #000;
        }
        
        .section-title {
            color: #ffcc02;
            font-size: 2rem;
            font-weight: bold;
            text-shadow: 2px 2px 0px #000;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        
        .users-table, .posts-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .users-table th, .users-table td,
        .posts-table th, .posts-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #333;
            color: white;
        }
        
        .users-table th, .posts-table th {
            background: rgba(255, 204, 2, 0.2);
            color: #ffcc02;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid #ffcc02;
        }
        
        .action-btn {
            background: #ff6b35;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            border: 2px solid #000;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            background: #ff8c42;
            transform: translateY(-1px);
        }
        
        .action-btn.danger {
            background: #dc3545;
        }
        
        .action-btn.danger:hover {
            background: #c82333;
        }
        
        .action-btn.info {
            background: #17a2b8;
        }
        
        .action-btn.info:hover {
            background: #138496;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 2px solid #000;
            font-weight: bold;
        }
        
        .alert-success {
            background: #28a745;
            color: white;
        }
        
        .alert-error {
            background: #dc3545;
            color: white;
        }
        
        @media (max-width: 768px) {
            .admin-title {
                font-size: 2rem;
            }
            
            .users-table, .posts-table {
                font-size: 0.9rem;
            }
            
            .users-table th, .users-table td,
            .posts-table th, .posts-table td {
                padding: 10px 5px;
            }
        }
    </style>
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
</body>
</html>