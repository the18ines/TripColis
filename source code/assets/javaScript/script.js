// Fonctions utilitaires globales
function showNotification(message, type = 'success') {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    
    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <strong>${type === 'success' ? 'Succès' : 'Erreur'}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        <div>${message}</div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Gestion des formulaires
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Initialisation des tooltips Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser tous les tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Gestion des formulaires
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredInputs = this.querySelectorAll('input[required], select[required]');
            let hasEmpty = false;
            
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    hasEmpty = true;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (hasEmpty) {
                e.preventDefault();
                showNotification('Veuillez remplir tous les champs obligatoires', 'error');
            }
        });
    });
    
    // Masquer les messages d'alerte après 5 secondes
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 150);
        });
    }, 5000);
});

// Fonction pour formater les prix
function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XAF'
    }).format(price);
}

// Fonction pour calculer la distance entre deux points (Haversine)
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Rayon de la Terre en km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c; // Distance en km
}

// Fonction pour charger les données en AJAX
async function fetchData(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Fetch error:', error);
        showNotification('Erreur de connexion au serveur', 'error');
        return null;
    }
}

// Fonction pour confirmer les actions
function confirmAction(message) {
    return new Promise((resolve) => {
        if (window.confirm(message)) {
            resolve(true);
        } else {
            resolve(false);
        }
    });
}

// Fonction pour générer un numéro de commande
function generateOrderNumber() {
    const timestamp = Date.now().toString().slice(-6);
    const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
    return `TC-${timestamp}${random}`;
}