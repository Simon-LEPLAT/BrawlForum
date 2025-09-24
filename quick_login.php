<?php
require_once 'config/database.php';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === 'admin' && $password === 'admin123') {
        // Simuler une connexion admin réussie
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['email'] = 'admin@brawlforum.com';
        $_SESSION['role'] = 'admin';
        $_SESSION['login_time'] = time();
        
        echo "<h2>✅ Connexion admin réussie!</h2>";
        echo "<p><a href='index.php'>Retour à l'accueil</a></p>";
        echo "<p><a href='admin.php'>Aller à la page admin</a></p>";
        exit;
    } else {
        echo "<h2>❌ Identifiants incorrects</h2>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Connexion Admin - Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; padding: 20px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h2>Connexion Admin - Test</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Nom d'utilisateur (admin)" required>
        <input type="password" name="password" placeholder="Mot de passe (admin123)" required>
        <button type="submit">Se connecter</button>
    </form>
    
    <p><small>Utilisez: admin / admin123</small></p>
    <p><a href="index.php">Retour à l'accueil</a></p>
</body>
</html>