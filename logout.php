<?php
require_once 'config/database.php';

// Déconnexion de l'utilisateur
$userManager->logout();

// Message de confirmation
setFlashMessage('Vous avez été déconnecté avec succès.', 'success');

// Redirection vers la page d'accueil
Utils::redirect('index.php');
?>