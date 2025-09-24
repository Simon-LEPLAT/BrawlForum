<?php
session_start();
require_once 'config/database.php';

echo "<h2>Debug Session Admin</h2>";

echo "<h3>Contenu de \$_SESSION:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

$currentUser = $userManager->getCurrentUser();

echo "<h3>Résultat de getCurrentUser():</h3>";
echo "<pre>";
print_r($currentUser);
echo "</pre>";

echo "<h3>Tests de vérification:</h3>";
echo "currentUser existe: " . (isset($currentUser) ? "✅ OUI" : "❌ NON") . "<br>";
echo "currentUser est un tableau: " . (is_array($currentUser) ? "✅ OUI" : "❌ NON") . "<br>";

if ($currentUser) {
    echo "currentUser['role'] existe: " . (isset($currentUser['role']) ? "✅ OUI" : "❌ NON") . "<br>";
    if (isset($currentUser['role'])) {
        echo "currentUser['role'] = '" . $currentUser['role'] . "'<br>";
        echo "currentUser['role'] === 'admin': " . ($currentUser['role'] === 'admin' ? "✅ OUI" : "❌ NON") . "<br>";
    }
}

echo "<h3>Test de condition navbar:</h3>";
if ($currentUser && isset($currentUser['role']) && $currentUser['role'] === 'admin') {
    echo "✅ Le lien Administration DEVRAIT apparaître dans la navbar";
} else {
    echo "❌ Le lien Administration NE DEVRAIT PAS apparaître dans la navbar";
}

echo "<br><br><a href='index.php'>Retour à l'accueil</a>";
?>