<?php
require_once '../includes/autoload.php';

/*
 * Slett en stilling
 */

// Må være arbeidsgiver eller admin
auth_check(['employer', 'admin']);

//Innlogget bruker 
$user      = Auth::user();
$user_id   = $user['id'];
$user_role = $user['role'];

// Kun POST-requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../dashboard/employer.php', 'Ugyldig forespørsel.', 'danger');
}

// CSRF-sjekk
csrf_check();

// Hent job_id POST og sørg for at det er et heltall 
$job_id = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);

if (!$job_id) {
    redirect('../dashboard/employer.php', 'Ugyldig stillings-ID.', 'danger');
}

// Hent stillingen
$job = Job::findById($job_id);

if (!$job) {
    redirect('../dashboard/employer.php', 'Stillingen finnes ikke.', 'danger');
}

// Hvis IKKE rolle admin ELLER bruker har employer & employer id matcher med user
if (
    !(
        ($user_role === 'admin') || 
        ($user_role === 'employer' && $job['employer_id'] == $user_id)
    )
) {
    redirect('view.php?id=' . $job_id, 'Du har ikke tilgang til å slette denne stillingen.', 'danger');
}

// Slett stillingen
if (Job::delete($job_id)) {
    redirect('../dashboard/employer.php', 'Stillingen er slettet!', 'success');
} else {
    redirect('../dashboard/employer.php', 'Kunne ikke slette stillingen. Prøv igjen senere.', 'danger');
}