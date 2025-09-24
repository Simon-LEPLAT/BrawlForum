-- Base de données BrawlForum
-- Script d'initialisation pour phpMyAdmin

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS brawlforum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE brawlforum;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('user', 'admin') DEFAULT 'user',
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(20) DEFAULT 'avatar1',
    birthdate DATE NOT NULL,
    newsletter BOOLEAN DEFAULT FALSE,
    terms_accepted BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Table des catégories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50),
    color VARCHAR(7) DEFAULT '#007bff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des posts/sujets
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    views INT DEFAULT 0,
    likes INT DEFAULT 0,
    is_pinned BOOLEAN DEFAULT FALSE,
    is_locked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
);

-- Table des commentaires
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    parent_id INT NULL,
    likes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
    INDEX idx_post (post_id),
    INDEX idx_user (user_id),
    INDEX idx_parent (parent_id)
);

-- Table des sessions utilisateur
CREATE TABLE IF NOT EXISTS user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_expires (expires_at)
);

-- Insertion des catégories par défaut
INSERT INTO categories (name, slug, description, icon, color) VALUES
('Stratégies', 'strategies', 'Partagez vos stratégies et tactiques pour dominer l\'arène', 'fas fa-chess', '#9c27b0'),
('Équipe', 'team', 'Trouvez des coéquipiers et formez votre équipe de rêve', 'fas fa-users', '#f44336'),
('Skins', 'skins', 'Discutez des derniers skins et personnalisations', 'fas fa-palette', '#ff9800'),
('Événements', 'events', 'Informations sur les événements et tournois', 'fas fa-calendar-alt', '#2196f3');

-- Création d'un utilisateur admin par défaut (mot de passe: admin123)
INSERT INTO users (username, email, role, password, avatar, birthdate, newsletter, terms_accepted) VALUES
('admin', 'admin@brawlforum.com', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'avatar1', '1998-01-01', FALSE, TRUE);

-- Quelques posts d'exemple
INSERT INTO posts (title, content, user_id, category_id) VALUES
('Bienvenue sur BrawlForum !', 'Bienvenue dans notre communauté dédiée à Brawl Stars ! Ici vous pouvez partager vos stratégies, trouver des coéquipiers et discuter de tout ce qui concerne le jeu.', 1, 1),
('Meilleures stratégies pour Gem Grab', 'Voici quelques conseils pour exceller en Gem Grab : contrôlez le centre, protégez le porteur de gemmes, et coordonnez-vous avec votre équipe.', 1, 1),
('Recherche équipe compétitive', 'Je cherche des joueurs sérieux pour former une équipe compétitive. Niveau minimum : 20000 trophées.', 1, 2);