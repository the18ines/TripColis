<?php
include 'config.php';
include 'auth.php';

redirigerSiNonConnecte();

if (obtenirRoleUtilisateur() !== 'transporteur') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

$userId = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'update_disponibilite':
        $disponible = $input['disponible'] ?? false;
        
        try {
            $sql = "UPDATE transporteurs SET disponibilite = ? WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$disponible, $userId]);
            
            echo json_encode(['success' => true]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
        
    case 'update_position':
        $latitude = $input['latitude'] ?? null;
        $longitude = $input['longitude'] ?? null;
        
        if ($latitude && $longitude) {
            // Ici, vous pourriez stocker la position dans la base de données
            // pour le suivi en temps réel
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Coordonnées invalides']);
        }
        break;
        
    case 'accepter_mission':
        $missionId = $input['mission_id'] ?? null;
        
        if ($missionId) {
            try {
                // Vérifier si la mission est toujours disponible
                $sql = "SELECT * FROM commandes WHERE id = ? AND status = 'en_attente' AND transporteur_id IS NULL";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$missionId]);
                $mission = $stmt->fetch();
                
                if ($mission) {
                    // Accepter la mission
                    $sql = "UPDATE commandes SET transporteur_id = ?, status = 'acceptee', date_acceptation = NOW() WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$userId, $missionId]);
                    
                    // Créer une notification pour le client
                    $sql = "INSERT INTO notifications (user_id, type, message) VALUES (?, 'mission_acceptee', ?)";
                    $stmt = $pdo->prepare($sql);
                    $message = "Votre commande #" . $mission['numero_commande'] . " a été acceptée par un transporteur";
                    $stmt->execute([$mission['client_id'], $message]);
                    
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Mission non disponible']);
                }
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        break;
        
    case 'demarrer_mission':
        $missionId = $input['mission_id'] ?? null;
        
        if ($missionId) {
            try {
                // Vérifier si la mission appartient au transporteur
                $sql = "SELECT * FROM commandes WHERE id = ? AND transporteur_id = ? AND status = 'acceptee'";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$missionId, $userId]);
                $mission = $stmt->fetch();
                
                if ($mission) {
                    // Démarrer la mission
                    $sql = "UPDATE commandes SET status = 'en_cours' WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$missionId]);
                    
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Mission non trouvée']);
                }
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        break;
        
    case 'terminer_mission':
        $missionId = $input['mission_id'] ?? null;
        
        if ($missionId) {
            try {
                // Vérifier si la mission appartient au transporteur
                $sql = "SELECT * FROM commandes WHERE id = ? AND transporteur_id = ? AND status = 'en_cours'";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$missionId, $userId]);
                $mission = $stmt->fetch();
                
                if ($mission) {
                    // Terminer la mission
                    $sql = "UPDATE commandes SET status = 'terminee', date_livraison = NOW() WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$missionId]);
                    
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Mission non trouvée']);
                }
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        break;
        
    case 'get_missions_acceptees':
        try {
            $sql = "SELECT c.* FROM commandes c 
                    WHERE c.transporteur_id = ? 
                    AND c.status = 'acceptee'
                    ORDER BY c.date_creation DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'missions' => $missions]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
}
?>