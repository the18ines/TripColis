🚀 TripColis: parcel sending and collection platform

📌 Description du projet

Dans les zones urbaines du Cameroun, les mototaxis constituent un moyen de transport rapide et largement utilisé. Cependant, leur utilisation pour le transport de colis reste non structurée, sans plateforme numérique fiable ni garantie formelle.
TripColis est une plateforme web (et mobile-friendly) qui vise à structurer et sécuriser le transport de colis via mototaxis. Elle met en relation clients, transporteurs (mototaxis) et administrateurs, afin de garantir transparence, traçabilité et fiabilité des transactions.

🎯 Objectifs du projet
-Mettre en place une plateforme numérique de gestion des envois et retraits de colis.
-Automatiser la mise en relation entre clients et transporteurs disponibles.
-Fournir un espace administrateur pour la gestion des transporteurs et des transactions.
-Générer des tickets numériques sécurisés pour chaque opération.
-Gérer les contrats, paiements et commissions de manière électronique.
-Offrir un tableau de bord analytique pour le suivi des activités.

🧠 Analyse et conception du système

👥 Acteurs du système

🧑‍💼 Administrateur
Gestion globale de la plateforme
Validation des transporteurs
Gestion des contrats et documents
Suivi des transactions et statistiques

🏍️ Transporteur (Mototaxi)
Soumission de candidature (CNI, permis, assurance…)
Acceptation ou refus des demandes de transport
Négociation des prix
Validation des livraisons

👤 Client
Demande d’envoi ou de retrait de colis
Consultation des transporteurs disponibles
Paiement des services
Suivi de l’historique des transactions

🧩 Entités principales
Utilisateur
Client
Transporteur
Commande
Transaction
Agence
Contrat
Document
Notification


🚫 Contraintes non fonctionnelles

⚡ Performance : temps de réponse < 3 secondes
🔐 Sécurité : chiffrement des données sensibles
📱 Responsive : compatible mobile, tablette et desktop
⏱️ Disponibilité : > 95%
🧱 Évolutivité : architecture modulaire extensible


🛠️ Technologies utilisées

💻 Langages
PHP (backend)
HTML5 / CSS3 (frontend)
JavaScript (interactivité)

🧰 Environnement
XAMPP / WAMP (serveur local Apache/MySQL)
Windows / Linux
Visual Studio Code

🗄️ Base de données
MySQL

🎨 Conception & modélisation
UML (StarUML)
Figma (maquettes UI/UX)


🏗️ Architecture du système

Le système repose sur une architecture 3-tiers :

Présentation (Frontend) : interface utilisateur web responsive
Logique métier (Backend PHP) : traitement des demandes et règles métier
Base de données (MySQL) : stockage structuré des données


📦 Installation et exécution

1. Cloner le projet
git clone https://github.com/votre-utilisateur/tripcolis.git

2. Installer un serveur local
Installer XAMPP ou WAMP
Lancer Apache et MySQL

3. Importer la base de données
Ouvrir phpMyAdmin
Importer le fichier database.sql

4. Configurer la connexion BD
Modifier le fichier :
/config/db.php

5. Lancer le projet
Accéder via :
http://localhost/tripcolis


📄 Licence
Ce projet est développé dans un cadre académique. Toute utilisation commerciale nécessite une autorisation préalable.

👨‍💻 Auteur

Projet développé par Marissa Ines DJOMO WOGUEP
Étudiante en Ingénieur Informatique - IUC
