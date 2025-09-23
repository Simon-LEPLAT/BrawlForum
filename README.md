# BrawlForum

Un forum communautaire dédié à Brawl Stars, développé en PHP avec une interface moderne et responsive.

## 🎮 Fonctionnalités

- **Système d'authentification** : Inscription et connexion sécurisées
- **Sélection d'avatars** : 4 personnages Brawl Stars (Shelly, Colt, Nita, Bull)
- **Interface responsive** : Design adaptatif pour tous les appareils
- **Catégories de discussion** : Stratégies, Équipe, Skins, Événements
- **Design thématique** : Interface inspirée de l'univers Brawl Stars

## 🚀 Installation

1. Clonez le repository :
```bash
git clone https://github.com/Simon-LEPLAT/forumBrawl.git
cd forumBrawl
```

2. Configurez votre base de données dans `config/database.php`

3. Lancez le serveur local :
```bash
php -S localhost:8000
```

4. Accédez à l'application : `http://localhost:8000`

## 📁 Structure du projet

```
BrawlForum/
├── assets/
│   ├── css/style.css      # Styles principaux
│   ├── img/               # Images et avatars
│   └── js/main.js         # Scripts JavaScript
├── config/
│   └── database.php       # Configuration BDD
├── index.php              # Page d'accueil
├── login.php              # Page de connexion
├── register.php           # Page d'inscription
└── profile.php            # Page de profil
```

## 🎨 Technologies utilisées

- **Backend** : PHP
- **Frontend** : HTML5, CSS3, JavaScript
- **Base de données** : MySQL/MariaDB
- **Design** : CSS Grid, Flexbox, Animations CSS

## 👥 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou soumettre une pull request.

## 📄 Licence

Ce projet est sous licence MIT.