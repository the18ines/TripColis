<?php
$mot_de_passe_clair = 'password'; // Choisissez un mot de passe fort
$mot_de_passe_hash = password_hash($mot_de_passe_clair, PASSWORD_DEFAULT);

echo "Mot de passe clair: " . $mot_de_passe_clair . "\n";
echo "Mot de passe hashé: " . $mot_de_passe_hash . "\n";

// Exemple d'insertion SQL
echo "\n--- Requête SQL à exécuter ---\n";
echo "INSERT INTO users (nom, prenom, email, telephone, password, role, status) VALUES ('Admin1', 'System', 'admin1@tripcolis.cm', '699054908', '" . $mot_de_passe_hash . "', 'admin1', 'actif');\n";
?>