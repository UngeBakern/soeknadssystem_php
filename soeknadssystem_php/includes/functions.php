<?php
/**
 * Hovedfunksjoner for søknadssystemet
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vis feilmelding
 */
function set_flash($message, $type = 'success') {
    $_SESSION['flash_messages'][] = [
        'message' => $message,
        'type' => $type, // 'success', 'danger', 'info', 'warning'
    ];
}

/**
 * Shortcut: suksessmelding
 */
function show_success($message) {
    set_flash($message, 'success');
}

/**
 * Feilmelding 
 */
function show_error($message) {
    set_flash($message, 'danger'); 
}

/**
 * Hent og tøm flash-meldinger
 */
function get_flash_messages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Renderer flashmeldinger
 */
function render_flash_messages() {
    $flash_messages = get_flash_messages();

    if(empty($flash_messages)) {
        return;
    }

    foreach ($flash_messages as $flash) {

        $type   = Validator::sanitize($flash['type'] ?? 'danger');
        $message = Validator::sanitize($flash['message'] ?? '');

        if ($message === '') {
            continue;
        }

        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
            ' . $message . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Lukk"></button>
        </div>';
    }
}

/**
 * Redirect funksjon med valgfri flash melding
 */
function redirect($url, $message = '', $type = 'success') {
    if ($message !== '') {
        set_flash($message, $type);
    }

    header("Location: $url");
    exit();
}

/**
 * Formater dato til norsk format
 */
function format_date($date) {
    return date('d.m.Y H:i', strtotime($date));
}


/**
 * Jobb relaterte hjelpefunksjoner
 */

// Denne funksjonen returnerer en etikett for jobbstatus, brukes på jobblisten.
function get_job_status_label($status) {

    $labels = [
        'active' => [
            'label' => 'Aktiv',
            'class' => 'badge bg-success',
    ],
        'inactive' => [
            'label' => 'Inaktiv',
            'class' => 'badge bg-secondary',
    ],
        'deleted' => [
            'label' => 'Lukket',
            'class' => 'badge bg-danger',
        ],
    ];
    return $labels[$status] ?? $labels['active'];

}

function getStatusBadge($status) {
    $badges = [
        'active' => '<span class="badge bg-success">Aktiv</span>',
        'inactive' => '<span class="badge bg-secondary">Inaktiv</span>',
        'deleted' => '<span class="badge bg-danger">Lukket</span>',
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">' . Validator::sanitize($status) . '</span>';
}