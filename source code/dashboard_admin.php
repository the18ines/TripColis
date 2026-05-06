<?php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et est un admin
$user = getCurrentUser();
if (!$user || $user['role'] !== 'admin') {
    header('Location: auth.php');
    exit();
}

// Récupérer les statistiques
$stats = [
    'total_clients' => 0,
    'total_transporteurs' => 0,
    'total_commandes' => 0,
    'commandes_en_attente' => 0,
    'commandes_en_cours' => 0,
    'chiffre_affaires' => 0,
    'commissions' => 0
];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'client'");
$stats['total_clients'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'transporteur'");
$stats['total_transporteurs'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM commandes");
$stats['total_commandes'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM commandes WHERE status = 'pending'");
$stats['commandes_en_attente'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM commandes WHERE status IN ('accepted', 'in_progress')");
$stats['commandes_en_cours'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT SUM(montant) as total FROM commandes WHERE status = 'completed'");
$stats['chiffre_affaires'] = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT SUM(commission) as total FROM commandes WHERE status = 'completed'");
$stats['commissions'] = $stmt->fetch()['total'] ?? 0;

// Récupérer les dernières commandes
$stmt = $pdo->query("SELECT c.*, u.nom as client_nom, u.prenom as client_prenom 
                     FROM commandes c 
                     JOIN utilisateurs u ON c.client_id = u.id 
                     ORDER BY c.created_at DESC LIMIT 10");
$dernieres_commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les transporteurs en attente de validation
$stmt = $pdo->query("SELECT u.*, t.* 
                     FROM utilisateurs u 
                     LEFT JOIN transporteurs t ON u.id = t.user_id 
                     WHERE u.role = 'transporteur' AND u.status = 'pending' 
                     ORDER BY u.created_at DESC");
$transporteurs_pending = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les commandes par statut pour le graphique
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM commandes GROUP BY status");
$commandes_by_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Administrateur - TripColis</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary-blue: #1e3a5f;
            --secondary-blue: #2c5282;
            --accent-orange: #ed8936;
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
        
        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
            margin: 0;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-top: 4px solid var(--accent-orange);
        }
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 10px;
            color: var(--primary-blue);
        }
        
        .stat-card p {
            color: #6b7280;
            margin-bottom: 0;
        }
        
        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: white;
        }
        
        .icon-blue { background-color: var(--primary-blue); }
        .icon-green { background-color: #38a169; }
        .icon-orange { background-color: var(--accent-orange); }
        .icon-purple { background-color: #9f7aea; }
        
        .dashboard-card {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        
        .btn-admin {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-validate {
            background-color: #38a169;
            color: white;
        }
        
        .btn-validate:hover {
            background-color: #2f855a;
        }
        
        .btn-reject {
            background-color: #e53e3e;
            color: white;
        }
        
        .btn-reject:hover {
            background-color: #c53030;
        }
        
        .btn-view {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .btn-view:hover {
            background-color: #2c5282;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-pending { background-color: #fef3c7; color: #92400e; }
        .badge-active { background-color: #d1fae5; color: #065f46; }
        .badge-completed { background-color: #dbeafe; color: #1e40af; }
        .badge-cancelled { background-color: #fee2e2; color: #991b1b; }
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
                <small>Administration</small>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard_admin.php" class="active">
                        <i class="fas fa-tachometer-alt me-3"></i>
                        <span>Tableau de bord</span>
                    </a>
                </li>
                <li>
                    <a href="admin_users.php">
                        <i class="fas fa-users me-3"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>
                <li>
                    <a href="admin_orders.php">
                        <i class="fas fa-shipping-fast me-3"></i>
                        <span>Commandes</span>
                    </a>
                </li>
                <li>
                    <a href="admin_transporteurs.php">
                        <i class="fas fa-motorcycle me-3"></i>
                        <span>Transporteurs</span>
                        <?php if(count($transporteurs_pending) > 0): ?>
                        <span class="badge bg-danger ms-auto"><?php echo count($transporteurs_pending); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="admin_finances.php">
                        <i class="fas fa-money-bill-wave me-3"></i>
                        <span>Finances</span>
                    </a>
                </li>
                <li>
                    <a href="admin_documents.php">
                        <i class="fas fa-file-contract me-3"></i>
                        <span>Documents</span>
                    </a>
                </li>
                <li>
                    <a href="admin_settings.php">
                        <i class="fas fa-cog me-3"></i>
                        <span>Paramètres</span>
                    </a>
                </li>
                <li>
                    <a href="admin_reports.php">
                        <i class="fas fa-chart-bar me-3"></i>
                        <span>Rapports</span>
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
                    <h4 class="mb-0">Bonjour, Admin 👋</h4>
                    <div class="d-flex align-items-center gap-3">
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control" placeholder="Rechercher...">
                            <button class="btn btn-outline-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-2"></i>
                                <?php echo htmlspecialchars($user['prenom']); ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profil</a>
                                <a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Paramètres</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="icon icon-blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3><?php echo $stats['total_clients']; ?></h3>
                    <p>Clients inscrits</p>
                </div>
                
                <div class="stat-card">
                    <div class="icon icon-green">
                        <i class="fas fa-motorcycle"></i>
                    </div>
                    <h3><?php echo $stats['total_transporteurs']; ?></h3>
                    <p>Transporteurs actifs</p>
                </div>
                
                <div class="stat-card">
                    <div class="icon icon-orange">
                        <i class="fas fa-box"></i>
                    </div>
                    <h3><?php echo $stats['total_commandes']; ?></h3>
                    <p>Commandes totales</p>
                </div>
                
                <div class="stat-card">
                    <div class="icon icon-purple">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3><?php echo number_format($stats['chiffre_affaires'], 0, ',', ' '); ?> FCFA</h3>
                    <p>Chiffre d'affaires</p>
                </div>
            </div>
            
            <!-- Graphiques et tableaux -->
            <div class="row">
                <!-- Graphique des commandes -->
                <div class="col-lg-8">
                    <div class="dashboard-card">
                        <h5 class="mb-4">Statistiques des commandes</h5>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <canvas id="ordersChart" height="200"></canvas>
                            </div>
                            <div class="col-md-6">
                                <div class="list-group">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>En attente</span>
                                        <span class="badge badge-pending"><?php echo $stats['commandes_en_attente']; ?></span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>En cours</span>
                                        <span class="badge bg-primary"><?php echo $stats['commandes_en_cours']; ?></span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>Terminées</span>
                                        <span class="badge badge-completed"><?php echo $stats['total_commandes'] - $stats['commandes_en_attente'] - $stats['commandes_en_cours']; ?></span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>Commissions</span>
                                        <span class="badge bg-success"><?php echo number_format($stats['commissions'], 0, ',', ' '); ?> FCFA</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dernières commandes -->
                    <div class="dashboard-card mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5>Dernières commandes</h5>
                            <a href="admin_orders.php" class="btn btn-sm btn-primary">Voir toutes</a>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>N° Commande</th>
                                        <th>Client</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($dernieres_commandes as $commande): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $commande['numero_commande'] ?? 'TC-' . str_pad($commande['id'], 5, '0', STR_PAD_LEFT); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($commande['client_prenom'] . ' ' . $commande['client_nom']); ?></td>
                                        <td><?php echo number_format($commande['montant'], 0, ',', ' '); ?> FCFA</td>
                                        <td>
                                            <?php 
                                            $status_badge = 'badge-pending';
                                            $status_text = 'En attente';
                                            switch($commande['status']) {
                                                case 'pending': $status_badge = 'badge-pending'; $status_text = 'En attente'; break;
                                                case 'accepted': $status_badge = 'badge-active'; $status_text = 'Acceptée'; break;
                                                case 'in_progress': $status_badge = 'badge-active'; $status_text = 'En cours'; break;
                                                case 'completed': $status_badge = 'badge-completed'; $status_text = 'Terminée'; break;
                                                case 'cancelled': $status_badge = 'badge-cancelled'; $status_text = 'Annulée'; break;
                                            }
                                            ?>
                                            <span class="status-badge <?php echo $status_badge; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($commande['created_at'])); ?></td>
                                        <td>
                                            <a href="admin_order_details.php?id=<?php echo $commande['id']; ?>" class="btn btn-sm btn-view">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Transporteurs en attente -->
                <div class="col-lg-4">
                    <div class="dashboard-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5>Transporteurs en attente</h5>
                            <span class="badge bg-danger"><?php echo count($transporteurs_pending); ?></span>
                        </div>
                        
                        <?php if(empty($transporteurs_pending)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                                <p class="text-muted">Aucun transporteur en attente</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach($transporteurs_pending as $transporteur): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($transporteur['prenom'] . ' ' . $transporteur['nom']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($transporteur['email']); ?></small>
                                        </div>
                                        <span class="badge badge-pending">En attente</span>
                                    </div>
                                    <div class="small mb-2">
                                        <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($transporteur['telephone']); ?>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn-admin btn-validate" onclick="validateTransporteur(<?php echo $transporteur['id']; ?>)">
                                            <i class="fas fa-check me-1"></i>Valider
                                        </button>
                                        <button class="btn-admin btn-reject" onclick="rejectTransporteur(<?php echo $transporteur['id']; ?>)">
                                            <i class="fas fa-times me-1"></i>Rejeter
                                        </button>
                                        <a href="admin_user_details.php?id=<?php echo $transporteur['id']; ?>" class="btn-admin btn-view">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Actions rapides -->
                    <div class="dashboard-card mt-4">
                        <h5 class="mb-4">Actions rapides</h5>
                        <div class="d-grid gap-2">
                            <a href="admin_add_user.php" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Ajouter un utilisateur
                            </a>
                            <a href="admin_export.php" class="btn btn-outline-primary">
                                <i class="fas fa-download me-2"></i>Exporter les données
                            </a>
                            <a href="admin_settings.php" class="btn btn-outline-primary">
                                <i class="fas fa-cog me-2"></i>Paramètres système
                            </a>
                            <button class="btn btn-outline-warning" onclick="runSystemCheck()">
                                <i class="fas fa-sync-alt me-2"></i>Vérification système
                            </button>
                        </div>
                    </div>
                    
                    <!-- Informations système -->
                    <div class="dashboard-card mt-4">
                        <h6 class="mb-3">Statut système</h6>
                        <div class="list-group">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Version</span>
                                <span class="badge bg-info">v1.0.0</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Base de données</span>
                                <span class="badge bg-success">Connectée</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Serveur</span>
                                <span class="badge bg-success">En ligne</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Dernière sauvegarde</span>
                                <small><?php echo date('d/m/Y'); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Graphique des commandes
        const ctx = document.getElementById('ordersChart').getContext('2d');
        const ordersChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['En attente', 'En cours', 'Terminées', 'Annulées'],
                datasets: [{
                    data: [
                        <?php echo $stats['commandes_en_attente']; ?>,
                        <?php echo $stats['commandes_en_cours']; ?>,
                        <?php echo $stats['total_commandes'] - $stats['commandes_en_attente'] - $stats['commandes_en_cours']; ?>,
                        0 // Annulées
                    ],
                    backgroundColor: [
                        '#f59e0b',
                        '#3b82f6',
                        '#10b981',
                        '#ef4444'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Valider un transporteur
        function validateTransporteur(userId) {
            if (confirm('Valider ce transporteur ?')) {
                fetch('admin_validate_transporteur.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ user_id: userId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Transporteur validé avec succès');
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.error);
                    }
                });
            }
        }
        
        // Rejeter un transporteur
        function rejectTransporteur(userId) {
            const reason = prompt('Raison du rejet :');
            if (reason) {
                fetch('admin_reject_transporteur.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        user_id: userId,
                        reason: reason 
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Transporteur rejeté');
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.error);
                    }
                });
            }
        }
        
        // Vérification système
        function runSystemCheck() {
            fetch('admin_system_check.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Vérification système terminée\n' + data.message);
                    } else {
                        alert('Erreur lors de la vérification');
                    }
                });
        }
        
        // Mise à jour en temps réel des statistiques
        function updateStats() {
            fetch('admin_live_stats.php')
                .then(response => response.json())
                .then(data => {
                    // Mettre à jour les statistiques affichées
                    document.querySelectorAll('.stat-card h3').forEach((el, index) => {
                        const values = [
                            data.total_clients,
                            data.total_transporteurs,
                            data.total_orders,
                            data.revenue + ' FCFA'
                        ];
                        if (values[index]) {
                            el.textContent = values[index];
                        }
                    });
                });
        }
        
        // Mettre à jour les stats toutes les 30 secondes
        setInterval(updateStats, 30000);
    </script>
</body>
</html>