<?php
require_once 'config/database.php';

$error = '';
$success = '';

// Rediriger si déjà connecté
if ($userManager->isLoggedIn()) {
    Utils::redirect('index.php');
}

if (!isset($csrf_token)) {
    $csrf_token = bin2hex(random_bytes(32)); // génère un token sécurisé
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation des données
    if (empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        // Tentative de connexion avec UserManager
        if ($userManager->authenticate($username, $password)) {
            $success = 'Connexion réussie ! Bienvenue ' . $username;
            Utils::redirect('index.php');
        } else {
            $error = 'Nom d\'utilisateur ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Connexion à BrawlForum : accédez au forum Brawl Stars avec votre compte sécurisé pour participer aux discussions.">
    <title>Brawl Forum - Connexion</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="assets/img/favicon.png" type="image/png">
</head>
<body class="login-page">
    <div class="login-container fade-in">
        <!-- Logo Brawl Stars -->
        <div class="login-logo">
            <img src="assets/img/logo.png" alt="Brawl Forum Logo" class="logo-image">
        </div>
        
        <!-- Titre Connexion -->
        <h1 class="login-title brawl-title">CONNEXION</h1>
        
        <!-- Message d'erreur -->
         <?php if (!empty($error)): ?>
             <div class="error-message">
                 <i class="fas fa-exclamation-triangle"></i>
                 <?= htmlspecialchars($error) ?>
             </div>
         <?php endif; ?>
        
        <!-- Formulaire de connexion -->
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <div class="form-group">
                <input type="text" 
                       name="username" 
                       class="form-input" 
                       placeholder="NOM D'UTILISATEUR" 
                       required
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <div class="password-input">
                    <input type="password" 
                           name="password" 
                           id="password"
                           class="form-input" 
                           placeholder="MOT DE PASSE" 
                           required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-lock" id="password-icon"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn login-btn brawl-title">
                SE CONNECTER
            </button>
        </form>
        
        <!-- Lien d'inscription -->
        <div class="signup-link">
            Pas encore de compte ? <a href="register.php">S'inscrire</a>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>