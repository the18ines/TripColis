<?php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et est un transporteur
$user = getCurrentUser();
if (!$user || $user['role'] !== 'transporteur') {
    header('Location: auth.php');
    exit();
}

// Récupérer les informations spécifiques au transporteur
$stmt = $pdo->prepare("SELECT * FROM transporteurs WHERE user_id = ?");
$stmt->execute([$user['id']]);
$transporteur = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les missions du transporteur
$stmt = $pdo->prepare("SELECT c.*, u.nom as client_nom, u.prenom as client_prenom 
                       FROM commandes c 
                       JOIN utilisateurs u ON c.client_id = u.id 
                       WHERE c.transporteur_id = ? OR c.status = 'pending'
                       ORDER BY c.created_at DESC");
$stmt->execute([$user['id']]);
$missions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques
$stats = [
    'disponibles' => 0,
    'en_cours' => 0,
    'terminees' => 0,
    'revenus' => 0
];

foreach ($missions as $mission) {
    if ($mission['transporteur_id'] == $user['id']) {
        if ($mission['status'] == 'in_progress') {
            $stats['en_cours']++;
        } elseif ($mission['status'] == 'completed') {
            $stats['terminees']++;
            $stats['revenus'] += $mission['montant'] * 0.8; // 80% pour le transporteur
        }
    } elseif ($mission['status'] == 'pending') {
        $stats['disponibles']++;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Transporteur - TripColis</title>
    
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
        
        .status-toggle {
            margin-top: 15px;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: #38a169;
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(30px);
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
        
        .icon-primary {
            background-color: rgba(30, 58, 95, 0.1);
            color: var(--primary-blue);
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
        
        .mission-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-available {
            background-color: rgba(56, 161, 105, 0.1);
            color: #38a169;
        }
        
        .badge-in-progress {
            background-color: rgba(237, 137, 54, 0.1);
            color: var(--accent-orange);
        }
        
        .badge-completed {
            background-color: rgba(30, 58, 95, 0.1);
            color: var(--primary-blue);
        }
        
        .badge-pending {
            background-color: rgba(214, 158, 46, 0.1);
            color: #d69e2e;
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
                <small>Tableau de bord transporteur</small>
            </div>
            
            <div class="user-profile">
                <div class="user-avatar mb-3">
                    <i class="fas fa-motorcycle fa-3x"></i>
                </div>
                <h6 class="mb-1"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h6>
                <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                <div class="badge bg-warning mt-2">Transporteur</div>
                
                <div class="status-toggle mt-3">
                    <label class="toggle-switch">
                        <input type="checkbox" id="availabilityToggle" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="ms-2">Disponible</span>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard_transporteur.php" class="active">
                        <i class="fas fa-tachometer-alt me-3"></i>
                        <span>Tableau de bord</span>
                    </a>
                </li>
                <li>
                    <a href="missions.php">
                        <i class="fas fa-search me-3"></i>
                        <span>Missions disponibles</span>
                        <?php if($stats['disponibles'] > 0): ?>
                        <span class="badge bg-success ms-auto"><?php echo $stats['disponibles']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="missions.php?status=active">
                        <i class="fas fa-motorcycle me-3"></i>
                        <span>Missions en cours</span>
                        <?php if($stats['en_cours'] > 0): ?>
                        <span class="badge bg-primary ms-auto"><?php echo $stats['en_cours']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="missions.php?status=completed">
                        <i class="fas fa-check-circle me-3"></i>
                        <span>Missions terminées</span>
                    </a>
                </li>
                <li>
                    <a href="earnings.php">
                        <i class="fas fa-money-bill-wave me-3"></i>
                        <span>Mes gains</span>
                    </a>
                </li>
                <li>
                    <a href="documents.php">
                        <i class="fas fa-file-contract me-3"></i>
                        <span>Documents</span>
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
                        <button class="btn btn-outline-primary position-relative">
                            <i class="fas fa-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                3
                            </span>
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
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="fw-bold"><?php echo $stats['disponibles']; ?></h3>
                        <p class="text-muted mb-0">Missions disponibles</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="dashboard-card stats-card">
                        <div class="card-icon icon-orange">
                            <i class="fas fa-motorcycle"></i>
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
                        <div class="card-icon icon-primary">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h3 class="fw-bold"><?php echo number_format($stats['revenus'], 0, ',', ' '); ?> FCFA</h3>
                        <p class="text-muted mb-0">Gains totaux</p>
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
                                <a href="missions.php" class="btn btn-custom-orange w-100 py-3 d-flex flex-column align-items-center">
                                    <i class="fas fa-search fa-2x mb-2"></i>
                                    <span>Voir missions</span>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center" data-bs-toggle="modal" data-bs-target="#startMissionModal">
                                    <i class="fas fa-play-circle fa-2x mb-2"></i>
                                    <span>Démarrer mission</span>
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="earnings.php" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center">
                                    <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                    <span>Mes gains</span>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="documents.php" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center">
                                    <i class="fas fa-file-contract fa-2x mb-2"></i>
                                    <span>Documents</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Missions disponibles -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="dashboard-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5>Missions disponibles</h5>
                            <a href="missions.php" class="btn btn-sm btn-primary">Voir toutes</a>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>N° Mission</th>
                                        <th>Client</th>
                                        <th>Agence</th>
                                        <th>Distance</th>
                                        <th>Rémunération</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $count = 0;
                                    foreach($missions as $mission): 
                                        if($mission['status'] !== 'pending') continue;
                                        if($count >= 3) break;
                                        $count++;
                                    ?>
                                    <tr>
                                        <td>
                                            <strong>TC-<?php echo str_pad($mission['id'], 5, '0', STR_PAD_LEFT); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($mission['client_prenom'] . ' ' . $mission['client_nom']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($mission['agence']); ?></td>
                                        <td><?php echo rand(2, 8); ?> km</td>
                                        <td>
                                            <strong><?php echo number_format($mission['montant'] * 0.8, 0, ',', ' '); ?> FCFA</strong>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-success" onclick="acceptMission(<?php echo $mission['id']; ?>)">
                                                <i class="fas fa-check"></i> Accepter
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if($count == 0): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="fas fa-search fa-2x text-muted mb-3"></i>
                                                <p class="text-muted">Aucune mission disponible</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Missions en cours -->
                    <div class="dashboard-card mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5>Missions en cours</h5>
                            <a href="missions.php?status=active" class="btn btn-sm btn-primary">Voir toutes</a>
                        </div>
                        
                        <div class="row">
                            <?php 
                            $count_active = 0;
                            foreach($missions as $mission): 
                                if($mission['transporteur_id'] != $user['id'] || !in_array($mission['status'], ['accepted', 'in_progress'])) continue;
                                if($count_active >= 2) break;
                                $count_active++;
                            ?>
                            <div class="col-md-6 mb-3">
                                <div class="card border-start border-3 border-orange">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="fw-bold">TC-<?php echo str_pad($mission['id'], 5, '0', STR_PAD_LEFT); ?></h6>
                                                <p class="mb-1 small"><?php echo htmlspecialchars($mission['agence']); ?></p>
                                                <small class="text-muted">Client: <?php echo htmlspecialchars($mission['client_prenom']); ?></small>
                                            </div>
                                            <span class="badge bg-primary">En cours</span>
                                        </div>
                                        
                                        <div class="progress mb-3" style="height: 8px;">
                                            <div class="progress-bar" style="width: <?php echo rand(30, 80); ?>%;"></div>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-success" onclick="completeMission(<?php echo $mission['id']; ?>)">
                                                <i class="fas fa-check me-1"></i> Terminer
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary" onclick="contactClient(<?php echo $mission['client_id']; ?>)">
                                                <i class="fas fa-phone me-1"></i> Appeler
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <?php if($count_active == 0): ?>
                                <div class="col-12">
                                    <div class="text-center py-4">
                                        <i class="fas fa-motorcycle fa-2x text-muted mb-3"></i>
                                        <p class="text-muted">Aucune mission en cours</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Informations rapides -->
                <div class="col-lg-4">
                    <div class="dashboard-card">
                        <h5 class="mb-4">Résumé des gains</h5>
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Aujourd'hui:</span>
                                <strong><?php echo number_format(rand(5000, 15000), 0, ',', ' '); ?> FCFA</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Cette semaine:</span>
                                <strong><?php echo number_format(rand(30000, 80000), 0, ',', ' '); ?> FCFA</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Ce mois:</span>
                                <strong><?php echo number_format($stats['revenus'], 0, ',', ' '); ?> FCFA</strong>
                            </div>
                            
                            <div class="text-center">
                                <a href="earnings.php" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="fas fa-chart-pie me-1"></i> Voir détails
                                </a>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="mb-3">Documents</h6>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="text-center p-3 border rounded">
                                    <i class="fas fa-file-contract fa-2x text-primary mb-2"></i>
                                    <div class="small">Contrat</div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="text-center p-3 border rounded">
                                    <i class="fas fa-scroll fa-2x text-warning mb-2"></i>
                                    <div class="small">Charte</div>
                                </div>
                            </div>
                        </div>
                        
                        <a href="documents.php" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-folder-open me-1"></i> Voir tous les documents
                        </a>
                    </div>
                    
                    <!-- Support -->
                    <div class="dashboard-card mt-4">
                        <h6 class="mb-3">Support TripColis</h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" onclick="openChat()">
                                <i class="fas fa-comments me-2"></i> Chat en direct
                            </button>
                            <a href="tel:+237699054508" class="btn btn-outline-primary">
                                <i class="fas fa-phone-alt me-2"></i> Nous appeler
                            </a>
                            <a href="support.php" class="btn btn-outline-warning">
                                <i class="fas fa-ticket-alt me-2"></i> Ouvrir un ticket
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Démarrer Mission -->
    <div class="modal fade" id="startMissionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Démarrer une mission</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Sélectionnez une mission à démarrer :</p>
                    <div class="list-group">
                        <?php 
                        $has_missions = false;
                        foreach($missions as $mission): 
                            if($mission['transporteur_id'] == $user['id'] && $mission['status'] == 'accepted'):
                                $has_missions = true;
                        ?>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <strong>TC-<?php echo str_pad($mission['id'], 5, '0', STR_PAD_LEFT); ?></strong>
                                <small class="d-block text-muted"><?php echo htmlspecialchars($mission['agence']); ?></small>
                            </div>
                            <button class="btn btn-sm btn-primary" onclick="startMission(<?php echo $mission['id']; ?>)">
                                Démarrer
                            </button>
                        </a>
                        <?php endif; endforeach; ?>
                        
                        <?php if(!$has_missions): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                                <p class="text-muted">Aucune mission à démarrer</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Disponibilité
        const availabilityToggle = document.getElementById('availabilityToggle');
        availabilityToggle.addEventListener('change', function() {
            fetch('update_availability.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    available: this.checked
                })
            })
            .then(response => response.json())
            .then(data => {
                showToast(this.checked ? 'Vous êtes maintenant disponible' : 'Vous êtes maintenant indisponible', 'success');
            });
        });
        
        // Accepter une mission
        function acceptMission(missionId) {
            if (confirm('Accepter cette mission ?')) {
                fetch('accept_mission.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        mission_id: missionId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Mission acceptée avec succès !', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showToast(data.error || 'Erreur', 'danger');
                    }
                });
            }
        }
        
        // Démarrer une mission
        function startMission(missionId) {
            fetch('start_mission.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    mission_id: missionId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Mission démarrée !', 'success');
                    $('#startMissionModal').modal('hide');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(data.error || 'Erreur', 'danger');
                }
            });
        }
        
        // Terminer une mission
        function completeMission(missionId) {
            if (confirm('Terminer cette mission ?')) {
                fetch('complete_mission.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        mission_id: missionId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Mission terminée avec succès !', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showToast(data.error || 'Erreur', 'danger');
                    }
                });
            }
        }
        
        // Contacter un client
        function contactClient(clientId) {
            alert('Appel du client...');
            // Implémentation réelle avec l'API téléphone
        }
        
        // Ouvrir le chat
        function openChat() {
            alert('Ouverture du chat en direct...');
            // Implémentation du chat
        }
        
        // Fonction toast
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