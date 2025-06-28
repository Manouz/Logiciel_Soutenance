
</main>
        <!-- Fin du contenu principal -->
        
        <!-- Footer -->
        <footer class="footer bg-white border-top py-3 mt-auto">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="text-muted small">
                            © <?= date('Y') ?> <?= UNIVERSITY_NAME ?><br>
                            <?= APP_NAME ?> - Version <?= APP_VERSION ?>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="d-flex justify-content-md-end align-items-center">
                            <span class="text-muted small me-3">
                                <i class="fas fa-clock me-1"></i>
                                Dernière connexion: 
                                <?php if ($currentUser && $currentUser['login_time']): ?>
                                    <?= formatDateFR(date('Y-m-d H:i:s', $currentUser['login_time']), 'd/m/Y à H:i') ?>
                                <?php else: ?>
                                    Inconnue
                                <?php endif; ?>
                            </span>
                            
                            <?php if (SessionManager::isLoggedIn()): ?>
                                <div class="session-indicator">
                                    <span class="badge bg-success small">
                                        <i class="fas fa-circle pulse"></i> En ligne
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Informations techniques (visible uniquement en mode debug) -->
                <?php if (DEBUG_MODE && hasPermission(ACCESS_LEVEL_ADMIN)): ?>
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="debug-info small text-muted">
                                <strong>Debug Info:</strong>
                                PHP: <?= PHP_VERSION ?> | 
                                Mémoire: <?= round(memory_get_usage() / 1024 / 1024, 2) ?>MB / 
                                <?= round(memory_get_peak_usage() / 1024 / 1024, 2) ?>MB | 
                                Temps: <?= round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000) ?>ms
                                <?php if (function_exists('sys_getloadavg')): ?>
                                    | Load: <?= implode(', ', array_map(fn($l) => round($l, 2), sys_getloadavg())) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </footer>
    </div>
    <!-- Fin du wrapper principal -->
    
    <!-- Modal de confirmation générique -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="confirmModalBody">
                    Êtes-vous sûr de vouloir effectuer cette action ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmModalConfirm">Confirmer</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de chargement -->
    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <div id="loadingModalText">Chargement en cours...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    
    <!-- Scripts utilitaires communs -->
    <script src="<?= asset('js/common/global-functions.js') ?>"></script>
    
    <!-- Scripts spécifiques additionnels -->
    <?php foreach ($additionalJS as $jsFile): ?>
        <script src="<?= asset('js/' . $jsFile) ?>"></script>
    <?php endforeach; ?>
    
    <script>
    // Fonctions globales supplémentaires
    
    // Modal de confirmation générique
    function showConfirmModal(message, callback, title = 'Confirmation') {
        document.getElementById('confirmModalLabel').textContent = title;
        document.getElementById('confirmModalBody').textContent = message;
        
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        modal.show();
        
        // Nettoyer les anciens listeners
        const confirmBtn = document.getElementById('confirmModalConfirm');
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        // Ajouter le nouveau listener
        newConfirmBtn.addEventListener('click', function() {
            modal.hide();
            if (typeof callback === 'function') {
                callback();
            }
        });
    }
    
    // Modal de chargement
    function showLoadingModal(text = 'Chargement en cours...') {
        document.getElementById('loadingModalText').textContent = text;
        const modal = new bootstrap.Modal(document.getElementById('loadingModal'));
        modal.show();
        return modal;
    }
    
    function hideLoadingModal() {
        const modalElement = document.getElementById('loadingModal');
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
    }
    
    // Gestion des erreurs AJAX globales
    window.addEventListener('unhandledrejection', function(event) {
        console.error('Erreur non gérée:', event.reason);
        hideLoader();
        hideLoadingModal();
        showToast('Une erreur inattendue s\'est produite.', 'error');
    });
    
    // Auto-save functionality
    function setupAutoSave(formSelector, saveUrl, interval = 30000) {
        const form = document.querySelector(formSelector);
        if (!form) return;
        
        let autoSaveTimer;
        let hasUnsavedChanges = false;
        
        // Marquer les changements
        form.addEventListener('input', function() {
            hasUnsavedChanges = true;
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(autoSave, interval);
        });
        
        function autoSave() {
            if (!hasUnsavedChanges) return;
            
            const formData = new FormData(form);
            formData.append('auto_save', '1');
            
            fetch(saveUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-Token': window.APP_CONFIG.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hasUnsavedChanges = false;
                    showToast('Sauvegarde automatique effectuée', 'info', 2000);
                }
            })
            .catch(console.error);
        }
        
        // Avertir avant de quitter la page s'il y a des changements non sauvés
        window.addEventListener('beforeunload', function(e) {
            if (hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = 'Vous avez des modifications non sauvegardées. Voulez-vous vraiment quitter cette page ?';
            }
        });
    }
    
    // Fonction pour formater les tailles de fichier
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Validation de fichier côté client
    function validateFile(file, maxSize = null, allowedTypes = null) {
        const errors = [];
        
        maxSize = maxSize || window.APP_CONFIG.maxFileSize;
        allowedTypes = allowedTypes || window.APP_CONFIG.allowedTypes;
        
        // Vérifier la taille
        if (file.size > maxSize) {
            errors.push(`Le fichier est trop volumineux. Taille maximale: ${formatFileSize(maxSize)}`);
        }
        
        // Vérifier le type
        const fileExtension = file.name.split('.').pop().toLowerCase();
        if (allowedTypes && !allowedTypes.includes(fileExtension)) {
            errors.push(`Type de fichier non autorisé. Types acceptés: ${allowedTypes.join(', ')}`);
        }
        
        return errors;
    }
    
    // Drag & Drop pour upload de fichiers
    function setupDragAndDrop(dropZoneSelector, fileInputSelector, callback) {
        const dropZone = document.querySelector(dropZoneSelector);
        const fileInput = document.querySelector(fileInputSelector);
        
        if (!dropZone || !fileInput) return;
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            dropZone.classList.add('drag-over');
        }
        
        function unhighlight() {
            dropZone.classList.remove('drag-over');
        }
        
        dropZone.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const files = e.dataTransfer.files;
            if (typeof callback === 'function') {
                callback(files);
            }
        }
        
        dropZone.addEventListener('click', () => fileInput.click());
    }
    
    // Session timeout warning
    <?php if (SessionManager::isLoggedIn()): ?>
    let sessionWarningShown = false;
    const sessionTimeout = <?= SESSION_TIMEOUT ?> * 1000; // Convert to milliseconds
    const warningTime = sessionTimeout - (5 * 60 * 1000); // 5 minutes before expiry
    
    setTimeout(function() {
        if (!sessionWarningShown) {
            sessionWarningShown = true;
            showConfirmModal(
                'Votre session expire dans 5 minutes. Voulez-vous prolonger votre session ?',
                function() {
                    // Refresh session
                    fetch('api/refresh-session.php', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-Token': window.APP_CONFIG.csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            sessionWarningShown = false;
                            showToast('Session prolongée avec succès', 'success');
                        } else {
                            window.location.href = 'login.php?error=session_expired';
                        }
                    })
                    .catch(() => {
                        window.location.href = 'login.php?error=session_expired';
                    });
                },
                'Session expirée'
            );
        }
    }, warningTime);
    <?php endif; ?>
    
    // Initialisation au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        // Activer les tooltips Bootstrap
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Activer les popovers Bootstrap
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Gestion des formulaires avec confirmation
        document.querySelectorAll('form[data-confirm]').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const message = this.getAttribute('data-confirm');
                showConfirmModal(message, () => {
                    this.submit();
                });
            });
        });
        
        // Gestion des boutons de suppression avec confirmation
        document.querySelectorAll('[data-action="delete"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const message = this.getAttribute('data-confirm') || 'Êtes-vous sûr de vouloir supprimer cet élément ?';
                const url = this.getAttribute('href') || this.getAttribute('data-url');
                
                showConfirmModal(message, () => {
                    if (url) {
                        window.location.href = url;
                    }
                }, 'Confirmer la suppression');
            });
        });
        
        // Auto-focus sur le premier champ de formulaire visible
        const firstInput = document.querySelector('form input:not([type="hidden"]):not([readonly]), form select:not([readonly]), form textarea:not([readonly])');
        if (firstInput && !firstInput.value) {
            firstInput.focus();
        }
        
        // Prévisualisation d'images
        document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
            input.addEventListener('change', function() {
                const file = this.files[0];
                const previewId = this.getAttribute('data-preview');
                const preview = document.getElementById(previewId);
                
                if (file && preview) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (preview.tagName === 'IMG') {
                            preview.src = e.target.result;
                        } else {
                            preview.style.backgroundImage = `url(${e.target.result})`;
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
        
        // Masquer/afficher les mots de passe
        document.querySelectorAll('[data-toggle="password"]').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const target = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (target.type === 'password') {
                    target.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    target.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });
        
        // Auto-resize des textareas
        document.querySelectorAll('textarea[data-auto-resize]').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
            
            // Trigger initial resize
            textarea.dispatchEvent(new Event('input'));
        });
        
        // Compteur de caractères
        document.querySelectorAll('[data-counter]').forEach(input => {
            const counterId = input.getAttribute('data-counter');
            const counter = document.getElementById(counterId);
            const maxLength = input.getAttribute('maxlength');
            
            if (counter) {
                function updateCounter() {
                    const currentLength = input.value.length;
                    counter.textContent = maxLength ? `${currentLength}/${maxLength}` : currentLength;
                    
                    if (maxLength) {
                        const percentage = (currentLength / maxLength) * 100;
                        counter.className = '';
                        
                        if (percentage >= 90) {
                            counter.classList.add('text-danger');
                        } else if (percentage >= 75) {
                            counter.classList.add('text-warning');
                        } else {
                            counter.classList.add('text-muted');
                        }
                    }
                }
                
                input.addEventListener('input', updateCounter);
                updateCounter(); // Initial count
            }
        });
        
        // Contrôles de copie pour les codes/identifiants
        document.querySelectorAll('[data-copy]').forEach(element => {
            element.style.cursor = 'pointer';
            element.title = 'Cliquer pour copier';
            
            element.addEventListener('click', function() {
                const text = this.getAttribute('data-copy') || this.textContent;
                
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(() => {
                        showToast('Copié dans le presse-papiers', 'success', 2000);
                    });
                } else {
                    // Fallback pour les navigateurs plus anciens
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    showToast('Copié dans le presse-papiers', 'success', 2000);
                }
            });
        });
        
        // Affichage conditionnel basé sur les sélections
        document.querySelectorAll('[data-show-when]').forEach(element => {
            const condition = element.getAttribute('data-show-when');
            const [targetId, value] = condition.split('=');
            const target = document.getElementById(targetId);
            
            if (target) {
                function toggleVisibility() {
                    const isVisible = target.value === value;
                    element.style.display = isVisible ? '' : 'none';
                }
                
                target.addEventListener('change', toggleVisibility);
                toggleVisibility(); // Initial state
            }
        });
        
        // Validation en temps réel
        document.querySelectorAll('input[data-validate]').forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
        
        function validateField(field) {
            const rules = field.getAttribute('data-validate').split('|');
            const feedback = field.parentNode.querySelector('.invalid-feedback');
            let isValid = true;
            let errorMessage = '';
            
            for (const rule of rules) {
                if (rule === 'required' && !field.value.trim()) {
                    isValid = false;
                    errorMessage = 'Ce champ est requis.';
                    break;
                } else if (rule === 'email' && field.value && !isValidEmail(field.value)) {
                    isValid = false;
                    errorMessage = 'Adresse email invalide.';
                    break;
                } else if (rule.startsWith('min:')) {
                    const min = parseInt(rule.split(':')[1]);
                    if (field.value.length < min) {
                        isValid = false;
                        errorMessage = `Minimum ${min} caractères requis.`;
                        break;
                    }
                } else if (rule.startsWith('max:')) {
                    const max = parseInt(rule.split(':')[1]);
                    if (field.value.length > max) {
                        isValid = false;
                        errorMessage = `Maximum ${max} caractères autorisés.`;
                        break;
                    }
                }
            }
            
            field.classList.remove('is-valid', 'is-invalid');
            field.classList.add(isValid ? 'is-valid' : 'is-invalid');
            
            if (feedback) {
                feedback.textContent = errorMessage;
            }
            
            return isValid;
        }
        
        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    });
    
    // Performance monitoring (en mode debug uniquement)
    <?php if (DEBUG_MODE): ?>
    window.addEventListener('load', function() {
        if (window.performance && window.performance.timing) {
            const timing = window.performance.timing;
            const loadTime = timing.loadEventEnd - timing.navigationStart;
            
            if (loadTime > 3000) { // Plus de 3 secondes
                console.warn('Page lente détectée:', loadTime + 'ms');
            }
        }
    });
    <?php endif; ?>
    </script>
    
    <style>
    /* Styles additionnels pour le footer et les composants */
    .footer {
        margin-left: var(--sidebar-width);
        transition: var(--transition);
    }
    
    .footer.sidebar-collapsed {
        margin-left: 80px;
    }
    
    @media (max-width: 768px) {
        .footer {
            margin-left: 0;
        }
    }
    
    .pulse {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .drag-over {
        border: 2px dashed var(--primary-color) !important;
        background-color: rgba(26, 84, 144, 0.1) !important;
    }
    
    .debug-info {
        padding: 0.5rem;
        background-color: #f8f9fa;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
    }
    
    /* Styles pour les indicateurs de validation */
    .is-valid {
        border-color: var(--success-color) !important;
    }
    
    .is-invalid {
        border-color: var(--danger-color) !important;
    }
    
    .invalid-feedback {
        display: block;
        color: var(--danger-color);
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    .valid-feedback {
        display: block;
        color: var(--success-color);
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    /* Styles pour les éléments copiables */
    [data-copy]:hover {
        background-color: rgba(0, 0, 0, 0.05);
        border-radius: 4px;
        padding: 2px 4px;
    }
    
    /* Styles pour le content wrapper sans sidebar */
    .content-wrapper.no-sidebar {
        margin-left: 0;
        margin-top: 0;
    }
    
    /* Animation pour les toasts */
    .toast {
        animation: slideInRight 0.3s ease-out;
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    /* Responsive adjustments */
    @media (max-width: 576px) {
        .toast-container {
            right: 10px;
            left: 10px;
        }
        
        .toast {
            margin-bottom: 0.5rem;
        }
    }
    </style>

</body>
</html>

<?php
// Nettoyer les buffers de sortie si nécessaire
if (ob_get_level()) {
    ob_end_flush();
}
?>