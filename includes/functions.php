<?php
// Fonctions utilitaires

function format_price($price) {
    return number_format($price, 2, ',', ' ') . ' €';
}

function limit_text($text, $limit) {
    if (str_word_count($text, 0) > $limit) {
        $words = str_word_count($text, 2);
        $pos = array_keys($words);
        $text = substr($text, 0, $pos[$limit]) . '...';
    }
    return $text;
}

function generate_star_rating($rating) {
    $full_stars = floor($rating);
    $half_star = $rating - $full_stars >= 0.5;
    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);

    $html = '';
    for ($i = 0; $i < $full_stars; $i++) {
        $html .= '<i class="fas fa-star"></i>';
    }
    if ($half_star) {
        $html .= '<i class="fas fa-star-half-alt"></i>';
    }
    for ($i = 0; $i < $empty_stars; $i++) {
        $html .= '<i class="far fa-star"></i>';
    }
    return $html;
}

function base_url($path = '') {
    return SITE_URL . '/' . ltrim($path, '/');
}

// 🧼 Fonction unique pour nettoyer les entrées utilisateur
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header('Location: ' . $url);
    exit();
}

function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        echo '<div class="alert alert-' . $type . '">' . $message . '</div>';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    }
}

function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function generate_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'];
}

function log_error($message) {
    $log_file = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $message = "[$timestamp] $message\n";
    file_put_contents($log_file, $message, FILE_APPEND | LOCK_EX);
}

function getArtisanImage($image_path) {
    $path = 'assets/images/artisans/' . $image_path;
    return ($image_path && file_exists($path)) ? $path : 'assets/images/artisan-default.jpg';
}

function getCategoryImage($image_path) {
    $path = 'assets/images/categories/' . $image_path;
    return ($image_path && file_exists($path)) ? $path : 'assets/images/category-default.jpg';
}

function truncateText($text, $length) {
    return (strlen($text) > $length) ? substr($text, 0, $length) . '...' : $text;
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: 403.php');
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?>
