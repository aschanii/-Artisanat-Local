<?php
// Vérifier si l'utilisateur est connecté
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Vérifier si l'utilisateur est admin
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Vérifier si l'utilisateur est un vendeur
function is_seller() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'seller';
}

// Rediriger si non connecté
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

// Rediriger si non admin
function require_admin() {
    require_login();
    if (!is_admin()) {
        header('Location: ../index.php');
        exit();
    }
}

// Rediriger si non vendeur
function require_seller() {
    require_login();
    if (!is_seller() && !is_admin()) {
        header('Location: ../index.php');
        exit();
    }
}

// Générer un token CSRF
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Valider le token CSRF
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>