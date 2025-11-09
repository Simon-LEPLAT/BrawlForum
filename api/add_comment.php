<?php
require_once '../config.php';
header('Content-Type: application/json; charset=utf-8');

// On démarre la session pour accéder à l'ID utilisateur
session_start();

// Vérifie que l'utilisateur est bien connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Utilisateur non connecté'
    ]);
    exit;
}

// Récupération de l'ID de l'utilisateur depuis la session
$user_id = (int)$_SESSION['user_id'];

// Vérifie que la requête est bien une méthode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire envoyé en AJAX
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $content = trim($_POST['content'] ?? '');

    // Vérifications de base
    if ($post_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'ID du post invalide'
        ]);
        exit;
    }

    if (empty($content)) {
        echo json_encode([
            'success' => false,
            'message' => 'Le contenu du commentaire est vide'
        ]);
        exit;
    }

    try {
        // Préparation de la requête SQL d'insertion
        $stmt = $pdo->prepare("
            INSERT INTO comments (post_id, user_id, content, created_at)
            VALUES (:post_id, :user_id, :content, NOW())
        ");

        // Exécution de la requête avec les valeurs sécurisées
        $stmt->execute([
            ':post_id' => $post_id,
            ':user_id' => $user_id,
            ':content' => $content
        ]);

        // Réponse JSON en cas de succès
        echo json_encode([
            'success' => true,
            'message' => 'Commentaire ajouté avec succès'
        ]);
    } catch (PDOException $e) {
        // Si une erreur SQL survient, on la renvoie au client
        echo json_encode([
            'success' => false,
            'message' => 'Erreur base de données : ' . $e->getMessage()
        ]);
    }
} else {
    // Cas où la requête n’est pas une méthode POST
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}
