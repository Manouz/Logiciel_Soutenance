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
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `dashboard_stats_${new Date().toISOString().split('T')[0]}.${format}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            showNotification('Export réussi', 'success');
        })
        .catch(error => {
            console.error('Erreur export:', error);
            showNotification('Erreur lors de l\'export', 'error');
        })
        .finally(() => {
            showLoader(false);
        });
    };
    
    // Export des logs
    window.exportLogs = function() {
        showLoader();
        
        fetch(`${CONFIG.API_URL}export_logs.php`, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': CONFIG.CSRF_TOKEN
            }
        })
        .then(response => {
            if (response.ok) {
                return response.blob();
            }
            throw new Error('Erreur lors de l\'export des logs');
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `system_logs_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            showNotification('Logs exportés avec succès', 'success');
        })
        .catch(error => {
            console.error('Erreur export logs:', error);
            showNotification('Erreur lors de l\'export des logs', 'error');
        })
        .finally(() => {
            showLoader(false);
        });
    };
}

/**
 * Configuration des actions rapides
 */
function setupQuickActions() {
    // Maintenance système
    window.runSystemMaintenance = function() {
        Swal.fire({
            title: 'Maintenance Système',
            text: 'Cette opération va optimiser la base de données et nettoyer les fichiers temporaires. Continuer ?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, lancer la maintenance',
            cancelButtonText: 'Annuler',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return fetch(`${CONFIG.API_URL}maintenance.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CONFIG.CSRF_TOKEN
                    },
                    body: JSON.stringify({ action: 'system_maintenance' })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau');
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Erreur lors de la maintenance');
                    }
                    return data;
                })
                .catch(error => {
                    Swal.showValidationMessage(`Erreur: ${error.message}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Maintenance Terminée!',
                    text: 'Le système a été optimisé avec succès.',
                    icon: 'success'
                });
                refreshDashboard();
            }
        });
    };
    
    // Gestion des rôles
    window.showRoleManagement = function() {
        // Ici on pourrait ouvrir une modal ou rediriger vers la page de gestion des rôles
        window.location.href = `${CONFIG.BASE_URL}pages/admin/roles/gestion.php`;
    };
}

/**
 * Configuration des filtres
 */
function setupFilters() {
    // Filtre des activités par type
    const activityFilters = document.querySelectorAll('.activity-filter');
    activityFilters.forEach(filter => {
        filter.addEventListener('change', filterActivities);
    });
    
    // Filtre des connexions
    const connectionFilters = document.querySelectorAll('.connection-filter');
    connectionFilters.forEach(filter => {
        filter.addEventListener('change', filterConnections);
    });
}

/**
 * Filtrage des activités
 */
function filterActivities() {
    const selectedTypes = Array.from(document.querySelectorAll('.activity-filter:checked'))
        .map(cb => cb.value);
    
    const activityItems = document.querySelectorAll('.activity-item');
    activityItems.forEach(item => {
        const activityType = item.querySelector('.activity-icon').className.split(' ').pop();
        if (selectedTypes.length === 0 || selectedTypes.includes(activityType)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

/**
 * Filtrage des connexions
 */
function filterConnections() {
    const showSuccess = document.querySelector('#showSuccessConnections')?.checked;
    const showFailed = document.querySelector('#showFailedConnections')?.checked;
    
    const connectionItems = document.querySelectorAll('.connection-item');
    connectionItems.forEach(item => {
        const isSuccess = item.querySelector('.fa-check-circle') !== null;
        const isFailed = item.querySelector('.fa-times-circle') !== null;
        
        let shouldShow = false;
        if (showSuccess && isSuccess) shouldShow = true;
        if (showFailed && isFailed) shouldShow = true;
        if (!showSuccess && !showFailed) shouldShow = true;
        
        item.style.display = shouldShow ? 'flex' : 'none';
    });
}

/**
 * Chargement des données du dashboard
 */
function loadDashboardData() {
    // Charger les notifications
    loadNotifications();
    
    // Charger les statistiques en temps réel
    loadRealTimeStats();
    
    // Charger les activités récentes
    loadRecentActivities();
}

/**
 * Chargement des statistiques en temps réel
 */
function loadRealTimeStats() {
    fetch(`${CONFIG.API_URL}dashboard_stats.php`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatsDisplay(data.stats);
            }
        })
        .catch(error => {
            console.error('Erreur chargement stats:', error);
        });
}

/**
 * Mise à jour de l'affichage des statistiques
 */
function updateStatsDisplay(stats) {
    // Mettre à jour les compteurs
    Object.keys(stats).forEach(key => {
        const element = document.querySelector(`[data-stat="${key}"]`);
        if (element) {
            const currentValue = parseInt(element.textContent.replace(/\D/g, ''));
            const newValue = stats[key];
            
            if (currentValue !== newValue) {
                animateCounterUpdate(element, currentValue, newValue);
            }
        }
    });
    
    // Mettre à jour les graphiques si nécessaire
    updateChartsData(stats);
}

/**
 * Animation de mise à jour des compteurs
 */
function animateCounterUpdate(element, from, to) {
    const duration = 1000;
    const step = (to - from) / (duration / 16);
    let current = from;
    
    const timer = setInterval(() => {
        current += step;
        if ((step > 0 && current >= to) || (step < 0 && current <= to)) {
            current = to;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current).toLocaleString();
    }, 16);
}

/**
 * Mise à jour des données des graphiques
 */
function updateChartsData(stats) {
    // Mise à jour du graphique système si de nouvelles données sont disponibles
    if (stats.chartData && systemAnalyticsChart) {
        systemAnalyticsChart.data = stats.chartData;
        systemAnalyticsChart.update('none');
    }
    
    // Mise à jour du graphique de distribution
    if (stats.roleDistribution && usersDistributionChart) {
        usersDistributionChart.data.datasets[0].data = stats.roleDistribution;
        usersDistributionChart.update('none');
    }
}

/**
 * Chargement des activités récentes
 */
function loadRecentActivities() {
    fetch(`${CONFIG.API_URL}recent_activities.php`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateActivitiesDisplay(data.activities);
            }
        })
        .catch(error => {
            console.error('Erreur chargement activités:', error);
        });
}

/**
 * Mise à jour de l'affichage des activités
 */
function updateActivitiesDisplay(activities) {
    const timeline = document.getElementById('activityTimeline');
    if (!timeline) return;
    
    // Garder les 10 premières activités et ajouter les nouvelles
    const currentItems = timeline.querySelectorAll('.activity-item');
    const currentCount = currentItems.length;
    
    activities.slice(0, 10).forEach((activity, index) => {
        if (index < currentCount) {
            // Mettre à jour l'activité existante si nécessaire
            const existingItem = currentItems[index];
            const activityId = existingItem.getAttribute('data-activity-id');
            if (activityId !== activity.id.toString()) {
                existingItem.replaceWith(createActivityElement(activity));
            }
        } else {
            // Ajouter une nouvelle activité
            timeline.appendChild(createActivityElement(activity));
        }
    });
    
    // Supprimer les activités en trop
    if (currentCount > 10) {
        for (let i = 10; i < currentCount; i++) {
            currentItems[i].remove();
        }
    }
}

/**
 * Création d'un élément d'activité
 */
function createActivityElement(activity) {
    const item = document.createElement('div');
    item.className = 'activity-item';
    item.setAttribute('data-activity-id', activity.id);
    
    const iconClass = getActivityIconClass(activity.type_action);
    const icon = getActivityIcon(activity.type_action);
    const text = getActivityText(activity.type_action, activity.table_cible);
    
    item.innerHTML = `
        <div class="activity-icon ${iconClass}">
            <i class="fas fa-${icon}"></i>
        </div>
        <div class="activity-content">
            <div class="activity-text">
                <strong>${activity.utilisateur_nom}</strong>
                ${text}
                ${activity.nom_role ? `<span class="role-badge">${activity.nom_role}</span>` : ''}
            </div>
            <div class="activity-time">
                <i class="fas fa-clock"></i>
                ${timeAgo(activity.date_action)}
            </div>
            ${activity.commentaire ? `<div class="activity-comment">${activity.commentaire}</div>` : ''}
        </div>
    `;
    
    return item;
}

/**
 * Fonctions utilitaires pour les activités
 */
function getActivityIcon(action) {
    const icons = {
        'CREATE': 'plus-circle',
        'UPDATE': 'edit',
        'DELETE': 'trash',
        'LOGIN': 'sign-in-alt',
        'LOGOUT': 'sign-out-alt',
        'BLOCK': 'lock',
        'UNBLOCK': 'unlock',
        'ERROR': 'exclamation-triangle',
        'WARNING': 'exclamation-circle'
    };
    return icons[action] || 'info-circle';
}

function getActivityIconClass(action) {
    const classes = {
        'CREATE': 'success',
        'UPDATE': 'primary',
        'DELETE': 'danger',
        'LOGIN': 'info',
        'LOGOUT': 'secondary',
        'BLOCK': 'warning',
        'UNBLOCK': 'success',
        'ERROR': 'danger',
        'WARNING': 'warning'
    };
    return classes[action] || 'info';
}

function getActivityText(action, table) {
    const actions = {
        'CREATE': 'a créé un enregistrement dans',
        'UPDATE': 'a modifié un enregistrement dans',
        'DELETE': 'a supprimé un enregistrement de',
        'LOGIN': 's\'est connecté au système',
        'LOGOUT': 's\'est déconnecté du système',
        'BLOCK': 'a bloqué un utilisateur',
        'UNBLOCK': 'a débloqué un utilisateur'
    };
    
    const text = actions[action] || 'a effectué une action sur';
    return ['LOGIN', 'LOGOUT', 'BLOCK', 'UNBLOCK'].includes(action) ? text : text + ' ' + table;
}

/**
 * Rafraîchissement du dashboard
 */
window.refreshDashboard = function() {
    showLoader();
    
    // Rafraîchir les données
    loadDashboardData();
    
    // Rafraîchir les graphiques
    if (systemAnalyticsChart) {
        systemAnalyticsChart.update();
    }
    if (usersDistributionChart) {
        usersDistributionChart.update();
    }
    
    // Animation de rafraîchissement
    const refreshBtn = document.querySelector('[onclick="refreshDashboard()"]');
    if (refreshBtn) {
        const icon = refreshBtn.querySelector('i');
        icon.style.animation = 'spin 1s linear';
        setTimeout(() => {
            icon.style.animation = '';
        }, 1000);
    }
    
    setTimeout(() => {
        showLoader(false);
        showNotification('Dashboard mis à jour', 'success');
    }, 1000);
};

/**
 * Rafraîchissement automatique des données
 */
function refreshDashboardData() {
    loadRealTimeStats();
    loadRecentActivities();
}

/**
 * Rafraîchissement des activités
 */
window.refreshActivities = function() {
    const btn = event.target.closest('button');
    const icon = btn.querySelector('i');
    
    icon.style.animation = 'spin 1s linear';
    
    loadRecentActivities();
    
    setTimeout(() => {
        icon.style.animation = '';
        showNotification('Activités mises à jour', 'info');
    }, 1000);
};

/**
 * Gestion des erreurs globales
 */
window.addEventListener('error', function(e) {
    console.error('Erreur JavaScript:', e.error);
    
    // Log de l'erreur côté serveur
    fetch(`${CONFIG.API_URL}log_error.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': CONFIG.CSRF_TOKEN
        },
        body: JSON.stringify({
            message: e.message,
            filename: e.filename,
            lineno: e.lineno,
            colno: e.colno,
            stack: e.error?.stack
        })
    }).catch(err => console.error('Erreur lors du log:', err));
});

/**
 * Gestion de la performance
 */
function monitorPerformance() {
    if ('performance' in window) {
        const perfData = {
            loadTime: performance.timing.loadEventEnd - performance.timing.navigationStart,
            domContentLoaded: performance.timing.domContentLoadedEventEnd - performance.timing.navigationStart,
            responseTime: performance.timing.responseEnd - performance.timing.requestStart
        };
        
        // Envoyer les données de performance si nécessaire
        if (perfData.loadTime > 5000) { // Plus de 5 secondes
            console.warn('Temps de chargement élevé:', perfData);
        }
    }
}

// Monitorer la performance au chargement
window.addEventListener('load', monitorPerformance);

/**
 * Nettoyage lors du déchargement de la page
 */
window.addEventListener('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    
    if (systemAnalyticsChart) {
        systemAnalyticsChart.destroy();
    }
    
    if (usersDistributionChart) {
        usersDistributionChart.destroy();
    }
});

/**
 * Animation CSS personnalisée
 */
const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .stat-card:hover .stat-icon {
        transform: scale(1.05);
    }
    
    .quick-action-btn:hover .quick-action-icon {
        transform: scale(1.1) rotate(5deg);
    }
    
    .activity-item {
        opacity: 0;
        animation: slideInLeft 0.5s ease-out forwards;
    }
    
    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
`;
document.head.appendChild(style);

// Export des fonctions pour usage global
window.AdminDashboard = {
    refresh: refreshDashboard,
    refreshActivities: window.refreshActivities,
    exportStats: window.exportStats,
    exportLogs: window.exportLogs,
    runMaintenance: window.runSystemMaintenance,
    showRoleManagement: window.showRoleManagement
};