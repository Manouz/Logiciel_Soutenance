<?php
// Fonctions utilitaires générales pour le projet

/**
 * Redirige vers une URL spécifiée.
 * @param string $url L'URL de redirection.
 */
function redirect(string $url): void {
    header("Location: " . $url);
    exit;
}

/**
 * Nettoie une chaîne de caractères pour éviter les failles XSS.
 * @param string|null $data La chaîne à nettoyer.
 * @return string La chaîne nettoyée.
 */
function sanitize_input(?string $data): string {
    if ($data === null) {
        return '';
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Vérifie si l'utilisateur est connecté et a un certain rôle.
 * @param array $roles_autorises Array des rôles autorisés. Si vide, vérifie juste si connecté.
 * @return bool True si autorisé, false sinon.
 */
function check_user_role(array $roles_autorises = []): bool {
    if (!isset($_SESSION['user_id'])) {
        return false; // Utilisateur non connecté
    }
    if (empty($roles_autorises)) {
        return true; // Connecté, aucun rôle spécifique requis
    }
    if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], $roles_autorises)) {
        return true; // L'utilisateur a l'un des rôles requis
    }
    return false; // Rôle non autorisé
}

/**
 * Définit un message flash en session.
 * @param string $message Le message à afficher.
 * @param string $type Le type de message (ex: 'success', 'danger', 'info').
 */
function set_flash_message(string $message, string $type = 'info'): void {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Affiche un message flash s'il existe et le supprime de la session.
 * Utilisé directement dans le template ou via une fonction d'affichage.
 * NOTE: Le header.php a déjà une logique pour afficher les messages flash.
 * Cette fonction pourrait être utilisée si on veut un contrôle plus fin à d'autres endroits.
 */
function display_flash_message(): void {
    if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])) {
        echo "<div class='alert alert-" . htmlspecialchars($_SESSION['flash_type']) . " alert-dismissible fade show' role='alert'>";
        echo htmlspecialchars($_SESSION['flash_message']);
        echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
        echo "</div>";
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

/**
 * Génère une réponse JSON standardisée.
 * @param bool $success Statut de l'opération.
 * @param string|null $message Message descriptif.
 * @param array $data Données additionnelles à retourner.
 * @param int $status_code Code de statut HTTP.
 */
function json_response(bool $success, ?string $message = null, array $data = [], int $status_code = 200): void {
    header('Content-Type: application/json');
    http_response_code($status_code);
    $response = ['success' => $success];
    if ($message !== null) {
        $response['message'] = $message;
    }
    if (!empty($data)) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

/**
 * Tronque une chaîne de caractères à une longueur donnée et ajoute "..."
 * @param string $text
 * @param int $max_length
 * @return string
 */
function truncate_text(string $text, int $max_length = 100): string {
    if (mb_strlen($text) > $max_length) {
        $text = mb_substr($text, 0, $max_length - 3) . '...';
    }
    return $text;
}

/**
 * Vérifie si la requête actuelle est une requête POST.
 * @return bool
 */
function is_post_request(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Vérifie si la requête actuelle est une requête GET.
 * @return bool
 */
function is_get_request(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

// TODO - SÉCURITÉ : Implémenter la protection CSRF
// Pour cela, il faudrait :
// 1. Une fonction pour générer un token CSRF et le stocker en session.
//    Ex: generate_csrf_token()
// 2. Une fonction pour valider un token CSRF soumis avec un formulaire.
//    Ex: validate_csrf_token($submitted_token)
// 3. Inclure le token CSRF comme champ caché dans tous les formulaires qui modifient l'état (POST).
//    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
// 4. Vérifier le token au début des scripts PHP qui traitent ces formulaires.
//    if (!validate_csrf_token($_POST['csrf_token'])) { /* Erreur ou rejet */ }

// Vous pouvez ajouter d'autres fonctions utilitaires ici au besoin.
// Par exemple, des fonctions pour la pagination, la gestion des dates, etc.

?>
