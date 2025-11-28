<?php

/**
 * Sjekk om bruker er innlogget 
 */
function is_logged_in() {
    return Auth::isLoggedIn();
}

/**
 * Sjekk om bruker har rolle
 */
function has_role($role) {
    return Auth::hasRole($role);
}

/**
 * Sjekker om bruker er innlogget og eventuelt har en av de tillatte rollene i * $allowed_roles
 * Hvis ikke, redirect til login side eller riktig dashboard.
 */
function auth_check($allowed_roles = []) {
    if (!is_logged_in()) {
        redirect('../auth/login.php', 'Du må være innlogget for å se denne siden.', 'danger');
    }

    if (empty($allowed_roles)) {
        return;
    }
    
    foreach ($allowed_roles as $role) {

        if (has_role($role)) {
            return;
        }
    }

    // Send til riktig dashboard hvis bruker ikke har tilgang
    $dashboard = has_role('employer') 
    ? '../dashboard/employer.php' 
    : '../dashboard/applicant.php';
    redirect($dashboard, 'Du har ikke tilgang til denne siden.', 'danger'); 
}

?>