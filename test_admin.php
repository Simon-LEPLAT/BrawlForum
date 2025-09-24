<?php
require_once 'config/database.php';

// Test de connexion admin
echo "<h2>Test du système admin</h2>";

// Simuler une connexion admin
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['email'] = 'admin@brawlforum.com';
$_SESSION['role'] = 'admin';
$_SESSION['login_time'] = time();

$currentUser = $userManager->getCurrentUser();

echo "<h3>Utilisateur actuel:</h3>";
echo "<pre>";
print_r($currentUser);
echo "</pre>";

echo "<h3>Test de vérification admin:</h3>";
if ($currentUser && $currentUser['role'] === 'admin') {
    echo "✅ L'utilisateur est bien admin<br>";
    echo "✅ Le lien Administration devrait apparaître dans la navbar<br>";
} else {
    echo "❌ Problème avec le rôle admin<br>";
    echo "Rôle détecté: " . ($currentUser['role'] ?? 'non défini') . "<br>";
}

echo "<h3>Test d'accès à la page admin:</h3>";
if (!isset($currentUser['role']) || $currentUser['role'] !== 'admin') {
    echo "❌ Accès refusé à admin.php<br>";
} else {
    echo "✅ Accès autorisé à admin.php<br>";
}
?>