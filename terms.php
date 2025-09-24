<?php
session_start();
require_once 'config/database.php';

$currentUser = $userManager->getCurrentUser();
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conditions d'utilisation - Brawl Forum</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: url('assets/img/background-login.png') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
        }
        
        .terms-container {
            max-width: 900px;
            margin: 100px auto 50px;
            padding: 0 20px;
        }
        
        .terms-panel {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: 3px solid #ffd700;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .terms-panel::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #ffd700, #ffed4e, #ffd700);
            border-radius: 25px;
            z-index: -1;
        }
        
        .terms-title {
            font-size: 3rem;
            font-weight: bold;
            color: #ffffff;
            text-align: center;
            margin-bottom: 40px;
            text-shadow: 3px 3px 0px #000;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .terms-content {
            background: rgba(30, 60, 114, 0.9);
            border: 2px solid #4a90e2;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .terms-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
            color: #ffffff;
        }
        
        .terms-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
            font-size: 1.5rem;
        }
        
        .icon-star { background: linear-gradient(135deg, #ffd700, #ffed4e); color: #000; }
        .icon-shield { background: linear-gradient(135deg, #e74c3c, #c0392b); color: #fff; }
        .icon-lock { background: linear-gradient(135deg, #9b59b6, #8e44ad); color: #fff; }
        .icon-warning { background: linear-gradient(135deg, #f39c12, #e67e22); color: #fff; }
        
        .terms-text h3 {
            font-size: 1.4rem;
            font-weight: bold;
            color: #ffd700;
            margin-bottom: 8px;
        }
        
        .terms-text p {
            font-size: 1rem;
            line-height: 1.6;
            color: #ffffff;
            margin: 0;
        }
        
        .terms-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        
        .btn-accept, .btn-refuse {
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: bold;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-accept {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: #000;
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.4);
        }
        
        .btn-accept:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(243, 156, 18, 0.6);
        }
        
        .btn-refuse {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: #fff;
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }
        
        .btn-refuse:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.6);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-links">
            <a href="index.php" class="nav-link">Accueil</a>
            <a href="posts.php" class="nav-link">Tous les posts</a>
            <a href="add-post.php" class="nav-link">Ajouter un post</a>
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

    <div class="terms-container">
        <div class="terms-panel">
            <h1 class="terms-title">Conditions d'utilisation</h1>
            
            <div class="terms-content">
                <div class="terms-item">
                    <div class="terms-icon icon-star">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="terms-text">
                        <h3>1. Objet</h3>
                        <p>Les présentes conditions d'utilisation régissent l'utilisation du forum Brawl Forum.</p>
                    </div>
                </div>

                <div class="terms-item">
                    <div class="terms-icon icon-star">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="terms-text">
                        <h3>Comptes utilisateurs</h3>
                        <p>Vous devez créer un compte pour publier. Vous êtes responsable de la sécurité de votre compte.</p>
                    </div>
                </div>

                <div class="terms-item">
                    <div class="terms-icon icon-shield">
                        <i class="fas fa-times"></i>
                    </div>
                    <div class="terms-text">
                        <h3>Règles de conduite</h3>
                        <p>Vous vous engagez à ne pas publier de contenu offensant, illégal ou enfreignant les règles du forum.</p>
                    </div>
                </div>

                <div class="terms-item">
                    <div class="terms-icon icon-lock">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="terms-text">
                        <h3>Propriété intellectuelle</h3>
                        <p>Les contenus publiés restent la propriété de leurs auteurs, mais vous accordez au forum une licence d'utilisation.</p>
                    </div>
                </div>

                <div class="terms-item">
                    <div class="terms-icon icon-warning">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <div class="terms-text">
                        <h3>Sanctions</h3>
                        <p>En cas de violation des règles, votre compte peut être suspendu ou banni.</p>
                    </div>
                </div>
            </div>

            <div class="terms-buttons">
                <a href="index.php" class="btn-accept">J'accepte</a>
                <a href="index.php" class="btn-refuse">Refuser</a>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>