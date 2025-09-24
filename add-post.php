<?php
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
    <title>Brawl Forum - Ajouter un post</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .add-post-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .add-post-panel {
            background: rgba(0,0,0,0.3);
            border-radius: 25px;
            padding: 40px;
            border: 4px solid #000;
            box-shadow: 0 15px 30px rgba(0,0,0,0.4);
        }
        
        .page-title {
            font-size: 3rem;
            color: #ffd700;
            text-align: center;
            margin-bottom: 40px;
            text-shadow: 3px 3px 0px #000;
        }
        
        .form-group {
            margin-bottom: 30px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            font-size: 1.3rem;
            font-weight: bold;
            color: #ffd700;
            text-transform: uppercase;
            text-shadow: 1px 1px 0px #000;
        }
        
        .form-input {
            width: 100%;
            padding: 20px;
            border: 4px solid #000;
            border-radius: 15px;
            font-size: 1.2rem;
            font-weight: bold;
            background: linear-gradient(135deg, #4a90e2, #357abd);
            color: white;
            transition: all 0.3s;
        }
        
        .form-input::placeholder {
            color: rgba(255,255,255,0.7);
            font-weight: normal;
        }
        
        .form-input:focus {
            outline: none;
            background: linear-gradient(135deg, #5ba0f2, #4080cd);
            box-shadow: 0 0 15px rgba(255,215,0,0.5);
        }
        
        .form-textarea {
            min-height: 150px;
            resize: vertical;
            font-family: Arial, sans-serif;
        }
        
        .category-select {
            position: relative;
        }
        
        .select-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .form-select {
            width: 100%;
            padding: 20px;
            border: 4px solid #000;
            border-radius: 15px;
            font-size: 1.2rem;
            font-weight: bold;
            background: linear-gradient(135deg, #4a90e2, #357abd);
            color: white;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
        
        .select-wrapper::after {
            content: '▼';
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #ffd700;
            font-size: 1.2rem;
            pointer-events: none;
        }
        
        .form-select option {
            background: #2a5298;
            color: white;
            padding: 10px;
        }
        
        .category-preview {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .category-badge {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: bold;
            border: 2px solid #000;
            cursor: pointer;
            transition: all 0.3s;
            opacity: 0.6;
        }
        
        .category-badge.strategies {
            background: linear-gradient(45deg, #8e44ad, #6a1b9a);
            color: white;
        }
        
        .category-badge.team {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
        }
        
        .category-badge.skins {
            background: linear-gradient(45deg, #f39c12, #e67e22);
            color: white;
        }
        
        .category-badge.events {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
        }
        
        .category-badge.selected {
            opacity: 1;
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .form-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
        }
        
        .btn-publish {
            background: linear-gradient(45deg, #ff8c00, #ff6b35);
            color: white;
            padding: 20px 40px;
            font-size: 1.5rem;
            border: 4px solid #000;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 900;
            text-transform: uppercase;
            transition: all 0.3s;
            box-shadow: 0 6px 0 #000;
        }
        
        .btn-publish:active {
            transform: translateY(3px);
            box-shadow: 0 3px 0 #000;
        }
        
        .btn-cancel {
            background: linear-gradient(45deg, #f44336, #d32f2f);
            color: white;
            padding: 20px 40px;
            font-size: 1.5rem;
            border: 4px solid #000;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 900;
            text-transform: uppercase;
            transition: all 0.3s;
            box-shadow: 0 6px 0 #000;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-cancel:active {
            transform: translateY(3px);
            box-shadow: 0 3px 0 #000;
        }
        
        .success-message {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
            border: 3px solid #000;
            font-weight: bold;
            text-align: center;
        }
        
        .error-messages {
            background: linear-gradient(45deg, #f44336, #d32f2f);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
            border: 3px solid #000;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .add-post-panel {
                padding: 20px;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .form-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-publish,
            .btn-cancel {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <nav class="nav-header">
        <div class="nav-links">
            <a href="index.php" class="nav-link">Accueil</a>
            <a href="posts.php" class="nav-link">Tous les posts</a>
            <a href="add-post.php" class="nav-link" style="color: #ffd700;">Ajouter un post</a>
        </div>
        
        <!-- Logo Central -->
        <div class="logo">
            <img src="assets/img/logo.png" alt="Brawl Forum Logo" class="logo-image">
        </div>
        
        <div class="nav-links">
            <a href="#" class="nav-link">Filtrer</a>
            <a href="profile.php" class="nav-link">Mon profil</a>
            <a href="logout.php" class="nav-link">Déconnexion</a>
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
</body>
</html>