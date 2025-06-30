/**
 * Dashboard Étudiant - JavaScript
 * Système de Validation Académique - Université Félix Houphouët-Boigny
 */

class EtudiantDashboard {
    constructor() {
        this.refreshInterval = null;
        this.notificationWS = null;
        this.charts = {};
        this.isInitialized = false;
        
        this.init();
    }
    
    init() {
        if (this.isInitialized) return;
        
        this.setupEventListeners();
        this.initializeCharts();
        this.setupAutoRefresh();
        this.setupNotifications();
        this.setupKeyboardShortcuts();
        this.loadUserPreferences();
        
        this.isInitialized = true;
        
        console.log('Dashboard Étudiant initialisé');
    }
    
    setupEventListeners() {
        // Gestion des clics sur les cartes statistiques
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('click', (e) => {
                if (!e.target.closest('.btn')) {
                    this.handleStatCardClick(card);
                }
            });
        });
        
        // Gestion du redimensionnement de la fenêtre
        window.addEventListener('resize', debounce(() => {
            this.handleResize();
        }, 250));
        
        // Gestion de la visibilité de la page
        document.addEventListener('visibilitychange', () => {
            this.handleVisibilityChange();
        });
        
        // Gestion des interactions avec les éléments de timeline
        document.querySelectorAll('.timeline-item').forEach(item => {
            item.addEventListener('click', () => {
                this.handleTimelineClick(item);
            });
        });
        
        // Gestion des actions rapides
        this.setupQuickActions();
    }
    
    setupQuickActions() {
        // Créer les boutons d'action rapide
        const quickActions = document.createElement('div');
        quickActions.className = 'quick-actions';
        quickActions.innerHTML = `
            <button class="quick-action-btn" data-action="new-report" title="Nouveau rapport">
                <i class="fas fa-plus"></i>
            </button>
            <button class="quick-action-btn" data-action="calendar" title="Calendrier">
                <i class="fas fa-calendar"></i>
            </button>
            <button class="quick-action-btn" data-action="help" title="Aide">
                <i class="fas fa-question"></i>
            </button>
        `;
        
        document.body.appendChild(quickActions);
        
        // Gestionnaires d'événements pour les actions rapides
        quickActions.addEventListener('click', (e) => {
            const action = e.target.closest('[data-action]')?.dataset.action;
            if (action) {
                this.handleQuickAction(action);
            }
        });
    }
    
    handleQuickAction(action) {
        switch (action) {
            case 'new-report':
                window.location.href = 'rapport/redaction.php';
                break;
            case 'calendar':
                window.location.href = 'calendrier.php';
                break;
            case 'help':
                this.showHelpModal();
                break;
        }
    }
    
    showHelpModal() {
        const modal = new bootstrap.Modal(document.createElement('div'));
        // Implementation du modal d'aide
        showToast('Aide contextuelle à venir', 'info');
    }
    
    handleStatCardClick(card) {
        const cardClasses = card.className;
        
        if (cardClasses.includes('rapport-progress')) {
            window.location.href = 'rapport/redaction.php';
        } else if (cardClasses.includes('mots-rediges')) {
            this.showWordCountDetails();
        } else if (cardClasses.includes('moyenne-generale')) {
            window.location.href = 'notes/consultation.php';
        } else if (cardClasses.includes('jours-restants')) {
            window.location.href = 'calendrier.php';
        }
    }
    
    showWordCountDetails() {
        // Afficher des détails sur la progression de rédaction
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Détails de Progression</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="wordCountChart" style="height: 300px;"></div>
                        <div class="mt-3">
                            <h6>Statistiques de rédaction</h6>
                            <div class="row">
                                <div class="col-6">
                                    <div class="text-center">
                                        <div class="h4 text-primary" id="averageWordsPerDay">0</div>
                                        <small class="text-muted">Mots/jour en moyenne</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center">
                                        <div class="h4 text-success" id="totalSessions">0</div>
                                        <small class="text-muted">Sessions de rédaction</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        // Charger les données pour le graphique
        this.loadWordCountChart();
        
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }
    
    handleTimelineClick(item) {
        const timelineText = item.querySelector('.timeline-content h6').textContent;
        
        switch (timelineText) {
            case 'Rédaction du Rapport':
                window.location.href = 'rapport/redaction.php';
                break;
            case 'Soumission du Rapport':
                if (item.classList.contains('current')) {
                    window.location.href = 'rapport/soumission.php';
                }
                break;
            case 'Soutenance':
                window.location.href = 'soutenance/planning.php';
                break;
        }
    }
    
    initializeCharts() {
        // Initialiser les mini-graphiques dans les cartes
        this.initProgressChart();
        this.initActivityChart();
    }
    
    initProgressChart() {
        const progressCanvas = document.getElementById('progressMiniChart');
        if (!progressCanvas) return;
        
        const ctx = progressCanvas.getContext('2d');
        
        // Données simulées pour la progression
        const data = {
            labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5'],
            datasets: [{
                label: 'Progression (%)',
                data: [10, 25, 45, 70, 85],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        };
        
        this.charts.progress = new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { display: false },
                    y: { display: false }
                },
                elements: {
                    point: { radius: 0 }
                }
            }
        });
    }
    
    initActivityChart() {
        const activityCanvas = document.getElementById('activityMiniChart');
        if (!activityCanvas) return;
        
        const ctx = activityCanvas.getContext('2d');
        
        // Données d'activité des 7 derniers jours
        const data = {
            labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            datasets: [{
                label: 'Activité',
                data: [3, 7, 4, 9, 6, 2, 5],
                backgroundColor: [
                    '#10b981', '#3b82f6', '#f59e0b', '#10b981',
                    '#8b5cf6', '#ef4444', '#06b6d4'
                ],
                borderWidth: 0
            }]
        };
        
        this.charts.activity = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { display: false },
                    y: { display: false }
                }
            }
        });
    }
    
    loadWordCountChart() {
        // Simuler le chargement des données de progression des mots
        fetch('api/word-count-history.php', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': window.APP_CONFIG.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.renderWordCountChart(data.history);
                document.getElementById('averageWordsPerDay').textContent = data.averagePerDay;
                document.getElementById('totalSessions').textContent = data.totalSessions;
            }
        })
        .catch(error => {
            console.error('Erreur chargement historique mots:', error);
            // Afficher des données fictives pour la démo
            this.renderWordCountChart([
                { date: '2024-01-01', words: 0 },
                { date: '2024-01-15', words: 1500 },
                { date: '2024-02-01', words: 3200 },
                { date: '2024-02-15', words: 5800 },
                { date: '2024-03-01', words: 8500 }
            ]);
            document.getElementById('averageWordsPerDay').textContent = '120';
            document.getElementById('totalSessions').textContent = '15';
        });
    }
    
    renderWordCountChart(data) {
        const canvas = document.getElementById('wordCountChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => new Date(item.date).toLocaleDateString('fr-FR')),
                datasets: [{
                    label: 'Nombre de mots',
                    data: data.map(item => item.words),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Nombre de mots'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                }
            }
        });
    }
    
    setupAutoRefresh() {
        // Actualiser les données toutes les 5 minutes
        this.refreshInterval = setInterval(() => {
            this.refreshDashboardData();
        }, 5 * 60 * 1000);
        
        // Actualiser immédiatement si la page redevient visible
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.refreshDashboardData();
            }
        });
    }
    
    refreshDashboardData() {
        if (document.hidden) return; // Ne pas actualiser si la page n'est pas visible
        
        fetch('api/dashboard-refresh.php', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': window.APP_CONFIG.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateDashboardElements(data);
            }
        })
        .catch(error => {
            console.error('Erreur actualisation dashboard:', error);
        });
    }
    
    updateDashboardElements(data) {
        // Mettre à jour les statistiques
        if (data.stats) {
            this.updateStatistics(data.stats);
        }
        
        // Mettre à jour les notifications
        if (data.notifications) {
            this.updateNotificationsList(data.notifications);
        }
        
        // Mettre à jour les événements
        if (data.events) {
            this.updateEventsList(data.events);
        }
        
        // Mettre à jour l'état d'éligibilité
        if (data.eligibilite) {
            this.updateEligibiliteStatus(data.eligibilite);
        }
        
        // Afficher une notification discrète de mise à jour
        this.showUpdateNotification();
    }
    
    updateStatistics(stats) {
        // Progression du rapport
        const progressBar = document.querySelector('.rapport-progress .progress-bar');
        const progressValue = document.querySelector('.rapport-progress .stat-value');
        
        if (progressBar && progressValue) {
            const oldProgress = parseFloat(progressBar.style.width) || 0;
            const newProgress = stats.progression_rapport;
            
            this.animateProgressBar(progressBar, oldProgress, newProgress);
            this.animateNumber(progressValue, oldProgress, newProgress, 1, '%');
        }
        
        // Mots rédigés
        const motsValue = document.querySelector('.mots-rediges .stat-value');
        if (motsValue) {
            const oldMots = parseInt(motsValue.textContent.replace(/\D/g, '')) || 0;
            this.animateNumber(motsValue, oldMots, stats.mots_rediges, 0, '', true);
        }
        
        // Moyenne générale
        const moyenneValue = document.querySelector('.moyenne-generale .stat-value');
        if (moyenneValue) {
            const oldMoyenne = parseFloat(moyenneValue.textContent) || 0;
            this.animateNumber(moyenneValue, oldMoyenne, stats.moyenne_generale, 2, '/20');
        }
        
        // Jours restants
        const joursValue = document.querySelector('.jours-restants .stat-value');
        const joursIcon = document.querySelector('.jours-restants .stat-icon');
        
        if (joursValue) {
            const oldJours = parseInt(joursValue.textContent) || 0;
            this.animateNumber(joursValue, oldJours, stats.jours_restants, 0);
            
            // Changer la couleur selon l'urgence
            if (joursIcon) {
                const urgentClass = stats.jours_restants <= 7 ? 'bg-danger' : 'bg-info';
                joursIcon.className = `stat-icon ${urgentClass}`;
            }
        }
    }
    
    animateProgressBar(element, fromValue, toValue) {
        const duration = 1000;
        const startTime = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const currentValue = fromValue + (toValue - fromValue) * this.easeOutCubic(progress);
            element.style.width = currentValue + '%';
            element.setAttribute('aria-valuenow', currentValue);
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }
    
    animateNumber(element, fromValue, toValue, decimals = 0, suffix = '', useFormatting = false) {
        const duration = 1000;
        const startTime = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const currentValue = fromValue + (toValue - fromValue) * this.easeOutCubic(progress);
            let displayValue = currentValue.toFixed(decimals);
            
            if (useFormatting) {
                displayValue = new Intl.NumberFormat('fr-FR').format(Math.round(currentValue));
            }
            
            element.textContent = displayValue + suffix;
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }
    
    easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }
    
    updateNotificationsList(notifications) {
        const activityList = document.querySelector('.activity-list');
        if (!activityList) return;
        
        // Sauvegarder le scroll position
        const scrollTop = activityList.scrollTop;
        
        activityList.innerHTML = '';
        
        if (notifications.length === 0) {
            activityList.innerHTML = `
                <div class="text-center text-muted py-3">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p>Aucune activité récente</p>
                </div>
            `;
            return;
        }
        
        notifications.forEach((notification, index) => {
            const item = document.createElement('div');
            item.className = 'activity-item';
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            
            item.innerHTML = `
                <div class="activity-icon">
                    <i class="fas fa-${this.getNotificationIcon(notification.type_notification)}"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">${notification.titre_notification}</div>
                    <div class="activity-time text-muted">
                        ${this.formatTimeAgo(notification.date_creation)}
                    </div>
                </div>
            `;
            
            activityList.appendChild(item);
            
            // Animation d'apparition
            setTimeout(() => {
                item.style.transition = 'all 0.3s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, index * 100);
        });
        
        // Restaurer le scroll position
        activityList.scrollTop = scrollTop;
    }
    
    updateEventsList(events) {
        const eventsList = document.querySelector('.events-list');
        if (!eventsList) return;
        
        eventsList.innerHTML = '';
        
        if (events.length === 0) {
            eventsList.innerHTML = `
                <div class="text-center text-muted py-3">
                    <i class="fas fa-calendar-times fa-2x mb-2"></i>
                    <p>Aucun événement à venir</p>
                </div>
            `;
            return;
        }
        
        events.forEach((event, index) => {
            const item = document.createElement('div');
            item.className = 'event-item';
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            
            const eventDate = new Date(event.start_date);
            const day = eventDate.getDate().toString().padStart(2, '0');
            const month = eventDate.toLocaleDateString('fr-FR', { month: 'short' });
            const time = eventDate.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            
            item.innerHTML = `
                <div class="event-date">
                    <div class="event-day">${day}</div>
                    <div class="event-month">${month}</div>
                </div>
                <div class="event-info">
                    <div class="event-title">${event.title}</div>
                    <div class="event-time text-muted">
                        <i class="fas fa-clock me-1"></i>
                        ${time}
                    </div>
                    ${event.location ? `
                        <div class="event-location text-muted">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            ${event.location}
                        </div>
                    ` : ''}
                </div>
            `;
            
            eventsList.appendChild(item);
            
            // Animation d'apparition
            setTimeout(() => {
                item.style.transition = 'all 0.4s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, index * 150);
        });
    }
    
    updateEligibiliteStatus(eligibilite) {
        const statusBadge = document.querySelector('.eligibilite-status .badge');
        const progressBar = document.querySelector('.eligibilite-progress .progress-bar');
        
        if (statusBadge) {
            statusBadge.textContent = eligibilite.statut_libelle;
            statusBadge.style.backgroundColor = eligibilite.statut_couleur;
        }
        
        if (progressBar) {
            this.animateProgressBar(progressBar, 
                parseFloat(progressBar.style.width) || 0, 
                eligibilite.progression_percentage);
        }
        
        // Mettre à jour les éléments de progression détaillés
        const progressItems = document.querySelectorAll('.progress-item i');
        progressItems.forEach((icon, index) => {
            if (eligibilite.details[index] && eligibilite.details[index].completed) {
                icon.className = 'fas fa-check-circle text-success me-2';
            } else {
                icon.className = 'fas fa-circle text-muted me-2';
            }
        });
    }
    
    showUpdateNotification() {
        // Afficher une notification discrète
        const notification = document.createElement('div');
        notification.className = 'update-notification';
        notification.innerHTML = `
            <i class="fas fa-sync-alt me-2"></i>
            Données mises à jour
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(16, 185, 129, 0.9);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            z-index: 1060;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        // Animation d'apparition
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Animation de disparition
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    setupNotifications() {
        // Configuration des notifications en temps réel
        if ('Notification' in window) {
            this.requestNotificationPermission();
        }
        
        // WebSocket pour les notifications en temps réel (si disponible)
        this.initWebSocketNotifications();
        
        // Polling de backup pour les notifications
        setInterval(() => {
            this.checkNewNotifications();
        }, 30000); // Toutes les 30 secondes
    }
    
    requestNotificationPermission() {
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    showToast('Notifications activées', 'success', 3000);
                }
            });
        }
    }
    
    initWebSocketNotifications() {
        // Implementation WebSocket pour les notifications temps réel
        try {
            const wsProtocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            const wsUrl = `${wsProtocol}//${window.location.host}/ws/notifications`;
            
            this.notificationWS = new WebSocket(wsUrl);
            
            this.notificationWS.onopen = () => {
                console.log('WebSocket notifications connecté');
                // Envoyer l'ID utilisateur pour l'identification
                this.notificationWS.send(JSON.stringify({
                    type: 'auth',
                    userId: window.APP_CONFIG.user?.id
                }));
            };
            
            this.notificationWS.onmessage = (event) => {
                const data = JSON.parse(event.data);
                this.handleWebSocketNotification(data);
            };
            
            this.notificationWS.onclose = () => {
                console.log('WebSocket notifications déconnecté');
                // Tentative de reconnexion après 5 secondes
                setTimeout(() => {
                    this.initWebSocketNotifications();
                }, 5000);
            };
            
            this.notificationWS.onerror = (error) => {
                console.error('Erreur WebSocket:', error);
            };
            
        } catch (error) {
            console.log('WebSocket non disponible, utilisation du polling');
        }
    }
    
    handleWebSocketNotification(data) {
        switch (data.type) {
            case 'new_feedback':
                this.showNotification('Nouveau feedback reçu', data.message, 'feedback');
                this.refreshDashboardData();
                break;
            case 'deadline_reminder':
                this.showNotification('Rappel d\'échéance', data.message, 'reminder');
                break;
            case 'report_status_change':
                this.showNotification('Statut du rapport mis à jour', data.message, 'report');
                this.refreshDashboardData();
                break;
            case 'system_notification':
                this.showNotification('Notification système', data.message, 'system');
                break;
        }
    }
    
    showNotification(title, message, type = 'info') {
        // Notification dans le navigateur
        if (Notification.permission === 'granted') {
            const notification = new Notification(title, {
                body: message,
                icon: '/assets/images/logos/ufhb-logo.png',
                tag: type,
                badge: '/assets/images/logos/ufhb-logo.png'
            });
            
            notification.onclick = () => {
                window.focus();
                notification.close();
                
                // Rediriger selon le type de notification
                switch (type) {
                    case 'feedback':
                        window.location.href = 'feedbacks/';
                        break;
                    case 'report':
                        window.location.href = 'rapport/';
                        break;
                    case 'reminder':
                        window.location.href = 'calendrier.php';
                        break;
                }
            };
            
            // Auto-fermeture après 5 secondes
            setTimeout(() => notification.close(), 5000);
        }
        
        // Toast notification dans l'interface
        showToast(message, this.getToastType(type), 5000);
        
        // Effet visuel sur l'icône de notification
        this.pulseNotificationIcon();
    }
    
    getToastType(notificationType) {
        const typeMap = {
            'feedback': 'info',
            'reminder': 'warning',
            'report': 'success',
            'system': 'info',
            'error': 'error'
        };
        
        return typeMap[notificationType] || 'info';
    }
    
    pulseNotificationIcon() {
        const notificationIcon = document.querySelector('#notificationsDropdown i');
        if (notificationIcon) {
            notificationIcon.classList.add('pulse');
            setTimeout(() => {
                notificationIcon.classList.remove('pulse');
            }, 2000);
        }
    }
    
    checkNewNotifications() {
        fetch('api/notifications-check.php', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': window.APP_CONFIG.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.hasNew) {
                data.newNotifications.forEach(notification => {
                    this.showNotification(
                        notification.titre_notification,
                        notification.contenu_notification,
                        notification.type_notification
                    );
                });
            }
        })
        .catch(error => {
            console.error('Erreur vérification notifications:', error);
        });
    }
    
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Raccourcis clavier uniquement si aucun input n'est focusé
            if (document.activeElement.tagName === 'INPUT' || 
                document.activeElement.tagName === 'TEXTAREA') {
                return;
            }
            
            switch (e.key) {
                case 'r':
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        this.refreshDashboardData();
                    }
                    break;
                case 'n':
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        window.location.href = 'rapport/redaction.php';
                    }
                    break;
                case 'c':
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        window.location.href = 'calendrier.php';
                    }
                    break;
                case 'f':
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        window.location.href = 'feedbacks/';
                    }
                    break;
                case '?':
                    e.preventDefault();
                    this.showKeyboardShortcuts();
                    break;
            }
        });
    }
    
    showKeyboardShortcuts() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Raccourcis Clavier</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <td><kbd>Ctrl</kbd> + <kbd>R</kbd></td>
                                            <td>Actualiser le dashboard</td>
                                        </tr>
                                        <tr>
                                            <td><kbd>Ctrl</kbd> + <kbd>N</kbd></td>
                                            <td>Nouveau rapport</td>
                                        </tr>
                                        <tr>
                                            <td><kbd>Ctrl</kbd> + <kbd>C</kbd></td>
                                            <td>Ouvrir le calendrier</td>
                                        </tr>
                                        <tr>
                                            <td><kbd>Ctrl</kbd> + <kbd>F</kbd></td>
                                            <td>Voir les feedbacks</td>
                                        </tr>
                                        <tr>
                                            <td><kbd>?</kbd></td>
                                            <td>Afficher cette aide</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }
    
    loadUserPreferences() {
        // Charger les préférences utilisateur depuis localStorage
        const preferences = JSON.parse(localStorage.getItem('dashboard_preferences') || '{}');
        
        // Appliquer les préférences
        if (preferences.autoRefresh === false) {
            this.disableAutoRefresh();
        }
        
        if (preferences.compactMode) {
            document.body.classList.add('compact-mode');
        }
        
        if (preferences.darkMode) {
            document.body.classList.add('dark-mode');
        }
    }
    
    saveUserPreferences() {
        const preferences = {
            autoRefresh: this.refreshInterval !== null,
            compactMode: document.body.classList.contains('compact-mode'),
            darkMode: document.body.classList.contains('dark-mode')
        };
        
        localStorage.setItem('dashboard_preferences', JSON.stringify(preferences));
    }
    
    disableAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }
    
    enableAutoRefresh() {
        if (!this.refreshInterval) {
            this.setupAutoRefresh();
        }
    }
    
    handleResize() {
        // Redimensionner les graphiques
        Object.values(this.charts).forEach(chart => {
            if (chart && chart.resize) {
                chart.resize();
            }
        });
        
        // Ajuster l'affichage des cartes sur mobile
        this.adjustMobileLayout();
    }
    
    adjustMobileLayout() {
        const isMobile = window.innerWidth <= 768;
        
        if (isMobile) {
            // Mode compact sur mobile
            document.body.classList.add('mobile-compact');
            
            // Masquer certains éléments moins importants
            document.querySelectorAll('.activity-time, .event-location').forEach(el => {
                el.style.display = 'none';
            });
        } else {
            document.body.classList.remove('mobile-compact');
            
            // Restaurer tous les éléments
            document.querySelectorAll('.activity-time, .event-location').forEach(el => {
                el.style.display = '';
            });
        }
    }
    
    handleVisibilityChange() {
        if (document.hidden) {
            // Page cachée - réduire l'activité
            this.disableAutoRefresh();
        } else {
            // Page visible - reprendre l'activité
            this.enableAutoRefresh();
            this.refreshDashboardData();
        }
    }
    
    getNotificationIcon(type) {
        const icons = {
            'feedback': 'comment',
            'reminder': 'bell',
            'system': 'cog',
            'complaint': 'exclamation-triangle',
            'calendar': 'calendar',
            'rapport': 'file-alt',
            'soutenance': 'graduation-cap'
        };
        
        return icons[type] || 'info-circle';
    }
    
    formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffTime = Math.abs(now - date);
        const diffMinutes = Math.ceil(diffTime / (1000 * 60));
        const diffHours = Math.ceil(diffTime / (1000 * 60 * 60));
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffMinutes < 60) {
            return `Il y a ${diffMinutes} min`;
        } else if (diffHours < 24) {
            return `Il y a ${diffHours}h`;
        } else if (diffDays === 1) {
            return 'Hier';
        } else if (diffDays < 7) {
            return `Il y a ${diffDays} jours`;
        } else {
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit'
            });
        }
    }
    
    // Méthode de nettoyage
    destroy() {
        // Nettoyer les intervalles
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
        
        // Fermer WebSocket
        if (this.notificationWS) {
            this.notificationWS.close();
        }
        
        // Détruire les graphiques
        Object.values(this.charts).forEach(chart => {
            if (chart && chart.destroy) {
                chart.destroy();
            }
        });
        
        // Sauvegarder les préférences
        this.saveUserPreferences();
        
        // Supprimer les event listeners
        document.removeEventListener('visibilitychange', this.handleVisibilityChange);
        window.removeEventListener('resize', this.handleResize);
        
        this.isInitialized = false;
    }
}

// Fonction utilitaire pour le debouncing
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

// Initialisation automatique quand le DOM est prêt
document.addEventListener('DOMContentLoaded', () => {
    window.etudiantDashboard = new EtudiantDashboard();
});

// Nettoyage avant la fermeture de la page
window.addEventListener('beforeunload', () => {
    if (window.etudiantDashboard) {
        window.etudiantDashboard.destroy();
    }
});