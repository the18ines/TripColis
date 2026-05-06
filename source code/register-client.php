<?php
include 'includes/config.php';
include 'includes/auth.php';

// Si l'utilisateur est déjà connecté, rediriger selon son rôle
if (estConnecte()) {
    redirigerSelonRole();
}

$pageTitle = 'Inscription Client - TripColis';
$pageDescription = 'Inscrivez-vous comme client TripColis et bénéficiez de nos services de retrait de colis par mototaxi';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nom' => $_POST['nom'],
        'prenom' => $_POST['prenom'],
        'email' => $_POST['email'],
        'telephone' => $_POST['telephone'],
        'password' => $_POST['password'],
        'role' => 'client',
        'ville' => $_POST['ville'] ?? '',
        'quartier' => $_POST['quartier'] ?? '',
        'adresse' => $_POST['adresse'] ?? ''
    ];
    
    if (inscrireUtilisateur($data)) {
        // Connecter automatiquement l'utilisateur
        if (connecterUtilisateur($data['email'], $data['password'])) {
            header('Location: dashboard_client.php?welcome=1');
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
        .register-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }
        
        .register-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            display: flex;
            min-height: 600px;
        }
        
        .register-hero {
            background: linear-gradient(135deg, var(--primary) 0%, #0c0759 100%);
            color: white;
            padding: 3rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .register-form {
            flex: 1.5;
            padding: 3rem;
            max-width: 600px;
        }
        
        .feature-list {
            margin: 2rem 0;
        }
        
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .feature-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .password-strength {
            margin-top: 0.5rem;
        }
        
        .strength-bar {
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            margin-top: 0.25rem;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .strength-weak { background: #ef4444; width: 25%; }
        .strength-medium { background: #f59e0b; width: 50%; }
        .strength-good { background: #3b82f6; width: 75%; }
        .strength-strong { background: #10b981; width: 100%; }
        
        .form-step {
            display: none;
        }
        
        .form-step.active {
            display: block;
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
            background: var(--primary);
            color: white;
        }
        
        .step.completed {
            background: var(--secondary);
            color: white;
        }
        
        @media (max-width: 992px) {
            .register-card {
                flex-direction: column;
            }
            
            .register-hero {
                padding: 2rem;
            }
            
            .register-form {
                padding: 2rem;
            }
        }
        
        :root {
            --primary: #170B87;
            --secondary: #F97316;
        }
    </style>
</head>
<body>
    <div class="register-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <div class="register-card">
                        <!-- Hero Section -->
                        <div class="register-hero">
                            <div>
                                <div class="text-center mb-4">
                                    <h2 class="mb-3">
                                        <span style="color: #F97316">Trip</span>Colis
                                    </h2>
                                    <p class="opacity-90">Retirez vos colis sans vous déplacer</p>
                                </div>
                                
                                <div class="feature-list">
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">Gain de temps</h5>
                                            <p class="opacity-90 mb-0">Plus besoin de faire la queue</p>
                                        </div>
                                    </div>
                                    
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <i class="fas fa-shield-alt"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">Service sécurisé</h5>
                                            <p class="opacity-90 mb-0">Transporteurs vérifiés</p>
                                        </div>
                                    </div>
                                    
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <i class="fas fa-bolt"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">Livraison rapide</h5>
                                            <p class="opacity-90 mb-0">En moins de 30 minutes</p>
                                        </div>
                                    </div>
                                    
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">Partout à Douala</h5>
                                            <p class="opacity-90 mb-0">Tous les quartiers couverts</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <p class="mb-3">Déjà membre ?</p>
                                <a href="login.php" class="btn btn-outline-light w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                                </a>
                            </div>
                        </div>

                        <!-- Formulaire d'inscription -->
                        <div class="register-form">
                            <div class="text-center mb-4">
                                <h3 class="mb-2">Créer un compte client</h3>
                                <p class="text-muted">Rejoignez des milliers de clients satisfaits</p>
                            </div>

                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger">
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="" id="clientRegisterForm">
                                <!-- Step Indicator -->
                                <div class="step-indicator mb-4">
                                    <div class="step active" data-step="1">1</div>
                                    <div class="step" data-step="2">2</div>
                                    <div class="step" data-step="3">3</div>
                                </div>

                                <!-- Step 1: Personal Info -->
                                <div class="form-step active" id="step1">
                                    <h4 class="mb-4">Informations personnelles</h4>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="prenom" class="form-label">Prénom *</label>
                                            <input type="text" class="form-control" id="prenom" name="prenom" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="nom" class="form-label">Nom *</label>
                                            <input type="text" class="form-control" id="nom" name="nom" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email *</label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="telephone" class="form-label">Téléphone *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">+237</span>
                                                <input type="tel" class="form-control" id="telephone" name="telephone" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-primary" onclick="nextStep(2)">
                                            Suivant <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Step 2: Location -->
                                <div class="form-step" id="step2">
                                    <h4 class="mb-4">Votre localisation</h4>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="ville" class="form-label">Ville *</label>
                                            <select class="form-select" id="ville" name="ville" required>
                                                <option value="">Choisissez votre ville</option>
                                                <option value="douala">Douala</option>
                                                <option value="yaounde">Yaoundé</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="quartier" class="form-label">Quartier *</label>
                                            <select class="form-select" id="quartier" name="quartier" required>
                                                <option value="">Choisissez votre quartier</option>
                                                <option value="akwa">Akwa</option>
                                                <option value="bonaberi">Bonabéri</option>
                                                <option value="bepanda">Bépanda</option>
                                                <option value="makepe">Makepe</option>
                                                <option value="deido">Deido</option>
                                                <option value="new-bell">New Bell</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="adresse" class="form-label">Adresse complète</label>
                                        <textarea class="form-control" id="adresse" name="adresse" rows="2"></textarea>
                                        <small class="text-muted">Pour des livraisons précises</small>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-secondary" onclick="prevStep(1)">
                                            <i class="fas fa-arrow-left me-2"></i>Retour
                                        </button>
                                        <button type="button" class="btn btn-primary" onclick="nextStep(3)">
                                            Suivant <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Step 3: Security -->
                                <div class="form-step" id="step3">
                                    <h4 class="mb-4">Sécurité du compte</h4>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Mot de passe *</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="password-strength">
                                            <small class="text-muted">Force du mot de passe : <span id="passwordStrengthText">Faible</span></small>
                                            <div class="strength-bar">
                                                <div class="strength-fill" id="passwordStrengthBar"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="password_confirmation" class="form-label">Confirmer le mot de passe *</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-check mb-4">
                                        <input class="form-check-input" type="checkbox" id="conditions" name="conditions" required>
                                        <label class="form-check-label" for="conditions">
                                            J'accepte les <a href="#" class="text-primary">conditions générales</a> et la 
                                            <a href="#" class="text-primary">politique de confidentialité</a>
                                        </label>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-secondary" onclick="prevStep(2)">
                                            <i class="fas fa-arrow-left me-2"></i>Retour
                                        </button>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="fas fa-user-plus me-2"></i>Créer mon compte
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="text-center mt-4">
                                <p class="text-muted">
                                    Déjà membre ? <a href="login.php" class="text-primary">Se connecter</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let currentStep = 1;
        
        function nextStep(step) {
            // Validate current step
            if (!validateStep(currentStep)) {
                return;
            }
            
            // Hide current step
            document.getElementById(`step${currentStep}`).classList.remove('active');
            document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');
            
            // Show next step
            document.getElementById(`step${step}`).classList.add('active');
            document.querySelector(`.step[data-step="${step}"]`).classList.add('active');
            
            currentStep = step;
        }
        
        function prevStep(step) {
            // Hide current step
            document.getElementById(`step${currentStep}`).classList.remove('active');
            document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');
            
            // Show previous step
            document.getElementById(`step${step}`).classList.add('active');
            document.querySelector(`.step[data-step="${step}"]`).classList.add('active');
            
            currentStep = step;
        }
        
        function validateStep(step) {
            const form = document.getElementById('clientRegisterForm');
            let isValid = true;
            
            switch(step) {
                case 1:
                    const requiredFields = ['prenom', 'nom', 'email', 'telephone'];
                    requiredFields.forEach(field => {
                        const input = form.elements[field];
                        if (!input.value.trim()) {
                            isValid = false;
                            input.classList.add('is-invalid');
                        } else {
                            input.classList.remove('is-invalid');
                        }
                    });
                    
                    // Email validation
                    const email = form.elements['email'];
                    if (email.value && !validateEmail(email.value)) {
                        isValid = false;
                        email.classList.add('is-invalid');
                    }
                    break;
                    
                case 2:
                    if (!form.elements['ville'].value || !form.elements['quartier'].value) {
                        isValid = false;
                        showToast('Veuillez sélectionner votre ville et quartier', 'warning');
                    }
                    break;
            }
            
            return isValid;
        }
        
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentNode.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
        
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
        // Password strength checker
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthBar = document.getElementById('passwordStrengthBar');
            const strengthText = document.getElementById('passwordStrengthText');
            
            let strength = 0;
            let text = 'Faible';
            let className = 'strength-weak';
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/\d/)) strength++;
            if (password.match(/[^a-zA-Z\d]/)) strength++;
            
            switch(strength) {
                case 1:
                    text = 'Faible';
                    className = 'strength-weak';
                    break;
                case 2:
                    text = 'Moyen';
                    className = 'strength-medium';
                    break;
                case 3:
                    text = 'Bon';
                    className = 'strength-good';
                    break;
                case 4:
                    text = 'Fort';
                    className = 'strength-strong';
                    break;
            }
            
            strengthBar.className = 'strength-fill ' + className;
            strengthText.textContent = text;
        });
        
        // Form submission
        document.getElementById('clientRegisterForm').addEventListener('submit', function(e) {
            if (!this.elements['conditions'].checked) {
                e.preventDefault();
                alert('Veuillez accepter les conditions générales');
                return;
            }
            
            if (document.getElementById('password').value !== 
                document.getElementById('password_confirmation').value) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas');
                return;
            }
            
            // Show loading
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Création en cours...';
        });
        
        function showToast(message, type) {
            // Simple alert for now, can be replaced with toast library
            alert(message);
        }
    </script>
</body>
</html>