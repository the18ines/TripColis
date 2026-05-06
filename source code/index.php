<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TripColis - Retrait de Colis par Mototaxi à Douala et Yaoundé</title>
    <meta name="description" content="Service professionnel de retrait et livraison de colis par mototaxi. Express Union, United Express à Douala et Yaoundé. Rapide, sécurisé, fiable.">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- AOS Animation -->
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    
    <link rel="stylesheet" href="style.css">
    
    <style>
        :root {
            --primary: #170B87;
            --primary-light: #8385acff;
            --secondary: #F97316;
            --secondary-light: #977f67ff;
            --dark: #1a1a2e;
            --light: #e3e6eaff;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 20px;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 40px rgba(0,0,0,0.15);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-sm);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.8rem;
            color: var(--primary) !important;
        }

        .navbar-brand span {
            color: var(--secondary);
        }

        .hero-section {
            background: linear-gradient(135deg, rgba(23, 11, 135, 0.95) 0%, rgba(9, 9, 121, 0.95) 50%, rgba(0, 4, 40, 0.95) 100%);
            color: white;
            padding: 8rem 0 4rem;
            position: relative;
            overflow: hidden;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #ffffff 0%, #F97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-feature {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius-lg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .section {
            padding: 5rem 0;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
            text-align: center;
            position: relative;
        }

        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            margin: 1rem auto;
            border-radius: 2px;
        }

        .feature-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2.5rem 2rem;
            text-align: center;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-light), #f0f1ff);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: var(--primary);
            font-size: 2rem;
        }

        .cta-section {
            background: linear-gradient(135deg, var(--primary) 0%, #0c0759 100%);
            color: white;
            border-radius: var(--radius-lg);
            padding: 4rem;
            position: relative;
            overflow: hidden;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: var(--radius-lg);
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <span>TRIP</span>COLIS
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#comment">Comment ça marche</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#zones">Zones desservies</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">À propos</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            Espace membre
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="auth.php?type=client">Espace Client</a>
                            <a class="dropdown-item" href="auth.php?type=transporteur">Espace Transporteur</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="auth.php">Connexion</a>
                        </div>
                    </li>
                </ul>
                
                <a href="auth.php?type=client" class="btn btn-primary-custom ms-lg-3">
                    <i class="fas fa-box me-2"></i>Retirer un colis
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h1 class="hero-title" data-aos="fade-up">
                        Retrait de colis<br>sans vous déplacer
                    </h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                        Un service professionnel de mototaxis vérifiés qui récupèrent vos colis dans les agences 
                        Express Union, United Express et vous les livrent où vous voulez, quand vous voulez.
                    </p>
                    
                    <div class="mb-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="hero-feature">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Service 100% sécurisé</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="hero-feature">
                                    <i class="fas fa-bolt"></i>
                                    <span>Livraison en 30 min</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="hero-feature">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Toute la ville couverte</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="hero-feature">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span>Paiement sécurisé</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex flex-wrap gap-3" data-aos="fade-up" data-aos-delay="300">
                        <a href="auth.php?type=client" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-box me-2"></i>Retirer un colis
                        </a>
                        <a href="#comment" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-play-circle me-2"></i>Comment ça marche
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="400">
                    <div class="hero-map-container bg-white rounded-4 p-4 shadow-lg">
                        <div id="hero-map" style="height: 400px; border-radius: var(--radius-md);"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Comment ça marche -->
    <section id="comment" class="section bg-light">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Comment fonctionne TripColis</h2>
            <p class="text-center text-muted mb-5" data-aos="fade-up" data-aos-delay="100">
                Trois étapes simples pour récupérer vos colis sans vous déplacer
            </p>
            
            <div class="row g-4">
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <h3 class="h4 mb-3">1. Créez votre demande</h3>
                        <p class="text-muted">
                            Indiquez l'agence, votre adresse de livraison et les détails du colis sur notre plateforme sécurisée.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-motorcycle"></i>
                        </div>
                        <h3 class="h4 mb-3">2. Un transporteur vérifié intervient</h3>
                        <p class="text-muted">
                            Un mototaxi certifié TripColis récupère votre colis et vous envoie une confirmation en temps réel.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h3 class="h4 mb-3">3. Recevez en toute sécurité</h3>
                        <p class="text-muted">
                            Votre colis vous est livré à l'adresse indiquée. Vous payez seulement après réception.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Zones desservies -->
    <section id="zones" class="section">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Zones desservies</h2>
            <p class="text-center text-muted mb-5" data-aos="fade-up" data-aos-delay="100">
                Nous couvrons les principaux quartiers de Douala et Yaoundé
            </p>
            
            <div class="row">
                <div class="col-lg-6 mb-4" data-aos="fade-up">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h4 class="mb-4 text-primary">
                                <i class="fas fa-city me-2"></i>Douala
                            </h4>
                            <div class="row g-2">
                                <?php 
                                $zonesDouala = ['Akwa', 'Bonabéri', 'Bépanda', 'Makepe', 'Deido', 'New Bell', 'Bonamoussadi', 'Logpom', 'Bali', 'Ndogbong'];
                                foreach($zonesDouala as $zone): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-2 border rounded mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span><?php echo $zone; ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h4 class="mb-4 text-primary">
                                <i class="fas fa-city me-2"></i>Yaoundé
                            </h4>
                            <div class="row g-2">
                                <?php 
                                $zonesYaounde = ['Bastos', 'Mvog-Ada', 'Mvog-Betsi', 'Ekounou', 'Nkolbisson', 'Mendong', 'Odza', 'Efoulan'];
                                foreach($zonesYaounde as $zone): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-2 border rounded mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span><?php echo $zone; ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistiques -->
    <section class="section bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-up">
                    <div class="text-center">
                        <h2 class="display-4 fw-bold text-primary">1250+</h2>
                        <p class="text-muted">Colis livrés</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-center">
                        <h2 class="display-4 fw-bold text-primary">85+</h2>
                        <p class="text-muted">Transporteurs agréés</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-center">
                        <h2 class="display-4 fw-bold text-primary">24+</h2>
                        <p class="text-muted">Quartiers desservis</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-center">
                        <h2 class="display-4 fw-bold text-primary">98%</h2>
                        <p class="text-muted">Satisfaction client</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section">
        <div class="container">
            <div class="cta-section text-center">
                <h2 class="display-5 fw-bold mb-4" data-aos="fade-up">
                    Prêt à simplifier <span class="text-warning">vos retraits de colis</span> ?
                </h2>
                <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                    Rejoignez les milliers de clients qui nous font déjà confiance
                </p>
                
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-center" data-aos="fade-up" data-aos-delay="200">
                    <a href="auth.php?type=client" class="btn btn-light btn-lg px-5">
                        <i class="fas fa-user-plus me-2"></i>Devenir Client
                    </a>
                    <a href="auth.php?type=transporteur" class="btn btn-outline-light btn-lg px-5">
                        <i class="fas fa-motorcycle me-2"></i>Devenir Transporteur
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h4 class="mb-4">
                        <span class="text-warning">TRIP</span>COLIS
                    </h4>
                    <p>Service professionnel de retrait et livraison de colis par mototaxi à Douala et Yaoundé.</p>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="mb-4">Liens rapides</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#home" class="text-white-50 text-decoration-none">Accueil</a></li>
                        <li class="mb-2"><a href="#comment" class="text-white-50 text-decoration-none">Comment ça marche</a></li>
                        <li class="mb-2"><a href="#zones" class="text-white-50 text-decoration-none">Zones desservies</a></li>
                        <li><a href="#about" class="text-white-50 text-decoration-none">À propos</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="mb-4">Contact</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-phone me-2"></i>+237 699 05 45 08
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2"></i>contact@tripcolis.cm
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt me-2"></i>Douala, Cameroun
                        </li>
                    </ul>
                </div>
                
                <div class="col-lg-3 mb-4">
                    <h5 class="mb-4">Suivez-nous</h5>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin fa-lg"></i></a>
                    </div>
                </div>
            </div>
            
            <hr class="bg-white-50">
            
            <div class="text-center pt-3">
                <p class="mb-0">&copy; 2024 TripColis. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
        
        // Initialize Map
        function initMap() {
            const map = L.map('hero-map').setView([4.0511, 9.7679], 12);
            
            L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            
            // Add markers
            const markers = [
                [4.046, 9.704, 'Express Union Akwa'],
                [4.075, 9.666, 'United Express Bonabéri'],
                [4.031, 9.731, 'Agence Bépanda'],
                [4.061, 9.718, 'Express Union Deido']
            ];
            
            markers.forEach(marker => {
                L.marker(marker)
                    .addTo(map)
                    .bindPopup(marker[2]);
            });
        }
        
        // Initialize map when page loads
        document.addEventListener('DOMContentLoaded', initMap);
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>