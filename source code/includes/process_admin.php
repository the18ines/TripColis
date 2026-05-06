<?php
include 'config.php';
include 'auth.php';

redirigerSiNonConnecte();

if (obtenirRoleUtilisateur() !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'valider_inscription':
        $userId = $input['user_id'] ?? null;
        
        if ($userId) {
            try {
                // Valider l'utilisateur
                $sql = "UPDATE users SET status = 'actif' WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$userId]);
                
                // Si c'est un transporteur, générer un matricule s'il n'en a pas
                $sql = "SELECT role FROM users WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
                
                if ($user['role'] === 'transporteur') {
                    $sql = "SELECT * FROM transporteurs WHERE user_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$userId]);
                    $transporteur = $stmt->fetch();
                    
                    if (!$transporteur) {
                        $matricule = 'TRC' . str_pad($userId, 5, '0', STR_PAD_LEFT);
                        $sql = "INSERT INTO transporteurs (user_id, matricule) VALUES (?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$userId, $matricule]);
                    }
                }
                
                echo json_encode(['success' => true]);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        break;
        
    case 'rejeter_inscription':
        $userId = $input['user_id'] ?? null;
        
        if ($userId) {
            try {
                // Rejeter l'utilisateur
                $sql = "UPDATE users SET status = 'bloque' WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$userId]);
                
                echo json_encode(['success' => true]);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
}
?>