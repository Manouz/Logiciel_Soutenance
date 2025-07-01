// Attend que le DOM soit entièrement chargé
document.addEventListener('DOMContentLoaded', function () {

    // Fonctions d'échappement HTML simples pour la sécurité XSS de base côté client
    function escapeHTML(str) {
        if (str === null || typeof str === 'undefined') return '';
        return String(str).replace(/[&<>"']/g, function (match) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[match];
        });
    }

    function escapeAttribute(str) {
        // Pour les attributs HTML, en particulier les classes ou les URL simples.
        // Une validation plus poussée est nécessaire pour les URL complexes ou les styles.
        if (str === null || typeof str === 'undefined') return '';
        return escapeHTML(String(str)); // Pour ce cas, escapeHTML est suffisant.
                                     // Pour les URL dans href, encodeURIComponent pourrait être plus approprié sur les parties variables.
    }

    const traitementModal = new bootstrap.Modal(document.getElementById('traitementModal'));
    const btnAjouterTraitement = document.getElementById('btnAjouterTraitement');
    const formTraitement = document.getElementById('formTraitement');
    const listeTraitementsBody = document.getElementById('listeTraitementsBody');
    const messagesDiv = document.getElementById('messages');
    let traitementIdField = document.getElementById('traitementId');

    // Fonction pour afficher les messages
    function afficherMessage(message, type = 'info') {
        messagesDiv.innerHTML = `<div class="alert alert-${type}" role="alert">${message}</div>`;
        setTimeout(() => { messagesDiv.innerHTML = ''; }, 5000);
    }

    // Charger la liste des traitements au chargement de la page
    chargerTraitements();

    // Ouvrir le modal pour un nouveau traitement
    if (btnAjouterTraitement) {
        btnAjouterTraitement.addEventListener('click', function () {
            formTraitement.reset();
            traitementIdField.value = ''; // Assure qu'il n'y a pas d'ID pour un ajout
            document.getElementById('traitementModalLabel').textContent = 'Ajouter un Traitement';
            traitementModal.show();
        });
    }

    // Soumission du formulaire (Ajout/Modification)
    if (formTraitement) {
        formTraitement.addEventListener('submit', function (e) {
            e.preventDefault();
            sauvegarderTraitement();
        });
    }

    // Fonction pour charger les traitements depuis le serveur
    async function chargerTraitements() {
        try {
            // Remplacer par l'URL correcte du script PHP qui liste les traitements
            const response = await fetch('traitement_lister.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const traitements = await response.json();

            listeTraitementsBody.innerHTML = ''; // Vider le tableau
            if (traitements.length === 0) {
                listeTraitementsBody.innerHTML = '<tr><td colspan="8" class="text-center">Aucun traitement trouvé.</td></tr>';
                return;
            }

            traitements.forEach(trait => {
                const row = listeTraitementsBody.insertRow();
                // Attention XSS: Les données (trait.lib_trait, trait.description, etc.) sont insérées via innerHTML.
                // Elles devraient être nettoyées côté serveur (déjà fait avec sanitize_input pour la plupart)
                // ou échappées ici si elles peuvent contenir du HTML arbitraire.
                // Pour trait.icone, s'assurer qu'il ne contient que des classes CSS valides.
                row.innerHTML = `
                    <td>${trait.id_trait}</td>
                    <td>${escapeHTML(trait.lib_trait)}</td>
                    <td>${escapeHTML(trait.description || '')}</td>
                    <td>${escapeHTML(trait.url_traitement || '')}</td>
                    <td><i class="${escapeAttribute(trait.icone || 'fas fa-file')}"></i> ${escapeHTML(trait.icone || '')}</td>
                    <td>${trait.ordre_affichage}</td>
                    <td>${trait.est_actif == 1 ? '<span class="badge bg-success">Oui</span>' : '<span class="badge bg-danger">Non</span>'}</td>
                    <td>
                        <button class="btn btn-sm btn-warning btn-modifier" data-id="${trait.id_trait}"><i class="fas fa-edit"></i> Modifier</button>
                        <button class="btn btn-sm btn-danger btn-supprimer" data-id="${trait.id_trait}"><i class="fas fa-trash"></i> Supprimer</button>
                    </td>
                `;
            });

            // Attacher les écouteurs d'événements pour les nouveaux boutons
            attacherEcouteursActions();

        } catch (error) {
            console.error('Erreur lors du chargement des traitements:', error);
            listeTraitementsBody.innerHTML = '<tr><td colspan="8" class="text-center">Erreur lors du chargement des données.</td></tr>';
            afficherMessage('Erreur lors du chargement des traitements. Vérifiez la console pour plus de détails.', 'danger');
        }
    }

    // Fonction pour sauvegarder (ajouter ou modifier) un traitement
    async function sauvegarderTraitement() {
        const formData = new FormData(formTraitement);
        const idTrait = formData.get('id_trait');
        const url = idTrait ? 'traitement_modifier.php' : 'traitement_ajouter.php';

        // S'assurer que est_actif est envoyé même si non coché
        if (!formData.has('est_actif')) {
            formData.set('est_actif', '0');
        } else {
            formData.set('est_actif', '1');
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
            }

            const result = await response.json();

            if (result.success) {
                afficherMessage(result.message || 'Traitement sauvegardé avec succès!', 'success');
                traitementModal.hide();
                chargerTraitements(); // Recharger la liste
            } else {
                afficherMessage(result.message || 'Erreur lors de la sauvegarde.', 'danger');
            }
        } catch (error) {
            console.error('Erreur lors de la sauvegarde du traitement:', error);
            afficherMessage(`Erreur: ${error.message}`, 'danger');
        }
    }

    // Fonction pour charger les données d'un traitement pour modification
    async function chargerTraitementPourModification(id) {
        try {
            // Assurez-vous que traitement_lister.php peut retourner un seul traitement par ID
            const response = await fetch(`traitement_lister.php?id=${id}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const traitement = await response.json();

            if (traitement && !traitement.error) { // Supposant que l'API retourne un objet traitement
                document.getElementById('traitementId').value = traitement.id_trait;
                document.getElementById('lib_trait').value = traitement.lib_trait;
                document.getElementById('description').value = traitement.description || '';
                document.getElementById('url_traitement').value = traitement.url_traitement || '';
                document.getElementById('icone').value = traitement.icone || 'fas fa-file';
                document.getElementById('ordre_affichage').value = traitement.ordre_affichage || 0;
                document.getElementById('est_actif').checked = traitement.est_actif == 1;

                document.getElementById('traitementModalLabel').textContent = 'Modifier le Traitement';
                traitementModal.show();
            } else {
                afficherMessage(traitement.error || 'Traitement non trouvé.', 'danger');
            }
        } catch (error) {
            console.error('Erreur lors du chargement du traitement pour modification:', error);
            afficherMessage('Erreur lors du chargement des données du traitement.', 'danger');
        }
    }

    // Fonction pour supprimer un traitement
    async function supprimerTraitement(id) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce traitement ?')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('id_trait', id);

            const response = await fetch('traitement_supprimer.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
            }

            const result = await response.json();

            if (result.success) {
                afficherMessage(result.message || 'Traitement supprimé avec succès!', 'success');
                chargerTraitements(); // Recharger la liste
            } else {
                afficherMessage(result.message || 'Erreur lors de la suppression.', 'danger');
            }
        } catch (error) {
            console.error('Erreur lors de la suppression du traitement:', error);
            afficherMessage(`Erreur: ${error.message}`, 'danger');
        }
    }

    // Attacher les écouteurs d'événements pour les boutons Modifier/Supprimer
    function attacherEcouteursActions() {
        document.querySelectorAll('.btn-modifier').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.dataset.id;
                chargerTraitementPourModification(id);
            });
        });

        document.querySelectorAll('.btn-supprimer').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.dataset.id;
                supprimerTraitement(id);
            });
        });
    }

    // Initialisation (si nécessaire pour des éléments qui ne sont pas encore dans le DOM au premier chargement)
    // chargerTraitements(); // Déjà appelé plus haut
});
