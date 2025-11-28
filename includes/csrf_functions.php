<?php

/**
 * Generer CSRF token
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valider CSRF token
 */
function csrf_verify() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/**
 * CSRF input filed for forms 
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . Validator::sanitize(csrf_token()) . '">';
}

/**
 * CSRF redirect ved feil
 */
function csrf_check($redirect_url = null) {
    if (!csrf_verify()) {
        if ($redirect_url) {
            redirect($redirect_url, 'Sikkerhetsfeil. Vennligst prøv igjen.', 'danger');
        } else {
            http_response_code(403);
            die('Sikkerhetsfeil: Ugyldig forespørsel.');
        }
    }
}

/**
 * Regenerer token
 */
function csrf_regenerate() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}