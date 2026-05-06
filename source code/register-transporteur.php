<?php
include 'includes/config.php';
include 'includes/auth.php';

// Si l'utilisateur est déjà connecté, rediriger selon son rôle
if (estConnecte()) {
    redirigerSelonRole();
}

$pageTitle = 'Devenir Transporteur TripColis';
$pageDescription = 'Rejoignez notre réseau de mototaxis de confiance et générez des revenus stables';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nom' => $_POST['nom'],
        'prenom' => $_POST['prenom'],
        'email' => $_POST['email'],
        'telephone' => $_POST['telephone'],
        'password' => $_POST['password'],
        'role' => 'transporteur',
        'ville' => $_POST['ville'] ?? '',
        'quartier' => $_POST['quartier'] ?? '',
        'cni' => $_POST['cni'] ?? ''
    ];
    
    if (inscrireUtilisateur($data)) {
        $userId = $pdo->lastInsertId();
        $matricule = 'TRC' . str_pad($userId, 5, '0', STR_PAD_LEFT);
        
        // Ajouter dans la table transporteurs
        $sql = "INSERT INTO transporteurs (user_id, matricule, mode_paiement, marque_moto, modele_moto, immatriculation) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $userId,
            $matricule,
            $_POST['mode_paiement'] ?? 'autre',
            $_POST['marque_moto'] ?? '',
            $_POST['modele_moto'] ?? '',
            $_POST['immatriculation'] ?? ''
        ]);
        
        // Connecter automatiquement l'utilisateur
        if (connecterUtilisateur($data['email'], $data['password'])) {
            header('Location: dashboard_transporteur.php?welcome=1');
            exit();
        }
    } else {
        $error = "Erreur lors de la création du compte";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo $pageDescription; ?>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .page-header {
            background: linear-gradient(135deg, #170B87 0%, #0c0759 100%);
            color: white;
            padding: 80px 0 40px;
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            z-index: 1;
        }
        
        .page-header-content {
            position: relative;
            z-index: 2;
        }
        
        .step-progress {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 800px;
            margin: 40px auto;
            position: relative;
        }
        
        .step-progress::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-50%);
            z-index: 1;
        }
        
        .step-item {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }
        
        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin: 0 auto 10px;
            border: 3px solid rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
        }
        
        .step-item.active .step-circle {
            background: white;
            color: #170B87;
            border-color: white;
            box-shadow: 0 0 0 5px rgba(255, 255, 255, 0.2);
        }
        
        .step-item.completed .step-circle {
            background: #F97316;
            color: white;
            border-color: #F97316;
        }
        
        .step-label {
            font-size: 0.9rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .step-item.active .step-label {
            color: white;
            font-weight: 600;
        }
        
        .form-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-top: -40px;
            position: relative;
            z-index: 10;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }
        
        .form-step {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        
        .form-step.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #F1F5F9;
        }
        
        .form-header h3 {
            color: #1a1a2e;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .form-header p {
            color: #6B7280;
            margin-bottom: 0;
        }
        
        .form-group-enhanced {
            margin-bottom: 1.5rem;
        }
        
        .form-label-enhanced {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #1a1a2e;
        }
        
        .form-control-enhanced {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #1a1a2e;
            background-color: white;
            background-clip: padding-box;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .form-control-enhanced:focus {
            border-color: #170B87;
            box-shadow: 0 0 0 3px rgba(23, 11, 135, 0.1);
            outline: none;
        }
        
        .input-with-action {
            position: relative;
        }
        
        .input-action {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6B7280;
            cursor: pointer;
            padding: 5px;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #F1F5F9;
        }
        
        .btn-form {
            padding: 0.875rem 2rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .btn-form:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .pricing-card {
            background: linear-gradient(135deg, #F0F9FF 0%, #E0F2FE 100%);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            border: 2px solid #BAE6FD;
        }
        
        .pricing-amount {
            font-size: 3rem;
            font-weight: 800;
            color: #0c0759;
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
        }
        
        .feature-list li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .feature-list li i {
            color: #10b981;
        }
        
        .payment-method {
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-method:hover, .payment-method.active {
            border-color: #170B87;
            background: rgba(23, 11, 135, 0.05);
        }
        
        .payment-icon {
            font-size: 2rem;
            color: #170B87;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .page-header {
                padding: 60px 0 30px;
            }
            
            .form-container {
                padding: 20px;
                margin-top: -20px;
            }
            
            .step-progress {
                margin: 20px auto;
            }
            
            .step-circle {
                width: 40px;
                height: 40px;
                font-size: 0.9rem;
            }
            
            .step-label {
                font-size: 0.8rem;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 1rem;
            }
            
            .btn-form {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container page-header-content">
            <div class="text-center">
                <h1 class="display-5 fw-bold mb-3">Devenez Transporteur TripColis</h1>
                <p class="lead mb-0">
                    Rejoignez notre réseau de mototaxis de confiance et générez des revenus complémentaires
                </p>
            </div>
            
            <!-- Indicateur d'étapes -->
            <div class="step-progress">
                <div class="step-item active" data-step="1">
                    <div class="step-circle">1</div>
                    <div class="step-label">Informations</div>
                </div>
                <div class="step-item" data-step="2">
                    <div class="step-circle">2</div>
                    <div class="step-label">Moto</div>
                </div>
                <div class="step-item" data-step="3">
                    <div class="step-circle">3</div>
                    <div class="step-label">Paiement</div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Étape 1: Informations personnelles -->
            <div class="form-step active" id="step1">
                <div class="form-header">
                    <h3>Informations Personnelles</h3>
                    <p>Remplissez vos informations de base pour commencer l'inscription</p>
                </div>
                
                <form method="POST" action="" id="transporteurForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-enhanced">
                                <label for="nom" class="form-label-enhanced">Nom *</label>
                                <input type="text" class="form-control-enhanced" id="nom" name="nom" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-enhanced">
                                <label for="prenom" class="form-label-enhanced">Prénom *</label>
                                <input type="text" class="form-control-enhanced" id="prenom" name="prenom" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-enhanced">
                                <label for="telephone" class="form-label-enhanced">Téléphone *</label>
                                <div class="input-with-action">
                                    <input type="tel" class="form-control-enhanced" id="telephone" name="telephone" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-enhanced">
                                <label for="email" class="form-label-enhanced">Email *</label>
                                <input type="email" class="form-control-enhanced" id="email" name="email" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-enhanced">
                                <label for="quartier" class="form-label-enhanced">Quartier de résidence *</label>
                                <select class="form-control-enhanced" id="quartier" name="quartier" required>
                                    <option value="">Sélectionnez votre quartier</option>
                                    <option value="akwa">Akwa</option>
                                    <option value="bonaberi">Bonabéri</option>
                                    <option value="bepanda">Bépanda</option>
                                    <option value="makepe">Makepe</option>
                                    <option value="deido">Deido</option>
                                    <option value="new-bell">New Bell</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-enhanced">
                                <label for="ville" class="form-label-enhanced">Ville *</label>
                                <select class="form-control-enhanced" id="ville" name="ville" required>
                                    <option value="">Sélectionnez votre ville</option>
                                    <option value="douala">Douala</option>
                                    <option value="yaounde">Yaoundé</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-enhanced">
                                <label for="password" class="form-label-enhanced">Mot de passe *</label>
                                <div class="input-with-action">
                                    <input type="password" class="form-control-enhanced" id="password" name="password" required>
                                    <button type="button" class="input-action toggle-password" data-target="#password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-enhanced">
                                <label for="password_confirmation" class="form-label-enhanced">Confirmation *</label>
                                <div class="input-with-action">
                                    <input type="password" class="form-control-enhanced" id="password_confirmation" 
                                           name="password_confirmation" required>
                                    <button type="button" class="input-action toggle-password" data-target="#password_confirmation">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="index.php" class="btn btn-outline-secondary btn-form">
                            <i class="fas fa-arrow-left me-2"></i> Retour
                        </a>
                        <button type="button" class="btn btn-primary btn-form next-step" data-next="2">
                            Suivant <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
            </div>
            
            <!-- Étape 2: Informations moto -->
            <div class="form-step" id="step2">
                <div class="form-header">
                    <h3>Informations sur la moto</h3>
                    <p>Renseignez les détails de votre moyen de transport</p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group-enhanced">
                            <label for="marque_moto" class="form-label-enhanced">Marque *</label>
                            <input type="text" class="form-control-enhanced" id="marque_moto" name="marque_moto" 
                                   required placeholder="Ex: Yamaha">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group-enhanced">
                            <label for="modele_moto" class="form-label-enhanced">Modèle *</label>
                            <input type="text" class="form-control-enhanced" id="modele_moto" name="modele_moto" 
                                   required placeholder="Ex: Crypton">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group-enhanced">
                            <label for="immatriculation" class="form-label-enhanced">Immatriculation *</label>
                            <input type="text" class="form-control-enhanced" id="immatriculation" name="immatriculation" 
                                   required placeholder="Ex: LT 1234 AB">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group-enhanced">
                            <label for="cni" class="form-label-enhanced">Numéro CNI</label>
                            <input type="text" class="form-control-enhanced" id="cni" name="cni" 
                                   placeholder="Votre numéro CNI">
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline-secondary btn-form prev-step" data-prev="1">
                        <i class="fas fa-arrow-left me-2"></i> Précédent
                    </button>
                    <button type="button" class="btn btn-primary btn-form next-step" data-next="3">
                        Suivant <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
            
            <!-- Étape 3: Paiement -->
            <div class="form-step" id="step3">
                <div class="form-header">
                    <h3>Paiement des frais d'inscription</h3>
                    <p>Finalisez votre inscription en payant les frais d'adhésion</p>
                </div>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="form-group-enhanced">
                            <label class="form-label-enhanced">Mode de paiement *</label>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="payment-method" data-method="om">
                                        <div class="payment-icon">
                                            <i class="fas fa-mobile-alt"></i>
                                        </div>
                                        <h6>Orange Money</h6>
                                        <p class="small text-muted mb-0">Paiement mobile</p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="payment-method" data-method="momo">
                                        <div class="payment-icon">
                                            <i class="fas fa-mobile-alt"></i>
                                        </div>
                                        <h6>MTN Mobile Money</h6>
                                        <p class="small text-muted mb-0">Paiement mobile</p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="payment-method" data-method="especes">
                                        <div class="payment-icon">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <h6>Espèces</h6>
                                        <p class="small text-muted mb-0">Paiement en agence</p>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="mode_paiement" name="mode_paiement" value="" required>
                        </div>
                        
                        <div class="form-group-enhanced">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="conditions" name="conditions" required>
                                <label class="form-check-label" for="conditions">
                                    J'accepte les <a href="#" class="text-primary">conditions générales</a> et la 
                                    <a href="#" class="text-primary">politique de confidentialité</a> de TripColis
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="pricing-card">
                            <h5 class="mb-3">Frais d'inscription</h5>
                            <div class="pricing-amount">5,000</div>
                            <div class="pricing-period">FCFA</div>
                            <p class="mb-3">Frais uniques d'inscription</p>
                            
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i> Accès complet à la plateforme</li>
                                <li><i class="fas fa-check"></i> Support client 24/7</li>
                                <li><i class="fas fa-check"></i> Formations gratuites</li>
                                <li><i class="fas fa-check"></i> Assurance responsabilité</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline-secondary btn-form prev-step" data-prev="2">
                        <i class="fas fa-arrow-left me-2"></i> Précédent
                    </button>
                    <button type="submit" class="btn btn-primary btn-form">
                        <i class="fas fa-check me-2"></i>
                        Finaliser l'inscription
                    </button>
                </div>
            </div>
        </form>
        
        <div class="text-center mt-4">
            <p class="text-muted">Déjà transporteur ? <a href="login.php" class="text-primary">Se connecter</a></p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Navigation entre les étapes
    let currentStep = 1;
    
    // Boutons suivant/précédent
    document.querySelectorAll('.next-step').forEach(button => {
        button.addEventListener('click', function() {
            const nextStep = parseInt(this.dataset.next);
            if (validateStep(currentStep)) {
                goToStep(nextStep);
            }
        });
    });
    
    document.querySelectorAll('.prev-step').forEach(button => {
        button.addEventListener('click', function() {
            const prevStep = parseInt(this.dataset.prev);
            goToStep(prevStep);
        });
    });
    
    function goToStep(stepNumber) {
        // Cacher l'étape actuelle
        document.getElementById(`step${currentStep}`).classList.remove('active');
        document.querySelector(`.step-item[data-step="${currentStep}"]`).classList.remove('active');
        
        // Afficher la nouvelle étape
        document.getElementById(`step${stepNumber}`).classList.add('active');
        document.querySelector(`.step-item[data-step="${stepNumber}"]`).classList.add('active');
        
        // Marquer les étapes précédentes comme complétées
        for (let i = 1; i < stepNumber; i++) {
            document.querySelector(`.step-item[data-step="${i}"]`).classList.add('completed');
        }
        
        currentStep = stepNumber;
    }
    
    function validateStep(step) {
        let isValid = true;
        
        if (step === 1) {
            const requiredFields = document.querySelectorAll('#step1 .form-control-enhanced[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            const email = document.getElementById('email');
            if (email.value && !isValidEmail(email.value)) {
                email.classList.add('is-invalid');
                isValid = false;
            }
            
            const password = document.getElementById('password');
            const confirm = document.getElementById('password_confirmation');
            if (password.value && password.value.length < 8) {
                password.classList.add('is-invalid');
                isValid = false;
            }
            if (password.value !== confirm.value) {
                confirm.classList.add('is-invalid');
                isValid = false;
            }
        }
        
        if (step === 2) {
            const requiredFields = document.querySelectorAll('#step2 .form-control-enhanced[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
        }
        
        return isValid;
    }
    
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    // Gestion des modes de paiement
    document.querySelectorAll('.payment-method').forEach(method => {
        method.addEventListener('click', function() {
            document.querySelectorAll('.payment-method').forEach(m => {
                m.classList.remove('active');
            });
            
            this.classList.add('active');
            const methodValue = this.dataset.method;
            document.getElementById('mode_paiement').value = methodValue;
        });
    });
    
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const target = this.dataset.target;
            const input = document.querySelector(target);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });
    });
    
    // Form submission validation
    document.getElementById('transporteurForm').addEventListener('submit', function(e) {
        if (!this.elements['conditions'].checked) {
            e.preventDefault();
            alert('Veuillez accepter les conditions générales.');
            return;
        }
        
        if (!document.getElementById('mode_paiement').value) {
            e.preventDefault();
            alert('Veuillez sélectionner un mode de paiement.');
            return;
        }
    });
});
</script>
</body>
</html>