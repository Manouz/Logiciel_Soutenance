/**
 * JavaScript pour le Dashboard Administrateur
 * Fichier: assets/js/admin/admin-dashboard.js
 */

// Variables globales
let systemAnalyticsChart = null;
let usersDistributionChart = null;
let refreshInterval = null;

// Initialisation du dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

/**
 * Initialisation complète du dashboard
 */
function initializeDashboard() {
    // Animation des compteurs
    animateCounters();
    
    // Initialisation des graphiques
    initializeCharts();
    
    // Animation des cercles de santé
    animateHealthCircles();
    
    // Configuration du rafraîchissement automatique
    setupAutoRefresh();
    
    // Configuration des événements
    setupEventListeners();
    
    // Chargement initial des données
    loadDashboardData();
    
    console.log('Dashboard Administrateur initialisé');
}

/**
 * Animation des compteurs numériques
 */
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number[data-counter]');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-counter'));
        const duration = 2000; // 2 secondes
        const step = target / (duration / 16); // 60 FPS
        let current = 0;
        
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            counter.textContent = Math.floor(current).toLocaleString();
        }, 16);
    });
}

/**
 * Initialisation des graphiques Chart.js
 */
function initializeCharts() {
    // Graphique des analyses système
    initSystemAnalyticsChart();
    
    // Graphique de distribution des utilisateurs
    initUsersDistributionChart();
}

/**
 * Graphique des analyses système
 */
function initSystemAnalyticsChart() {
    const ctx = document.getElementById('systemAnalyticsChart');
    if (!ctx) return;
    
    // Données de simulation (à remplacer par de vraies données API)
    const data = {
        labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
        datasets: [
            {
                label: 'Connexions',
                data: [45, 52, 38, 65, 78, 42, 35],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Nouvelles inscriptions',
                data: [12, 19, 8, 15, 22, 8, 5],
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Rapports soumis',
                data: [8, 15, 12, 18, 25, 15, 10],
                borderColor: '#f39c12',
                backgroundColor: 'rgba(243, 156, 18, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    };
    
    const config = {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Activité Système - 7 derniers jours'
                },
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                }
            },
            elements: {
                point: {
                    radius: 5,
                    hoverRadius: 8
                }
            }
        }
    };
    
    systemAnalyticsChart = new Chart(ctx, config);
}

/**
 * Graphique de distribution des utilisateurs
 */
function initUsersDistributionChart() {
    const ctx = document.getElementById('usersDistributionChart');
    if (!ctx) return;
    
    // Récupération des données depuis la page (à améliorer avec API)
    const roleStats = [];
    document.querySelectorAll('.role-stat-item').forEach(item => {
        const name = item.querySelector('.role-name').textContent;
        const count = parseInt(item.querySelector('.role-count.total').textContent);
        roleStats.push({ name, count });
    });
    
    const data = {
        labels: roleStats.map(role => role.name),
        datasets: [{
            data: roleStats.map(role => role.count),
            backgroundColor: [
                '#e74c3c', '#f39c12', '#f1c40f', 
                '#27ae60', '#20c997', '#17a2b8', 
                '#6f42c1', '#e83e8c'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    };
    
    const config = {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed * 100) / total).toFixed(1);
                            return `${context.label}: ${context.parsed} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '50%'
        }
    };
    
    usersDistributionChart = new Chart(ctx, config);
}

/**
 * Animation des cercles de santé
 */
function animateHealthCircles() {
    const healthCircles = document.querySelectorAll('.health-circle[data-percentage]');
    
    healthCircles.forEach(circle => {
        const percentage = parseInt(circle.getAttribute('data-percentage'));
        const degrees = (percentage / 100) * 360;
        
        setTimeout(() => {
            circle.style.setProperty('--percentage', `${degrees}deg`);
            circle.classList.add('animated');
        }, 500);
    });
}

/**
 * Configuration du rafraîchissement automatique
 */
function setupAutoRefresh() {
    // Rafraîchissement toutes les 30 secondes
    refreshInterval = setInterval(() => {
        refreshDashboardData();
    }, 30000);
    
    // Arrêter le rafraîchissement si la page n'est plus visible
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(refreshInterval);
        } else {
            refreshInterval = setInterval(refreshDashboardData, 30000);
        }
    });
}

/**
 * Configuration des événements
 */
function setupEventListeners() {
    // Bouton de rafraîchissement manuel
    const refreshBtn = document.querySelector('[onclick="refreshDashboard()"]');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', refreshDashboard);
    }
    
    // Gestion des exports
    setupExportListeners();
    
    // Gestion des actions rapides
    setupQuickActions();
    
    // Gestion des filtres et recherches
    setupFilters();
}

/**
 * Configuration des exports
 */
function setupExportListeners() {
    // Export PDF
    window.exportStats = function(format) {
        showLoader();
        
        const data = {
            format: format,
            type: 'dashboard_stats'
        };
        
        fetch(`${CONFIG.API_URL}export.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CONFIG.CSRF_TOKEN
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (response.ok) {
                return response.blob();
            }
            throw new Error('Erreur lors de l\'export');
        })
        .then(blob => {
            const url = window.URL.create