<?php
require_once 'config/database.php';

$error = '';
$success = '';

// Rediriger si déjà connecté
if ($userManager->isLoggedIn()) {
    Utils::redirect('profile.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = Utils::sanitize($_POST['username'] ?? '');
    $email = Utils::sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Vérification du token CSRF
    if (!Utils::verifyCSRFToken($csrf_token)) {
        $error = 'Token de sécurité invalide.';
    } elseif (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Tous les champs sont obligatoires.';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        $result = $userManager->register($username, $email, $password);
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
    <title>Brawl Forum - Inscription</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="register-page">
    <div class="register-container fade-in">
        <!-- Logo Brawl Stars -->
        <div class="register-logo">
            <img src="assets/img/logo.png" alt="Brawl Forum Logo" class="logo-image">
        </div>
        
        <!-- Titre Inscription -->
        <h1 class="register-title brawl-title">INSCRIPTION</h1>
        
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
        
        <!-- Formulaire d'inscription -->
        <form method="POST" action="" class="register-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="form-group">
                <div class="input-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" 
                           name="username" 
                           class="form-input" 
                           placeholder="NOM D'UTILISATEUR" 
                           required
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <div class="input-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" 
                           name="email" 
                           class="form-input" 
                           placeholder="EMAIL" 
                           required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" 
                           name="password" 
                           class="form-input" 
                           placeholder="MOT DE PASSE" 
                           required
                           minlength="8">
                </div>
            </div>
            
            <div class="form-group">
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" 
                           name="confirm_password" 
                           class="form-input" 
                           placeholder="CONFIRMER LE MOT DE PASSE" 
                           required
                           minlength="8">
                </div>
            </div>
            
            <div class="form-group">
                <div class="input-icon">
                    <i class="fas fa-calendar"></i>
                    <input type="date" 
                           name="birthdate" 
                           class="form-input" 
                           required
                           max="<?= date('Y-m-d', strtotime('-13 years')) ?>">
                </div>
                <small class="form-help">Vous devez avoir au moins 13 ans</small>
            </div>
            
            <div class="form-group">
                <label class="avatar-label">CHOISISSEZ VOTRE AVATAR</label>
                <div class="avatar-selection">
                    <label class="avatar-option">
                        <input type="radio" name="avatar" value="avatar1" required>
                        <img src="assets/img/avatar1.svg" alt="Shelly" class="avatar-img">
                        <span class="avatar-name">Shelly</span>
                    </label>
                    <label class="avatar-option">
                        <input type="radio" name="avatar" value="avatar2">
                        <img src="assets/img/avatar2.svg" alt="Colt" class="avatar-img">
                        <span class="avatar-name">Colt</span>
                    </label>
                    <label class="avatar-option">
                        <input type="radio" name="avatar" value="avatar3">
                        <img src="assets/img/avatar3.svg" alt="Nita" class="avatar-img">
                        <span class="avatar-name">Nita</span>
                    </label>
                    <label class="avatar-option">
                        <input type="radio" name="avatar" value="avatar4">
                        <img src="assets/img/avatar4.svg" alt="Bull" class="avatar-img">
                        <span class="avatar-name">Bull</span>
                    </label>
                </div>
            </div>
            
            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="accept_terms" required>
                    <span class="checkmark"></span>
                    J'accepte les <a href="#" class="terms-link">conditions d'utilisation</a> et la <a href="#" class="privacy-link">politique de confidentialité</a>
                </label>
            </div>
            
            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="newsletter">
                    <span class="checkmark"></span>
                    Je souhaite recevoir les actualités du forum par email
                </label>
            </div>
            
            <button type="submit" class="btn register-btn brawl-title">
                REJOINDRE L'ARÈNE !
            </button>
        </form>
        
        <!-- Lien de connexion -->
        <div class="login-link">
            Déjà un compte ? <a href="login.php">Se connecter</a>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>