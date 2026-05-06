<?php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et est un client
$user = getCurrentUser();
if (!$user || $user['role'] !== 'client') {
    header('Location: auth.php');
    exit();
}

// Récupérer les commandes du client
$stmt = $pdo->prepare("SELECT * FROM commandes WHERE client_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques
$stats = [
    'en_cours' => 0,
    'terminees' => 0,
    'en_attente' => 0,
    'annulees' => 0
];

foreach ($commandes as $commande) {
    switch ($commande['status']) {
        case 'pending': $stats['en_attente']++; break;
        case 'accepted': $stats['en_cours']++; break;
        case 'in_progress': $stats['en_cours']++; break;
        case 'completed': $stats['terminees']++; break;
        case 'cancelled': $stats['annulees']++; break;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Client - TripColis</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    
    <style>
        :root {
            --primary-blue: #1e3a5f;
            --secondary-blue: #2c5282;
            --accent-orange: #ed8936;
            --light-orange: #fbd38d;
            --sidebar-width: 260px;
        }
        
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
            background-color: #f7fafc;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-blue) 0%, #152642 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .user-profile {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
            margin: 0;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        
        .sidebar-menu a:hover, 
        .sidebar-menu a.active {
            background-color: rgba(255,255,255,0.08);
            color: white;
            border-left-color: var(--accent-orange);
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
        }
        
        .top-navbar {
            background-color: white;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        .stats-card {
            border-top: 4px solid var(--accent-orange);
        }
        
        .card-icon {
            width: 55px;
            height: 55px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 18px;
        }
        
        .icon-orange {
            background-color: rgba(237, 137, 54, 0.1);
            color: var(--accent-orange);
        }
        
        .icon-success {
            background-color: rgba(56, 161, 105, 0.1);
            color: #38a169;
        }
        
        .icon-warning {
            background-color: rgba(214, 158, 46, 0.1);
            color: #d69e2e;
        }
        
        .icon-danger {
            background-color: rgba(229, 62, 62, 0.1);
            color: #e53e3e;
        }
        
        .btn-custom-orange {
            background: linear-gradient(to right, var(--accent-orange), #f6ad55);
            color: white;
            border: none;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
        }
        
        .btn-custom-orange:hover {
            background: linear-gradient(to right, #dd6b20, #ed8936);
            color: white;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h4 class="mb-0">
                    <span style="color: var(--accent-orange);">TRIP</span>COLIS
                </h4>
                <small>Tableau de bord client</small>
            </div>
            
            <div class="user-profile">
                <div class="user-avatar mb-3">
                    <i class="fas fa-user-circle fa-3x"></i>
                </div>
                <h6 class="mb-1"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h6>
                <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                <div class="badge bg-warning mt-2">Client</div>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard_client.php" class="active">
                        <i class="fas fa-tachometer-alt me-3"></i>
                        <span>Tableau de bord</span>
                    </a>
                </li>
                <li>
                    <a href="new_order.php">
                        <i class="fas fa-plus-circle me-3"></i>
                        <span>Nouvelle commande</span>
                    </a>
                </li>
                <li>
                    <a href="orders.php">
                        <i class="fas fa-shipping-fast me-3"></i>
                        <span>Mes commandes</span>
                        <?php if($stats['en_attente'] > 0): ?>
                        <span class="badge bg-danger ms-auto"><?php echo $stats['en_attente']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="history.php">
                        <i class="fas fa-history me-3"></i>
                        <span>Historique</span>
                    </a>
                </li>
                <li>
                    <a href="profile.php">
                        <i class="fas fa-user-cog me-3"></i>
                        <span>Mon profil</span>
                    </a>
                </li>
                <li>
                    <a href="charte.php">
                        <i class="fas fa-file-contract me-3"></i>
                        <span>Charte TripColis</span>
                    </a>
                </li>
                <li>
                    <a href="support.php">
                        <i class="fas fa-headset me-3"></i>
                        <span>Support</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php" class="text-danger">
                        <i class="fas fa-sign-out-alt me-3"></i>
                        <span>Déconnexion</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <div class="top-navbar">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Bonjour, <?php echo htmlspecialchars($user['prenom']); ?> 👋</h4>
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-bell"></i>
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-custom-orange dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-2"></i>
                                <?php echo htmlspecialchars($user['prenom']); ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Mon profil</a>
                                <a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Paramètres</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="dashboard-card stats-card">
                        <div class="card-icon icon-orange">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 class="fw-bold"><?php echo $stats['en_attente']; ?></h3>
                        <p class="text-muted mb-0">En attente</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="dashboard-card stats-card">
                        <div class="card-icon icon-orange">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h3 class="fw-bold"><?php echo $stats['en_cours']; ?></h3>
                        <p class="text-muted mb-0">En cours</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="dashboard-card stats-card">
                        <div class="card-icon icon-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="fw-bold"><?php echo $stats['terminees']; ?></h3>
                        <p class="text-muted mb-0">Terminées</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="dashboard-card stats-card">
                        <div class="card-icon icon-danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h3 class="fw-bold"><?php echo $stats['annulees']; ?></h3>
                        <p class="text-muted mb-0">Annulées</p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="dashboard-card">
                        <h5 class="mb-4">Actions rapides</h5>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="new_order.php" class="btn btn-custom-orange w-100 py-3 d-flex flex-column align-items-center">
                                    <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                    <span>Nouveau retrait</span>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="new_order.php?type=send" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center">
                                    <i class="fas fa-paper-plane fa-2x mb-2"></i>
                                    <span>Envoyer un colis</span>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="orders.php" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center">
                                    <i class="fas fa-list-alt fa-2x mb-2"></i>
                                    <span>Mes commandes</span>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="charte.php" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center">
                                    <i class="fas fa-file-contract fa-2x mb-2"></i>
                                    <span>Charte TripColis</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Dernières commandes -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="dashboard-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5>Dernières commandes</h5>
                            <a href="orders.php" class="btn btn-sm btn-primary">Voir toutes</a>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>N° Commande</th>
                                        <th>Date</th>
                                        <th>Agence</th>
                                        <th>Statut</th>
                                        <th>Montant</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($commandes)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="fas fa-box-open fa-2x text-muted mb-3"></i>
                                                <p class="text-muted">Aucune commande pour le moment</p>
                                                <a href="new_order.php" class="btn btn-custom-orange">Créer votre première commande</a>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php 
                                        $count = 0;
                                        foreach($commandes as $commande): 
                                            if($count >= 5) break;
                                            $count++;
                                        ?>
                                        <tr>
                                            <td>
                                                <strong>TC-<?php echo str_pad($commande['id'], 5, '0', STR_PAD_LEFT); ?></strong>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($commande['created_at'])); ?></td>
                                            <td><?php echo htmlspecialchars($commande['agence']); ?></td>
                                            <td>
                                                <?php 
                                                $status_class = 'warning';
                                                $status_text = 'En attente';
                                                switch($commande['status']) {
                                                    case 'pending': $status_class = 'warning'; $status_text = 'En attente'; break;
                                                    case 'accepted': $status_class = 'info'; $status_text = 'Acceptée'; break;
                                                    case 'in_progress': $status_class = 'primary'; $status_text = 'En cours'; break;
                                                    case 'completed': $status_class = 'success'; $status_text = 'Terminée'; break;
                                                    case 'cancelled': $status_class = 'danger'; $status_text = 'Annulée'; break;
                                                }
                                                ?>
                                                <span class="badge bg-<?php echo $status_class; ?>">
                                                    <?php echo $status_text; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?php echo number_format($commande['montant'], 0, ',', ' '); ?> FCFA</strong>
                                            </td>
                                            <td>
                                                <a href="order_details.php?id=<?php echo $commande['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Informations rapides -->
                <div class="col-lg-4">
                    <div class="dashboard-card">
                        <h5 class="mb-4">Informations importantes</h5>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Service disponible</strong>
                            <p class="mb-0 small">Lun-Dim: 7h-20h</p>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Documents à préparer</strong>
                            <p class="mb-0 small">Préparez votre CNI pour le retrait</p>
                        </div>
                        
                        <div class="alert alert-success">
                            <i class="fas fa-bolt me-2"></i>
                            <strong>Livraison express</strong>
                            <p class="mb-0 small">30 min en moyenne</p>
                        </div>
                        
                        <div class="mt-4">
                            <h6>Support client</h6>
                            <p class="small text-muted mb-2">
                                <i class="fas fa-phone me-2"></i>+237 699 05 45 08
                            </p>
                            <p class="small text-muted mb-0">
                                <i class="fas fa-envelope me-2"></i>support@tripcolis.cm
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script pour le tableau de bord
        document.addEventListener('DOMContentLoaded', function() {
            // Notification toast
            <?php if(isset($_SESSION['success'])): ?>
                showToast('<?php echo $_SESSION['success']; ?>', 'success');
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error'])): ?>
                showToast('<?php echo $_SESSION['error']; ?>', 'danger');
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        });
        
        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-bg-${type} border-0 position-fixed bottom-0 end-0 m-3`;
            toast.style.zIndex = '1050';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
    </script>
</body>
</html>