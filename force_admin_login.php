<?php
session_start();

// Forcer la connexion admin
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['email'] = 'admin@brawlforum.com';
$_SESSION['role'] = 'admin';
$_SESSION['login_time'] = time();

// Rediriger vers l'accueil
header('Location: index.php');
exit;
?>