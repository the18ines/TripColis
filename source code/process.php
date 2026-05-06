<?php
require_once 'config.php';

// Traitement des inscriptions client
if (isset($_POST['register_client'])) {
    $nom = sanitize($_POST['nom']);
    $prenom = sanitize($_POST['prenom']);
    $email = sanitize($_POST['email']);
    $telephone = sanitize($_POST['telephone']);
    $ville = sanitize($_POST['ville']);
    $quartier = sanitize($_POST['quartier']);
    $adresse = sanitize($_POST['adresse']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'client';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, telephone, ville, quartier, adresse, password, role) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $email, $telephone, $ville, $quartier, $adresse, $password, $role]);
        
        $_SESSION['success'] = "Compte créé avec succès !";
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['role'] = $role;
        
        redirect("dashboard_client.php");
    } catch(PDOException $e) {
        $_SESSION['error'] = "Erreur lors de l'inscription : " . $e->getMessage();
    }
}

// Traitement des inscriptions transporteur
if (isset($_POST['register_transporteur'])) {
    // Étape 1: Informations personnelles
    $nom = sanitize($_POST['nom']);
    $prenom = sanitize($_POST['prenom']);
    $email = sanitize($_POST['email']);
    $telephone = sanitize($_POST['telephone']);
    $quartier = sanitize($_POST['quartier']);
    $ville = sanitize($_POST['ville']);
    
    // Informations moto
    $marque_moto = sanitize($_POST['marque_moto']);
    $modele_moto = sanitize($_POST['modele_moto']);
    $immatriculation = sanitize($_POST['immatriculation']);
    $annee_moto = sanitize($_POST['annee_moto']);
    
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'transporteur';
    $status = 'pending'; // En attente de validation
    
    try {
        // Insertion de l'utilisateur
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, telephone, ville, quartier, password, role, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $email, $telephone, $ville, $quartier, $password, $role, $status]);
        
        $user_id = $pdo->lastInsertId();
        
        // Insertion des infos transporteur
        $stmt = $pdo->prepare("INSERT INTO transporteurs (user_id, marque_moto, modele_moto, immatriculation, annee_moto) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $marque_moto, $modele_moto, $immatriculation, $annee_moto]);
        
        // Gestion des documents (si uploadés)
        if (isset($_FILES['carte_grise']) && $_FILES['carte_grise']['error'] == 0) {
            // Upload du document
            $document_path = uploadDocument($_FILES['carte_grise'], $user_id);
            $stmt = $pdo->prepare("INSERT INTO documents (user_id, type_document, chemin_fichier) VALUES (?, 'carte_grise', ?)");
            $stmt->execute([$user_id, $document_path]);
        }
        
        $_SESSION['success'] = "Inscription transporteur en attente de validation !";
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = $role;
        
        redirect("dashboard_transporteur.php");
    } catch(PDOException $e) {
        $_SESSION['error'] = "Erreur lors de l'inscription : " . $e->getMessage();
    }
}

// Connexion utilisateur
if (isset($_POST['login'])) {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nom'] = $user['nom'];
            
            // Redirection selon le rôle
            switch ($user['role']) {
                case 'client':
                    redirect("dashboard_client.php");
                    break;
                case 'transporteur':
                    redirect("dashboard_transporteur.php");
                    break;
                case 'admin':
                    redirect("dashboard_admin.php");
                    break;
                default:
                    redirect("index.html");
            }
        } else {
            $_SESSION['error'] = "Email ou mot de passe incorrect";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Erreur de connexion : " . $e->getMessage();
    }
}

// Création d'une commande
if (isset($_POST['create_order'])) {
    $client_id = $_SESSION['user_id'];
    $agence = sanitize($_POST['agence']);
    $type_service = sanitize($_POST['type_service']);
    $adresse_livraison = sanitize($_POST['adresse_livraison']);
    $description_colis = sanitize($_POST['description_colis']);
    $poids = sanitize($_POST['poids']);
    $dimensions = sanitize($_POST['dimensions']);
    $fragile = isset($_POST['fragile']) ? 1 : 0;
    $urgence = isset($_POST['urgence']) ? 1 : 0;
    $montant = calculatePrice($poids, $distance); // Fonction à implémenter
    
    try {
        $stmt = $pdo->prepare("INSERT INTO commandes (client_id, agence, type_service, adresse_livraison, description_colis, poids, dimensions, fragile, urgence, montant, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$client_id, $agence, $type_service, $adresse_livraison, $description_colis, $poids, $dimensions, $fragile, $urgence, $montant]);
        
        $_SESSION['success'] = "Commande créée avec succès !";
        redirect("dashboard_client.php");
    } catch(PDOException $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
    }
}

// Fonction pour uploader les documents
function uploadDocument($file, $user_id) {
    $target_dir = "uploads/documents/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = "user_" . $user_id . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $target_file;
    }
    
    return null;
}

// Fonction pour calculer le prix
function calculatePrice($poids, $distance) {
    $prix_base = 1000;
    $prix_poids = $poids * 100; // 100 FCFA par kg
    $prix_distance = $distance * 200; // 200 FCFA par km
    
    return $prix_base + $prix_poids + $prix_distance;
}
?>