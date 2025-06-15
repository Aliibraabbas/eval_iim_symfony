# eval_iim_symfony

# 🎓 Projet Symfony — Plateforme de gestion de produits

Ce projet a été réalisé dans le cadre de l'évaluation Symfony à l’IIM. Il s'agit d'une application de gestion de produits avec un système d’authentification, des rôles `admin` et `user`, un système de notifications, ainsi que l’utilisation de Messenger pour l’envoi de points en arrière-plan.

## 🚀 Fonctionnalités principales

### 👤 Utilisateur
- S'inscrire, se connecter, se déconnecter
- Modifier son **nom** et **prénom**
- Visualiser tous les produits
- Visualiser la fiche détaillée d’un produit
- Acheter un produit (si utilisateur actif et solde suffisant)
- Être notifié si désactivé ou solde insuffisant

### 🔐 Admin
- Créer, modifier, supprimer des produits
- Désactiver ou activer un utilisateur
- Voir les notifications :
  - Lorsqu’un produit est modifié
  - Lorsqu’un utilisateur effectue un achat
- Ajouter **1000 points** à tous les utilisateurs actifs via un bouton (dispatch Messenger)
- Accéder via des routes GET aux produits qu’il a créés

---

## 🔧 Technologies utilisées
- PHP 8.2+
- Symfony 7
- Doctrine ORM
- Symfony Security (authentification + rôles)
- Twig
- Messenger (mode synchrone)
- Bootstrap CSS (custom simple)
- MySQL

---

## 📦 Installation (locale)

```bash
# Cloner le projet
git clone https://github.com/votre-utilisateur/votre-projet.git
cd votre-projet

# Installer les dépendances
composer install

# Créer la base de données
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Lancer le serveur de développement
symfony server:start
