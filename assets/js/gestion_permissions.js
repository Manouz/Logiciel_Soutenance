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

    // Note: escapeAttribute n'est pas directement utilisé ici car on coche des checkboxes,
    // mais c'est bon de l'avoir si on ajoutait des attributs dynamiques.

    const selectGroupeUtilisateur = document.getElementById('selectGroupeUtilisateur');
    const permissionsContainer = document.getElementById('permissionsContainer');
    const listePermissionsBody = document.getElementById('listePermissionsBody');
    const nomGroupeSelectionneSpan = document.getElementById('nomGroupeSelectionne');
    const formPermissions = document.getElementById('formPermissions');
    const messagesPermissionsDiv = document.getElementById('messagesPermissions');
    const selectedGroupeIdField = document.getElementById('selectedGroupeId');

    // Fonction pour afficher les messages
    function afficherMessagePermissions(message, type = 'info') {
        messagesPermissionsDiv.innerHTML = `<div class="alert alert-${type}" role="alert">${message}</div>`;
        setTimeout(() => { messagesPermissionsDiv.innerHTML = ''; }, 5000);
    }

    // Charger les groupes d'utilisateurs au chargement de la page
    async function chargerGroupesUtilisateurs() {
        try {
            // Remplacer par l'URL correcte du script PHP qui liste les groupes
            // Ce script PHP devra être créé. Ex: 'groupe_lister.php'
            const response = await fetch('groupe_lister.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const groupes = await response.json();

            selectGroupeUtilisateur.innerHTML = '<option selected disabled value="">Sélectionnez un groupe...</option>'; // Reset
            if (groupes.length > 0) {
                groupes.forEach(groupe => {
                    const option = document.createElement('option');
                    option.value = groupe.id_gu;
                    option.textContent = groupe.lib_gu;
                    selectGroupeUtilisateur.appendChild(option);
                });
            } else {
                 selectGroupeUtilisateur.innerHTML = '<option selected disabled value="">Aucun groupe trouvé</option>';
            }
        } catch (error) {
            console.error('Erreur lors du chargement des groupes:', error);
            selectGroupeUtilisateur.innerHTML = '<option selected disabled value="">Erreur chargement</option>';
            afficherMessagePermissions('Erreur lors du chargement des groupes.', 'danger');
        }
    }

    // Charger les permissions pour le groupe sélectionné
    async function chargerPermissionsPourGroupe(id_gu, nom_gu) {
        if (!id_gu) {
            permissionsContainer.style.display = 'none';
            return;
        }

        selectedGroupeIdField.value = id_gu;
        nomGroupeSelectionneSpan.textContent = nom_gu;
        permissionsContainer.style.display = 'block';
        listePermissionsBody.innerHTML = '<tr><td colspan="8" class="text-center">Chargement des permissions...</td></tr>';

        try {
            // Remplacer par l'URL correcte du script PHP qui charge les permissions pour un groupe
            // Ce script PHP devra être créé. Ex: 'permissions_charger.php'
            const response = await fetch(`permissions_charger.php?id_gu=${id_gu}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json(); // Attend un objet {traitements: [], permissions: {}}

            listePermissionsBody.innerHTML = ''; // Vider le tableau

            if (data.traitements && data.traitements.length > 0) {
                data.traitements.forEach(trait => {
                    const perm = data.permissions[trait.id_trait] || {}; // Permissions pour ce traitement
                    const row = listePermissionsBody.insertRow();
                    // Le libellé du traitement est la seule donnée dynamique affichée ici.
                    // Les noms des permissions sont fixes.
                    row.innerHTML = `
                        <td>${escapeHTML(trait.lib_trait)} (ID: ${trait.id_trait})</td>
                        <td><input type="checkbox" class="form-check-input" name="permissions[${trait.id_trait}][voir_dans_sidebar]" ${perm.voir_dans_sidebar ? 'checked' : ''}></td>
                        <td><input type="checkbox" class="form-check-input" name="permissions[${trait.id_trait}][peut_ajouter]" ${perm.peut_ajouter ? 'checked' : ''}></td>
                        <td><input type="checkbox" class="form-check-input" name="permissions[${trait.id_trait}][peut_modifier]" ${perm.peut_modifier ? 'checked' : ''}></td>
                        <td><input type="checkbox" class="form-check-input" name="permissions[${trait.id_trait}][peut_supprimer]" ${perm.peut_supprimer ? 'checked' : ''}></td>
                        <td><input type="checkbox" class="form-check-input" name="permissions[${trait.id_trait}][peut_imprimer]" ${perm.peut_imprimer ? 'checked' : ''}></td>
                        <td><input type="checkbox" class="form-check-input" name="permissions[${trait.id_trait}][peut_exporter]" ${perm.peut_exporter ? 'checked' : ''}></td>
                        <td><input type="checkbox" class="form-check-input" name="permissions[${trait.id_trait}][peut_importer]" ${perm.peut_importer ? 'checked' : ''}></td>
                    `;
                });
            } else {
                listePermissionsBody.innerHTML = '<tr><td colspan="8" class="text-center">Aucun traitement à configurer. Veuillez d\'abord ajouter des traitements.</td></tr>';
            }
        } catch (error) {
            console.error('Erreur lors du chargement des permissions:', error);
            listePermissionsBody.innerHTML = '<tr><td colspan="8" class="text-center">Erreur lors du chargement des permissions.</td></tr>';
            afficherMessagePermissions('Erreur lors du chargement des permissions.', 'danger');
        }
    }

    // Écouteur pour le changement de groupe sélectionné
    if (selectGroupeUtilisateur) {
        selectGroupeUtilisateur.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            chargerPermissionsPourGroupe(this.value, selectedOption.text);
        });
    }

    // Soumission du formulaire de permissions
    if (formPermissions) {
        formPermissions.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(formPermissions);
            const id_gu = selectedGroupeIdField.value;
            if (!id_gu) {
                afficherMessagePermissions('Aucun groupe sélectionné.', 'danger');
                return;
            }
            // formData.append('id_gu', id_gu); // Déjà dans le formulaire via input hidden

            try {
                // Remplacer par l'URL correcte du script PHP qui sauvegarde les permissions
                // Ce script PHP devra être créé. Ex: 'permissions_sauvegarder.php'
                const response = await fetch('permissions_sauvegarder.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
                }

                const result = await response.json();
                if (result.success) {
                    afficherMessagePermissions(result.message || 'Permissions sauvegardées avec succès!', 'success');
                    // Optionnel: Recharger les permissions pour voir les changements (si la sauvegarde ne retourne pas tout)
                    // chargerPermissionsPourGroupe(id_gu, nomGroupeSelectionneSpan.textContent);
                } else {
                    afficherMessagePermissions(result.message || 'Erreur lors de la sauvegarde des permissions.', 'danger');
                }
            } catch (error) {
                console.error('Erreur lors de la sauvegarde des permissions:', error);
                afficherMessagePermissions(`Erreur: ${error.message}`, 'danger');
            }
        });
    }

    // Initialisation
    chargerGroupesUtilisateurs();
});
