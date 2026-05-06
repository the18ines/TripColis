<?php
require_once 'config.php';

// Déterminer si c'est une inscription client ou transporteur
$is_transporteur = isset($_GET['type']) && $_GET['type'] == 'transporteur';
$is_client = isset($_GET['type']) && $_GET['type'] == 'client';

$page_title = $is_transporteur ? 'Devenir Transporteur' : ($is_client ? 'Inscription Client' : 'Connexion');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - TripColis</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 0;
        }
        
        .auth-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .auth-hero {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #152642 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .auth-form {
            padding: 3rem;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e5e7eb;
            z-index: 0;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }
        
        .step.active {
            background: var(--primary-blue);
            color: white;
        }
        
        .form-step {
            display: none;
        }
        
        .form-step.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-<?php echo $is_transporteur ? '12' : '10'; ?>">
                    <div class="auth-card">
                        <?php if($is_transporteur): ?>
                            <!-- Formulaire transporteur (multi-étapes) -->
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="auth-hero">
                                        <div class="text-center mb-4">
                                            <h2 class="mb-3">
                                                <span style="color: var(--accent-orange);">TRIP</span>COLIS
                                            </h2>
                                            <p>Devenez Transporteur</p>
                                        </div>
                                        
                                        <div class="feature-list">
                                            <div class="feature-item d-flex align-items-center mb-3">
                                                <div class="feature-icon me-3">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Revenus stables</h6>
                                                    <small class="opacity-90">Générez des revenus complémentaires</small>
                                                </div>
                                            </div>
                                            
                                            <div class="feature-item d-flex align-items-center mb-3">
                                                <div class="feature-icon me-3">
                                                    <i class="fas fa-shield-alt"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Assurance incluse</h6>
                                                    <small class="opacity-90">Protection pendant vos missions</small>
                                                </div>
                                            </div>
                                            
                                            <div class="feature-item d-flex align-items-center mb-3">
                                                <div class="feature-icon me-3">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Horaires flexibles</h6>
                                                    <small class="opacity-90">Travaillez à votre rythme</small>
                                                </div>
                                            </div>
                                            
                                            <div class="feature-item d-flex align-items-center">
                                                <div class="feature-icon me-3">
                                                    <i class="fas fa-headset"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Support 24/7</h6>
                                                    <small class="opacity-90">Assistance technique permanente</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4 text-center">
                                            <p class="mb-3">Déjà membre ?</p>
                                            <a href="auth.php" class="btn btn-outline-light w-100">
                                                <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-7">
                                    <div class="auth-form">
                                        <h3 class="mb-4">Inscription Transporteur</h3>
                                        
                                        <?php if(isset($_SESSION['error'])): ?>
                                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                                        <?php endif; ?>
                                        
                                        <form method="POST" action="process.php" enctype="multipart/form-data" id="transporteurForm">
                                            <!-- Step Indicator -->
                                            <div class="step-indicator mb-4">
                                                <div class="step active" data-step="1">1</div>
                                                <div class="step" data-step="2">2</div>
                                                <div class="step" data-step="3">3</div>
                                            </div>
                                            
                                            <!-- Étape 1: Informations personnelles -->
                                            <div class="form-step active" id="step1">
                                                <h5 class="mb-3">Informations Personnelles</h5>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Nom *</label>
                                                        <input type="text" class="form-control" name="nom" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Prénom *</label>
                                                        <input type="text" class="form-control" name="prenom" required>
                                                    </div>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Email *</label>
                                                        <input type="email" class="form-control" name="email" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Téléphone *</label>
                                                        <input type="tel" class="form-control" name="telephone" required>
                                                    </div>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Ville *</label>
                                                        <select class="form-select" name="ville" required>
                                                            <option value="">Choisir</option>
                                                            <option value="douala">Douala</option>
                                                            <option value="yaounde">Yaoundé</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Quartier *</label>
                                                        <input type="text" class="form-control" name="quartier" required>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn-primary" onclick="nextStep(2)">
                                                        Suivant <i class="fas fa-arrow-right ms-2"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Étape 2: Informations moto -->
                                            <div class="form-step" id="step2">
                                                <h5 class="mb-3">Informations Moto</h5>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Marque *</label>
                                                        <input type="text" class="form-control" name="marque_moto" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Modèle *</label>
                                                        <input type="text" class="form-control" name="modele_moto" required>
                                                    </div>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Immatriculation *</label>
                                                        <input type="text" class="form-control" name="immatriculation" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Année</label>
                                                        <input type="number" class="form-control" name="annee_moto" min="2000" max="<?php echo date('Y'); ?>">
                                                    </div>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Mot de passe *</label>
                                                        <div class="input-group">
                                                            <input type="password" class="form-control" name="password" id="password" required>
                                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Confirmation *</label>
                                                        <div class="input-group">
                                                            <input type="password" class="form-control" name="password_confirmation" required>
                                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between">
                                                    <button type="button" class="btn btn-outline-secondary" onclick="prevStep(1)">
                                                        <i class="fas fa-arrow-left me-2"></i> Retour
                                                    </button>
                                                    <button type="button" class="btn btn-primary" onclick="nextStep(3)">
                                                        Suivant <i class="fas fa-arrow-right ms-2"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Étape 3: Documents -->
                                            <div class="form-step" id="step3">
                                                <h5 class="mb-3">Documents Requis</h5>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Carte Grise *</label>
                                                    <input type="file" class="form-control" name="carte_grise" accept=".pdf,.jpg,.jpeg,.png" required>
                                                    <small class="text-muted">Format PDF, JPG, PNG (Max 5MB)</small>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Permis de conduire *</label>
                                                    <input type="file" class="form-control" name="permis" accept=".pdf,.jpg,.jpeg,.png" required>
                                                </div>
                                                
                                                <div class="mb-4">
                                                    <label class="form-label">CNI *</label>
                                                    <input type="file" class="form-control" name="cni" accept=".pdf,.jpg,.jpeg,.png" required>
                                                </div>
                                                
                                                <div class="form-check mb-4">
                                                    <input class="form-check-input" type="checkbox" id="conditions" required>
                                                    <label class="form-check-label">
                                                        J'accepte les conditions générales
                                                    </label>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between">
                                                    <button type="button" class="btn btn-outline-secondary" onclick="prevStep(2)">
                                                        <i class="fas fa-arrow-left me-2"></i> Retour
                                                    </button>
                                                    <button type="submit" class="btn btn-success" name="register_transporteur">
                                                        <i class="fas fa-check me-2"></i> Finaliser l'inscription
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                        <?php elseif($is_client): ?>
                            <!-- Formulaire client -->
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="auth-hero">
                                        <div class="text-center mb-4">
                                            <h2 class="mb-3">
                                                <span style="color: var(--accent-orange);">TRIP</span>COLIS
                                            </h2>
                                            <p>Inscription Client</p>
                                        </div>
                                        
                                        <div class="feature-list">
                                            <div class="feature-item d-flex align-items-center mb-3">
                                                <div class="feature-icon me-3">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Gain de temps</h6>
                                                    <small class="opacity-90">Plus besoin de faire la queue</small>
                                                </div>
                                            </div>
                                            
                                            <div class="feature-item d-flex align-items-center mb-3">
                                                <div class="feature-icon me-3">
                                                    <i class="fas fa-shield-alt"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Service sécurisé</h6>
                                                    <small class="opacity-90">Transporteurs vérifiés</small>
                                                </div>
                                            </div>
                                            
                                            <div class="feature-item d-flex align-items-center mb-3">
                                                <div class="feature-icon me-3">
                                                    <i class="fas fa-bolt"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Livraison rapide</h6>
                                                    <small class="opacity-90">En moins de 30 minutes</small>
                                                </div>
                                            </div>
                                            
                                            <div class="feature-item d-flex align-items-center">
                                                <div class="feature-icon me-3">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Partout à Douala</h6>
                                                    <small class="opacity-90">Tous les quartiers couverts</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4 text-center">
                                            <p class="mb-3">Déjà membre ?</p>
                                            <a href="auth.php" class="btn btn-outline-light w-100">
                                                <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-7">
                                    <div class="auth-form">
                                        <h3 class="mb-4">Inscription Client</h3>
                                        
                                        <?php if(isset($_SESSION['error'])): ?>
                                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                                        <?php endif; ?>
                                        
                                        <form method="POST" action="process.php">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Prénom *</label>
                                                    <input type="text" class="form-control" name="prenom" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Nom *</label>
                                                    <input type="text" class="form-control" name="nom" required>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Email *</label>
                                                    <input type="email" class="form-control" name="email" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Téléphone *</label>
                                                    <input type="tel" class="form-control" name="telephone" required>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Ville *</label>
                                                    <select class="form-select" name="ville" required>
                                                        <option value="">Choisir</option>
                                                        <option value="douala">Douala</option>
                                                        <option value="yaounde">Yaoundé</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Quartier *</label>
                                                    <input type="text" class="form-control" name="quartier" required>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Adresse complète</label>
                                                <textarea class="form-control" name="adresse" rows="2"></textarea>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Mot de passe *</label>
                                                    <input type="password" class="form-control" name="password" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Confirmation *</label>
                                                    <input type="password" class="form-control" name="password_confirmation" required>
                                                </div>
                                            </div>
                                            
                                            <div class="form-check mb-4">
                                                <input class="form-check-input" type="checkbox" id="conditions" required>
                                                <label class="form-check-label">
                                                    J'accepte les conditions générales
                                                </label>
                                            </div>
                                            
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary btn-lg" name="register_client">
                                                    <i class="fas fa-user-plus me-2"></i>Créer mon compte
                                                </button>
                                            </div>
                                        </form>
                                        
                                        <div class="text-center mt-4">
                                            <p class="text-muted mb-0">
                                                Déjà membre ? <a href="auth.php">Se connecter</a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        <?php else: ?>
                            <!-- Formulaire de connexion -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="auth-hero">
                                        <div class="text-center mb-4">
                                            <h1 class="mb-3">
                                                <span style="color: var(--accent-orange);">TRIP</span>COLIS
                                            </h1>
                                            <p>Retirez vos colis sans vous déplacer</p>
                                        </div>
                                        
                                        <div class="text-center mt-5">
                                            <h4 class="mb-3">Nouveau sur TripColis ?</h4>
                                            <a href="auth.php?type=client" class="btn btn-outline-light w-100 mb-3">
                                                <i class="fas fa-user me-2"></i>Devenir Client
                                            </a>
                                            <a href="auth.php?type=transporteur" class="btn btn-outline-light w-100">
                                                <i class="fas fa-motorcycle me-2"></i>Devenir Transporteur
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="auth-form">
                                        <h3 class="mb-4">Connexion</h3>
                                        
                                        <?php if(isset($_SESSION['error'])): ?>
                                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                                        <?php endif; ?>
                                        
                                        <?php if(isset($_SESSION['success'])): ?>
                                            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                                        <?php endif; ?>
                                        
                                        <form method="POST" action="process.php">
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="email" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Mot de passe</label>
                                                <input type="password" class="form-control" name="password" required>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="remember">
                                                    <label class="form-check-label" for="remember">
                                                        Se souvenir de moi
                                                    </label>
                                                </div>
                                                <a href="#" class="text-primary">Mot de passe oublié ?</a>
                                            </div>
                                            
                                            <div class="d-grid mb-3">
                                                <button type="submit" class="btn btn-primary btn-lg" name="login">
                                                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                                                </button>
                                            </div>
                                        </form>
                                        
                                        <div class="text-center">
                                            <p class="text-muted mb-3">Ou connectez-vous avec</p>
                                            <button class="btn btn-outline-secondary w-100 mb-2">
                                                <i class="fab fa-google me-2"></i>Google
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if($is_transporteur): ?>
        let currentStep = 1;
        
        function nextStep(step) {
            if (!validateStep(currentStep)) return;
            
            document.getElementById(`step${currentStep}`).classList.remove('active');
            document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');
            
            document.getElementById(`step${step}`).classList.add('active');
            document.querySelector(`.step[data-step="${step}"]`).classList.add('active');
            
            currentStep = step;
        }
        
        function prevStep(step) {
            document.getElementById(`step${currentStep}`).classList.remove('active');
            document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');
            
            document.getElementById(`step${step}`).classList.add('active');
            document.querySelector(`.step[data-step="${step}"]`).classList.add('active');
            
            currentStep = step;
        }
        
        function validateStep(step) {
            const form = document.getElementById('transporteurForm');
            let isValid = true;
            
            if (step === 1) {
                const required = ['nom', 'prenom', 'email', 'telephone', 'ville', 'quartier'];
                required.forEach(field => {
                    const input = form.elements[field];
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
            }
            
            if (step === 2) {
                const required = ['marque_moto', 'modele_moto', 'immatriculation', 'password'];
                required.forEach(field => {
                    const input = form.elements[field];
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
                
                if (form.elements['password'].value !== form.elements['password_confirmation'].value) {
                    isValid = false;
                    alert('Les mots de passe ne correspondent pas');
                }
            }
            
            return isValid;
        }
        
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }
        <?php endif; ?>
    </script>
</body>
</html>