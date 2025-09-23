// ===== BRAWL FORUM - JAVASCRIPT PRINCIPAL =====

// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {
    initializeAnimations();
    initializeInteractions();
    initializeResponsive();
    initializePageSpecific();
});

// ===== ANIMATIONS =====
function initializeAnimations() {
    // Animation d'entrée pour les éléments
    const fadeElements = document.querySelectorAll('.fade-in');
    fadeElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            element.style.transition = 'all 0.6s ease-out';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 150);
    });
    
    // Animation de pulsation pour les éléments importants
    const pulseElements = document.querySelectorAll('.pulse');
    pulseElements.forEach(element => {
        element.style.animation = 'pulse 2s infinite';
    });
    
    // Effet de survol pour les cartes de catégorie
    const categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.05)';
            this.style.boxShadow = '0 20px 40px rgba(0,0,0,0.5)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 8px 16px rgba(0,0,0,0.3)';
        });
    });
}

// ===== INTERACTIONS =====
function initializeInteractions() {
    // Gestion des clics sur les catégories
    const categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach(card => {
        card.addEventListener('click', function() {
            const category = this.classList[1]; // strategies, team, skins, events
            filterByCategory(category);
            
            // Effet visuel de clic
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            }, 100);
        });
    });
    
    // Gestion des discussions
    const discussionItems = document.querySelectorAll('.discussion-item');
    discussionItems.forEach(item => {
        item.addEventListener('click', function() {
            // Simulation d'ouverture de discussion
            showNotification('Ouverture de la discussion...', 'info');
        });
        
        // Effet de survol
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(10px)';
            this.style.backgroundColor = 'rgba(255,215,0,0.1)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
            this.style.backgroundColor = 'rgba(0,0,0,0.2)';
        });
    });
    
    // Gestion des boutons avec effet sonore visuel
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Effet de ripple
            createRippleEffect(e, this);
        });
    });
}

// ===== FONCTIONS UTILITAIRES =====

// Filtrer par catégorie
function filterByCategory(category) {
    console.log('Filtrage par catégorie:', category);
    showNotification(`Affichage des posts de la catégorie: ${category}`, 'success');
    
    // Ici on pourrait implémenter le filtrage réel
    // Pour l'instant, on simule avec une notification
}

// Créer un effet de ripple sur les boutons
function createRippleEffect(event, element) {
    const ripple = document.createElement('span');
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;
    
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.classList.add('ripple');
    
    // Ajouter les styles CSS pour l'effet ripple
    ripple.style.position = 'absolute';
    ripple.style.borderRadius = '50%';
    ripple.style.background = 'rgba(255,255,255,0.6)';
    ripple.style.transform = 'scale(0)';
    ripple.style.animation = 'ripple 0.6s linear';
    ripple.style.pointerEvents = 'none';
    
    element.style.position = 'relative';
    element.style.overflow = 'hidden';
    element.appendChild(ripple);
    
    setTimeout(() => {
        ripple.remove();
    }, 600);
}

// Afficher une notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}-circle"></i>
        ${message}
    `;
    
    // Styles de la notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? 'linear-gradient(45deg, #4CAF50, #45a049)' : 
                     type === 'error' ? 'linear-gradient(45deg, #f44336, #d32f2f)' : 
                     'linear-gradient(45deg, #2196F3, #1976D2)'};
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        border: 2px solid #000;
        font-weight: bold;
        z-index: 1000;
        transform: translateX(400px);
        transition: transform 0.3s ease-out;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    `;
    
    document.body.appendChild(notification);
    
    // Animation d'entrée
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Suppression automatique
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// ===== RESPONSIVE =====
function initializeResponsive() {
    // Gestion du menu mobile
    const navHeader = document.querySelector('.nav-header');
    if (navHeader && window.innerWidth <= 768) {
        createMobileMenu();
    }
    
    // Redimensionnement de la fenêtre
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            createMobileMenu();
        } else {
            removeMobileMenu();
        }
    });
}

function createMobileMenu() {
    const navHeader = document.querySelector('.nav-header');
    if (!navHeader || navHeader.querySelector('.mobile-menu-toggle')) return;
    
    const menuToggle = document.createElement('button');
    menuToggle.className = 'mobile-menu-toggle';
    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
    menuToggle.style.cssText = `
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 10px;
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 1001;
    `;
    
    navHeader.appendChild(menuToggle);
    
    menuToggle.addEventListener('click', function() {
        navHeader.classList.toggle('mobile-open');
        this.innerHTML = navHeader.classList.contains('mobile-open') ? 
            '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
    });
}

function removeMobileMenu() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    if (menuToggle) {
        menuToggle.remove();
    }
    
    const navHeader = document.querySelector('.nav-header');
    if (navHeader) {
        navHeader.classList.remove('mobile-open');
    }
}

// ===== ANIMATIONS CSS DYNAMIQUES =====
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    .shake {
        animation: shake 0.5s ease-in-out;
    }
    
    .mobile-open {
        flex-direction: column !important;
        height: auto !important;
    }
    
    .mobile-open .nav-links {
        flex-direction: column !important;
        width: 100% !important;
        margin-top: 20px !important;
    }
    
    @media (max-width: 768px) {
        .nav-header {
            position: relative;
            min-height: 60px;
        }
        
        .nav-header:not(.mobile-open) .nav-links {
            display: none;
        }
        
        .nav-header:not(.mobile-open) .logo {
            display: none;
        }
    }
`;
document.head.appendChild(style);

// ===== FONCTIONS GLOBALES =====

// Fonction pour valider les formulaires
function validateForm(formElement) {
    const inputs = formElement.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('shake');
            input.style.borderColor = '#f44336';
            isValid = false;
            
            setTimeout(() => {
                input.classList.remove('shake');
            }, 500);
        } else {
            input.style.borderColor = '#4CAF50';
        }
    });
    
    return isValid;
}

// Fonction pour formater les dates
function formatTimeAgo(date) {
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) return 'À l\'instant';
    if (diffInSeconds < 3600) return `Il y a ${Math.floor(diffInSeconds / 60)} min`;
    if (diffInSeconds < 86400) return `Il y a ${Math.floor(diffInSeconds / 3600)} h`;
    return `Il y a ${Math.floor(diffInSeconds / 86400)} jour(s)`;
}

// Fonction pour gérer le localStorage
function saveToStorage(key, data) {
    try {
        localStorage.setItem(key, JSON.stringify(data));
    } catch (e) {
        console.warn('Impossible de sauvegarder dans le localStorage:', e);
    }
}

function loadFromStorage(key) {
    try {
        const data = localStorage.getItem(key);
        return data ? JSON.parse(data) : null;
    } catch (e) {
        console.warn('Impossible de charger depuis le localStorage:', e);
        return null;
    }
}

// Fonction pour débouncer les événements
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialisation des tooltips
function initializeTooltips() {
    const elementsWithTooltip = document.querySelectorAll('[data-tooltip]');
    elementsWithTooltip.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(event) {
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = event.target.dataset.tooltip;
    tooltip.style.cssText = `
        position: absolute;
        background: rgba(0,0,0,0.9);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.9rem;
        z-index: 1000;
        pointer-events: none;
        white-space: nowrap;
    `;
    
    document.body.appendChild(tooltip);
    
    const rect = event.target.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
    
    event.target._tooltip = tooltip;
}

function hideTooltip(event) {
    if (event.target._tooltip) {
        event.target._tooltip.remove();
        delete event.target._tooltip;
    }
}

// Initialiser les tooltips au chargement
document.addEventListener('DOMContentLoaded', initializeTooltips);

console.log('🎮 Brawl Forum JavaScript chargé avec succès!');

// ===== FONCTIONS POUR LES PAGES DE CONNEXION ET INSCRIPTION =====

// Fonction pour basculer l'affichage du mot de passe
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.querySelector('.password-toggle i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.className = 'fas fa-unlock';
    } else {
        passwordInput.type = 'password';
        passwordIcon.className = 'fas fa-lock';
    }
}

// Animation d'entrée pour la page de connexion
function initializeLoginAnimation() {
    const container = document.querySelector('.login-container');
    if (container) {
        container.style.transform = 'scale(0.8)';
        container.style.opacity = '0';
        
        setTimeout(() => {
            container.style.transition = 'all 0.6s ease-out';
            container.style.transform = 'scale(1)';
            container.style.opacity = '1';
        }, 100);
    }
}

// Animation d'entrée pour la page d'inscription
function initializeRegisterAnimation() {
    const container = document.querySelector('.register-container');
    if (container) {
        container.style.transform = 'scale(0.5) rotate(-10deg)';
        container.style.opacity = '0';
        
        setTimeout(() => {
            container.style.transition = 'all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
            container.style.transform = 'scale(1) rotate(0deg)';
            container.style.opacity = '1';
        }, 100);
    }
}

// Validation en temps réel pour les champs de saisie
function initializeInputValidation() {
    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.length > 0) {
                this.style.borderColor = '#4CAF50';
                this.style.boxShadow = '0 0 10px rgba(76, 175, 80, 0.3)';
            } else {
                this.style.borderColor = '#000';
                this.style.boxShadow = 'none';
            }
        });
    });
}

// Initialisation spécifique selon la page
function initializePageSpecific() {
    // Vérifier si on est sur la page de connexion
    if (document.body.classList.contains('login-page')) {
        initializeLoginAnimation();
    }
    
    // Vérifier si on est sur la page d'inscription
    if (document.body.classList.contains('register-page')) {
        initializeRegisterAnimation();
        initializeInputValidation();
    }
}