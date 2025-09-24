<?php
// ===== CONFIGURATION BASE DE DONNÉES - BRAWL FORUM =====

// Fonction pour charger le fichier .env
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Ignorer les commentaires
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }
        if (!getenv($name)) {
            putenv(sprintf('%s=%s', $name, $value));
        }
    }
    return true;
}

// Charger le fichier .env
$envPath = dirname(__DIR__) . '/.env';
loadEnv($envPath);

// Configuration de la base de données depuis les variables d'environnement
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'brawlforum');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_CHARSET', 'utf8mb4');

// Configuration des sessions (seulement si aucune session n'est active)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 86400); // 24 heures
    ini_set('session.gc_maxlifetime', 86400);
    session_start();
}

// Classe de gestion de la base de données
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            // En cas d'erreur de connexion, on simule avec des données fictives
            $this->connection = null;
            error_log("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function isConnected() {
        return $this->connection !== null;
    }
}

// Classe de gestion des utilisateurs
class UserManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Authentification utilisateur
    public function authenticate($username, $password) {
        $conn = $this->db->getConnection();
        
        if (!$conn) {
            return false;
        }
        
        try {
            // Rechercher l'utilisateur par nom d'utilisateur ou email
            $stmt = $conn->prepare("SELECT id, username, email, password, avatar, role FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Connexion réussie - stocker les informations en session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['avatar'] = $user['avatar'];
                $_SESSION['role'] = $user['role'] ?? 'user';
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                
                // Mettre à jour la dernière connexion
                $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Erreur authentification: " . $e->getMessage());
            return false;
        }
    }
    
    // Inscription utilisateur
    public function register($username, $email, $password, $avatar = null) {
        $conn = $this->db->getConnection();
        
        if (!$conn) {
            return false;
        }
        
        try {
            // Vérifier si l'utilisateur ou l'email existe déjà
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                return false; // Utilisateur déjà existant
            }
            
            // Hasher le mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Utiliser l'avatar fourni ou un avatar par défaut
            $userAvatar = $avatar ?: Utils::getDefaultAvatar($username);
            
            // Insérer le nouvel utilisateur
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, avatar, created_at, is_active) VALUES (?, ?, ?, ?, NOW(), 1)");
            
            if ($stmt->execute([$username, $email, $hashedPassword, $userAvatar])) {
                $userId = $conn->lastInsertId();
                
                // Connecter automatiquement l'utilisateur
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['avatar'] = $userAvatar;
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Erreur inscription: " . $e->getMessage());
            return false;
        }
    }
    
    // Vérifier si l'utilisateur est connecté
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    // Obtenir les informations de l'utilisateur connecté
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'] ?? 'user@brawlforum.com',
            'avatar' => 'https://cdn.brawlstats.com/player-icons/28000000.png',
            'role' => $_SESSION['role'] ?? 'user',
            'join_date' => date('Y-m-d', $_SESSION['login_time'] ?? time()),
            'posts_count' => rand(10, 100),
            'likes_received' => rand(50, 500),
            'level' => rand(1, 50)
        ];
    }
    
    // Déconnexion
    public function logout() {
        session_destroy();
        return true;
    }
    
    // Récupérer tous les utilisateurs (pour l'administration)
    public function getAllUsers() {
        $conn = $this->db->getConnection();
        
        if (!$conn) {
            // Retourner des données fictives si pas de connexion
            return [
                ['id' => 1, 'username' => 'admin', 'email' => 'admin@brawlforum.com', 'created_at' => '2023-01-01 00:00:00'],
                ['id' => 2, 'username' => 'MaxPower', 'email' => 'maxpower@email.com', 'created_at' => '2023-02-12 00:00:00'],
                ['id' => 3, 'username' => 'StarPlayer', 'email' => 'starplayer@email.com', 'created_at' => '2022-10-02 00:00:00'],
                ['id' => 4, 'username' => 'Brawler33', 'email' => 'brawler33@email.com', 'created_at' => '2022-05-21 00:00:00'],
                ['id' => 5, 'username' => 'NinjaX', 'email' => 'ninjaX@email.com', 'created_at' => '2021-12-08 00:00:00']
            ];
        }
        
        try {
            $stmt = $conn->prepare("SELECT id, username, email, created_at FROM users WHERE is_active = 1 ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération utilisateurs: " . $e->getMessage());
            return [];
        }
    }
    
    // Supprimer un utilisateur (pour l'administration)
    public function deleteUser($userId) {
        $conn = $this->db->getConnection();
        
        if (!$conn) {
            return true; // Simulation de succès
        }
        
        try {
            // Supprimer complètement l'utilisateur de la base de données
            // Les contraintes ON DELETE CASCADE supprimeront automatiquement :
            // - Tous les posts de l'utilisateur
            // - Tous les commentaires de l'utilisateur
            // - Toutes les sessions de l'utilisateur
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND id != 1"); // Protéger l'admin principal
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Erreur suppression utilisateur: " . $e->getMessage());
            return false;
        }
    }
}

// Classe de gestion des posts
class PostManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Créer un nouveau post
    public function createPost($title, $content, $category, $userId) {
        // Validation
        if (strlen($title) < 5) {
            return ['success' => false, 'message' => 'Le titre doit contenir au moins 5 caractères'];
        }
        
        if (strlen($content) < 10) {
            return ['success' => false, 'message' => 'Le contenu doit contenir au moins 10 caractères'];
        }
        
        $validCategories = ['strategies', 'team', 'skins', 'events'];
        if (!in_array($category, $validCategories)) {
            return ['success' => false, 'message' => 'Catégorie invalide'];
        }
        
        $conn = $this->db->getConnection();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Erreur de connexion à la base de données'];
        }
        
        try {
            // Récupérer l'ID de la catégorie à partir de son slug
            $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
            $stmt->execute([$category]);
            $categoryData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$categoryData) {
                return ['success' => false, 'message' => 'Catégorie non trouvée'];
            }
            
            $categoryId = $categoryData['id'];
            
            // Insérer le nouveau post dans la base de données
            $stmt = $conn->prepare("INSERT INTO posts (title, content, user_id, category_id) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$title, $content, $userId, $categoryId]);
            
            if ($result) {
                $postId = $conn->lastInsertId();
                return ['success' => true, 'message' => 'Post créé avec succès !', 'post_id' => $postId];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la création du post'];
            }
            
        } catch (PDOException $e) {
            error_log("Erreur création post: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors de la création du post'];
        }
    }
    
    // Obtenir les posts de l'utilisateur
    public function getUserPosts($userId) {
        $conn = $this->db->getConnection();
        
        if (!$conn) {
            return [];
        }
        
        try {
            $stmt = $conn->prepare("
                SELECT p.*, c.slug as category_slug, c.name as category_name, u.username as author
                FROM posts p 
                JOIN categories c ON p.category_id = c.id 
                JOIN users u ON p.user_id = u.id 
                WHERE p.user_id = ? 
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$userId]);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formater les posts pour la compatibilité
            $formattedPosts = [];
            foreach ($posts as $post) {
                $formattedPosts[] = [
                    'id' => $post['id'],
                    'title' => $post['title'],
                    'content' => $post['content'],
                    'category' => $post['category_slug'],
                    'author' => $post['author'],
                    'created_at' => $post['created_at'],
                    'likes' => $post['likes'],
                    'comments' => 0 // TODO: compter les commentaires
                ];
            }
            
            return $formattedPosts;
            
        } catch (PDOException $e) {
            error_log("Erreur récupération posts utilisateur: " . $e->getMessage());
            return [];
        }
    }
    
    // Obtenir les posts récents
    public function getRecentPosts($limit = 10) {
        $conn = $this->db->getConnection();
        
        if (!$conn) {
            return [];
        }
        
        try {
            $stmt = $conn->prepare("
                SELECT p.*, c.slug as category_slug, c.name as category_name, u.username as author, u.avatar
                FROM posts p 
                JOIN categories c ON p.category_id = c.id 
                JOIN users u ON p.user_id = u.id 
                ORDER BY p.created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formater les posts pour la compatibilité
            $formattedPosts = [];
            foreach ($posts as $post) {
                // Calculer le temps écoulé
                $createdTime = new DateTime($post['created_at']);
                $now = new DateTime();
                $interval = $now->diff($createdTime);
                
                if ($interval->d > 0) {
                    $lastActivity = $interval->d . ' jour' . ($interval->d > 1 ? 's' : '');
                } elseif ($interval->h > 0) {
                    $lastActivity = $interval->h . ' heure' . ($interval->h > 1 ? 's' : '');
                } elseif ($interval->i > 0) {
                    $lastActivity = $interval->i . ' min';
                } else {
                    $lastActivity = 'À l\'instant';
                }
                
                $formattedPosts[] = [
                    'id' => $post['id'],
                    'title' => $post['title'],
                    'content' => $post['content'],
                    'author' => $post['author'],
                    'category' => $post['category_slug'],
                    'replies' => 0, // TODO: compter les commentaires
                    'last_activity' => $lastActivity,
                    'avatar' => 'assets/img/' . $post['avatar'] . '.svg',
                    'views' => $post['views'],
                    'likes' => $post['likes'],
                    'created_at' => $post['created_at'],
                    'comments_count' => 0 // TODO: compter les commentaires
                ];
            }
            
            return $formattedPosts;
            
        } catch (PDOException $e) {
            error_log("Erreur récupération posts récents: " . $e->getMessage());
            return [];
        }
    }
    
    // Obtenir tous les posts avec recherche optionnelle
    public function getAllPosts($search = '') {
        $conn = $this->db->getConnection();
        
        if (!$conn) {
            return [];
        }
        
        try {
            // Construire la requête avec recherche optionnelle
            $sql = "
                SELECT p.*, c.slug as category, c.name as category_name, u.username as author, u.avatar
                FROM posts p 
                JOIN categories c ON p.category_id = c.id 
                JOIN users u ON p.user_id = u.id 
            ";
            
            $params = [];
            
            if (!empty($search)) {
                $sql .= " WHERE (p.title LIKE ? OR p.content LIKE ? OR u.username LIKE ?)";
                $searchTerm = '%' . $search . '%';
                $params = [$searchTerm, $searchTerm, $searchTerm];
            }
            
            $sql .= " ORDER BY p.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formater les posts pour la compatibilité
            $formattedPosts = [];
            foreach ($posts as $post) {
                $formattedPosts[] = [
                    'id' => $post['id'],
                    'title' => $post['title'],
                    'content' => $post['content'],
                    'author' => $post['author'],
                    'category' => $post['category'],
                    'created_at' => $post['created_at'],
                    'comments_count' => 0, // TODO: compter les commentaires
                    'views' => $post['views'],
                    'likes' => $post['likes']
                ];
            }
            
            return $formattedPosts;
            
        } catch (PDOException $e) {
            error_log("Erreur récupération tous les posts: " . $e->getMessage());
            return [];
        }
    }
    
    // Obtenir les posts par catégorie
    public function getPostsByCategory($category, $search = '') {
        $allPosts = $this->getAllPosts($search);
        
        return array_filter($allPosts, function($post) use ($category) {
            return $post['category'] === $category;
        });
    }
    
    // Supprimer un post (pour l'administration)
    public function deletePost($postId) {
        $conn = $this->db->getConnection();
        
        if (!$conn) {
            return true; // Simulation de succès
        }
        
        try {
            // Supprimer le post et ses commentaires associés
            $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
            return $stmt->execute([$postId]);
        } catch (PDOException $e) {
            error_log("Erreur suppression post: " . $e->getMessage());
            return false;
        }
    }
}

// Classe utilitaire
class Utils {
    // Nettoyer les données d'entrée
    public static function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    // Générer un token CSRF
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    // Vérifier le token CSRF
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // Redirection sécurisée
    public static function redirect($url) {
        header("Location: $url");
        exit();
    }
    
    // Formater le temps écoulé
    public static function timeAgo($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'À l\'instant';
        if ($time < 3600) return floor($time/60) . ' min';
        if ($time < 86400) return floor($time/3600) . ' h';
        if ($time < 2592000) return floor($time/86400) . ' jour(s)';
        if ($time < 31536000) return floor($time/2592000) . ' mois';
        return floor($time/31536000) . ' an(s)';
    }
    
    // Obtenir l'avatar par défaut
    public static function getDefaultAvatar($username) {
        $avatars = [
            'https://cdn.brawlstats.com/player-icons/28000000.png',
            'https://cdn.brawlstats.com/player-icons/28000001.png',
            'https://cdn.brawlstats.com/player-icons/28000002.png',
            'https://cdn.brawlstats.com/player-icons/28000003.png',
            'https://cdn.brawlstats.com/player-icons/28000004.png'
        ];
        
        return $avatars[crc32($username) % count($avatars)];
    }
}

// Initialisation des gestionnaires
$userManager = new UserManager();
$postManager = new PostManager();

// Vérification de la connexion pour les pages protégées
function requireLogin() {
    global $userManager;
    
    // Éviter les boucles de redirection
    $currentPage = basename($_SERVER['PHP_SELF']);
    if ($currentPage === 'login.php' || $currentPage === 'register.php') {
        return;
    }
    
    if (!$userManager->isLoggedIn()) {
        Utils::redirect('login.php');
    }
}

// Fonction pour inclure le header
function includeHeader($title = 'Brawl Forum') {
    echo "<!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>$title</title>
        <link rel='stylesheet' href='assets/css/style.css'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    </head>
    <body>";
}

// Fonction pour inclure le footer
function includeFooter() {
    echo "<script src='assets/js/main.js'></script>
    </body>
    </html>";
}

// Messages flash
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = ['message' => $message, 'type' => $type];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

// Log des erreurs
function logError($message, $file = '', $line = '') {
    $logMessage = date('Y-m-d H:i:s') . " - $message";
    if ($file) $logMessage .= " in $file";
    if ($line) $logMessage .= " on line $line";
    error_log($logMessage . PHP_EOL, 3, 'logs/error.log');
}

// Créer le dossier logs s'il n'existe pas
if (!file_exists('logs')) {
    mkdir('logs', 0755, true);
}

?>