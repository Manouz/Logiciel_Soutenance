</main>
    
    <?php if (SessionManager::isLoggedIn()): ?>
    <!-- Modal Profil Utilisateur -->
    <div class="modal fade" id="profileModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mon Profil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="profile-avatar mb-3">
                                <img src="<?= ASSETS_URL ?>images/avatars/default.png" alt="Avatar" class="rounded-circle" width="120" height="120">
                            </div>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-camera"></i> Changer la photo
                            </button>
                        </div>
                        <div class="col-md-8">
                            <form id="profileForm">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nom</label>
                                        <input type="text" class="form-control" name="nom" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Prénoms</label>
                                        <input type="text" class="form-control" name="prenoms" readonly>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars(SessionManager::getUserEmail()) ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" name="telephone">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Rôle principal</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($current_role) ?>" readonly>
                                </div>
                                <?php if (count($user_roles) > 1): ?>
                                <div class="mb-3">
                                    <label class="form-label">Autres rôles</label>
                                    <div>
                                        <?php foreach ($user_roles as $role): ?>
                                            <?php if ($role !== $current_role): ?>
                                                <span class="badge bg-secondary me-1"><?= htmlspecialchars($role) ?></span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" onclick="updateProfile()">Sauvegarder</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Paramètres -->
    <div class="modal fade" id="settingsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Paramètres</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="settingsForm">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        
                        <h6>Changer le mot de passe</h6>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe actuel</label>
                            <input type="password" class="form-control" name="current_password" autocomplete="current-password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" name="new_password" autocomplete="new-password">
                            <div class="form-text">
                                Le mot de passe doit contenir au moins 8 caractères avec majuscules, minuscules, chiffres et caractères spéciaux.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password" class="form-control" name="confirm_password" autocomplete="new-password">
                        </div>
                        
                        <hr>
                        
                        <h6>Notifications</h6>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="email_notifications" checked>
                            <label class="form-check-label">Notifications par email</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="desktop_notifications" checked>
                            <label class="form-check-label">Notifications sur le bureau</label>
                        </div>
                        
                        <hr>
                        
                        <h6>Préférences d'affichage</h6>
                        <div class="mb-3">
                            <label class="form-label">Thème</label>
                            <select class="form-select" name="theme">
                                <option value="light">Clair</option>
                                <option value="dark">Sombre</option>
                                <option value="auto">Automatique</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Langue</label>
                            <select class="form-select" name="language">
                                <option value="fr" selected>Français</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="saveSettings()">Sauvegarder</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Footer -->
    <footer class="footer mt-5 py-4 bg-light border-top">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">
                        &copy; <?= date('Y') ?> <?= UNIVERSITY_NAME ?>. Tous droits réservés.
                    </p>
                    <small class="text-muted">
                        <?= APP_NAME ?> v<?= APP_VERSION ?>
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        Développé pour la validation académique Master 2
                    </small>
                    <?php if (SessionManager::isLoggedIn()): ?>
                    <br>
                    <small class="text-muted">
                        Connecté en tant que: <?= htmlspecialchars($current_role) ?>
                    </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Scripts JavaScript -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    
    <!-- Sweet Alert 2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Scripts globaux -->
    <script src="<?= ASSETS_URL ?>js/common/global-functions.js"></script>
    
    <?php if (SessionManager::isLoggedIn()): ?>
    <!-- Scripts spécifiques aux rôles -->
    <?php if (hasRole(ROLE_ADMIN)): ?>
        <script src="<?= ASSETS_URL ?>js/admin/admin-dashboard.js"></script>
        <script src="<?= ASSETS_URL ?>js/admin/crud-operations.js"></script>
    <?php endif; ?>
    
    <?php if (hasRole(ROLE_RESPONSABLE_SCOLARITE)): ?>
        <script src="<?= ASSETS_URL ?>js/responsable_scolarite/dashboard-responsable.js"></script>
    <?php endif; ?>
    
    <?php if (hasRole(ROLE_ETUDIANT)): ?>
        <script src="<?= ASSETS_URL ?>js/etudiant/dashboard-etudiant.js"></script>
    <?php endif; ?>
    <?php endif; ?>
    
    <!-- Scripts personnalisés pour la page -->
    <?php if (isset($custom_js)): ?>
        <?php foreach ($custom_js as $js): ?>
            <script src="<?= ASSETS_URL ?>js/<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
    $(document).ready(function() {
        // Initialisation de la sidebar
        initSidebar();
        
        // Chargement des notifications
        loadNotifications();
        
        // Configuration globale d'AJAX
        setupAjaxDefaults();
        
        // Initialisation des tooltips et popovers
        initBootstrapComponents();
        
        // Auto-refresh des notifications
        setInterval(loadNotifications, 60000); // Toutes les minutes
    });
    
    // Initialisation de la sidebar
    function initSidebar() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        
        if (sidebarToggle && sidebar && mainContent) {
            sidebarToggle.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.toggle('show');
                } else {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                }
            });
            
            // Fermer la sidebar sur mobile quand on clique à l'extérieur
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768 && 
                    !sidebar.contains(e.target) && 
                    !sidebarToggle.contains(e.target) && 
                    sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            });
        }
    }
    
    // Chargement des notifications
    function loadNotifications() {
        <?php if (SessionManager::isLoggedIn()): ?>
        fetch(CONFIG.API_URL + 'notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationsUI(data.notifications);
                }
            })
            .catch(error => console.error('Erreur notifications:', error));
        <?php endif; ?>
    }
    
    // Mise à jour de l'interface des notifications
    function updateNotificationsUI(notifications) {
        const count = notifications.filter(n => !n.est_lue).length;
        const countElement = document.getElementById('notificationsCount');
        const listElement = document.getElementById('notificationsList');
        
        if (countElement) {
            countElement.textContent = count;
            countElement.style.display = count > 0 ? 'inline' : 'none';
        }
        
        if (listElement) {
            listElement.innerHTML = '<li><h6 class="dropdown-header">Notifications</h6></li>';
            
            if (notifications.length === 0) {
                listElement.innerHTML += '<li><div class="text-center p-3"><small class="text-muted">Aucune notification</small></div></li>';
            } else {
                notifications.slice(0, 5).forEach(notification => {
                    const item = document.createElement('li');
                    item.innerHTML = `
                        <a class="dropdown-item ${!notification.est_lue ? 'fw-bold' : ''}" href="#" onclick="markAsRead(${notification.notification_id})">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold">${notification.titre_notification}</div>
                                    <small class="text-muted">${notification.contenu_notification}</small>
                                </div>
                                <small class="text-muted">${timeAgo(notification.date_creation)}</small>
                            </div>
                        </a>
                    `;
                    listElement.appendChild(item);
                });
                
                if (notifications.length > 5) {
                    listElement.innerHTML += `
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="#">Voir toutes les notifications</a></li>
                    `;
                }
            }
        }
    }
    
    // Marquer une notification comme lue
    function markAsRead(notificationId) {
        fetch(CONFIG.API_URL + 'notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CONFIG.CSRF_TOKEN
            },
            body: JSON.stringify({
                action: 'mark_read',
                notification_id: notificationId
            })
        });
    }
    
    // Configuration globale d'AJAX
    function setupAjaxDefaults() {
        // Ajouter le token CSRF à toutes les requêtes AJAX
        $.ajaxSetup({
            beforeSend: function(xhr, settings) {
                if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                    xhr.setRequestHeader("X-CSRF-Token", CONFIG.CSRF_TOKEN);
                }
            }
        });
        
        // Gestion globale des erreurs AJAX
        $(document).ajaxError(function(event, xhr, settings, error) {
            if (xhr.status === 401) {
                showNotification('Session expirée. Veuillez vous reconnecter.', 'error');
                setTimeout(() => {
                    window.location.href = CONFIG.BASE_URL + 'login.php';
                }, 2000);
            } else if (xhr.status === 403) {
                showNotification('Accès non autorisé.', 'error');
            } else if (xhr.status >= 500) {
                showNotification('Erreur serveur. Veuillez réessayer.', 'error');
            }
        });
    }
    
    // Initialisation des composants Bootstrap
    function initBootstrapComponents() {
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }
    
    // Fonctions utilitaires
    function timeAgo(dateString) {
        const now = new Date();
        const date = new Date(dateString);
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) return 'À l\'instant';
        if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' min';
        if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' h';
        if (diffInSeconds < 2592000) return Math.floor(diffInSeconds / 86400) + ' j';
        
        return date.toLocaleDateString('fr-FR');
    }
    
    // Fonctions pour les modals
    function updateProfile() {
        const formData = new FormData(document.getElementById('profileForm'));
        
        fetch(CONFIG.API_URL + 'profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Profil mis à jour avec succès', 'success');
                bootstrap.Modal.getInstance(document.getElementById('profileModal')).hide();
            } else {
                showNotification(data.message || 'Erreur lors de la mise à jour', 'error');
            }
        })
        .catch(error => {
            showNotification('Erreur lors de la mise à jour', 'error');
        });
    }
    
    function saveSettings() {
        const formData = new FormData(document.getElementById('settingsForm'));
        
        fetch(CONFIG.API_URL + 'settings.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Paramètres sauvegardés avec succès', 'success');
                bootstrap.Modal.getInstance(document.getElementById('settingsModal')).hide();
                
                // Recharger la page si le thème a changé
                if (formData.get('theme')) {
                    location.reload();
                }
            } else {
                showNotification(data.message || 'Erreur lors de la sauvegarde', 'error');
            }
        })
        .catch(error => {
            showNotification('Erreur lors de la sauvegarde', 'error');
        });
    }
    
    // Gestion des formulaires avec confirmation
    window.confirmAction = function(message, callback) {
        Swal.fire({
            title: 'Confirmation',
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed && typeof callback === 'function') {
                callback();
            }
        });
    };
    
    // Loader global
    window.showLoader = function(show = true) {
        const loader = document.getElementById('globalLoader');
        if (loader) {
            loader.style.display = show ? 'flex' : 'none';
        }
    };
    </script>
    
    <!-- Loader global -->
    <div id="globalLoader" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" style="background: rgba(255,255,255,0.8); z-index: 9999; display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
    </div>
    
</body>
</html>