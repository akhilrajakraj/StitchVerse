<?php
// includes/functions.php

// Sanitize input for safe HTML output
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Check if any user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) || isset($_SESSION['tailor_id']);
}

// Get the current logged-in user's ID (either tailor or customer)
function current_user_id() {
    return $_SESSION['user_id'] ?? ($_SESSION['tailor_id'] ?? null);
}

// Get the current user's role
function get_role() {
    if (isset($_SESSION['user_id'])) return 'customer';
    if (isset($_SESSION['tailor_id'])) return 'tailor';
    return null;
}

// Redirect to login if not authenticated
function require_login($redirect_to = null) {
    if (!is_logged_in()) {
        $redirect = $redirect_to ? urlencode($redirect_to) : '';
        header("Location: login.php?error=auth_required&redirect=$redirect");
        exit;
    }
}

// Format date output (e.g. 'Mar 12, 2025')
function format_date($date_string) {
    $date = date_create($date_string);
    return $date ? date_format($date, 'M j, Y') : 'N/A';
}

// Debug helper (for dev only)
function debug_log($message) {
    error_log('[ThreadHub] ' . $message);
}

?>