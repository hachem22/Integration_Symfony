# CareConnect - Système de Gestion Hospitalière

<p align="center">
  <img src="https://asset.cloudinary.com/ddaouadrk/1e442a2178c9c8466b156f8fa0fc2135" alt="CareConnect Logo" />
  <br>
  <em>Une solution complète pour la gestion des opérations hospitalières</em>
</p>

## 📋 Table des Matières
- [Aperçu](#-aperçu)
- [Fonctionnalités](#-fonctionnalités)
  - [Gestion des Rendez-vous](#-gestion-des-rendez-vous)
  - [Gestion des Utilisateurs](#-gestion-des-utilisateurs)
  - [Gestion des Réclamations](#-gestion-des-réclamations)
  - [Gestion des Chambres et Lits](#-gestion-des-chambres-et-lits)
  - [Gestion du Stock Pharmacie](#-gestion-du-stock-pharmacie)
  - [Gestion des Blogs & Événements](#-gestion-des-blogs--événements)
- [Architecture](#-architecture)
  - [Technologies Utilisées](#-technologies-utilisées)
  - [Structure du Projet](#-structure-du-projet)
- [Installation](#-installation)
  - [Prérequis](#prérequis)
  - [Installation Web (Symfony)](#-installation-web-symfony)
  - [Installation Desktop (JavaFX)](#-installation-desktop-javafx)
- [API Intégrées](#-api-intégrées)
- [Captures d'écran](#-captures-décran)
- [Contact](#-contact)

## 🌟 Aperçu

**CareConnect** est une plateforme complète disponible en versions web et desktop, conçue pour optimiser la gestion des établissements de santé. Notre solution s'adresse à cinq acteurs clés du milieu hospitalier :

| Utilisateur | Rôle Principal |
|-------------|----------------|
| 👨‍⚕️ **Médecin** | Gestion des rendez-vous, diagnostics et ordonnances |
| 🧑‍⚕️ **Responsable** | Administration des ressources et personnel |
| 💊 **Pharmacien** | Gestion des stocks pharmaceutiques |
| 🏥 **Patient** | Prise de rendez-vous et suivi médical |
| 👩‍💼 **Administrateur** | Configuration système et supervision générale |

## 🚀 Fonctionnalités

### 📅 Gestion des Rendez-vous

- **Planification Intelligente**
  - Calendrier interactif pour les médecins
  - Algorithme d'optimisation des créneaux horaires
  - Vue hebdomadaire/mensuelle personnalisable
- **Système de Notifications**
  - Rappels automatiques 24h avant le rendez-vous (email/SMS)
  - Alertes en cas d'annulation ou de modification
- **Documentation Médicale**
  - Génération automatique de diagnostics et d'ordonnances via IA
  - Export PDF sécurisé des documents médicaux
  - Historique des consultations accessible aux patients
- **Assistance Virtuelle**
  - Chatbot médical pour l'analyse préliminaire des symptômes
  - Recommandations basées sur l'historique médical

### 🔒 Gestion des Utilisateurs

- **Sécurité Renforcée**
  - Authentification multi-facteurs
  - Reconnaissance faciale pour l'accès au système
  - Protection reCAPTCHA contre les attaques automatisées
  - Jetons JWT pour la gestion sécurisée des sessions
- **Administration des Comptes**
  - Blocage automatique après 5 tentatives infructueuses
  - Notification par email en cas de tentative suspecte
  - Création de comptes employés avec mot de passe auto-généré
  - Processus de réinitialisation sécurisé via lien temporaire

### 🗨️ Gestion des Réclamations

- **Système de Tickets**
  - Interface intuitive de création de réclamations (limite: 3/jour/utilisateur)
  - Catégorisation automatique par gravité et département concerné
- **Traitement Intelligent**
  - Filtrage automatique du contenu inapproprié
  - Suggestions de solutions basées sur des cas similaires (IA)
  - Routage automatique vers le service compétent
- **Suivi et Feedback**
  - Notifications par email à chaque étape du traitement
  - Système d'évaluation post-résolution
  - Statistiques de performance accessibles aux gestionnaires

### 🏥 Gestion des Chambres et Lits

- **Visualisation Avancée**
  - Interface 3D interactive des étages et chambres
  - Code couleur pour l'état d'occupation des lits
  - Plan hospitalier généré dynamiquement
- **Gestion des Ressources**
  - Système d'attribution automatique des lits selon critères médicaux
  - Notifications automatiques lors des affectations
  - Tableaux de bord avec métriques d'occupation
- **Outils d'Analyse**
  - Statistiques d'occupation exportables (CSV, Excel)
  - Prévisions de disponibilité basées sur les tendances historiques
  - Assistant virtuel pour la recherche rapide de disponibilités

### 💊 Gestion du Stock Pharmacie

- **Monitoring en Temps Réel**
  - Tableau de bord des niveaux de stock actuels
  - Alertes paramétrables pour les seuils critiques
  - Système de commande automatisée pour réapprovisionnement
- **Processus de Transaction**
  - Paiement en ligne sécurisé avec cachet électronique
  - Historique complet des transactions
  - Facturation automatique avec TVA différenciée
- **Base de Données Médicamenteuse**
  - Catalogue exhaustif avec notices et contre-indications
  - Analyse des interactions médicamenteuses (IA)
  - Système de traçabilité des lots et dates de péremption

### 📝 Gestion des Blogs & Événements

- **Plateforme de Publication**
  - Éditeur WYSIWYG pour création de contenu multimédia
  - Modération automatique des commentaires par IA
  - Traduction instantanée en plusieurs langues
- **Organisation d'Événements**
  - Création et promotion d'événements de santé publique
  - Intégration de la géolocalisation et données météo
  - Génération de QR codes pour l'accès aux détails
- **Gestion des Participations**
  - Système d'inscription avec limite de participants
  - Rappels automatiques et liste d'attente
  - Matériel de formation généré par IA

## 🏗 Architecture

### 💻 Technologies Utilisées

| Composant | Web (Symfony) | Desktop (JavaFX) |
|-----------|---------------|------------------|
| Langage | PHP 8.2 | Java 21 |
| Interface | Twig + Bootstrap 5 | FXML + CSS Animations |
| Base de données | MySQL 8 | SQLite |
| Sécurité | JWT + reCAPTCHA v3 | BCrypt + AES-256 |
| Outils | Webpack Encore, Redis | Maven, SceneBuilder |

### 📁 Structure du Projet

#### Version Web
```
Integration_Symfony/
├── assets/           # JS/CSS personnalisés
│   ├── js/
│   └── css/
├── config/           # Configuration Symfony
├── public/           # Fichiers accessibles publiquement
├── src/              # Code source PHP
│   ├── Controller/   # Contrôleurs de l'application
│   ├── Entity/       # Modèles de données
│   ├── Repository/   # Accès aux données
│   └── Service/      # Services métier
└── templates/        # Vues Twig
```

#### Version Desktop
```
PIDEV_JAVAFX/
├── src/
│   ├── main/
│   │   ├── java/
│   │   │   ├── controllers/  # Contrôleurs JavaFX
│   │   │   ├── models/       # Modèles de données
│   │   │   ├── services/     # Services métier
│   │   │   └── utils/        # Classes utilitaires
│   │   └── resources/
│   │       ├── fxml/         # Interfaces FXML
│   │       ├── css/          # Styles de l'application
│   │       └── images/       # Ressources graphiques
│   └── test/                 # Tests unitaires
└── pom.xml                   # Configuration Maven
```

## 📥 Installation

### Prérequis

- **Web** : 
  - PHP 8.2 ou supérieur
  - Composer
  - Node.js 18+
  - XAMPP/WAMP/MAMP
  
- **Desktop** : 
  - JDK 20+
  - Maven 3.9+

### 🌐 Installation Web (Symfony)

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/hachem22/Integration_Symfony.git
   cd Integration_Symfony
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Configurer l'environnement**
   ```bash
   # Copier le fichier d'environnement
   cp .env .env.local
   
   # Éditer .env.local avec vos paramètres de base de données
   ```

4. **Initialiser la base de données**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   php bin/console doctrine:fixtures:load  # Données de test (optionnel)
   ```

5. **Démarrer le serveur de développement**
   ```bash
   symfony serve:start
   ```
   L'application sera disponible à l'adresse: `http://localhost:8000`

### 💻 Installation Desktop (JavaFX)

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/haythemdr/PIDEV_JAVAFX.git
   cd PIDEV_JAVAFX
   ```

2. **Compiler l'application**
   ```bash
   mvn clean install
   ```

3. **Exécuter l'application**
   ```bash
   java -jar target/careconnect-desktop.jar
   ```
   
   Ou via Maven:
   ```bash
   mvn javafx:run
   ```

## 🌐 API Intégrées

| Service | Utilisation | Documentation |
|---------|-------------|---------------|
| Twilio | Envoi de SMS de notification | [Twilio Docs](https://www.twilio.com/docs) |
| Mailjet | Gestion des emails transactionnels | [Mailjet API](https://dev.mailjet.com/) |
| Google Gemini | Génération de diagnostics et contenus IA | [Gemini API](https://ai.google.dev/gemini-api) |
| JWT Auth | Sécurisation des sessions utilisateurs | [JWT.io](https://jwt.io/) |
| RapidAPI | Services d'IA et d'analyse | [RapidAPI](https://rapidapi.com/) |

## 📸 Captures d'écran

<p align="center">
  <img src="/api/placeholder/200/150" alt="Dashboard" />
  <img src="/api/placeholder/200/150" alt="Calendar" />
  <img src="/api/placeholder/200/150" alt="Pharmacy" />
</p>



## 📞 Contact

- **Email**: support@careconnect.com
- **Site Web**: [www.careconnect.com](https://www.careconnect.com)
- **GitHub**: [@hachem22](https://github.com/hachem22), [@haythemdr](https://github.com/haythemdr)

---

<p align="center">
  <em>Made with ❤️ by l'équipe CareConnect</em>
</p>
