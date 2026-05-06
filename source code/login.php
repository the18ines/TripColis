<?php
include 'includes/config.php';
include 'includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Connexion
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        if (connecterUtilisateur($email, $password)) {
            redirigerSelonRole();
        } else {
            $error = "Email ou mot de passe incorrect";
        }
    } elseif (isset($_POST['register'])) {
        // Inscription
        $data = [
            'nom' => $_POST['nom'],
            'prenom' => $_POST['prenom'],
            'email' => $_POST['email'],
            'telephone' => $_POST['telephone'],
            'password' => $_POST['password'],
            'role' => $_POST['role'],
            'cni' => $_POST['cni'] ?? '',
            'quartier' => $_POST['quartier'] ?? ''
        ];
        
        if (inscrireUtilisateur($data)) {
            // Si c'est un transporteur, créer l'entrée dans la table transporteurs
            if ($data['role'] === 'transporteur') {
                $userId = $pdo->lastInsertId();
                $matricule = 'TRC' . str_pad($userId, 5, '0', STR_PAD_LEFT);
                
                $sql = "INSERT INTO transporteurs (user_id, matricule, mode_paiement) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$userId, $matricule, $_POST['mode_paiement'] ?? 'autre']);
            }
            
            $success = "Compte créé avec succès! Veuillez vous connecter.";
        } else {
            $error = "Erreur lors de la création du compte";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TripColis - Connexion/Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .auth-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #170B87 0%, #0c0759 100%);
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
        
        .auth-tabs {
            display: flex;
            border-bottom: 2px solid #eee;
            margin-bottom: 30px;
        }
        
        .auth-tab {
            flex: 1;
            text-align: center;
            padding: 15px;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .auth-tab.active {
            color: #170B87;
            border-bottom-color: #F97316;
        }
        
        .auth-form {
            display: none;
        }
        
        .auth-form.active {
            display: block;
        }
        
        .role-selection {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .role-btn {
            flex: 1;
            padding: 15px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        
        .role-btn.active {
            border-color: #170B87;
            background: rgba(23, 11, 135, 0.1);
            color: #170B87;
        }
        
        .role-btn i {
            display: block;
            font-size: 24px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="text-center mb-4">
                <h2><i class="fas fa-motorcycle me-2 text-primary"></i>TripColis</h2>
                <p class="text-muted">Plateforme de retrait de colis par mototaxi</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="auth-tabs">
                <div class="auth-tab active" onclick="showForm('login')">Connexion</div>
                <div class="auth-tab" onclick="showForm('register')">Inscription</div>
            </div>
            
            <!-- Formulaire de connexion -->
            <form id="loginForm" class="auth-form active" method="POST">
                <input type="hidden" name="login" value="1">
                
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Se souvenir de moi</label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                </button>
                
                <div class="text-center mt-3">
                    <a href="#" class="text-decoration-none">Mot de passe oublié ?</a>
                </div>
            </form>
            
            <!-- Formulaire d'inscription -->
            <form id="registerForm" class="auth-form" method="POST">
                <input type="hidden" name="register" value="1">
                
                <div class="role-selection mb-4">
                    <div class="role-btn active" data-role="client" onclick="selectRole('client')">
                        <i class="fas fa-user"></i>
                        <span>Client</span>
                    </div>
                    <div class="role-btn" data-role="transporteur" onclick="selectRole('transporteur')">
                        <i class="fas fa-motorcycle"></i>
                        <span>Transporteur</span>
                    </div>
                </div>
                
                <input type="hidden" name="role" id="selectedRole" value="client">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-control" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Téléphone</label>
                    <input type="tel" name="telephone" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                
                <div class="mb-3" id="cniField">
                    <label class="form-label">Numéro CNI</label>
                    <input type="text" name="cni" class="form-control">
                </div>
                
                <div class="mb-3" id="quartierField">
                    <label class="form-label">Quartier</label>
                    <input type="text" name="quartier" class="form-control">
                </div>
                
                <div class="mb-3" id="modePaiementField" style="display: none;">
                    <label class="form-label">Mode de paiement préféré</label>
                    <select name="mode_paiement" class="form-select">
                        <option value="OM">Orange Money</option>
                        <option value="MoMo">MTN Mobile Money</option>
                        <option value="Esp">Espèces</option>
                        <option value="banque">Virement bancaire</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="fas fa-user-plus me-2"></i>Créer mon compte
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function showForm(formId) {
            // Activer le bon tab
            document.querySelectorAll('.auth-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelector(`.auth-tab[onclick="showForm('${formId}')"]`).classList.add('active');
            
            // Afficher le bon formulaire
            document.querySelectorAll('.auth-form').forEach(form => {
                form.classList.remove('active');
            });
            document.getElementById(formId + 'Form').classList.add('active');
        }
        
        function selectRole(role) {
            document.querySelectorAll('.role-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`.role-btn[data-role="${role}"]`).classList.add('active');
            
            document.getElementById('selectedRole').value = role;
            
            // Afficher/masquer les champs selon le rôle
            const modePaiementField = document.getElementById('modePaiementField');
            const cniField = document.getElementById('cniField');
            const quartierField = document.getElementById('quartierField');
            
            if (role === 'transporteur') {
                modePaiementField.style.display = 'block';
                cniField.style.display = 'block';
                quartierField.style.display = 'block';
            } else {
                modePaiementField.style.display = 'none';
                cniField.style.display = 'none';
                quartierField.style.display = 'none';
            }
        }
    </script>
</body>
</html>