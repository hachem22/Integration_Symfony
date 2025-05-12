# CareConnect - Système de Gestion Hospitalière


## 📖 Description
**CareConnect** est une application web et desktop dédiée à la gestion complète des opérations hospitalières. Elle intègre des fonctionnalités avancées pour **5 acteurs** principaux : 
- **Patient**
- **Médecin**
- **Responsable**
- **Pharmacien**
- **Administrateur**

L'objectif est de simplifier la gestion des rendez-vous, des lits, des réclamations, des stocks pharmaceutiques, des événements, et bien plus, tout en assurant une expérience utilisateur fluide et sécurisée. 

---

## 📚 Table des Matières
- [Fonctionnalités Clés](#-fonctionnalités-clés)
- [Technologies Utilisées](#-technologies-utilisées)
- [Installation](#-installation)
- [Utilisation](#-utilisation)
- [API Intégrées](#-api-intégrées)
- [Contributions](#-contributions)
- [Licence](#-licence)

---

## 🚀 Fonctionnalités Clés

### 📅 Gestion des Rendez-vous
- **Planification intelligente** des rendez-vous avec calendrier interactif pour les médecins.
- **Notifications** par email/SMS (rappel 24h avant).
- **Génération automatique** de diagnostics et d'ordonnances via IA.
- Chatbot médical pour répondre aux symptômes et recommandations.
- Téléchargement de documents (PDF : diagnostic, ordonnance, certificat).

### 🔒 Gestion des Utilisateurs
- **Sécurité renforcée** : Reconnaissance faciale, reCAPTCHA, JWT.
- Blocage après 5 tentatives infructueuses + notification par email.
- Création de comptes employés avec mot de passe auto-généré (envoi par email).
- Réinitialisation de mot de passe via lien sécurisé.

### 🗨️ Gestion des Réclamations
- Création de réclamations par les utilisateurs (max 3/jour).
- **Traitement automatisé** via IA (filtrage de gros mots, suggestions de solutions).
- Notifications par email lors de la résolution.
- Système d'avis post-traitement.

### 🏥 Gestion des Chambres et Lits
- Visualisation 3D des chambres (lits disponibles/occupés).
- Plan hospitalier généré automatiquement (PDF téléchargeable).
- Notifications email/SMS lors de l'affectation d'un lit.
- Statistiques exportables en CSV.
- Chatbot pour recherche de lits disponibles.

### 💊 Gestion du Stock Pharmacie
- Alertes de stock critique + commandes automatisées.
- Paiement en ligne avec cachet électronique.
- Catalogue des médicaments avec notices et effets secondaires (IA).
- Génération de PDF pour les stocks en alerte.

### 📝 Gestion des Blogs & Événements
- Publications avec commentaires modérés par IA (traduction, filtrage).
- Événements géolocalisés + météo intégrée.
- QR code pour accéder aux détails d'un événement.
- Inscriptions limitées + formations générées par IA.

---

## 💻 Technologies Utilisées
- **Backend**: Symfony (PHP), MySQL.
- **Desktop**: JavaFX.
- **Sécurité**: JWT, reCAPTCHA, Reconnaissance faciale.
- **APIs**: Twilio (SMS), Mailjet (Email), RapidAPI (IA), Gemini (Chatbot).

---

💻 Technologies Utilisées
Composant	Web (Symfony)	Desktop (JavaFX)
Langage	PHP 8.2	Java 21
Interface	Twig + Bootstrap 5	FXML + CSS Animations
Base de données	MySQL 8	SQLite
Sécurité	JWT + reCAPTCHA v3	BCrypt + AES-256
Outils	Webpack Encore, Redis	Maven, SceneBuilder
📥 Installation
Prérequis
Web : PHP 8.2, Composer, Node.js 18+, XAMPP/WAMP

Desktop : JDK 20+, Maven 3.9+

🌐 Partie Web
1.Cloner le dépôt :
git clone https://github.com/hachem22/Integration_Symfony.git
cd Integration_Symfony
2.Installer les dépendances :

composer install && npm install
npm run build
3.Configurer la base de données :

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

4.Démarrer le serveur :
symfony serve:start

💻 Partie Desktop
1.Cloner le dépôt :
git clone https://github.com/haythemdr/PIDEV_JAVAFX.git
cd PIDEV_JAVAFX
2.Compiler avec Maven :
mvn clean install
3.Exécuter l'application :
java -jar target/careconnect-desktop.jar
🌐 API Intégrées
Service	Usage	Documentation
Twilio	Envoi de SMS	Twilio Docs
Gemini	Génération de diagnostics	Gemini API
JWT Auth	Gestion des sessions	JWT.io
architecture :
web :
Integration_Symfony/
├── assets/ # JS/CSS personnalisés
├── src/ # Logique métier Symfony
└── templates/ # Vues Twig
desktop:
PIDEV_JAVAFX/
├── src/main/java/ # Code source Java
└── resources/ # FXML + CSS







 
