<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit();
}

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $avatar = $_POST['avatar'] ?? 'avatar1';
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    $terms = isset($_POST['terms']) && $_POST['terms'] === '1' ? 1 : 0;
    
    // Calculer l'âge à partir de la date de naissance
    $age = 0;
    if (!empty($birthdate)) {
        $birth = new DateTime($birthdate);
        $today = new DateTime();
        $age = $today->diff($birth)->y;
    }
    
    // Validation des données
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($birthdate)) {
        $error = 'Tous les champs obligatoires doivent être remplis.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $error = 'Le nom d\'utilisateur doit contenir entre 3 et 50 caractères.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'L\'adresse email n\'est pas valide.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif ($age < 13 || $age > 120) {
        $error = 'L\'âge doit être compris entre 13 et 120 ans.';
    } elseif ($terms != 1) {
        $error = 'Vous devez accepter les conditions d\'utilisation.';
    } else {
        // Connexion à la base de données
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        if ($conn) {
            try {
                // Vérifier si l'utilisateur ou l'email existe déjà
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                
                if ($stmt->rowCount() > 0) {
                    $error = 'Ce nom d\'utilisateur ou cette adresse email est déjà utilisé.';
                } else {
                    // Hasher le mot de passe
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insérer le nouvel utilisateur
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password, avatar, birthdate, newsletter, terms_accepted) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    
                    if ($stmt->execute([$username, $email, $hashed_password, $avatar, $birthdate, $newsletter, $terms])) {
                        $user_id = $conn->lastInsertId();
                        
                        // Connecter automatiquement l'utilisateur
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['username'] = $username;
                        $_SESSION['avatar'] = $avatar;
                        
                        $success = 'Inscription réussie ! Bienvenue sur BrawlForum !';
                        header('Location: index.php');
                        exit();
                    } else {
                        $error = 'Erreur lors de l\'inscription. Veuillez réessayer.';
                    }
                }
            } catch (PDOException $e) {
                $error = 'Erreur de base de données. Veuillez réessayer plus tard.';
                error_log("Erreur inscription: " . $e->getMessage());
            }
        } else {
            $error = 'Impossible de se connecter à la base de données.';
        }
    }
}
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
                    <input type="checkbox" name="terms" value="1" required>
                    <span class="checkmark"></span>
                    J'accepte les <a href="terms.php" class="terms-link" target="_blank"> conditions d'utilisation </a> et la <a href="privacy.php" class="privacy-link" target="_blank">politique de confidentialité</a>
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