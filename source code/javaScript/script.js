// script.js - Fonctions JavaScript principales

document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialiser les popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Gestion des toasts
    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    const toastList = toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl);
    });
    
    // Afficher tous les toasts
    toastList.forEach(toast => toast.show());
    
    // Fonction utilitaire pour afficher des toasts
    window.showToast = function(message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();
        
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-bg-${type}" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        
        // Supprimer le toast après disparition
        toastElement.addEventListener('hidden.bs.toast', function () {
            this.remove();
        });
    };
    
    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '1055';
        document.body.appendChild(container);
        return container;
    }
    
    // Gestion des formulaires avec validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Fonction pour confirmer les actions critiques
    window.confirmAction = function(message, callback) {
        if (confirm(message)) {
            if (typeof callback === 'function') {
                callback();
            }
            return true;
        }
        return false;
    };
    
    // Gestion des téléchargements de fichiers
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            const fileName = this.files[0]?.name || 'Aucun fichier sélectionné';
            const label = this.nextElementSibling?.querySelector('.file-name') || 
                         this.parentElement?.querySelector('.file-name');
            
            if (label) {
                label.textContent = fileName;
            }
        });
    });
    
    // Calculatrice de prix pour les commandes
    window.calculatePrice = function(poids, distance, fragile = false, urgent = false) {
        const basePrice = 1500;
        const pricePerKg = 100;
        const pricePerKm = 200;
        const fragileSurcharge = 500;
        const urgentSurcharge = 1000;
        
        let total = basePrice;
        total += poids * pricePerKg;
        total += distance * pricePerKm;
        if (fragile) total += fragileSurcharge;
        if (urgent) total += urgentSurcharge;
        
        return total;
    };
    
    // Formatage des nombres (prix)
    window.formatPrice = function(price) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'XAF',
            minimumFractionDigits: 0
        }).format(price);
    };
    
    // Formatage des dates
    window.formatDate = function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };
    
    // Gestion du statut de disponibilité (transporteur)
    const availabilityToggle = document.getElementById('availabilityToggle');
    if (availabilityToggle) {
        availabilityToggle.addEventListener('change', function() {
            const isAvailable = this.checked;
            
            fetch('update_availability.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ available: isAvailable })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(
                        isAvailable ? 'Vous êtes maintenant disponible' : 'Vous êtes maintenant indisponible',
                        'success'
                    );
                } else {
                    showToast('Erreur lors de la mise à jour', 'danger');
                    this.checked = !isAvailable;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Erreur de connexion', 'danger');
                this.checked = !isAvailable;
            });
        });
    }
    
    // Système de notification en temps réel
    function checkNotifications() {
        if (typeof userId !== 'undefined') {
            fetch(`check_notifications.php?user_id=${userId}&last_check=${lastNotificationCheck}`)
                .then(response => response.json())
                .then(data => {
                    if (data.new_notifications > 0) {
                        updateNotificationBadge(data.new_notifications);
                        if (data.notifications.length > 0) {
                            showNewNotifications(data.notifications);
                        }
                    }
                    lastNotificationCheck = Date.now();
                });
        }
    }
    
    // Vérifier les notifications toutes les 30 secondes
    let lastNotificationCheck = Date.now();
    setInterval(checkNotifications, 30000);
    
    // Fonction pour mettre à jour le badge de notifications
    function updateNotificationBadge(count) {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
            
            // Animation du badge
            if (count > 0) {
                badge.classList.add('animate__animated', 'animate__pulse');
                setTimeout(() => {
                    badge.classList.remove('animate__animated', 'animate__pulse');
                }, 1000);
            }
        }
    }
    
    // Fonction pour afficher les nouvelles notifications
    function showNewNotifications(notifications) {
        notifications.forEach(notification => {
            showToast(notification.message, 'info');
        });
    }
    
    // Gestion de la recherche en temps réel
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this.value);
            }, 500);
        });
    }
    
    // Fonction de recherche
    function performSearch(query) {
        if (query.length < 2) return;
        
        fetch(`search.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                displaySearchResults(data);
            });
    }
    
    // Gestion des onglets
    const tabTriggers = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href') || this.dataset.bsTarget;
            const targetTab = document.querySelector(targetId);
            
            if (targetTab) {
                // Sauvegarder l'onglet actif dans le localStorage
                localStorage.setItem('activeTab', targetId);
            }
        });
    });
    
    // Restaurer l'onglet actif
    const activeTabId = localStorage.getItem('activeTab');
    if (activeTabId) {
        const activeTab = document.querySelector(`[href="${activeTabId}"], [data-bs-target="${activeTabId}"]`);
        if (activeTab) {
            const tab = new bootstrap.Tab(activeTab);
            tab.show();
        }
    }
    
    // Gestion des modales avec chargement AJAX
    document.querySelectorAll('[data-ajax-modal]').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.dataset.ajaxModal;
            const modalId = this.dataset.bsTarget || '#ajaxModal';
            const modal = document.querySelector(modalId);
            
            if (modal && url) {
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        const modalBody = modal.querySelector('.modal-body');
                        if (modalBody) {
                            modalBody.innerHTML = html;
                        }
                    });
            }
        });
    });
    
    // Gestion des cartes interactives
    document.querySelectorAll('.interactive-card').forEach(card => {
        card.addEventListener('click', function() {
            this.classList.toggle('selected');
        });
    });
    
    // Initialisation des cartes Leaflet
    window.initializeMap = function(mapId, lat, lng, markers = []) {
        const map = L.map(mapId).setView([lat, lng], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        markers.forEach(markerData => {
            const marker = L.marker([markerData.lat, markerData.lng]).addTo(map);
            if (markerData.popup) {
                marker.bindPopup(markerData.popup);
            }
        });
        
        return map;
    };
    
    // Gestion du drag and drop pour les fichiers
    const dropAreas = document.querySelectorAll('.drop-area');
    dropAreas.forEach(area => {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            area.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            area.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            area.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            area.classList.add('highlight');
        }
        
        function unhighlight() {
            area.classList.remove('highlight');
        }
        
        area.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length) {
                const input = area.querySelector('input[type="file"]');
                if (input) {
                    input.files = files;
                    const event = new Event('change', { bubbles: true });
                    input.dispatchEvent(event);
                }
            }
        }
    });
    
    // Système de pagination AJAX
    document.querySelectorAll('.pagination a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            
            if (url && url !== '#') {
                fetchPage(url);
            }
        });
    });
    
    function fetchPage(url) {
        fetch(url)
            .then(response => response.text())
            .then(html => {
                // Mettre à jour le contenu de la page
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.querySelector('#main-content');
                
                if (newContent) {
                    document.querySelector('#main-content').innerHTML = newContent.innerHTML;
                    
                    // Mettre à jour l'URL dans la barre d'adresse
                    window.history.pushState({}, '', url);
                    
                    // Re-initialiser les scripts
                    setTimeout(() => {
                        const event = new Event('DOMContentLoaded');
                        document.dispatchEvent(event);
                    }, 100);
                }
            });
    }
    
    // Gestion de l'historique du navigateur
    window.addEventListener('popstate', function() {
        fetchPage(window.location.href);
    });
    
    // Auto-save pour les formulaires longs
    const autoSaveForms = document.querySelectorAll('[data-auto-save]');
    autoSaveForms.forEach(form => {
        const saveKey = form.dataset.autoSave || 'autoSave_' + form.id;
        let saveTimeout;
        
        // Restaurer les données sauvegardées
        const savedData = localStorage.getItem(saveKey);
        if (savedData) {
            try {
                const data = JSON.parse(savedData);
                Object.keys(data).forEach(key => {
                    const input = form.querySelector(`[name="${key}"]`);
                    if (input) {
                        if (input.type === 'checkbox' || input.type === 'radio') {
                            input.checked = data[key];
                        } else {
                            input.value = data[key];
                        }
                    }
                });
                
                showToast('Données restaurées', 'info');
            } catch (e) {
                console.error('Erreur de restauration:', e);
            }
        }
        
        // Sauvegarder automatiquement
        form.addEventListener('input', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                const formData = new FormData(form);
                const data = {};
                formData.forEach((value, key) => {
                    data[key] = value;
                });
                
                localStorage.setItem(saveKey, JSON.stringify(data));
                
                // Afficher une indication discrète
                const indicator = document.createElement('div');
                indicator.className = 'auto-save-indicator';
                indicator.textContent = 'Sauvegardé';
                indicator.style.cssText = `
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: var(--success-color);
                    color: white;
                    padding: 5px 10px;
                    border-radius: 4px;
                    font-size: 12px;
                    opacity: 0;
                    transition: opacity 0.3s;
                    z-index: 1000;
                `;
                document.body.appendChild(indicator);
                
                setTimeout(() => {
                    indicator.style.opacity = '1';
                }, 10);
                
                setTimeout(() => {
                    indicator.style.opacity = '0';
                    setTimeout(() => indicator.remove(), 300);
                }, 2000);
            }, 1000);
        });
        
        // Nettoyer après soumission
        form.addEventListener('submit', function() {
            localStorage.removeItem(saveKey);
        });
    });
    
    // Gestion des onglets avec contenu chargé dynamiquement
    document.querySelectorAll('[data-bs-toggle="tab"][data-load]').forEach(tab => {
        tab.addEventListener('click', function() {
            const targetId = this.getAttribute('href') || this.dataset.bsTarget;
            const loadUrl = this.dataset.load;
            
            if (loadUrl) {
                const targetPane = document.querySelector(targetId);
                if (targetPane && !targetPane.dataset.loaded) {
                    fetch(loadUrl)
                        .then(response => response.text())
                        .then(html => {
                            targetPane.innerHTML = html;
                            targetPane.dataset.loaded = true;
                        });
                }
            }
        });
    });
});

// Fonction pour générer un PDF (utilisant jsPDF)
window.generatePDF = function(elementId, filename = 'document.pdf') {
    const element = document.getElementById(elementId);
    if (element) {
        html2canvas(element).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF();
            const imgWidth = 210; // A4 width in mm
            const pageHeight = 295; // A4 height in mm
            const imgHeight = canvas.height * imgWidth / canvas.width;
            let heightLeft = imgHeight;
            let position = 0;
            
            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
            
            while (heightLeft >= 0) {
                position = heightLeft - imgHeight;
                pdf.addPage();
                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }
            
            pdf.save(filename);
        });
    }
};

// Fonction pour partager sur les réseaux sociaux
window.shareOnSocial = function(platform, url = window.location.href, text = document.title) {
    let shareUrl;
    
    switch(platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`;
            break;
        case 'linkedin':
            shareUrl = `https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(url)}&title=${encodeURIComponent(text)}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`;
            break;
        default:
            return;
    }
    
    window.open(shareUrl, '_blank', 'width=600,height=400');
};

// Fonction pour copier du texte dans le presse-papier
window.copyToClipboard = function(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copié dans le presse-papier', 'success');
    }).catch(err => {
        console.error('Erreur de copie:', err);
        showToast('Erreur lors de la copie', 'danger');
    });
};

// Fonction pour obtenir la position géographique
window.getCurrentLocation = function() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject('La géolocalisation n\'est pas supportée par votre navigateur');
            return;
        }
        
        navigator.geolocation.getCurrentPosition(
            position => {
                resolve({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                });
            },
            error => {
                let errorMessage;
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = 'Permission refusée';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = 'Position indisponible';
                        break;
                    case error.TIMEOUT:
                        errorMessage = 'Timeout';
                        break;
                    default:
                        errorMessage = 'Erreur inconnue';
                }
                reject(errorMessage);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });
};

// Débounce function pour les événements fréquents
window.debounce = function(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

// Throttle function pour limiter la fréquence d'exécution
window.throttle = function(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
};