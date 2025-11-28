<?php
require_once '../includes/autoload.php';

/*
 * Logg ut
 */

// Sjekk at bruker er logget inn
if (!Auth::isLoggedIn()) {
    redirect('../auth/login.php', 'Du er ikke logget inn.', 'info');
}

// Kun tillat POST-requests 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../index.php', 'Ugyldig forespørsel.', 'danger');
}

// CSRF-sjekk for logout-forespørselen
csrf_check('../index.php');

// Logger ut bruker (tømmer session variabler, cookie)
Auth::logout();

redirect('../auth/login.php', 'Du er nå logget ut. Mi sees!', 'success');
?>
