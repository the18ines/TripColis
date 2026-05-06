CREATE DATABASE IF NOT EXISTS web1_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE web1_db;

-- Table des utilisateurs
CREATE TABLE utilisateurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    ville VARCHAR(50),
    quartier VARCHAR(100),
    adresse TEXT,
    password VARCHAR(255) NOT NULL,
    role ENUM('client', 'transporteur', 'admin') DEFAULT 'client',
    status ENUM('active', 'pending', 'suspended') DEFAULT 'active',
    matricule VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Table des transporteurs (informations supplémentaires)
CREATE TABLE transporteurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    marque_moto VARCHAR(100),
    modele_moto VARCHAR(100),
    immatriculation VARCHAR(50),
    annee_moto YEAR,
    disponible BOOLEAN DEFAULT TRUE,
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_missions INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_disponible (disponible)
);

-- Table des documents
CREATE TABLE documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type_document ENUM('cni', 'permis', 'carte_grise', 'assurance', 'contrat', 'charte'),
    chemin_fichier VARCHAR(500),
    nom_fichier VARCHAR(255),
    taille_fichier INT,
    statut ENUM('pending', 'validated', 'rejected') DEFAULT 'pending',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    validated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, type_document)
);

-- Table des commandes
CREATE TABLE commandes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_commande VARCHAR(50) UNIQUE,
    client_id INT NOT NULL,
    transporteur_id INT NULL,
    agence VARCHAR(255) NOT NULL,
    type_service ENUM('retrait', 'envoi') DEFAULT 'retrait',
    adresse_livraison TEXT NOT NULL,
    description_colis TEXT,
    poids DECIMAL(5,2), -- en kg
    dimensions VARCHAR(50), -- LxHxP en cm
    fragile BOOLEAN DEFAULT FALSE,
    urgence BOOLEAN DEFAULT FALSE,
    valeur_estimee DECIMAL(10,2),
    distance_km DECIMAL(5,2),
    montant DECIMAL(10,2) NOT NULL,
    commission DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending', 'accepted', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    ticket_number VARCHAR(50) UNIQUE,
    payment_method ENUM('cash', 'om', 'momo', 'bank') DEFAULT 'cash',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (client_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (transporteur_id) REFERENCES utilisateurs(id),
    INDEX idx_client_status (client_id, status),
    INDEX idx_transporteur_status (transporteur_id, status),
    INDEX idx_status_created (status, created_at)
);

-- Table des notifications
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('order', 'payment', 'system', 'alert'),
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    related_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
);

-- Table des transactions
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    user_id INT NOT NULL,
    type ENUM('payment', 'commission', 'refund'),
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    payment_method ENUM('cash', 'om', 'momo', 'bank'),
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    reference VARCHAR(100),
    FOREIGN KEY (order_id) REFERENCES commandes(id),
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id),
    INDEX idx_user_date (user_id, transaction_date)
);

-- Table des paramètres de tarification
CREATE TABLE pricing_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    base_price DECIMAL(10,2) DEFAULT 1500.00,
    price_per_kg DECIMAL(10,2) DEFAULT 100.00,
    price_per_km DECIMAL(10,2) DEFAULT 200.00,
    fragile_surcharge DECIMAL(10,2) DEFAULT 500.00,
    urgent_surcharge DECIMAL(10,2) DEFAULT 1000.00,
    commission_rate DECIMAL(5,2) DEFAULT 20.00, -- 20%
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des agences partenaires
CREATE TABLE agences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    type ENUM('express_union', 'united_express', 'other'),
    ville VARCHAR(50),
    quartier VARCHAR(100),
    adresse TEXT,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    contact VARCHAR(20),
    horaires TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    INDEX idx_ville_type (ville, type)
);

-- Table des zones de service
CREATE TABLE zones_service (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ville VARCHAR(50) NOT NULL,
    quartier VARCHAR(100) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    UNIQUE KEY unique_ville_quartier (ville, quartier)
);

-- Table des litiges
CREATE TABLE litiges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    type ENUM('delay', 'damage', 'missing', 'other'),
    description TEXT NOT NULL,
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    resolution TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (order_id) REFERENCES commandes(id),
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id),
    INDEX idx_status (status)
);

-- Insertion des données initiales

-- Paramètres de tarification
INSERT INTO pricing_settings (base_price, price_per_kg, price_per_km, fragile_surcharge, urgent_surcharge, commission_rate) 
VALUES (1500.00, 100.00, 200.00, 500.00, 1000.00, 20.00);

-- Zones de service (Douala)
INSERT INTO zones_service (ville, quartier) VALUES
('Douala', 'Akwa'),
('Douala', 'Bonabéri'),
('Douala', 'Bépanda'),
('Douala', 'Makepe'),
('Douala', 'Deido'),
('Douala', 'New Bell'),
('Douala', 'Bonamoussadi'),
('Douala', 'Logpom'),
('Douala', 'Bali'),
('Douala', 'Ndogbong');

-- Zones de service (Yaoundé)
INSERT INTO zones_service (ville, quartier) VALUES
('Yaoundé', 'Bastos'),
('Yaoundé', 'Mvog-Ada'),
('Yaoundé', 'Mvog-Betsi'),
('Yaoundé', 'Ekounou'),
('Yaoundé', 'Nkolbisson'),
('Yaoundé', 'Mendong'),
('Yaoundé', 'Odza'),
('Yaoundé', 'Efoulan');

-- Agences partenaires
INSERT INTO agences (nom, type, ville, quartier, adresse, latitude, longitude) VALUES
('Express Union Akwa', 'express_union', 'Douala', 'Akwa', 'Carrefour Shell Akwa', 4.0460, 9.7040),
('United Express Bonabéri', 'united_express', 'Douala', 'Bonabéri', 'Entrée Pont Bonabéri', 4.0750, 9.6660),
('Agence Voyage Bépanda', 'other', 'Douala', 'Bépanda', 'Marché Bépanda', 4.0310, 9.7310),
('Express Union Deido', 'express_union', 'Douala', 'Deido', 'Carrefour Deido', 4.0610, 9.7180),
('Agence New-Bell', 'other', 'Douala', 'New Bell', 'Marché New-Bell', 4.0180, 9.6970),
('Express Union Bastos', 'express_union', 'Yaoundé', 'Bastos', 'Bastos, près de l''ambassade', 3.8680, 11.5120),
('United Express Centre', 'united_express', 'Yaoundé', 'Centre', 'Centre-ville Yaoundé', 3.8620, 11.5170),
('Agence Mvog-Ada', 'other', 'Yaoundé', 'Mvog-Ada', 'Mvog-Ada Marché', 3.8550, 11.5050);

-- Création d'un compte admin par défaut
INSERT INTO utilisateurs (nom, prenom, email, telephone, password, role) 
VALUES ('Admin', 'TripColis', 'admin@tripcolis.cm', '699054508', '$2y$10$YourHashedPasswordHere', 'admin');