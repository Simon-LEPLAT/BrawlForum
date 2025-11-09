<?php
require_once '../config.php';
header('Content-Type: application/json; charset=utf-8');

// Récupère le dernier ID connu (pour le rafraîchissement)
$since_id = isset($_GET['since_id']) ? (int)$_GET['since_id'] : 0;

try {
    // 🔹 On récupère les posts avec le nom du user associé
    // On suppose que ta table "posts" contient "user_id"
    // et qu’il existe une table "users" avec au moins "id" et "username"
    $query = "
        SELECT p.id, p.title, p.content, p.created_at, p.user_id,
               u.username AS author
        FROM posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.id > :since_id
        ORDER BY p.id DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':since_id', $since_id, PDO::PARAM_INT);
    $stmt->execute();

    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'posts' => $posts
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données : ' . $e->getMessage()
    ]);
}
