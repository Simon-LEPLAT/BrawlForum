<?php
// ===== CONFIGURATION BASE DE DONNÉES - BRAWL FORUM =====

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'brawl_forum');
define('DB_USER', 'root');
define('DB_PASS', '');
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
        // Simulation d'authentification (à remplacer par une vraie vérification)
        $validUsers = [
            'admin' => 'admin123',
            'player1' => 'password123',
            'brawler' => 'brawl2024',
            'gamer' => 'gaming123'
        ];
        
        if (isset($validUsers[$username]) && $validUsers[$username] === $password) {
            $_SESSION['user_id'] = uniqid();
            $_SESSION['username'] = $username;
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            return true;
        }
        
        return false;
    }
    
    // Inscription utilisateur
    public function register($username, $email, $password) {
        // Validation basique
        if (strlen($username) < 3) {
            return ['success' => false, 'message' => 'Le nom d\'utilisateur doit contenir au moins 3 caractères'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Adresse email invalide'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères'];
        }
        
        // Simulation d'inscription réussie
        $_SESSION['user_id'] = uniqid();
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        return ['success' => true, 'message' => 'Inscription réussie !'];
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
        
        // Simulation de création de post
        $postId = uniqid();
        
        // Sauvegarder dans la session pour simulation
        if (!isset($_SESSION['user_posts'])) {
            $_SESSION['user_posts'] = [];
        }
        
        $_SESSION['user_posts'][] = [
            'id' => $postId,
            'title' => $title,
            'content' => $content,
            'category' => $category,
            'author' => $_SESSION['username'],
            'created_at' => date('Y-m-d H:i:s'),
            'likes' => 0,
            'comments' => 0
        ];
        
        return ['success' => true, 'message' => 'Post créé avec succès !', 'post_id' => $postId];
    }
    
    // Obtenir les posts de l'utilisateur
    public function getUserPosts($userId) {
        return $_SESSION['user_posts'] ?? [];
    }
    
    // Obtenir les posts récents
    public function getRecentPosts($limit = 10) {
        // Données simulées pour les discussions récentes
        return [
            [
                'id' => 1,
                'title' => 'Meilleure stratégie pour Gem Grab',
                'author' => 'ProGamer',
                'category' => 'strategies',
                'replies' => 23,
                'last_activity' => '2 min',
                'avatar' => 'https://cdn.brawlstats.com/player-icons/28000001.png'
            ],
            [
                'id' => 2,
                'title' => 'Équipe parfaite pour Heist',
                'author' => 'BrawlMaster',
                'category' => 'team',
                'replies' => 15,
                'last_activity' => '5 min',
                'avatar' => 'https://cdn.brawlstats.com/player-icons/28000002.png'
            ],
            [
                'id' => 3,
                'title' => 'Nouveau skin Spike disponible !',
                'author' => 'SkinCollector',
                'category' => 'skins',
                'replies' => 42,
                'last_activity' => '10 min',
                'avatar' => 'https://cdn.brawlstats.com/player-icons/28000003.png'
            ],
            [
                'id' => 4,
                'title' => 'Event spécial ce week-end',
                'author' => 'EventHunter',
                'category' => 'events',
                'replies' => 8,
                'last_activity' => '15 min',
                'avatar' => 'https://cdn.brawlstats.com/player-icons/28000004.png'
            ],
            [
                'id' => 5,
                'title' => 'Guide complet pour débutants',
                'author' => 'Helper',
                'category' => 'strategies',
                'replies' => 67,
                'last_activity' => '20 min',
                'avatar' => 'https://cdn.brawlstats.com/player-icons/28000005.png'
            ]
        ];
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