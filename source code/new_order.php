<?php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et est un client
$user = getCurrentUser();
if (!$user || $user['role'] !== 'client') {
    header('Location: auth.php');
    exit();
}

// Agences disponibles
$agences = [
    'Express Union Akwa - Douala',
    'United Express Bonabéri - Douala',
    'Agence Voyage Bépanda - Douala',
    'Express Union Deido - Douala',
    'Agence New-Bell - Douala',
    'Express Union Bastos - Yaoundé',
    'United Express Centre - Yaoundé',
    'Agence Mvog-Ada - Yaoundé'
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    $agence = sanitize($_POST['agence']);
    $type_service = sanitize($_POST['type_service']);
    $adresse_livraison = sanitize($_POST['adresse_livraison']);
    $description_colis = sanitize($_POST['description_colis']);
    $poids = floatval($_POST['poids']);
    $dimensions = sanitize($_POST['dimensions']);
    $fragile = isset($_POST['fragile']) ? 1 : 0;
    $urgence = isset($_POST['urgence']) ? 1 : 0;
    $distance = floatval($_POST['distance']);
    $montant = calculatePrice($poids, $distance, $fragile, $urgence);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO commandes (client_id, agence, type_service, adresse_livraison, description_colis, poids, dimensions, fragile, urgence, distance, montant, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$user['id'], $agence, $type_service, $adresse_livraison, $description_colis, $poids, $dimensions, $fragile, $urgence, $distance, $montant]);
        
        $_SESSION['success'] = "Commande créée avec succès ! Un transporteur va être assigné rapidement.";
        header('Location: dashboard_client.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
    }
}

// Fonction pour calculer le prix
function calculatePrice($poids, $distance, $fragile = false, $urgence = false) {
    $prix_base = 1500;
    $prix_poids = $poids * 100; // 100 FCFA par kg
    $prix_distance = $distance * 200; // 200 FCFA par km
    $supplement_fragile = $fragile ? 500 : 0;
    $supplement_urgence = $urgence ? 1000 : 0;
    
    return $prix_base + $prix_poids + $prix_distance + $supplement_fragile + $supplement_urgence;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Commande - TripColis</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    
    <style>
        .order-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 0;
        }
        
        .order-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .order-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #152642 100%);
            color: white;
            padding: 2rem;
        }
        
        .order-form {
            padding: 3rem;
        }
        
        .form-section {
            margin-bottom: 2.5rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #f1f5f9;
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .price-calculator {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            border: 2px solid #e2e8f0;
        }
        
        .price-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .price-total {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e3a5f;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #e2e8f0;
        }
        
        .map-container {
            height: 300px;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="order-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <div class="order-card">
                        <!-- Header -->
                        <div class="order-header text-center">
                            <h1 class="mb-3">
                                <i class="fas fa-box me-2"></i>Nouvelle Commande
                            </h1>
                            <p class="mb-0">Remplissez les détails de votre colis pour une livraison rapide et sécurisée</p>
                        </div>
                        
                        <!-- Formulaire -->
                        <div class="order-form">
                            <?php if(isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" id="orderForm">
                                <!-- Section 1: Type de service -->
                                <div class="form-section">
                                    <h4 class="mb-4">1. Type de service</h4>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check card p-3">
                                                <input class="form-check-input" type="radio" name="type_service" id="retrait" value="retrait" checked>
                                                <label class="form-check-label" for="retrait">
                                                    <i class="fas fa-download fa-2x text-primary mb-2"></i>
                                                    <h5>Retrait de colis</h5>
                                                    <p class="text-muted mb-0">Nous récupérons votre colis à l'agence</p>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check card p-3">
                                                <input class="form-check-input" type="radio" name="type_service" id="envoi" value="envoi">
                                                <label class="form-check-label" for="envoi">
                                                    <i class="fas fa-paper-plane fa-2x text-primary mb-2"></i>
                                                    <h5>Envoi de colis</h5>
                                                    <p class="text-muted mb-0">Nous transportons votre colis vers une agence</p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Section 2: Agence -->
                                <div class="form-section">
                                    <h4 class="mb-4">2. Sélection de l'agence</h4>
                                    <div class="mb-3">
                                        <label class="form-label">Agence *</label>
                                        <select class="form-select" name="agence" id="agence" required>
                                            <option value="">Sélectionnez une agence</option>
                                            <?php foreach($agences as $agence): ?>
                                            <option value="<?php echo htmlspecialchars($agence); ?>"><?php echo htmlspecialchars($agence); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <!-- Map -->
                                    <div class="mt-4">
                                        <label class="form-label">Localisation de l'agence</label>
                                        <div class="map-container" id="map">
                                            <!-- La carte sera initialisée par JavaScript -->
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Section 3: Détails du colis -->
                                <div class="form-section">
                                    <h4 class="mb-4">3. Détails du colis</h4>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Description du colis *</label>
                                            <textarea class="form-control" name="description_colis" rows="3" required placeholder="Ex: Colis documents, vêtements, etc."></textarea>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Adresse de livraison *</label>
                                            <textarea class="form-control" name="adresse_livraison" rows="3" required placeholder="Votre adresse complète"></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Poids (kg) *</label>
                                            <input type="number" class="form-control" name="poids" id="poids" min="0.1" max="50" step="0.1" value="1" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Dimensions (cm)</label>
                                            <input type="text" class="form-control" name="dimensions" placeholder="Ex: 30x20x15">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Distance estimée (km) *</label>
                                            <input type="number" class="form-control" name="distance" id="distance" min="1" max="50" value="5" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Valeur estimée (FCFA)</label>
                                            <input type="number" class="form-control" name="valeur" placeholder="Optionnel">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="fragile" id="fragile">
                                                <label class="form-check-label" for="fragile">
                                                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                                    Colis fragile (supplément 500 FCFA)
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="urgence" id="urgence">
                                                <label class="form-check-label" for="urgence">
                                                    <i class="fas fa-bolt text-danger me-2"></i>
                                                    Service urgent (supplément 1000 FCFA)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Section 4: Calcul du prix -->
                                <div class="form-section">
                                    <h4 class="mb-4">4. Calcul du prix</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="price-calculator">
                                                <div class="price-item">
                                                    <span>Prix de base:</span>
                                                    <span id="basePrice">1 500 FCFA</span>
                                                </div>
                                                <div class="price-item">
                                                    <span>Supplément poids (<span id="weightText">1 kg</span>):</span>
                                                    <span id="weightSurcharge">100 FCFA</span>
                                                </div>
                                                <div class="price-item">
                                                    <span>Supplément distance (<span id="distanceText">5 km</span>):</span>
                                                    <span id="distanceSurcharge">1 000 FCFA</span>
                                                </div>
                                                <div class="price-item" id="fragileSurchargeItem" style="display: none;">
                                                    <span>Supplément fragile:</span>
                                                    <span id="fragileSurcharge">500 FCFA</span>
                                                </div>
                                                <div class="price-item" id="urgentSurchargeItem" style="display: none;">
                                                    <span>Supplément urgent:</span>
                                                    <span id="urgentSurcharge">1 000 FCFA</span>
                                                </div>
                                                <div class="price-total">
                                                    <span>Total:</span>
                                                    <span id="totalPrice">2 600 FCFA</span>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-4">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    <small>Le prix final peut varier légèrement selon le trajet exact</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="card border-0 bg-light h-100">
                                                <div class="card-body d-flex flex-column justify-content-center">
                                                    <div class="text-center mb-4">
                                                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                                                        <h5>Garanties TripColis</h5>
                                                    </div>
                                                    
                                                    <ul class="list-unstyled">
                                                        <li class="mb-2">
                                                            <i class="fas fa-check-circle text-success me-2"></i>
                                                            Transporteurs vérifiés
                                                        </li>
                                                        <li class="mb-2">
                                                            <i class="fas fa-check-circle text-success me-2"></i>
                                                            Suivi en temps réel
                                                        </li>
                                                        <li class="mb-2">
                                                            <i class="fas fa-check-circle text-success me-2"></i>
                                                            Paiement sécurisé
                                                        </li>
                                                        <li class="mb-2">
                                                            <i class="fas fa-check-circle text-success me-2"></i>
                                                            Assurance colis
                                                        </li>
                                                        <li>
                                                            <i class="fas fa-check-circle text-success me-2"></i>
                                                            Support 24/7
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Actions -->
                                <div class="form-section">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check mb-4">
                                                <input class="form-check-input" type="checkbox" id="conditions" required>
                                                <label class="form-check-label" for="conditions">
                                                    J'accepte les <a href="charte.php" target="_blank">conditions générales</a> de TripColis
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-end gap-3">
                                                <a href="dashboard_client.php" class="btn btn-outline-secondary">
                                                    <i class="fas fa-arrow-left me-2"></i>Retour
                                                </a>
                                                <button type="submit" name="create_order" class="btn btn-primary btn-lg">
                                                    <i class="fas fa-check me-2"></i>Confirmer la commande
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialisation de la carte
        let map;
        let marker;
        
        function initMap() {
            // Coordonnées par défaut (Douala)
            const defaultLat = 4.0511;
            const defaultLng = 9.7679;
            
            // Initialiser la carte
            map = L.map('map').setView([defaultLat, defaultLng], 13);
            
            // Ajouter les tuiles OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            // Ajouter un marqueur
            marker = L.marker([defaultLat, defaultLng]).addTo(map)
                .bindPopup('Agence sélectionnée')
                .openPopup();
        }
        
        // Calcul du prix
        function calculatePrice() {
            const poids = parseFloat(document.getElementById('poids').value) || 1;
            const distance = parseFloat(document.getElementById('distance').value) || 5;
            const isFragile = document.getElementById('fragile').checked;
            const isUrgent = document.getElementById('urgence').checked;
            
            const prixBase = 1500;
            const supplementPoids = poids * 100;
            const supplementDistance = distance * 200;
            const supplementFragile = isFragile ? 500 : 0;
            const supplementUrgent = isUrgent ? 1000 : 0;
            
            const total = prixBase + supplementPoids + supplementDistance + supplementFragile + supplementUrgent;
            
            // Mettre à jour l'affichage
            document.getElementById('weightText').textContent = poids.toFixed(1) + ' kg';
            document.getElementById('distanceText').textContent = distance + ' km';
            document.getElementById('weightSurcharge').textContent = supplementPoids.toLocaleString('fr-FR') + ' FCFA';
            document.getElementById('distanceSurcharge').textContent = supplementDistance.toLocaleString('fr-FR') + ' FCFA';
            
            // Gérer les suppléments
            const fragileItem = document.getElementById('fragileSurchargeItem');
            const urgentItem = document.getElementById('urgentSurchargeItem');
            
            if (isFragile) {
                fragileItem.style.display = 'flex';
                document.getElementById('fragileSurcharge').textContent = '500 FCFA';
            } else {
                fragileItem.style.display = 'none';
            }
            
            if (isUrgent) {
                urgentItem.style.display = 'flex';
                document.getElementById('urgentSurcharge').textContent = '1 000 FCFA';
            } else {
                urgentItem.style.display = 'none';
            }
            
            // Total
            document.getElementById('totalPrice').textContent = total.toLocaleString('fr-FR') + ' FCFA';
        }
        
        // Écouter les changements
        document.getElementById('poids').addEventListener('input', calculatePrice);
        document.getElementById('distance').addEventListener('input', calculatePrice);
        document.getElementById('fragile').addEventListener('change', calculatePrice);
        document.getElementById('urgence').addEventListener('change', calculatePrice);
        
        // Gérer la sélection d'agence
        document.getElementById('agence').addEventListener('change', function() {
            const agence = this.value;
            if (agence.includes('Douala')) {
                map.setView([4.0511, 9.7679], 13);
                marker.setLatLng([4.0511, 9.7679]);
            } else if (agence.includes('Yaoundé')) {
                map.setView([3.848, 11.502], 13);
                marker.setLatLng([3.848, 11.502]);
            }
            marker.bindPopup(agence).openPopup();
        });
        
        // Initialiser
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            calculatePrice();
        });
        
        // Validation du formulaire
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            const poids = parseFloat(document.getElementById('poids').value);
            const distance = parseFloat(document.getElementById('distance').value);
            
            if (poids > 50) {
                e.preventDefault();
                alert('Le poids maximum est de 50 kg');
                return;
            }
            
            if (distance > 50) {
                e.preventDefault();
                alert('La distance maximum est de 50 km');
                return;
            }
        });
    </script>
</body>
</html>