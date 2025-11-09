<?php
require_once '../config.php';
header('Content-Type: application/json; charset=utf-8');

$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

try {
    // 🔹 On récupère les commentaires liés à un post
    // avec le nom du user correspondant à user_id
    $query = "
        SELECT c.id, c.content, c.created_at, c.user_id,
               u.username AS author
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = :post_id
        ORDER BY c.id ASC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();

    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'comments' => $comments
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données : ' . $e->getMessage()
    ]);
}
