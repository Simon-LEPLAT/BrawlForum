<?php
// Script de test pour vérifier la configuration de la base de données

require_once 'config/database.php';

echo "=== TEST DE CONFIGURATION DE LA BASE DE DONNÉES ===\n\n";

// Vérifier si le fichier .env existe
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "✅ Fichier .env trouvé\n";
} else {
    echo "❌ Fichier .env non trouvé\n";
    exit(1);
}

// Afficher la configuration chargée
echo "\n--- Configuration chargée ---\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_PASS: " . (empty(DB_PASS) ? "(vide)" : "***") . "\n";
echo "DB_CHARSET: " . DB_CHARSET . "\n";

// Tester la connexion à la base de données
echo "\n--- Test de connexion ---\n";
try {
    $db = Database::getInstance();
    
    if ($db->isConnected()) {
        echo "✅ Connexion à la base de données réussie!\n";
        
        // Tester une requête simple
        $conn = $db->getConnection();
        if ($conn !== null) {
            $stmt = $conn->query("SELECT 1 as test");
            $result = $stmt->fetch();
            
            if ($result && $result['test'] == 1) {
                echo "✅ Test de requête réussi!\n";
            } else {
                echo "❌ Échec du test de requête\n";
            }
            
            // Vérifier si les tables existent
            echo "\n--- Vérification des tables ---\n";
            $tables = ['users', 'posts', 'comments'];
            foreach ($tables as $table) {
                try {
                    $stmt = $conn->query("SHOW TABLES LIKE '$table'");
                    if ($stmt->rowCount() > 0) {
                        echo "✅ Table '$table' existe\n";
                    } else {
                        echo "⚠️  Table '$table' n'existe pas\n";
                    }
                } catch (Exception $e) {
                    echo "❌ Erreur lors de la vérification de la table '$table': " . $e->getMessage() . "\n";
                }
            }
        } else {
            echo "❌ Connexion null, impossible de tester les requêtes\n";
        }
        
    } else {
        echo "❌ Échec de la connexion à la base de données\n";
        echo "Vérifiez que MySQL est démarré et que la base de données 'brawlforum' existe.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DU TEST ===\n";
?>