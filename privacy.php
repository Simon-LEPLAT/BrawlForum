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
    <title>Politique de confidentialité - Brawl Forum</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="assets/img/favicon.png" type="image/png" />
    <style>
        body {
            background: url('assets/img/background-login.png') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
        }
        
        .privacy-container {
            max-width: 900px;
            margin: 100px auto 50px;
            padding: 0 20px;
        }
        
        .privacy-panel {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: 3px solid #ffd700;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .privacy-panel::before {
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
        
        .privacy-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #ffffff;
            text-align: center;
            margin-bottom: 40px;
            text-shadow: 3px 3px 0px #000;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .privacy-content {
            background: rgba(30, 60, 114, 0.9);
            border: 2px solid #4a90e2;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .privacy-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
            color: #ffffff;
        }
        
        .privacy-icon {
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
        
        .icon-chart { background: linear-gradient(135deg, #3498db, #2980b9); color: #fff; }
        .icon-gear { background: linear-gradient(135deg, #95a5a6, #7f8c8d); color: #fff; }
        .icon-lock { background: linear-gradient(135deg, #f39c12, #e67e22); color: #fff; }
        .icon-shield { background: linear-gradient(135deg, #27ae60, #229954); color: #fff; }
        
        .privacy-text h3 {
            font-size: 1.4rem;
            font-weight: bold;
            color: #ffd700;
            margin-bottom: 8px;
        }
        
        .privacy-text p {
            font-size: 1rem;
            line-height: 1.6;
            color: #ffffff;
            margin: 0;
        }
        
        .privacy-footer {
            background: rgba(30, 60, 114, 0.9);
            border: 2px solid #4a90e2;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            color: #ffffff;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .back-button {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn-back {
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
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: #fff;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.6);
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

    <div class="privacy-container">
        <div class="privacy-panel">
            <h1 class="privacy-title">Politique de confidentialité</h1>
            
            <div class="privacy-content">
                <div class="privacy-item">
                    <div class="privacy-icon icon-chart">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="privacy-text">
                        <h3>Données collectées</h3>
                        <p>Nous collectons des données sur votre nom d'utilisateur, votre adresse e-mail et des informations sur votre activité sur notre forum.</p>
                    </div>
                </div>

                <div class="privacy-item">
                    <div class="privacy-icon icon-gear">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="privacy-text">
                        <h3>Utilisation des données</h3>
                        <p>Nous utilisons vos données pour fournir, améliorer et protéger notre forum ainsi que pour communiquer avec vous.</p>
                    </div>
                </div>

                <div class="privacy-item">
                    <div class="privacy-icon icon-lock">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="privacy-text">
                        <h3>Partage et sécurité</h3>
                        <p>Nous ne partageons pas vos données avec des tiers sans votre consentement.</p>
                    </div>
                </div>

                <div class="privacy-item">
                    <div class="privacy-icon icon-shield">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="privacy-text">
                        <h3>Droits des utilisateurs</h3>
                        <p>Vous pouvez demander l'accès, la rectification ou la suppression de vos données personnelles à tout moment.</p>
                    </div>
                </div>
            </div>

            <div class="privacy-footer">
                <p>Nous nous conformons au RGPD et autres lois applicables en matière de protection des données.</p>
            </div>

            <div class="back-button">
                <a href="index.php" class="btn-back">Retour à l'accueil</a>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>