<?php
include 'includes/config.php';
include 'includes/auth.php';

// Si l'utilisateur est déjà connecté, rediriger selon son rôle
if (estConnecte()) {
    redirigerSelonRole();
}

$pageTitle = 'Inscription - TripColis';
$error = '';
$success = '';

// Récupérer le rôle depuis l'URL ou le formulaire
$role = isset($_GET['role']) ? $_GET['role'] : (isset($_POST['role']) ? $_POST['role'] : 'client');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $data = [
        'nom' => $_POST['nom'],
        'prenom' => $_POST['prenom'],
        'email' => $_POST['email'],
        'telephone' => $_POST['telephone'],
        'password' => $_POST['password'],
        'role' => $role,
        'ville' => $_POST['ville'] ?? '',
        'quartier' => $_POST['quartier'] ?? '',
        'adresse' => $_POST['adresse'] ?? ''
    ];
    
    // Validation des champs
    if (empty($data['nom']) || empty($data['prenom']) || empty($data['email']) || empty($data['password'])) {
        $error = "Veuillez remplir tous les champs obligatoires";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide";
    } elseif (strlen($data['password']) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères";
    } else {
        // Vérifier si l'email existe déjà
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$data['email']]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Cet email est déjà utilisé";
        } else {
            if (inscrireUtilisateur($data)) {
                // Pour le transporteur, créer l'entrée dans la table transporteurs
                if ($role === 'transporteur') {
                    $userId = $pdo->lastInsertId();
                    $matricule = 'TRC' . str_pad($userId, 5, '0', STR_PAD_LEFT);
                    
                    $sql = "INSERT INTO transporteurs (user_id, matricule, mode_paiement) VALUES (?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $modePaiement = $_POST['mode_paiement'] ?? 'autre';
                    $stmt->execute([$userId, $matricule, $modePaiement]);
                }
                
                $success = "Compte créé avec succès! Vous pouvez maintenant vous connecter.";
                $role = 'client'; // Réinitialiser pour afficher le formulaire client
            } else {
                $error = "Erreur lors de la création du compte";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .auth-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 500px;
        }
        
        .role-tabs {
            display: flex;
            border-bottom: 2px solid #eee;
            margin-bottom: 30px;
        }
        
        .role-tab {
            flex: 1;
            text-align: center;
            padding: 15px;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .role-tab.active {
            color: #170B87;
            border-bottom-color: #F97316;
        }
        
        .role-tab i {
            display: block;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .role-form {
            display: none;
        }
        
        .role-form.active {
            display: block;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header h3 {
            color: #170B87;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .form-header p {
            color: #6b7280;
        }
        
        .feature-list {
            margin: 20px 0;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .feature-item i {
            color: #F97316;
            margin-right: 10px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="form-header">
                <h2><i class="fas fa-motorcycle me-2" style="color: #170B87;"></i>TripColis</h2>
                <p>Rejoignez notre plateforme de retrait de colis</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?>
                    <br><br>
                    <a href="login.php" class="btn btn-success">
                        <i class="fas fa-sign-in-alt me-2"></i>Se connecter maintenant
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
            <div class="role-tabs">
                <div class="role-tab <?php echo $role === 'client' ? 'active' : ''; ?>" onclick="selectRole('client')">
                    <i class="fas fa-user"></i>
                    <span>Client</span>
                </div>
                <div class="role-tab <?php echo $role === 'transporteur' ? 'active' : ''; ?>" onclick="selectRole('transporteur')">
                    <i class="fas fa-motorcycle"></i>
                    <span>Transporteur</span>
                </div>
            </div>
            
            <!-- Formulaire Client -->
            <form id="clientForm" class="role-form <?php echo $role === 'client' ? 'active' : ''; ?>" method="POST" action="">
                <input type="hidden" name="role" value="client">
                <input type="hidden" name="register" value="1">
                
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Retrait de colis en 30 minutes</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Transporteurs vérifiés</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Paiement sécurisé</span>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Prénom *</label>
                        <input type="text" name="prenom" class="form-control" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Téléphone *</label>
                    <input type="tel" name="telephone" class="form-control" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ville</label>
                        <select name="ville" class="form-select">
                            <option value="">Choisir une ville</option>
                            <option value="douala">Douala</option>
                            <option value="yaounde">Yaoundé</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Quartier</label>
                        <input type="text" name="quartier" class="form-control">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Mot de passe *</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Confirmer le mot de passe *</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="conditions" required>
                    <label class="form-check-label">
                        J'accepte les <a href="#" class="text-primary">conditions générales</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="fas fa-user-plus me-2"></i>Créer mon compte client
                </button>
                
                <div class="text-center mt-3">
                    <p class="text-muted">Déjà inscrit ? <a href="login.php">Se connecter</a></p>
                </div>
            </form>
            
            <!-- Formulaire Transporteur -->
            <form id="transporteurForm" class="role-form <?php echo $role === 'transporteur' ? 'active' : ''; ?>" method="POST" action="">
                <input type="hidden" name="role" value="transporteur">
                <input type="hidden" name="register" value="1">
                
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Revenus complémentaires</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Horaires flexibles</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Formation et support</span>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Prénom *</label>
                        <input type="text" name="prenom" class="form-control" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Téléphone *</label>
                    <input type="tel" name="telephone" class="form-control" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ville *</label>
                        <select name="ville" class="form-select" required>
                            <option value="">Choisir une ville</option>
                            <option value="douala">Douala</option>
                            <option value="yaounde">Yaoundé</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Quartier *</label>
                        <input type="text" name="quartier" class="form-control" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Adresse complète</label>
                    <textarea name="adresse" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Mode de paiement préféré</label>
                    <select name="mode_paiement" class="form-select">
                        <option value="OM">Orange Money</option>
                        <option value="MoMo">MTN Mobile Money</option>
                        <option value="Esp">Espèces</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Mot de passe *</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Confirmer le mot de passe *</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="conditions" required>
                    <label class="form-check-label">
                        J'accepte les <a href="#" class="text-primary">conditions générales</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-warning w-100 py-2">
                    <i class="fas fa-motorcycle me-2"></i>Devenir transporteur
                </button>
                
                <div class="text-center mt-3">
                    <p class="text-muted">Déjà inscrit ? <a href="login.php">Se connecter</a></p>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function selectRole(role) {
            // Activer le bon tab
            document.querySelectorAll('.role-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.role-tab').forEach(tab => {
                if (tab.textContent.includes(role === 'client' ? 'Client' : 'Transporteur')) {
                    tab.classList.add('active');
                }
            });
            
            // Afficher le bon formulaire
            document.querySelectorAll('.role-form').forEach(form => {
                form.classList.remove('active');
            });
            document.getElementById(role + 'Form').classList.add('active');
            
            // Mettre à jour l'URL sans recharger
            history.replaceState(null, null, '?role=' + role);
        }
        
        // Validation des mots de passe
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const password = this.querySelector('input[name="password"]');
                const confirm = this.querySelector('input[name="password_confirmation"]');
                
                if (password.value !== confirm.value) {
                    e.preventDefault();
                    alert('Les mots de passe ne correspondent pas');
                    return;
                }
                
                if (password.value.length < 6) {
                    e.preventDefault();
                    alert('Le mot de passe doit contenir au moins 6 caractères');
                    return;
                }
            });
        });
    </script>
</body>
</html>