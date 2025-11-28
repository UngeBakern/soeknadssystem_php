<?php
require_once '../includes/autoload.php';

/*
 * Dashboard for søker
 */

// Sjekk innlogging og rolle 
auth_check(['applicant']);

//Innlogget bruker 
$user      = Auth::user();
$user_id   = $user['id'];
$user_name = $user['name'] ?? 'Bruker';

// Håndterer trekk søknad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw_application'])) {

    csrf_check();

    $application_id = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);

    if (!$application_id) {
        redirect('applicant.php', 'Ugyldig søknads-ID.', 'danger'); 
    }

    $application = Application::findById($application_id);

    if (!$application) {
        redirect('applicant.php', 'Søknaden ble ikke funnet.', 'danger'); 
    }

    // Sjekk at innlogget søker eier søknaden 
    if ($application['applicant_id'] != $user_id) {
        redirect('applicant.php', 'Du har ikke tillatelse til å trekke tilbake denne søknaden.', 'danger'); 
    }

    // Blokker trekk på behandlet søknad 
    if ($application['status'] === 'Tilbud' || $application['status'] === 'Avslått') {
        redirect('applicant.php', 'Du kan ikke trekke tilbake en søknad som allerede er behandlet.', 'danger');
    }

    if (Application::delete($application_id)) {
        redirect('applicant.php', 'Søknaden ble trukket tilbake.', 'success');

    } else {    
        redirect('applicant.php', 'Noe gikk galt. Prøv igjen.', 'danger');
    }

}

/**
 * Jobber som er tilgjengelige for denne søkeren
 * Status = 'active', deadline ikke passert, søkeren har ikke søkt på dem
 */

$available_jobs = Job::getAvailableForApplicant($user_id);
// Søknader til statistikk og "mine søknader 
$my_applications = Application::getByApplicant($user_id);
// Enkle anbefalte stillinger = topp 3 av tilgjengelige jobber. 
$recommended_jobs = array_slice($available_jobs, 0, 3);

$pending_applications  = Application::getByApplicantAndStatus($user_id, 'Vurderes');
$accepted_applications = Application::getByApplicantAndStatus($user_id, 'Tilbud');
$rejected_applications = Application::getByApplicantAndStatus($user_id, 'Avslått');

// Beregn statistikk 
$stats = [
    'available_jobs'        => count($available_jobs),
    'my_applications'       => count($my_applications), 
    'pending'               => count($pending_applications),
    'accepted'              => count($accepted_applications),
    'rejected'              => count($rejected_applications),
    'favorites'             => '0' // Placeholder for favoritter
];

$status_badges = [
    'Mottatt'  => 'info', 
    'Vurderes' => 'warning',
    'Tilbud'   => 'success',
    'Avslått'  => 'danger'
];


// Sett sidevariabler
$page_title = 'Dashboard - Søker';
$body_class = 'bg-light';

require_once '../includes/header.php';


?>
       <!-- Page Header -->
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="mb-4">
                    <h1 class="h2 mb-2">Velkommen, <?php echo Validator::sanitize($user_name); ?>!</h1>
                    <p class="text-muted">Finn din neste hjelpelærerstilling</p>
                </div>
                <?php render_flash_messages(); ?>
            </div>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="container pb-5">
        <div class="row">
            <!-- Quick Actions -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-rocket text-primary me-2"></i>
                            Hurtighandlinger
                        </h5>
                        <div class="d-grid gap-2">
                            <a href="../jobs/list.php" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>
                                Søk etter stillinger
                            </a>
                            <a href="../profile/view.php" class="btn btn-outline-primary">
                                <i class="fas fa-user me-2"></i>
                                Oppdater profil
                            </a>
                            <a href="../profile/upload.php" class="btn btn-outline-secondary">
                                <i class="fas fa-file-alt me-2"></i>
                                Last opp dokumenter
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Min aktivitet
                        </h5>
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <div class="p-3 bg-primary bg-opacity-10 rounded">
                                    <h3 class="text-primary mb-1"><?php echo $stats['available_jobs']; ?></h3>
                                    <small class="text-muted">Tilgjengelige stillinger</small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="p-3 bg-success bg-opacity-10 rounded">
                                    <h3 class="text-success mb-1"><?php echo $stats['my_applications']; ?></h3>
                                    <small class="text-muted">Mine søknader</small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="p-3 bg-warning bg-opacity-10 rounded">
                                    <h3 class="text-warning mb-1"><?php echo $stats['pending']; ?></h3>
                                    <small class="text-muted">Under vurdering</small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="p-3 bg-info bg-opacity-10 rounded">
                                    <h3 class="text-info mb-1"><?php echo $stats['favorites']; ?></h3>
                                    <small class="text-muted">Favoritter</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Applications -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-paper-plane text-primary me-2"></i>
                            Mine søknader
                        </h5>

                        <?php if (!empty($my_applications)): ?>
                            <?php 
                            $recent_applications = array_slice($my_applications, 0, 5);
                            foreach ($recent_applications as $application): 
                                $badge_color = $status_badges[$application['status']] ?? 'secondary';
                            ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <div class="bg-primary bg-opacity-10 rounded p-2">
                                            <img src="../uialogo.jpeg" 
                                                 alt="Logo" 
                                                 class="img-fluid" 
                                                 style="width: 30px; height: 30px; object-fit: contain;">
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0">
                                                <a href="../jobs/view.php?id=<?php echo $application['job_id']; ?>" 
                                                   class="text-decoration-none text-dark">
                                                    <?php echo Validator::sanitize($application['job_title']); ?>
                                                </a>
                                            </h6>
                                            <span class="badge bg-<?php echo $badge_color; ?> ms-2">
                                                <?php echo Validator::sanitize($application['status']); ?>
                                            </span>
                                        </div>
                                        <p class="text-muted small mb-1">
                                            <i class="fas fa-building me-1"></i>
                                            <?php echo Validator::sanitize($application['employer_name']); ?>
                                        </p>
                                        <?php if (!empty($application['location'])): ?>
                                            <p class="text-muted small mb-2">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?php echo Validator::sanitize($application['location']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <small class="text-muted d-block mb-2">
                                            <i class="fas fa-calendar me-1"></i>
                                            Søkt <?php echo date('d.m.Y', strtotime($application['created_at'])); ?>
                                        </small>
                                        <div class="d-flex gap-2">
                                            <a href="../applications/view.php?id=<?php echo $application['id']; ?>" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>
                                                Se søknad
                                            </a>
                                            
                                            <?php if ($application['status'] !== 'Tilbud' && $application['status'] !== 'Avslått'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="withdraw_application" value="1">
                                                    <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                                    <button type="submit" 
                                                            class="btn btn-outline-danger btn-sm"
                                                            onclick="return confirm('Er du sikker på at du vil trekke denne søknaden? Dette kan ikke angres.');">
                                                        <i class="fas fa-times me-1"></i>
                                                        Trekk søknad
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <?php if (count($my_applications) > 5): ?>
                                <div class="text-center mt-3">
                                    <a href="../applications/my_applications.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-list me-1"></i>
                                        Se alle mine søknader (<?php echo count($my_applications); ?>)
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-paper-plane fa-2x text-muted mb-3"></i>
                                <h6>Ingen søknader ennå</h6>
                                <p class="text-muted mb-3">Du har ikke søkt på noen stillinger ennå.</p>
                                <a href="../jobs/list.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-search me-1"></i>
                                    Utforsk stillinger
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recommended Jobs -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-star text-primary me-2"></i>
                            Anbefalte stillinger
                        </h5>

                        <?php if (!empty($recommended_jobs)): ?>
                            <?php foreach ($recommended_jobs as $job): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <div class="bg-primary bg-opacity-10 rounded p-2">
                                            <img src="../uialogo.jpeg" 
                                                 alt="Logo" 
                                                 class="img-fluid" 
                                                 style="width: 30px; height: 30px; object-fit: contain;">
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo Validator::sanitize($job['title']); ?></h6>
                                        <p class="text-muted small mb-2"><?php echo Validator::sanitize($job['company']); ?></p>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo Validator::sanitize($job['location']); ?>
                                        </p>
                                        <div class="d-flex gap-2">
                                            <a href="../jobs/view.php?id=<?php echo $job['id']; ?>" 
                                               class="btn btn-outline-primary btn-sm">
                                                Se detaljer
                                            </a>
                                            <a href="../jobs/apply.php?id=<?php echo $job['id']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                Søk nå
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="text-center mt-3">
                                <a href="../jobs/list.php" class="btn btn-outline-primary btn-sm">
                                    Se alle stillinger
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-briefcase fa-2x text-muted mb-3"></i>
                                <h6>Ingen stillinger tilgjengelig</h6>
                                <p class="text-muted">Kom tilbake senere for nye muligheter.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include_once '../includes/footer.php'; ?>
